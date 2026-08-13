<?php

use App\Http\Controllers\Admin\Account\PermissionController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/account/permission')
    ->name('admin.account.permission.')
    ->controller(PermissionController::class)
    ->group(function () {
        Route::get('/', 'index')
            ->middleware('permission:admin.account.permission.list')
            ->name('index');

        Route::get('/list', 'list')
            ->middleware('permission:admin.account.permission.list')
            ->name('list');

        Route::get('/menu', 'menu')
            ->middleware('permission:admin.account.permission.setting')
            ->name('menu');

        Route::get('/role/{role}/permissions', 'rolePermissions')
            ->middleware('permission:admin.account.permission.setting')
            ->name('role.permissions');

        Route::put('/role/{role}/permissions', 'syncRolePermissions')
            ->middleware('permission:admin.account.permission.setting')
            ->name('role.permissions.sync');

        Route::post('/sync-superadmin', 'syncSuperadmin')
            ->middleware('permission:admin.account.permission.setting')
            ->name('sync-superadmin');

        Route::post('/', 'store')
            ->middleware('permission:admin.account.permission.store')
            ->name('store');

        Route::put('/{permission}', 'update')
            ->middleware('permission:admin.account.permission.update')
            ->name('update');

        Route::delete('/{permission}', 'destroy')
            ->middleware('permission:admin.account.permission.destroy')
            ->name('destroy');
    });
