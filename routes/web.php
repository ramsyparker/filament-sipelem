<?php

use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

// Authentication Routes
Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'store'])->name('login.store');
Route::get('/register', [AuthController::class, 'create'])->name('register');
Route::post('/register', [AuthController::class, 'storeRegister'])->name('register.store');
Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});
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


