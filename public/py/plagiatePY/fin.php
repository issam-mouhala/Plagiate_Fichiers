<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\Submission;
use App\Models\SubmissionZipFile;
use App\Models\SubmissionAnalysis;
use App\Jobs\ProcessPlagiarismAnalysis;

class PlagiatController extends Controller
{
    function index()
    {
        return view("accueil.index");
    }

    function upload()
    {
        return view("upload.index");
    }

    // ═══════════════════════════════════════════
    //  TABLEAU DE BORD UTILISATEUR
    // ═══════════════════════════════════════════
    public function userDashboard()
    {
        $userId = auth()->id();
        $now = now();

        // ─── Stats globales de l'utilisateur ───
        $stats = [
            'total'         => Submission::where('user_id', $userId)->count(),
            'today'         => Submission::where('user_id', $userId)->whereDate('created_at', today())->count(),
            'this_week'     => Submission::where('user_id', $userId)->whereBetween('created_at', [$now->startOfWeek(), $now->endOfWeek()])->count(),
            'this_month'    => Submission::where('user_id', $userId)->whereMonth('created_at', $now->month)->count(),
            'plagiarized'   => Submission::where('user_id', $userId)->plagiarized()->count(),
            'clean'         => Submission::where('user_id', $userId)->clean()->count(),
            'critical'      => Submission::where('user_id', $userId)->critical()->count(),
            'high'          => Submission::where('user_id', $userId)->high()->count(),
            'medium'        => Submission::where('user_id', $userId)->where('overall_level', 'medium')->count(),
            'low'           => Submission::where('user_id', $userId)->low()->count(),
            'processing'    => Submission::where('user_id', $userId)->where('overall_level', 'processing')->count(),
            'error'         => Submission::where('user_id', $userId)->where('overall_level', 'error')->count(),
            'total_files'   => SubmissionZipFile::whereIn('submission_id',
                Submission::where('user_id', $userId)->pluck('id')
            )->count(),
            'avg_score'     => Submission::where('user_id', $userId)->where('overall_level', '!=', 'processing')
                ->where('overall_level', '!=', 'error')
                ->avg('overall_score'),
            'total_size'    => Submission::where('user_id', $userId)->sum('file_size'),
        ];

        // ─── Répartition par niveau ───
        $levelStats = [
            'none'     => $stats['clean'],
            'low'      => $stats['low'],
            'medium'   => $stats['medium'],
            'high'     => $stats['high'],
            'critical' => $stats['critical'],
        ];
        $totalWithLevel = array_sum($levelStats) ?: 1;
        foreach ($levelStats as $level => $count) {
            $levelStats[$level . '_percent'] = round(($count / $totalWithLevel) * 100, 1);
        }

        // ─── Répartition par type de fichier ───
        $typeStats = Submission::where('user_id', $userId)
            ->select('detected_type', \DB::raw('COUNT(*) as count'))
            ->groupBy('detected_type')
            ->orderByDesc('count')
            ->pluck('count', 'detected_type')
            ->toArray();

        // ─── Score moyen par jour (7 derniers jours) ───
        $dailyScores = Submission::where('user_id', $userId)
            ->where('overall_level', '!=', 'processing')
            ->where('overall_level', '!=', 'error')
            ->where('created_at', '>=', $now->copy()->subDays(6))
            ->select(\DB::raw('DATE(created_at) as date'), \DB::raw('AVG(overall_score) as avg_score'), \DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // ─── Top 5 fichiers les plus plagiés ───
        $topPlagiarized = Submission::where('user_id', $userId)
            ->where('plagiarism_detected', true)
            ->orderByDesc('overall_score')
            ->take(5)
            ->get();

        // ─── Activité récente ───
        $recentActivity = Submission::where('user_id', $userId)
            ->latest()
            ->take(10)
            ->get();

        // ─── Analyses en cours ───
        $processingJobs = Submission::where('user_id', $userId)
            ->where('overall_level', 'processing')
            ->latest()
            ->get();

        // ─── Erreurs ───
        $errorJobs = Submission::where('user_id', $userId)
            ->where('overall_level', 'error')
            ->latest()
            ->get();

        return view('user-dashboard.index', compact(
            'stats', 'levelStats', 'typeStats', 'dailyScores',
            'topPlagiarized', 'recentActivity', 'processingJobs', 'errorJobs'
        ));
    }

    // ═══════════════════════════════════════════
    //  ANALYSE FICHIER UNIQUE (ASYNC avec Queue)
    // ═══════════════════════════════════════════
    public function analyse(Request $r)
    {
        $r->validate([
            'submission' => 'required|file|max:20240',
            'file_type'  => 'nullable|string|in:text,code,auto',
        ]);

        $file = $r->file('submission');

        try {
            $health = Http::timeout(5)->get(env('API_KEY_PY').'/api/health');
        } catch (\Exception $e) {
            return back()->with('error', 'API non disponible.');
        }

        $filePath = $file->store('submissions', 'local');

        $submission = Submission::create([
            'user_id'             => auth()->id(),
            'filename'            => $file->getClientOriginalName(),
            'file_path'           => $filePath,
            'original_extension'  => $file->getClientOriginalExtension(),
            'detected_type'       => 'text',
            'file_size'           => $file->getSize(),
            'is_zip'              => false,
            'overall_level'       => 'processing',
            'source'              => 'upload',
            'ip_address'          => $r->ip(),
            'session_id'          => $r->session()->getId(),
        ]);

        ProcessPlagiarismAnalysis::dispatch(
            submissionId:      $submission->id,
            filePath:          $filePath,
            filename:          $file->getClientOriginalName(),
            originalExtension: $file->getClientOriginalExtension(),
            detectedType:      'text',
            fileSize:          $file->getSize(),
            isZip:             false,
            apiEndpoint:       '/api/check',
            userId:            auth()->id(),
            ipAddress:         $r->ip(),
            sessionId:         $r->session()->getId(),
            source:            'upload',
        );

        return redirect()->route('submission.show', $submission->id)
            ->with('info', 'Analyse en cours... Les résultats apparaîtront automatiquement.');
    }

    // ═══════════════════════════════════════════
    //  AJOUT AU CORPUS / ZIP (ASYNC)
    // ═══════════════════════════════════════════
    function create()
    {
        return view("add.index");
    }

    function store(Request $r)
    {
        $r->validate([
            'file' => 'required|file|max:512000',
        ]);

        $file = $r->file('file');
        $filePath = $file->store('submissions', 'local');

        $submission = Submission::create([
            'user_id'             => auth()->id(),
            'filename'            => $file->getClientOriginalName(),
            'file_path'           => $filePath,
            'original_extension'  => strtolower($file->getClientOriginalExtension()),
            'detected_type'       => 'zip',
            'file_size'           => $file->getSize(),
            'is_zip'              => true,
            'overall_level'       => 'processing',
            'source'              => 'corpus_add',
            'ip_address'          => $r->ip(),
            'session_id'          => $r->session()->getId(),
        ]);

        ProcessPlagiarismAnalysis::dispatch(
            submissionId:      $submission->id,
            filePath:          $filePath,
            filename:          $file->getClientOriginalName(),
            originalExtension: strtolower($file->getClientOriginalExtension()),
            detectedType:      'zip',
            fileSize:          $file->getSize(),
            isZip:             true,
            apiEndpoint:       '/api/add',
            userId:            auth()->id(),
            ipAddress:         $r->ip(),
            sessionId:         $r->session()->getId(),
            source:            'corpus_add',
            metadata: [
                'auteur' => auth()->user()->name ?? 'Inconnu',
                'date'   => now()->toDateTimeString(),
            ],
        );

        return redirect()->back()->with('success',
            "Fichier envoyé ! (ID: {$submission->id}) L'analyse tourne en arrière-plan. " .
            "<a href='/history/{$submission->id}'>Voir le statut</a>"
        );
    }

    // ═══════════════════════════════════════════
    //  HISTORIQUE GLOBAL
    // ═══════════════════════════════════════════
    public function history(Request $r)
    {
        $query = Submission::with('zipFiles', 'analyses')->latest();

        if ($r->filled('level')) {
            $query->where('overall_level', $r->level);
        }
        if ($r->filled('type')) {
            $query->where('detected_type', $r->type);
        }
        if ($r->filled('search')) {
            $query->where('filename', 'like', '%' . $r->search . '%');
        }
        if ($r->filled('user_id')) {
            $query->where('user_id', $r->user_id);
        }

        $submissions = $query->paginate(20);
        return view('history.index', compact('submissions'));
    }

    // ═══════════════════════════════════════════
    //  DETAIL SUBMISSION
    // ═══════════════════════════════════════════
    public function showSubmission($id)
    {
        $submission = Submission::with(['zipFiles', 'analyses'])->findOrFail($id);
        return view('submission-detail.index', compact('submission'));
    }

    // ═══════════════════════════════════════════
    //  API : statut (polling JS)
    // ═══════════════════════════════════════════
    public function checkStatus($id)
    {
        $submission = Submission::findOrFail($id);
        return response()->json([
            'id'                 => $submission->id,
            'status'             => $submission->overall_level,
            'overall_score'      => $submission->overall_score,
            'overall_level'      => $submission->overall_level,
            'plagiarism_detected'=> $submission->plagiarism_detected,
            'text_matches_count' => $submission->text_matches_count,
            'image_matches_count'=> $submission->image_matches_count,
            'updated_at'         => $submission->updated_at->toIso8601String(),
        ]);
    }

    // ═══════════════════════════════════════════
    //  SUPPRIMER
    // ═══════════════════════════════════════════
    public function deleteSubmission($id)
    {
        $submission = Submission::findOrFail($id);
        $submission->delete();
        return redirect()->back()->with('success', 'Submission supprimée.');
    }

    // ═══════════════════════════════════════════
    //  AUTH
    // ═══════════════════════════════════════════
    function login()
    {
        return view("accueil.login");
    }

    function register()
    {
        return view("accueil.register");
    }
}
