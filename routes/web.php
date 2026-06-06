<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlagiatController;
Route::get('/accueil',PlagiatController::class."@index")->name("accueil.index");
Route::get('/',PlagiatController::class."@index")->name("accueil.index");
Route::get('/upload',PlagiatController::class."@upload")->name("upload.index");
Route::post('/analyse',PlagiatController::class."@analyse")->name("analyse.index");
Route::get('/reference/ajouter',PlagiatController::class."@create")->name("create.index");
Route::post('/reference/ajouter',PlagiatController::class."@store")->name("store.index");
Route::get('/zip/{name}', function (Request $request,$cheminVersFichier) {


    $response = Http::timeout(120)->attach(
       'file',
       fopen("C:\Users\Any\OneDrive\Desktop\laravel\learn\public\\fils\\". $cheminVersFichier, 'r'),
       $cheminVersFichier
   )->post('http://localhost:5000/api/check-zip', [
       'cross_compare'     => true,
       'add_to_database'  => false,
   ]);

   dd($response->json());
    if ($response->successful()) {
        $id = $response->json()['id'];
        echo "Fichier ajouté avec l'ID : $id";
    }
});
