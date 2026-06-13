<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlagiatController;
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/user', PlagiatController::class."@getSubmessionUser");

});
