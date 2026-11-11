<x-layouts.admin :title="$category->exists ? 'Ubah Kategori' : 'Tambah Kategori Produk'">
    @if ($category->exists)
        <!-- Form Ubah 1 Kategori -->
        <div class="max-w-xl space-y-5">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.categories.index') }}"
                       class="flex size-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-blue-300 hover:text-[#1B64F2]">
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-lg font-extrabold text-slate-900">Ubah Nama Kategori</h1>
                        <p class="text-xs text-slate-500">Perbarui nama kategori produk dan layanan IT Anda.</p>
                    </div>
                </div>
            </div>

            <form method="POST"
                action="{{ route('admin.categories.update', $category) }}"
                class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="mb-1.5 block text-xs font-extrabold uppercase tracking-wider text-slate-600">
                        Nama Kategori *
                    </label>
                    <input
                        id="name"
                        name="name"
                        value="{{ old('name', $category->name) }}"
                        required
                        maxlength="100"
                        placeholder="Masukkan nama kategori..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3 text-sm font-bold text-slate-900 transition focus:border-[#1B64F2] focus:bg-white focus:outline-none focus:ring-3 focus:ring-blue-500/10"
                    >
                    @error('name')
                        <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                    @error('slug')
                        <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-2.5 border-t border-slate-100 pt-4">
                    <a href="{{ route('admin.categories.index') }}"
                       class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-600 transition hover:bg-slate-50">
                        Batal
                    </a>
                    <button type="submit"
                        class="rounded-xl bg-[#1B64F2] px-5 py-2.5 text-xs font-extrabold text-white shadow-md shadow-blue-600/20 transition hover:bg-[#1554D1]">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    @else
        @php
            $oldCategories = old('categories');
            $initialCount = is_array($oldCategories) && count($oldCategories) > 0 ? count($oldCategories) : 4;
        @endphp

        <!-- Form Tambah Kategori dengan Pilihan Jumlah -->
        <div class="max-w-4xl space-y-5">
            <!-- Top Navigation & Control Card -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-xs">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <a href="{{ route('admin.categories.index') }}"
                           class="flex size-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-600 transition hover:border-blue-300 hover:bg-blue-50 hover:text-[#1B64F2]"
                           title="Kembali">
                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                        <div>
                            <h1 class="text-lg font-extrabold tracking-tight text-slate-900">Tambah Kategori Baru</h1>
                            <p class="text-xs text-slate-500">
                                Pilih jumlah kategori yang ingin dibuat sekaligus, lalu ketik nama kategorinya.
                            </p>
                        </div>
                    </div>

                    <!-- Pemilih Jumlah Kategori -->
                    <div class="flex flex-wrap items-center gap-2.5">
                        <div class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-slate-50 p-1">
                            <span class="px-2.5 text-[11px] font-extrabold text-slate-500">Jumlah:</span>
                            @foreach ([1, 2, 3, 4, 5] as $num)
                                <button
                                    type="button"
                                    onclick="setCategoryCount({{ $num }})"
                                    data-count-btn="{{ $num }}"
                                    class="count-pill-btn flex size-8 items-center justify-center rounded-lg text-xs font-extrabold transition"
                                >
                                    {{ $num }}
                                </button>
                            @endforeach
                        </div>

                        <select
                            id="category-count-select"
                            onchange="setCategoryCount(parseInt(this.value, 10))"
                            class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-extrabold text-slate-700 shadow-2xs focus:border-[#1B64F2] focus:outline-none"
                        >
                            @for ($n = 1; $n <= 10; $n++)
                                <option value="{{ $n }}" {{ $initialCount === $n ? 'selected' : '' }}>
                                    {{ $n }} Kategori
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-700">
                    <ul class="list-disc space-y-1 pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-4">
                @csrf

                <!-- Container Grid Input Nama Kategori -->
                <div id="categories-grid" class="grid gap-3.5 sm:grid-cols-2"></div>

                <!-- Bottom Action Bar -->
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 sm:px-6 shadow-xs">
                    <button
                        type="button"
                        onclick="addOneCategorySlot()"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-dashed border-blue-300 bg-blue-50/50 px-4 py-2.5 text-xs font-extrabold text-[#1B64F2] transition hover:bg-blue-100/70"
                    >
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Tambah 1 Baris Lagi</span>
                    </button>

                    <div class="flex items-center gap-2.5">
                        <a href="{{ route('admin.categories.index') }}"
                           class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-bold text-slate-600 transition hover:bg-slate-50">
                            Batal
                        </a>
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-[#1B64F2] px-6 py-2.5 text-xs font-extrabold text-white shadow-md shadow-blue-600/20 transition hover:bg-[#1554D1]">
                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Simpan <span id="submit-count-label">{{ $initialCount }}</span> Kategori</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <script>
            const initialOldData = @json($oldCategories ?? []);
            const placeholders = [
                'Laptop & Perangkat Kerja',
                'Jaringan & Infrastruktur Server',
                'Lisensi Software Resmi',
                'Jasa Instalasi & Maintenance IT',
                'Komponen & Aksesoris PC',
                'Keamanan Siber & Firewall',
                'Perangkat Kasir (POS) & Printer',
                'Sewa Perangkat IT Kantor',
                'Peralatan Video Conference',
                'Layanan Backup & Cloud'
            ];

            let categoryItems = [];

            if (Array.isArray(initialOldData) && initialOldData.length > 0) {
                categoryItems = initialOldData.map(item => ({
                    name: item.name || ''
                }));
            } else {
                categoryItems = [
                    { name: '' },
                    { name: '' },
                    { name: '' },
                    { name: '' }
                ];
            }

            function syncCurrentDomValues() {
                const nameInputs = document.querySelectorAll('.cat-name-input');
                nameInputs.forEach((input, idx) => {
                    if (categoryItems[idx]) {
                        categoryItems[idx].name = input.value;
                    }
                });
            }

            function setCategoryCount(targetCount) {
                syncCurrentDomValues();
                const count = Math.max(1, Math.min(10, targetCount));

                while (categoryItems.length < count) {
                    categoryItems.push({ name: '' });
                }
                if (categoryItems.length > count) {
                    categoryItems = categoryItems.slice(0, count);
                }

                renderCategoryCards();
            }

            function addOneCategorySlot() {
                syncCurrentDomValues();
                if (categoryItems.length >= 10) {
                    alert('Maksimal 10 kategori sekaligus dalam satu kali penyimpanan.');
                    return;
                }
                categoryItems.push({ name: '' });
                renderCategoryCards();
            }

            function removeCategorySlot(index) {
                syncCurrentDomValues();
                if (categoryItems.length <= 1) return;
                categoryItems.splice(index, 1);
                renderCategoryCards();
            }

            function escapeHtml(str) {
                return String(str || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            }

            function renderCategoryCards() {
                const grid = document.getElementById('categories-grid');
                const count = categoryItems.length;

                grid.className = count === 1 ? 'grid gap-3.5 max-w-xl' : 'grid gap-3.5 sm:grid-cols-2';

                grid.innerHTML = categoryItems.map((item, i) => {
                    const ph = placeholders[i % placeholders.length];
                    const numBadge = String(i + 1).padStart(2, '0');
                    const removeBtn = count > 1
                        ? `<button type="button" onclick="removeCategorySlot(${i})" class="flex size-7 items-center justify-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-600" title="Hapus baris ini">✕</button>`
                        : '';

                    return `
                        <div class="group relative rounded-2xl border border-slate-200/85 bg-white p-4 sm:p-5 shadow-xs transition hover:border-blue-200 focus-within:border-[#1B64F2] focus-within:ring-3 focus-within:ring-blue-500/10">
                            <div class="mb-3 flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex size-7 items-center justify-center rounded-lg bg-blue-50 text-xs font-extrabold text-[#1B64F2] border border-blue-200/80 group-focus-within:bg-[#1B64F2] group-focus-within:text-white transition">
                                        ${numBadge}
                                    </span>
                                    <label for="cat_name_${i}" class="text-xs font-extrabold uppercase tracking-wider text-slate-700 cursor-pointer">
                                        Nama Kategori #${i + 1}
                                    </label>
                                </div>
                                ${removeBtn}
                            </div>

                            <input
                                id="cat_name_${i}"
                                name="categories[${i}][name]"
                                value="${escapeHtml(item.name)}"
                                oninput="if (categoryItems[${i}]) categoryItems[${i}].name = this.value;"
                                ${i === 0 ? 'required' : ''}
                                maxlength="100"
                                placeholder="Contoh: ${ph}"
                                class="cat-name-input w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm font-bold text-slate-900 placeholder:font-normal placeholder:text-slate-400 transition focus:border-[#1B64F2] focus:bg-white focus:outline-none"
                            >
                        </div>
                    `;
                }).join('');

                // Update active state on pill buttons
                document.querySelectorAll('.count-pill-btn').forEach(btn => {
                    const btnCount = parseInt(btn.getAttribute('data-count-btn'), 10);
                    if (btnCount === count) {
                        btn.className = 'count-pill-btn flex size-8 items-center justify-center rounded-lg bg-[#1B64F2] text-xs font-extrabold text-white shadow-xs transition';
                    } else {
                        btn.className = 'count-pill-btn flex size-8 items-center justify-center rounded-lg text-xs font-extrabold text-slate-600 hover:bg-slate-200/70 transition';
                    }
                });

                const selectEl = document.getElementById('category-count-select');
                if (selectEl) selectEl.value = String(count);

                const submitLabel = document.getElementById('submit-count-label');
                if (submitLabel) submitLabel.textContent = String(count);
            }

            document.addEventListener('DOMContentLoaded', () => {
                renderCategoryCards();
            });
        </script>
    @endif
</x-layouts.admin>
