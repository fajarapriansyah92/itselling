<x-layouts.admin :title="'Chat Pesanan #' . $order->order_number">
    <div class="space-y-5">
        <!-- Top Navigation -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('admin.chats.index') }}"
               class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-[#1B64F2] transition">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Kotak Masuk Chat</span>
            </a>
            <a href="{{ route('admin.orders.show', $order) }}"
               class="inline-flex items-center justify-center rounded-full border border-[#60A5FA] bg-white px-4 py-1.5 text-xs font-bold text-[#1B64F2] hover:bg-[#1B64F2] hover:text-white transition">
                Lihat Detail Pesanan
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <!-- Left 2 Cols: Chat Window -->
            <div class="lg:col-span-2 card overflow-hidden bg-white flex flex-col h-[640px]">
                <!-- Chat Window Header -->
                <div class="p-4 sm:px-6 border-b border-slate-100 bg-[#F8FAFC] flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-full bg-[#1B64F2] text-white flex items-center justify-center font-extrabold text-sm shrink-0">
                            {{ strtoupper(substr($order->user->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-extrabold text-sm text-slate-900">{{ $order->user->name }}</h3>
                                <span class="size-2 rounded-full bg-emerald-500" title="Online"></span>
                            </div>
                            <p class="text-xs text-slate-500">Pesanan <span class="font-mono font-bold text-[#1B64F2]">#{{ $order->order_number }}</span> &bull; {{ $order->user->email }}</p>
                        </div>
                    </div>
                    <x-order-status :status="$order->status" />
                </div>

                <!-- Messages Container -->
                <div class="flex-1 p-6 overflow-y-auto space-y-4 bg-[#F8FAFC]/60" id="chat-messages">
                    @forelse ($order->messages as $msg)
                        @if ($msg->sender_type === 'customer')
                            <div class="flex items-start gap-3 max-w-[85%]" data-msg-id="{{ $msg->id }}">
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
                            <div class="flex items-start justify-end gap-3 ml-auto max-w-[85%]" data-msg-id="{{ $msg->id }}">
                                <div>
                                    <div class="flex items-center justify-end gap-2 mb-1">
                                        <span class="text-[10px] text-slate-400 font-mono">{{ $msg->created_at->format('H:i') }}</span>
                                        <span class="text-xs font-bold text-[#1B64F2]">Anda (Admin)</span>
                                    </div>
                                    <div class="rounded-2xl rounded-tr-sm bg-[#EFF6FF] border border-[#BFDBFE] p-3 text-xs text-slate-900 leading-relaxed shadow-2xs">
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
                        <div class="py-20 text-center" id="chat-empty-state">
                            <p class="text-sm font-bold text-slate-700">Belum Ada Pesan Masuk</p>
                            <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Kirim pesan pertama Anda kepada pelanggan menggunakan formulir di bawah ini.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Input Footer -->
                <div class="p-4 bg-white border-t border-slate-100 space-y-3">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="text-[10px] font-bold text-slate-400 uppercase mr-1">Template Balasan:</span>
                        <button type="button" onclick="setQuickMessage('Halo kak, pesanan hardware sedang kami persiapkan dan cek kelayakannya di gudang.')"
                                class="rounded-full border border-slate-200 bg-[#F8FAFC] px-3 py-1 text-[11px] font-medium text-slate-600 hover:text-[#1B64F2] hover:border-blue-300 transition cursor-pointer">
                            Sedang dipersiapkan
                        </button>
                        <button type="button" onclick="setQuickMessage('Halo kak, resi kurir pengiriman telah kami update. Silakan dicek di pelacakan pesanan ya.')"
                                class="rounded-full border border-slate-200 bg-[#F8FAFC] px-3 py-1 text-[11px] font-medium text-slate-600 hover:text-[#1B64F2] hover:border-blue-300 transition cursor-pointer">
                            Resi sudah update
                        </button>
                        <button type="button" onclick="setQuickMessage('Halo kak, teknisi kami siap hadir untuk pengerjaan sesuai jadwal yang disepakati.')"
                                class="rounded-full border border-slate-200 bg-[#F8FAFC] px-3 py-1 text-[11px] font-medium text-slate-600 hover:text-[#1B64F2] hover:border-blue-300 transition cursor-pointer">
                            Jadwal teknisi ready
                        </button>
                    </div>

                    <form id="admin-chat-form" method="POST" action="{{ route('admin.orders.messages.store', $order) }}" class="flex items-center gap-2">
                        @csrf
                        <input type="text" id="admin-chat-text" name="message" required placeholder="Tulis balasan pesan ke pelanggan..." autocomplete="off"
                               class="field-input flex-1 text-xs">
                        <button type="submit" id="admin-chat-btn" class="btn-primary rounded-full px-5 py-2.5 text-xs font-bold shrink-0">
                            Kirim
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right 1 Col: Order Summary Panel -->
            <div class="space-y-4">
                <div class="card p-5 bg-white">
                    <h4 class="font-extrabold text-sm text-slate-900 mb-3">Ringkasan Pesanan</h4>
                    <dl class="space-y-3 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Nomor Pesanan</span>
                            <span class="font-mono font-bold text-[#1B64F2]">#{{ $order->order_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Total Transaksi</span>
                            <span class="font-extrabold text-slate-900 font-mono">@rupiah($order->total)</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Penerima</span>
                            <span class="font-bold text-slate-800">{{ $order->recipient_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Telepon</span>
                            <span class="font-mono text-slate-700">{{ $order->recipient_phone }}</span>
                        </div>
                        @if ($order->tracking_number)
                            <div class="flex justify-between">
                                <span class="text-slate-500">Nomor Resi</span>
                                <span class="font-mono font-bold text-[#1B64F2]">{{ $order->tracking_number }}</span>
                            </div>
                        @endif
                    </dl>

                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-2">Item Pesanan ({{ $order->items->count() }})</p>
                        <div class="space-y-2">
                            @foreach ($order->items as $item)
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-slate-800 font-semibold truncate max-w-[170px]">{{ $item->product_name }}</span>
                                    <span class="text-slate-400 font-mono">x{{ $item->quantity }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-5">
                        <a href="{{ route('admin.orders.show', $order) }}"
                           class="w-full text-center block rounded-full border border-[#60A5FA] bg-white py-2 text-xs font-bold text-[#1B64F2] hover:bg-[#1B64F2] hover:text-white transition">
                            Buka Detail Lengkap Pesanan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function setQuickMessage(text) {
            const input = document.getElementById('admin-chat-text');
            if (input) {
                input.value = text;
                input.focus();
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const box = document.getElementById('chat-messages');
            const form = document.getElementById('admin-chat-form');
            const input = document.getElementById('admin-chat-text');
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
                        <div class="flex items-start gap-3 max-w-[85%]" data-msg-id="${msg.id}">
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
                        <div class="flex items-start justify-end gap-3 ml-auto max-w-[85%]" data-msg-id="${msg.id}">
                            <div>
                                <div class="flex items-center justify-end gap-2 mb-1">
                                    <span class="text-[10px] text-slate-400 font-mono">${msg.time}</span>
                                    <span class="text-xs font-bold text-[#1B64F2]">Anda (Admin)</span>
                                </div>
                                <div class="rounded-2xl rounded-tr-sm bg-[#EFF6FF] border border-[#BFDBFE] p-3 text-xs text-slate-900 leading-relaxed shadow-2xs">
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
