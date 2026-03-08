<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// Route::view('/', 'welcome');

Route::redirect('/', '/admin/homepage', 301)
    ->name('redirectHome');

Route::redirect('/login', '/admin/login', 301)
    ->name('login');
