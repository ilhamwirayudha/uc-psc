@props([
    'padding' => 'p-6',
    'rounded' => 'rounded-2xl',
])

<div {{ $attributes->merge(['class' => "glass-card {$rounded} overflow-hidden"]) }}>
    @if(isset($header))
        <div class="px-6 py-4 bg-purple-deep/95 text-white border-b border-purple-900/30 flex items-center justify-between">
            {{ $header }}
        </div>
    @endif

    <div class="{{ $padding }}">
        {{ $slot }}
    </div>
</div>
