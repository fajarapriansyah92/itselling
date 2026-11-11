@props(['status'])

@php
    $statusEnum = $status instanceof \App\Enums\OrderStatus ? $status : \App\Enums\OrderStatus::tryFrom($status);
    $config = match ($statusEnum) {
        \App\Enums\OrderStatus::Pending => [
            'bg' => 'bg-amber-50 border-amber-200 text-amber-700',
            'dot' => 'bg-amber-500',
            'label' => $statusEnum?->label() ?? 'Menunggu',
        ],
        \App\Enums\OrderStatus::Paid => [
            'bg' => 'bg-sky-50 border-sky-200 text-sky-700',
            'dot' => 'bg-sky-500',
            'label' => $statusEnum?->label() ?? 'Dibayar',
        ],
        \App\Enums\OrderStatus::Processing => [
            'bg' => 'bg-purple-50 border-purple-200 text-purple-700',
            'dot' => 'bg-purple-500 animate-pulse',
            'label' => $statusEnum?->label() ?? 'Diproses',
        ],
        \App\Enums\OrderStatus::Shipped => [
            'bg' => 'bg-blue-50 border-blue-200 text-blue-700',
            'dot' => 'bg-blue-500',
            'label' => $statusEnum?->label() ?? 'Dikirim',
        ],
        \App\Enums\OrderStatus::Delivered => [
            'bg' => 'bg-teal-50 border-teal-200 text-teal-700',
            'dot' => 'bg-teal-500',
            'label' => $statusEnum?->label() ?? 'Diterima',
        ],
        \App\Enums\OrderStatus::Completed => [
            'bg' => 'bg-emerald-50 border-emerald-200 text-emerald-700',
            'dot' => 'bg-emerald-500',
            'label' => $statusEnum?->label() ?? 'Selesai',
        ],
        \App\Enums\OrderStatus::Cancelled => [
            'bg' => 'bg-rose-50 border-rose-200 text-rose-700',
            'dot' => 'bg-rose-500',
            'label' => $statusEnum?->label() ?? 'Dibatalkan',
        ],
        default => [
            'bg' => 'bg-slate-100 border-slate-200 text-slate-600',
            'dot' => 'bg-slate-400',
            'label' => 'Unknown',
        ]
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold whitespace-nowrap ' . $config['bg']]) }}>
    <span class="inline-block size-1.5 rounded-full {{ $config['dot'] }}" style="width: 6px; height: 6px; min-width: 6px;"></span>
    <span>{{ $config['label'] }}</span>
</span>
