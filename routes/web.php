<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlagiatController;
Route::get('/accueil',PlagiatController::class."@index")->name("accueil.index");
Route::get('/login',PlagiatController::class."@login")->name("accueil.login");
Route::post('/login',PlagiatController::class."@login")->name("accueil.login");
Route::get('/register',PlagiatController::class."@register")->name("accueil.register");
Route::post('/register',PlagiatController::class."@register")->name("accueil.register");
Route::get('/',PlagiatController::class."@index")->name("accueil.index");
Route::get('/upload',PlagiatController::class."@upload")->name("upload.index");
Route::post('/analyse',PlagiatController::class."@analyse")->name("analyse.index");
Route::get('/reference/ajouter',PlagiatController::class."@create")->name("create.index");
Route::post('/reference/ajouter',PlagiatController::class."@store")->name("store.index");
Route::get('/zip/{name}', function (Request $request,$cheminVersFichier) {
    set_time_limit(0);


    $response = Http::timeout(1200)->attach(
       'file',
       fopen("C:\Users\Any\OneDrive\Desktop\laravel\learn\public\\fils\\". $cheminVersFichier, 'r'),
       $cheminVersFichier
   )->post('http://localhost:5000/api/check-zip', [
       'cross_compare'     => true,
       'add_to_database'  => false,
   ]);
   $data  = $response->json();

   $d     = $data['data'] ?? [];

   return view("analyse.analyse_zip", [
    // Archive
    'zip_filename'     => $d['filename'] ?? $file->getClientOriginalName(),
    'zip_info'         => $d['zip_info'] ?? [],
    'files_extracted'  => $d['files_extracted'] ?? [],

    // Score global
    'plagiarism_detected' => $d['plagiarism_detected'] ?? false,
    'overall_score'    => $d['overall_score'] ?? 0,
    'overall_level'    => $d['overall_level'] ?? 'none',

    // Croisement interne
    'cross_analysis'   => $d['cross_file_analysis'] ?? [],
    'cross_matches'    => $d['cross_file_analysis']['matches'] ?? [],

    // Per-file base
    'per_file_analysis' => $d['per_file_database_analysis'] ?? [],
    'per_file_results'  => $d['per_file_database_analysis']['results'] ?? [],

    // Images
    'image_analysis'   => $d['image_analysis'] ?? [],
    'image_matches'    => $d['image_analysis']['image_matches'] ?? [],

    // JSON brut
    'raw_response'     => $data,
]);});
