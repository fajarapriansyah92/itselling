<?php

namespace App\Http\Middleware;

use App\Models\ApiLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class LogApiRequests
{
    /**
     * Handle an incoming request and log its Web Service activity.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        try {
            $userAgent = (string) $request->userAgent();
            $clientType = $this->determineClientType($userAgent);

            $payload = null;
            if ($request->isMethod('post') || $request->isMethod('put') || $request->isMethod('patch')) {
                $sanitized = $request->except(['password', 'password_confirmation', 'token']);
                $payload = ! empty($sanitized) ? json_encode($sanitized, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;
            }

            ApiLog::create([
                'method' => strtoupper($request->method()),
                'endpoint' => '/'.ltrim($request->path(), '/'),
                'status_code' => $response->getStatusCode(),
                'ip_address' => $request->ip(),
                'user_agent' => substr($userAgent, 0, 255),
                'client_type' => $clientType,
                'response_time_ms' => $durationMs,
                'request_payload' => $payload,
            ]);

            // Rolling buffer: jaga database tetap bersih (maksimal 25 riwayat terbaru)
            // Log lama yang terlewat akan otomatis terhapus agar database tidak bengkak
            $maxLogs = 25;
            $oldLogIds = ApiLog::latest('id')->skip($maxLogs)->pluck('id');
            if ($oldLogIds->isNotEmpty()) {
                ApiLog::whereIn('id', $oldLogIds)->delete();
            }
        } catch (Throwable) {
            // Logging should never interrupt the API response.
        }

        return $response;
    }

    /**
     * Determine client origin based on User-Agent.
     */
    private function determineClientType(string $userAgent): string
    {
        $ua = strtolower($userAgent);

        if (str_contains($ua, 'dart') || str_contains($ua, 'flutter')) {
            return 'Mobile (Flutter Client)';
        }

        if (str_contains($ua, 'postman')) {
            return 'Postman API Tester';
        }

        if (str_contains($ua, 'curl')) {
            return 'cURL Terminal';
        }

        if (str_contains($ua, 'mozilla') || str_contains($ua, 'chrome') || str_contains($ua, 'safari')) {
            return 'Web Browser / Inspector';
        }

        return 'REST Client API';
    }
}
