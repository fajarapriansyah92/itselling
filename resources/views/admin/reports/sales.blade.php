<x-layouts.admin title="Laporan Penjualan">
    @php
        $hasPrintLogo = file_exists(public_path('images/logo.png'));
        $avgOrderValue = $orders->count() > 0 ? $revenueTotal / $orders->count() : 0;
    @endphp

    <!-- Web Interactive View (Hidden on Print) -->
    <div class="space-y-5 print:hidden">
        <!-- Filter & Action Card -->
        <form method="GET" class="card bg-white p-4 sm:p-5 flex flex-wrap items-end justify-between gap-4">
            <div class="flex flex-wrap items-end gap-3.5">
                <x-field label="Dari Tanggal" name="from">
                    <input id="from" name="from" type="date" value="{{ $from->toDateString() }}" class="field-input text-xs font-semibold">
                </x-field>
                <x-field label="Sampai Tanggal" name="to">
                    <input id="to" name="to" type="date" value="{{ $to->toDateString() }}" class="field-input text-xs font-semibold">
                </x-field>
                <button type="submit" class="rounded-full border border-[#60A5FA] bg-blue-50/70 px-5 py-2.5 text-xs font-extrabold text-[#1B64F2] hover:bg-[#1B64F2] hover:text-white transition cursor-pointer">
                    Terapkan Filter
                </button>
                @if (request()->hasAny(['from', 'to']))
                    <a href="{{ route('admin.reports.sales') }}" class="rounded-full border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition">
                        Reset Bulan Ini
                    </a>
                @endif
            </div>

            <div class="flex items-center gap-2.5 ml-auto">
                <span class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3.5 py-1.5 text-[11px] font-bold text-slate-600">
                    Periode: {{ $from->translatedFormat('d M Y') }} – {{ $to->translatedFormat('d M Y') }}
                </span>
                <button type="button" onclick="window.print()"
                        class="btn-primary rounded-full px-5 py-2.5 text-xs font-extrabold inline-flex items-center gap-2 cursor-pointer">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>Cetak Laporan (A4)</span>
                </button>
            </div>
        </form>

        <!-- Top 4 KPI Summary Cards -->
        <section class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Card 1: Total Pendapatan (Royal Blue Hero) -->
            <div style="background: linear-gradient(135deg, #1E64F0 0%, #1552D6 100%);"
                 class="bg-[#1B64F2] rounded-2xl p-5 text-white shadow-lg shadow-blue-600/20 flex flex-col justify-between col-span-2 sm:col-span-1">
                <div class="flex items-start justify-between gap-3">
                    <span style="color: #FFFFFF !important;" class="text-xs font-bold uppercase tracking-wider">Total Pendapatan</span>
                    <div class="size-8 rounded-xl bg-white text-[#1B64F2] flex items-center justify-center shadow-xs shrink-0">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <p style="color: #FFFFFF !important;" class="text-xl sm:text-2xl font-extrabold tabular-nums font-mono">@rupiah($revenueTotal)</p>
                    <p style="color: #DBEAFE !important;" class="text-[11px] font-medium mt-1">Transaksi Lunas & Selesai</p>
                </div>
            </div>

            <!-- Card 2: Jumlah Transaksi -->
            <div class="card p-5 bg-white flex flex-col justify-between">
                <div class="flex items-start justify-between gap-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Jumlah Transaksi</span>
                    <div class="size-7 rounded-lg bg-blue-50 text-[#1B64F2] flex items-center justify-center shrink-0 border border-blue-200">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <p class="text-2xl font-extrabold text-slate-900 tabular-nums">{{ number_format($orders->count(), 0, ',', '.') }}</p>
                    <p class="text-[11px] font-medium text-slate-400 mt-1">Invoice terbayar</p>
                </div>
            </div>

            <!-- Card 3: Total Item / Unit Terjual -->
            <div class="card p-5 bg-white flex flex-col justify-between">
                <div class="flex items-start justify-between gap-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Item & Layanan Terjual</span>
                    <div class="size-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-200">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <p class="text-2xl font-extrabold text-slate-900 tabular-nums">{{ number_format($quantityTotal, 0, ',', '.') }}</p>
                    <p class="text-[11px] font-medium text-slate-400 mt-1">Unit hardware, lisensi & paket jasa</p>
                </div>
            </div>

            <!-- Card 4: Rata-Rata Nilai Transaksi -->
            <div class="card p-5 bg-white flex flex-col justify-between">
                <div class="flex items-start justify-between gap-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Rata-Rata / Order</span>
                    <div class="size-7 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 border border-purple-200">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <p class="text-xl font-extrabold text-slate-900 tabular-nums font-mono">@rupiah($avgOrderValue)</p>
                    <p class="text-[11px] font-medium text-slate-400 mt-1">Nilai rata-rata per invoice</p>
                </div>
            </div>
        </section>

        <!-- 2-Column Breakdown: Category & Product Summary -->
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">
            <!-- Left: Penjualan per Kategori (2 cols) -->
            <section class="card overflow-hidden bg-white lg:col-span-2">
                <div class="px-5 py-4 border-b border-slate-100 bg-[#F8FAFC] flex items-center justify-between">
                    <div>
                        <h2 class="font-extrabold text-slate-900 text-sm">Penjualan per Kategori</h2>
                        <p class="text-[11px] text-slate-400">Kontribusi pendapatan tiap kategori</p>
                    </div>
                    <span class="rounded-full bg-blue-50 border border-blue-200 px-2.5 py-0.5 text-[10px] font-extrabold text-[#1B64F2]">
                        {{ $categorySummary->count() }} Kategori
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="table-base">
                        <thead>
                            <tr>
                                <th class="text-left">Kategori</th>
                                <th class="text-center">Terjual</th>
                                <th class="text-right">Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs">
                            @forelse ($categorySummary as $row)
                                <tr>
                                    <td class="font-bold text-slate-800">{{ $row->category_name }}</td>
                                    <td class="text-center tabular-nums font-bold text-slate-700">{{ $row->quantity_sold }}</td>
                                    <td class="text-right whitespace-nowrap tabular-nums font-mono font-extrabold text-[#1B64F2]">@rupiah($row->revenue)</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-8 text-center text-slate-400 text-xs">Tidak ada penjualan pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Right: Rincian Produk & Layanan Terjual (3 cols) -->
            <section class="card overflow-hidden bg-white lg:col-span-3">
                <div class="px-5 py-4 border-b border-slate-100 bg-[#F8FAFC] flex items-center justify-between">
                    <div>
                        <h2 class="font-extrabold text-slate-900 text-sm">Produk & Layanan Terjual</h2>
                        <p class="text-[11px] text-slate-400">Akumulasi unit/paket terjual berdasarkan item spesifik</p>
                    </div>
                    <span class="rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 text-[10px] font-extrabold text-emerald-700">
                        {{ $productSummary->count() }} Produk
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="table-base">
                        <thead>
                            <tr>
                                <th class="text-left">Nama Produk / Layanan</th>
                                <th class="text-left">Kategori</th>
                                <th class="text-center">Qty</th>
                                <th class="text-right">Subtotal Omzet</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs">
                            @forelse ($productSummary as $prod)
                                <tr>
                                    <td>
                                        <p class="font-bold text-slate-900">{{ $prod->product_name }}</p>
                                        @if ($prod->sku)
                                            <span class="text-[10px] font-mono text-slate-400">SKU: {{ $prod->sku }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="rounded-md bg-slate-100 border border-slate-200 px-2 py-0.5 text-[10px] font-bold text-slate-600">
                                            {{ $prod->category_name ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="text-center tabular-nums font-extrabold text-slate-800">{{ $prod->quantity_sold }}</td>
                                    <td class="text-right whitespace-nowrap tabular-nums font-mono font-extrabold text-emerald-600">@rupiah($prod->revenue)</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-slate-400 text-xs">Belum ada item produk yang terjual.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- Detailed Transactions Table with Purchased Products -->
        <section class="card overflow-hidden bg-white">
            <div class="px-5 py-4 border-b border-slate-100 bg-[#F8FAFC] flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="font-extrabold text-slate-900 text-sm sm:text-base">Detail Transaksi & Rincian Produk Dibeli</h2>
                    <p class="text-xs text-slate-400">Daftar lengkap pesanan pelanggan beserta produk/jasa yang dibeli dan metode pembayarannya</p>
                </div>
                <span class="rounded-full bg-blue-50 border border-blue-200 px-3 py-1 text-xs font-extrabold text-[#1B64F2]">
                    {{ $orders->count() }} Transaksi
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th class="w-10 text-center">No</th>
                            <th class="text-left">Tanggal</th>
                            <th class="text-left">No. Pesanan</th>
                            <th class="text-left">Pelanggan</th>
                            <th class="text-left">Produk / Layanan yang Dibeli</th>
                            <th class="text-left">Metode Bayar</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs">
                        @forelse ($orders as $order)
                            <tr class="align-top">
                                <td class="text-center tabular-nums font-bold text-slate-400">{{ $loop->iteration }}</td>
                                <td class="whitespace-nowrap">
                                    <span class="font-bold text-slate-800 block">{{ $order->created_at->translatedFormat('d M Y') }}</span>
                                    <span class="text-[10px] font-mono text-slate-400">{{ $order->created_at->format('H:i') }} WIB</span>
                                </td>
                                <td class="whitespace-nowrap">
                                    <a href="{{ route('admin.orders.show', $order) }}"
                                       class="font-mono font-extrabold text-[#1B64F2] hover:underline inline-flex items-center gap-1">
                                        <span>#{{ $order->order_number }}</span>
                                    </a>
                                </td>
                                <td>
                                    <p class="font-bold text-slate-900">{{ $order->user->name }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $order->recipient_phone }}</p>
                                </td>
                                <td class="min-w-[240px]">
                                    <div class="space-y-1.5">
                                        @foreach ($order->items as $item)
                                            @php
                                                $typeEnum = $item->product?->type;
                                                $badgeStyle = match ($typeEnum?->value) {
                                                    'service' => 'bg-purple-50 text-purple-700 border-purple-200',
                                                    'software' => 'bg-sky-50 text-sky-700 border-sky-200',
                                                    default => 'bg-blue-50 text-[#1B64F2] border-blue-200',
                                                };
                                            @endphp
                                            <div class="flex items-start justify-between gap-2 rounded-xl bg-[#F8FAFC] border border-slate-200/80 px-2.5 py-1.5">
                                                <div class="min-w-0">
                                                    <div class="flex flex-wrap items-center gap-1.5">
                                                        <span class="font-extrabold text-[#1B64F2] font-mono">{{ $item->quantity }}x</span>
                                                        <span class="font-bold text-slate-800">{{ $item->product_name }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-1.5 mt-0.5">
                                                        @if ($typeEnum)
                                                            <span class="rounded px-1.5 py-0.2 text-[9px] font-bold border {{ $badgeStyle }}">
                                                                {{ $typeEnum->label() }}
                                                            </span>
                                                        @endif
                                                        @if ($item->product?->category?->name)
                                                            <span class="text-[10px] text-slate-400">• {{ $item->product->category->name }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <span class="font-mono font-semibold text-[11px] text-slate-600 whitespace-nowrap shrink-0">
                                                    @rupiah($item->subtotal)
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="whitespace-nowrap">
                                    <span class="rounded-lg bg-slate-100 border border-slate-200 px-2.5 py-1 text-[11px] font-bold text-slate-700">
                                        {{ $order->payment_channel ?? ($order->payment_method === 'credit_card' ? 'Kartu Kredit' : 'Transfer VA') }}
                                    </span>
                                </td>
                                <td class="text-center whitespace-nowrap">
                                    <x-order-status :status="$order->status" />
                                </td>
                                <td class="text-right whitespace-nowrap font-mono font-extrabold text-slate-900 tabular-nums text-sm">
                                    @rupiah($order->total)
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    Tidak ada transaksi pada periode tanggal ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($orders->isNotEmpty())
                        <tfoot class="bg-[#F8FAFC] border-t-2 border-slate-200">
                            <tr>
                                <td colspan="7" class="py-4 px-5 text-right font-extrabold uppercase tracking-wider text-xs text-slate-600">
                                    Total Pendapatan ({{ $orders->count() }} Transaksi / {{ $quantityTotal }} Item)
                                </td>
                                <td class="py-4 px-5 text-right font-mono text-base font-black whitespace-nowrap tabular-nums text-[#1B64F2]">
                                    @rupiah($revenueTotal)
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </section>
    </div>

    <!-- Dedicated Official A4 Print Report Layout (Only visible when printing) -->
    <div class="hidden print:block bg-white text-slate-900">
        <div class="rounded-2xl border border-slate-200 overflow-hidden">
            <!-- Header Banner -->
            <div style="background: linear-gradient(135deg, #1B64F2 0%, #1554D1 100%);" class="bg-[#1B64F2] px-7 py-5 text-white flex items-center justify-between">
                <div class="flex items-center gap-3.5">
                    <div style="background-color: #FFFFFF !important; color: #1B64F2 !important;" class="size-11 rounded-xl bg-white text-[#1B64F2] font-extrabold text-sm flex items-center justify-center shrink-0 p-1">
                        @if ($hasPrintLogo)
                            <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="w-full h-full object-contain">
                        @else
                            DSC
                        @endif
                    </div>
                    <div>
                        <h1 style="color: #FFFFFF !important;" class="text-lg font-extrabold tracking-tight text-white">{{ config('app.name') }}</h1>
                        <p style="color: #DBEAFE !important;" class="text-[11px] text-blue-100 font-medium">Laporan Resmi Rekapitulasi Penjualan Produk & Layanan IT</p>
                    </div>
                </div>
                <div class="text-right">
                    <p style="color: #DBEAFE !important;" class="text-[10px] font-bold uppercase tracking-widest">Periode Laporan</p>
                    <p style="color: #FFFFFF !important;" class="text-sm font-extrabold mt-0.5">{{ $from->translatedFormat('d M Y') }} – {{ $to->translatedFormat('d M Y') }}</p>
                </div>
            </div>

            <div class="p-6 space-y-5">
                <!-- Print Summary Strip -->
                <div class="grid grid-cols-3 gap-4 text-xs">
                    <div class="rounded-xl border border-slate-200 bg-[#F8FAFC] p-3.5">
                        <span class="text-[10px] font-bold uppercase text-slate-400 block">Jumlah Transaksi</span>
                        <span class="text-lg font-extrabold text-slate-900">{{ $orders->count() }} Pesanan</span>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-[#F8FAFC] p-3.5">
                        <span class="text-[10px] font-bold uppercase text-slate-400 block">Total Item Terjual</span>
                        <span class="text-lg font-extrabold text-slate-900">{{ $quantityTotal }} Item</span>
                    </div>
                    <div class="rounded-xl border border-blue-200 bg-[#EFF6FF] p-3.5">
                        <span class="text-[10px] font-bold uppercase text-[#1B64F2] block">Total Pendapatan</span>
                        <span class="text-lg font-black font-mono text-[#1B64F2]">@rupiah($revenueTotal)</span>
                    </div>
                </div>

                <!-- Print Transactions Table -->
                <div class="rounded-xl border border-slate-200 overflow-hidden">
                    <table class="w-full text-left border-collapse text-[11px]">
                        <thead>
                            <tr class="bg-[#F1F5F9] border-b border-slate-200 text-[10px] font-extrabold uppercase text-slate-600">
                                <th class="py-2 px-3 text-center w-8">No</th>
                                <th class="py-2 px-3">Tanggal</th>
                                <th class="py-2 px-3">No. Invoice</th>
                                <th class="py-2 px-3">Pelanggan</th>
                                <th class="py-2 px-3">Rincian Produk / Jasa Dibeli</th>
                                <th class="py-2 px-3">Pembayaran</th>
                                <th class="py-2 px-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach ($orders as $order)
                                <tr class="align-top">
                                    <td class="py-2 px-3 text-center font-bold text-slate-500">{{ $loop->iteration }}</td>
                                    <td class="py-2 px-3 whitespace-nowrap">{{ $order->created_at->translatedFormat('d M Y') }}</td>
                                    <td class="py-2 px-3 font-mono font-bold text-slate-900">#{{ $order->order_number }}</td>
                                    <td class="py-2 px-3 font-bold text-slate-800">{{ $order->user->name }}</td>
                                    <td class="py-2 px-3">
                                        @foreach ($order->items as $item)
                                            <div>• <strong>{{ $item->quantity }}x</strong> {{ $item->product_name }} (@rupiah($item->subtotal))</div>
                                        @endforeach
                                    </td>
                                    <td class="py-2 px-3">{{ $order->payment_channel ?? 'Transfer VA' }}</td>
                                    <td class="py-2 px-3 text-right font-mono font-bold text-slate-900 whitespace-nowrap">@rupiah($order->total)</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-[#F8FAFC] border-t-2 border-slate-300">
                            <tr>
                                <td colspan="6" class="py-2.5 px-3 text-right font-extrabold uppercase">Total Pendapatan Keseluruhan</td>
                                <td class="py-2.5 px-3 text-right font-mono font-black text-[#1B64F2] text-xs">@rupiah($revenueTotal)</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Print Signature Footer -->
                <div class="pt-6 flex items-end justify-between text-xs text-slate-600">
                    <div>
                        <p>Dicetak pada: <strong>{{ now()->translatedFormat('d F Y, H:i') }} WIB</strong></p>
                        <p class="text-[10px] text-slate-400 mt-0.5">Dokumen ini dihasilkan secara otomatis oleh Sistem Informasi {{ config('app.name') }}.</p>
                    </div>
                    <div class="text-center">
                        <p>{{ now()->translatedFormat('d F Y') }}</p>
                        <p class="text-[10px] text-slate-400">Mengetahui, Administrator</p>
                        <p class="mt-14 font-extrabold text-slate-900 underline">{{ auth()->user()->name }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
