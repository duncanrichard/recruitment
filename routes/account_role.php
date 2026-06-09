<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Account\RoleController;

Route::prefix('admin/account/role')
    ->name('admin.account.role.')
    ->group(function () {
        Route::get('/', [RoleController::class, 'index'])
            ->middleware('permission:admin.account.role.list')
            ->name('index');

        Route::get('/list', [RoleController::class, 'list'])
            ->middleware('permission:admin.account.role.list')
            ->name('list');

        Route::post('/', [RoleController::class, 'store'])
            ->middleware('permission:admin.account.role.store')
            ->name('store');

        Route::put('/{role}', [RoleController::class, 'update'])
            ->middleware('permission:admin.account.role.update')
            ->name('update');

        Route::delete('/{role}', [RoleController::class, 'destroy'])
            ->middleware('permission:admin.account.role.destroy')
            ->name('destroy');
    });