@props(['items' => null])

@php
    if (empty($items)) {
        $routeName = \Illuminate\Support\Facades\Route::currentRouteName();

        $routeMap = [
            'dashboard' => [],
            'clients.index' => [
                ['label' => 'Klien & Psikolog'],
                ['label' => 'Daftar Klien'],
            ],
            'clients.create' => [
                ['label' => 'Klien & Psikolog'],
                ['label' => 'Daftar Klien', 'url' => route('clients.index')],
                ['label' => 'Tambah Klien'],
            ],
            'clients.show' => [
                ['label' => 'Klien & Psikolog'],
                ['label' => 'Daftar Klien', 'url' => route('clients.index')],
                ['label' => 'Detail Klien'],
            ],
            'clients.edit' => [
                ['label' => 'Klien & Psikolog'],
                ['label' => 'Daftar Klien', 'url' => route('clients.index')],
                ['label' => 'Edit Klien'],
            ],
            'client-forms.index' => [
                ['label' => 'Klien & Psikolog'],
                ['label' => 'Formulir Klien'],
            ],
            'client-forms.results' => [
                ['label' => 'Klien & Psikolog'],
                ['label' => 'Formulir Klien', 'url' => route('client-forms.index')],
                ['label' => 'Hasil Respon Formulir'],
            ],
            'client-forms.show' => [
                ['label' => 'Klien & Psikolog'],
                ['label' => 'Formulir Klien', 'url' => route('client-forms.index')],
                ['label' => 'Detail Formulir'],
            ],
            'counselors.index' => [
                ['label' => 'Klien & Psikolog'],
                ['label' => 'Psikolog'],
            ],
            'counselors.create' => [
                ['label' => 'Klien & Psikolog'],
                ['label' => 'Psikolog', 'url' => route('counselors.index')],
                ['label' => 'Tambah Psikolog'],
            ],
            'counselors.edit' => [
                ['label' => 'Klien & Psikolog'],
                ['label' => 'Psikolog', 'url' => route('counselors.index')],
                ['label' => 'Edit Psikolog'],
            ],
            'calendar.index' => [
                ['label' => 'Layanan & Jadwal'],
                ['label' => 'Kalender'],
            ],
            'bookings.index' => [
                ['label' => 'Layanan & Jadwal'],
                ['label' => 'Layanan'],
            ],
            'bookings.counseling' => [
                ['label' => 'Layanan & Jadwal'],
                ['label' => 'Layanan Konseling'],
            ],
            'bookings.psychotest' => [
                ['label' => 'Layanan & Jadwal'],
                ['label' => 'Layanan Psikotes'],
            ],
            'bookings.create' => [
                ['label' => 'Layanan & Jadwal'],
                ['label' => 'Layanan Konseling', 'url' => route('bookings.counseling')],
                ['label' => 'Tambah Layanan'],
            ],
            'bookings.show' => [
                ['label' => 'Layanan & Jadwal'],
                ['label' => 'Layanan Konseling', 'url' => route('bookings.counseling')],
                ['label' => 'Detail Layanan'],
            ],
            'bookings.edit' => [
                ['label' => 'Layanan & Jadwal'],
                ['label' => 'Layanan Konseling', 'url' => route('bookings.counseling')],
                ['label' => 'Edit Layanan'],
            ],
            'assignments.index' => [
                ['label' => 'Administrasi'],
                ['label' => 'Kelola Penugasan'],
            ],
            'staff-management.index' => [
                ['label' => 'Administrasi'],
                ['label' => 'Kelola Staff'],
            ],
            'staff-management.create' => [
                ['label' => 'Administrasi'],
                ['label' => 'Kelola Staff', 'url' => route('staff-management.index')],
                ['label' => 'Tambah Staff'],
            ],
            'staff-management.show' => [
                ['label' => 'Administrasi'],
                ['label' => 'Kelola Staff', 'url' => route('staff-management.index')],
                ['label' => 'Detail Staff'],
            ],
            'profile.edit' => [
                ['label' => 'Akun'],
                ['label' => 'Profil Saya'],
            ],
        ];

        $resolvedItems = $routeMap[$routeName] ?? [
            ['label' => 'UC PSC', 'url' => route('dashboard')],
            ['label' => 'Halaman'],
        ];
    } else {
        $resolvedItems = $items;
    }
@endphp

@if(count($resolvedItems) > 0)
<nav aria-label="Breadcrumb" class="hidden sm:flex items-center gap-1.5 text-[13px] font-medium text-purple-deep/55 mb-1.5 flex-wrap">
    @foreach($resolvedItems as $item)
        @php
            $isFirst = $loop->first;
            $isLast = $loop->last;
            $url = $item['url'] ?? null;
        @endphp

        @if(!$isFirst)
            <span class="text-purple-deep/30 select-none" aria-hidden="true">/</span>
        @endif

        @if($isLast)
            <span aria-current="page" class="text-purple-deep/85 font-semibold truncate max-w-[200px] md:max-w-[280px]">
                {{ $item['label'] }}
            </span>
        @elseif(!empty($url))
            <a href="{{ $url }}" class="hover:text-orange transition-colors truncate max-w-[160px] md:max-w-[220px]">
                {{ $item['label'] }}
            </a>
        @else
            <span class="text-purple-deep/55 truncate max-w-[160px] md:max-w-[220px]">
                {{ $item['label'] }}
            </span>
        @endif
    @endforeach
</nav>
@endif
