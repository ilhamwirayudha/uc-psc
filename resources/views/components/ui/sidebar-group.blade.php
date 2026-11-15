@props(['label'])

<div {{ $attributes->merge(['class' => 'pt-4 first:pt-1']) }}>
    <p class="px-3 mb-1.5 text-[11px] font-semibold uppercase tracking-wider text-white/50 select-none"
       :class="sidebarCollapsed ? 'lg:hidden' : ''">
        {{ $label }}
    </p>
    <div class="hidden my-2 mx-2.5 border-t border-white/15"
         :class="sidebarCollapsed ? 'lg:block' : 'lg:hidden'"></div>
    <div class="space-y-1">
        {{ $slot }}
    </div>
</div>
