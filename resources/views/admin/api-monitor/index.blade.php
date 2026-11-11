<x-layouts.admin title="Web Service Monitor & Traffic Log">
    <div class="space-y-5">
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

            <!-- Card 2: Request 2 Platform (Mobile + Web) -->
            <div class="card p-5 bg-white flex flex-col justify-between">
                <div class="flex items-start justify-between gap-3">
                    <span class="text-sm font-bold text-slate-800">2 Platform Client</span>
                    <div class="size-6 rounded-md bg-[#1B64F2] text-white flex items-center justify-center shrink-0">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-2">
                    <div class="flex items-baseline gap-2">
                        <p class="text-lg font-extrabold text-[#1B64F2] tabular-nums"><span id="metric-mobile">{{ number_format($mobileRequests) }}</span> <span class="text-[11px] font-semibold text-slate-500">Mobile</span></p>
                        <span class="text-slate-300">•</span>
                        <p class="text-lg font-extrabold text-emerald-600 tabular-nums"><span id="metric-web">{{ number_format($webRequests ?? 0) }}</span> <span class="text-[11px] font-semibold text-slate-500">Web</span></p>
                    </div>
                    <p class="text-xs font-medium text-slate-400 mt-1">Flutter Android & Web Admin</p>
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
                    <h2 class="text-sm font-extrabold text-slate-900">Diagram Alur Arsitektur Multi-Platform RESTful Web Service</h2>
                    <p class="text-xs text-slate-400">Satu Web Service terpusat melayani 2 Platform sekaligus: Aplikasi Mobile (Flutter Android) & Web Control Panel (Admin)</p>
                </div>
                <span class="rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1 text-[11px] font-bold text-emerald-700">
                    MULTI-PLATFORM REST API: ACTIVE
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                <!-- 2 Client Platforms Node -->
                <div class="space-y-2.5">
                    <div class="rounded-2xl border border-blue-100 bg-[#F8FAFC] p-3 text-center">
                        <p class="font-extrabold text-slate-900 text-xs">PLATFORM 1: MOBILE CLIENT</p>
                        <p class="text-xs text-[#1B64F2] font-bold mt-0.5">Flutter Android App (Pelanggan)</p>
                        <div class="mt-1.5 text-[10px] text-slate-500 font-mono bg-white border border-slate-200/80 rounded-lg py-1 px-2">
                            Endpoint: /api/v1/*
                        </div>
                    </div>
                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50/30 p-3 text-center">
                        <p class="font-extrabold text-slate-900 text-xs">PLATFORM 2: WEB CLIENT</p>
                        <p class="text-xs text-emerald-600 font-bold mt-0.5">Web Control Panel (Admin)</p>
                        <div class="mt-1.5 text-[10px] text-slate-500 font-mono bg-white border border-slate-200/80 rounded-lg py-1 px-2">
                            Endpoint: /api/v1/admin/*
                        </div>
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

        <!-- Grid 2 Kolom: Keamanan API (Enkripsi Token) & Pengujian Langsung QoS Tanpa Postman -->
        <section style="align-items: start;" class="grid grid-cols-1 lg:grid-cols-2 gap-5 items-start">
            <!-- Panel 1: Keamanan API & Enkripsi Bearer Token (SHA-256) -->
            <div class="card p-5 bg-white flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <h2 class="text-sm font-extrabold text-slate-900">1. Keamanan API & Enkripsi Token</h2>
                        <span class="rounded-full bg-blue-50 border border-blue-200 px-2.5 py-0.5 text-[10px] font-bold text-[#1B64F2]">
                            SHA-256 + Bearer Auth
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Setiap pertukaran data transaksi, keranjang, dan chat dilindungi oleh middleware <code class="font-mono font-bold text-slate-700">auth:sanctum</code>. Token login pelanggan dienkripsi menggunakan algoritma satu arah <strong>Hash SHA-256</strong> di database.
                    </p>

                    <!-- Alur Keamanan 3 Langkah -->
                    <div class="mt-3.5 grid grid-cols-3 gap-2 text-center">
                        <div class="rounded-xl border border-slate-200/80 bg-[#F8FAFC] p-2.5">
                            <span class="text-[10px] font-extrabold text-[#1B64F2] block">LANGKAH 1</span>
                            <p class="text-xs font-bold text-slate-800 mt-0.5">Login User</p>
                            <p class="text-[10px] text-slate-500 mt-0.5">Cek Email & Hash Password</p>
                        </div>
                        <div class="rounded-xl border border-slate-200/80 bg-[#F8FAFC] p-2.5">
                            <span class="text-[10px] font-extrabold text-[#1B64F2] block">LANGKAH 2</span>
                            <p class="text-xs font-bold text-slate-800 mt-0.5">Enkripsi Token</p>
                            <p class="text-[10px] text-slate-500 mt-0.5">Simpan Hash SHA-256 di DB</p>
                        </div>
                        <div class="rounded-xl border border-slate-200/80 bg-[#F8FAFC] p-2.5">
                            <span class="text-[10px] font-extrabold text-emerald-600 block">LANGKAH 3</span>
                            <p class="text-xs font-bold text-slate-800 mt-0.5">Validasi Header</p>
                            <p class="text-[10px] text-slate-500 mt-0.5">Tolak (401) Jika Tanpa Token</p>
                        </div>
                    </div>
                </div>

                <!-- Bukti Hash Token Aktif di Database -->
                <div class="rounded-xl border border-blue-100 bg-[#F8FAFC] p-3.5 font-mono text-[11px]">
                    <div class="flex items-center justify-between text-slate-500 text-[10px] mb-1.5 pb-1.5 border-b border-slate-200/80">
                        <span class="font-bold text-slate-600">BUKTI ENKRIPSI DATABASE (personal_access_tokens)</span>
                        <span class="rounded-full bg-emerald-50 border border-emerald-200 px-2 py-0.5 text-[10px] text-emerald-700 font-bold">{{ $activeTokensCount ?? 0 }} Token Aktif</span>
                    </div>
                    @if (!empty($activeTokens) && $activeTokens->isNotEmpty())
                        <div style="max-height: 120px; overflow-y: auto;" class="space-y-2 pr-1">
                            @foreach ($activeTokens as $tokenItem)
                                <div class="bg-white border border-slate-200/80 rounded-lg p-2 space-y-0.5 text-[11px]">
                                    <p><span class="text-slate-500">User/Device:</span> <span class="text-[#1B64F2] font-bold">{{ $tokenItem->tokenable?->name ?? 'Pelanggan' }} ({{ $tokenItem->name }})</span></p>
                                    <p style="word-break: break-all;"><span class="text-slate-500">SHA-256 Hash:</span> <span class="text-slate-800 font-semibold">{{ $tokenItem->token }}</span></p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-slate-500 text-[11px]">Belum ada sesi token login aktif. Silakan login dari aplikasi Android untuk melihat hasil hash SHA-256.</p>
                    @endif
                </div>
            </div>

            <!-- Panel 2: Pengujian Langsung Endpoint & Kualitas Layanan (QoS) Tanpa Postman -->
            <div class="card p-5 bg-white flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <h2 class="text-sm font-extrabold text-slate-900">2. Uji Langsung Web Service & QoS (Tanpa Postman)</h2>
                        <span class="rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 text-[10px] font-bold text-emerald-700">
                            Built-In API Tester
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Uji performa <strong>Quality of Service (QoS)</strong> dan response JSON secara langsung dari panel ini tanpa aplikasi pihak ketiga (Postman).
                    </p>

                    <!-- Ringkasan Indikator QoS -->
                    <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                        <div class="rounded-xl border border-slate-200/80 bg-[#F8FAFC] p-2">
                            <span class="text-[10px] text-slate-400 font-semibold block">Respon Tercepat</span>
                            <span id="qos-min-time" class="text-xs font-extrabold text-emerald-600 font-mono">{{ $minResponseTime ?? 0 }} ms</span>
                        </div>
                        <div class="rounded-xl border border-slate-200/80 bg-[#F8FAFC] p-2">
                            <span class="text-[10px] text-slate-400 font-semibold block">Respon Terlambat</span>
                            <span id="qos-max-time" class="text-xs font-extrabold text-slate-700 font-mono">{{ $maxResponseTime ?? 0 }} ms</span>
                        </div>
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-2">
                            <span class="text-[10px] text-emerald-700 font-semibold block">Kategori QoS</span>
                            <span class="text-xs font-extrabold text-emerald-700">
                                {{ ($avgResponseTime ?? 0) < 200 ? 'Sangat Baik (A)' : 'Baik (B)' }}
                            </span>
                        </div>
                    </div>

                    <!-- Form Pilih Endpoint & Tombol Eksekusi -->
                    <div class="mt-3 flex flex-col sm:flex-row gap-2">
                        <select id="tester-endpoint" class="w-full rounded-xl border border-slate-200 bg-[#F8FAFC] px-3 py-2 text-xs font-mono font-bold text-slate-800 focus:border-[#1B64F2] focus:outline-none">
                            <option value="/api/v1/products">[Mobile API] GET /api/v1/products (Katalog Produk)</option>
                            <option value="/api/v1/categories">[Mobile API] GET /api/v1/categories (Daftar Kategori)</option>
                            <option value="/api/v1/admin/dashboard">[Web Admin API] GET /api/v1/admin/dashboard (Statistik)</option>
                            <option value="/api/v1/admin/orders">[Web Admin API] GET /api/v1/admin/orders (Data Pesanan)</option>
                            <option value="/api/v1/admin/reports/sales">[Web Admin API] GET /api/v1/admin/reports/sales (Laporan)</option>
                            <option value="/api/v1/orders">[Uji Keamanan] GET /api/v1/orders (Tanpa Token -> 401)</option>
                        </select>
                        <button type="button" onclick="runBuiltInApiTest()" id="tester-btn"
                                class="btn-primary rounded-xl px-4 py-2 text-xs font-bold whitespace-nowrap cursor-pointer shrink-0">
                            Uji Endpoint
                        </button>
                    </div>
                </div>

                <!-- Kotak Output Hasil Uji QoS & JSON (Dengan Scrollbar Sendiri & Tema Terang Senada) -->
                <div class="rounded-xl border border-blue-100 bg-[#F8FAFC] p-3.5 font-mono text-[11px]">
                    <div class="flex flex-wrap items-center justify-between gap-2 text-[10px] text-slate-500 mb-2 pb-1.5 border-b border-slate-200/80">
                        <span id="tester-status-label" class="font-bold text-slate-700">HASIL UJI: Pilih endpoint lalu klik "Uji Endpoint"</span>
                        <span id="tester-qos-badge" class="rounded-full bg-emerald-50 border border-emerald-200 px-2 py-0.5 text-[10px] text-emerald-700 font-bold">Siap Diuji</span>
                    </div>
                    <pre id="tester-json-output" style="max-height: 140px; overflow-y: auto; overflow-x: auto; margin: 0;" class="text-[11px] text-slate-700 bg-white border border-slate-200/80 rounded-lg p-2.5 whitespace-pre-wrap leading-relaxed">// Output JSON dari Web Service akan tampil di sini...</pre>
                </div>
            </div>
        </section>

        <!-- Panel 3: Tabel Spesifikasi Katalog Endpoint (Alur Input -> Metode -> Output) -->
        <section class="card overflow-hidden bg-white">
            <div class="p-4 sm:px-6 border-b border-slate-100 bg-[#F8FAFC] flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900">3. Spesifikasi Endpoint Web Service (Alur Input &rarr; Metode &rarr; Output)</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Dokumentasi parameter Input, proses Metode di server, dan format JSON Output pada kedua platform</p>
                </div>
                <span class="rounded-full bg-blue-50 border border-blue-200 px-3 py-1 text-[10px] font-bold text-[#1B64F2]">
                    Format Pertukaran Data: JSON
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="table-base text-xs">
                    <thead>
                        <tr>
                            <th class="text-left">Method & Endpoint</th>
                            <th class="text-left">Platform & Keamanan</th>
                            <th class="text-left">Input (Parameter Request)</th>
                            <th class="text-left">Metode (Proses Web Service)</th>
                            <th class="text-left">Output (Response JSON)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr>
                            <td class="font-mono font-bold text-slate-800 whitespace-nowrap">
                                <span class="rounded bg-blue-50 text-[#1B64F2] border border-blue-200 px-2 py-0.5 text-[10px] mr-1">POST</span>
                                /api/v1/auth/login
                            </td>
                            <td class="whitespace-nowrap">
                                <span class="font-bold text-[#1B64F2] block">Mobile Flutter</span>
                                <span class="text-[10px] text-slate-400">Public (Generate Token)</span>
                            </td>
                            <td class="font-mono text-[11px] text-slate-600">email, password, device_name</td>
                            <td class="text-slate-600">Validasi kredensial user dengan <code class="font-mono text-slate-800">Hash::check()</code> & buat token SHA-256</td>
                            <td class="font-mono text-[11px] text-emerald-700">200 OK: { token, token_type: "Bearer", user }</td>
                        </tr>
                        <tr>
                            <td class="font-mono font-bold text-slate-800 whitespace-nowrap">
                                <span class="rounded bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 text-[10px] mr-1">GET</span>
                                /api/v1/products
                            </td>
                            <td class="whitespace-nowrap">
                                <span class="font-bold text-[#1B64F2] block">Mobile Flutter</span>
                                <span class="text-[10px] text-slate-400">Public REST API</span>
                            </td>
                            <td class="font-mono text-[11px] text-slate-600">?search=, ?category_id= (Opsional)</td>
                            <td class="text-slate-600">Query produk aktif beserta relasi kategori & rata-rata rating ulasan</td>
                            <td class="font-mono text-[11px] text-emerald-700">200 OK: { data: [ id, name, price, stock... ] }</td>
                        </tr>
                        <tr>
                            <td class="font-mono font-bold text-slate-800 whitespace-nowrap">
                                <span class="rounded bg-blue-50 text-[#1B64F2] border border-blue-200 px-2 py-0.5 text-[10px] mr-1">POST</span>
                                /api/v1/orders
                            </td>
                            <td class="whitespace-nowrap">
                                <span class="font-bold text-[#1B64F2] block">Mobile Flutter</span>
                                <span class="text-[10px] text-amber-600 font-bold">Bearer Token (Protected)</span>
                            </td>
                            <td class="font-mono text-[11px] text-slate-600">items[product_id, quantity], payment_method, delivery_address</td>
                            <td class="text-slate-600">Cek stok di DB, hitung total harga, potong stok, buat nomor invoice & VA</td>
                            <td class="font-mono text-[11px] text-emerald-700">201 Created: { order_number, total_amount, va_number }</td>
                        </tr>
                        <tr>
                            <td class="font-mono font-bold text-slate-800 whitespace-nowrap">
                                <span class="rounded bg-blue-50 text-[#1B64F2] border border-blue-200 px-2 py-0.5 text-[10px] mr-1">POST</span>
                                /api/v1/orders/{no}/messages
                            </td>
                            <td class="whitespace-nowrap">
                                <span class="font-bold text-[#1B64F2] block">Mobile Flutter</span>
                                <span class="text-[10px] text-amber-600 font-bold">Bearer Token (Protected)</span>
                            </td>
                            <td class="font-mono text-[11px] text-slate-600">message, attachment (Opsional)</td>
                            <td class="text-slate-600">Validasi kepemilikan pesanan & simpan pesan diskusi ke tabel <code class="font-mono text-slate-800">order_messages</code></td>
                            <td class="font-mono text-[11px] text-emerald-700">201 Created: { id, sender, message, created_at }</td>
                        </tr>
                        <tr>
                            <td class="font-mono font-bold text-slate-800 whitespace-nowrap">
                                <span class="rounded bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 text-[10px] mr-1">GET</span>
                                /api/v1/admin/dashboard
                            </td>
                            <td class="whitespace-nowrap">
                                <span class="font-bold text-emerald-600 block">Web Control Panel</span>
                                <span class="text-[10px] text-slate-400">Admin Web Service</span>
                            </td>
                            <td class="font-mono text-[11px] text-slate-600">Header Accept: application/json</td>
                            <td class="text-slate-600">Agregasi total pendapatan, jumlah pesanan, produk, dan pelanggan dari DB</td>
                            <td class="font-mono text-[11px] text-emerald-700">200 OK: { platform, data: { revenue, total_orders... } }</td>
                        </tr>
                        <tr>
                            <td class="font-mono font-bold text-slate-800 whitespace-nowrap">
                                <span class="rounded bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 text-[10px] mr-1">GET</span>
                                /api/v1/admin/reports/sales
                            </td>
                            <td class="whitespace-nowrap">
                                <span class="font-bold text-emerald-600 block">Web Control Panel</span>
                                <span class="text-[10px] text-slate-400">Admin Web Service</span>
                            </td>
                            <td class="font-mono text-[11px] text-slate-600">?from=YYYY-MM-DD, ?to=YYYY-MM-DD</td>
                            <td class="text-slate-600">Filter pesanan selesai per rentang tanggal & hitung rekap item terjual</td>
                            <td class="font-mono text-[11px] text-emerald-700">200 OK: { summary: { orders_count, revenue... } }</td>
                        </tr>
                    </tbody>
                </table>
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
                                            <pre style="max-height: 140px; overflow-y: auto;" class="mt-1 p-2.5 bg-[#F8FAFC] border border-slate-200 rounded-xl text-slate-700 font-mono whitespace-pre-wrap">{{ $log->request_payload }}</pre>
                                        </details>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap font-sans">
                                    @if (str_contains($log->client_type, 'Flutter') || str_contains($log->client_type, 'Mobile'))
                                        <span class="inline-flex items-center gap-1.5 font-bold text-[#1B64F2]">
                                            <span class="size-2 rounded-full bg-[#1B64F2]"></span>
                                            {{ $log->client_type }}
                                        </span>
                                    @elseif (str_contains($log->client_type, 'Web') || str_contains($log->client_type, 'Admin'))
                                        <span class="inline-flex items-center gap-1.5 font-bold text-emerald-600">
                                            <span class="size-2 rounded-full bg-emerald-500"></span>
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
                                    <p class="text-xs text-slate-400 mt-1">Buka aplikasi Android (Flutter) atau klik tombol <strong class="text-[#1B64F2]">"Uji Endpoint"</strong> pada panel di atas untuk menguji request pertama.</p>
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
                    if (document.getElementById('metric-web') && data.metrics.web !== undefined) {
                        document.getElementById('metric-web').textContent = data.metrics.web;
                    }
                    document.getElementById('metric-success-rate').textContent = data.metrics.success_rate + '%';
                    document.getElementById('metric-avg-time').textContent = data.metrics.avg_time;
                    if (document.getElementById('qos-min-time') && data.metrics.min_time !== undefined) {
                        document.getElementById('qos-min-time').textContent = data.metrics.min_time + ' ms';
                    }
                    if (document.getElementById('qos-max-time') && data.metrics.max_time !== undefined) {
                        document.getElementById('qos-max-time').textContent = data.metrics.max_time + ' ms';
                    }
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
                } else if (log.client_type.includes('Web') || log.client_type.includes('Admin')) {
                    clientBadge = `<span class="inline-flex items-center gap-1.5 font-bold text-emerald-600"><span class="size-2 rounded-full bg-emerald-500"></span>${escapeHtml(log.client_type)}</span>`;
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
                            <pre style="max-height: 140px; overflow-y: auto;" class="mt-1 p-2.5 bg-[#F8FAFC] border border-slate-200 rounded-xl text-slate-700 font-mono whitespace-pre-wrap">${escapeHtml(log.payload)}</pre>
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

        // Fungsi untuk menguji endpoint Web Service & menghitung kecepatan QoS langsung tanpa Postman
        async function runBuiltInApiTest() {
            const endpoint = document.getElementById('tester-endpoint').value;
            const statusLabel = document.getElementById('tester-status-label');
            const qosBadge = document.getElementById('tester-qos-badge');
            const jsonOutput = document.getElementById('tester-json-output');
            const btn = document.getElementById('tester-btn');

            btn.disabled = true;
            btn.textContent = 'Menguji...';
            statusLabel.textContent = `MENGIRIM REQUEST: GET ${endpoint} ...`;
            qosBadge.textContent = 'Menghitung QoS...';
            qosBadge.className = 'rounded-full bg-blue-50 border border-blue-200 px-2 py-0.5 text-[10px] text-[#1B64F2] font-bold';

            const startTime = performance.now();

            try {
                const response = await fetch(endpoint, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Client-Platform': 'Web Control Panel (Built-In Tester)'
                    }
                });

                const durationMs = Math.round(performance.now() - startTime);
                const data = await response.json();

                statusLabel.textContent = `HTTP ${response.status} (${endpoint}) • Waktu Respon: ${durationMs} ms`;

                if (response.ok) {
                    qosBadge.textContent = durationMs < 200 ? `QoS: Sangat Cepat (${durationMs} ms)` : `QoS: Stabil (${durationMs} ms)`;
                    qosBadge.className = 'rounded-full bg-emerald-50 border border-emerald-200 px-2 py-0.5 text-[10px] text-emerald-700 font-bold';
                    jsonOutput.style.color = '#1E293B';
                } else {
                    qosBadge.textContent = `Proteksi Aktif (${response.status} Ditolak Tanpa Token)`;
                    qosBadge.className = 'rounded-full bg-amber-50 border border-amber-200 px-2 py-0.5 text-[10px] text-amber-700 font-bold';
                    jsonOutput.style.color = '#B45309';
                }

                jsonOutput.textContent = JSON.stringify(data, null, 2);

                // Refresh tabel log di bawahnya supaya hasil tes langsung muncul di daftar trafik
                fetchLatestLogs(true);
            } catch (error) {
                statusLabel.textContent = 'Gagal menghubungi endpoint Web Service';
                qosBadge.textContent = 'Error Koneksi';
                qosBadge.className = 'text-rose-400 font-bold';
                jsonOutput.textContent = String(error);
            } finally {
                btn.disabled = false;
                btn.textContent = 'Uji Endpoint';
            }
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
