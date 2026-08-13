<?php

namespace App\Jobs;

use App\Models\JadwalInterview;
use App\Services\GoogleCalendarService;
use App\Services\IntegrationFailureNotifier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class SyncInterviewCalendar implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public array $backoff = [30, 120, 300];

    public int $uniqueFor = 300;

    public function __construct(
        public readonly string $scheduleId,
        public readonly string $action,
        public readonly string $idempotencyKey,
    ) {
        $this->onQueue('integrations');
    }

    public function uniqueId(): string
    {
        return $this->idempotencyKey;
    }

    public function handle(GoogleCalendarService $calendar): void
    {
        $schedule = JadwalInterview::withTrashed()
            ->with('jadwalInterviewKandidat.kandidat')
            ->findOrFail($this->scheduleId);

        DB::table('integration_deliveries')->where('idempotency_key', $this->idempotencyKey)->update([
            'status' => 'processing',
            'attempts' => DB::raw('attempts + 1'),
            'updated_at' => now(),
        ]);

        if ($this->action === 'delete') {
            $result = $calendar->deleteEvent($schedule->google_calendar_event_id ?? null);
            $schedule->forceFill([
                'google_calendar_event_id' => null,
                'google_calendar_html_link' => null,
                'google_meet_link' => null,
            ])->saveQuietly();
        } else {
            $candidates = $schedule->jadwalInterviewKandidat->pluck('kandidat')->filter();
            $attendees = $candidates->filter(fn ($candidate) => filter_var($candidate->email, FILTER_VALIDATE_EMAIL))
                ->map(fn ($candidate) => ['email' => $candidate->email, 'name' => $candidate->nama_lengkap])
                ->values()->all();

            if ($attendees === []) {
                throw new RuntimeException('Tidak ada email kandidat valid untuk sinkronisasi Calendar.');
            }

            $result = $calendar->upsertInterviewEvent([
                'event_id' => $schedule->google_calendar_event_id ?? null,
                'summary' => $schedule->judul_interview ?: 'Jadwal Interview Kandidat',
                'description' => $candidates->pluck('nama_lengkap')->filter()->implode(', '),
                'start' => $schedule->jadwal_interview,
                'attendees' => $attendees,
            ]);

            $schedule->forceFill([
                'google_calendar_event_id' => $result['event_id'] ?? null,
                'google_calendar_html_link' => $result['html_link'] ?? null,
                'google_meet_link' => $result['meet_link'] ?? null,
            ])->saveQuietly();
        }

        DB::table('integration_deliveries')->where('idempotency_key', $this->idempotencyKey)->update([
            'status' => 'delivered',
            'provider_response' => json_encode($result, JSON_THROW_ON_ERROR),
            'delivered_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function failed(?\Throwable $exception): void
    {
        DB::table('integration_deliveries')->where('idempotency_key', $this->idempotencyKey)->update([
            'status' => 'failed',
            'error_message' => Str::limit((string) $exception?->getMessage(), 2000, ''),
            'updated_at' => now(),
        ]);

        app(IntegrationFailureNotifier::class)->notify('google_calendar', $this->idempotencyKey, $exception);
    }
}
