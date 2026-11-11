<x-layouts.admin :title="$customer->name">
    <a href="{{ route('admin.customers.index') }}" class="text-sm text-slate-500 hover:text-slate-800">&larr; Kembali ke daftar pelanggan</a>

    <section class="card grid gap-4 p-5 sm:grid-cols-3">
        <div><p class="text-sm text-slate-500">Email</p><p class="font-medium">{{ $customer->email }}</p></div>
        <div><p class="text-sm text-slate-500">Telepon</p><p class="font-medium">{{ $customer->phone ?? '—' }}</p></div>
        <div><p class="text-sm text-slate-500">Alamat</p><p class="font-medium">{{ $customer->address ?? '—' }}</p></div>
    </section>

    <section class="card overflow-x-auto">
        <h2 class="px-5 py-4 font-semibold text-slate-900">Riwayat Pesanan</h2>
        <table class="table-base">
            <thead>
                <tr><th>No. Pesanan</th><th>Tanggal</th><th class="text-right">Item</th><th class="text-right">Total</th><th>Status</th></tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td><a href="{{ route('admin.orders.show', $order) }}" class="font-medium text-brand-700 hover:underline">{{ $order->order_number }}</a></td>
                        <td class="whitespace-nowrap">{{ $order->created_at->translatedFormat('d M Y H:i') }}</td>
                        <td class="text-right tabular-nums">{{ $order->items_count }}</td>
                        <td class="text-right whitespace-nowrap tabular-nums">@rupiah($order->total)</td>
                        <td><x-order-status :status="$order->status" /></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-slate-500">Belum ada pesanan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</x-layouts.admin>
