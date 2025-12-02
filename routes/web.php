<?php

use Illuminate\Support\Facades\Route;

/*
Route::get('/', function () {
    return view('welcome');
})->name('home');
*/

Route::redirect('/', '/admin/homepage', 301)
    ->name('redirectHome');

Route::redirect('/login', '/admin/login', 301)
    ->name('login');
