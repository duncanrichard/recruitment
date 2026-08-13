<?php

namespace App\Http\Middleware;

use App\Services\RecruitmentAuditService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditDataAccess
{
    public function handle(Request $request, Closure $next, string $event = 'exported'): Response
    {
        $response = $next($request);

        if ($response->isSuccessful()) {
            app(RecruitmentAuditService::class)->record(
                'http_route',
                (string) ($request->route()?->getName() ?: $request->path()),
                $event,
                ['filters' => $request->except(['token', 'password'])]
            );
        }

        return $response;
    }
}
