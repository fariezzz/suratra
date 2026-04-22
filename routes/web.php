<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LetterRequestController;
use App\Http\Controllers\ResidentController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role:pengurus_rt,pengurus_rw')->group(function (): void {
        Route::resource('warga', ResidentController::class)
            ->except('show')
            ->parameters(['warga' => 'resident'])
            ->names('residents');
    });

    Route::prefix('pengajuan-surat')->name('letters.')->group(function (): void {
        Route::get('/', [LetterRequestController::class, 'index'])->name('index');
        Route::post('/', [LetterRequestController::class, 'store'])
            ->middleware('role:warga')
            ->name('store');
        Route::post('/{letterRequest}/rt', [LetterRequestController::class, 'rtDecision'])
            ->middleware('role:pengurus_rt')
            ->name('rt-decision');
        Route::post('/{letterRequest}/rw', [LetterRequestController::class, 'rwDecision'])
            ->middleware('role:pengurus_rw')
            ->name('rw-decision');
        Route::get('/{letterRequest}/surat', [LetterRequestController::class, 'show'])->name('show');
        Route::get('/{letterRequest}/dokumen/{key}', [LetterRequestController::class, 'downloadDocument'])->name('document');
    });
});
