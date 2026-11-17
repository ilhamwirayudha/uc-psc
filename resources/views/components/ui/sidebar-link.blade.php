@props([
    'href',
    'active' => false,
    'label',
    'sub' => false,
])

<a
    href="{{ $href }}"
    :title="sidebarCollapsed ? '{{ $label }}' : null"
    {{ $attributes->merge([
        'class' => implode(' ', [
            'group relative flex items-center gap-3 rounded-xl border transition-all duration-150',
            $sub ? 'px-3 py-2 text-[14px] whitespace-nowrap' : 'px-3.5 py-2.5 text-[15px]',
            $active
                ? 'bg-white/18 border-white/25 backdrop-blur-sm text-white font-semibold shadow-[0_6px_20px_rgba(0,0,0,0.12),inset_0_1px_0_rgba(255,255,255,0.22)]'
                : 'border-transparent text-white/95 hover:bg-white/10 hover:border-white/15 hover:text-white font-medium',
        ]),
    ]) }}
    @if(!$sub)
    :class="sidebarCollapsed ? 'lg:justify-center lg:px-0 lg:w-11 lg:h-11 lg:mx-auto' : ''"
    @endif
>
    @if($active)
        <span class="absolute left-1.5 top-1/2 -translate-y-1/2 w-0.5 h-3.5 rounded-full bg-orange shadow-[0_0_6px_rgba(227,139,44,0.75)]"
              @if(!$sub) :class="sidebarCollapsed ? 'lg:hidden' : ''" @endif></span>
    @endif

    @if(isset($icon))
        <span class="flex-shrink-0 flex items-center justify-center transition-colors {{ $active ? 'text-orange' : 'text-white/85 group-hover:text-white' }}">
            {{ $icon }}
        </span>
    @endif

    <span class="truncate transition-transform duration-150 {{ $sub ? 'group-hover:translate-x-0.5' : '' }}"
          @if(!$sub) :class="sidebarCollapsed ? 'lg:hidden' : 'group-hover:translate-x-0.5'" @endif>
        {{ $label }}
    </span>
</a>
