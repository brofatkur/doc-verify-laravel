<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\AdminController;

// Public search and verification routes
Route::get('/', function () {
    return view('home');
});
Route::get('/search', [DocumentController::class, 'search']);
Route::get('/verify/{documentId}', [DocumentController::class, 'showPublicVerify']);
Route::get('/search-translators', [AuthController::class, 'searchTranslators']);
Route::get('/verify-translator/{translatorId}', [AuthController::class, 'showPublicTranslator']);

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'doLogin']);
    Route::get('/register', [AuthController::class, 'register']);
    Route::post('/register', [AuthController::class, 'doRegister']);
    Route::get('/forgot-password', [AuthController::class, 'forgotPasswordView']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::get('/reset-password', [AuthController::class, 'resetPasswordView']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Protected Portal Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Dashboards & Profile Configuration
    Route::get('/admin', [AdminController::class, 'dashboard']);
    Route::get('/admin/profile', [AdminController::class, 'profile']);
    Route::post('/admin/profile', [AuthController::class, 'updateProfile']);

    // Document Management
    Route::get('/admin/documents/new', [AdminController::class, 'createDocument']);
    Route::post('/admin/documents', [DocumentController::class, 'store']);
    Route::post('/admin/documents/{id}/toggle-qr', [DocumentController::class, 'toggleQr']);
    Route::post('/admin/documents/import-json', [DocumentController::class, 'importJson']);

    // User management for Super Admin
    Route::post('/admin/users', [AdminController::class, 'storeUser']);
    Route::post('/admin/users/{id}/update', [AdminController::class, 'updateUser']);
    Route::post('/admin/users/{id}/delete', [AdminController::class, 'deleteUser']);
});
