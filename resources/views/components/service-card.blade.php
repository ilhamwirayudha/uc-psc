@props(['icon', 'title', 'description'])

<div class="card-hover bg-white rounded-2xl p-6 border border-[#EDE1FA] flex items-start gap-4">
    {{-- Icon --}}
    <div class="w-11 h-11 rounded-full bg-lavender-mid flex items-center justify-center flex-shrink-0 text-orange">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            {!! $icon !!}
        </svg>
    </div>
    {{-- Teks --}}
    <div>
        <h3 class="font-display text-base text-purple-deep font-semibold mb-1">{{ $title }}</h3>
        <p class="text-sm text-[#6B5B85] leading-relaxed text-justify">{{ $description }}</p>
    </div>
</div>