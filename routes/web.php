<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\AdminController;

// Public search and verification routes with rate limiting (REV-19)
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/', function () {
        return view('home');
    });
    Route::get('/verify-translator', function () {
        return view('verify-translator-search');
    });
    Route::get('/search', [DocumentController::class, 'search']);
    Route::get('/verify/{documentId}', [DocumentController::class, 'showPublicVerify']);
    Route::get('/search-translators', [AuthController::class, 'searchTranslators']);
    Route::get('/verify-translator/{translatorId}', [AuthController::class, 'showPublicTranslator']);
    Route::get('/api/check-member/{memberNo}', [AuthController::class, 'checkMember']);
});

// Install Super Admin Route
Route::get('/install', [AuthController::class, 'showInstallForm']);
Route::post('/install', [AuthController::class, 'installSuperAdmin']);

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
    Route::get('/admin/documents/{id}/edit', [AdminController::class, 'editDocument']);
    Route::post('/admin/documents/{id}/update', [DocumentController::class, 'update']);
    Route::post('/admin/documents/{id}/archive', [DocumentController::class, 'archive']);
    Route::post('/admin/documents/{id}/toggle-qr', [DocumentController::class, 'toggleQr']);
    Route::post('/admin/documents/import-json', [DocumentController::class, 'importJson']);

    // User management for Super Admin / Admin
    Route::get('/admin/users', [AdminController::class, 'users']);
    Route::post('/admin/users', [AdminController::class, 'storeUser']);
    Route::post('/admin/users/{id}/update', [AdminController::class, 'updateUser']);
    Route::post('/admin/users/{id}/delete', [AdminController::class, 'deleteUser']);
    Route::post('/admin/users/import-json', [AdminController::class, 'importTranslatorsJson']);

    // Audit logs for Super Admin
    Route::get('/admin/audit-logs', [AdminController::class, 'auditLogs']);

    // Master Data CRUD
    Route::get('/admin/document-types', [AdminController::class, 'documentTypes']);
    Route::post('/admin/document-types', [AdminController::class, 'storeDocumentType']);
    Route::post('/admin/document-types/{id}/update', [AdminController::class, 'updateDocumentType']);
    Route::post('/admin/document-types/{id}/delete', [AdminController::class, 'deleteDocumentType']);

    Route::get('/admin/language-directions', [AdminController::class, 'languageDirections']);
    Route::post('/admin/language-directions', [AdminController::class, 'storeLanguageDirection']);
    Route::post('/admin/language-directions/{id}/update', [AdminController::class, 'updateLanguageDirection']);
    Route::post('/admin/language-directions/{id}/delete', [AdminController::class, 'deleteLanguageDirection']);
});
