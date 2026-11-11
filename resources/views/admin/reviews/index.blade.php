<x-layouts.admin title="Ulasan Produk & Rating">
    <div class="space-y-4">
        <!-- Top 4 KPI Cards (Medlio Style: 1 Royal Blue Highlight Card + 3 White Cards) -->
        <section class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
            <!-- Card 1: Total Ulasan (Solid Royal Blue) -->
            <div style="background: linear-gradient(135deg, #1E64F0 0%, #1552D6 100%);"
                 class="bg-[#1B64F2] rounded-2xl p-4 text-white shadow-lg shadow-blue-600/20 flex flex-col justify-between">
                <div class="flex items-start justify-between gap-3">
                    <span style="color: #FFFFFF !important;" class="text-xs sm:text-sm font-bold">Total Ulasan</span>
                    <div class="size-8 rounded-xl bg-white text-[#1B64F2] flex items-center justify-center shadow-xs shrink-0">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-1.5">
                    <p style="color: #FFFFFF !important;" class="text-2xl font-extrabold tabular-nums">{{ $stats['total'] }}</p>
                    <p style="color: #DBEAFE !important;" class="text-[11px] font-medium mt-0.5">Ulasan dari pesanan selesai</p>
                </div>
            </div>

            <!-- Card 2: Rata-rata Rating -->
            <div class="card p-4 bg-white flex flex-col justify-between">
                <div class="flex items-start justify-between gap-3">
                    <span class="text-xs sm:text-sm font-bold text-slate-800">Rata-rata Rating</span>
                    <div class="size-6 rounded-md bg-[#1B64F2] text-white flex items-center justify-center shrink-0">
                        <svg class="size-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-1.5">
                    <div class="flex items-baseline gap-2">
                        <p class="text-2xl font-extrabold text-slate-900 tabular-nums">{{ $stats['average'] }}</p>
                        <div class="flex text-amber-400 text-xs">
                            @for ($i = 1; $i <= 5; $i++)
                                <span>{{ $i <= round($stats['average']) ? '★' : '☆' }}</span>
                            @endfor
                        </div>
                    </div>
                    <p class="text-[11px] font-medium text-slate-400 mt-0.5">Skala 1.0 - 5.0 bintang</p>
                </div>
            </div>

            <!-- Card 3: Perlu Dibalas -->
            <div class="card p-4 bg-white flex flex-col justify-between">
                <div class="flex items-start justify-between gap-3">
                    <span class="text-xs sm:text-sm font-bold text-slate-800">Perlu Dibalas</span>
                    <div class="size-6 rounded-md bg-[#1B64F2] text-white flex items-center justify-center shrink-0">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-1.5">
                    <p class="text-2xl font-extrabold text-rose-600 tabular-nums">{{ $stats['needs_reply'] }}</p>
                    <p class="text-[11px] font-medium text-slate-400 mt-0.5">Belum ada respon admin</p>
                </div>
            </div>

            <!-- Card 4: Bintang 5 (Puas) -->
            <div class="card p-4 bg-white flex flex-col justify-between">
                <div class="flex items-start justify-between gap-3">
                    <span class="text-xs sm:text-sm font-bold text-slate-800">Bintang 5 (Puas)</span>
                    <div class="size-6 rounded-md bg-[#1B64F2] text-white flex items-center justify-center shrink-0">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-1.5">
                    <p class="text-2xl font-extrabold text-emerald-600 tabular-nums">{{ $stats['five_stars'] }}</p>
                    <p class="text-[11px] font-medium text-slate-400 mt-0.5">Ulasan sempurna</p>
                </div>
            </div>
        </section>

        <!-- Compact Filter & Search Bar Card -->
        <div class="card p-3.5 bg-white">
            <form method="GET" action="{{ route('admin.reviews.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2.5 items-center">
                <div class="lg:col-span-5 relative">
                    <svg class="size-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari ulasan, produk, invoice, atau nama..."
                           style="padding-left: 2.3rem;"
                           class="field-input text-xs py-2">
                </div>

                <div class="lg:col-span-2">
                    <select name="rating" class="field-input text-xs font-medium py-2">
                        <option value="">Semua Bintang</option>
                        <option value="5" @selected(request('rating') == '5')>★★★★★ (5 Bintang)</option>
                        <option value="4" @selected(request('rating') == '4')>★★★★ (4 Bintang)</option>
                        <option value="3" @selected(request('rating') == '3')>★★★ (3 Bintang)</option>
                        <option value="2" @selected(request('rating') == '2')>★★ (2 Bintang)</option>
                        <option value="1" @selected(request('rating') == '1')>★ (1 Bintang)</option>
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <select name="product_id" class="field-input text-xs font-medium py-2">
                        <option value="">Semua Produk</option>
                        @foreach ($products as $prod)
                            <option value="{{ $prod->id }}" @selected(request('product_id') == $prod->id)>{{ $prod->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-2 flex items-center gap-2">
                    <button type="submit" class="btn-primary flex-1 py-2 px-4 text-xs font-bold rounded-xl">
                        Filter
                    </button>
                    @if (request()->hasAny(['search', 'rating', 'product_id', 'needs_reply']))
                        <a href="{{ route('admin.reviews.index') }}" class="btn-secondary py-2 px-3 text-xs font-bold rounded-xl">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Reviews 2-Column Grid Card (Max 4 per page so no vertical scrolling needed) -->
        <section class="card overflow-hidden bg-white">
            <div class="px-5 py-3.5 border-b border-slate-100 bg-[#F8FAFC] flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2.5">
                    <div class="size-7 rounded-lg bg-amber-50 text-amber-500 flex items-center justify-center border border-amber-200">
                        <svg class="size-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </div>
                    <h2 class="font-extrabold text-sm text-slate-900">Daftar Ulasan Masuk</h2>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[11px] font-medium text-slate-400">
                        Halaman {{ $reviews->currentPage() }} dari {{ max(1, $reviews->lastPage()) }}
                    </span>
                    <span class="rounded-full bg-blue-50 border border-blue-200 px-3 py-0.5 text-xs font-bold text-[#1B64F2]">
                        {{ $reviews->total() }} Ulasan
                    </span>
                </div>
            </div>

            @if ($reviews->isEmpty())
                <div class="p-10 text-center">
                    <div class="size-11 rounded-2xl bg-blue-50 text-[#1B64F2] flex items-center justify-center mx-auto mb-2.5">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-slate-800">Belum Ada Ulasan Produk</p>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Ulasan dari pesanan selesai akan tampil di sini.</p>
                </div>
            @else
                <!-- 2-Column Compact Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 p-4 sm:p-5">
                    @foreach ($reviews as $review)
                        <article class="rounded-2xl border border-slate-200/85 bg-[#F8FAFC]/60 hover:bg-white hover:border-[#93C5FD] p-4 flex flex-col justify-between gap-3 transition shadow-2xs">
                            <div class="space-y-2.5">
                                <!-- Top Row: User + Rating + Delete -->
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="size-9 rounded-full bg-[#EBF3FF] text-[#1B64F2] flex items-center justify-center font-extrabold text-xs shrink-0">
                                            {{ strtoupper(substr($review->user->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="font-extrabold text-slate-900 text-xs truncate">{{ $review->user->name }}</span>
                                                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.2 rounded-full">
                                                    ✓ Terverifikasi
                                                </span>
                                                <a href="{{ route('admin.orders.show', $review->order) }}" class="font-mono text-[11px] font-extrabold text-[#1B64F2] hover:underline">
                                                    #{{ $review->order->order_number }}
                                                </a>
                                            </div>
                                            <p class="text-[11px] text-slate-400">{{ $review->created_at->translatedFormat('d M Y, H:i') }} ({{ $review->created_at->diffForHumans() }})</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <div class="flex items-center gap-1 bg-amber-50 border border-amber-200/80 px-2.5 py-0.5 rounded-full">
                                            <span class="text-amber-400 text-xs leading-none">★</span>
                                            <span class="text-xs font-extrabold text-amber-800">{{ $review->rating }}.0</span>
                                        </div>
                                        <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ulasan ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus ulasan"
                                                    class="size-7 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:text-rose-600 hover:border-rose-200 hover:bg-rose-50 transition cursor-pointer">
                                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Compact Product Pill + Review Comment -->
                                <div class="rounded-xl bg-white border border-slate-200/80 p-3 space-y-1.5">
                                    <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-1.5">
                                        <a href="{{ route('admin.products.edit', $review->product) }}" class="text-xs font-bold text-[#1B64F2] hover:underline truncate">
                                            {{ $review->product->name }}
                                        </a>
                                        <span class="text-[10px] font-mono text-slate-400 shrink-0">{{ $review->product->sku }}</span>
                                    </div>
                                    <p class="text-xs text-slate-700 font-medium leading-relaxed line-clamp-2" title="{{ $review->comment }}">
                                        "{{ $review->comment }}"
                                    </p>
                                </div>
                            </div>

                            <!-- Compact Reply Footer -->
                            <div>
                                @if ($review->admin_reply)
                                    <div class="bg-[#EFF6FF] border-l-3 border-[#1B64F2] px-3 py-2 rounded-r-xl">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-[11px] font-extrabold text-[#1B64F2]">Respon Toko</span>
                                            <span class="text-[10px] text-slate-400 font-mono">{{ $review->admin_replied_at?->translatedFormat('d M Y') }}</span>
                                        </div>
                                        <p class="text-xs text-slate-700 font-medium line-clamp-1 mt-0.5" title="{{ $review->admin_reply }}">
                                            {{ $review->admin_reply }}
                                        </p>
                                        <details class="mt-1">
                                            <summary class="text-[11px] font-bold text-[#1B64F2] hover:underline cursor-pointer select-none">
                                                Edit Balasan
                                            </summary>
                                            <form action="{{ route('admin.reviews.reply', $review) }}" method="POST" class="mt-2 space-y-2">
                                                @csrf
                                                <textarea name="admin_reply" rows="2" required class="field-input bg-white text-xs py-1.5">{{ $review->admin_reply }}</textarea>
                                                <button type="submit" class="btn-primary rounded-full px-3.5 py-1 text-[11px] font-bold">
                                                    Simpan
                                                </button>
                                            </form>
                                        </details>
                                    </div>
                                @else
                                    <details class="rounded-xl border border-dashed border-blue-200 bg-blue-50/40 px-3 py-2">
                                        <summary class="text-xs font-bold text-[#1B64F2] hover:underline cursor-pointer flex items-center justify-between select-none">
                                            <span>+ Balas Ulasan Ini</span>
                                            <span class="text-[10px] font-semibold text-rose-500">Belum dibalas</span>
                                        </summary>
                                        <form action="{{ route('admin.reviews.reply', $review) }}" method="POST" class="mt-2.5 space-y-2">
                                            @csrf
                                            <textarea name="admin_reply" rows="2" required placeholder="Tuliskan tanggapan resmi dari toko..."
                                                      class="field-input bg-white text-xs py-1.5"></textarea>
                                            <button type="submit" class="btn-primary rounded-full px-4 py-1 text-[11px] font-bold">
                                                Kirim Balasan
                                            </button>
                                        </form>
                                    </details>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($reviews->hasPages())
                    <div class="px-5 py-3.5 border-t border-slate-100 bg-[#F8FAFC]">
                        {{ $reviews->links() }}
                    </div>
                @endif
            @endif
        </section>
    </div>
</x-layouts.admin>
