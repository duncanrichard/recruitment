<?php

use Illuminate\Support\Facades\Route;

Route::view('/admin', 'pages.admin.index', [
    'title' => 'Admin Dashboard',
])->name('admin.dashboard');