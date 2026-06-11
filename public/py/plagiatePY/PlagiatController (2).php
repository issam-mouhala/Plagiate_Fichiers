<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\Submission;
use App\Models\SubmissionZipFile;
use App\Models\SubmissionAnalysis;

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
    //  ANALYSE FICHIER UNIQUE + SAUVEGARDE DB
    // ═══════════════════════════════════════════
    public function analyse(Request $r)
    {
        // 1. Valider
        $r->validate([
            'submission' => 'required|file|max:20240',
            'file_type'  => 'nullable|string|in:text,code,auto',
        ]);

        $file = $r->file('submission');
        $fileType = $r->input('file_type', 'auto');

        // 2. Vérifier que l'API est disponible
        try {
            $health = Http::timeout(5)->get(env('API_KEY_PY').'/api/health');
        } catch (\Exception $e) {
            return back()->with('error', 'API non disponible. Vérifiez que le serveur Python tourne sur le port 5000.');
        }

        // 3. Envoyer le fichier à l'API
        try {
            $response = Http::timeout(300)->attach(
                'file',
                $file->get(),
                $file->getClientOriginalName()
            )->post(env('API_KEY_PY').'/api/check', [
                'file_type' => "auto",
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Timeout. Le fichier est peut-être trop volumineux ou l\'API répond lentement.');
        }

        // 4. Erreur API
        if ($response->failed()) {
            return back()->with('error', 'Erreur API : ' . $response->body());
        }

        $data = $response->json();
        $d = $data['data'] ?? [];

        // 5. Extraire les moteurs utilisés depuis les matches
        $enginesUsed = [];
        foreach (($d['content_analysis']['text_matches'] ?? []) as $match) {
            foreach (($match['engines'] ?? []) as $name => $info) {
                $enginesUsed[$name] = true;
            }
        }

        // ─── SAUVEGARDE EN BASE DE DONNÉES ───
        $filename = $d['filename'] ?? $file->getClientOriginalName();
        $detectedType = $d['detected_type'] ?? 'text';
        $overallScore = $d['overall_score'] ?? 0;
        $overallLevel = $d['overall_level'] ?? 'none';
        $plagiarismDetected = $d['plagiarism_detected'] ?? false;
        $textMatches = $d['content_analysis']['text_matches'] ?? [];
        $imageMatches = $d['image_analysis']['image_matches'] ?? [];
        $timing = $d['timing'] ?? [];

        // Sauvegarder le fichier uploadé
        $filePath = $file->store('submissions', 'local');

        // Créer la submission en base
        $submission = Submission::create([
            'user_id'             => auth()->id(),
            'filename'            => $filename,
            'file_path'           => $filePath,
            'original_extension'  => $file->getClientOriginalExtension(),
            'detected_type'       => $detectedType,
            'file_size'           => $file->getSize(),
            'is_zip'              => false,
            'extracted_text'      => $d['extracted_text'] ?? null,
            'content_length'      => $d['content_length'] ?? 0,
            'images_extracted'    => $d['images_extracted'] ?? 0,
            'pages'               => $d['extraction']['pages'] ?? null,
            'overall_score'       => $overallScore,
            'overall_level'       => $overallLevel,
            'plagiarism_detected' => $plagiarismDetected,
            'num_comparisons'     => $d['num_comparisons'] ?? 0,
            'matches_count'       => count($textMatches) + count($imageMatches),
            'text_matches_count'  => count($textMatches),
            'image_matches_count' => count($imageMatches),
            'cross_matches_count' => 0,
            'engines_used'        => array_keys($enginesUsed),
            'content_analysis'    => $d['content_analysis'] ?? null,
            'text_matches'        => $textMatches,
            'image_matches'       => $imageMatches,
            'extraction'          => $d['extraction'] ?? null,
            'timing'              => $timing,
            'raw_response'        => $data,
            'source'              => 'upload',
            'ip_address'          => $r->ip(),
            'session_id'          => $r->session()->getId(),
        ]);

        // Sauvegarder le log d'analyse
        SubmissionAnalysis::create([
            'submission_id'       => $submission->id,
            'analysis_type'       => 'single',
            'overall_score'       => $overallScore,
            'overall_level'       => $overallLevel,
            'plagiarism_detected' => $plagiarismDetected,
            'duration_seconds'    => $timing['total'] ?? $timing['total_seconds'] ?? 0,
            'api_response'        => $data,
        ]);
        // ─── FIN SAUVEGARDE DB ───

        // 6. Envoyer à la vue
        return view("analyse.index", [
            // Submission (pour lien vers détail)
            'submission_id'    => $submission->id,

            // Fichier
            'filename'         => $filename,
            'file_type'        => $detectedType,
            'detected_format'  => $detectedType,
            'extraction'       => $d['extraction'] ?? [],
            'content_length'   => $d['content_length'] ?? 0,
            'images_extracted' => $d['images_extracted'] ?? 0,
            'num_comparisons'  => $d['num_comparisons'] ?? 0,

            // Score global
            'plagiarism_detected' => $plagiarismDetected,
            'overall_score'  => $overallScore,
            'overall_level'  => $overallLevel,

            // Analyse texte
            'content_analysis' => $d['content_analysis'] ?? [],
            'text_matches'     => $textMatches,
            'engines_used'     => $enginesUsed,

            // Analyse images
            'image_analysis' => $d['image_analysis'] ?? [],
            'image_matches'  => $imageMatches,

            // Tout le JSON brut pour export
            'raw_response' => $data,
        ]);
    }

    // ═══════════════════════════════════════════
    //  AJOUT AU CORPUS + SAUVEGARDE DB
    // ═══════════════════════════════════════════
    function create()
    {
        return view("add.index");
    }

    function store(Request $r)
    {
        // 1. Valider
        $r->validate([
            'file' => 'required|file|max:512000', // max 500 MB pour ZIP
        ]);

        $file = $r->file('file');

        // 2. Sauvegarder le fichier localement
        $filePath = $file->store('submissions', 'local');

        // 3. Envoyer le fichier à l'API Python (/api/add)
        try {
            $response = Http::timeout(600)->attach(
                'file',
                file_get_contents($file->path()),
                $file->getClientOriginalName()
            )->post(env('API_KEY_PY').'/api/add', [
                'file_type' => 'auto',
                'metadata'  => json_encode([
                    'auteur' => auth()->user()->name ?? 'Inconnu',
                    'date'   => now()->toDateTimeString(),
                ]),
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur connexion API : ' . $e->getMessage());
        }

        // 4. Erreur API
        if ($response->failed()) {
            return back()->with('error', 'Erreur API : ' . $response->body());
        }

        $data = $response->json();
        $d    = $data['data'] ?? [];

        // 5. Extraire les résultats
        $perFileResults = $d['per_file_database_analysis']['results'] ?? [];
        $crossMatches   = $d['cross_file_analysis'] ?? [];
        $imageMatches   = $d['image_analysis']['image_matches'] ?? [];
        $timing         = $d['timing'] ?? [];

        // 6. Sauvegarder en base de données
        $submission = Submission::create([
            'user_id'             => auth()->id(),
            'filename'            => $file->getClientOriginalName(),
            'file_path'           => $filePath,
            'original_extension'  => strtolower($file->getClientOriginalExtension()),
            'detected_type'       => 'zip',
            'file_size'           => $file->getSize(),
            'is_zip'              => true,
            'content_length'      => 0,
            'images_extracted'    => $d['image_analysis']['images_checked'] ?? 0,
            'overall_score'       => $d['overall_score'] ?? 0,
            'overall_level'       => $d['overall_level'] ?? 'none',
            'plagiarism_detected' => $d['plagiarism_detected'] ?? false,
            'num_comparisons'     => $d['num_comparisons'] ?? 0,
            'matches_count'       => count($crossMatches),
            'text_matches_count'  => count($crossMatches),
            'image_matches_count' => count($imageMatches),
            'cross_matches_count' => count($crossMatches),
            'engines_used'        => $d['engines_used'] ?? [],
            'cross_matches'       => $crossMatches,
            'image_matches'       => $imageMatches,
            'per_file_results'    => $perFileResults,
            'zip_info'            => $d['files_extracted'] ?? null,
            'timing'              => $timing,
            'raw_response'        => $data,
            'source'              => 'corpus_add',
            'ip_address'          => $r->ip(),
            'session_id'          => $r->session()->getId(),
        ]);

        // 7. Sauvegarder les fichiers internes du ZIP
        foreach ($perFileResults as $fileResult) {
            SubmissionZipFile::create([
                'submission_id' => $submission->id,
                'filename'      => $fileResult['filename'] ?? '',
                'extension'     => pathinfo($fileResult['filename'] ?? '', PATHINFO_EXTENSION),
                'file_type'     => $fileResult['file_type'] ?? 'text',
                'content_length'=> $fileResult['content_length'] ?? 0,
                'max_score'     => $fileResult['max_score'] ?? 0,
                'max_level'     => $fileResult['max_level'] ?? 'none',
                'best_match'    => $fileResult['best_match'] ?? null,
                'matches'       => $fileResult['matches'] ?? [],
            ]);
        }

        // 8. Sauvegarder le log d'analyse
        SubmissionAnalysis::create([
            'submission_id'       => $submission->id,
            'analysis_type'       => 'zip',
            'overall_score'       => $submission->overall_score,
            'overall_level'       => $submission->overall_level,
            'plagiarism_detected' => $submission->plagiarism_detected,
            'duration_seconds'    => $timing['total'] ?? $timing['total_seconds'] ?? 0,
            'api_response'        => $data,
        ]);

        // 9. Rediriger avec message de succès
        return redirect()->back()->with('success',
            "Fichier ajouté au corpus avec succès ! (ID: {$submission->id}) " .
            "| Score: " . round($submission->overall_score * 100, 1) . "%" .
            " | Niveau: " . ucfirst($submission->overall_level) .
            " | " . count($perFileResults) . " fichiers analysés"
        );
    }

    // ═══════════════════════════════════════════
    //  HISTORIQUE DES SUBMISSIONS
    // ═══════════════════════════════════════════
    public function history(Request $r)
    {
        $query = Submission::with('zipFiles', 'analyses')->latest();

        // Filtres
        if ($r->filled('level')) {
            $query->where('overall_level', $r->level);
        }
        if ($r->filled('type')) {
            $query->where('detected_type', $r->type);
        }
        if ($r->filled('search')) {
            $query->where('filename', 'like', '%' . $r->search . '%');
        }
        if ($r->filled('date_from')) {
            $query->whereDate('created_at', '>=', $r->date_from);
        }
        if ($r->filled('date_to')) {
            $query->whereDate('created_at', '<=', $r->date_to);
        }

        $submissions = $query->paginate(20);

        return view('history.index', compact('submissions'));
    }

    // ═══════════════════════════════════════════
    //  DETAIL D'UNE SUBMISSION
    // ═══════════════════════════════════════════
    public function showSubmission($id)
    {
        $submission = Submission::with(['zipFiles', 'analyses'])->findOrFail($id);
        return view('submission-detail.index', compact('submission'));
    }

    // ═══════════════════════════════════════════
    //  SUPPRIMER UNE SUBMISSION
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
