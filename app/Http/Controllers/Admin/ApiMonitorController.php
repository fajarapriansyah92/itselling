<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiMonitorController extends Controller
{
    /**
     * Display the Web Service Monitor dashboard and incoming HTTP request logs.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->wantsJson() || $request->has('poll')) {
            $latestLogs = ApiLog::latest()->take(25)->get();

            $totalRequests = ApiLog::count();
            $successRequests = ApiLog::whereBetween('status_code', [200, 299])->count();
            $avgResponseTime = (int) round(ApiLog::avg('response_time_ms') ?? 0);
            $mobileRequests = ApiLog::where('client_type', 'like', '%Mobile%')
                ->orWhere('client_type', 'like', '%Flutter%')
                ->count();

            $successRate = $totalRequests > 0 ? round(($successRequests / $totalRequests) * 100, 1) : 100;

            return response()->json([
                'metrics' => [
                    'total' => number_format($totalRequests),
                    'mobile' => number_format($mobileRequests),
                    'success_rate' => $successRate,
                    'avg_time' => $avgResponseTime,
                ],
                'logs' => $latestLogs->map(function (ApiLog $log) {
                    return [
                        'id' => $log->id,
                        'time' => $log->created_at->format('H:i:s'),
                        'date' => $log->created_at->format('d/m/Y'),
                        'method' => $log->method,
                        'endpoint' => $log->endpoint,
                        'client_type' => $log->client_type,
                        'ip_address' => $log->ip_address,
                        'status_code' => $log->status_code,
                        'response_time_ms' => $log->response_time_ms,
                        'payload' => $log->request_payload,
                    ];
                }),
            ]);
        }

        $logs = ApiLog::latest()->take(25)->get();

        $totalRequests = ApiLog::count();
        $successRequests = ApiLog::whereBetween('status_code', [200, 299])->count();
        $avgResponseTime = (int) round(ApiLog::avg('response_time_ms') ?? 0);
        $mobileRequests = ApiLog::where('client_type', 'like', '%Mobile%')
            ->orWhere('client_type', 'like', '%Flutter%')
            ->count();

        $successRate = $totalRequests > 0 ? round(($successRequests / $totalRequests) * 100, 1) : 100;

        return view('admin.api-monitor.index', compact(
            'logs',
            'totalRequests',
            'successRate',
            'avgResponseTime',
            'mobileRequests'
        ));
    }

    /**
     * Truncate the API logs.
     */
    public function clear(): RedirectResponse
    {
        ApiLog::truncate();

        return redirect()->route('admin.api-monitor.index')
            ->with('status', 'Log lalu lintas Web Service berhasil dikosongkan.');
    }
}
