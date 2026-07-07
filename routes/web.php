<?php

declare(strict_types=1);

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('homepage');

// Points Laravel's default "login" redirect at the default panel's login page.
// Uses the panel's named route so it stays in sync if the panel path changes.
Route::get('/login', fn () => redirect()->route('filament.app-panel.auth.login'))
    ->name('login');

Route::get('/app/login', fn () => redirect()->route('filament.app-panel.auth.login'))
    ->name('app-login');

