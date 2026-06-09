<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Account\UserController;

Route::prefix('admin/account/user')
    ->name('admin.account.user.')
    ->group(function () {
        Route::get('/', [UserController::class, 'index'])
            ->middleware('permission:admin.account.user.list')
            ->name('index');

        /*
        |--------------------------------------------------------------------------
        | JSON DATA
        |--------------------------------------------------------------------------
        | Route ini wajib di atas /{user}
        */
        Route::get('/list', [UserController::class, 'list'])
            ->middleware('permission:admin.account.user.list')
            ->name('list');

        Route::get('/options', [UserController::class, 'options'])
            ->middleware('permission:admin.account.user.list')
            ->name('options');

        /*
        |--------------------------------------------------------------------------
        | CRUD USER
        |--------------------------------------------------------------------------
        */
        Route::post('/', [UserController::class, 'store'])
            ->middleware('permission:admin.account.user.store')
            ->name('store');

        Route::put('/{user}', [UserController::class, 'update'])
            ->middleware('permission:admin.account.user.update')
            ->name('update');

        Route::delete('/{user}', [UserController::class, 'destroy'])
            ->middleware('permission:admin.account.user.destroy')
            ->name('destroy');
    });