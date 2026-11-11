<x-layouts.admin title="Web Service Monitor & Traffic Log">
    <div class="space-y-5">
        <!-- Top Action Bar -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="inline-flex items-center gap-2 rounded-full bg-blue-50 border border-blue-200 px-3.5 py-1.5 text-xs font-bold text-[#1B64F2]">
                <span class="size-2 rounded-full bg-[#1B64F2] animate-pulse"></span>
                <span>Arsitektur Client-Server RESTful API</span>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ url('/api') }}" target="_blank"
                   class="inline-flex items-center gap-2 rounded-full border border-[#60A5FA] bg-white px-4 py-1.5 text-xs font-bold text-[#1B64F2] hover:bg-[#1B64F2] hover:text-white transition">
                    <span>Buka Manifest (/api)</span>
                </a>
                <a href="{{ url('/api/v1/products') }}" target="_blank"
                   class="btn-primary rounded-full px-4 py-1.5 text-xs font-bold">
                    <span>Buka JSON Produk</span>
                </a>
                @if ($totalRequests > 0)
                    <form action="{{ route('admin.api-monitor.clear') }}" method="POST" onsubmit="return confirm('Kosongkan semua log lalu lintas Web Service?');">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 rounded-full border border-rose-200 bg-rose-50 px-4 py-1.5 text-xs font-bold text-rose-600 hover:bg-rose-100 transition cursor-pointer">
                            <span>Reset Log</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Top 4 KPI Metrics Cards (Medlio Style) -->
        <section class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Card 1: Total Request Masuk (Solid Royal Blue) -->
            <div style="background: linear-gradient(135deg, #1E64F0 0%, #1552D6 100%);"
                 class="bg-[#1B64F2] rounded-2xl p-5 text-white shadow-lg shadow-blue-600/20 flex flex-col justify-between">
                <div class="flex items-start justify-between gap-3">
                    <span style="color: #FFFFFF !important;" class="text-sm font-bold">Total Request</span>
                    <div class="size-9 rounded-xl bg-white text-[#1B64F2] flex items-center justify-center shadow-xs shrink-0">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-2">
                    <p id="metric-total" style="color: #FFFFFF !important;" class="text-3xl font-extrabold tabular-nums">{{ number_format($totalRequests) }}</p>
                    <p style="color: #DBEAFE !important;" class="text-xs font-medium mt-1">Lalu lintas tercatat (Max 25)</p>
                </div>
            </div>

            <!-- Card 2: Request dari Mobile -->
            <div class="card p-5 bg-white flex flex-col justify-between">
                <div class="flex items-start justify-between gap-3">
                    <span class="text-sm font-bold text-slate-800">Request Mobile</span>
                    <div class="size-6 rounded-md bg-[#1B64F2] text-white flex items-center justify-center shrink-0">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-2">
                    <p id="metric-mobile" class="text-2xl font-extrabold text-slate-900 tabular-nums">{{ number_format($mobileRequests) }}</p>
                    <p class="text-xs font-medium text-slate-400 mt-1">Aplikasi Flutter Android</p>
                </div>
            </div>

            <!-- Card 3: Success Rate -->
            <div class="card p-5 bg-white flex flex-col justify-between">
                <div class="flex items-start justify-between gap-3">
                    <span class="text-sm font-bold text-slate-800">Success Rate</span>
                    <div class="size-6 rounded-md bg-[#1B64F2] text-white flex items-center justify-center shrink-0">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-2">
                    <p id="metric-success-rate" class="text-2xl font-extrabold text-emerald-600 tabular-nums">{{ $successRate }}%</p>
                    <p class="text-xs font-medium text-slate-400 mt-1">Status HTTP 2xx OK</p>
                </div>
            </div>

            <!-- Card 4: Rata-Rata Respon -->
            <div class="card p-5 bg-white flex flex-col justify-between">
                <div class="flex items-start justify-between gap-3">
                    <span class="text-sm font-bold text-slate-800">Rata-Rata Respon</span>
                    <div class="size-6 rounded-md bg-[#1B64F2] text-white flex items-center justify-center shrink-0">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-2xl font-extrabold text-slate-900 tabular-nums">
                        <span id="metric-avg-time">{{ $avgResponseTime }}</span>
                        <span class="text-xs font-semibold text-slate-400">ms</span>
                    </p>
                    <p class="text-xs font-medium text-slate-400 mt-1">Kecepatan server API</p>
                </div>
            </div>
        </section>

        <!-- Visual Architecture Diagram Card -->
        <section class="card p-5 bg-white">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900">Diagram Alur Arsitektur RESTful Web Service</h2>
                    <p class="text-xs text-slate-400">Pertukaran data JSON mandiri antara aplikasi mobile dan server Laravel</p>
                </div>
                <span class="rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1 text-[11px] font-bold text-emerald-700">
                    REST Web Server: STANDALONE & ACTIVE
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                <!-- Client Node -->
                <div class="rounded-2xl border border-blue-100 bg-[#F8FAFC] p-4 text-center">
                    <div class="size-10 rounded-xl bg-[#EBF3FF] text-[#1B64F2] mx-auto flex items-center justify-center mb-2">
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <p class="font-extrabold text-slate-900 text-xs sm:text-sm">CLIENT</p>
                    <p class="text-xs text-[#1B64F2] font-bold mt-0.5">Flutter Mobile App (Android)</p>
                    <div class="mt-2 text-[10px] text-slate-500 font-mono bg-white border border-slate-200/80 rounded-lg py-1 px-2">
                        Mengirim HTTP Request
                    </div>
                </div>

                <!-- Web Service Flow Center -->
                <div class="flex flex-col items-center justify-center px-2 py-2 text-center space-y-2">
                    <div class="w-full flex items-center justify-center gap-2 text-xs font-mono font-bold text-[#1B64F2] bg-blue-50/70 rounded-xl py-1.5 px-3 border border-blue-200">
                        <span>HTTP Request (JSON)</span>
                        <span>&rarr;</span>
                    </div>
                    <div class="size-10 rounded-xl bg-[#1B64F2] text-white flex items-center justify-center shadow-md shadow-blue-600/20">
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                    </div>
                    <div class="w-full flex items-center justify-center gap-2 text-xs font-mono font-bold text-emerald-700 bg-emerald-50/80 rounded-xl py-1.5 px-3 border border-emerald-200">
                        <span>&larr;</span>
                        <span>HTTP Response (JSON)</span>
                    </div>
                </div>

                <!-- Rest Web Server Node -->
                <div class="rounded-2xl border border-blue-100 bg-[#F8FAFC] p-4 text-center">
                    <div class="size-10 rounded-xl bg-[#EBF3FF] text-[#1B64F2] mx-auto flex items-center justify-center mb-2">
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <p class="font-extrabold text-slate-900 text-xs sm:text-sm">REST WEB SERVER</p>
                    <p class="text-xs text-[#1B64F2] font-bold mt-0.5">Laravel REST API (/api/v1)</p>
                    <div class="mt-2 text-[10px] text-slate-500 font-mono bg-white border border-slate-200/80 rounded-lg py-1 px-2">
                        Terhubung ke Database & Gateway
                    </div>
                </div>
            </div>
        </section>

        <!-- Live Traffic Log Table Card -->
        <section class="card overflow-hidden bg-white">
            <div class="p-4 sm:px-6 border-b border-slate-100 bg-[#F8FAFC] flex flex-wrap items-center justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-sm sm:text-base font-extrabold text-slate-900">Lalu Lintas HTTP Request & Response Real-Time</h2>
                        <span id="live-indicator" class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 text-[10px] font-bold text-emerald-700">
                            <span class="size-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                            LIVE STREAM
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">Memantau koneksi secara otomatis setiap 2 detik tanpa refresh manual.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button id="toggle-poll-btn" onclick="toggleAutoRefresh()"
                            class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3.5 py-1.5 text-xs font-bold text-slate-600 hover:border-blue-300 transition cursor-pointer">
                        <span id="poll-dot" class="size-2 rounded-full bg-emerald-500"></span>
                        <span id="poll-text">Auto: ON (2s)</span>
                    </button>
                    <button onclick="fetchLatestLogs(true)"
                            class="inline-flex items-center gap-1.5 rounded-full border border-[#60A5FA] bg-white px-3.5 py-1.5 text-xs font-bold text-[#1B64F2] hover:bg-[#1B64F2] hover:text-white transition cursor-pointer">
                        <span>Ambil Sekarang</span>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th class="text-left">Waktu</th>
                            <th class="text-left">Method</th>
                            <th class="text-left">Endpoint Web Service</th>
                            <th class="text-left">Client Asal</th>
                            <th class="text-center">Status HTTP</th>
                            <th class="text-right">Durasi</th>
                        </tr>
                    </thead>
                    <tbody id="logs-table-body" class="font-mono text-xs">
                        @forelse ($logs as $log)
                            <tr class="transition" data-log-id="{{ $log->id }}">
                                <td class="whitespace-nowrap text-slate-600">
                                    <span class="font-bold text-slate-800">{{ $log->created_at->format('H:i:s') }}</span>
                                    <span class="text-[10px] text-slate-400 block">{{ $log->created_at->format('d/m/Y') }}</span>
                                </td>
                                <td class="whitespace-nowrap">
                                    @php
                                        $methodColors = [
                                            'GET' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'POST' => 'bg-blue-50 text-[#1B64F2] border-blue-200',
                                            'PUT' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'DELETE' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        ];
                                        $color = $methodColors[$log->method] ?? 'bg-slate-100 text-slate-600 border-slate-200';
                                    @endphp
                                    <span class="rounded-lg border px-2.5 py-1 text-[11px] font-extrabold {{ $color }}">
                                        {{ $log->method }}
                                    </span>
                                </td>
                                <td class="font-bold text-slate-800">
                                    {{ $log->endpoint }}
                                    @if ($log->request_payload)
                                        <details class="mt-1 text-[10px] text-slate-500 cursor-pointer font-sans">
                                            <summary class="font-bold text-[#1B64F2] hover:underline">Lihat Payload</summary>
                                            <pre class="mt-1 p-2.5 bg-[#F8FAFC] border border-slate-200 rounded-xl text-slate-700 font-mono whitespace-pre-wrap">{{ $log->request_payload }}</pre>
                                        </details>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap font-sans">
                                    @if (str_contains($log->client_type, 'Flutter') || str_contains($log->client_type, 'Mobile'))
                                        <span class="inline-flex items-center gap-1.5 font-bold text-[#1B64F2]">
                                            <span class="size-2 rounded-full bg-[#1B64F2]"></span>
                                            {{ $log->client_type }}
                                        </span>
                                    @elseif (str_contains($log->client_type, 'Browser'))
                                        <span class="inline-flex items-center gap-1.5 font-semibold text-slate-600">
                                            <span class="size-2 rounded-full bg-slate-400"></span>
                                            {{ $log->client_type }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 font-bold text-amber-600">
                                            <span class="size-2 rounded-full bg-amber-500"></span>
                                            {{ $log->client_type }}
                                        </span>
                                    @endif
                                    <span class="text-[10px] text-slate-400 block font-mono">{{ $log->ip_address }}</span>
                                </td>
                                <td class="whitespace-nowrap text-center">
                                    @if ($log->status_code >= 200 && $log->status_code < 300)
                                        <span class="rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 text-[11px] font-bold">
                                            {{ $log->status_code }} OK
                                        </span>
                                    @elseif ($log->status_code >= 400 && $log->status_code < 500)
                                        <span class="rounded-full bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-0.5 text-[11px] font-bold">
                                            {{ $log->status_code }} Client Err
                                        </span>
                                    @else
                                        <span class="rounded-full bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-0.5 text-[11px] font-bold">
                                            {{ $log->status_code }} Server Err
                                        </span>
                                    @endif
                                </td>
                                <td class="text-right whitespace-nowrap font-mono font-bold text-slate-700">
                                    {{ $log->response_time_ms }} ms
                                </td>
                            </tr>
                        @empty
                            <tr id="empty-row">
                                <td colspan="6" class="py-12 text-center font-sans">
                                    <p class="font-bold text-slate-700 text-sm">Belum ada aktivitas Web Service yang tercatat.</p>
                                    <p class="text-xs text-slate-400 mt-1">Buka aplikasi Android (Flutter) atau klik tombol <strong class="text-[#1B64F2]">"Buka JSON Produk"</strong> di kanan atas untuk menguji request pertama.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <!-- Client-side Realtime Script -->
    <script>
        let isPolling = true;
        let pollTimer = null;
        let lastKnownId = {{ $logs->first()?->id ?? 0 }};

        function toggleAutoRefresh() {
            isPolling = !isPolling;
            const dot = document.getElementById('poll-dot');
            const text = document.getElementById('poll-text');
            const indicator = document.getElementById('live-indicator');

            if (isPolling) {
                dot.className = 'size-2 rounded-full bg-emerald-500';
                text.textContent = 'Auto: ON (2s)';
                indicator.style.display = 'inline-flex';
                startPolling();
            } else {
                dot.className = 'size-2 rounded-full bg-slate-400';
                text.textContent = 'Auto: PAUSED';
                indicator.style.display = 'none';
                clearInterval(pollTimer);
            }
        }

        async function fetchLatestLogs(force = false) {
            try {
                const response = await fetch("{{ route('admin.api-monitor.index') }}?poll=1", {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) return;

                const data = await response.json();

                if (data.metrics) {
                    document.getElementById('metric-total').textContent = data.metrics.total;
                    document.getElementById('metric-mobile').textContent = data.metrics.mobile;
                    document.getElementById('metric-success-rate').textContent = data.metrics.success_rate + '%';
                    document.getElementById('metric-avg-time').textContent = data.metrics.avg_time;
                }

                if (!data.logs || data.logs.length === 0) return;

                const newestLog = data.logs[0];
                if (!force && newestLog.id <= lastKnownId) {
                    return;
                }

                lastKnownId = newestLog.id;
                renderRows(data.logs);
            } catch (err) {
                console.error("Gagal polling API monitor:", err);
            }
        }

        function renderRows(logs) {
            const tbody = document.getElementById('logs-table-body');
            const emptyRow = document.getElementById('empty-row');
            if (emptyRow) emptyRow.remove();

            const rowsHtml = logs.map(log => {
                let methodColor = 'bg-slate-100 text-slate-600 border-slate-200';
                if (log.method === 'GET') methodColor = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                else if (log.method === 'POST') methodColor = 'bg-blue-50 text-[#1B64F2] border-blue-200';
                else if (log.method === 'PUT') methodColor = 'bg-amber-50 text-amber-700 border-amber-200';
                else if (log.method === 'DELETE') methodColor = 'bg-rose-50 text-rose-700 border-rose-200';

                let clientBadge = `<span class="inline-flex items-center gap-1.5 font-bold text-amber-600"><span class="size-2 rounded-full bg-amber-500"></span>${escapeHtml(log.client_type)}</span>`;
                if (log.client_type.includes('Flutter') || log.client_type.includes('Mobile')) {
                    clientBadge = `<span class="inline-flex items-center gap-1.5 font-bold text-[#1B64F2]"><span class="size-2 rounded-full bg-[#1B64F2]"></span>${escapeHtml(log.client_type)}</span>`;
                } else if (log.client_type.includes('Browser')) {
                    clientBadge = `<span class="inline-flex items-center gap-1.5 font-semibold text-slate-600"><span class="size-2 rounded-full bg-slate-400"></span>${escapeHtml(log.client_type)}</span>`;
                }

                let statusBadge = `<span class="rounded-full bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-0.5 text-[11px] font-bold">${log.status_code} Server Err</span>`;
                if (log.status_code >= 200 && log.status_code < 300) {
                    statusBadge = `<span class="rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 text-[11px] font-bold">${log.status_code} OK</span>`;
                } else if (log.status_code >= 400 && log.status_code < 500) {
                    statusBadge = `<span class="rounded-full bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-0.5 text-[11px] font-bold">${log.status_code} Client Err</span>`;
                }

                let payloadHtml = '';
                if (log.payload) {
                    payloadHtml = `
                        <details class="mt-1 text-[10px] text-slate-500 cursor-pointer font-sans">
                            <summary class="font-bold text-[#1B64F2] hover:underline">Lihat Payload</summary>
                            <pre class="mt-1 p-2.5 bg-[#F8FAFC] border border-slate-200 rounded-xl text-slate-700 font-mono whitespace-pre-wrap">${escapeHtml(log.payload)}</pre>
                        </details>
                    `;
                }

                return `
                    <tr class="transition" data-log-id="${log.id}">
                        <td class="whitespace-nowrap text-slate-600">
                            <span class="font-bold text-slate-800">${log.time}</span>
                            <span class="text-[10px] text-slate-400 block">${log.date}</span>
                        </td>
                        <td class="whitespace-nowrap">
                            <span class="rounded-lg border px-2.5 py-1 text-[11px] font-extrabold ${methodColor}">
                                ${log.method}
                            </span>
                        </td>
                        <td class="font-bold text-slate-800">
                            ${escapeHtml(log.endpoint)}
                            ${payloadHtml}
                        </td>
                        <td class="whitespace-nowrap font-sans">
                            ${clientBadge}
                            <span class="text-[10px] text-slate-400 block font-mono">${escapeHtml(log.ip_address || '-')}</span>
                        </td>
                        <td class="whitespace-nowrap text-center">
                            ${statusBadge}
                        </td>
                        <td class="text-right whitespace-nowrap font-mono font-bold text-slate-700">
                            ${log.response_time_ms} ms
                        </td>
                    </tr>
                `;
            }).join('');

            tbody.innerHTML = rowsHtml;
        }

        function escapeHtml(string) {
            if (!string) return '';
            return String(string)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function startPolling() {
            clearInterval(pollTimer);
            pollTimer = setInterval(() => {
                if (isPolling) {
                    fetchLatestLogs();
                }
            }, 2000);
        }

        document.addEventListener('DOMContentLoaded', () => {
            startPolling();
        });
    </script>
</x-layouts.admin>
