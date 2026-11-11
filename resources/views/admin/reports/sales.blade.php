<x-layouts.admin title="Laporan Penjualan">
    <form method="GET" class="card flex flex-wrap items-end gap-3 p-4 print:hidden">
        <x-field label="Dari tanggal" name="from">
            <input id="from" name="from" type="date" value="{{ $from->toDateString() }}" class="field-input">
        </x-field>
        <x-field label="Sampai tanggal" name="to">
            <input id="to" name="to" type="date" value="{{ $to->toDateString() }}" class="field-input">
        </x-field>
        <button type="submit" class="btn-secondary">Tampilkan</button>
        <button type="button" onclick="window.print()" class="btn-primary ml-auto">Cetak Laporan</button>
    </form>

    <div class="hidden text-center print:block">
        <h1 class="text-xl font-bold">{{ config('app.name') }}</h1>
        <p class="font-semibold">Laporan Penjualan</p>
    </div>
    <p class="text-sm text-slate-600 print:text-center">Periode {{ $from->translatedFormat('d F Y') }} – {{ $to->translatedFormat('d F Y') }}</p>

    <section class="grid gap-4 sm:grid-cols-3">
        <article class="card p-5">
            <p class="text-sm text-slate-500">Jumlah Transaksi</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums">{{ $orders->count() }}</p>
        </article>
        <article class="card p-5">
            <p class="text-sm text-slate-500">Item Terjual</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums">{{ number_format($quantityTotal, 0, ',', '.') }}</p>
        </article>
        <article class="card p-5">
            <p class="text-sm text-slate-500">Total Pendapatan</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums">@rupiah($revenueTotal)</p>
        </article>
    </section>

    <section class="card overflow-x-auto">
        <h2 class="px-5 py-4 font-semibold text-slate-900">Penjualan per Kategori</h2>
        <table class="table-base">
            <thead>
                <tr><th>Kategori</th><th class="text-right">Item Terjual</th><th class="text-right">Pendapatan</th></tr>
            </thead>
            <tbody>
                @forelse ($categorySummary as $row)
                    <tr>
                        <td>{{ $row->category_name }}</td>
                        <td class="text-right tabular-nums">{{ $row->quantity_sold }}</td>
                        <td class="text-right whitespace-nowrap tabular-nums">@rupiah($row->revenue)</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-slate-500">Tidak ada penjualan pada periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section class="card overflow-x-auto">
        <h2 class="px-5 py-4 font-semibold text-slate-900">Detail Transaksi</h2>
        <table class="table-base">
            <thead>
                <tr><th>No</th><th>Tanggal</th><th>No. Pesanan</th><th>Pelanggan</th><th class="text-right">Item</th><th>Status</th><th class="text-right">Total</th></tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td class="tabular-nums">{{ $loop->iteration }}</td>
                        <td class="whitespace-nowrap">{{ $order->created_at->translatedFormat('d M Y') }}</td>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->user->name }}</td>
                        <td class="text-right tabular-nums">{{ $order->quantity_total }}</td>
                        <td>{{ $order->status->label() }}</td>
                        <td class="text-right whitespace-nowrap tabular-nums">@rupiah($order->total)</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-slate-500">Tidak ada transaksi pada periode ini.</td></tr>
                @endforelse
            </tbody>
            @if ($orders->isNotEmpty())
                <tfoot>
                    <tr>
                        <td colspan="6" class="text-right font-semibold">Total Pendapatan</td>
                        <td class="text-right font-semibold whitespace-nowrap tabular-nums">@rupiah($revenueTotal)</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </section>

    <div class="hidden pt-12 text-right text-sm print:block">
        <p>{{ now()->translatedFormat('d F Y') }}</p>
        <p class="mt-16 font-semibold">{{ auth()->user()->name }}</p>
    </div>
</x-layouts.admin>
