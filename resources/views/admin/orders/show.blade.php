<x-layouts.admin :title="'Pesanan #' . $order->order_number">
    @php
        $isDigital = $order->items->every(fn ($item) => optional($item->product)->type === \App\Enums\ProductType::Software);
        $isService = $order->items->every(fn ($item) => optional($item->product)->type === \App\Enums\ProductType::Service);
        $isHardware = ! $isDigital && ! $isService;

        $orderTypeLabel = $isDigital ? 'Software & Lisensi' : ($isService ? 'Jasa & Layanan IT' : 'Hardware & Barang Fisik');
    @endphp

    <!-- Web Interactive View (Hidden on Print) -->
    <div class="space-y-5 print:hidden">
    <!-- Breadcrumb & Top Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <a href="{{ route('admin.orders.index') }}"
           class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-[#1B64F2] transition">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Daftar Pesanan</span>
        </a>
        <div class="flex items-center gap-2.5">
            <button onclick="window.print()"
                    class="rounded-full border border-slate-200 bg-white text-slate-700 hover:border-[#1B64F2] hover:text-[#1B64F2] py-2 px-4 text-xs font-bold inline-flex items-center gap-2 shadow-2xs transition cursor-pointer">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Cetak Nota / Invoice</span>
            </button>
        </div>
    </div>

    <!-- Order Header Hero Card -->
    <div class="card p-6 bg-white">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2.5 mb-2">
                    <span class="font-mono text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                        #{{ $order->order_number }}
                    </span>
                    <button type="button"
                            onclick="copyWithToast('{{ $order->order_number }}', 'Nomor pesanan #{{ $order->order_number }} berhasil disalin!')"
                            class="text-slate-400 hover:text-[#1B64F2] transition p-1.5 rounded-lg hover:bg-blue-50 cursor-pointer"
                            title="Salin nomor pesanan">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </button>
                    <x-order-status :status="$order->status" />

                    @if ($isService)
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-purple-200 bg-purple-50 px-3 py-1 text-xs font-bold text-purple-700">
                            Jasa / Layanan IT
                        </span>
                    @elseif ($isDigital)
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-bold text-sky-700">
                            Produk Lisensi Digital
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-[#1B64F2]">
                            Hardware / Fisik
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 font-medium">
                    Dipesan pada <span class="text-slate-800 font-bold">{{ $order->created_at->translatedFormat('d F Y, H:i') }} WIB</span>
                    &bull; Klien: <span class="text-slate-800 font-bold">{{ $order->user->name }}</span>
                </p>
            </div>

            <div class="text-left lg:text-right border-t lg:border-t-0 pt-3 lg:pt-0 border-slate-100">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Transaksi</p>
                <p class="text-2xl lg:text-3xl font-extrabold text-[#1B64F2] tabular-nums font-mono">@rupiah($order->total)</p>
            </div>
        </div>

        <!-- Workflow Step Progress & Quick Action Hero -->
        <div class="mt-6 pt-6 border-t border-slate-100">
            @php
                $statusVal = $order->status->value;
                $isCompleted = $statusVal === 'completed';
                $isCancelled = $statusVal === 'cancelled';
                $isProcessing = $statusVal === 'processing';
                $isShipped = in_array($statusVal, ['shipped', 'delivered'], true);
                $isPaid = $statusVal === 'paid';
                $isPending = $statusVal === 'pending';
            @endphp

            @if (! $isCancelled)
                @if ($isHardware)
                    @php
                        $currentStep = match ($statusVal) {
                            'pending' => 1,
                            'paid' => 2,
                            'processing' => 3,
                            'shipped', 'delivered' => 4,
                            'completed' => 5,
                            default => 1,
                        };
                        $steps = [
                            1 => ['title' => '1. Dipesan', 'sub' => $order->created_at->translatedFormat('d M, H:i')],
                            2 => ['title' => '2. Pembayaran', 'sub' => $order->paid_at ? $order->paid_at->translatedFormat('d M, H:i') : ($isPending ? 'Menunggu' : 'Terverifikasi')],
                            3 => ['title' => '3. Kemas Gudang', 'sub' => $isProcessing ? 'Sedang Dikemas' : ($currentStep > 3 ? 'Selesai Dikemas' : 'Menunggu')],
                            4 => ['title' => '4. Pengiriman', 'sub' => $isShipped ? 'Dalam Perjalanan' : ($currentStep > 4 ? 'Telah Sampai' : 'Belum Dikirim')],
                            5 => ['title' => '5. Selesai', 'sub' => $order->completed_at ? $order->completed_at->translatedFormat('d M, H:i') : 'Menunggu Selesai'],
                        ];
                    @endphp
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5 mb-5">
                        @foreach ($steps as $num => $step)
                            @php
                                $done = $currentStep > $num || ($num === 5 && $currentStep === 5);
                                $curr = $currentStep === $num;
                            @endphp
                            <div class="rounded-xl border p-3 transition {{ $curr ? 'bg-[#EFF6FF] border-[#1B64F2] shadow-2xs' : ($done ? 'bg-slate-50 border-blue-200' : 'bg-slate-50/60 border-slate-200/70 opacity-65') }}">
                                <div class="flex items-center gap-2">
                                    <div class="size-5 rounded-full text-[10px] font-extrabold flex items-center justify-center shrink-0 {{ $curr || $done ? 'bg-[#1B64F2] text-white' : 'bg-slate-200 text-slate-500' }}">
                                        {{ $done && ! $curr ? '✓' : $num }}
                                    </div>
                                    <span class="text-xs font-bold {{ $curr ? 'text-[#1B64F2]' : ($done ? 'text-slate-800' : 'text-slate-500') }}">
                                        {{ $step['title'] }}
                                    </span>
                                </div>
                                <p class="text-[10px] mt-1 font-mono {{ $curr ? 'text-[#1B64F2] font-semibold' : 'text-slate-400' }}">
                                    {{ $step['sub'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @elseif ($isService)
                    @php
                        $currentStep = match ($statusVal) {
                            'pending' => 1,
                            'paid' => 2,
                            'processing' => 3,
                            'completed' => 4,
                            default => 1,
                        };
                        $steps = [
                            1 => ['title' => '1. Dipesan', 'sub' => $order->created_at->translatedFormat('d M, H:i')],
                            2 => ['title' => '2. Pembayaran', 'sub' => $order->paid_at ? $order->paid_at->translatedFormat('d M, H:i') : ($isPending ? 'Menunggu' : 'Terverifikasi')],
                            3 => ['title' => '3. Pengerjaan Jasa', 'sub' => $isProcessing ? 'Teknisi Bekerja' : ($currentStep > 3 ? 'Selesai Dikerjakan' : 'Menunggu')],
                            4 => ['title' => '4. Selesai', 'sub' => $order->completed_at ? $order->completed_at->translatedFormat('d M, H:i') : 'Menunggu'],
                        ];
                    @endphp
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 mb-5">
                        @foreach ($steps as $num => $step)
                            @php
                                $done = $currentStep > $num || ($num === 4 && $currentStep === 4);
                                $curr = $currentStep === $num;
                            @endphp
                            <div class="rounded-xl border p-3 transition {{ $curr ? 'bg-[#EFF6FF] border-[#1B64F2] shadow-2xs' : ($done ? 'bg-slate-50 border-blue-200' : 'bg-slate-50/60 border-slate-200/70 opacity-65') }}">
                                <div class="flex items-center gap-2">
                                    <div class="size-5 rounded-full text-[10px] font-extrabold flex items-center justify-center shrink-0 {{ $curr || $done ? 'bg-[#1B64F2] text-white' : 'bg-slate-200 text-slate-500' }}">
                                        {{ $done && ! $curr ? '✓' : $num }}
                                    </div>
                                    <span class="text-xs font-bold {{ $curr ? 'text-[#1B64F2]' : ($done ? 'text-slate-800' : 'text-slate-500') }}">
                                        {{ $step['title'] }}
                                    </span>
                                </div>
                                <p class="text-[10px] mt-1 font-mono {{ $curr ? 'text-[#1B64F2] font-semibold' : 'text-slate-400' }}">
                                    {{ $step['sub'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @else
                    @php
                        $currentStep = match ($statusVal) {
                            'pending' => 1,
                            'paid' => 2,
                            'completed' => 3,
                            default => 1,
                        };
                        $steps = [
                            1 => ['title' => '1. Dipesan', 'sub' => $order->created_at->translatedFormat('d M, H:i')],
                            2 => ['title' => '2. Pembayaran & Lisensi', 'sub' => $order->paid_at ? $order->paid_at->translatedFormat('d M, H:i') : ($isPending ? 'Menunggu' : 'Terverifikasi')],
                            3 => ['title' => '3. Selesai', 'sub' => $order->completed_at ? $order->completed_at->translatedFormat('d M, H:i') : 'Menunggu'],
                        ];
                    @endphp
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 mb-5">
                        @foreach ($steps as $num => $step)
                            @php
                                $done = $currentStep > $num || ($num === 3 && $currentStep === 3);
                                $curr = $currentStep === $num;
                            @endphp
                            <div class="rounded-xl border p-3 transition {{ $curr ? 'bg-[#EFF6FF] border-[#1B64F2] shadow-2xs' : ($done ? 'bg-slate-50 border-blue-200' : 'bg-slate-50/60 border-slate-200/70 opacity-65') }}">
                                <div class="flex items-center gap-2">
                                    <div class="size-5 rounded-full text-[10px] font-extrabold flex items-center justify-center shrink-0 {{ $curr || $done ? 'bg-[#1B64F2] text-white' : 'bg-slate-200 text-slate-500' }}">
                                        {{ $done && ! $curr ? '✓' : $num }}
                                    </div>
                                    <span class="text-xs font-bold {{ $curr ? 'text-[#1B64F2]' : ($done ? 'text-slate-800' : 'text-slate-500') }}">
                                        {{ $step['title'] }}
                                    </span>
                                </div>
                                <p class="text-[10px] mt-1 font-mono {{ $curr ? 'text-[#1B64F2] font-semibold' : 'text-slate-400' }}">
                                    {{ $step['sub'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif

            <!-- Contextual Quick Action Banner -->
            @if ($isPending)
                <div class="rounded-2xl border border-amber-200 bg-amber-50/70 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3.5">
                        <div class="size-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-slate-900 text-sm">Menunggu Pembayaran via Payment Gateway</h3>
                            <p class="text-xs text-slate-600 mt-0.5">
                                Tagihan diproses via Midtrans ({{ $order->payment_channel ?? 'Virtual Account' }}). Status otomatis menjadi Lunas setelah dibayar.
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('payment.web', $order) }}" target="_blank"
                       class="btn-primary rounded-full px-5 py-2.5 text-xs font-bold shrink-0">
                        Buka Simulator Web Bayar
                    </a>
                </div>
            @elseif ($isHardware)
                @if ($isPaid)
                    <div class="rounded-2xl border border-blue-200 bg-[#EFF6FF] p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-3.5">
                            <div class="size-10 rounded-xl bg-[#1B64F2] text-white flex items-center justify-center shrink-0 shadow-xs">
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-sm">Pembayaran Diterima — Siap Diproses & Dikemas</h3>
                                <p class="text-xs text-slate-600 mt-0.5">Pembayaran telah lunas. Klik tombol di samping untuk mulai pengemasan barang hardware di gudang.</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="shrink-0">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="processing">
                            <button type="submit"
                                    class="btn-primary rounded-full px-5 py-2.5 text-xs font-bold"
                                    onclick="return confirm('Mulai proses pengemasan barang hardware di gudang?')">
                                Mulai Proses & Kemas Barang
                            </button>
                        </form>
                    </div>
                @elseif ($isProcessing)
                    <div class="rounded-2xl border border-blue-200 bg-[#EFF6FF] p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-3.5">
                            <div class="size-10 rounded-xl bg-[#1B64F2] text-white flex items-center justify-center shrink-0 shadow-xs">
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-sm">Barang Siap Dikirim ke Ekspedisi</h3>
                                <p class="text-xs text-slate-600 mt-0.5">Klik "Kirim Barang" untuk men-generate nomor resi otomatis dan menginfokan kurir ke pelanggan.</p>
                            </div>
                        </div>
                        @php
                            $randomCouriers = ['JNE', 'J&T', 'SiCepat', 'TIKI', 'Anteraja'];
                            $generatedCourier = $randomCouriers[array_rand($randomCouriers)];
                            $generatedResi = $generatedCourier . ' - ' . rand(1000000000, 9999999999);
                        @endphp
                        <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="shrink-0">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="shipped">
                            <input type="hidden" name="tracking_number" value="{{ $generatedResi }}">
                            <button type="submit"
                                    class="btn-primary rounded-full px-5 py-2.5 text-xs font-bold"
                                    onclick="return confirm('Kirim barang sekarang? Ekspedisi otomatis: {{ $generatedResi }}')">
                                Kirim Barang
                            </button>
                        </form>
                    </div>
                @elseif ($isShipped)
                    <div class="rounded-2xl border border-blue-200 bg-[#EFF6FF] p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-3.5">
                            <div class="size-10 rounded-xl bg-[#1B64F2] text-white flex items-center justify-center shrink-0 shadow-xs">
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-sm">Barang Dalam Perjalanan Pengiriman</h3>
                                <p class="text-xs text-slate-600 mt-0.5">
                                    Resi Ekspedisi: <span class="font-mono font-bold text-[#1B64F2]">{{ $order->tracking_number ?? '-' }}</span>.
                                </p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="shrink-0">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="completed">
                            <button type="submit"
                                    class="btn-primary rounded-full px-5 py-2.5 text-xs font-bold"
                                    onclick="return confirm('Apakah barang hardware telah sampai di tangan pembeli?')">
                                Konfirmasi Sampai & Selesai
                            </button>
                        </form>
                    </div>
                @elseif ($isCompleted)
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-4 flex items-center justify-between gap-4 flex-wrap">
                        <div class="flex items-center gap-3.5">
                            <div class="size-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0">
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </div>
                            <div>
                                <p class="font-extrabold text-slate-900 text-xs sm:text-sm">Pesanan Hardware Telah Selesai</p>
                                <p class="text-[11px] text-slate-600">Barang telah diterima pembeli. Resi: <span class="font-mono font-bold text-emerald-700">{{ $order->tracking_number ?? '-' }}</span> &bull; Selesai pada {{ $order->completed_at?->translatedFormat('d F Y, H:i') }}.</p>
                            </div>
                        </div>
                    </div>
                @endif
            @elseif ($isService)
                @if ($isPaid)
                    <div class="rounded-2xl border border-purple-200 bg-purple-50/70 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-3.5">
                            <div class="size-10 rounded-xl bg-purple-600 text-white flex items-center justify-center shrink-0">
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-sm">Pembayaran Diterima — Jadwalkan Pengerjaan Teknisi</h3>
                                <p class="text-xs text-slate-600 mt-0.5">Klik tombol di samping untuk menugaskan teknisi dan mulai pengerjaan layanan IT.</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="shrink-0">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="processing">
                            <button type="submit"
                                    class="btn-primary rounded-full px-5 py-2.5 text-xs font-bold"
                                    onclick="return confirm('Mulai proses pengerjaan jasa oleh teknisi?')">
                                Kerjakan oleh Teknisi
                            </button>
                        </form>
                    </div>
                @elseif ($isProcessing)
                    <div class="rounded-2xl border border-purple-200 bg-purple-50/70 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-3.5">
                            <div class="size-10 rounded-xl bg-purple-600 text-white flex items-center justify-center shrink-0">
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-sm">Teknisi Sedang Mengerjakan Layanan IT</h3>
                                <p class="text-xs text-slate-600 mt-0.5">Setelah pengerjaan selesai dan sistem teruji, klik tombol di samping.</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="shrink-0">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="completed">
                            <button type="submit"
                                    class="btn-primary rounded-full px-5 py-2.5 text-xs font-bold"
                                    onclick="return confirm('Pekerjaan jasa telah tuntas dikerjakan dan diverifikasi?')">
                                Tandai Pengerjaan Selesai
                            </button>
                        </form>
                    </div>
                @elseif ($isCompleted)
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-4 flex items-center gap-3.5">
                        <div class="size-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        </div>
                        <div>
                            <p class="font-extrabold text-slate-900 text-xs sm:text-sm">Pengerjaan Jasa IT Telah Selesai</p>
                            <p class="text-[11px] text-slate-600">Pesanan telah tuntas dikerjakan pada {{ $order->completed_at?->translatedFormat('d F Y, H:i') }}.</p>
                        </div>
                    </div>
                @endif
            @elseif ($isDigital)
                @if ($isPaid)
                    <div class="rounded-2xl border border-blue-200 bg-[#EFF6FF] p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-3.5">
                            <div class="size-10 rounded-xl bg-[#1B64F2] text-white flex items-center justify-center shrink-0">
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-sm">Lisensi Digital Telah Diterbitkan</h3>
                                <p class="text-xs text-slate-600 mt-0.5">Kode lisensi sudah muncul di aplikasi pelanggan. Anda dapat menyelesaikan pesanan ini.</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="shrink-0">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="completed">
                            <button type="submit"
                                    class="btn-primary rounded-full px-5 py-2.5 text-xs font-bold"
                                    onclick="return confirm('Tandai pesanan software ini sebagai Selesai?')">
                                Selesaikan Pesanan
                            </button>
                        </form>
                    </div>
                @elseif ($isCompleted)
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-4 flex items-center gap-3.5">
                        <div class="size-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        </div>
                        <div>
                            <p class="font-extrabold text-slate-900 text-xs sm:text-sm">Lisensi Software Telah Aktif & Selesai</p>
                            <p class="text-[11px] text-slate-600">Kode lisensi telah diterima pelanggan dan pesanan berstatus Selesai.</p>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>

    <!-- Main 2-Column Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Left Column: Items, Payment Gateway & Live Chat -->
        <div class="lg:col-span-2 space-y-5">
            <!-- Order Items Card -->
            <section class="card overflow-hidden bg-white">
                <div class="flex items-center justify-between border-b border-slate-100 bg-[#F8FAFC] px-6 py-4">
                    <div>
                        <h2 class="font-extrabold text-slate-900 text-sm">Rincian Item & Layanan</h2>
                        <p class="text-xs text-slate-500">Daftar produk, paket layanan, atau lisensi dalam pesanan ini</p>
                    </div>
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-[11px] font-extrabold text-[#1B64F2] border border-blue-200">
                        {{ $order->items->count() }} Item
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="table-base">
                        <thead>
                            <tr>
                                <th class="text-left">Deskripsi Item</th>
                                <th class="text-right">Harga Satuan</th>
                                <th class="text-center">Qty</th>
                                <th class="text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr>
                                    <td>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="font-bold text-slate-900 text-sm">{{ $item->product_name }}</p>
                                            @if ($item->product?->type)
                                                <span class="rounded-md px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-[#1B64F2] border border-blue-200">
                                                    {{ $item->product->type->label() }}
                                                </span>
                                            @endif
                                        </div>
                                        @if ($item->product?->sku)
                                            <p class="text-[11px] text-slate-400 font-mono mt-0.5">SKU: {{ $item->product->sku }}</p>
                                        @endif
                                    </td>
                                    <td class="text-right whitespace-nowrap tabular-nums text-slate-600 font-mono text-xs">@rupiah($item->price)</td>
                                    <td class="text-center font-bold text-slate-800 tabular-nums text-xs">{{ $item->quantity }}</td>
                                    <td class="text-right whitespace-nowrap font-extrabold text-slate-900 tabular-nums font-mono text-xs">@rupiah($item->subtotal)</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t border-slate-200 bg-[#F8FAFC]">
                            <tr>
                                <td colspan="3" class="py-4 px-6 text-right font-extrabold text-slate-500 uppercase text-xs tracking-wider">Total Pembayaran</td>
                                <td class="py-4 px-6 text-right text-base font-extrabold whitespace-nowrap text-[#1B64F2] tabular-nums font-mono">@rupiah($order->total)</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>

            <!-- Payment Gateway Transaction Card -->
            <section class="card p-6 bg-white">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="font-extrabold text-slate-900 text-sm">Informasi Payment Gateway (Midtrans)</h2>
                        <p class="text-xs text-slate-500">Transaksi diproses secara otomatis via REST API & Webhook Midtrans</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 border border-emerald-200">
                        <span class="size-1.5 rounded-full bg-emerald-500"></span>
                        Gateway Aktif
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div class="rounded-xl bg-[#F8FAFC] border border-slate-200/80 p-4">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Metode Pembayaran</span>
                        <span class="block text-sm font-extrabold text-slate-900 mt-1">
                            {{ $order->payment_channel ?? ($order->payment_method === 'credit_card' ? 'Kartu Kredit' : 'Virtual Account') }}
                        </span>
                    </div>
                    <div class="rounded-xl bg-[#F8FAFC] border border-slate-200/80 p-4">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nomor VA / Akun</span>
                        <span class="block text-sm font-mono font-extrabold text-[#1B64F2] mt-1">
                            {{ $order->va_number ?? ($order->payment_method === 'credit_card' ? '4811-••••-••••-1114' : 'Auto Generated') }}
                        </span>
                    </div>
                    <div class="rounded-xl bg-[#F8FAFC] border border-slate-200/80 p-4">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status Transaksi</span>
                        <span class="block text-sm font-extrabold {{ $isPaid || $order->status->value === 'completed' || $order->status->value === 'shipped' ? 'text-emerald-600' : 'text-amber-600' }} mt-1">
                            {{ $order->status->label() }}
                        </span>
                    </div>
                </div>
            </section>

            <!-- Live Chat Pesanan -->
            <section id="chat-section" class="card overflow-hidden bg-white print:hidden">
                <div class="flex items-center justify-between border-b border-slate-100 bg-[#F8FAFC] px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="size-8 rounded-lg bg-blue-50 text-[#1B64F2] flex items-center justify-center border border-blue-200">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-extrabold text-slate-900 text-sm">Live Chat Pesanan dengan Pelanggan</h2>
                            <p class="text-xs text-slate-500">Komunikasi langsung terkait pengiriman, teknisi, atau resi pesanan</p>
                        </div>
                    </div>
                    <span id="chat-count-badge" class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-[#1B64F2] border border-blue-200">
                        {{ $order->messages->count() }} Pesan
                    </span>
                </div>

                <!-- Messages Container -->
                <div class="p-6 max-h-[380px] overflow-y-auto space-y-4 bg-[#F8FAFC]/60" id="chat-box">
                    @forelse ($order->messages as $msg)
                        @if ($msg->sender_type === 'customer')
                            <div class="flex items-start gap-3 max-w-[80%]" data-msg-id="{{ $msg->id }}">
                                <div class="size-8 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ strtoupper(substr($msg->user?->name ?? 'C', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs font-bold text-slate-800">{{ $msg->user?->name ?? 'Pelanggan' }}</span>
                                        <span class="text-[10px] text-slate-400 font-mono">{{ $msg->created_at->format('H:i') }}</span>
                                    </div>
                                    <div class="rounded-2xl rounded-tl-sm bg-white border border-slate-200 p-3 text-xs text-slate-700 leading-relaxed shadow-2xs">
                                        {{ $msg->message }}
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="flex items-start justify-end gap-3 ml-auto max-w-[80%]" data-msg-id="{{ $msg->id }}">
                                <div>
                                    <div class="flex items-center justify-end gap-2 mb-1">
                                        <span class="text-[10px] text-slate-400 font-mono">{{ $msg->created_at->format('H:i') }}</span>
                                        <span class="text-xs font-bold text-[#1B64F2]">Anda (Admin)</span>
                                    </div>
                                    <div class="rounded-2xl rounded-tr-sm bg-[#EFF6FF] border border-[#BFDBFE] p-3 text-xs text-slate-800 leading-relaxed shadow-2xs">
                                        {{ $msg->message }}
                                    </div>
                                    <div class="text-right mt-1">
                                        <span class="text-[10px] text-slate-400 font-mono">
                                            {{ $msg->read_at ? '✓✓ Dibaca pelanggan' : '✓ Terkirim' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="size-8 rounded-full bg-[#1B64F2] text-white flex items-center justify-center font-extrabold text-xs shrink-0">
                                    IT
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="p-8 text-center" id="chat-empty-state">
                            <p class="text-xs font-bold text-slate-700">Belum Ada Riwayat Chat</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Mulai percakapan atau tunggu pesan masuk dari aplikasi mobile.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Quick Replies & Form -->
                <div class="p-4 bg-white border-t border-slate-100 space-y-3">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="text-[10px] font-bold text-slate-400 uppercase mr-1">Balasan Cepat:</span>
                        <button type="button" onclick="setAdminMessage('Halo kak, pesanan hardware sedang kami persiapkan dan cek kelayakannya di gudang.')"
                                class="rounded-full border border-slate-200 bg-[#F8FAFC] px-3 py-1 text-[11px] font-medium text-slate-600 hover:text-[#1B64F2] hover:border-blue-300 transition cursor-pointer">
                            Sedang dipersiapkan
                        </button>
                        <button type="button" onclick="setAdminMessage('Halo kak, resi kurir pengiriman telah kami update. Silakan dicek di pelacakan pesanan ya.')"
                                class="rounded-full border border-slate-200 bg-[#F8FAFC] px-3 py-1 text-[11px] font-medium text-slate-600 hover:text-[#1B64F2] hover:border-blue-300 transition cursor-pointer">
                            Resi sudah update
                        </button>
                        <button type="button" onclick="setAdminMessage('Halo kak, teknisi kami siap hadir untuk pengerjaan sesuai jadwal yang disepakati.')"
                                class="rounded-full border border-slate-200 bg-[#F8FAFC] px-3 py-1 text-[11px] font-medium text-slate-600 hover:text-[#1B64F2] hover:border-blue-300 transition cursor-pointer">
                            Jadwal teknisi ready
                        </button>
                    </div>

                    <form id="admin-chat-form" method="POST" action="{{ route('admin.orders.messages.store', $order) }}" class="flex items-center gap-2">
                        @csrf
                        <input type="text" id="admin-chat-input" name="message" required placeholder="Tulis balasan pesan ke pelanggan..." autocomplete="off"
                               class="field-input flex-1 text-xs">
                        <button type="submit" id="admin-chat-btn" class="btn-primary rounded-xl px-5 py-2.5 text-xs font-bold shrink-0">
                            Kirim
                        </button>
                    </form>
                </div>
            </section>
        </div>

        <!-- Right Column: Customer Info & Timeline -->
        <div class="space-y-5">
            <!-- Customer Information Card -->
            <section class="card p-6 bg-white">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="size-11 rounded-full bg-[#EBF3FF] text-[#1B64F2] font-extrabold text-sm flex items-center justify-center shrink-0">
                        {{ strtoupper(substr($order->user->name, 0, 2)) }}
                    </div>
                    <div class="min-w-0">
                        <a href="{{ route('admin.customers.show', $order->user) }}" class="font-extrabold text-slate-900 text-sm hover:text-[#1B64F2] transition block truncate">
                            {{ $order->user->name }}
                        </a>
                        <p class="text-xs text-slate-400 truncate">{{ $order->user->email }}</p>
                    </div>
                </div>

                <dl class="mt-4 space-y-3.5 text-xs">
                    <div>
                        <dt class="text-slate-400 uppercase font-bold text-[10px] tracking-wider">Kontak Penerima</dt>
                        <dd class="mt-1 font-bold text-slate-800 flex items-center justify-between gap-2">
                            <span>{{ $order->recipient_name }}</span>
                            @php
                                $cleanPhone = preg_replace('/[^0-9]/', '', $order->recipient_phone);
                                if (str_starts_with($cleanPhone, '0')) {
                                    $cleanPhone = '62' . substr($cleanPhone, 1);
                                }
                            @endphp
                            <a href="https://wa.me/{{ $cleanPhone }}?text=Halo%20{{ urlencode($order->recipient_name) }},%20mengenai%20pesanan%20{{ $order->order_number }}"
                               target="_blank" rel="noopener"
                               class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-bold text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition">
                                <span>WhatsApp</span>
                            </a>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-slate-400 uppercase font-bold text-[10px] tracking-wider">Nomor HP</dt>
                        <dd class="mt-0.5 font-mono font-semibold text-slate-700">{{ $order->recipient_phone }}</dd>
                    </div>

                    <div>
                        <dt class="text-slate-400 uppercase font-bold text-[10px] tracking-wider">Metode Pembayaran</dt>
                        <dd class="mt-0.5 font-bold text-slate-800">
                            @if ($order->payment_method === 'credit_card')
                                Kartu Kredit (Otomatis Lunas)
                            @elseif ($order->payment_method === 'ewallet')
                                E-Wallet ({{ $order->payment_channel ?? 'QRIS' }})
                            @else
                                Transfer Bank ({{ $order->payment_channel ?? 'BCA' }})
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-slate-400 uppercase font-bold text-[10px] tracking-wider">{{ $isHardware ? 'Alamat Pengiriman Hardware' : ($isService ? 'Lokasi / Alamat Pemasangan' : 'Tujuan Lisensi / Akun Digital') }}</dt>
                        <dd class="mt-1 whitespace-pre-line text-slate-700 bg-[#F8FAFC] p-3 rounded-xl border border-slate-200/80 font-medium leading-relaxed">
                            {{ $order->shipping_address }}
                        </dd>
                    </div>

                    @if ($order->tracking_number)
                        <div>
                            <dt class="text-slate-400 uppercase font-bold text-[10px] tracking-wider">Nomor Resi Ekspedisi</dt>
                            <dd class="mt-1 font-mono font-bold bg-[#F8FAFC] p-3 rounded-xl border border-slate-200/80 flex items-center justify-between">
                                <span class="tracking-wider text-[#1B64F2]">{{ $order->tracking_number }}</span>
                                <button type="button" onclick="copyWithToast('{{ $order->tracking_number }}', 'Nomor resi {{ $order->tracking_number }} berhasil disalin!')"
                                        class="rounded-full bg-blue-50 border border-blue-200 text-[#1B64F2] hover:bg-blue-100 px-3 py-1 text-[11px] font-sans font-bold cursor-pointer transition">
                                    Salin
                                </button>
                            </dd>
                        </div>
                    @endif

                    @php
                        $cleanNotes = $order->notes ? trim(preg_replace('/^\[(CC|MB|EW)\][^|\n]*(\|\s*)?/', '', $order->notes)) : '';
                    @endphp
                    @if ($cleanNotes)
                        <div>
                            <dt class="text-slate-400 uppercase font-bold text-[10px] tracking-wider">Catatan Khusus Klien</dt>
                            <dd class="mt-1 whitespace-pre-line text-amber-800 bg-amber-50 p-3 rounded-xl border border-amber-200 font-medium text-[11px] leading-relaxed">
                                {{ $cleanNotes }}
                            </dd>
                        </div>
                    @endif
                </dl>
            </section>

            <!-- Order Timeline Activity -->
            <section class="card p-6 bg-white">
                <h2 class="font-extrabold text-slate-900 text-sm mb-4">Linimasa Aktivitas</h2>
                <div class="relative pl-5 border-l-2 border-blue-100 space-y-4 text-xs">
                    <div class="relative">
                        <div class="absolute -left-[25px] top-1 size-2.5 rounded-full bg-[#1B64F2] ring-4 ring-white"></div>
                        <p class="font-bold text-slate-900">Pesanan Dibuat</p>
                        <p class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $order->created_at->translatedFormat('d M Y, H:i') }} WIB</p>
                    </div>

                    @if ($order->paid_at)
                        <div class="relative">
                            <div class="absolute -left-[25px] top-1 size-2.5 rounded-full bg-[#1B64F2] ring-4 ring-white"></div>
                            <p class="font-bold text-slate-900">Pembayaran Diterima</p>
                            <p class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $order->paid_at->translatedFormat('d M Y, H:i') }} WIB</p>
                        </div>
                    @endif

                    @if ($order->status->value === 'processing')
                        <div class="relative">
                            <div class="absolute -left-[25px] top-1 size-2.5 rounded-full bg-purple-600 ring-4 ring-white animate-pulse"></div>
                            <p class="font-bold text-purple-700">{{ $isService ? 'Sedang Dikerjakan' : 'Sedang Diproses' }}</p>
                            <p class="text-[11px] text-purple-500 mt-0.5 font-mono">Sedang berlangsung saat ini</p>
                        </div>
                    @endif

                    @if ($order->completed_at)
                        <div class="relative">
                            <div class="absolute -left-[25px] top-1 size-2.5 rounded-full bg-emerald-500 ring-4 ring-white"></div>
                            <p class="font-bold text-emerald-700">Pesanan Selesai</p>
                            <p class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $order->completed_at->translatedFormat('d M Y, H:i') }} WIB</p>
                        </div>
                    @endif

                    @if ($order->cancelled_at)
                        <div class="relative">
                            <div class="absolute -left-[25px] top-1 size-2.5 rounded-full bg-rose-500 ring-4 ring-white"></div>
                            <p class="font-bold text-rose-600">Pesanan Dibatalkan</p>
                            <p class="text-[11px] text-rose-400 font-mono mt-0.5">{{ $order->cancelled_at->translatedFormat('d M Y, H:i') }} WIB</p>
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
    </div>

    <!-- Dedicated Single-Page Modern A4 Print Invoice (Only visible when printing) -->
    @php
        $hasPrintLogo = file_exists(public_path('images/logo.png'));
        $isPaidStamp = in_array($order->status->value, ['paid', 'processing', 'shipped', 'delivered', 'completed'], true);
        $cleanNotesPrint = $order->notes ? trim(preg_replace('/^\[(CC|MB|EW)\][^|\n]*(\|\s*)?/', '', $order->notes)) : '';
    @endphp
    <div class="hidden print:block bg-white text-slate-900" style="page-break-inside: avoid; break-inside: avoid;">
        <div class="rounded-2xl border border-slate-200 overflow-hidden">
            <!-- Top Royal Blue Accent Banner -->
            <div style="background: linear-gradient(135deg, #1B64F2 0%, #1554D1 100%);" class="bg-[#1B64F2] px-7 py-5 text-white flex items-center justify-between">
                <div class="flex items-center gap-3.5">
                    <div style="background-color: #FFFFFF !important; color: #1B64F2 !important;" class="size-11 rounded-xl bg-white text-[#1B64F2] font-extrabold text-sm flex items-center justify-center shadow-xs shrink-0 p-1">
                        @if ($hasPrintLogo)
                            <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="w-full h-full object-contain">
                        @else
                            DSC
                        @endif
                    </div>
                    <div>
                        <h1 style="color: #FFFFFF !important;" class="text-lg font-extrabold tracking-tight text-white leading-tight">{{ config('app.name') }}</h1>
                        <p style="color: #DBEAFE !important;" class="text-[11px] text-blue-100 font-medium">Enterprise IT Hardware, Software Licensing & Managed Services</p>
                    </div>
                </div>

                <div class="text-right">
                    <p style="color: #DBEAFE !important;" class="text-[10px] font-extrabold uppercase tracking-widest text-blue-100">Official Tax Invoice</p>
                    <p style="color: #FFFFFF !important;" class="text-xl font-black font-mono tracking-tight text-white mt-0.5">#{{ $order->order_number }}</p>
                </div>
            </div>

            <!-- Invoice Body -->
            <div class="p-7 space-y-5">
                <!-- Metadata & Status Strip -->
                <div class="flex items-center justify-between border-b border-slate-200 pb-4 text-xs">
                    <div class="flex items-center gap-6">
                        <div>
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Tanggal Pesanan</span>
                            <span class="font-bold text-slate-800">{{ $order->created_at->translatedFormat('d F Y, H:i') }} WIB</span>
                        </div>
                        <div>
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Kategori Layanan</span>
                            <span class="font-bold text-slate-800">{{ $orderTypeLabel }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Metode Pembayaran</span>
                            <span class="font-bold text-slate-800">{{ $order->payment_channel ?? ($order->payment_method === 'credit_card' ? 'Kartu Kredit' : 'Virtual Account') }}</span>
                        </div>
                    </div>

                    <div>
                        @if ($isPaidStamp)
                            <span class="inline-flex items-center gap-1.5 rounded-full border-2 border-emerald-600 bg-emerald-50 px-3.5 py-1 text-xs font-black uppercase tracking-wider text-emerald-700">
                                ✓ LUNAS ({{ strtoupper($order->status->label()) }})
                            </span>
                        @elseif ($order->status->value === 'cancelled')
                            <span class="inline-flex items-center gap-1.5 rounded-full border-2 border-rose-600 bg-rose-50 px-3.5 py-1 text-xs font-black uppercase tracking-wider text-rose-700">
                                DIBATALKAN
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-full border-2 border-amber-500 bg-amber-50 px-3.5 py-1 text-xs font-black uppercase tracking-wider text-amber-700">
                                MENUNGGU PEMBAYARAN
                            </span>
                        @endif
                    </div>
                </div>

                <!-- 2-Column Billing & Shipping Box -->
                <div class="grid grid-cols-2 gap-5 text-xs">
                    <div class="rounded-xl border border-slate-200 bg-[#F8FAFC] p-4">
                        <p class="text-[10px] font-extrabold uppercase tracking-wider text-[#1B64F2] mb-1.5">Ditagihkan Kepada (Klien)</p>
                        <p class="font-extrabold text-slate-900 text-sm">{{ $order->recipient_name }}</p>
                        <p class="text-slate-600 font-medium mt-0.5">Akun: {{ $order->user->name }} ({{ $order->user->email }})</p>
                        <p class="text-slate-600 font-mono mt-0.5">Telp: {{ $order->recipient_phone }}</p>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-[#F8FAFC] p-4">
                        <p class="text-[10px] font-extrabold uppercase tracking-wider text-[#1B64F2] mb-1.5">
                            {{ $isHardware ? 'Alamat Pengiriman Barang' : ($isService ? 'Lokasi Pengerjaan Jasa' : 'Detail Pengiriman & Resi') }}
                        </p>
                        <p class="text-slate-700 font-medium leading-snug line-clamp-2">{{ $order->shipping_address }}</p>
                        @if ($order->tracking_number)
                            <p class="mt-1.5 font-mono text-[11px] font-bold text-slate-900">
                                No. Resi: <span class="text-[#1B64F2]">{{ $order->tracking_number }}</span>
                            </p>
                        @endif
                        @if ($order->va_number)
                            <p class="mt-1 font-mono text-[11px] text-slate-500">
                                Ref / VA: {{ $order->va_number }}
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Itemized Products Table -->
                <div class="rounded-xl border border-slate-200 overflow-hidden">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-[#F1F5F9] border-b border-slate-200 text-[10px] font-extrabold uppercase tracking-wider text-slate-600">
                                <th class="py-2.5 px-4 w-10 text-center">No</th>
                                <th class="py-2.5 px-4">Deskripsi Produk / Jasa IT</th>
                                <th class="py-2.5 px-4 text-right">Harga Satuan</th>
                                <th class="py-2.5 px-3 text-center">Qty</th>
                                <th class="py-2.5 px-4 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/80">
                            @foreach ($order->items as $idx => $item)
                                <tr>
                                    <td class="py-2.5 px-4 text-center font-bold text-slate-400">{{ $idx + 1 }}</td>
                                    <td class="py-2.5 px-4">
                                        <p class="font-bold text-slate-900">{{ $item->product_name }}</p>
                                        <p class="text-[10px] text-slate-500 font-mono">
                                            SKU: {{ $item->product?->sku ?? '-' }}
                                            @if ($item->product?->type)
                                                · {{ $item->product->type->label() }}
                                            @endif
                                        </p>
                                    </td>
                                    <td class="py-2.5 px-4 text-right font-mono text-slate-700 whitespace-nowrap">@rupiah($item->price)</td>
                                    <td class="py-2.5 px-3 text-center font-bold text-slate-900">{{ $item->quantity }}</td>
                                    <td class="py-2.5 px-4 text-right font-mono font-bold text-slate-900 whitespace-nowrap">@rupiah($item->subtotal)</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Summary & Total Row -->
                <div class="flex items-start justify-between gap-6 pt-1">
                    <div class="max-w-xs text-[10px] text-slate-500 leading-relaxed">
                        <p class="font-bold text-slate-700 uppercase tracking-wider mb-1">Catatan & Ketentuan:</p>
                        @if ($cleanNotesPrint)
                            <p class="mb-1 text-slate-700 font-medium">"{{ $cleanNotesPrint }}"</p>
                        @endif
                        <p>Nota ini merupakan bukti transaksi resmi yang diterbitkan secara elektronik oleh sistem {{ config('app.name') }} dan sah tanpa tanda tangan basah.</p>
                    </div>

                    <div class="w-64 rounded-xl border border-blue-200 bg-[#EFF6FF] p-3.5 space-y-1.5 text-xs">
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Subtotal ({{ $order->items->sum('quantity') }} item)</span>
                            <span class="font-mono font-semibold">@rupiah($order->total)</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Biaya Layanan / Pajak</span>
                            <span class="font-mono font-semibold">Termasuk</span>
                        </div>
                        <div class="border-t border-blue-200 pt-1.5 flex items-center justify-between">
                            <span class="font-extrabold text-slate-900 uppercase text-[11px]">Total Tagihan</span>
                            <span class="font-mono text-base font-black text-[#1B64F2]">@rupiah($order->total)</span>
                        </div>
                    </div>
                </div>

                <!-- Bottom Signature & Timestamp Footer -->
                <div class="pt-4 border-t border-slate-200 flex items-end justify-between text-[11px] text-slate-500">
                    <div>
                        <p>Dicetak pada: <span class="font-semibold text-slate-700">{{ now()->translatedFormat('d F Y, H:i') }} WIB</span></p>
                        <p class="text-[10px] text-slate-400 mt-0.5">Terima kasih atas kepercayaan Anda menggunakan layanan {{ config('app.name') }}.</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] uppercase font-bold tracking-wider text-slate-400">Otorisasi Digital</p>
                        <p class="font-extrabold text-slate-900 mt-3">{{ config('app.name') }}</p>
                        <p class="text-[10px] text-[#1B64F2] font-bold">Verified Electronic Invoice</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function setAdminMessage(text) {
            const input = document.getElementById('admin-chat-input');
            if (input) {
                input.value = text;
                input.focus();
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const box = document.getElementById('chat-box');
            const form = document.getElementById('admin-chat-form');
            const input = document.getElementById('admin-chat-input');
            const countBadge = document.getElementById('chat-count-badge');
            const pollUrl = "{{ route('admin.chats.show', $order) }}";
            const storeUrl = "{{ route('admin.orders.messages.store', $order) }}";
            const csrfToken = "{{ csrf_token() }}";

            function scrollToBottom() {
                if (box) {
                    box.scrollTop = box.scrollHeight;
                }
            }
            scrollToBottom();

            function escapeHtml(str) {
                const div = document.createElement('div');
                div.textContent = str;
                return div.innerHTML;
            }

            function renderMessage(msg) {
                if (msg.sender_type === 'customer') {
                    const initial = (msg.sender_name || 'C').charAt(0).toUpperCase();
                    return `
                        <div class="flex items-start gap-3 max-w-[80%]" data-msg-id="${msg.id}">
                            <div class="size-8 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs shrink-0">
                                ${initial}
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs font-bold text-slate-800">${escapeHtml(msg.sender_name)}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">${msg.time}</span>
                                </div>
                                <div class="rounded-2xl rounded-tl-sm bg-white border border-slate-200 p-3 text-xs text-slate-700 leading-relaxed shadow-2xs">
                                    ${escapeHtml(msg.message)}
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    const statusText = msg.read_at ? '✓✓ Dibaca pelanggan' : '✓ Terkirim';
                    return `
                        <div class="flex items-start justify-end gap-3 ml-auto max-w-[80%]" data-msg-id="${msg.id}">
                            <div>
                                <div class="flex items-center justify-end gap-2 mb-1">
                                    <span class="text-[10px] text-slate-400 font-mono">${msg.time}</span>
                                    <span class="text-xs font-bold text-[#1B64F2]">Anda (Admin)</span>
                                </div>
                                <div class="rounded-2xl rounded-tr-sm bg-[#EFF6FF] border border-[#BFDBFE] p-3 text-xs text-slate-800 leading-relaxed shadow-2xs">
                                    ${escapeHtml(msg.message)}
                                </div>
                                <div class="text-right mt-1">
                                    <span class="text-[10px] text-slate-400 font-mono">
                                        ${statusText}
                                    </span>
                                </div>
                            </div>
                            <div class="size-8 rounded-full bg-[#1B64F2] text-white flex items-center justify-center font-extrabold text-xs shrink-0">
                                IT
                            </div>
                        </div>
                    `;
                }
            }

            if (form) {
                form.addEventListener('submit', async function (e) {
                    e.preventDefault();
                    const text = (input ? input.value : '').trim();
                    if (!text) return;

                    input.value = '';
                    input.focus();

                    const tempId = 'temp-' + Date.now();
                    const now = new Date();
                    const timeStr = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
                    const optMsg = {
                        id: tempId,
                        sender_type: 'admin',
                        sender_name: 'Anda (Admin)',
                        message: text,
                        time: timeStr,
                        read_at: false
                    };
                    const emptyState = document.getElementById('chat-empty-state');
                    if (emptyState) emptyState.remove();
                    box.insertAdjacentHTML('beforeend', renderMessage(optMsg));
                    scrollToBottom();

                    try {
                        const res = await fetch(storeUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({ message: text })
                        });
                        const data = await res.json();
                        if (data.success && data.message) {
                            const optEl = box.querySelector(`[data-msg-id="${tempId}"]`);
                            if (optEl) {
                                optEl.setAttribute('data-msg-id', data.message.id);
                            }
                            if (countBadge) {
                                const currentCount = box.querySelectorAll('[data-msg-id]').length;
                                countBadge.textContent = currentCount + ' Pesan';
                            }
                        }
                    } catch (err) {
                        console.error('Error sending message:', err);
                    }
                });
            }

            let isPolling = false;
            setInterval(async function () {
                if (isPolling) return;
                isPolling = true;
                try {
                    const res = await fetch(pollUrl, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    if (res.ok) {
                        const data = await res.json();
                        if (data.messages && Array.isArray(data.messages)) {
                            const existingIds = new Set(
                                Array.from(box.querySelectorAll('[data-msg-id]')).map(el => el.getAttribute('data-msg-id'))
                            );
                            let hasNew = false;
                            for (const msg of data.messages) {
                                if (!existingIds.has(String(msg.id))) {
                                    const emptyState = document.getElementById('chat-empty-state');
                                    if (emptyState) emptyState.remove();
                                    box.insertAdjacentHTML('beforeend', renderMessage(msg));
                                    hasNew = true;
                                }
                            }
                            if (hasNew) {
                                scrollToBottom();
                                if (countBadge) {
                                    countBadge.textContent = data.messages.length + ' Pesan';
                                }
                            }
                        }
                    }
                } catch (e) {
                } finally {
                    isPolling = false;
                }
            }, 1500);
        });
    </script>
</x-layouts.admin>
