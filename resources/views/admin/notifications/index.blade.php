<x-layouts.admin title="Pusat Notifikasi">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-extrabold text-white tracking-tight">Pusat Notifikasi</h1>
                @php
                    $unreadTotal = $notifications->where('read_at', null)->count();
                @endphp
                @if ($unreadTotal > 0)
                    <span id="page-notif-unread-count" class="rounded-full bg-[#00DFB6]/20 text-[#00DFB6] border border-[#00DFB6]/30 px-2.5 py-0.5 text-xs font-black">
                        {{ $unreadTotal }} Belum Dibaca
                    </span>
                @endif
            </div>
            <p class="text-xs text-[#8FA0B2] mt-1">Pantau seluruh pembaruan transaksi, pesan obrolan, dan ulasan pelanggan secara terpusat.</p>
        </div>
        @if ($unreadTotal > 0)
            <div id="page-mark-all-container">
                <button type="button" id="page-btn-mark-all-read" class="inline-flex items-center gap-2 rounded-xl border border-[#00DFB6]/40 bg-[#00DFB6]/10 px-4 py-2 text-xs font-bold text-[#00DFB6] hover:bg-[#00DFB6] hover:text-[#090E17] transition cursor-pointer shadow-lg shadow-[#00DFB6]/10">
                    <svg style="width: 15px; height: 15px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Tandai Semua Dibaca</span>
                </button>
            </div>
        @endif
    </div>

    <div class="space-y-3" id="page-notif-list">
        @forelse ($notifications as $notif)
            <div @class([
                'page-notif-item flex items-start gap-4 rounded-2xl border p-4 transition-all',
                'bg-[#0D1522] border-[#1A2838]' => $notif->read_at !== null,
                'bg-[#0D1522] border-[#00DFB6]/40 shadow-lg shadow-[#00DFB6]/5 is-page-unread' => $notif->read_at === null,
            ])>
                {{-- Icon based on type --}}
                <div @class([
                    'mt-0.5 size-10 shrink-0 rounded-xl flex items-center justify-center border',
                    'bg-amber-500/15 text-amber-400 border-amber-500/30' => $notif->type === 'new_order',
                    'bg-emerald-500/15 text-emerald-400 border-emerald-500/30' => $notif->type === 'payment',
                    'bg-purple-500/15 text-purple-400 border-purple-500/30' => $notif->type === 'order_received',
                    'bg-[#00DFB6]/15 text-[#00DFB6] border-[#00DFB6]/30' => $notif->type === 'order_chat',
                    'bg-sky-500/15 text-sky-400 border-sky-500/30' => !in_array($notif->type, ['new_order', 'payment', 'order_received', 'order_chat']),
                ])>
                    @if ($notif->type === 'new_order')
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    @elseif ($notif->type === 'payment')
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    @elseif ($notif->type === 'order_chat')
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    @elseif ($notif->type === 'order_received')
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                    @else
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="font-bold text-white text-sm">{{ $notif->title }}</p>
                        @if ($notif->read_at === null)
                            <span class="page-unread-pill inline-flex items-center rounded-full bg-[#00DFB6] px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-[#090E17]">Baru</span>
                        @endif
                    </div>
                    <p class="text-xs text-[#8FA0B2] mt-1 leading-relaxed">{{ $notif->message }}</p>
                    <p class="text-[10px] text-[#6B8299] mt-2 font-mono">{{ $notif->created_at->translatedFormat('d M Y, H:i') }} ({{ $notif->created_at->diffForHumans() }})</p>

                    @if ($notif->type === 'order_received')
                        <div class="mt-3 p-3 rounded-xl bg-amber-500/10 border border-amber-500/30">
                            <p class="text-xs font-bold text-amber-300 mb-1 flex items-center gap-1.5">
                                <span>⚡</span> Tindakan Diperlukan
                            </p>
                            <p class="text-xs text-amber-200/80 mb-2.5">Pembeli sudah konfirmasi terima barang. Silakan selesaikan pesanan ini secara resmi.</p>
                            <a href="{{ route('admin.notifications.read', $notif) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-bold text-[#090E17] hover:bg-amber-400 transition">
                                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                Selesaikan Pesanan
                            </a>
                        </div>
                    @endif
                </div>

                <div class="flex shrink-0 flex-col items-end gap-2">
                    @if ($notif->link && $notif->type !== 'order_received')
                        <a href="{{ route('admin.notifications.read', $notif) }}" class="inline-flex items-center gap-1 rounded-lg border border-[#1A2838] bg-[#101924] px-2.5 py-1 text-xs font-bold text-[#00DFB6] hover:bg-[#00DFB6] hover:text-[#090E17] transition">
                            <span>Buka</span>
                            <span class="text-xs">&rarr;</span>
                        </a>
                    @endif
                    @if ($notif->read_at !== null)
                        <span class="text-[11px] text-[#6B8299] font-medium flex items-center gap-1">
                            <svg style="width: 12px; height: 12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Dibaca
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center py-20 text-center rounded-2xl border border-[#1A2838] bg-[#0D1522]">
                <div class="size-14 rounded-2xl bg-[#101924] text-[#6B8299] flex items-center justify-center mb-3 border border-[#1A2838]">
                    <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                </div>
                <p class="font-bold text-white text-sm">Belum Ada Notifikasi</p>
                <p class="text-xs text-[#8FA0B2] mt-1 max-w-sm">Notifikasi baru akan muncul di sini setiap kali ada pesanan baru, pembayaran masuk, atau obrolan pelanggan.</p>
            </div>
        @endforelse
    </div>

    @if ($notifications->hasPages())
        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pageMarkAllBtn = document.getElementById('page-btn-mark-all-read');
            if (pageMarkAllBtn) {
                pageMarkAllBtn.addEventListener('click', async function () {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    pageMarkAllBtn.disabled = true;
                    pageMarkAllBtn.innerHTML = '<span>Memproses...</span>';

                    try {
                        const response = await fetch("{{ route('admin.notifications.read-all') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (response.ok) {
                            // Update badge count
                            document.getElementById('page-notif-unread-count')?.remove();
                            document.getElementById('page-mark-all-container')?.remove();
                            document.querySelectorAll('.page-unread-pill').forEach(el => el.remove());
                            document.querySelectorAll('.page-notif-item').forEach(el => {
                                el.classList.remove('border-[#00DFB6]/40', 'shadow-lg', 'shadow-[#00DFB6]/5', 'is-page-unread');
                                el.classList.add('border-[#1A2838]');
                            });
                            // Also update top bell badge
                            document.getElementById('notif-bell-badge')?.remove();
                            document.getElementById('notif-badge-count')?.remove();
                            document.getElementById('btn-mark-all-read')?.remove();
                        } else if (response.status === 419) {
                            window.location.reload();
                        } else {
                            window.location.href = "{{ route('admin.notifications.read-all') }}";
                        }
                    } catch (err) {
                        console.error('Failed to mark all as read:', err);
                        window.location.href = "{{ route('admin.notifications.read-all') }}";
                    }
                });
            }
        });
    </script>
</x-layouts.admin>
