<x-layouts.admin title="Pelanggan">
    <form method="GET" class="flex gap-2">
        <input name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..." class="field-input max-w-sm">
        <button type="submit" class="btn-secondary">Cari</button>
    </form>

    <section class="card overflow-x-auto">
        <table class="table-base">
            <thead>
                <tr><th>Nama</th><th>Email</th><th>Telepon</th><th class="text-right">Pesanan</th><th class="text-right">Total Belanja</th><th>Terdaftar</th></tr>
            </thead>
            <tbody>
                @forelse ($customers as $customer)
                    <tr>
                        <td><a href="{{ route('admin.customers.show', $customer) }}" class="font-medium text-brand-700 hover:underline">{{ $customer->name }}</a></td>
                        <td>{{ $customer->email }}</td>
                        <td>{{ $customer->phone ?? '—' }}</td>
                        <td class="text-right tabular-nums">{{ $customer->orders_count }}</td>
                        <td class="text-right whitespace-nowrap tabular-nums">@rupiah($customer->total_spent ?? 0)</td>
                        <td class="whitespace-nowrap">{{ $customer->created_at->translatedFormat('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-slate-500">Pelanggan tidak ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    {{ $customers->links() }}
</x-layouts.admin>
