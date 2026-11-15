@props([
    'show',
    'maxWidth' => 'max-w-2xl',
    'title' => null,
    'closeAction' => null,
])

@php
    $closeHandler = $closeAction ?: "{$show} = false";
@endphp

<div
    x-show="{{ $show }}"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    role="dialog"
    aria-modal="true"
>
    {{-- Blurred Backdrop (Strict Rule: Never closes modal on backdrop click) --}}
    <div
        x-show="{{ $show }}"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-[#1F0E38]/55 backdrop-blur-md transition-opacity"
    ></div>

    <div class="flex min-h-full items-center justify-center p-4">
        <div
            x-show="{{ $show }}"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-3 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-3 sm:scale-95"
            class="relative w-full {{ $maxWidth }} glass-modal rounded-3xl overflow-hidden text-left"
        >
            {{-- Specular Top Highlight Edge --}}
            <div class="absolute top-0 inset-x-10 h-[1.5px] bg-gradient-to-r from-transparent via-white to-transparent pointer-events-none"></div>

            @if($title || isset($header))
                <div class="px-6 py-4 bg-purple-deep/95 text-white flex items-center justify-between gap-4">
                    @if(isset($header))
                        {{ $header }}
                    @else
                        <h3 class="text-base sm:text-lg font-bold text-white tracking-tight">{{ $title }}</h3>
                    @endif

                    <button
                        type="button"
                        @click="{{ $closeHandler }}"
                        class="w-9 h-9 rounded-full bg-white/15 hover:bg-white/25 text-white flex items-center justify-center transition cursor-pointer flex-shrink-0"
                        title="Tutup"
                        aria-label="Tutup"
                    >
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>
            @endif

            <div class="p-6">
                {{ $slot }}
            </div>

            @if(isset($footer))
                <div class="px-6 py-4 bg-white/40 border-t border-white/70 flex items-center justify-end gap-3">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
