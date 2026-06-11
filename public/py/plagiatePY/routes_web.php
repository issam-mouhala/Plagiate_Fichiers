<?php

use App\Http\Controllers\PlagiatController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes Plagiarism Detection
|--------------------------------------------------------------------------
*/

// ─── Pages ───
Route::get('/', [PlagiatController::class, 'index'])->name('home');
Route::get('/upload', [PlagiatController::class, 'upload'])->name('upload');
Route::get('/add', [PlagiatController::class, 'create'])->name('add.create');

// ─── Auth ───
Route::get('/login', [PlagiatController::class, 'login'])->name('login');
Route::get('/register', [PlagiatController::class, 'register'])->name('register');

// ─── Analyse (fichier unique) ───
Route::post('/analyse', [PlagiatController::class, 'analyse'])->name('analyse');

// ─── Ajouter au corpus (ZIP) ───
Route::post('/add', [PlagiatController::class, 'store'])->name('add.store');

// ─── Historique ───
Route::get('/history', [PlagiatController::class, 'history'])->name('history');

// ─── Détail submission ───
Route::get('/history/{id}', [PlagiatController::class, 'showSubmission'])->name('submission.show');

// ─── Supprimer submission ───
Route::delete('/history/{id}', [PlagiatController::class, 'deleteSubmission'])->name('submission.delete');

// ─── API : vérifier statut (polling JS) ───
Route::get('/api/submission/{id}/status', [PlagiatController::class, 'checkStatus'])->name('submission.status');
