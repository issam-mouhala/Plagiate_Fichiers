<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlagiatController;

/*
|--------------------------------------------------------------------------
| PlagioScan — Routes
|--------------------------------------------------------------------------
*/

// Dashboard (unified upload + results)
Route::get('/', [PlagiatController::class, 'dashboard'])->name('dashboard');

// Single-file analysis
Route::post('/analyse', [PlagiatController::class, 'analyse'])->name('dashboard.analyse');

// ZIP analysis
Route::post('/analyse-zip', [PlagiatController::class, 'analyseZip'])->name('dashboard.analyse-zip');

// API health proxy (for frontend status check)
Route::get('/api/health-proxy', function () {
    try {
        $response = \Illuminate\Support\Facades\Http::timeout(3)->get('http://localhost:5000/api/health');
        return response()->json($response->json());
    } catch (\Exception $e) {
        return response()->json(['status' => 'offline', 'error' => $e->getMessage()], 503);
    }
});

// API stats (JSON)
Route::get('/api/submissions/stats', [PlagiatController::class, 'apiStats'])->name('api.submissions.stats');

// Serve extracted images (proxy to Python API)
Route::get('/serve-image/{filename}', [PlagiatController::class, 'serveImage'])->name('serve.image');

// =============================================
//  SUBMISSIONS — History / Detail / Delete
// =============================================

// History list (with filters)
Route::get('/history', [PlagiatController::class, 'history'])->name('submissions.history');

// Submission detail
Route::get('/history/{id}', [PlagiatController::class, 'showSubmission'])->name('submissions.show');

// Delete submission
Route::delete('/history/{id}', [PlagiatController::class, 'deleteSubmission'])->name('submissions.delete');

// =============================================
//  LEGACY — Old separate pages (kept for BC)
// =============================================
Route::get('/upload', [PlagiatController::class, 'upload']);
Route::get('/upload-zip', [PlagiatController::class, 'uploadZip']);
