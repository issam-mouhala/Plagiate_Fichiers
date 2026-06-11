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
    //  ANALYSE FICHIER UNIQUE (ASYNC avec Queue)
    // ═══════════════════════════════════════════
    public function analyse(Request $r)
    {
        // 1. Valider
        $r->validate([
            'submission' => 'required|file|max:20240',
            'file_type'  => 'nullable|string|in:text,code,auto',
        ]);

        $file = $r->file('submission');

        // 2. Vérifier que l'API est disponible (rapide, 5s)
        try {
            $health = Http::timeout(5)->get(env('API_KEY_PY').'/api/health');
        } catch (\Exception $e) {
            return back()->with('error', 'API non disponible. Vérifiez que le serveur Python tourne.');
        }

        // 3. Sauvegarder le fichier localement
        $filePath = $file->store('submissions', 'local');

        // 4. Créer la submission en base (statut = "processing")
        $submission = Submission::create([
            'user_id'             => auth()->id(),
            'filename'            => $file->getClientOriginalName(),
            'file_path'           => $filePath,
            'original_extension'  => $file->getClientOriginalExtension(),
            'detected_type'       => 'text',
            'file_size'           => $file->getSize(),
            'is_zip'              => false,
            'overall_level'       => 'processing',  // ← en cours
            'source'              => 'upload',
            'ip_address'          => $r->ip(),
            'session_id'          => $r->session()->getId(),
        ]);

        // 5. Lancer le job en arrière-plan (NON BLOQUANT)
        ProcessPlagiarismAnalysis::dispatch(
            submissionId:     $submission->id,
            filePath:         $filePath,
            filename:         $file->getClientOriginalName(),
            originalExtension: $file->getClientOriginalExtension(),
            detectedType:     'text',
            fileSize:         $file->getSize(),
            isZip:            false,
            apiEndpoint:      '/api/check',
            userId:           auth()->id(),
            ipAddress:        $r->ip(),
            sessionId:        $r->session()->getId(),
            source:           'upload',
        );

        // 6. Réponse IMMÉDIATE - pas de blocage !
        return redirect()->route('submission.show', $submission->id)
            ->with('info', 'Analyse en cours... Les résultats apparaîtront automatiquement.');
    }

    // ═══════════════════════════════════════════
    //  AJOUT AU CORPUS / ZIP (ASYNC avec Queue)
    // ═══════════════════════════════════════════
    function create()
    {
        return view("add.index");
    }

    function store(Request $r)
    {
        // 1. Valider
        $r->validate([
            'file' => 'required|file|max:512000',
        ]);

        $file = $r->file('file');

        // 2. Sauvegarder le fichier localement
        $filePath = $file->store('submissions', 'local');

        // 3. Créer la submission en base (statut = "processing")
        $submission = Submission::create([
            'user_id'             => auth()->id(),
            'filename'            => $file->getClientOriginalName(),
            'file_path'           => $filePath,
            'original_extension'  => strtolower($file->getClientOriginalExtension()),
            'detected_type'       => 'zip',
            'file_size'           => $file->getSize(),
            'is_zip'              => true,
            'overall_level'       => 'processing',  // ← en cours
            'source'              => 'corpus_add',
            'ip_address'          => $r->ip(),
            'session_id'          => $r->session()->getId(),
        ]);

        // 4. Lancer le job en arrière-plan (NON BLOQUANT)
        ProcessPlagiarismAnalysis::dispatch(
            submissionId:     $submission->id,
            filePath:         $filePath,
            filename:         $file->getClientOriginalName(),
            originalExtension: strtolower($file->getClientOriginalExtension()),
            detectedType:     'zip',
            fileSize:         $file->getSize(),
            isZip:            true,
            apiEndpoint:      '/api/add',
            userId:           auth()->id(),
            ipAddress:        $r->ip(),
            sessionId:        $r->session()->getId(),
            source:           'corpus_add',
            metadata:         [
                'auteur' => auth()->user()->name ?? 'Inconnu',
                'date'   => now()->toDateTimeString(),
            ],
        );

        // 5. Réponse IMMÉDIATE
        return redirect()->back()->with('success',
            "Fichier envoyé avec succès ! (ID: {$submission->id}) " .
            "L'analyse tourne en arrière-plan. " .
            "<a href='/history/{$submission->id}'>Voir le statut</a>"
        );
    }

    // ═══════════════════════════════════════════
    //  HISTORIQUE
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

        $submissions = $query->paginate(20);
        return view('history.index', compact('submissions'));
    }

    // ═══════════════════════════════════════════
    //  DETAIL SUBMISSION (avec auto-refresh si en cours)
    // ═══════════════════════════════════════════
    public function showSubmission($id)
    {
        $submission = Submission::with(['zipFiles', 'analyses'])->findOrFail($id);
        return view('submission-detail.index', compact('submission'));
    }

    // ═══════════════════════════════════════════
    //  API : vérifier le statut d'une submission (pour polling JS)
    // ═══════════════════════════════════════════
    public function checkStatus($id)
    {
        $submission = Submission::findOrFail($id);

        return response()->json([
            'id'                => $submission->id,
            'status'            => $submission->overall_level, // 'processing', 'none', 'low', 'medium', 'high', 'critical', 'error'
            'overall_score'     => $submission->overall_score,
            'overall_level'     => $submission->overall_level,
            'plagiarism_detected'=> $submission->plagiarism_detected,
            'text_matches_count' => $submission->text_matches_count,
            'image_matches_count'=> $submission->image_matches_count,
            'updated_at'        => $submission->updated_at->toIso8601String(),
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
