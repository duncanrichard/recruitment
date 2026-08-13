<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CompanyAccessService;
use App\Services\SpreadsheetValueSanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RecruitmentAuditController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $this->validateFilters($request);
        $query = $this->query($validated);

        return response()->json([
            'success' => true,
            'summary' => [
                'total' => (clone $query)->count(),
                'failed_integrations' => (clone $query)->where('event', 'failed_permanently')->count(),
                'downloads' => (clone $query)->where('event', 'downloaded')->count(),
            ],
            'data' => $query->latest('created_at')->paginate(50)->withQueryString(),
        ]);
    }

    public function show(string $audit): JsonResponse
    {
        $query = $this->query([]);
        $row = $query->where('id', $audit)->first();
        abort_unless($row, 404);

        return response()->json(['success' => true, 'data' => $row]);
    }

    public function export(Request $request): StreamedResponse
    {
        $rows = $this->query($this->validateFilters($request))->latest('created_at')->limit(10000)->get();

        return response()->streamDownload(function () use ($rows) {
            $output = fopen('php://output', 'wb');
            fputcsv($output, ['Waktu', 'Event', 'Tipe', 'Record ID', 'Company ID', 'User ID', 'Route', 'Request ID', 'IP']);
            $safe = app(SpreadsheetValueSanitizer::class);

            foreach ($rows as $row) {
                fputcsv($output, array_map(
                    fn ($value) => $safe->sanitize($value),
                    [$row->created_at, $row->event, $row->auditable_type, $row->auditable_id, $row->company_id, $row->user_id, $row->route_name, $row->request_id, $row->ip_address]
                ));
            }

            fclose($output);
        }, 'recruitment-audit-'.now()->format('Ymd-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function validateFilters(Request $request): array
    {
        return $request->validate([
            'event' => ['nullable', 'string', 'max:30'],
            'auditable_type' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'uuid'],
            'company_id' => ['nullable', 'uuid'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);
    }

    private function query(array $filters)
    {
        $query = DB::table('recruitment_audits');
        app(CompanyAccessService::class)->apply($query, Auth::user(), 'company_id');

        return $query
            ->when($filters['event'] ?? null, fn ($q, $value) => $q->where('event', $value))
            ->when($filters['auditable_type'] ?? null, fn ($q, $value) => $q->where('auditable_type', $value))
            ->when($filters['user_id'] ?? null, fn ($q, $value) => $q->where('user_id', $value))
            ->when($filters['company_id'] ?? null, fn ($q, $value) => $q->where('company_id', $value))
            ->when($filters['date_from'] ?? null, fn ($q, $value) => $q->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn ($q, $value) => $q->whereDate('created_at', '<=', $value));
    }
}
