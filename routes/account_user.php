<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Account\UserController;

Route::prefix('admin/account/user')
    ->name('account-user.')
    ->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');

        /*
        |--------------------------------------------------------------------------
        | JSON DATA
        |--------------------------------------------------------------------------
        | Route ini wajib di atas /{user}
        */
        Route::get('/list', [UserController::class, 'list'])->name('list');
        Route::get('/options', [UserController::class, 'options'])->name('options');

        /*
        |--------------------------------------------------------------------------
        | CRUD USER
        |--------------------------------------------------------------------------
        */
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });