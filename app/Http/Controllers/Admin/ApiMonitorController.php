<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Sanctum\PersonalAccessToken;

class ApiMonitorController extends Controller
{
    // Tampilkan halaman monitor Web Service beserta statistik QoS dan log trafik real-time
    public function index(Request $request): View|JsonResponse
    {
        // Kalau request datang dari auto-refresh (AJAX polling setiap 2 detik), kirim data dalam bentuk JSON
        if ($request->wantsJson() || $request->has('poll')) {
            $latestLogs = ApiLog::latest()->take(25)->get();

            // Hitung metrik performa (QoS) dan jumlah trafik dari masing-masing platform
            $totalRequests = ApiLog::count();
            $successRequests = ApiLog::whereBetween('status_code', [200, 299])->count();
            $avgResponseTime = (int) round(ApiLog::avg('response_time_ms') ?? 0);
            $minResponseTime = (int) (ApiLog::min('response_time_ms') ?? 0);
            $maxResponseTime = (int) (ApiLog::max('response_time_ms') ?? 0);
            $mobileRequests = ApiLog::where('client_type', 'like', '%Mobile%')
                ->orWhere('client_type', 'like', '%Flutter%')
                ->count();
            $webRequests = ApiLog::where('client_type', 'like', '%Web%')
                ->orWhere('client_type', 'like', '%Admin%')
                ->count();

            $successRate = $totalRequests > 0 ? round(($successRequests / $totalRequests) * 100, 1) : 100;

            return response()->json([
                'metrics' => [
                    'total' => number_format($totalRequests),
                    'mobile' => number_format($mobileRequests),
                    'web' => number_format($webRequests),
                    'success_rate' => $successRate,
                    'avg_time' => $avgResponseTime,
                    'min_time' => $minResponseTime,
                    'max_time' => $maxResponseTime,
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

        // Ambil 25 log request terakhir untuk ditampilkan pertama kali saat halaman dibuka
        $logs = ApiLog::latest()->take(25)->get();

        $totalRequests = ApiLog::count();
        $successRequests = ApiLog::whereBetween('status_code', [200, 299])->count();
        $avgResponseTime = (int) round(ApiLog::avg('response_time_ms') ?? 0);
        $minResponseTime = (int) (ApiLog::min('response_time_ms') ?? 0);
        $maxResponseTime = (int) (ApiLog::max('response_time_ms') ?? 0);
        $mobileRequests = ApiLog::where('client_type', 'like', '%Mobile%')
            ->orWhere('client_type', 'like', '%Flutter%')
            ->count();
        $webRequests = ApiLog::where('client_type', 'like', '%Web%')
            ->orWhere('client_type', 'like', '%Admin%')
            ->count();

        $successRate = $totalRequests > 0 ? round(($successRequests / $totalRequests) * 100, 1) : 100;

        // Ambil daftar token aktif yang di-hash SHA-256 di database sebagai bukti keamanan API
        $activeTokensCount = PersonalAccessToken::count();
        $activeTokens = PersonalAccessToken::with('tokenable')->latest()->take(10)->get();

        return view('admin.api-monitor.index', compact(
            'logs',
            'totalRequests',
            'successRate',
            'avgResponseTime',
            'minResponseTime',
            'maxResponseTime',
            'mobileRequests',
            'webRequests',
            'activeTokensCount',
            'activeTokens'
        ));
    }

    // Kosongkan seluruh isi tabel log Web Service kalau tombol Reset Log ditekan
    public function clear(): RedirectResponse
    {
        ApiLog::truncate();

        return redirect()->route('admin.api-monitor.index')
            ->with('status', 'Log lalu lintas Web Service berhasil dikosongkan.');
    }
}
