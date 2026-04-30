<?php

declare(strict_types=1);

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('homepage');

Route::redirect('/login', '/app/login', 301)
    ->name('login');
