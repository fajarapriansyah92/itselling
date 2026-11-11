<x-layouts.admin title="Daftar Pesanan">
    <!-- Status Filter Pills (Medlio Royal Blue Style) -->
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.orders.index', request()->only('search')) }}"
           class="whitespace-nowrap rounded-full px-4 py-2 text-xs font-bold transition border {{ $currentStatus === null ? 'bg-[#1B64F2] text-white border-[#1B64F2] shadow-sm shadow-blue-600/20' : 'bg-white text-slate-600 border-slate-200/90 hover:border-blue-300 hover:text-[#1B64F2]' }}">
            Semua Pesanan
        </a>
        @foreach ($statuses as $status)
            <a href="{{ route('admin.orders.index', [...request()->only('search'), 'status' => $status->value]) }}"
               class="whitespace-nowrap rounded-full px-4 py-2 text-xs font-bold transition border {{ $currentStatus === $status ? 'bg-[#1B64F2] text-white border-[#1B64F2] shadow-sm shadow-blue-600/20' : 'bg-white text-slate-600 border-slate-200/90 hover:border-blue-300 hover:text-[#1B64F2]' }}">
                {{ $status->label() }}
            </a>
        @endforeach
    </div>

    <!-- Search Bar Card -->
    <div class="card p-4 bg-white">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            @if ($currentStatus)
                <input type="hidden" name="status" value="{{ $currentStatus->value }}">
            @endif
            <div class="relative flex-1">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari berdasarkan nomor pesanan atau nama pelanggan..."
                       style="padding-left: 2.5rem;"
                       class="field-input text-xs">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="btn-primary py-2 px-5 text-xs font-bold rounded-xl">
                    Cari
                </button>
                @if (request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.orders.index') }}" class="btn-secondary py-2 px-3.5 text-xs font-bold rounded-xl">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Orders Table Card -->
    <section class="card overflow-hidden bg-white">
        <div class="overflow-x-auto">
            <table class="table-base">
                <thead>
                    <tr>
                        <th class="text-left">No. Pesanan</th>
                        <th class="text-left">Pelanggan</th>
                        <th class="text-left">Tanggal Masuk</th>
                        <th class="text-center">Jumlah Item</th>
                        <th class="text-right">Total Transaksi</th>
                        <th class="text-center">Metode Pembayaran</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr class="transition">
                            <td class="whitespace-nowrap">
                                <a href="{{ route('admin.orders.show', $order) }}"
                                   class="font-mono text-xs font-extrabold text-[#1B64F2] hover:text-[#1554D1] hover:underline">
                                    {{ $order->order_number }}
                                </a>
                            </td>
                            <td class="whitespace-nowrap">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-[#EBF3FF] text-[#1B64F2] text-xs font-extrabold">
                                        {{ strtoupper(substr($order->user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 text-xs leading-tight">{{ $order->user->name }}</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $order->recipient_phone }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap text-xs text-slate-500 font-medium">
                                {{ $order->created_at->translatedFormat('d M Y, H:i') }}
                            </td>
                            <td class="whitespace-nowrap text-center">
                                <span class="inline-flex size-6 items-center justify-center rounded-lg bg-slate-100 font-bold text-slate-700 text-xs tabular-nums">
                                    {{ $order->items_count }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap text-right font-extrabold text-slate-900 text-xs font-mono tabular-nums">
                                @rupiah($order->total)
                            </td>
                            <td class="whitespace-nowrap text-center">
                                <span class="inline-flex items-center rounded-full bg-[#F1F5F9] px-3 py-1 text-[11px] font-bold text-slate-700 border border-slate-200/80">
                                    {{ $order->payment_channel ?? ($order->payment_method === 'credit_card' ? 'Kartu Kredit' : 'Virtual Account') }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap text-center">
                                <x-order-status :status="$order->status" />
                            </td>
                            <td class="whitespace-nowrap text-right">
                                <a href="{{ route('admin.orders.show', $order) }}"
                                   class="inline-flex items-center justify-center whitespace-nowrap rounded-full border border-[#60A5FA] bg-white px-4 py-1.5 text-xs font-bold text-[#1B64F2] hover:bg-[#1B64F2] hover:text-white transition">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-12">
                                <div class="size-12 rounded-2xl bg-blue-50 text-[#1B64F2] mx-auto flex items-center justify-center mb-3">
                                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <p class="text-sm font-bold text-slate-800">Tidak ada pesanan ditemukan</p>
                                <p class="text-xs text-slate-400 mt-1">Coba sesuaikan filter atau kata kunci pencarian Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $orders->links() }}
    </div>
</x-layouts.admin>
