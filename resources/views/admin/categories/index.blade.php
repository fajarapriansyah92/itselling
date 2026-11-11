<x-layouts.admin title="Kategori Produk">
    <!-- Header Section -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="flex size-11 items-center justify-center rounded-2xl bg-[#1B64F2] text-white shadow-md shadow-blue-600/20">
                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-xl font-extrabold tracking-tight text-slate-900">Kategori Produk & Layanan</h1>
                    <span class="rounded-full bg-blue-50 border border-blue-200 px-2.5 py-0.5 text-xs font-extrabold text-[#1B64F2]">
                        {{ $categories->count() }} Kategori
                    </span>
                </div>
                <p class="text-xs text-slate-500">Kelola pengelompokan katalog perangkat keras, lisensi software, dan jasa IT.</p>
            </div>
        </div>

        <a href="{{ route('admin.categories.create') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-[#1B64F2] px-4 py-2.5 text-xs font-extrabold text-white shadow-md shadow-blue-600/20 transition hover:bg-[#1554D1]">
            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Kategori</span>
        </a>
    </div>

    @if ($categories->isEmpty())
        <!-- Empty State -->
        <section class="rounded-2xl border border-slate-200/80 bg-white p-12 text-center shadow-xs">
            <div class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-blue-50 text-[#1B64F2] border border-blue-100">
                <svg class="size-8" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <h2 class="mt-4 text-base font-extrabold text-slate-900">Belum Ada Kategori Terdaftar</h2>
            <p class="mx-auto mt-1 max-w-md text-xs text-slate-500">
                Tambahkan kategori terlebih dahulu (bisa sekaligus beberapa kategori) sebelum mulai memasukkan katalog produk.
            </p>
            <div class="mt-5">
                <a href="{{ route('admin.categories.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-[#1B64F2] px-5 py-2.5 text-xs font-extrabold text-white shadow-md shadow-blue-600/20 transition hover:bg-[#1554D1]">
                    <span>+ Buat Kategori Sekarang</span>
                </a>
            </div>
        </section>
    @else
        <!-- Clean Table Card -->
        <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/70 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">
                            <th class="w-16 px-5 py-3.5 text-center">No</th>
                            <th class="px-5 py-3.5">Nama Kategori</th>
                            <th class="px-5 py-3.5 text-center">Jumlah Produk</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($categories as $index => $category)
                            <tr class="group transition hover:bg-blue-50/30">
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex size-7 items-center justify-center rounded-lg bg-slate-100 text-xs font-extrabold text-slate-600 group-hover:bg-[#1B64F2] group-hover:text-white transition">
                                        {{ $index + 1 }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="font-extrabold text-slate-900 text-sm">{{ $category->name }}</span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex items-center gap-1.5 rounded-full {{ $category->products_count > 0 ? 'bg-blue-50 text-[#1B64F2] border border-blue-200' : 'bg-slate-100 text-slate-500' }} px-3 py-1 text-xs font-extrabold tabular-nums">
                                        {{ $category->products_count }} Produk
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.categories.edit', $category) }}"
                                           class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:border-blue-300 hover:text-[#1B64F2]">
                                            <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Ubah
                                        </a>
                                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Hapus kategori {{ addslashes($category->name) }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50/70 px-3 py-1.5 text-xs font-bold text-rose-600 transition hover:bg-rose-100">
                                                <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</x-layouts.admin>
