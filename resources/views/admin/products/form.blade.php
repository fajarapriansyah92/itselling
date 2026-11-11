<x-layouts.admin :title="$product->exists ? 'Ubah Produk' : 'Tambah Produk'">
    <form method="POST" enctype="multipart/form-data"
        action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}"
        class="card max-w-3xl space-y-5 p-6">
        @csrf
        @if ($product->exists)
            @method('PUT')
        @endif

        <div class="grid gap-5 sm:grid-cols-2">
            <x-field label="Nama produk" name="name" class="sm:col-span-2">
                <input id="name" name="name" value="{{ old('name', $product->name) }}" required class="field-input">
                @error('slug')
                    <p class="text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </x-field>

            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label for="sku" class="block text-xs font-extrabold uppercase tracking-wider text-slate-600">SKU (Kode Produk)</label>
                    <button type="button" id="btn-generate-sku" title="Generate SKU otomatis sesuai kategori produk" class="inline-flex items-center gap-1 text-[11px] font-extrabold text-[#1B64F2] hover:underline transition">
                        <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>Auto Buat</span>
                    </button>
                </div>
                <div class="relative">
                    <input id="sku" name="sku" value="{{ old('sku', $product->sku) }}" maxlength="50" placeholder="Klik untuk auto-generate (contoh: HDW-001)" class="field-input pr-9">
                    <div id="sku-spinner" class="hidden absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                        <div class="size-3.5 animate-spin rounded-full border-2 border-[#1B64F2] border-t-transparent"></div>
                    </div>
                </div>
                <p class="text-[11px] text-slate-500">
                    Otomatis menyesuaikan kategori: <span class="font-bold text-slate-700">HDW</span> (Hardware), <span class="font-bold text-slate-700">SRV</span> (Jasa), <span class="font-bold text-slate-700">LIC</span> (Lisensi).
                </p>
                @error('sku')
                    <p class="text-xs text-rose-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <x-field label="Merek (Opsional)" name="brand" hint="Kosongkan jika menambahkan layanan / jasa IT.">
                <input id="brand" name="brand" value="{{ old('brand', $product->brand) }}" maxlength="100" placeholder="Contoh: Mikrotik, Lenovo (Kosongkan untuk Jasa)" class="field-input">
            </x-field>

            <x-field label="Kategori" name="category_id">
                <select id="category_id" name="category_id" required class="field-input">
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </x-field>

            <x-field label="Harga (Rp)" name="price">
                <input id="price" name="price" type="number" min="0" step="1000" value="{{ old('price', $product->price !== null ? (int) $product->price : null) }}" required class="field-input">
            </x-field>

            <x-field label="Stok (Opsional untuk Jasa)" name="stock" hint="Kosongkan jika kategori berupa layanan / jasa IT.">
                <input id="stock" name="stock" type="number" min="0" value="{{ old('stock', $product->stock) }}" placeholder="Contoh: 10 (Kosongkan untuk Jasa)" class="field-input">
            </x-field>

            <x-field label="Satuan (Opsional untuk Jasa)" name="unit" hint="Kosongkan jika kategori berupa layanan / jasa IT.">
                <input id="unit" name="unit" value="{{ old('unit', $product->exists ? $product->unit : '') }}" maxlength="30" placeholder="Contoh: unit / lisensi (Kosongkan untuk Jasa)" class="field-input">
            </x-field>

            <x-field label="Deskripsi Produk" name="description" hint="Jelaskan spesifikasi, cakupan pekerjaan, atau fitur produk. Ringkasan di aplikasi mobile akan dibuatkan otomatis dari deskripsi ini." class="sm:col-span-2">
                <textarea id="description" name="description" rows="5" required placeholder="Tuliskan spesifikasi produk, fitur lengkap, atau cakupan layanan di sini..." class="field-input leading-relaxed">{{ old('description', $product->description) }}</textarea>
            </x-field>

            <x-field label="Gambar produk" name="image" hint="Mendukung format gambar (JPG, JPEG, PNG, WEBP, GIF, SVG, BMP) hingga 10 MB. Gambar yang diunggah akan langsung tampil di aplikasi mobile (APK)." class="sm:col-span-2">
                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                    <div class="flex flex-wrap items-center gap-5">
                        <div id="image-preview-container" class="relative">
                            @if ($product->image_url)
                                <img id="image-preview" src="{{ $product->image_url }}" alt="Preview" class="size-24 rounded-xl object-cover border border-slate-200 shadow-xs bg-white">
                            @else
                                <img id="image-preview" src="" alt="Preview" class="hidden size-24 rounded-xl object-cover border border-slate-200 shadow-xs bg-white">
                                <div id="image-placeholder" class="grid size-24 place-items-center rounded-xl bg-white text-xs font-bold text-slate-400 border border-dashed border-slate-300">
                                    No Image
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 min-w-[240px] space-y-3">
                            @if ($product->exists)
                                <div class="flex flex-wrap items-center gap-2.5">
                                    <label class="cursor-pointer inline-flex items-center gap-2 rounded-xl bg-[#1B64F2] px-4 py-2.5 text-xs font-extrabold text-white shadow-md shadow-blue-600/20 hover:bg-[#1554D1] transition">
                                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                        <span>{{ $product->image_url ? 'Ganti Gambar' : 'Tambah Gambar' }}</span>
                                        <input type="file" id="direct-image-input" name="image" form="direct-image-form" accept="image/*" class="hidden" onchange="document.getElementById('direct-image-form').submit()">
                                    </label>

                                    @if ($product->image_url)
                                        <button type="submit" form="destroy-image-form" class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-xs font-bold text-rose-600 hover:bg-rose-100 transition" onclick="return confirm('Yakin ingin menghapus gambar produk ini?')">
                                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <span>Hapus Gambar</span>
                                        </button>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-500">
                                    Klik tombol di atas untuk menambah atau menghapus gambar secara instan.
                                </p>
                            @else
                                <input id="image" name="image" type="file" accept="image/*" class="text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-extrabold file:bg-[#1B64F2] file:text-white hover:file:bg-[#1554D1] cursor-pointer">
                                <p class="text-xs text-slate-500">Pilih gambar untuk produk baru ini.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </x-field>
        </div>

        <label class="flex items-center gap-2.5 text-sm font-semibold text-slate-700 cursor-pointer">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active)) class="size-4 rounded border-slate-300 text-[#1B64F2] focus:ring-[#1B64F2]">
            Tampilkan produk di aplikasi mobile
        </label>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" name="stay" value="1" class="btn-primary">
                {{ $product->exists ? 'Simpan Perubahan' : 'Simpan Produk' }}
            </button>
            <a href="{{ route('admin.products.index') }}" class="btn-secondary">Kembali ke Katalog</a>
        </div>
    </form>

    @if ($product->exists)
        <form id="direct-image-form" method="POST" action="{{ route('admin.products.upload-image', $product) }}" enctype="multipart/form-data" class="hidden">
            @csrf
        </form>
        <form id="destroy-image-form" method="POST" action="{{ route('admin.products.destroy-image', $product) }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endif

    <script>
        document.getElementById('image')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const preview = document.getElementById('image-preview');
                const placeholder = document.getElementById('image-placeholder');
                preview.src = URL.createObjectURL(file);
                preview.classList.remove('hidden');
                if (placeholder) placeholder.classList.add('hidden');
            }
        });

        // SKU Auto-generation & Jasa Category Detection logic
        const skuInput = document.getElementById('sku');
        const categorySelect = document.getElementById('category_id');
        const stockInput = document.getElementById('stock');
        const brandInput = document.getElementById('brand');
        const unitInput = document.getElementById('unit');
        const btnGenerateSku = document.getElementById('btn-generate-sku');
        const skuSpinner = document.getElementById('sku-spinner');

        function syncCategoryServiceState() {
            if (!categorySelect) return;
            const selectedText = (categorySelect.options[categorySelect.selectedIndex]?.text || '').toLowerCase();
            const isServiceCategory = /(jasa|layanan|service|servis|instalasi|maintenance|perbaikan|konsultasi|support)/i.test(selectedText);

            if (stockInput) {
                stockInput.placeholder = isServiceCategory
                    ? 'Tidak perlu diisi untuk Jasa (∞)'
                    : 'Contoh: 10 (Kosongkan untuk Jasa)';
                if (isServiceCategory && {{ $product->exists ? 'false' : 'true' }}) {
                    stockInput.value = '';
                }
            }

            if (brandInput) {
                brandInput.placeholder = isServiceCategory
                    ? 'Tidak perlu diisi untuk Jasa'
                    : 'Contoh: Mikrotik, Lenovo (Opsional)';
            }

            if (unitInput) {
                unitInput.placeholder = isServiceCategory
                    ? 'Tidak perlu diisi untuk Jasa (Opsional)'
                    : 'Contoh: unit / lisensi (Opsional untuk Jasa)';
                if (isServiceCategory && {{ $product->exists ? 'false' : 'true' }}) {
                    unitInput.value = '';
                }
            }
        }

        async function fetchOrGenerateSku(force = false) {
            if (!skuInput) return;
            if (!force && skuInput.value.trim() !== '') return;

            const categoryId = categorySelect ? categorySelect.value : '';
            if (skuSpinner) skuSpinner.classList.remove('hidden');

            try {
                const response = await fetch(`{{ route('admin.products.generate-sku') }}?category_id=${encodeURIComponent(categoryId)}`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (response.ok) {
                    const data = await response.json();
                    if (data.sku) {
                        skuInput.value = data.sku;
                        skuInput.classList.add('ring-2', 'ring-blue-400');
                        setTimeout(() => skuInput.classList.remove('ring-2', 'ring-blue-400'), 600);
                        return;
                    }
                }
                throw new Error('Fallback needed');
            } catch (err) {
                const randomNum = String(Math.floor(100 + Math.random() * 900));
                skuInput.value = `HDW-${randomNum}`;
            } finally {
                if (skuSpinner) skuSpinner.classList.add('hidden');
            }
        }

        skuInput?.addEventListener('focus', function() {
            fetchOrGenerateSku(false);
        });
        skuInput?.addEventListener('click', function() {
            fetchOrGenerateSku(false);
        });

        btnGenerateSku?.addEventListener('click', function(e) {
            e.preventDefault();
            fetchOrGenerateSku(true);
        });

        categorySelect?.addEventListener('change', function() {
            syncCategoryServiceState();
            const val = skuInput.value.trim();
            const isNew = {{ $product->exists ? 'false' : 'true' }};
            const isAutoFormat = /^(HDW|LIC|SRV)-\d+$/i.test(val);

            if (isNew && (val === '' || isAutoFormat)) {
                fetchOrGenerateSku(true);
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            syncCategoryServiceState();
        });
    </script>
</x-layouts.admin>
