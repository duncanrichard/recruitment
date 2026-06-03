<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Account\PermissionController;

Route::prefix('admin/account/permission')
    ->name('admin.account.permission.')
    ->controller(PermissionController::class)
    ->group(function () {
        Route::get('/', 'index')
            ->name('index');

        Route::get('/list', 'list')
            ->name('list');

        Route::get('/menu', 'menu')
            ->name('menu');

        Route::get('/role/{role}/permissions', 'rolePermissions')
            ->name('role.permissions');

        Route::put('/role/{role}/permissions', 'syncRolePermissions')
            ->name('role.permissions.sync');

        Route::post('/sync-superadmin', 'syncSuperadmin')
            ->name('sync-superadmin');

        Route::post('/', 'store')
            ->name('store');

        Route::put('/{permission}', 'update')
            ->name('update');

        Route::delete('/{permission}', 'destroy')
            ->name('destroy');
    });