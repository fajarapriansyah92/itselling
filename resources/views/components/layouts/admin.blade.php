@props(['title'])

@php
    $pendingOrders = \App\Models\Order::where('status', \App\Enums\OrderStatus::Pending)->count();
    $paidOrders = \App\Models\Order::where('status', \App\Enums\OrderStatus::Paid)->count();
    $unreadOrderChat = \App\Models\OrderMessage::where('sender_type', 'customer')->whereNull('read_at')->count();
    $unreadProductChat = \App\Models\ProductInquiryMessage::where('sender_type', 'customer')->whereNull('read_at')->count();
    $unreadChatCount = $unreadOrderChat + $unreadProductChat;
    $unrepliedReviewsCount = \App\Models\ProductReview::whereNull('admin_reply')->count();
    $unreadCount = \App\Models\AdminNotification::whereNull('read_at')->count();
    $latestNotifications = \App\Models\AdminNotification::orderByRaw('read_at IS NOT NULL, created_at DESC')->take(6)->get();
    $hasLogo = file_exists(public_path('images/logo.png'));

    $primaryNavigation = [
        ['route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l9-9 9 9M5 10v10h14V10'],
        ['route' => 'admin.orders.index', 'active' => 'admin.orders.*', 'label' => 'Pesanan', 'badge' => $pendingOrders + $paidOrders, 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        ['route' => 'admin.products.index', 'active' => 'admin.products.*', 'label' => 'Produk & Jasa', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
        ['route' => 'admin.customers.index', 'active' => 'admin.customers.*', 'label' => 'Pelanggan', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
        ['route' => 'admin.chats.index', 'active' => 'admin.chats.*', 'label' => 'Chat & Pesan', 'badge' => $unreadChatCount, 'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'],
        ['route' => 'admin.categories.index', 'active' => 'admin.categories.*', 'label' => 'Kategori', 'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
        ['route' => 'admin.reviews.index', 'active' => 'admin.reviews.*', 'label' => 'Ulasan Produk', 'badge' => $unrepliedReviewsCount, 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
    ];

    $secondaryNavigation = [
        ['route' => 'admin.reports.sales', 'active' => 'admin.reports.*', 'label' => 'Laporan', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
        ['route' => 'admin.api-monitor.index', 'active' => 'admin.api-monitor.*', 'label' => 'Web Service', 'icon' => 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z'],
    ];
@endphp

<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #1B64F2;
            color: #0F172A;
        }
        .medlio-nav-item {
            position: relative;
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
            transition: all 0.18s ease;
        }
        .medlio-nav-item:hover {
            color: #FFFFFF;
            background-color: rgba(255, 255, 255, 0.12);
        }
        .medlio-nav-item.active {
            color: #1B64F2;
            background-color: #F5F7FB;
            font-weight: 700;
        }
        @media (min-width: 1024px) {
            .medlio-nav-item.active::before {
                content: "";
                position: absolute;
                right: 0;
                top: -16px;
                width: 16px;
                height: 16px;
                background: transparent;
                border-bottom-right-radius: 16px;
                box-shadow: 6px 6px 0 6px #F5F7FB;
                pointer-events: none;
            }
            .medlio-nav-item.active::after {
                content: "";
                position: absolute;
                right: 0;
                bottom: -16px;
                width: 16px;
                height: 16px;
                background: transparent;
                border-top-right-radius: 16px;
                box-shadow: 6px -6px 0 6px #F5F7FB;
                pointer-events: none;
            }
        }
        svg {
            flex-shrink: 0;
        }
        @keyframes toastSlideIn {
            from { opacity: 0; transform: translateY(-12px) scale(0.96); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes toastProgress {
            from { width: 100%; }
            to { width: 0%; }
        }
        .medlio-toast-enter {
            animation: toastSlideIn 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .medlio-toast-bar {
            animation: toastProgress 4s linear forwards;
        }
        @page {
            size: A4 portrait;
            margin: 10mm 12mm;
        }
        @media print {
            html, body {
                background: #FFFFFF !important;
                color: #0F172A !important;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-[#1B64F2] font-sans text-slate-900 antialiased selection:bg-[#1B64F2] selection:text-white">
    <!-- Global Floating Toast Container (Top Right) -->
    <div id="medlio-toast-container" class="fixed top-5 right-5 z-[100] flex flex-col gap-2.5 w-full max-w-sm pointer-events-none px-4 sm:px-0 print:hidden"></div>

    <!-- Global Custom Confirmation Modal (Replaces native browser confirm()) -->
    <div id="medlio-confirm-backdrop" class="fixed inset-0 z-[110] hidden items-center justify-center bg-slate-900/45 backdrop-blur-[2px] p-4 print:hidden">
        <div id="medlio-confirm-box" class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl border border-slate-100 transform transition-all scale-95 opacity-0 duration-150">
            <div class="flex items-start gap-4">
                <div id="medlio-confirm-icon-wrap" class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-[#EBF3FF] text-[#1B64F2]">
                    <svg id="medlio-confirm-icon" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 id="medlio-confirm-title" class="text-base font-extrabold text-slate-900">Konfirmasi Tindakan</h3>
                    <p id="medlio-confirm-message" class="mt-1.5 text-xs text-slate-500 leading-relaxed"></p>
                </div>
            </div>
            <div class="mt-6 flex items-center justify-end gap-2.5">
                <button type="button" id="medlio-confirm-cancel"
                        class="rounded-full border border-slate-200 bg-white px-5 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition cursor-pointer">
                    Batal
                </button>
                <button type="button" id="medlio-confirm-ok"
                        class="rounded-full bg-[#1B64F2] px-5 py-2 text-xs font-bold text-white shadow-md shadow-blue-600/20 hover:bg-[#1554D1] transition cursor-pointer">
                    Ya, Lanjutkan
                </button>
            </div>
        </div>
    </div>

    <!-- Outer Royal Blue Frame (Medlio Shell) -->
    <div class="flex min-h-screen flex-col lg:flex-row lg:p-3 print:block print:min-h-0 print:p-0 print:bg-white">
        <!-- Left Sidebar -->
        <aside class="w-full lg:w-60 shrink-0 flex flex-col justify-between pt-5 pb-6 pl-4 pr-4 lg:pr-0 print:hidden select-none">
            <div>
                <!-- Brand Title / Logo -->
                <div class="px-4 pt-2 pb-7 flex items-center gap-3">
                    @if ($hasLogo)
                        <div class="size-9 rounded-xl bg-white flex items-center justify-center p-1 shadow-sm shrink-0">
                            <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="w-full h-full object-contain">
                        </div>
                    @endif
                    <a href="{{ route('admin.dashboard') }}" class="text-2xl font-extrabold tracking-tight text-white">
                        {{ config('app.name') }}
                    </a>
                </div>

                <!-- Primary Menu -->
                <nav class="space-y-1">
                    @foreach ($primaryNavigation as $item)
                        @php $isActive = request()->routeIs($item['active']); @endphp
                        <a href="{{ route($item['route']) }}"
                           class="medlio-nav-item flex items-center justify-between rounded-2xl lg:rounded-r-none lg:rounded-l-full px-4 py-3 text-[13px] {{ $isActive ? 'active' : '' }}">
                            <div class="flex items-center gap-3">
                                <svg class="size-[18px]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                    <path d="{{ $item['icon'] }}" />
                                </svg>
                                <span>{{ $item['label'] }}</span>
                            </div>
                            <span class="sidebar-badge-wrapper" data-badge-route="{{ $item['route'] }}">
                                @if (isset($item['badge']) && $item['badge'] > 0)
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-extrabold {{ $isActive ? 'bg-[#1B64F2] text-white' : 'bg-white text-[#1B64F2]' }}">
                                        {{ $item['badge'] }}
                                    </span>
                                @endif
                            </span>
                        </a>
                    @endforeach
                </nav>

                <!-- Divider Line -->
                <div class="my-5 mr-4 border-t border-white/25"></div>

                <!-- Secondary Menu -->
                <nav class="space-y-1">
                    @foreach ($secondaryNavigation as $item)
                        @php $isActive = request()->routeIs($item['active']); @endphp
                        <a href="{{ route($item['route']) }}"
                           class="medlio-nav-item flex items-center justify-between rounded-2xl lg:rounded-r-none lg:rounded-l-full px-4 py-3 text-[13px] {{ $isActive ? 'active' : '' }}">
                            <div class="flex items-center gap-3">
                                <svg class="size-[18px]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                    <path d="{{ $item['icon'] }}" />
                                </svg>
                                <span>{{ $item['label'] }}</span>
                            </div>
                        </a>
                    @endforeach
                </nav>
            </div>

            <!-- Subtle Bottom Version Status -->
            <div class="hidden lg:flex items-center gap-2 px-4 pt-6 text-white/75 text-xs font-medium">
                <span class="size-2 rounded-full bg-emerald-300 animate-pulse"></span>
                <span>{{ config('app.name') }} Control</span>
            </div>
        </aside>

        <!-- Main Rounded Inner Canvas -->
        <div class="flex min-w-0 flex-1 flex-col bg-[#F5F7FB] lg:rounded-[28px] shadow-2xl overflow-hidden print:bg-white print:rounded-none print:shadow-none print:overflow-visible">
            <!-- Top White Floating Bar (Search + Icons + Profile) -->
            <header class="mx-4 sm:mx-6 mt-4 sm:mt-5 rounded-2xl bg-white px-4 sm:px-6 py-3 flex items-center justify-between gap-4 border border-slate-200/60 shadow-[0_2px_10px_-3px_rgba(15,23,42,0.05)] print:hidden">
                <!-- Left Search Bar -->
                <form action="{{ route('admin.orders.index') }}" method="GET" class="relative flex-1 max-w-md">
                    <svg class="size-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search pesanan, invoice, pelanggan..."
                           style="padding-left: 2.4rem;"
                           class="w-full rounded-xl border border-slate-200/80 bg-[#F8FAFC] py-2 pr-4 text-xs font-medium text-slate-700 placeholder:text-slate-400 focus:border-[#1B64F2] focus:bg-white focus:outline-none">
                </form>

                <!-- Right Action Icons & User Pill -->
                <div class="flex items-center gap-2.5">
                    <!-- Notification Bell Button -->
                    <div class="relative" id="notif-container">
                        <button type="button" id="notif-btn"
                           class="relative flex size-9 items-center justify-center rounded-xl border border-slate-200/90 bg-white text-slate-600 transition hover:border-[#1B64F2] hover:text-[#1B64F2] cursor-pointer"
                           title="Notifikasi">
                            <svg class="size-[17px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            @if ($unreadCount > 0)
                                <span id="notif-bell-badge" class="absolute -top-1 -right-1 flex size-4 items-center justify-center rounded-full bg-rose-500 text-[9px] font-bold text-white ring-2 ring-white">
                                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                </span>
                            @endif
                        </button>

                        <!-- Dropdown Panel -->
                        <div id="notif-dropdown" class="hidden absolute right-0 mt-2.5 w-80 sm:w-96 rounded-2xl border border-slate-200 bg-white shadow-2xl z-50 overflow-hidden divide-y divide-slate-100">
                            <div class="p-3.5 px-4 flex items-center justify-between bg-[#F8FAFC]">
                                <div class="flex items-center gap-2">
                                    <span class="font-extrabold text-sm text-slate-900">Notifikasi</span>
                                    @if ($unreadCount > 0)
                                        <span id="notif-badge-count" class="rounded-full bg-[#DBEAFE] text-[#1B64F2] px-2 py-0.5 text-[10px] font-extrabold">
                                            {{ $unreadCount }} Baru
                                        </span>
                                    @endif
                                </div>
                                @if ($unreadCount > 0)
                                    <button type="button" id="btn-mark-all-read" class="text-[11px] font-bold text-[#1B64F2] hover:underline cursor-pointer">
                                        Tandai semua dibaca
                                    </button>
                                @endif
                            </div>

                            <div class="max-h-[340px] overflow-y-auto divide-y divide-slate-100">
                                @forelse ($latestNotifications as $notif)
                                    <a href="{{ $notif->link ?? '#' }}"
                                       data-read-url="{{ route('admin.notifications.read', $notif) }}"
                                       class="notif-item flex items-start gap-3 p-3.5 hover:bg-slate-50 transition group {{ $notif->isUnread() ? 'bg-blue-50/50' : '' }}">
                                        <div class="size-8 rounded-lg shrink-0 flex items-center justify-center mt-0.5 bg-blue-50 text-[#1B64F2] border border-blue-200">
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-1">
                                                <p class="text-xs font-bold text-slate-900 truncate group-hover:text-[#1B64F2] transition">{{ $notif->title }}</p>
                                                @if ($notif->isUnread())
                                                    <span class="notif-unread-dot size-2 rounded-full bg-[#1B64F2] shrink-0"></span>
                                                @endif
                                            </div>
                                            <p class="text-[11px] text-slate-600 line-clamp-2 mt-0.5 leading-snug">{{ $notif->message }}</p>
                                            <p class="text-[10px] text-slate-400 mt-1 font-mono">{{ $notif->created_at->diffForHumans() }}</p>
                                        </div>
                                    </a>
                                @empty
                                    <div class="p-6 text-center">
                                        <p class="text-xs font-bold text-slate-700">Tidak ada notifikasi</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">Semua pembaruan pesanan sudah diperiksa.</p>
                                    </div>
                                @endforelse
                            </div>

                            <div class="p-2.5 bg-[#F8FAFC] text-center">
                                <a href="{{ route('admin.notifications.index') }}" class="text-xs font-bold text-[#1B64F2] hover:underline block py-1">
                                    Lihat Semua Riwayat Notifikasi &rarr;
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Logout Icon Button -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                title="Keluar dari Panel Admin"
                                class="flex size-9 items-center justify-center rounded-xl border border-slate-200/90 bg-white text-slate-600 transition hover:border-rose-300 hover:bg-rose-50 hover:text-rose-600 cursor-pointer">
                            <svg class="size-[17px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>

                    <!-- User Profile Card Pill -->
                    <div class="flex items-center gap-2.5 rounded-xl border border-slate-200/80 bg-[#F8FAFC] px-3 py-1.5">
                        <div class="size-7 rounded-full bg-[#1B64F2] text-white font-extrabold text-xs flex items-center justify-center shrink-0">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="hidden sm:block text-left leading-tight">
                            <p class="text-xs font-bold text-slate-900 truncate max-w-[130px]">{{ auth()->user()->name }}</p>
                            <p class="text-[10px] text-slate-400 truncate max-w-[130px]">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Body -->
            <main class="flex-1 p-4 sm:p-6 space-y-5 max-w-7xl w-full mx-auto print:p-0 print:space-y-0 print:max-w-none">
                @if (!request()->routeIs('admin.dashboard'))
                    <div class="flex flex-wrap items-center justify-between gap-2 pb-1 print:hidden">
                        <div>
                            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">{{ $title }}</h1>
                            <p class="text-xs text-slate-500">{{ now()->translatedFormat('l, d F Y') }}</p>
                        </div>
                    </div>
                @endif

                @php $flashSuccess = session('status') ?? session('success'); @endphp
                @if ($flashSuccess)
                    <div id="flash-status-banner" class="relative overflow-hidden rounded-2xl border border-emerald-200/90 bg-white p-4 shadow-sm flex items-center justify-between gap-4 print:hidden">
                        <div class="flex items-center gap-3.5">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500 text-white shadow-xs">
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-extrabold uppercase tracking-wider text-emerald-600">Berhasil</p>
                                <p class="text-xs sm:text-sm font-bold text-slate-800 mt-0.5">{{ $flashSuccess }}</p>
                            </div>
                        </div>
                        <button type="button" onclick="this.closest('#flash-status-banner').remove()"
                                class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition cursor-pointer">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div id="flash-error-banner" class="relative overflow-hidden rounded-2xl border border-rose-200/90 bg-white p-4 shadow-sm flex items-center justify-between gap-4 print:hidden">
                        <div class="flex items-center gap-3.5">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-rose-500 text-white shadow-xs">
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-extrabold uppercase tracking-wider text-rose-600">Perhatian</p>
                                <p class="text-xs sm:text-sm font-bold text-slate-800 mt-0.5">{{ session('error') }}</p>
                            </div>
                        </div>
                        <button type="button" onclick="this.closest('#flash-error-banner').remove()"
                                class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition cursor-pointer">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    <script>
        // Global Medlio Floating Toast Function
        window.showToast = function (message, type = 'success', customTitle = null) {
            const container = document.getElementById('medlio-toast-container');
            if (!container) return;

            const isError = type === 'error';
            const isInfo = type === 'info';
            const accentBg = isError ? 'bg-rose-500' : (isInfo ? 'bg-[#1B64F2]' : 'bg-emerald-500');
            const accentText = isError ? 'text-rose-600' : (isInfo ? 'text-[#1B64F2]' : 'text-emerald-600');
            const title = customTitle || (isError ? 'Gagal' : (isInfo ? 'Informasi' : 'Berhasil'));
            const iconPath = isError
                ? 'M6 18L18 6M6 6l12 12'
                : 'M5 13l4 4L19 7';

            const toast = document.createElement('div');
            toast.className = 'medlio-toast-enter pointer-events-auto relative overflow-hidden rounded-2xl border border-slate-200/90 bg-white p-4 shadow-xl flex items-start gap-3.5';
            toast.innerHTML = `
                <div class="flex size-9 shrink-0 items-center justify-center rounded-xl ${accentBg} text-white shadow-xs">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="${iconPath}" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0 pr-2">
                    <p class="text-[11px] font-extrabold uppercase tracking-wider ${accentText}">${title}</p>
                    <p class="text-xs font-bold text-slate-800 mt-0.5 leading-snug"></p>
                </div>
                <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition cursor-pointer">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <div class="medlio-toast-bar absolute bottom-0 left-0 h-1 ${accentBg}"></div>
            `;
            toast.querySelector('p.text-slate-800').textContent = message;
            toast.querySelector('button').addEventListener('click', () => toast.remove());

            container.appendChild(toast);
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateY(-8px)';
                    toast.style.transition = 'all 0.2s ease';
                    setTimeout(() => toast.remove(), 200);
                }
            }, 4000);
        };

        // Helper for copying text with sleek Toast
        window.copyWithToast = function (text, label = 'Teks berhasil disalin!') {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(() => {
                    window.showToast(label, 'info', 'Disalin ke Clipboard');
                });
            } else {
                window.showToast(label, 'info', 'Disalin ke Clipboard');
            }
        };

        // Replace native browser alert() with sleek Medlio Toast
        window.alert = function (msg) {
            window.showToast(String(msg), 'info', 'Pemberitahuan');
        };

        // Global Custom Confirm Modal
        window.showConfirmModal = function (message, onConfirm, isDanger = false) {
            const backdrop = document.getElementById('medlio-confirm-backdrop');
            const box = document.getElementById('medlio-confirm-box');
            const titleEl = document.getElementById('medlio-confirm-title');
            const msgEl = document.getElementById('medlio-confirm-message');
            const okBtn = document.getElementById('medlio-confirm-ok');
            const cancelBtn = document.getElementById('medlio-confirm-cancel');
            const iconWrap = document.getElementById('medlio-confirm-icon-wrap');

            if (!backdrop || !box) {
                if (confirm(message)) onConfirm();
                return;
            }

            msgEl.textContent = message;
            if (isDanger) {
                titleEl.textContent = 'Konfirmasi Penghapusan';
                iconWrap.className = 'flex size-12 shrink-0 items-center justify-center rounded-2xl bg-rose-50 text-rose-600';
                okBtn.className = 'rounded-full bg-rose-600 px-5 py-2 text-xs font-bold text-white shadow-md shadow-rose-600/20 hover:bg-rose-700 transition cursor-pointer';
                okBtn.textContent = 'Ya, Hapus';
            } else {
                titleEl.textContent = 'Konfirmasi Tindakan';
                iconWrap.className = 'flex size-12 shrink-0 items-center justify-center rounded-2xl bg-[#EBF3FF] text-[#1B64F2]';
                okBtn.className = 'rounded-full bg-[#1B64F2] px-5 py-2 text-xs font-bold text-white shadow-md shadow-blue-600/20 hover:bg-[#1554D1] transition cursor-pointer';
                okBtn.textContent = 'Ya, Lanjutkan';
            }

            backdrop.classList.remove('hidden');
            backdrop.classList.add('flex');
            requestAnimationFrame(() => {
                box.classList.remove('scale-95', 'opacity-0');
                box.classList.add('scale-100', 'opacity-100');
            });

            function close() {
                box.classList.remove('scale-100', 'opacity-100');
                box.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    backdrop.classList.add('hidden');
                    backdrop.classList.remove('flex');
                }, 150);
            }

            const newOk = okBtn.cloneNode(true);
            const newCancel = cancelBtn.cloneNode(true);
            okBtn.parentNode.replaceChild(newOk, okBtn);
            cancelBtn.parentNode.replaceChild(newCancel, cancelBtn);

            newCancel.addEventListener('click', close);
            newOk.addEventListener('click', function () {
                close();
                onConfirm();
            });
        };

        document.addEventListener('DOMContentLoaded', function () {
            @if ($flashSuccess)
                window.showToast(@json($flashSuccess), 'success');
            @endif
            @if (session('error'))
                window.showToast(@json(session('error')), 'error');
            @endif

            // Automatically upgrade all inline return confirm('...') on forms & buttons to Medlio Modal!
            document.querySelectorAll('form[onsubmit*="confirm("], button[onclick*="confirm("]').forEach(function (el) {
                const attrName = el.tagName === 'FORM' ? 'onsubmit' : 'onclick';
                const rawAttr = el.getAttribute(attrName) || '';
                const match = rawAttr.match(/confirm\(['"](.+?)['"]\)/);
                if (!match) return;

                const message = match[1];
                const isDanger = /hapus|kosongkan|batal/i.test(message);
                el.removeAttribute(attrName);

                if (el.tagName === 'FORM') {
                    el.addEventListener('submit', function (e) {
                        if (el.dataset.confirmed === 'true') return;
                        e.preventDefault();
                        window.showConfirmModal(message, function () {
                            el.dataset.confirmed = 'true';
                            el.requestSubmit ? el.requestSubmit() : el.submit();
                        }, isDanger);
                    });
                } else {
                    el.addEventListener('click', function (e) {
                        if (el.dataset.confirmed === 'true') return;
                        e.preventDefault();
                        window.showConfirmModal(message, function () {
                            el.dataset.confirmed = 'true';
                            const targetForm = el.getAttribute('form')
                                ? document.getElementById(el.getAttribute('form'))
                                : el.closest('form');
                            if (targetForm) {
                                targetForm.dataset.confirmed = 'true';
                                targetForm.requestSubmit ? targetForm.requestSubmit() : targetForm.submit();
                            } else {
                                el.click();
                            }
                        }, isDanger);
                    });
                }
            });

            const notifBtn = document.getElementById('notif-btn');
            const notifDropdown = document.getElementById('notif-dropdown');
            const notifContainer = document.getElementById('notif-container');

            if (notifBtn && notifDropdown) {
                notifBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    notifDropdown.classList.toggle('hidden');
                });

                document.addEventListener('click', function (e) {
                    if (notifContainer && !notifContainer.contains(e.target)) {
                        notifDropdown.classList.add('hidden');
                    }
                });

                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') {
                        notifDropdown.classList.add('hidden');
                    }
                });
            }

            const btnMarkAll = document.getElementById('btn-mark-all-read');
            if (btnMarkAll) {
                btnMarkAll.addEventListener('click', async function (e) {
                    e.preventDefault();
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    btnMarkAll.disabled = true;
                    btnMarkAll.textContent = 'Memproses...';

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
                            document.getElementById('notif-bell-badge')?.remove();
                            document.getElementById('notif-badge-count')?.remove();
                            btnMarkAll.remove();
                            document.querySelectorAll('.notif-unread-dot').forEach(el => el.remove());
                            document.querySelectorAll('.notif-item').forEach(el => el.classList.remove('bg-blue-50/50'));
                            window.showToast('Semua notifikasi telah ditandai dibaca.', 'success');
                        } else if (response.status === 419) {
                            window.location.reload();
                        } else {
                            btnMarkAll.disabled = false;
                            btnMarkAll.textContent = 'Tandai semua dibaca';
                        }
                    } catch (err) {
                        window.location.href = "{{ route('admin.notifications.read-all') }}";
                    }
                });
            }

            document.querySelectorAll('.notif-item').forEach(function (item) {
                item.addEventListener('click', function (e) {
                    const readUrl = this.getAttribute('data-read-url');
                    const href = this.getAttribute('href');
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    if (readUrl) {
                        fetch(readUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        }).catch(() => {});
                    }

                    if (!href || href === '#' || href === 'javascript:void(0)') {
                        e.preventDefault();
                    }
                });
            });

            // Sinkronisasi Real-Time Web Admin dengan RESTful Web Service (/api/v1/admin/*)
            (async function syncWebAdminWithRestWebService() {
                const path = window.location.pathname;
                let apiEndpoint = '/api/v1/admin/dashboard';

                if (path.includes('/admin/products')) {
                    apiEndpoint = '/api/v1/admin/products' + window.location.search;
                } else if (path.includes('/admin/categories')) {
                    apiEndpoint = '/api/v1/admin/categories';
                } else if (path.includes('/admin/orders')) {
                    apiEndpoint = '/api/v1/admin/orders' + window.location.search;
                } else if (path.includes('/admin/customers')) {
                    apiEndpoint = '/api/v1/admin/customers';
                } else if (path.includes('/admin/chats')) {
                    apiEndpoint = '/api/v1/admin/chats';
                } else if (path.includes('/admin/reviews')) {
                    apiEndpoint = '/api/v1/admin/reviews';
                } else if (path.includes('/admin/reports')) {
                    apiEndpoint = '/api/v1/admin/reports/sales';
                } else if (path.includes('/admin/api-monitor')) {
                    return;
                }

                try {
                    const start = performance.now();
                    console.info(`🌐 [HTTP REQUEST] ---> GET ${window.location.origin}${apiEndpoint} | Platform: Web Control Panel (Admin)`);
                    const res = await fetch(apiEndpoint, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-Client-Platform': 'Web Control Panel (Admin)'
                        }
                    });
                    const elapsed = Math.round(performance.now() - start);
                    const text = await res.clone().text();
                    console.info(`✅ [HTTP RESPONSE] <--- ${res.status} (${elapsed}ms) | Data: ${text.length} bytes | ${apiEndpoint}`);
                } catch (e) {
                    // Ignore network error
                }
            })();
        });
    </script>
</body>
</html>
