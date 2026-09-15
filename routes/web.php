<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransparencyController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\StaffManagementController;

Route::get('/', [HomeController::class, 'index']);

Route::get('/profil', function () {
    return view('profile');
});

Route::get('/lapor', function () {
    return view('reports.create');
});

Route::post('/reports', [ReportController::class, 'store'])->middleware('throttle:5,1');

Route::get('/track', [ReportController::class, 'trackForm']);
Route::post('/track', [ReportController::class, 'trackResult'])->middleware('throttle:10,1');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::get('/forgot-password', [AuthController::class, 'showForgotPassword']);
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:3,1');

Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,1');

Route::post('/logout', [AuthController::class, 'logout']);

Route::middleware(['auth', 'approved'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::patch('/clusters/{cluster}', [DashboardController::class, 'updateStatus']);
    Route::post('/dashboard/summary', [DashboardController::class, 'generateSummary'])->middleware('throttle:5,1');
    Route::get('/dashboard/summary/status', [DashboardController::class, 'summaryStatus']);
    Route::middleware('admin')->group(function () {
        Route::get('/staff', [StaffManagementController::class, 'index']);
        Route::patch('/staff/{user}/approve', [StaffManagementController::class, 'approve']);
        Route::delete('/staff/{user}/reject', [StaffManagementController::class, 'reject']);
    });
});

Route::get('/transparansi', [TransparencyController::class, 'index']);
Route::get('/bukti-nyata', [TransparencyController::class, 'proof']);