<?php

use App\Http\Controllers\Messenger\MessengerController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [MessengerController::class, 'index'])->name('dashboard');

    Route::get('/api/users', [MessengerController::class, 'getUsersForPage'])->name('users.page');

    Route::get('/messages/{user}', [MessengerController::class, 'getMessages'])->name('messages.show');
    Route::post('/messages/{user}/read', [MessengerController::class, 'markAsRead'])->name('messages.read');
    Route::post('/messages', [MessengerController::class, 'sendMessage'])->name('messages.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
