<?php

use App\Http\Controllers\Admin\IntegrationAlertController;
use Illuminate\Support\Facades\Route;

Route::view('/admin', 'pages.admin.index', [
    'title' => 'Admin Dashboard',
])->name('admin.dashboard');

Route::get('/admin/integration-alerts', [IntegrationAlertController::class, 'index'])
    ->middleware('permission:admin.integration-alert.list')
    ->name('admin.integration-alerts.index');
Route::post('/admin/integration-alerts/{delivery}/retry', [IntegrationAlertController::class, 'retry'])
    ->middleware('permission:admin.integration-alert.retry')
    ->name('admin.integration-alerts.retry');
Route::patch('/admin/integration-alerts/{delivery}/acknowledge', [IntegrationAlertController::class, 'acknowledge'])
    ->middleware('permission:admin.integration-alert.acknowledge')
    ->name('admin.integration-alerts.acknowledge');
