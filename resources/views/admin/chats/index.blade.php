<x-layouts.admin title="Chat & Pesan Pelanggan">
    <div class="space-y-5">
        <!-- Top Filter Tabs & Live Status Row -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <!-- Navigation Pill Tabs -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.chats.index', ['tab' => 'all']) }}"
                   class="whitespace-nowrap rounded-full px-4 py-2 text-xs font-bold transition border flex items-center gap-2 {{ $activeTab === 'all' ? 'bg-[#1B64F2] text-white border-[#1B64F2] shadow-sm shadow-blue-600/20' : 'bg-white text-slate-600 border-slate-200/90 hover:border-blue-300 hover:text-[#1B64F2]' }}">
                    <span>Semua Percakapan</span>
                </a>
                <a href="{{ route('admin.chats.index', ['tab' => 'products']) }}"
                   class="whitespace-nowrap rounded-full px-4 py-2 text-xs font-bold transition border flex items-center gap-2 {{ $activeTab === 'products' ? 'bg-[#1B64F2] text-white border-[#1B64F2] shadow-sm shadow-blue-600/20' : 'bg-white text-slate-600 border-slate-200/90 hover:border-blue-300 hover:text-[#1B64F2]' }}">
                    <span>Tanya Produk (Katalog)</span>
                    <span id="tab-badge-products-wrapper">
                        @if ($totalUnreadProducts > 0)
                            <span id="tab-badge-products" class="rounded-full bg-rose-500 text-white font-extrabold text-[10px] px-1.5 py-0.5">
                                {{ $totalUnreadProducts }}
                            </span>
                        @endif
                    </span>
                </a>
                <a href="{{ route('admin.chats.index', ['tab' => 'orders']) }}"
                   class="whitespace-nowrap rounded-full px-4 py-2 text-xs font-bold transition border flex items-center gap-2 {{ $activeTab === 'orders' ? 'bg-[#1B64F2] text-white border-[#1B64F2] shadow-sm shadow-blue-600/20' : 'bg-white text-slate-600 border-slate-200/90 hover:border-blue-300 hover:text-[#1B64F2]' }}">
                    <span>Diskusi Pesanan</span>
                    <span id="tab-badge-orders-wrapper">
                        @if ($totalUnreadOrders > 0)
                            <span id="tab-badge-orders" class="rounded-full bg-rose-500 text-white font-extrabold text-[10px] px-1.5 py-0.5">
                                {{ $totalUnreadOrders }}
                            </span>
                        @endif
                    </span>
                </a>
            </div>

            <!-- Right Live Status & Unread Indicator -->
            <div class="flex items-center gap-2.5">
                <div id="unread-header-badge-container">
                    @if ($totalUnreadProducts + $totalUnreadOrders > 0)
                        <span id="unread-header-badge" class="rounded-full bg-rose-50 text-rose-600 border border-rose-200 px-3 py-1.5 text-xs font-extrabold">
                            {{ $totalUnreadProducts + $totalUnreadOrders }} Pesan Belum Dibaca
                        </span>
                    @endif
                </div>
                <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 border border-emerald-200 px-3.5 py-1.5">
                    <span class="relative flex size-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full size-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-xs font-bold text-emerald-700">Live Realtime</span>
                </div>
            </div>
        </div>

        <!-- Section 1: Tanya Produk (Katalog) -->
        @if ($activeTab === 'all' || $activeTab === 'products')
            <section class="card overflow-hidden bg-white">
                <div class="p-4 sm:px-6 border-b border-slate-100 bg-[#F8FAFC] flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-3">
                        <div class="size-9 rounded-xl bg-[#EBF3FF] text-[#1B64F2] flex items-center justify-center shrink-0">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-extrabold text-sm text-slate-900">Konsultasi Katalog Produk</h2>
                            <p class="text-[11px] text-slate-500">Pertanyaan calon pembeli mengenai spesifikasi produk & layanan</p>
                        </div>
                    </div>
                    <span id="product-inquiries-count" class="rounded-full bg-blue-50 border border-blue-200 px-3 py-1 text-xs font-bold text-[#1B64F2]">
                        {{ $productInquiries->total() }} Calon Pembeli
                    </span>
                </div>

                <div id="product-inquiries-container">
                    @if ($productInquiries->isEmpty())
                        <div class="p-10 text-center">
                            <p class="text-xs font-semibold text-slate-400">Belum ada calon pembeli yang menanyakan produk katalog saat ini.</p>
                        </div>
                    @else
                        <div class="divide-y divide-slate-100">
                            @foreach ($productInquiries as $inquiry)
                                @php
                                    $latestMsg = $inquiry->messages->first();
                                    $hasUnread = $inquiry->unread_messages_count > 0;
                                @endphp
                                <div class="p-4 sm:px-6 hover:bg-[#F8FBFF] transition flex flex-col sm:flex-row sm:items-center justify-between gap-4 {{ $hasUnread ? 'bg-blue-50/40' : '' }}">
                                    <div class="flex items-start gap-3.5 min-w-0">
                                        <div class="size-10 rounded-full shrink-0 flex items-center justify-center font-extrabold text-xs {{ $hasUnread ? 'bg-[#1B64F2] text-white shadow-sm shadow-blue-500/20' : 'bg-[#EBF3FF] text-[#1B64F2]' }}">
                                            {{ strtoupper(substr($inquiry->user->name, 0, 1)) }}
                                        </div>
                                        <div class="space-y-1 min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="font-extrabold text-slate-900 text-xs sm:text-sm">{{ $inquiry->user->name }}</span>
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 border border-blue-200 px-2.5 py-0.5 text-[11px] font-bold text-[#1B64F2]">
                                                    <span>Produk: {{ $inquiry->product->name }}</span>
                                                    <span class="font-mono text-[10px] text-slate-500">({{ $inquiry->product->sku }})</span>
                                                </span>
                                                @if ($hasUnread)
                                                    <span class="rounded-full bg-rose-500 text-white font-extrabold text-[10px] px-2 py-0.5">
                                                        {{ $inquiry->unread_messages_count }} Baru
                                                    </span>
                                                @endif
                                            </div>
                                            @if ($latestMsg)
                                                <p class="text-xs text-slate-600 line-clamp-1 {{ $hasUnread ? 'font-bold text-slate-900' : '' }}">
                                                    <span class="text-slate-400 font-semibold">{{ $latestMsg->sender_type === 'admin' ? 'Anda: ' : 'Pelanggan: ' }}</span>
                                                    {{ $latestMsg->message }}
                                                </p>
                                            @endif
                                            <p class="text-[11px] text-slate-400 font-mono">
                                                {{ $latestMsg ? $latestMsg->created_at->diffForHumans() : $inquiry->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                                        <a href="{{ route('admin.chats.product.show', $inquiry) }}"
                                           class="inline-flex items-center justify-center whitespace-nowrap rounded-full border border-[#60A5FA] bg-white px-4 py-1.5 text-xs font-bold text-[#1B64F2] hover:bg-[#1B64F2] hover:text-white transition">
                                            Buka Percakapan
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        @endif

        <!-- Section 2: Diskusi Pesanan -->
        @if ($activeTab === 'all' || $activeTab === 'orders')
            <section class="card overflow-hidden bg-white">
                <div class="p-4 sm:px-6 border-b border-slate-100 bg-[#F8FAFC] flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-3">
                        <div class="size-9 rounded-xl bg-[#EBF3FF] text-[#1B64F2] flex items-center justify-center shrink-0">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-extrabold text-sm text-slate-900">Chat Diskusi Pesanan Aktif</h2>
                            <p class="text-[11px] text-slate-500">Komunikasi pengiriman, resi, & jadwal teknisi untuk pesanan yang sedang berjalan</p>
                        </div>
                    </div>
                    <span id="orders-count" class="rounded-full bg-blue-50 border border-blue-200 px-3 py-1 text-xs font-bold text-[#1B64F2]">
                        {{ $ordersWithChats->total() }} Pesanan
                    </span>
                </div>

                <div id="orders-container">
                    @if ($ordersWithChats->isEmpty())
                        <div class="p-10 text-center">
                            <p class="text-xs font-semibold text-slate-400">Belum ada percakapan terkait nomor pesanan saat ini.</p>
                        </div>
                    @else
                        <div class="divide-y divide-slate-100">
                            @foreach ($ordersWithChats as $order)
                                @php
                                    $latestMsg = $order->messages->first();
                                    $hasUnread = $order->unread_messages_count > 0;
                                @endphp
                                <div class="p-4 sm:px-6 hover:bg-[#F8FBFF] transition flex flex-col sm:flex-row sm:items-center justify-between gap-4 {{ $hasUnread ? 'bg-blue-50/40' : '' }}">
                                    <div class="flex items-start gap-3.5 min-w-0">
                                        <div class="size-10 rounded-full shrink-0 flex items-center justify-center font-extrabold text-xs {{ $hasUnread ? 'bg-[#1B64F2] text-white shadow-sm shadow-blue-500/20' : 'bg-[#EBF3FF] text-[#1B64F2]' }}">
                                            {{ strtoupper(substr($order->user->name, 0, 1)) }}
                                        </div>
                                        <div class="space-y-1 min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="font-extrabold text-slate-900 text-xs sm:text-sm">{{ $order->user->name }}</span>
                                                <a href="{{ route('admin.orders.show', $order) }}" class="font-mono text-xs font-extrabold text-[#1B64F2] hover:underline">
                                                    #{{ $order->order_number }}
                                                </a>
                                                <x-order-status :status="$order->status" />
                                                @if ($hasUnread)
                                                    <span class="rounded-full bg-rose-500 text-white font-extrabold text-[10px] px-2 py-0.5">
                                                        {{ $order->unread_messages_count }} Baru
                                                    </span>
                                                @endif
                                            </div>
                                            @if ($latestMsg)
                                                <p class="text-xs text-slate-600 line-clamp-1 {{ $hasUnread ? 'font-bold text-slate-900' : '' }}">
                                                    <span class="text-slate-400 font-semibold">{{ $latestMsg->sender_type === 'admin' ? 'Anda: ' : 'Pelanggan: ' }}</span>
                                                    {{ $latestMsg->message }}
                                                </p>
                                            @endif
                                            <p class="text-[11px] text-slate-400 font-mono">
                                                {{ $latestMsg ? $latestMsg->created_at->diffForHumans() : $order->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                                        <a href="{{ route('admin.orders.show', $order) }}#chat-section"
                                           class="inline-flex items-center justify-center whitespace-nowrap rounded-full border border-[#60A5FA] bg-white px-4 py-1.5 text-xs font-bold text-[#1B64F2] hover:bg-[#1B64F2] hover:text-white transition">
                                            Buka Percakapan
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        @endif
    </div>

    <!-- Real-time Inbox Polling & Audio Chime Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const activeTab = "{{ $activeTab }}";
            let lastTotalUnread = {{ $totalUnreadProducts + $totalUnreadOrders }};
            let isPolling = false;

            function playChime() {
                try {
                    const AudioContextClass = window.AudioContext || window.webkitAudioContext;
                    if (!AudioContextClass) return;
                    const ctx = new AudioContextClass();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(587.33, ctx.currentTime);
                    osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.15);
                    gain.gain.setValueAtTime(0.2, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.35);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.35);
                } catch (_) {}
            }

            function escapeHtml(str) {
                if (!str) return '';
                const d = document.createElement('div');
                d.textContent = str;
                return d.innerHTML;
            }

            async function pollInbox() {
                if (isPolling) return;
                isPolling = true;

                try {
                    const url = new URL("{{ route('admin.chats.index') }}", window.location.origin);
                    url.searchParams.set('tab', activeTab);

                    const res = await fetch(url.toString(), {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!res.ok) return;
                    const data = await res.json();

                    if (data.total_unread > lastTotalUnread) {
                        playChime();
                    }
                    lastTotalUnread = data.total_unread;

                    const headerContainer = document.getElementById('unread-header-badge-container');
                    if (headerContainer) {
                        if (data.total_unread > 0) {
                            headerContainer.innerHTML = `
                                <span id="unread-header-badge" class="rounded-full bg-rose-50 text-rose-600 border border-rose-200 px-3 py-1.5 text-xs font-extrabold">
                                    ${data.total_unread} Pesan Belum Dibaca
                                </span>
                            `;
                        } else {
                            headerContainer.innerHTML = '';
                        }
                    }

                    document.querySelectorAll('.sidebar-badge-wrapper[data-badge-route="admin.chats.index"]').forEach(el => {
                        if (data.total_unread > 0) {
                            el.innerHTML = `
                                <span class="rounded-full bg-[#1B64F2] text-white px-2 py-0.5 text-[10px] font-extrabold">
                                    ${data.total_unread}
                                </span>
                            `;
                        } else {
                            el.innerHTML = '';
                        }
                    });

                    const tabProductsWrapper = document.getElementById('tab-badge-products-wrapper');
                    if (tabProductsWrapper) {
                        tabProductsWrapper.innerHTML = data.total_unread_products > 0
                            ? `<span id="tab-badge-products" class="rounded-full bg-rose-500 text-white font-extrabold text-[10px] px-1.5 py-0.5">${data.total_unread_products}</span>`
                            : '';
                    }

                    const tabOrdersWrapper = document.getElementById('tab-badge-orders-wrapper');
                    if (tabOrdersWrapper) {
                        tabOrdersWrapper.innerHTML = data.total_unread_orders > 0
                            ? `<span id="tab-badge-orders" class="rounded-full bg-rose-500 text-white font-extrabold text-[10px] px-1.5 py-0.5">${data.total_unread_orders}</span>`
                            : '';
                    }

                    const inqCountEl = document.getElementById('product-inquiries-count');
                    if (inqCountEl) {
                        inqCountEl.textContent = `${data.product_inquiries_total} Calon Pembeli`;
                    }

                    const inqContainer = document.getElementById('product-inquiries-container');
                    if (inqContainer && data.product_inquiries) {
                        if (data.product_inquiries.length === 0) {
                            inqContainer.innerHTML = `
                                <div class="p-10 text-center">
                                    <p class="text-xs font-semibold text-slate-400">Belum ada calon pembeli yang menanyakan produk katalog saat ini.</p>
                                </div>
                            `;
                        } else {
                            inqContainer.innerHTML = `
                                <div class="divide-y divide-slate-100">
                                    ${data.product_inquiries.map(inq => {
                                        const hasUnread = inq.unread_count > 0;
                                        return `
                                            <div class="p-4 sm:px-6 hover:bg-[#F8FBFF] transition flex flex-col sm:flex-row sm:items-center justify-between gap-4 ${hasUnread ? 'bg-blue-50/40' : ''}">
                                                <div class="flex items-start gap-3.5 min-w-0">
                                                    <div class="size-10 rounded-full shrink-0 flex items-center justify-center font-extrabold text-xs ${hasUnread ? 'bg-[#1B64F2] text-white shadow-sm shadow-blue-500/20' : 'bg-[#EBF3FF] text-[#1B64F2]'}">
                                                        ${escapeHtml(inq.user_initial)}
                                                    </div>
                                                    <div class="space-y-1 min-w-0">
                                                        <div class="flex flex-wrap items-center gap-2">
                                                            <span class="font-extrabold text-slate-900 text-xs sm:text-sm">${escapeHtml(inq.user_name)}</span>
                                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 border border-blue-200 px-2.5 py-0.5 text-[11px] font-bold text-[#1B64F2]">
                                                                <span>Produk: ${escapeHtml(inq.product_name)}</span>
                                                                <span class="font-mono text-[10px] text-slate-500">(${escapeHtml(inq.product_sku)})</span>
                                                            </span>
                                                            ${hasUnread ? `
                                                                <span class="rounded-full bg-rose-500 text-white font-extrabold text-[10px] px-2 py-0.5">
                                                                    ${inq.unread_count} Baru
                                                                </span>
                                                            ` : ''}
                                                        </div>
                                                        ${inq.latest_message ? `
                                                            <p class="text-xs text-slate-600 line-clamp-1 ${hasUnread ? 'font-bold text-slate-900' : ''}">
                                                                <span class="text-slate-400 font-semibold">${inq.latest_sender_type === 'admin' ? 'Anda: ' : 'Pelanggan: '}</span>
                                                                ${escapeHtml(inq.latest_message)}
                                                            </p>
                                                        ` : ''}
                                                        <p class="text-[11px] text-slate-400 font-mono">
                                                            ${escapeHtml(inq.time_human)}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                                                    <a href="${inq.chat_url}" class="inline-flex items-center justify-center whitespace-nowrap rounded-full border border-[#60A5FA] bg-white px-4 py-1.5 text-xs font-bold text-[#1B64F2] hover:bg-[#1B64F2] hover:text-white transition">
                                                        Buka Percakapan
                                                    </a>
                                                </div>
                                            </div>
                                        `;
                                    }).join('')}
                                </div>
                            `;
                        }
                    }

                    const ordersCountEl = document.getElementById('orders-count');
                    if (ordersCountEl) {
                        ordersCountEl.textContent = `${data.orders_total} Pesanan`;
                    }

                    const ordersContainer = document.getElementById('orders-container');
                    if (ordersContainer && data.orders) {
                        if (data.orders.length === 0) {
                            ordersContainer.innerHTML = `
                                <div class="p-10 text-center">
                                    <p class="text-xs font-semibold text-slate-400">Belum ada percakapan terkait nomor pesanan saat ini.</p>
                                </div>
                            `;
                        } else {
                            ordersContainer.innerHTML = `
                                <div class="divide-y divide-slate-100">
                                    ${data.orders.map(order => {
                                        const hasUnread = order.unread_count > 0;
                                        return `
                                            <div class="p-4 sm:px-6 hover:bg-[#F8FBFF] transition flex flex-col sm:flex-row sm:items-center justify-between gap-4 ${hasUnread ? 'bg-blue-50/40' : ''}">
                                                <div class="flex items-start gap-3.5 min-w-0">
                                                    <div class="size-10 rounded-full shrink-0 flex items-center justify-center font-extrabold text-xs ${hasUnread ? 'bg-[#1B64F2] text-white shadow-sm shadow-blue-500/20' : 'bg-[#EBF3FF] text-[#1B64F2]'}">
                                                        ${escapeHtml(order.user_initial)}
                                                    </div>
                                                    <div class="space-y-1 min-w-0">
                                                        <div class="flex flex-wrap items-center gap-2">
                                                            <span class="font-extrabold text-slate-900 text-xs sm:text-sm">${escapeHtml(order.user_name)}</span>
                                                            <span class="font-mono text-xs font-extrabold text-[#1B64F2]">
                                                                #${escapeHtml(order.order_number)}
                                                            </span>
                                                            <span class="rounded-full bg-slate-100 border border-slate-200 px-2.5 py-0.5 text-[10px] font-bold text-slate-600">
                                                                ${escapeHtml(order.status)}
                                                            </span>
                                                            ${hasUnread ? `
                                                                <span class="rounded-full bg-rose-500 text-white font-extrabold text-[10px] px-2 py-0.5">
                                                                    ${order.unread_count} Baru
                                                                </span>
                                                            ` : ''}
                                                        </div>
                                                        ${order.latest_message ? `
                                                            <p class="text-xs text-slate-600 line-clamp-1 ${hasUnread ? 'font-bold text-slate-900' : ''}">
                                                                <span class="text-slate-400 font-semibold">${order.latest_sender_type === 'admin' ? 'Anda: ' : 'Pelanggan: '}</span>
                                                                ${escapeHtml(order.latest_message)}
                                                            </p>
                                                        ` : ''}
                                                        <p class="text-[11px] text-slate-400 font-mono">
                                                            ${escapeHtml(order.time_human)}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                                                    <a href="${order.chat_url}" class="inline-flex items-center justify-center whitespace-nowrap rounded-full border border-[#60A5FA] bg-white px-4 py-1.5 text-xs font-bold text-[#1B64F2] hover:bg-[#1B64F2] hover:text-white transition">
                                                        Buka Percakapan
                                                    </a>
                                                </div>
                                            </div>
                                        `;
                                    }).join('')}
                                </div>
                            `;
                        }
                    }
                } catch (err) {
                } finally {
                    isPolling = false;
                }
            }

            setInterval(pollInbox, 2500);
        });
    </script>
</x-layouts.admin>
