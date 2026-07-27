<?php

use App\Http\Controllers\VisionBoardController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::resource('vision-boards', VisionBoardController::class)->only(['index', 'store', 'update', 'destroy']);
});

require __DIR__.'/settings.php';
