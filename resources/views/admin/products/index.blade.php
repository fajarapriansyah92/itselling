<x-layouts.admin title="Katalog Produk & Jasa IT">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="flex flex-1 flex-wrap gap-2">
            <input name="search" value="{{ request('search') }}" placeholder="Cari nama, SKU, atau merek..." class="field-input max-w-xs text-xs">
            <select name="category" class="field-input w-auto text-xs font-medium">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary py-2 text-xs font-bold">Filter</button>
        </form>
        <a href="{{ route('admin.products.create') }}" class="btn-primary py-2 text-xs font-bold">+ Tambah Produk / Jasa</a>
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/70 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">
                        <th class="px-5 py-3.5">Item / Layanan</th>
                        <th class="px-5 py-3.5">Kategori</th>
                        <th class="px-5 py-3.5 text-right">Harga</th>
                        <th class="px-5 py-3.5 text-right">Stok</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($products as $product)
                        <tr class="transition hover:bg-blue-50/30">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    @if ($product->image_url)
                                        <img src="{{ $product->image_url }}" alt="" class="size-11 rounded-xl object-cover border border-slate-200 bg-slate-50">
                                    @else
                                        <div class="grid size-11 place-items-center rounded-xl bg-blue-50 text-xs font-extrabold text-[#1B64F2] border border-blue-200">IT</div>
                                    @endif
                                    <div>
                                        <p class="font-extrabold text-slate-900 text-sm">{{ $product->name }}</p>
                                        <p class="text-xs text-slate-500 font-mono">
                                            {{ $product->sku }}
                                            @if ($product->brand)
                                                · <strong class="font-sans text-slate-700">{{ $product->brand }}</strong>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center rounded-full bg-blue-50 border border-blue-200 px-2.5 py-0.5 text-xs font-extrabold text-[#1B64F2]">
                                    {{ $product->category->name }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap font-extrabold text-slate-900 tabular-nums">
                                @rupiah($product->price)
                                @if ($product->unit && $product->type->value !== 'service')
                                    <span class="text-xs text-slate-400 font-normal">/{{ $product->unit }}</span>
                                @endif
                            </td>
                            <td @class([
                                'px-5 py-4 text-right tabular-nums font-extrabold text-xs',
                                'text-rose-600' => $product->stock !== null && $product->stock <= 5,
                                'text-slate-700' => $product->stock === null || $product->stock > 5,
                            ])>
                                {{ $product->stock ?? '∞ (Jasa)' }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span @class([
                                    'rounded-full px-2.5 py-0.5 text-xs font-extrabold border',
                                    'bg-emerald-50 text-emerald-700 border-emerald-200' => $product->is_active,
                                    'bg-slate-100 text-slate-500 border-slate-200' => ! $product->is_active,
                                ])>
                                    {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.products.edit', $product) }}"
                                       class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 hover:border-blue-300 hover:text-[#1B64F2] transition">
                                        Ubah
                                    </a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Hapus produk ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="rounded-xl border border-rose-200 bg-rose-50/70 px-3 py-1.5 text-xs font-bold text-rose-600 hover:bg-rose-100 transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-xs font-semibold text-slate-400">
                                Belum ada produk atau jasa IT terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-4">
        {{ $products->links() }}
    </div>
</x-layouts.admin>
