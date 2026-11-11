<x-layouts.admin title="Chat Konsultasi Produk · {{ $inquiry->product->name }}">
    <div class="space-y-5">
        <!-- Top Navigation & Actions -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('admin.chats.index', ['tab' => 'products']) }}"
               class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-[#1B64F2] transition">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Kembali ke Daftar Chat</span>
            </a>

            <a href="{{ route('admin.products.edit', $inquiry->product) }}"
               target="_blank"
               class="inline-flex items-center gap-2 rounded-full border border-[#60A5FA] bg-white px-4 py-1.5 text-xs font-bold text-[#1B64F2] hover:bg-[#1B64F2] hover:text-white transition">
                <span>Buka Edit Produk</span>
            </a>
        </div>

        <!-- Auto-detected Product Preview Card -->
        <div class="card p-5 bg-white border-l-4 border-l-[#1B64F2]">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="size-16 rounded-xl bg-[#F8FAFC] border border-slate-200 shrink-0 overflow-hidden flex items-center justify-center p-1.5">
                        @if ($inquiry->product->image_url)
                            <img src="{{ $inquiry->product->image_url }}" alt="{{ $inquiry->product->name }}" class="size-full object-cover rounded-lg">
                        @else
                            <div class="size-full flex items-center justify-center text-[#1B64F2] bg-blue-50 rounded-lg">
                                <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                        @endif
                    </div>
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-blue-50 border border-blue-200 text-[#1B64F2] px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider">
                                Sedang Ditanyakan
                            </span>
                            <span class="font-mono text-xs text-slate-500">{{ $inquiry->product->sku }}</span>
                            <span class="text-xs text-slate-300">&bull;</span>
                            <span class="text-xs font-medium text-slate-500">{{ $inquiry->product->category?->name ?? 'Katalog' }}</span>
                        </div>
                        <h3 class="text-base font-extrabold text-slate-900">{{ $inquiry->product->name }}</h3>
                        <p class="text-xs text-[#1B64F2] font-extrabold font-mono">
                            Rp {{ number_format($inquiry->product->price, 0, ',', '.') }}
                            <span class="text-[11px] font-normal text-slate-400">/ {{ $inquiry->product->unit }}</span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 border-t md:border-t-0 md:border-l border-slate-100 pt-3 md:pt-0 md:pl-6 shrink-0">
                    <div class="text-left md:text-right">
                        <p class="text-[11px] font-semibold text-slate-400">Ketersediaan Unit</p>
                        <p class="text-xs font-extrabold mt-0.5">
                            @if ($inquiry->product->stock === null)
                                <span class="text-emerald-600">Jasa Siap Dijadwalkan</span>
                            @elseif ($inquiry->product->stock > 0)
                                <span class="text-emerald-600">Stok: {{ $inquiry->product->stock }} {{ $inquiry->product->unit }}</span>
                            @else
                                <span class="text-rose-600">Stok Habis</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Conversation Card -->
        <div class="card overflow-hidden bg-white flex flex-col">
            <!-- Chat Header -->
            <div class="p-4 sm:px-6 border-b border-slate-100 bg-[#F8FAFC] flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="size-10 rounded-full bg-[#1B64F2] text-white font-extrabold text-sm flex items-center justify-center shrink-0">
                        {{ strtoupper(substr($inquiry->user->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-xs sm:text-sm font-extrabold text-slate-900 leading-tight">{{ $inquiry->user->name }}</p>
                        <p class="text-[11px] text-slate-500">{{ $inquiry->user->email }} · {{ $inquiry->user->phone ?? 'Pelanggan Terdaftar' }}</p>
                    </div>
                </div>
                <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1">
                    <span class="inline-flex size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-[11px] font-bold text-emerald-700">Live Realtime</span>
                </div>
            </div>

            <!-- Messages Stream -->
            <div id="messages-container" class="p-6 space-y-4 max-h-[460px] min-h-[300px] overflow-y-auto bg-[#F8FAFC]/60">
                @forelse ($inquiry->messages as $msg)
                    @php $isAdmin = $msg->sender_type === 'admin'; @endphp
                    <div class="flex flex-col {{ $isAdmin ? 'items-end' : 'items-start' }}">
                        <div class="flex items-center gap-1.5 mb-1 px-1">
                            <span class="text-[11px] font-bold {{ $isAdmin ? 'text-[#1B64F2]' : 'text-slate-700' }}">
                                {{ $isAdmin ? 'Anda (Admin DSC)' : $inquiry->user->name }}
                            </span>
                            <span class="text-[10px] text-slate-400 font-mono">&bull; {{ $msg->created_at->format('H:i') }}</span>
                        </div>
                        <div class="max-w-lg rounded-2xl p-3.5 px-4 text-xs leading-relaxed shadow-2xs {{ $isAdmin ? 'bg-[#EFF6FF] border border-[#BFDBFE] text-slate-900 font-medium rounded-tr-sm' : 'bg-white text-slate-700 rounded-tl-sm border border-slate-200' }}">
                            {!! nl2br(e($msg->message)) !!}
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-slate-400">
                        <p class="text-xs font-semibold">Belum ada pesan dalam konsultasi ini.</p>
                    </div>
                @endforelse
            </div>

            <!-- Chat Reply Form -->
            <form id="reply-form" method="POST" action="{{ route('admin.chats.product.reply', $inquiry) }}" class="p-4 bg-white border-t border-slate-100">
                @csrf
                <div class="rounded-2xl border border-slate-200 bg-[#F8FAFC] p-3 focus-within:border-[#1B64F2] focus-within:bg-white transition">
                    <textarea
                        id="message-input"
                        name="message"
                        rows="2"
                        required
                        placeholder="Tulis balasan pesan di sini..."
                        class="w-full bg-transparent p-1 text-xs text-slate-800 placeholder-slate-400 focus:outline-none resize-none leading-relaxed"
                    ></textarea>
                    <div class="flex items-center justify-between pt-2.5 px-0.5 border-t border-slate-200/70 mt-1">
                        <div class="flex items-center gap-1.5 text-[11px] text-slate-400">
                            <span class="inline-flex items-center gap-1">
                                <kbd class="px-1.5 py-0.5 rounded bg-white border border-slate-200 text-[10px] font-mono text-slate-600">Enter</kbd> kirim
                            </span>
                            <span>&bull;</span>
                            <span class="inline-flex items-center gap-1">
                                <kbd class="px-1.5 py-0.5 rounded bg-white border border-slate-200 text-[10px] font-mono text-slate-600">Ctrl+Enter</kbd> baris baru
                            </span>
                        </div>
                        <button
                            type="submit"
                            id="btn-send"
                            class="btn-primary rounded-full px-5 py-2 text-xs font-bold"
                        >
                            Kirim
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('messages-container');
            const form = document.getElementById('reply-form');
            const input = document.getElementById('message-input');
            const btnSend = document.getElementById('btn-send');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            function scrollToBottom() {
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            }
            scrollToBottom();

            input?.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    if (e.ctrlKey || e.shiftKey) {
                        if (e.ctrlKey && !e.shiftKey) {
                            e.preventDefault();
                            const start = this.selectionStart;
                            const end = this.selectionEnd;
                            this.value = this.value.substring(0, start) + "\n" + this.value.substring(end);
                            this.selectionStart = this.selectionEnd = start + 1;
                        }
                    } else {
                        e.preventDefault();
                        if (this.value.trim().length > 0) {
                            form?.requestSubmit();
                        }
                    }
                }
            });

            form?.addEventListener('submit', async function (e) {
                e.preventDefault();
                const text = input.value.trim();
                if (!text) return;

                btnSend.disabled = true;
                btnSend.textContent = 'Mengirim...';

                try {
                    const response = await fetch("{{ route('admin.chats.product.reply', $inquiry) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ message: text })
                    });

                    if (response.ok) {
                        const data = await response.json();
                        input.value = '';

                        const bubble = document.createElement('div');
                        bubble.className = 'flex flex-col items-end';
                        bubble.innerHTML = `
                            <div class="flex items-center gap-1.5 mb-1 px-1">
                                <span class="text-[11px] font-bold text-[#1B64F2]">Anda (Admin DSC)</span>
                                <span class="text-[10px] text-slate-400 font-mono">&bull; ${data.message.time}</span>
                            </div>
                            <div class="max-w-lg rounded-2xl p-3.5 px-4 text-xs leading-relaxed bg-[#EFF6FF] border border-[#BFDBFE] text-slate-900 font-medium rounded-tr-sm shadow-2xs">
                                ${text.replace(/\n/g, '<br>')}
                            </div>
                        `;
                        container.appendChild(bubble);
                        scrollToBottom();
                    } else {
                        form.submit();
                    }
                } catch (err) {
                    form.submit();
                } finally {
                    btnSend.disabled = false;
                    btnSend.textContent = 'Kirim';
                }
            });

            setInterval(async function () {
                try {
                    const res = await fetch("{{ route('admin.chats.product.show', $inquiry) }}", {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (res.ok) {
                        const json = await res.json();
                        const currentCount = container.querySelectorAll('.flex.flex-col').length;
                        if (json.messages && json.messages.length > currentCount) {
                            container.innerHTML = json.messages.map(m => {
                                const isAdmin = m.sender_type === 'admin';
                                return `
                                    <div class="flex flex-col ${isAdmin ? 'items-end' : 'items-start'}">
                                        <div class="flex items-center gap-1.5 mb-1 px-1">
                                            <span class="text-[11px] font-bold ${isAdmin ? 'text-[#1B64F2]' : 'text-slate-700'}">
                                                ${isAdmin ? 'Anda (Admin DSC)' : '{{ $inquiry->user->name }}'}
                                            </span>
                                            <span class="text-[10px] text-slate-400 font-mono">&bull; ${m.time}</span>
                                        </div>
                                        <div class="max-w-lg rounded-2xl p-3.5 px-4 text-xs leading-relaxed shadow-2xs ${isAdmin ? 'bg-[#EFF6FF] border border-[#BFDBFE] text-slate-900 font-medium rounded-tr-sm' : 'bg-white text-slate-700 rounded-tl-sm border border-slate-200'}">
                                            ${m.message.replace(/\n/g, '<br>')}
                                        </div>
                                    </div>
                                `;
                            }).join('');
                            scrollToBottom();
                        }
                    }
                } catch (e) {}
            }, 2500);
        });
    </script>
</x-layouts.admin>
