<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\AdminController;

// Welcome page
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Authentication Routes
Route::prefix('auth')->group(function () {
    // Doctor Login
    Route::get('/doctor/login', [AuthController::class, 'showDoctorLogin'])->name('doctor.login');
    Route::post('/doctor/login', [AuthController::class, 'doctorLogin'])->name('doctor.login.submit');

    // Admin Login
    Route::get('/admin/login', [AuthController::class, 'showAdminLogin'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'adminLogin'])->name('admin.login.submit');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
});

// Doctor Routes
Route::prefix('doctor')->middleware(['auth', 'role:doctor'])->group(function () {
    Route::get('/dashboard', [DoctorController::class, 'dashboard'])->name('doctor.dashboard');
    Route::get('/patient/{patientId}', [DoctorController::class, 'getPatient']);
    Route::post('/patient/{patientId}/health-plan', [DoctorController::class, 'sendHealthPlan']);
    Route::get('/patient/{patientId}/compliance', [DoctorController::class, 'getComplianceData']);
    Route::get('/patient/{patientId}/activity', [DoctorController::class, 'getActivityLog']);
});

// Admin Routes
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    
    // Professionals Management
    Route::get('/professionals', [AdminController::class, 'getProfessionals']);
    Route::post('/professionals', [AdminController::class, 'addProfessional']);
    Route::put('/professionals/{id}', [AdminController::class, 'editProfessional']);
    Route::delete('/professionals/{id}', [AdminController::class, 'deleteProfessional']);

    // Settings
    Route::post('/settings', [AdminController::class, 'updateSettings']);
    Route::post('/admin/add', [AdminController::class, 'addAdmin']);
    Route::delete('/admin/{id}', [AdminController::class, 'removeAdmin']);

    // Stats
    Route::get('/stats', [AdminController::class, 'getStats']);
});
