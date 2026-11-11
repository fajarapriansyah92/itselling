<x-layouts.admin title="Dashboard">
    @php
        $highestMonth = max(1, (float) $monthlyRevenue->max('total'));
        $peakIndex = $monthlyRevenue->search(fn ($m) => (float) $m['total'] === (float) $monthlyRevenue->max('total'));
        if ($peakIndex === false) {
            $peakIndex = count($monthlyRevenue) - 1;
        }
    @endphp

    <!-- Top Title & Primary Pill Button Row (Matches Medlio "Dashboard" + "New Consultation") -->
    <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Dashboard</h1>
        <a href="{{ route('admin.products.create') }}"
           class="inline-flex items-center gap-2 rounded-full bg-[#1B64F2] px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-blue-600/20 transition hover:bg-[#1554D1]">
            <span>+ Tambah Produk</span>
        </a>
    </div>

    <!-- Top 4 KPI Cards Row (1 Solid Royal Blue Card + 3 White Cards with Mini Blue Badges) -->
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <!-- Card 1: Primary Highlighted Blue Card ("Upcoming Today" style) -->
        <a href="{{ route('admin.orders.index', ['status' => 'paid']) }}"
           style="background: linear-gradient(135deg, #1E64F0 0%, #1552D6 100%);"
           class="bg-[#1B64F2] group relative flex flex-col justify-between rounded-2xl p-5 text-white shadow-lg shadow-blue-600/20 transition hover:brightness-105">
            <div class="flex items-start justify-between gap-3">
                <p style="color: #FFFFFF !important;" class="text-sm font-bold text-white/95">Perlu Diproses</p>
                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white text-[#1B64F2] shadow-sm">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <div class="mt-2">
                <p style="color: #FFFFFF !important;" class="text-3xl font-extrabold tracking-tight text-white tabular-nums">
                    {{ number_format($inProgressOrderCount, 0, ',', '.') }}
                </p>
                <p style="color: #DBEAFE !important;" class="mt-1 text-xs font-medium text-blue-100">Pesanan aktif dijadwalkan</p>
            </div>
        </a>

        <!-- Card 2: White Card ("Total Appointments" style) -->
        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}"
           class="flex flex-col justify-between rounded-2xl border border-slate-200/75 bg-white p-5 shadow-xs transition hover:border-blue-300">
            <div class="flex items-start justify-between gap-3">
                <p class="text-sm font-bold text-slate-800">Menunggu Bayar</p>
                <div class="flex size-6 shrink-0 items-center justify-center rounded-md bg-[#1B64F2] text-white">
                    <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-2">
                <p class="text-2xl font-extrabold tracking-tight text-slate-900 tabular-nums">
                    {{ number_format($pendingOrderCount, 0, ',', '.') }}
                </p>
                <p class="mt-1 text-xs font-medium text-slate-400">Bulan ini</p>
            </div>
        </a>

        <!-- Card 3: White Card ("Revenue" style) -->
        <a href="{{ route('admin.reports.sales') }}"
           class="flex flex-col justify-between rounded-2xl border border-slate-200/75 bg-white p-5 shadow-xs transition hover:border-blue-300">
            <div class="flex items-start justify-between gap-3">
                <p class="text-sm font-bold text-slate-800">Pendapatan</p>
                <div class="flex size-6 shrink-0 items-center justify-center rounded-md bg-[#1B64F2] text-white">
                    <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-2">
                <p class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 tabular-nums truncate" title="@rupiah($revenueThisMonth)">
                    @rupiah($revenueThisMonth)
                </p>
                <p class="mt-1 text-xs font-medium text-slate-400">Bulan ini</p>
            </div>
        </a>

        <!-- Card 4: White Card ("Referrals" style) -->
        <a href="{{ route('admin.customers.index') }}"
           class="flex flex-col justify-between rounded-2xl border border-slate-200/75 bg-white p-5 shadow-xs transition hover:border-blue-300">
            <div class="flex items-start justify-between gap-3">
                <p class="text-sm font-bold text-slate-800">Pelanggan & Produk</p>
                <div class="flex size-6 shrink-0 items-center justify-center rounded-md bg-[#1B64F2] text-white">
                    <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-2">
                <p class="text-2xl font-extrabold tracking-tight text-slate-900 tabular-nums">
                    {{ number_format($customerCount, 0, ',', '.') }}
                </p>
                <p class="mt-1 text-xs font-medium text-slate-400">{{ $activeProductCount }} Produk & Jasa Aktif</p>
            </div>
        </a>
    </section>

    <!-- Middle 3-Column Analytics Section (Pill Bar Chart + Total Revenue Wave Card + Diagnostics/Inventory Card) -->
    <section class="grid gap-4 xl:grid-cols-12">
        <!-- Left Card (5 cols): Pill Bar Chart ("Daily appointment count" style) -->
        <article class="xl:col-span-5 rounded-2xl border border-slate-200/75 bg-white p-5 shadow-xs flex flex-col justify-between">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="flex size-6 items-center justify-center rounded-md bg-blue-50 text-[#1B64F2]">
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </span>
                    <h2 class="text-sm font-extrabold text-slate-900">Statistik Pendapatan Bulanan</h2>
                </div>

                <!-- Segmented Pill Tabs -->
                <div class="inline-flex items-center rounded-full bg-slate-100 p-0.5 text-[11px] font-semibold text-slate-500">
                    <span class="px-2.5 py-1">6 Bulan</span>
                    <span class="rounded-full bg-[#1B64F2] px-3 py-1 font-bold text-white shadow-xs">Bulanan</span>
                </div>
            </div>

            <!-- Chart Area with Y-Axis & Thick Rounded Pill Bars -->
            <div class="mt-6 flex gap-3 items-end h-56 pt-8">
                <!-- Y-Axis Labels -->
                <div class="flex flex-col justify-between h-full pb-6 text-[10px] font-semibold text-slate-400 select-none">
                    <span>100%</span>
                    <span>75%</span>
                    <span>50%</span>
                    <span>25%</span>
                    <span>0</span>
                </div>

                <!-- Pill Bars -->
                <div class="flex-1 flex items-end justify-between gap-2.5 sm:gap-3.5 h-full">
                    @foreach ($monthlyRevenue as $idx => $month)
                        @php
                            $isPeak = ($idx === $peakIndex) && ($month['total'] > 0 || $idx === count($monthlyRevenue) - 1);
                            $heightPct = $month['total'] > 0 ? max(24, round(($month['total'] / $highestMonth) * 100)) : 8;
                        @endphp
                        <div class="flex-1 flex flex-col items-center justify-end h-full group">
                            <div class="relative w-full max-w-[44px] flex flex-col items-center justify-end h-full pb-2">
                                @if ($isPeak)
                                    <!-- Floating Blue Speech Bubble Callout on Peak Bar -->
                                    <div class="mb-2 relative rounded-lg bg-[#1B64F2] px-2 py-1 text-[10px] font-extrabold text-white shadow-md whitespace-nowrap">
                                        {{ number_format($month['total'] / 1000000, 1, ',', '.') }}jt
                                        <span class="absolute left-1/2 -bottom-1 -translate-x-1/2 size-2 rotate-45 bg-[#1B64F2]"></span>
                                    </div>
                                @else
                                    <span class="mb-1.5 text-[10px] font-bold text-slate-400 opacity-0 group-hover:opacity-100 transition whitespace-nowrap">
                                        {{ number_format($month['total'] / 1000000, 1, ',', '.') }}jt
                                    </span>
                                @endif

                                <!-- Capsule / Pill Bar -->
                                <div style="height: {{ $heightPct }}%; background: {{ $isPeak ? 'linear-gradient(180deg, #1E64F0 0%, #0E48C6 100%)' : 'linear-gradient(180deg, #67ACFA 0%, #4E97F7 100%)' }};"
                                     class="relative w-full rounded-full transition-all duration-300 shadow-xs group-hover:brightness-105"
                                     title="{{ $month['label'] }}: @rupiah($month['total'])">
                                    @if ($isPeak)
                                        <!-- Top Dot Pin -->
                                        <span class="absolute -top-1.5 left-1/2 -translate-x-1/2 size-3 rounded-full bg-[#1B64F2] ring-2 ring-white shadow-xs"></span>
                                    @endif
                                </div>
                            </div>
                            <span class="text-[11px] font-semibold {{ $isPeak ? 'text-slate-900 font-bold' : 'text-slate-400' }}">
                                {{ $month['label'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </article>

        <!-- Center Card (4 cols): Total Revenue Highlight & Smooth Wave Chart ("Revenue 4,820.89" style) -->
        <article class="xl:col-span-4 rounded-2xl border border-slate-200/75 bg-white p-5 shadow-xs flex flex-col justify-between overflow-hidden">
            <div>
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="flex size-6 items-center justify-center rounded-md bg-blue-50 text-[#1B64F2]">
                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-xs font-extrabold text-slate-900 leading-tight">Akumulasi Omset</h2>
                            <p class="text-[10px] text-slate-400">Total pendapatan bersih masuk</p>
                        </div>
                    </div>
                    <span class="rounded-full bg-[#1B64F2] px-2.5 py-0.5 text-[10px] font-bold text-white">Real-time</span>
                </div>

                <!-- Big Number + Growth Pill -->
                <div class="mt-4 flex flex-wrap items-baseline gap-2">
                    <p class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 tabular-nums">
                        @rupiah($revenueTotal)
                    </p>
                    <span class="rounded-full bg-[#1B64F2] px-2 py-0.5 text-[10px] font-extrabold text-white">
                        +Aktif
                    </span>
                </div>
            </div>

            <!-- Smooth Area Wave Illustration with Floating Callout Badge -->
            <div class="relative mt-4 pt-6 -mx-5 -mb-5">
                <!-- Floating Callout Badge in Center of Wave -->
                <div class="absolute left-1/2 top-3 -translate-x-1/2 z-10 flex flex-col items-center">
                    <div class="rounded-lg bg-[#1B64F2] px-2.5 py-1 text-[10px] font-extrabold text-white shadow-md whitespace-nowrap">
                        {{ number_format($revenueThisMonth / 1000000, 1, ',', '.') }} jt bln ini
                    </div>
                    <span class="w-0.5 h-2 bg-[#1B64F2]"></span>
                    <span class="size-2.5 rounded-full bg-[#1B64F2] ring-2 ring-white shadow"></span>
                </div>

                <svg class="w-full h-36 block" viewBox="0 0 400 130" preserveAspectRatio="none" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="medlioWaveGrad" x1="200" y1="0" x2="200" y2="130" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#3B82F6" stop-opacity="0.55" />
                            <stop offset="65%" stop-color="#93C5FD" stop-opacity="0.25" />
                            <stop offset="100%" stop-color="#EFF6FF" stop-opacity="0.05" />
                        </linearGradient>
                    </defs>
                    @if ($revenueTotal > 0)
                        <path d="M0 20 C65 75, 110 85, 160 65 C195 50, 225 48, 265 62 C315 80, 355 55, 400 95 L400 130 L0 130 Z" fill="url(#medlioWaveGrad)" />
                        <path d="M0 20 C65 75, 110 85, 160 65 C195 50, 225 48, 265 62 C315 80, 355 55, 400 95" stroke="#3B82F6" stroke-width="2.5" stroke-linecap="round" />
                    @else
                        <path d="M0 112 L400 112 L400 130 L0 130 Z" fill="url(#medlioWaveGrad)" />
                        <path d="M0 112 L400 112" stroke="#93C5FD" stroke-width="2" stroke-dasharray="4 4" stroke-linecap="round" />
                    @endif
                </svg>
            </div>
        </article>

        <!-- Right Card (3 cols): Top Products & Inventory Status ("Diagnostics + Lab Activity" style) -->
        <article class="xl:col-span-3 rounded-2xl border border-slate-200/75 bg-white p-5 shadow-xs flex flex-col justify-between">
            <!-- Top Half: Produk Terlaris (Diagnostics Style) -->
            <div>
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-extrabold text-slate-900">Produk Terlaris</h2>
                    <a href="{{ route('admin.products.index') }}" class="text-[11px] font-bold text-[#1B64F2] hover:underline">
                        Lihat semua &rsaquo;
                    </a>
                </div>

                <div class="mt-3 space-y-2">
                    @forelse ($topProducts->take(2) as $index => $item)
                        <a href="{{ route('admin.products.index') }}"
                           class="flex items-center justify-between gap-2.5 rounded-xl border border-slate-100 bg-[#F8FAFC] p-2.5 transition hover:border-blue-200">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-[#E0ECFF] text-[#1B64F2]">
                                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-800 truncate">{{ $item->product_name }}</p>
                                    <p class="text-[10px] font-bold {{ $index === 0 ? 'text-emerald-600' : 'text-amber-500' }}">
                                        {{ $item->quantity_sold }} Terjual
                                    </p>
                                </div>
                            </div>
                            <svg class="size-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @empty
                        <p class="text-xs text-slate-400 py-2">Belum ada data penjualan.</p>
                    @endforelse
                </div>
            </div>

            <div class="my-3.5 border-t border-slate-100"></div>

            <!-- Bottom Half: Status Inventori (Lab Activity Style) -->
            <div>
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-extrabold text-slate-900">Status Inventori</h2>
                    <a href="{{ route('admin.products.index') }}" class="text-[11px] font-bold text-[#1B64F2] hover:underline">
                        Lihat semua &rsaquo;
                    </a>
                </div>

                <ul class="mt-2.5 space-y-2">
                    @forelse ($lowStockProducts->take(2) as $product)
                        <li class="flex items-start gap-2">
                            <span class="mt-1 size-2 rounded-full bg-rose-500 ring-2 ring-rose-100 shrink-0"></span>
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('admin.products.edit', $product) }}" class="text-[11px] font-bold text-slate-700 hover:underline block truncate">
                                    {{ $product->name }}: <span class="text-rose-600">Sisa {{ $product->stock }}</span>
                                </a>
                                <p class="text-[10px] text-slate-400">{{ $product->sku }} · Perlu restock</p>
                            </div>
                        </li>
                    @empty
                        <li class="flex items-start gap-2">
                            <span class="mt-1 size-2 rounded-full bg-emerald-500 ring-2 ring-emerald-100 shrink-0"></span>
                            <div>
                                <p class="text-[11px] font-bold text-slate-700">Stok Gudang: <span class="text-emerald-600">Normal</span></p>
                                <p class="text-[10px] text-slate-400">{{ $activeProductCount }} produk siap dikirim</p>
                            </div>
                        </li>
                    @endforelse
                </ul>
            </div>
        </article>
    </section>

    <!-- Bottom Section: Pesanan Terbaru ("Appointments" Horizontal Row Cards Style) -->
    <section class="rounded-2xl border border-slate-200/75 bg-white p-5 sm:p-6 shadow-xs">
        <!-- Section Header -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-1.5">
                    <h2 class="text-base font-extrabold text-slate-900">Pesanan Terbaru</h2>
                    <svg class="size-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
                <p class="text-xs text-slate-400 mt-0.5">Pantau dan kelola jadwal transaksi pesanan dari aplikasi mobile</p>
            </div>

            <!-- Right Filter Controls & CTA Button -->
            <div class="flex flex-wrap items-center gap-2.5">
                <div class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600">
                    <svg class="size-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>{{ now()->translatedFormat('d F Y') }}</span>
                </div>

                <a href="{{ route('admin.orders.index') }}"
                   class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:border-blue-300">
                    <span>Semua Status</span>
                    <svg class="size-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </a>

                <a href="{{ route('admin.orders.index') }}"
                   class="inline-flex items-center gap-1.5 rounded-full bg-[#1B64F2] px-4 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-[#1554D1] transition">
                    <span>Lihat Semua</span>
                </a>
            </div>
        </div>

        <!-- Horizontal Card Rows List -->
        <div class="mt-5 space-y-2.5">
            @forelse ($latestOrders as $order)
                <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-slate-200/80 bg-white px-4 py-3 transition hover:border-[#93C5FD] hover:shadow-xs">
                    <!-- Left: Customer Avatar & Name / Phone / Invoice -->
                    <div class="flex items-center gap-3 min-w-[200px]">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-[#EBF3FF] text-[#1B64F2]">
                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-extrabold text-slate-900">{{ $order->user->name }}</p>
                            <p class="text-[11px] text-slate-400">
                                {{ $order->recipient_phone }} · <span class="font-mono font-semibold text-slate-500">{{ $order->order_number }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Right Cluster: Time, Date, Price, Status Badge, and Pill Buttons -->
                    <div class="flex flex-wrap items-center gap-3 sm:gap-4 ml-auto">
                        <!-- Time & Date with Clock Icon -->
                        <div class="flex items-center gap-2 text-xs">
                            <span class="flex size-5 items-center justify-center rounded-full bg-blue-50 text-[#1B64F2]">
                                <svg class="size-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                            <span class="font-bold text-slate-700 tabular-nums">{{ $order->created_at->format('H:i') }}</span>
                            <span class="font-semibold text-slate-500 ml-1">{{ $order->created_at->translatedFormat('d M Y') }}</span>
                        </div>

                        <!-- Total Price -->
                        <span class="text-xs font-extrabold text-slate-900 font-mono tabular-nums">
                            @rupiah($order->total)
                        </span>

                        <!-- Order Status Pill -->
                        <x-order-status :status="$order->status" />

                        <!-- Action Outline Pills ("Start Session" / "EMR" style) -->
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('admin.orders.show', $order) }}"
                               class="rounded-full border border-[#60A5FA] bg-white px-3.5 py-1 text-[11px] font-bold text-[#1B64F2] transition hover:bg-[#1B64F2] hover:text-white">
                                Detail Pesanan
                            </a>
                            <a href="{{ route('admin.chats.show', $order) }}"
                               class="rounded-full border border-[#93C5FD] bg-white px-3 py-1 text-[11px] font-bold text-[#1B64F2] transition hover:bg-blue-50">
                                Chat
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-slate-200 py-8 text-center text-xs text-slate-400">
                    Belum ada pesanan masuk dari aplikasi mobile.
                </div>
            @endforelse
        </div>
    </section>
</x-layouts.admin>
