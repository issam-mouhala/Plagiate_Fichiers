<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\Submission;
use App\Models\SubmissionZipFile;
use App\Models\SubmissionAnalysis;

class PlagiatController extends Controller
{
    // =============================================
    //  DASHBOARD — Unified upload + results page
    // =============================================

    /**
     * Display the unified dashboard (upload page).
     * This is the main entry point — "/" route.
     */
    public function dashboard()
    {
        // Stats pour le dashboard
        $stats = Submission::getLevelStats();
        $recentSubmissions = Submission::latestSubmissions(5);

        return view('dashboard', [
            'stats'               => $stats,
            'recent_submissions'  => $recentSubmissions,
        ]);
    }

    /**
     * Handle single-file analysis from the dashboard.
     * POST /analyse
     */
    public function analyse(Request $r)
    {
        // 1. Validate
        $r->validate([
            'submission' => 'required|file|max:10240',
        ]);

        $file = $r->file('submission');

        // 2. Check API availability
        try {
            Http::timeout(5)->get('http://localhost:5000/api/health');
        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', 'API non disponible. Vérifiez que le serveur Python tourne sur le port 5000.');
        }

        // 3. Send file to API
        try {
            $response = Http::timeout(120)->attach(
                'file',
                fopen($file->getRealPath(), 'r'),
                $file->getClientOriginalName()
            )->post('http://localhost:5000/api/check', [
                'file_type' => 'auto',
            ]);
        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', 'Timeout. Le fichier est trop volumineux ou l\'API répond lentement.');
        }

        // 4. Check API error
        if ($response->failed()) {
            return redirect()->route('dashboard')->with('error', 'Erreur API : ' . $response->body());
        }

        $data = $response->json();
        $d    = $data['data'] ?? [];

        // 5. Extract engines used from matches
        $enginesUsed = [];
        foreach (($d['content_analysis']['text_matches'] ?? []) as $match) {
            foreach (($match['engines'] ?? []) as $name => $info) {
                $enginesUsed[$name] = true;
            }
        }
        $enginesUsed = array_keys($enginesUsed);

        // 6. SAUVEGARDER DANS LA BASE DE DONNÉES
        $submission = Submission::create([
            'user_id'             => auth()->id(),
            'filename'            => $file->getClientOriginalName(),
            'original_extension'  => $file->getClientOriginalExtension(),
            'detected_type'       => $d['detected_type'] ?? 'text',
            'file_size'           => $file->getSize(),
            'is_zip'              => false,
            'content_length'      => $d['content_length'] ?? 0,
            'images_extracted'    => $d['images_extracted'] ?? 0,
            'pages'               => $d['extraction']['pages'] ?? null,
            'overall_score'       => $d['overall_score'] ?? 0,
            'overall_level'       => $d['overall_level'] ?? 'none',
            'plagiarism_detected' => $d['plagiarism_detected'] ?? false,
            'num_comparisons'     => $d['num_comparisons'] ?? 0,
            'matches_count'       => count($d['content_analysis']['text_matches'] ?? []),
            'text_matches_count'  => count($d['content_analysis']['text_matches'] ?? []),
            'image_matches_count' => count($d['image_analysis']['image_matches'] ?? []),
            'cross_matches_count' => 0,
            'engines_used'        => $enginesUsed,
            'content_analysis'    => $d['content_analysis'] ?? null,
            'text_matches'        => $d['content_analysis']['text_matches'] ?? [],
            'image_matches'       => $d['image_analysis']['image_matches'] ?? [],
            'extraction'          => $d['extraction'] ?? null,
            'timing'              => $d['timing'] ?? null,
            'raw_response'        => $data,
            'source'              => 'upload',
            'ip_address'          => $r->ip(),
            'session_id'          => $r->session()->getId(),
        ]);

        // 7. Return to dashboard view with result_mode='file'
        return view('dashboard', [
            'result_mode'          => 'file',
            'submission_id'        => $submission->id,

            // File info
            'filename'             => $d['filename'] ?? $file->getClientOriginalName(),
            'file_type'            => $d['detected_type'] ?? 'text',
            'extraction'           => $d['extraction'] ?? [],
            'content_length'       => $d['content_length'] ?? 0,
            'images_extracted'     => $d['images_extracted'] ?? 0,
            'num_comparisons'      => $d['num_comparisons'] ?? 0,

            // Global score
            'plagiarism_detected'  => $d['plagiarism_detected'] ?? false,
            'overall_score'        => $d['overall_score'] ?? 0,
            'overall_level'        => $d['overall_level'] ?? 'none',

            // Text analysis
            'content_analysis'     => $d['content_analysis'] ?? [],
            'text_matches'         => $d['content_analysis']['text_matches'] ?? [],
            'engines_used'         => $enginesUsed,

            // Image analysis
            'image_analysis'       => $d['image_analysis'] ?? [],
            'image_matches'        => $d['image_analysis']['image_matches'] ?? [],

            // Raw JSON
            'raw_response'         => $data,
        ]);
    }

    /**
     * Handle ZIP analysis from the dashboard.
     * POST /analyse-zip
     */
    public function analyseZip(Request $r)
    {
        // 1. Validate
        $r->validate([
            'submission' => 'required|file|mimes:zip|max:51200',
        ]);

        $file = $r->file('submission');

        // 2. Check API availability
        try {
            Http::timeout(5)->get('http://localhost:5000/api/health');
        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', 'API non disponible. Vérifiez que le serveur Python tourne sur le port 5000.');
        }

        // 3. Send ZIP to API check-zip
        try {
            $response = Http::timeout(300)->attach(
                'file',
                fopen($file->getRealPath(), 'r'),
                $file->getClientOriginalName()
            )->post('http://localhost:5000/api/check-zip', [
                'cross_compare'   => true,
                'add_to_database' => false,
                'use_semantic'    => true,
                'use_winnowing'   => true,
                'use_ast'         => true,
                'use_lcs'         => true,
            ]);
        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', 'Timeout. Le ZIP est trop volumineux ou l\'API répond lentement.');
        }

        // 4. Check API error
        if ($response->failed()) {
            return redirect()->route('dashboard')->with('error', 'Erreur API : ' . $response->body());
        }

        $data = $response->json();
        $d    = $data['data'] ?? [];

        $perFileResults = $d['per_file_database_analysis']['results'] ?? [];
        $crossMatches   = $d['cross_file_analysis'] ?? [];
        $imageMatches   = $d['image_analysis']['image_matches'] ?? [];

        // 5. SAUVEGARDER DANS LA BASE DE DONNÉES
        $submission = Submission::create([
            'user_id'             => auth()->id(),
            'filename'            => $file->getClientOriginalName(),
            'original_extension'  => 'zip',
            'detected_type'       => 'zip',
            'file_size'           => $file->getSize(),
            'is_zip'              => true,
            'content_length'      => 0,
            'images_extracted'    => $d['image_analysis']['images_checked'] ?? 0,
            'overall_score'       => $d['overall_score'] ?? 0,
            'overall_level'       => $d['overall_level'] ?? 'none',
            'plagiarism_detected' => $d['plagiarism_detected'] ?? false,
            'num_comparisons'     => 0,
            'matches_count'       => count($crossMatches),
            'text_matches_count'  => count($crossMatches),
            'image_matches_count' => count($imageMatches),
            'cross_matches_count' => count($crossMatches),
            'engines_used'        => $d['engines_used'] ?? [],
            'cross_matches'       => $crossMatches,
            'image_matches'       => $imageMatches,
            'per_file_results'    => $perFileResults,
            'zip_info'            => $d['files_extracted'] ?? null,
            'timing'              => $d['timing'] ?? null,
            'raw_response'        => $data,
            'source'              => 'upload',
            'ip_address'          => $r->ip(),
            'session_id'          => $r->session()->getId(),
        ]);

        // Sauvegarder les fichiers internes du ZIP
        foreach ($perFileResults as $fileResult) {
            SubmissionZipFile::create([
                'submission_id'   => $submission->id,
                'filename'         => $fileResult['filename'] ?? '',
                'extension'        => pathinfo($fileResult['filename'] ?? '', PATHINFO_EXTENSION),
                'file_type'        => $fileResult['file_type'] ?? 'text',
                'content_length'   => $fileResult['content_length'] ?? 0,
                'max_score'        => $fileResult['max_score'] ?? 0,
                'max_level'        => $fileResult['max_level'] ?? 'none',
                'best_match'       => $fileResult['best_match'] ?? null,
                'matches'          => $fileResult['matches'] ?? [],
            ]);
        }

        // 6. Return to dashboard view with result_mode='zip'
        return view('dashboard', [
            'result_mode'          => 'zip',
            'submission_id'        => $submission->id,

            // Archive
            'zip_filename'         => $d['filename'] ?? $file->getClientOriginalName(),
            'zip_info'             => $d['files_extracted'] ?? [],
            'files_extracted'      => $d['files_extracted'] ?? [],

            // Global score
            'plagiarism_detected'  => $d['plagiarism_detected'] ?? false,
            'overall_score'        => $d['overall_score'] ?? 0,
            'overall_level'        => $d['overall_level'] ?? 'none',

            // Cross analysis
            'cross_analysis'       => $d['cross_file_analysis'] ?? [],
            'cross_matches'        => $d['cross_file_analysis']['matches'] ?? [],

            // Per-file base
            'per_file_analysis'    => $d['per_file_database_analysis'] ?? [],
            'per_file_results'     => $d['per_file_database_analysis']['results'] ?? [],

            // Images
            'image_analysis'       => $d['image_analysis'] ?? [],
            'image_matches'        => $d['image_analysis']['image_matches'] ?? [],

            // Raw JSON
            'raw_response'         => $data,
        ]);
    }

    // =============================================
    //  HISTORY — Liste des soumissions
    // =============================================

    /**
     * Affiche l'historique de toutes les soumissions.
     * GET /history
     */
    public function history(Request $r)
    {
        $query = Submission::with('user');

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

        $submissions = $query->latest()->paginate(20);
        $stats = Submission::getLevelStats();

        return view('submissions.history', [
            'submissions' => $submissions,
            'stats'       => $stats,
        ]);
    }

    /**
     * Affiche le détail d'une soumission.
     * GET /history/{id}
     */
    public function showSubmission($id)
    {
        $submission = Submission::with(['zipFiles', 'analyses', 'user'])->findOrFail($id);

        return view('submissions.show', [
            'submission' => $submission,
        ]);
    }

    /**
     * Supprime une soumission.
     * DELETE /history/{id}
     */
    public function deleteSubmission($id)
    {
        $submission = Submission::findOrFail($id);
        $submission->delete();

        return redirect()->route('submissions.history')
            ->with('success', 'Soumission supprimée avec succès.');
    }

    /**
     * API endpoint — Stats en JSON (pour dashboard AJAX).
     * GET /api/submissions/stats
     */
    public function apiStats()
    {
        return response()->json(Submission::getLevelStats());
    }

    // =============================================
    //  SERVE IMAGE — Proxy to Python API
    // =============================================

    /**
     * Serve an extracted image from uploads/images/ via Python API.
     * GET /serve-image/{filename}
     */
    public function serveImage($filename)
    {
        try {
            $response = Http::timeout(10)->get('http://localhost:5000/api/serve-image/' . urlencode($filename));

            if ($response->status() === 404) {
                abort(404, 'Image non trouvée');
            }

            if ($response->failed()) {
                abort(500, 'Erreur serveur image');
            }

            // Determine content type
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $mimeTypes = [
                'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
                'gif' => 'image/gif', 'bmp' => 'image/bmp', 'webp' => 'image/webp',
            ];
            $contentType = $mimeTypes[$ext] ?? 'image/png';

            return response($response->body())->header('Content-Type', $contentType)
                ->header('Cache-Control', 'public, max-age=3600');
        } catch (\Exception $e) {
            abort(503, 'API non disponible');
        }
    }

    // =============================================
    //  LEGACY — Old separate pages (kept for BC)
    // =============================================

    function index()
    {
        return view("accueil.index");
    }

    function upload()
    {
        return view("upload.index");
    }

    public function uploadZip()
    {
        return view("upload_zip.index");
    }
}
