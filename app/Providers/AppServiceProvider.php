<?php

namespace App\Providers;

use App\Models\DaftarHadirTestMmpi;
use App\Models\DaftarHadirTestZoom;
use App\Models\DataRiwayatDiri;
use App\Models\HasilReviewManagement;
use App\Models\JadwalInterviewKandidat;
use App\Models\JadwalOfferingLetter;
use App\Observers\RecruitmentAuditObserver;
use App\Services\DatabaseMutationAuditParser;
use App\Services\RecruitmentAuditService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        DB::listen(function (QueryExecuted $query) {
            static $recording = false;

            if ($recording || ! preg_match('/^\s*(insert|update|delete)\b/i', $query->sql, $operation)) {
                return;
            }

            if (! preg_match('/(?:into|update|from)\s+["`]?([a-zA-Z0-9_]+)["`]?/i', $query->sql, $table)) {
                return;
            }

            if (in_array($table[1], ['recruitment_audits', 'integration_deliveries', 'jobs', 'failed_jobs'], true)) {
                return;
            }

            $recording = true;

            try {
                app(RecruitmentAuditService::class)->record(
                    'database_table',
                    $table[1],
                    'db_'.strtolower($operation[1]),
                    app(DatabaseMutationAuditParser::class)->metadata($query)
                );
            } finally {
                $recording = false;
            }
        });

        foreach ([
            DataRiwayatDiri::class,
            DaftarHadirTestZoom::class,
            DaftarHadirTestMmpi::class,
            JadwalInterviewKandidat::class,
            HasilReviewManagement::class,
            JadwalOfferingLetter::class,
        ] as $model) {
            $model::observe(RecruitmentAuditObserver::class);
        }

        RateLimiter::for('login', function (Request $request) {
            $email = Str::lower((string) $request->input('email'));

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });

        RateLimiter::for('candidate-read', function (Request $request) {
            return Limit::perMinute(60)->by(
                (string) $request->route('token').'|'.$request->ip()
            );
        });

        RateLimiter::for('candidate-write', function (Request $request) {
            return Limit::perMinute(20)->by(
                (string) $request->route('token').'|'.$request->ip()
            );
        });

        Gate::before(function ($user, string $ability) {
            if (! method_exists($user, 'hasRole')) {
                return null;
            }

            if (
                $user->hasRole('Superadmin') ||
                $user->hasRole('Super Admin') ||
                $user->hasRole('superadmin') ||
                $user->hasRole('super admin')
            ) {
                return true;
            }

            return null;
        });
    }
}
