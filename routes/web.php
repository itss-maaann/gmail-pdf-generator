<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\AuthStatusController;

Route::view('/', 'home');

// OAuth Routes
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('google.oauth.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.oauth.callback');

// AJAX Auth Status Check
Route::get('/check-auth-status', [AuthStatusController::class, 'check'])->name('check.auth.status');

// Core Functionality
Route::post('/simulate-conversation', [ConversationController::class, 'simulate'])->name('simulate.conversation');
Route::post('/generate-pdf', [PdfController::class, 'generate'])->name('generate.pdf');
Route::get('/check-pdf-status/{id}', [PdfController::class, 'checkPdfStatus']);
Route::get('/download-pdf/{id}', [PdfController::class, 'downloadPdf']);


Route::get('/test-pdf-path', function () {
    return config('snappy.pdf.binary');
});
