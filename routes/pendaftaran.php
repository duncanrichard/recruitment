<?php

use Illuminate\Support\Facades\Route;

Route::get('/pendaftaran', function () {
    return view('pages.pendaftaran.index', [
        'title' => 'Pendaftaran',
    ]);
})->name('pendaftaran');