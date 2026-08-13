<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendFonnteBatch;
use App\Jobs\SyncInterviewCalendar;
use App\Services\CompanyAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IntegrationAlertController extends Controller
{
    public function index(): JsonResponse
    {
        $query = DB::table('integration_deliveries');
        app(CompanyAccessService::class)->apply($query, Auth::user(), 'company_id');

        return response()->json([
            'success' => true,
            'data' => $query
                ->where(function ($query) {
                    $query->where('status', 'failed')
                        ->orWhere(function ($stale) {
                            $stale->whereIn('status', ['queued', 'processing'])
                                ->where('updated_at', '<', now()->subMinutes(15));
                        });
                })
                ->latest('updated_at')
                ->limit(100)
                ->get(),
        ]);
    }

    public function retry(string $delivery): JsonResponse
    {
        $query = DB::table('integration_deliveries')->where('idempotency_key', $delivery);
        app(CompanyAccessService::class)->apply($query, Auth::user(), 'company_id');
        $row = $query->first();
        abort_unless($row && $row->status === 'failed', 404);

        $payload = json_decode((string) $row->payload, true) ?: [];
        abort_if($payload === [], 422, 'Payload retry tidak tersedia untuk delivery lama.');
        abort_unless(in_array($row->provider, ['fonnte', 'google_calendar'], true), 422, 'Provider tidak mendukung retry.');

        DB::table('integration_deliveries')->where('idempotency_key', $delivery)->update([
            'status' => 'queued',
            'error_message' => null,
            'updated_at' => now(),
        ]);

        if ($row->provider === 'fonnte') {
            SendFonnteBatch::dispatch((string) $payload['company_id'], $payload['messages'] ?? [], $delivery);
        } elseif ($row->provider === 'google_calendar') {
            SyncInterviewCalendar::dispatch((string) $payload['schedule_id'], (string) ($payload['action'] ?? 'sync'), $delivery);
        }

        return response()->json(['success' => true, 'message' => 'Delivery masuk antrean retry.']);
    }

    public function acknowledge(Request $request, string $delivery): JsonResponse
    {
        $query = DB::table('integration_deliveries')->where('idempotency_key', $delivery);
        app(CompanyAccessService::class)->apply($query, Auth::user(), 'company_id');
        $updated = $query->update([
            'acknowledged_at' => now(),
            'updated_at' => now(),
        ]);
        abort_unless($updated, 404);

        return response()->json(['success' => true]);
    }
}
