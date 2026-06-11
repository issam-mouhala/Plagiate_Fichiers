<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Submission;
use App\Models\SubmissionZipFile;
use App\Models\SubmissionAnalysis;

class ProcessPlagiarismAnalysis implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // ─── Temps max d'exécution du job (10 minutes) ───
    public int $timeout = 600;

    // ─── Nombre de tentatives en cas d'échec ───
    public int $tries = 2;

    /**
     * Données de la soumission
     */
    public function __construct(
        public int    $submissionId,
        public string  $filePath,       // chemin stockage local
        public string  $filename,
        public string  $originalExtension,
        public string  $detectedType,    // 'zip' ou 'single'
        public int     $fileSize,
        public bool    $isZip,
        public string  $apiEndpoint,    // '/api/check' ou '/api/add'
        public ?int    $userId,
        public string  $ipAddress,
        public string  $sessionId,
        public string  $source,         // 'upload' ou 'corpus_add'
        public array   $metadata = [],
    ) {}

    /**
     * Exécuter l'analyse en arrière-plan
     */
    public function handle(): void
    {
        $apiUrl = env('API_KEY_PY', 'http://localhost:5000');

        // ─── 1. Récupérer la submission ───
        $submission = Submission::find($this->submissionId);
        if (!$submission) {
            \Log::error("Submission {$this->submissionId} introuvable");
            return;
        }

        // ─── 2. Mettre à jour le statut → "processing" ───
        $submission->update([
            'overall_level' => 'processing',
        ]);

        // ─── 3. Lire le fichier depuis le stockage ───
        if (!Storage::disk('local')->exists($this->filePath)) {
            $submission->update([
                'overall_level' => 'error',
            ]);
            \Log::error("Fichier introuvable: {$this->filePath}");
            return;
        }

        $fullPath = Storage::disk('local')->path($this->filePath);

        // ─── 4. Appeler l'API Python ───
        try {
            $response = Http::timeout(600)->attach(
                'file',
                file_get_contents($fullPath),
                $this->filename
            )->post($apiUrl . $this->apiEndpoint, [
                'file_type' => 'auto',
                'metadata'  => json_encode($this->metadata),
            ]);
        } catch (\Exception $e) {
            $submission->update([
                'overall_level' => 'error',
            ]);
            \Log::error("API Error pour submission {$this->submissionId}: " . $e->getMessage());
            return;
        }

        // ─── 5. Vérifier la réponse API ───
        if ($response->failed()) {
            $submission->update([
                'overall_level' => 'error',
            ]);
            \Log::error("API Failed pour submission {$this->submissionId}: " . $response->body());
            return;
        }

        $data = $response->json();
        $d    = $data['data'] ?? [];

        // ─── 6. Extraire les résultats ───
        $textMatches    = $d['content_analysis']['text_matches'] ?? [];
        $imageMatches   = $d['image_analysis']['image_matches'] ?? [];
        $crossMatches   = $d['cross_file_analysis'] ?? [];
        $perFileResults = $d['per_file_database_analysis']['results'] ?? [];
        $enginesUsed    = [];

        // Extraire les moteurs utilisés
        foreach ($textMatches as $match) {
            foreach (($match['engines'] ?? []) as $name => $info) {
                $enginesUsed[$name] = true;
            }
        }

        $overallScore = $d['overall_score'] ?? 0;
        $overallLevel = $d['overall_level'] ?? 'none';
        $timing = $d['timing'] ?? [];

        // ─── 7. Mettre à jour la submission avec les résultats ───
        $submission->update([
            'extracted_text'      => $d['extracted_text'] ?? null,
            'content_length'      => $d['content_length'] ?? 0,
            'images_extracted'    => $d['images_extracted'] ?? ($d['image_analysis']['images_checked'] ?? 0),
            'pages'               => $d['extraction']['pages'] ?? null,
            'overall_score'       => $overallScore,
            'overall_level'       => $overallLevel,
            'plagiarism_detected' => $d['plagiarism_detected'] ?? false,
            'num_comparisons'     => $d['num_comparisons'] ?? 0,
            'matches_count'       => count($textMatches) + count($imageMatches) + count($crossMatches),
            'text_matches_count'  => count($textMatches) + count($crossMatches),
            'image_matches_count' => count($imageMatches),
            'cross_matches_count' => count($crossMatches),
            'engines_used'        => array_keys($enginesUsed),
            'content_analysis'    => $d['content_analysis'] ?? null,
            'text_matches'        => $textMatches,
            'image_matches'       => $imageMatches,
            'cross_matches'       => $crossMatches,
            'per_file_results'    => $perFileResults,
            'zip_info'            => $d['files_extracted'] ?? null,
            'extraction'          => $d['extraction'] ?? null,
            'timing'              => $timing,
            'raw_response'        => $data,
        ]);

        // ─── 8. Sauvegarder les fichiers internes du ZIP ───
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

        // ─── 9. Sauvegarder le log d'analyse ───
        SubmissionAnalysis::create([
            'submission_id'       => $submission->id,
            'analysis_type'       => $this->isZip ? 'zip' : 'single',
            'overall_score'       => $overallScore,
            'overall_level'       => $overallLevel,
            'plagiarism_detected' => $submission->plagiarism_detected,
            'duration_seconds'    => $timing['total'] ?? $timing['total_seconds'] ?? 0,
            'api_response'        => $data,
        ]);

        \Log::info("Submission {$this->submissionId} traitée avec succès - Score: {$overallScore}");
    }

    /**
     * Gérer l'échec du job
     */
    public function failed(\Throwable $exception): void
    {
        $submission = Submission::find($this->submissionId);
        if ($submission) {
            $submission->update([
                'overall_level' => 'error',
            ]);
        }

        \Log::error("Job échoué pour submission {$this->submissionId}: " . $exception->getMessage());
    }
}
