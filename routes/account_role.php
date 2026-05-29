<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Account\RoleController;

Route::prefix('admin/account/role')->name('admin.account.role.')->group(function () {
    Route::get('/', [RoleController::class, 'index'])->name('index');
    Route::get('/list', [RoleController::class, 'list'])->name('list');
    Route::post('/', [RoleController::class, 'store'])->name('store');
    Route::put('/{role}', [RoleController::class, 'update'])->name('update');
    Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
});