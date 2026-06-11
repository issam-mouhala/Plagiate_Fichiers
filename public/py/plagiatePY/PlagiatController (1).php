<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\SubmissionZipFile;
use App\Models\SubmissionAnalysis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PlagiatController extends Controller
{
    // ─── URL de l'API Python v4 ───
    // Change cette URL apr�s le d�ploiement sur Hugging Face
    private string $apiUrl = 'http://localhost:5000';

    // ─── Timeout pour les requ�tes API (en secondes) ───
    private int $apiTimeout = 300; // 5 min pour les gros fichiers ZIP

    // ═══════════════════════════════════════════
    //  DASHBOARD
    // ═══════════════════════════════════════════
    public function dashboard()
    {
        $stats = [
            'total'      => Submission::count(),
            'today'      => Submission::whereDate('created_at', today())->count(),
            'plagiarized' => Submission::plagiarized()->count(),
            'clean'       => Submission::clean()->count(),
            'critical'    => Submission::critical()->count(),
            'high'        => Submission::high()->count(),
            'recent'      => Submission::recent()->take(10)->get(),
        ];

        $levelStats = Submission::getLevelStats();

        return view('dashboard', compact('stats', 'levelStats'));
    }

    // ═══════════════════════════════════════════
    //  ANALYSE FICHIER UNIQUE
    // ═══════════════════════════════════════════
    public function analyse(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200', // max 50 MB
        ]);

        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $fileSize = $file->getSize();

        // ─── D�tecter le type de fichier ───
        $ftype = $this->detectFileType($extension);

        // ─── Appeler l'API Python v4 : /api/check ───
        $apiResponse = $this->callApi('/api/check', $file);

        if (!$apiResponse || !isset($apiResponse['success'])) {
            return redirect()->back()->with('error',
                'Erreur de communication avec l\'API. V�rifiez que le serveur Python est en marche.');
        }

        // ─── Sauvegarder le fichier localement ───
        $filePath = $file->store('submissions', 'local');

        // ─── Mapper la r�ponse API vers le mod�le Submission ───
        $submission = $this->mapApiResponseToSubmission($apiResponse, [
            'filename'          => $filename,
            'file_path'         => $filePath,
            'original_extension'=> $extension,
            'detected_type'     => $ftype,
            'file_size'         => $fileSize,
            'is_zip'            => false,
            'source'            => 'upload',
            'ip_address'        => $request->ip(),
            'session_id'        => $request->session()->getId(),
        ]);

        $submission->save();

        // ─── Sauvegarder le log d'analyse ───
        $this->saveAnalysisLog($submission, 'single', $apiResponse);

        return redirect()->back()->with('success',
            "Analyse termin�e : {$filename} | Score : " .
            round($submission->overall_score * 100, 1) . "% | " .
            ucfirst($submission->overall_level));
    }

    // ═══════════════════════════════════════════
    //  ANALYSE FICHIER ZIP
    // ═══════════════════════════════════════════
    public function analyseZip(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:zip|max:512000', // max 500 MB
        ]);

        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $fileSize = $file->getSize();

        // ─── Appeler l'API Python v4 : /api/check-zip ───
        $apiResponse = $this->callApi('/api/check-zip', $file);

        if (!$apiResponse || !isset($apiResponse['success'])) {
            return redirect()->back()->with('error',
                'Erreur de communication avec l\'API.');
        }

        // ─── Sauvegarder le fichier ZIP localement ───
        $filePath = $file->store('submissions', 'local');

        // ─── Mapper la r�ponse API ───
        $submission = $this->mapApiResponseToSubmission($apiResponse, [
            'filename'          => $filename,
            'file_path'         => $filePath,
            'original_extension'=> 'zip',
            'detected_type'     => 'zip',
            'file_size'         => $fileSize,
            'is_zip'            => true,
            'source'            => 'upload',
            'ip_address'        => $request->ip(),
            'session_id'        => $request->session()->getId(),
        ]);

        $submission->save();

        // ─── Sauvegarder les r�sultats par fichier (ZIP) ───
        $this->saveZipFileResults($submission, $apiResponse);

        // ─── Sauvegarder le log d'analyse ───
        $this->saveAnalysisLog($submission, 'zip', $apiResponse);

        $fileCount = $apiResponse['zip_info']['total_files'] ?? 0;

        return redirect()->back()->with('success',
            "ZIP analys� : {$filename} ({$fileCount} fichiers) | Score global : " .
            round($submission->overall_score * 100, 1) . "%");
    }

    // ═══════════════════════════════════════════
    //  HISTORIQUE
    // ═══════════════════════════════════════════
    public function history(Request $request)
    {
        $query = Submission::with('zipFiles')->latest();

        // Filtres
        if ($request->filled('level')) {
            $query->where('overall_level', $request->level);
        }
        if ($request->filled('type')) {
            $query->byType($request->type);
        }
        if ($request->filled('search')) {
            $query->where('filename', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $submissions = $query->paginate(20);

        return view('submissions.history', compact('submissions'));
    }

    // ═══════════════════════════════════════════
    //  DETAIL D'UNE SUBMISSION
    // ═══════════════════════════════════════════
    public function showSubmission($id)
    {
        $submission = Submission::with(['zipFiles', 'analyses'])->findOrFail($id);
        return view('submissions.show', compact('submission'));
    }

    // ═══════════════════════════════════════════
    //  SUPPRIMER UNE SUBMISSION
    // ═══════════════════════════════════════════
    public function deleteSubmission($id)
    {
        $submission = Submission::findOrFail($id);
        $submission->delete(); // le boot() supprime aussi le fichier

        return redirect()->route('history')->with('success', 'Submission supprim�e.');
    }

    // ═══════════════════════════════════════════
    //  STATS JSON (pour AJAX)
    // ═══════════════════════════════════════════
    public function apiStats()
    {
        return response()->json([
            'total'       => Submission::count(),
            'today'       => Submission::whereDate('created_at', today())->count(),
            'plagiarized'  => Submission::plagiarized()->count(),
            'clean'        => Submission::clean()->count(),
            'level_stats'  => Submission::getLevelStats(),
        ]);
    }

    // ═══════════════════════════════════════════
    //  HEALTH PROXY
    // ═══════════════════════════════════════════
    public function healthProxy()
    {
        try {
            $response = Http::timeout(10)->get("{$this->apiUrl}/api/health");
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['status' => 'offline', 'error' => $e->getMessage()], 503);
        }
    }

    // ═══════════════════════════════════════════
    //  SERVE IMAGE PROXY
    // ═══════════════════════════════════════════
    public function serveImage($filename)
    {
        try {
            $response = Http::timeout(30)->get("{$this->apiUrl}/api/serve-image/{$filename}");
            if ($response->successful()) {
                return response($response->body(), 200)
                    ->header('Content-Type', $response->header('Content-Type'));
            }
        } catch (\Exception $e) {
            // Silent fail
        }
        abort(404, 'Image non trouv�e');
    }

    // ═══════════════════════════════════════════
    //  M�THODES PRIV�ES
    // ═══════════════════════════════════════════

    /**
     * Appeler un endpoint de l'API Python v4
     */
    private function callApi(string $endpoint, $file): ?array
    {
        try {
            $response = Http::timeout($this->apiTimeout)
                ->post("{$this->apiUrl}{$endpoint}", [
                    'file' => fopen($file->getRealPath(), 'r'),
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            \Log::error("API Error: {$response->status()}", [
                'endpoint' => $endpoint,
                'body'     => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            \Log::error("API Connection Error: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Mapper la r�ponse API v4 vers le mod�le Submission
     * Compatible avec les deux formats :
     *   - Format 1 : R�ponse simple (id, filename, type)
     *   - Format 2 : R�ponse compl�te (results, score, matches)
     */
    private function mapApiResponseToSubmission(array $apiResponse, array $extra): Submission
    {
        $submission = new Submission();
        $submission->fill($extra);

        // ─── Si l'API retourne des r�sultats d'analyse complets ───
        if (isset($apiResponse['results']) || isset($apiResponse['overall_score'])) {
            $results = $apiResponse['results'] ?? $apiResponse;

            $submission->overall_score     = $results['overall_score'] ?? $apiResponse['overall_score'] ?? 0;
            $submission->overall_level     = $results['overall_level'] ?? $apiResponse['overall_level'] ?? 'none';
            $submission->plagiarism_detected = ($submission->overall_score >= 0.3);

            $submission->num_comparisons    = $results['num_comparisons'] ?? $apiResponse['num_comparisons'] ?? 0;
            $submission->matches_count      = $results['matches_count'] ?? $apiResponse['matches_count'] ?? 0;
            $submission->text_matches_count = $results['text_matches_count'] ?? $apiResponse['text_matches_count'] ?? 0;
            $submission->image_matches_count= $results['image_matches_count'] ?? $apiResponse['image_matches_count'] ?? 0;
            $submission->cross_matches_count= $results['cross_matches_count'] ?? $apiResponse['cross_matches_count'] ?? 0;

            // Contenu extrait
            $submission->extracted_text     = $results['extracted_text'] ?? $apiResponse['extracted_text'] ?? null;
            $submission->content_length     = strlen($submission->extracted_text ?? '');
            $submission->images_extracted    = $results['images_extracted'] ?? $apiResponse['images_extracted'] ?? 0;
            $submission->pages              = $results['pages'] ?? $apiResponse['pages'] ?? null;

            // JSON d�taill�s
            $submission->engines_used       = $results['engines_used'] ?? $apiResponse['engines_used'] ?? null;
            $submission->content_analysis   = $results['content_analysis'] ?? $apiResponse['content_analysis'] ?? null;
            $submission->text_matches       = $results['text_matches'] ?? $apiResponse['text_matches'] ?? null;
            $submission->image_matches      = $results['image_matches'] ?? $apiResponse['image_matches'] ?? null;
            $submission->cross_matches      = $results['cross_matches'] ?? $apiResponse['cross_matches'] ?? null;
            $submission->per_file_results   = $results['per_file_results'] ?? $apiResponse['per_file_results'] ?? null;
            $submission->zip_info           = $results['zip_info'] ?? $apiResponse['zip_info'] ?? null;
            $submission->extraction         = $results['extraction'] ?? $apiResponse['extraction'] ?? null;
            $submission->timing             = $results['timing'] ?? $apiResponse['timing'] ?? null;
            $submission->raw_response        = $apiResponse;

        } else {
            // ─── Format simple : l'API retourne juste les infos de soumission ───
            // Les r�sultats seront remplis plus tard ou resteront vides
            $submission->content_length     = $apiResponse['content_length'] ?? strlen($extra['filename']);
            $submission->images_extracted    = $apiResponse['images_stored'] ?? 0;
            $submission->raw_response        = $apiResponse;
        }

        return $submission;
    }

    /**
     * D�tecter le type de fichier
     */
    private function detectFileType(string $ext): string
    {
        return match($ext) {
            'pdf'  => 'pdf',
            'docx', 'doc' => 'docx',
            'txt'  => 'text',
            'py', 'js', 'java', 'php', 'cpp', 'c', 'cs', 'rb', 'go', 'rs', 'ts' => 'code',
            'png', 'jpg', 'jpeg', 'gif', 'bmp', 'webp', 'svg' => 'image',
            'zip'  => 'zip',
            default => 'text',
        };
    }

    /**
     * Sauvegarder le log d'analyse
     */
    private function saveAnalysisLog(Submission $submission, string $type, array $apiResponse): void
    {
        $analysis = new SubmissionAnalysis();
        $analysis->submission_id        = $submission->id;
        $analysis->analysis_type        = $type;
        $analysis->overall_score        = $submission->overall_score;
        $analysis->overall_level        = $submission->overall_level;
        $analysis->plagiarism_detected  = $submission->plagiarism_detected;

        // Calculer la dur�e depuis le timing API
        $timing = $apiResponse['timing'] ?? [];
        $analysis->duration_seconds = $timing['total_seconds'] ??
            ($timing['total'] ?? 0);

        $analysis->api_response = $apiResponse;
        $analysis->save();
    }

    /**
     * Sauvegarder les r�sultats par fichier ZIP
     */
    private function saveZipFileResults(Submission $submission, array $apiResponse): void
    {
        $perFile = $apiResponse['per_file_results'] ?? [];
        $zipInfo = $apiResponse['zip_info'] ?? [];

        foreach ($perFile as $fileResult) {
            $zipFile = new SubmissionZipFile();
            $zipFile->submission_id  = $submission->id;
            $zipFile->filename       = $fileResult['filename'] ?? 'unknown';
            $zipFile->extension      = pathinfo($zipFile->filename, PATHINFO_EXTENSION);
            $zipFile->file_type      = $this->detectFileType($zipFile->extension);
            $zipFile->content_length = $fileResult['content_length'] ?? 0;
            $zipFile->max_score      = $fileResult['max_score'] ?? $fileResult['overall_score'] ?? 0;
            $zipFile->max_level      = $fileResult['max_level'] ?? $fileResult['overall_level'] ?? 'none';
            $zipFile->best_match     = $fileResult['best_match'] ?? null;
            $zipFile->matches        = $fileResult['matches'] ?? $fileResult['text_matches'] ?? null;
            $zipFile->save();
        }
    }
}
