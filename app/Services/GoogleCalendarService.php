<?php

namespace App\Services;

use Carbon\Carbon;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\ConferenceData;
use Google\Service\Calendar\CreateConferenceRequest;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventAttendee;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Calendar\ConferenceSolutionKey;

class GoogleCalendarService
{
    private Calendar $calendar;
    private string $calendarId;
    private string $timezone;

    public function __construct()
    {
        $this->calendarId = env('GOOGLE_CALENDAR_ID', 'primary');
        $this->timezone = env('APP_TIMEZONE', 'Asia/Jakarta');

        $client = new Client();
        $client->setApplicationName('Recruitment Interview Calendar');
        $client->setScopes([
            Calendar::CALENDAR,
        ]);

        $credentialsPath = base_path(env('GOOGLE_CALENDAR_CREDENTIALS'));

        if (!file_exists($credentialsPath)) {
            throw new \RuntimeException('File credential Google Calendar tidak ditemukan: ' . $credentialsPath);
        }

        $client->setAuthConfig($credentialsPath);

        $impersonateEmail = env('GOOGLE_CALENDAR_IMPERSONATE_EMAIL');

        if (!empty($impersonateEmail)) {
            $client->setSubject($impersonateEmail);
        }

        $this->calendar = new Calendar($client);
    }

    public function upsertInterviewEvent(array $payload): array
    {
        $eventId = $payload['event_id'] ?? null;

        $event = $this->buildEvent($payload);

        if ($eventId) {
            $result = $this->calendar->events->update(
                $this->calendarId,
                $eventId,
                $event,
                [
                    'sendUpdates' => 'all',
                    'conferenceDataVersion' => 1,
                ]
            );
        } else {
            $result = $this->calendar->events->insert(
                $this->calendarId,
                $event,
                [
                    'sendUpdates' => 'all',
                    'conferenceDataVersion' => 1,
                ]
            );
        }

        return [
            'success' => true,
            'event_id' => $result->getId(),
            'html_link' => $result->getHtmlLink(),
            'meet_link' => $result->getHangoutLink(),
        ];
    }

    public function deleteEvent(?string $eventId): array
    {
        if (!$eventId) {
            return [
                'success' => true,
                'message' => 'Tidak ada event Google Calendar yang perlu dihapus.',
            ];
        }

        $this->calendar->events->delete(
            $this->calendarId,
            $eventId,
            [
                'sendUpdates' => 'all',
            ]
        );

        return [
            'success' => true,
            'message' => 'Event Google Calendar berhasil dihapus.',
        ];
    }

    private function buildEvent(array $payload): Event
    {
        $start = Carbon::parse($payload['start'], $this->timezone);
        $durationMinutes = (int) ($payload['duration_minutes'] ?? env('GOOGLE_CALENDAR_EVENT_DURATION_MINUTES', 60));
        $end = (clone $start)->addMinutes($durationMinutes);

        $attendees = collect($payload['attendees'] ?? [])
            ->filter(fn ($item) => !empty($item['email']))
            ->unique('email')
            ->map(function ($item) {
                return new EventAttendee([
                    'email' => $item['email'],
                    'displayName' => $item['name'] ?? null,
                    'optional' => false,
                ]);
            })
            ->values()
            ->all();

        $event = new Event([
            'summary' => $payload['summary'] ?? 'Jadwal Interview Kandidat',
            'description' => $payload['description'] ?? null,
            'location' => $payload['location'] ?? 'Google Meet',
            'start' => new EventDateTime([
                'dateTime' => $start->toRfc3339String(),
                'timeZone' => $this->timezone,
            ]),
            'end' => new EventDateTime([
                'dateTime' => $end->toRfc3339String(),
                'timeZone' => $this->timezone,
            ]),
            'attendees' => $attendees,
        ]);

        $conferenceData = new ConferenceData();
        $conferenceRequest = new CreateConferenceRequest();
        $conferenceRequest->setRequestId('interview-' . uniqid());

        $conferenceSolutionKey = new ConferenceSolutionKey();
        $conferenceSolutionKey->setType('hangoutsMeet');

        $conferenceRequest->setConferenceSolutionKey($conferenceSolutionKey);
        $conferenceData->setCreateRequest($conferenceRequest);

        $event->setConferenceData($conferenceData);

        return $event;
    }
}