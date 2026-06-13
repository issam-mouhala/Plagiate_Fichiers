<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\Submission;
use App\Models\SubmissionZipFile;
use App\Models\SubmissionAnalysis;
use App\Jobs\ProcessPlagiarismAnalysis;
use Illuminate\Support\Facades\DB;

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

    // ═══════════════════════════════════════════════════════
    //  ANALYSE FICHIER UNIQUE (ASYNC avec Queue)
    // ═══════════════════════════════════════════════════════
    public function analyse(Request $r)
    {
        $r->validate([
            'submission' => 'required|file|max:20240',
            'file_type'  => 'nullable|string|in:text,code,auto',
        ]);

        $file = $r->file('submission');

        try {
            Http::timeout(5)->get(env('API_KEY_PY').'/api/health');
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

        return redirect()->route('analyse.result', $submission->id)
            ->with('info', 'Analyse en cours... Les résultats apparaîtront automatiquement.');
    }

    // ═══════════════════════════════════════════════════════
    //  RÉSULTAT ANALYSE — Rapport moderne
    //  Vue : resources/views/analyse/index.blade.php
    //  Fournit les 14 variables : filename, file_type, content_length,
    //      num_comparisons, engines_used, extraction, images_extracted,
    //      image_analysis, image_matches, overall_score,
    //      plagiarism_detected, content_analysis, text_matches, raw_response
    // ═══════════════════════════════════════════════════════
    public function analyseResult($id)
    {
        $submission = Submission::with(['zipFiles', 'analyses'])->findOrFail($id);

        // ─── Si en cours → page d'attente avec auto-refresh ───
        if ($submission->overall_level === 'processing') {
            return view('analyse.index', [
                'submission'        => $submission,
                'filename'          => $submission->filename,
                'file_type'         => $submission->detected_type ?? 'text',
                'content_length'    => 0,
                'num_comparisons'   => 0,
                'engines_used'      => [],
                'extraction'        => [],
                'images_extracted'  => 0,
                'image_analysis'    => null,
                'image_matches'     => [],
                'overall_score'     => 0,
                'plagiarism_detected' => false,
                'content_analysis'  => null,
                'text_matches'      => [],
                'raw_response'       => null,
            ]);
        }

        // ─── Si erreur ───
        if ($submission->overall_level === 'error') {
            return view('analyse.index', [
                'submission'        => $submission,
                'filename'          => $submission->filename,
                'file_type'         => $submission->detected_type ?? 'text',
                'content_length'    => 0,
                'num_comparisons'   => 0,
                'engines_used'      => [],
                'extraction'        => [],
                'images_extracted'  => 0,
                'image_analysis'    => null,
                'image_matches'     => [],
                'overall_score'     => 0,
                'plagiarism_detected' => false,
                'content_analysis'  => null,
                'text_matches'      => [],
                'raw_response'       => null,
            ]);
        }

        // ─── Résultats prêts → décoder raw_response ───
        $raw = $submission->raw_response;

        if (is_string($raw)) {
            $raw = json_decode($raw, true);
        }

        if (!is_array($raw)) {
            $raw = [];
        }

        // ─── Préparer les 14 variables pour la vue ───

        $filename       = $submission->filename;
        $file_type      = $raw['file_type'] ?? $submission->detected_type ?? 'text';
        $content_length = $raw['content_length'] ?? (isset($raw['content']) ? strlen($raw['content']) : 0);
        $num_comparisons = $raw['num_comparisons'] ?? ($raw['comparisons_performed'] ?? 0);
        $engines_used   = $raw['engines_used'] ?? ($submission->engines_used ?? []);
        $extraction     = $raw['extraction'] ?? [];
        $images_extracted = $raw['images_extracted'] ?? (isset($raw['extraction']['images_extracted']) ? $raw['extraction']['images_extracted'] : 0);
        $image_analysis = $raw['image_analysis'] ?? null;
        $image_matches  = $raw['image_matches'] ?? (isset($raw['image_analysis']['matches']) ? $raw['image_analysis']['matches'] : []);
        $overall_score  = (float) ($submission->overall_score ?? ($raw['overall_score'] ?? 0));
        $plagiarism_detected = (bool) ($submission->plagiarism_detected ?? ($raw['plagiarism_detected'] ?? false));
        $content_analysis = $raw['content_analysis'] ?? null;
        $text_matches   = $raw['text_matches'] ?? (isset($raw['content_analysis']['matches']) ? $raw['content_analysis']['matches'] : []);
        $raw_response   = $raw;

        return view('analyse.index', compact(
            'submission',
            'filename', 'file_type', 'content_length',
            'num_comparisons', 'engines_used', 'extraction',
            'images_extracted', 'image_analysis', 'image_matches',
            'overall_score', 'plagiarism_detected',
            'content_analysis', 'text_matches', 'raw_response'
        ));
    }

    // ═══════════════════════════════════════════════════════
    //  AJOUT AU CORPUS / ZIP (ASYNC)
    // ═══════════════════════════════════════════════════════
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

    // ═══════════════════════════════════════════════════════
    //  HISTORIQUE GLOBAL
    // ═══════════════════════════════════════════════════════
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

    // ═══════════════════════════════════════════════════════
    //  DETAIL SUBMISSION (simple, pour historique)
    // ═══════════════════════════════════════════════════════
    public function showSubmission($id)
    {
        $submission = Submission::with(['zipFiles', 'analyses'])->findOrFail($id);
        return view('submission-detail.index', compact('submission'));
    }

    // ═══════════════════════════════════════════════════════
    //  API : statut (polling JS)
    // ═══════════════════════════════════════════════════════
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

    // ═══════════════════════════════════════════════════════
    //  SUPPRIMER
    // ═══════════════════════════════════════════════════════
    public function deleteSubmission($id)
    {
        $submission = Submission::findOrFail($id);
        $submission->delete();
        return redirect()->back()->with('success', 'Submission supprimée.');
    }

    // ═══════════════════════════════════════════════════════
    //  AUTH
    // ═══════════════════════════════════════════════════════
    function login()
    {
        return view("accueil.login");
    }

    function register()
    {
        return view("accueil.register");
    }
}
