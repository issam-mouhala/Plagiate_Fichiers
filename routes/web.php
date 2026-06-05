<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlagiatController;
Route::get('/accueil',PlagiatController::class."@index")->name("accueil.index");
Route::get('/',PlagiatController::class."@index")->name("accueil.index");
Route::get('/upload',PlagiatController::class."@upload")->name("upload.index");
Route::post('/analyse',PlagiatController::class."@analyse")->name("analyse.index");
Route::get('/add/{name}', function (Request $request,$cheminVersFichier) {


    $response = Http::attach(
        'file',                       // nom du champ attendu par Python
        file_get_contents("C:\Users\Any\OneDrive\Desktop\laravel\learn\public\\fils\\".$cheminVersFichier),
        'mon_fichier.py'              // nom original
    )->post('http://localhost:5000/api/add', [
        'file_type' => 'text',       // ou 'text'
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
});
