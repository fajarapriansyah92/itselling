<?php

namespace App\Http\Middleware;

use App\Models\ApiLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class LogApiRequests
{
    // Middleware untuk mencatat setiap trafik HTTP Request & mengukur kecepatan respon (QoS)
    public function handle(Request $request, Closure $next): Response
    {
        // Catat waktu awal saat request baru masuk ke server (ibarat nyalain stopwatch)
        $startTime = microtime(true);

        // Teruskan request ke Controller sampai menghasilkan response JSON dari database
        $response = $next($request);

        // Hitung selisih waktu selesai dikurangi waktu mulai, lalu kali 1000 biar satuannya jadi milidetik (ms)
        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        // Jangan log request polling internal agar terminal & tabel log tetap bersih dan hanya mencatat aksi nyata
        if ($request->has('poll') || $request->has('silent') || $request->header('X-Silent-Poll') === '1' || str_starts_with($request->path(), 'admin/api-monitor')) {
            return $response;
        }

        try {
            $userAgent = (string) $request->userAgent();
            $clientPlatformHeader = (string) $request->header('X-Client-Platform', '');
            $clientType = $this->determineClientType($userAgent, $clientPlatformHeader, $request->path());

            $payload = null;
            if ($request->isMethod('post') || $request->isMethod('put') || $request->isMethod('patch')) {
                $sanitized = $request->except(['password', 'password_confirmation', 'token', '_token', '_method']);
                $payload = ! empty($sanitized) ? json_encode($sanitized, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;
            }

            $endpoint = '/'.ltrim($request->path(), '/');
            $queryString = $request->getQueryString();
            $fullEndpoint = $queryString ? "{$endpoint}?{$queryString}" : $endpoint;
            $method = strtoupper($request->method());
            $statusCode = $response->getStatusCode();
            $responseBytes = strlen((string) $response->getContent());

            // Tampilkan log real-time di terminal php artisan serve KHUSUS untuk Web Control Panel (Admin)
            // karena log Mobile (Flutter Client) sudah tampil di terminal flutter run.
            if (! str_contains($clientType, 'Flutter') && ! str_contains($clientType, 'Mobile')) {
                $reqLine = sprintf(
                    '🌐 [HTTP REQUEST]  ---> %s http://%s%s | Client: %s',
                    $method,
                    $request->getHttpHost(),
                    $fullEndpoint,
                    $clientType
                );
                $resLine = sprintf(
                    '✅ [HTTP RESPONSE] <--- %d (%dms) | Data: %d bytes | %s',
                    $statusCode,
                    $durationMs,
                    $responseBytes,
                    $endpoint
                );

                file_put_contents('php://stderr', $reqLine.PHP_EOL.$resLine.PHP_EOL);
            }

            // Simpan hasil pengukuran QoS (status HTTP & durasi ms) ke tabel api_logs di database
            ApiLog::create([
                'method' => $method,
                'endpoint' => $endpoint,
                'status_code' => $statusCode,
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
     * Determine client origin based on User-Agent and X-Client-Platform header.
     */
    private function determineClientType(string $userAgent, string $clientPlatformHeader = '', string $path = ''): string
    {
        if ($clientPlatformHeader !== '') {
            return $clientPlatformHeader;
        }

        if (str_starts_with($path, 'api/v1/admin') || str_starts_with($path, 'admin')) {
            return 'Web Control Panel (Admin)';
        }

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
            return 'Web Control Panel (Admin)';
        }

        return 'REST Client API';
    }
}
