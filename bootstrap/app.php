<?php

use App\Http\Middleware\AssignRequestId;
use App\Http\Middleware\AuditDataAccess;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(AssignRequestId::class);
        /*
        |--------------------------------------------------------------------------
        | Redirect Auth
        |--------------------------------------------------------------------------
        */
        $middleware->redirectGuestsTo(function (Request $request) {
            return route('login.page');
        });

        $middleware->redirectUsersTo(function (Request $request) {
            return route('dashboard.index');
        });

        /*
        |--------------------------------------------------------------------------
        | Spatie Permission Middleware Alias
        |--------------------------------------------------------------------------
        | Ini wajib supaya route bisa pakai:
        | ->middleware('permission:nama.permission')
        | ->middleware('role:Superadmin')
        | ->middleware('role_or_permission:...')
        */
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'audit.access' => AuditDataAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
