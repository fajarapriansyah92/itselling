@props(['label', 'name', 'hint' => null])

<div {{ $attributes->class('space-y-1.5') }}>
    <label for="{{ $name }}" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">{{ $label }}</label>
    {{ $slot }}
    @if ($hint)
        <p class="text-xs text-[#8FA0B2]">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="text-xs text-rose-400 font-semibold">{{ $message }}</p>
    @enderror
</div>
