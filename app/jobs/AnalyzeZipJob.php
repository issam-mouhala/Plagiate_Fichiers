<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class AnalyzeZipJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public $timeout = 7200;
    public $tries = 1;

    protected $filePath;
    protected $originalName;
    protected $taskId;

    public function __construct($filePath, $originalName, $taskId)
    {
        $this->filePath = $filePath;
        $this->originalName = $originalName;
        $this->taskId = $taskId;
    }

    public function handle()
    {
        try {
            // Vérifier que le fichier existe encore (au cas où)
            if (!file_exists($this->filePath)) {
                throw new \Exception("Fichier temporaire introuvable : {$this->filePath}");
            }

            // Lire le contenu du fichier
            $fileContent = file_get_contents($this->filePath);

            // Appel à l'API Python
            $response = Http::timeout(7200)->attach(
                'file',
                $fileContent,
                $this->originalName
            )->post(env('API_KEY_PY') . '/api/check-zip', [
                'cross_compare'   => true,
                'add_to_database' => false,
            ]);

            $data = $response->json();

            Cache::put("zip_result_{$this->taskId}", [
                'status' => 'completed',
                'data'   => $data['data'] ?? [],
                'raw'    => $data,
            ], now()->addHours(2));

            // Nettoyer le fichier temporaire après traitement
            @unlink($this->filePath);

        } catch (\Exception $e) {
            Cache::put("zip_result_{$this->taskId}", [
                'status' => 'failed',
                'error'  => $e->getMessage(),
            ], now()->addMinutes(30));
        }
    }
}
