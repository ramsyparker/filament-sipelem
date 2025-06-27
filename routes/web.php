<?php

use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CustomLogoutController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\IncomeReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

// Authentication Routes
Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'store'])->name('login.store');
Route::get('/register', [AuthController::class, 'create'])->name('register');
Route::post('/register', [AuthController::class, 'storeRegister'])->name('register.store');

Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
Route::post('admin/logout', CustomLogoutController::class)
    ->middleware('auth')
    ->name('filament.admin.auth.logout');

Route::post('owner/logout', CustomLogoutController::class)
    ->middleware('auth')
    ->name('filament.owner.auth.logout');
    
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/booking/history', [BookingController::class, 'history'])->name('booking.history');
});

// Email Verification Routes
Route::get('/email/verify', [AuthController::class, 'showVerificationNotice'])
    ->middleware('auth')
    ->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('welcome')->with('success', 'Email berhasil diverifikasi! Sekarang Anda dapat login.');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', [AuthController::class, 'resendVerificationEmail'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

Route::get('booking/{fieldId}', [BookingController::class, 'showBookingForm'])->name('booking.form');

Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');

Route::middleware(['auth'])->group(function () {
    // ...existing authenticated routes...
    Route::post('/membership/register', [MembershipController::class, 'register'])->name('membership.register');
});
Route::get('/payment-membership', [MembershipController::class, 'payment'])->name('midtrans.payment');
Route::get('/payment-booking', [BookingController::class, 'payment'])->name('booking.payment');
Route::post('/payment-membership-notification', [MembershipController::class, 'notification']);  // Callback URL
Route::post('/payment-booking-notification', [BookingController::class, 'notification']);

// Route untuk form lupa password
Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');

// Route untuk reset password
Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

// routes/web.php
Route::get('/jadwal', [ScheduleController::class, 'index'])->name('schedule.view');

// Route for income report PDF export
Route::get('/income-report/print-pdf', [IncomeReportController::class, 'printPdf'])->name('income-report.print-pdf');
