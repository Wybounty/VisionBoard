<?php

use App\Http\Controllers\VisionBoardController;
use App\Http\Controllers\VisionBoardBriefController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::resource('vision-boards', VisionBoardController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('vision-boards/{visionBoard}/brief', [VisionBoardBriefController::class, 'show'])
        ->name('vision-boards.brief.show');
    Route::post('vision-boards/{visionBoard}/brief', [VisionBoardBriefController::class, 'store'])
        ->name('vision-boards.brief.store');
});

require __DIR__.'/settings.php';
