<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\BackupEmailController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\OtpVerificationController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');

    // Google OAuth
    Route::get('auth/google/redirect',  [GoogleAuthController::class, 'redirect'])
        ->name('auth.google.redirect');
    Route::get('auth/google/callback',  [GoogleAuthController::class, 'callback'])
        ->name('auth.google.callback');
});

Route::middleware('auth')->group(function () {
    Route::put('password', [PasswordController::class, 'update'])
        ->name('password.update');

    // Recovery email (admin & superadmin only — di-guard di controller).
    Route::post('account/backup-email',          [BackupEmailController::class, 'store'])
        ->name('account.backup-email.store');
    Route::post('account/backup-email/resend',   [BackupEmailController::class, 'resend'])
        ->middleware('throttle:3,1')
        ->name('account.backup-email.resend');
    Route::delete('account/backup-email',        [BackupEmailController::class, 'destroy'])
        ->name('account.backup-email.destroy');
});

// Verify backup email — bisa diakses guest (signed URL) dan auth.
Route::get('account/backup-email/verify/{user}', [BackupEmailController::class, 'verify'])
    ->middleware('signed')
    ->name('account.backup-email.verify');

Route::middleware('auth')->group(function () {

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    // OTP email verification
    Route::get('verify-otp',          [OtpVerificationController::class, 'show'])
        ->name('verification.otp.notice');
    Route::post('verify-otp',         [OtpVerificationController::class, 'verify'])
        ->name('verification.otp.verify');
    Route::post('verify-otp/resend',  [OtpVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.otp.resend');

    // Alias agar middleware `verified` Laravel bisa redirect ke OTP page (default cari nama 'verification.notice').
    Route::get('email/verify', fn () => redirect()->route('verification.otp.notice'))
        ->name('verification.notice');
});
