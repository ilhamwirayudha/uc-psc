@extends('layouts.dashboard')

@section('title', 'Booking Layanan Psikotes — UC PSC')
@section('page-title', 'Booking Layanan Psikotes')
@section('page-subtitle', 'Kelola jadwal asesmen dan reservasi klien psikotes')

@section('content')
@include('bookings.partials.build-booking-data')

<div 
    x-data="bookingManager()"
    x-cloak
    class="space-y-6"
>
    {{-- Header & Search Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        {{-- Search Bar --}}
        <form method="GET" action="{{ route('bookings.psychotest') }}" class="w-full sm:w-80 md:w-96 relative">
            <div class="relative w-full">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama klien psikotes..."
                    class="w-full pl-11 pr-10 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-black placeholder:text-gray-400 font-medium shadow-xs">
                <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </div>
                @if(request('search'))
                    <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 transition cursor-pointer" title="Hapus pencarian">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </a>
                @endif
            </div>
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
        </form>

        {{-- Tombol Booking Psikotes Baru --}}
        <button type="button" @click="$dispatch('open-booking-modal', { category: 'psikotes' })" 
            class="px-4.5 py-2.5 bg-orange text-white text-sm font-semibold rounded-xl hover:bg-orange/90 transition shadow-xs flex-shrink-0 text-center cursor-pointer flex items-center justify-center gap-2">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Booking Psikotes Baru</span>
        </button>
    </div>

    {{-- Tag Filter Aktif (Jika ada filter atau pencarian) --}}
    @if(request()->hasAny(['search', 'status']))
    <div class="flex items-center gap-2 flex-wrap text-xs pt-0.5">
        <span class="text-gray-500 font-medium">Filter aktif:</span>
        @if(request('search'))
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-purple-50 text-purple-deep border border-purple-200 font-semibold">
                <span>Cari: "{{ request('search') }}"</span>
                <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="hover:text-red-500 text-sm font-bold leading-none">&times;</a>
            </span>
        @endif
        @if(request('status'))
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-purple-50 text-purple-deep border border-purple-200 font-semibold">
                <span>Status: {{ ucfirst(request('status')) }}</span>
                <a href="{{ request()->fullUrlWithQuery(['status' => null]) }}" class="hover:text-red-500 text-sm font-bold leading-none">&times;</a>
            </span>
        @endif
        <a href="{{ route('bookings.psychotest') }}" class="text-purple-deep hover:underline font-semibold ml-1">Reset Semua</a>
    </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- Helper fungsi 3-state sort: unselected -> asc -> desc -> unselected (default) --}}
    @php
        $getNextPsychotestSortUrl = function($column) use ($pSort, $pDir) {
            if ($pSort !== $column) {
                return request()->fullUrlWithQuery(['p_sort' => $column, 'p_dir' => 'asc']);
            }
            if ($pDir === 'asc') {
                return request()->fullUrlWithQuery(['p_sort' => $column, 'p_dir' => 'desc']);
            }
            return request()->fullUrlWithQuery(['p_sort' => null, 'p_dir' => null]);
        };
    @endphp

    {{-- TABEL: LAYANAN PSIKOTES                                                   --}}
    {{-- ========================================================================= --}}
    <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden flex flex-col justify-between">
        {{-- Header Card (Warna Ungu) --}}
        <div class="px-6 py-4 bg-purple-deep text-white flex items-center justify-between border-b border-purple-900/40">
            <h3 class="text-base font-bold text-white tracking-tight">Layanan Psikotes</h3>
            <span class="text-xs text-purple-100 font-medium bg-white/15 px-2.5 py-1 rounded-full">{{ $psikotesBookings->total() }} Data</span>
        </div>

        {{-- Table Layanan Psikotes --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm font-medium">
                <thead>
                    <tr class="bg-slate-50 text-black font-semibold select-none border-b border-[#EDE1FA]">
                        {{-- 1. ID --}}
                        <th class="px-6 py-3.5 text-left font-semibold">
                            <a href="{{ $getNextPsychotestSortUrl('id') }}"
                                class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer"
                                title="Urutkan ID angka: 1→9 atau 9→1">
                                <span>ID</span>
                                <span class="inline-flex items-center justify-center w-4 h-4 rounded transition {{ $pSort === 'id' ? 'text-purple-deep' : 'text-slate-400 group-hover:text-slate-600' }}">
                                    @if($pSort === 'id')
                                        @if($pDir === 'asc')
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                        @else
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                        @endif
                                    @else
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                    @endif
                                </span>
                            </a>
                        </th>

                        {{-- 2. Tanggal Psikotes --}}
                        <th class="px-6 py-3.5 text-left font-semibold">
                            <a href="{{ $getNextPsychotestSortUrl('tanggal') }}"
                                class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer"
                                title="Urutkan tanggal: Terlama atau Terkini">
                                <span>Tanggal Psikotes</span>
                                <span class="inline-flex items-center justify-center w-4 h-4 rounded transition {{ $pSort === 'tanggal' ? 'text-purple-deep' : 'text-slate-400 group-hover:text-slate-600' }}">
                                    @if($pSort === 'tanggal')
                                        @if($pDir === 'asc')
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                        @else
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                        @endif
                                    @else
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                    @endif
                                </span>
                            </a>
                        </th>

                        {{-- 3. Nama Klien --}}
                        <th class="px-6 py-3.5 text-left font-semibold">
                            <a href="{{ $getNextPsychotestSortUrl('klien') }}"
                                class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer"
                                title="Urutkan alfabetis: A→Z atau Z→A">
                                <span>Nama Klien</span>
                                <span class="inline-flex items-center justify-center w-4 h-4 rounded transition {{ $pSort === 'klien' ? 'text-purple-deep' : 'text-slate-400 group-hover:text-slate-600' }}">
                                    @if($pSort === 'klien')
                                        @if($pDir === 'asc')
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                        @else
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                        @endif
                                    @else
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                    @endif
                                </span>
                            </a>
                        </th>

                        {{-- 4. PIC --}}
                        <th class="px-6 py-3.5 text-left font-semibold">
                            <a href="{{ $getNextPsychotestSortUrl('pic') }}"
                                class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer"
                                title="Urutkan PIC: A→Z atau Z→A">
                                <span>PIC</span>
                                <span class="inline-flex items-center justify-center w-4 h-4 rounded transition {{ $pSort === 'pic' ? 'text-purple-deep' : 'text-slate-400 group-hover:text-slate-600' }}">
                                    @if($pSort === 'pic')
                                        @if($pDir === 'asc')
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                        @else
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                        @endif
                                    @else
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                    @endif
                                </span>
                            </a>
                        </th>

                        {{-- 5. Jenis Psikotes --}}
                        <th class="px-6 py-3.5 text-left font-semibold">
                            <a href="{{ $getNextPsychotestSortUrl('tipe') }}"
                                class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer"
                                title="Urutkan alfabetis: A→Z atau Z→A">
                                <span>Jenis Psikotes</span>
                                <span class="inline-flex items-center justify-center w-4 h-4 rounded transition {{ $pSort === 'tipe' ? 'text-purple-deep' : 'text-slate-400 group-hover:text-slate-600' }}">
                                    @if($pSort === 'tipe')
                                        @if($pDir === 'asc')
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                        @else
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                        @endif
                                    @else
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                    @endif
                                </span>
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F3EAFB]">
                    @forelse($psikotesBookings as $booking)
                        @php
                            $bookingData = buildBookingDataPayload($booking);
                            $jamSesi = $bookingData['jam_sesi'];
                            $client = $booking->client;
                            $isIndustri = $client && ($client->jenis === 'industri' || $client->jenis === 'company');
                            $cType = $client->service_type ?? '';
                            $participantList = $booking->participants->pluck('nama_peserta')->filter()->values();
                            if ($participantList->isEmpty() && $client && $client->clientParticipants->isNotEmpty()) {
                                $participantList = $client->clientParticipants->pluck('nama_peserta')->filter()->values();
                            }

                            // Format Lengkap Jenis Metode Format Level Kategori
                            $rawPackage = $client->counseling_type ?? '';
                            $isConfiguredPackage = !empty($rawPackage)
                                && str_contains($rawPackage, ' - ')
                                && (str_starts_with($rawPackage, 'Psikotes') || str_contains($rawPackage, 'Short') || str_contains($rawPackage, 'Long') || str_contains($rawPackage, 'Bakat Minat') || str_contains($rawPackage, 'Khusus'));

                            $jenisMetodeFormatLevelKategori = $isConfiguredPackage ? $rawPackage : null;
                        @endphp
                        <tr class="table-row-clickable hover:bg-slate-100 active:bg-slate-200 transition-colors duration-150 cursor-pointer" @click="openDetail({{ json_encode($bookingData) }})">
                            {{-- 1. ID --}}
                            <td class="px-6 py-3.5 text-black font-medium whitespace-nowrap">
                                {{ $booking->id }}
                            </td>

                            {{-- 2. Tanggal Psikotes --}}
                            <td class="px-6 py-3.5 text-black font-medium whitespace-nowrap">
                                @if($booking->tanggal_dijadwalkan)
                                    {{ $booking->tanggal_dijadwalkan->format('d/m/Y') }}{{ $jamSesi ? ' ' . $jamSesi : '' }}
                                @else
                                    <span class="text-gray-400 font-medium">-</span>
                                @endif
                            </td>

                            {{-- 3. Nama Klien --}}
                            <td class="px-6 py-3.5 text-black font-medium">
                                @if($isIndustri)
                                    <div x-data="{ openParticipants: false }" class="relative inline-block text-left" @click.outside="openParticipants = false">
                                        <button 
                                            type="button" 
                                            @click.stop="openParticipants = !openParticipants"
                                            class="font-medium text-black hover:text-purple-deep transition cursor-pointer inline-flex items-center gap-1.5 text-left"
                                            title="Klik untuk melihat daftar klien perusahaan terdaftar"
                                        >
                                            <span>{{ $client->name ?? 'Perusahaan' }}</span>
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 transition-transform" :class="{ 'rotate-180': openParticipants }"><polyline points="6 9 12 15 18 9"/></svg>
                                        </button>

                                        {{-- Dropdown List Klien Perusahaan --}}
                                        <div 
                                            x-show="openParticipants" 
                                            x-transition 
                                            class="absolute left-0 mt-2 w-64 bg-white rounded-xl shadow-lg border border-[#EDE1FA] p-3 z-50 text-xs font-normal"
                                            @click.stop
                                            x-cloak
                                        >
                                            <div class="font-medium text-black mb-2 pb-1.5 border-b border-gray-100 flex items-center justify-between">
                                                <span>Klien Terdaftar</span>
                                                <span class="text-gray-500 font-normal text-[11px]">{{ $participantList->count() }} orang</span>
                                            </div>
                                            @if($participantList->isNotEmpty())
                                                <ul class="max-h-48 overflow-y-auto space-y-1.5 text-black">
                                                    @foreach($participantList as $idx => $pName)
                                                        <li class="flex items-center gap-2">
                                                            <span class="text-gray-400 text-[10px] w-4">{{ $idx + 1 }}.</span>
                                                            <span class="truncate">{{ $pName }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <div class="text-gray-400 italic py-1">Belum ada klien terdaftar</div>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    {{ $client->name ?? '-' }}
                                @endif
                            </td>

                            {{-- 4. PIC --}}
                            <td class="px-6 py-3.5 text-black font-medium">
                                {{ $client->pic_name ?? '-' }}
                            </td>

                            {{-- 5. Jenis Psikotes (Jenis Metode Format Level Kategori) --}}
                            <td class="px-6 py-3.5 text-black font-medium">
                                @if($booking->service_package_name)
                                    {{ $booking->service_package_name }}
                                @elseif($jenisMetodeFormatLevelKategori)
                                    {{ $jenisMetodeFormatLevelKategori }}
                                @else
                                    <span class="text-gray-400 font-medium">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-400 font-medium">Belum ada data booking layanan psikotes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($psikotesBookings->hasPages())
        <div class="px-6 py-4 border-t border-[#EDE1FA]">
            {{ $psikotesBookings->links() }}
        </div>
        @endif
    </div>

    @include('bookings.partials.detail-modal')
    @include('bookings.partials.confirm-modal')
</div>

@include('bookings.partials.script')
@endsection
