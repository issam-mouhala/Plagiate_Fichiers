<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

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

    public function analyse(Request $r)
    {
        // 1. Valider
        $r->validate([
            'submission' => 'required|file|max:10240',
            'file_type'  => 'nullable|string|in:text,code,auto',
        ]);

        $file = $r->file('submission');
        $fileType = $r->input('file_type', 'auto');

        // 2. Vérifier que l'API est disponible
        try {
            $health = Http::timeout(5)->get('http://localhost:5000/api/health');
        } catch (\Exception $e) {
            return back()->with('error', 'API non disponible. Vérifiez que le serveur Python tourne sur le port 5000.');
        }

        // 3. Envoyer le fichier à l'API
        try {
            $response = Http::timeout(120)->attach(
                'file',
                $file->get(),
                $file->getClientOriginalName()
            )->post('http://localhost:5000/api/check', [
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

        // 6. Envoyer à la vue
        return view("analyse.index", [
            // Fichier
            'filename'         => $d['filename'] ?? $file->getClientOriginalName(),
            'file_type'        => $d['detected_type'] ?? 'text',
            'detected_format'  => $d['detected_type'] ?? null,
            'extraction'       => $d['extraction'] ?? [],
            'content_length'   => $d['content_length'] ?? 0,
            'images_extracted' => $d['images_extracted'] ?? 0,
            'num_comparisons'  => $d['num_comparisons'] ?? 0,

            // Score global
            'plagiarism_detected' => $d['plagiarism_detected'] ?? false,
            'overall_score'  => $d['overall_score'] ?? 0,
            'overall_level'  => $d['overall_level'] ?? 'none',

            // Analyse texte
            'content_analysis' => $d['content_analysis'] ?? [],
            'text_matches'     => $d['content_analysis']['text_matches'] ?? [],
            'engines_used'     => $enginesUsed,

            // Analyse images
            'image_analysis' => $d['image_analysis'] ?? [],
            'image_matches'  => $d['image_analysis']['image_matches'] ?? [],

            // Tout le JSON brut pour export
            'raw_response' => $data,
        ]);
    }
    function create(){
        return View("add.index");
    }
    function store(Request $r){
   // 3. Envoyer le fichier à l'API

     $response = Http::attach(
            'file',                       // nom du champ attendu par Python
            file_get_contents($r->file->path()),
            $r->file->getClientOriginalName()              // nom original
        )->post('http://localhost:5000/api/add', [
            'file_type' => 'auto',       // ou 'text'
            'metadata'  => json_encode([ // optionnel
                'auteur' => 'Prof Martin',
                'cours'  => 'Algorithmique'
            ])
             ]);
    dd($response);
        if ($response->successful()) {
            $id = $response->json()['id'];
            echo "Fichier ajouté avec l'ID : $id";
        }
        return View("add.index");
    }
}
