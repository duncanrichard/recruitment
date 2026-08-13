<?php

namespace Tests\Feature\Security;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PublicRateLimitTest extends TestCase
{
    public function test_login_route_uses_the_named_login_rate_limiter(): void
    {
        $middleware = Route::getRoutes()
            ->getByName('login.process')
            ->gatherMiddleware();

        $this->assertContains('throttle:login', $middleware);
    }

    public function test_candidate_read_routes_are_rate_limited(): void
    {
        foreach ([
            'pendaftaran.api.token',
            'pendaftaran.api.token.tahapan',
            'pendaftaran.api.token.cek-tahapan',
        ] as $routeName) {
            $middleware = Route::getRoutes()->getByName($routeName)->gatherMiddleware();

            $this->assertContains('throttle:candidate-read', $middleware, $routeName);
        }
    }

    public function test_candidate_mutation_routes_are_rate_limited(): void
    {
        foreach ([
            'pendaftaran.api.token.data-diri.update',
            'pendaftaran.api.token.riwayat-keluarga.update',
            'pendaftaran.api.token.riwayat-kesehatan.update',
            'pendaftaran.api.token.riwayat-pekerjaan.update',
            'pendaftaran.api.token.kesiapan-bekerja.update',
            'pendaftaran.api.token.jadwal-test.kehadiran',
            'pendaftaran.api.token.jadwal-test-mmpi.kehadiran',
            'pendaftaran.api.token.jadwal-interview.dokumen.upload',
        ] as $routeName) {
            $middleware = Route::getRoutes()->getByName($routeName)->gatherMiddleware();

            $this->assertContains('throttle:candidate-write', $middleware, $routeName);
        }
    }
}
