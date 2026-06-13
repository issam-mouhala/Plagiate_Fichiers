<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlagiatController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;

Route::get('/',PlagiatController::class."@index")->name("accueil.index");
Route::get('/base',PlagiatController::class."@base");
Route::get('/user',PlagiatController::class."@userDashboard");
Route::get('/api/submission/{id}/status', [PlagiatController::class, 'checkStatus'])->name('submission.status');
Route::get('/history/{id}', [PlagiatController::class, 'showSubmission'])->name('submission.show');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
Route::middleware('guest')->group(function () {
Route::get('/login',AuthController::class."@showLoginForm")->name("login");
Route::post('/login',AuthController::class."@login")->name("auth.login");
Route::get('/register',AuthController::class."@showRegisterForm")->name("register");
Route::post('/register',AuthController::class."@register")->name("auth.register");

});
Route::get('/analyse',PlagiatController::class."@analyse")->name("analyse.index");
Route::post('/analyse',PlagiatController::class."@analyse")->name("analyse.index");

Route::middleware('auth')->group(function () {
Route::get('/accueil',PlagiatController::class."@index")->name("accueil.index");
Route::get('/upload',PlagiatController::class."@upload")->name("upload.index");
Route::get('/reference/ajouter',PlagiatController::class."@create")->name("create.index");
Route::post('/reference/ajouter',PlagiatController::class."@store")->name("store.index");
Route::post('/zip', function (Request $request) {
    set_time_limit(0);


    $response = Http::timeout(12000)->attach(
       'file',
       fopen("C:\Users\Any\OneDrive\Desktop\laravel\learn\public\\fils\\". $request->file('submission')->getClientOriginalName(), 'r'),
       $request->file('submission')->getClientOriginalName()
   )->post(env('API_KEY_PY').'/api/check-zip', [
       'cross_compare'     => true,
       'add_to_database'  => false,
   ]);
   $data  = $response->json();
   $d     = $data['data'] ?? [];
//dd($d);
   return view("analyse.analyse_zip", [
    // Archive
    'zip_filename'     => $d['filename'] ?? $request->file('submission')->getClientOriginalName(),
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
]);})->name("analyse_zip");
Route::get('/dashboard', function () {
    return view('auth.dashboard');
})->name('dashboard');
});
