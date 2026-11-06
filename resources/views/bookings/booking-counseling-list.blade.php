@extends('layouts.dashboard')

@section('title', 'Booking Layanan Konseling — UC PSC')
@section('page-title', 'Booking Layanan Konseling')
@section('page-subtitle', 'Kelola jadwal sesi dan reservasi klien konseling')

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
        <form method="GET" action="{{ route('bookings.counseling') }}" class="w-full sm:w-80 md:w-96 relative">
            <div class="relative w-full">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama klien konseling..."
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

        {{-- Tombol Booking Konseling Baru --}}
        <button type="button" @click="$dispatch('open-booking-modal', { category: 'konseling' })" 
            class="px-4.5 py-2.5 bg-orange text-white text-sm font-semibold rounded-xl hover:bg-orange/90 transition shadow-xs flex-shrink-0 text-center cursor-pointer flex items-center justify-center gap-2">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Booking Konseling Baru</span>
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
        <a href="{{ route('bookings.counseling') }}" class="text-purple-deep hover:underline font-semibold ml-1">Reset Semua</a>
    </div>
    @endif

    {{-- Helper fungsi 3-state sort: unselected -> asc -> desc -> unselected (default) --}}
    @php
        $getNextCounselingSortUrl = function($column) use ($cSort, $cDir) {
            if ($cSort !== $column) {
                return request()->fullUrlWithQuery(['c_sort' => $column, 'c_dir' => 'asc']);
            }
            if ($cDir === 'asc') {
                return request()->fullUrlWithQuery(['c_sort' => $column, 'c_dir' => 'desc']);
            }
            return request()->fullUrlWithQuery(['c_sort' => null, 'c_dir' => null]);
        };
    @endphp

    {{-- ========================================================================= --}}
    {{-- TABEL: LAYANAN KONSELING                                                  --}}
    {{-- ========================================================================= --}}
    <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden flex flex-col justify-between">
        {{-- Header Card (Warna Ungu) --}}
        <div class="px-6 py-4 bg-purple-deep text-white flex items-center justify-between border-b border-purple-900/40">
            <h3 class="text-base font-bold text-white tracking-tight">Layanan Konseling</h3>
            <span class="text-xs text-purple-100 font-medium bg-white/15 px-2.5 py-1 rounded-full">{{ $counselingBookings->total() }} Data</span>
        </div>

        {{-- Table Layanan Konseling --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm font-medium">
                <thead>
                    <tr class="bg-slate-50 text-black font-semibold select-none border-b border-[#EDE1FA]">
                        {{-- 1. ID --}}
                        <th class="px-6 py-3.5 text-left font-semibold">
                            <a href="{{ $getNextCounselingSortUrl('id') }}"
                                class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer"
                                title="Urutkan ID angka: 1→9 atau 9→1">
                                <span>ID</span>
                                <span class="inline-flex items-center justify-center w-4 h-4 rounded transition {{ $cSort === 'id' ? 'text-purple-deep' : 'text-slate-400 group-hover:text-slate-600' }}">
                                    @if($cSort === 'id')
                                        @if($cDir === 'asc')
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

                        {{-- 2. Tanggal Konseling --}}
                        <th class="px-6 py-3.5 text-left font-semibold">
                            <a href="{{ $getNextCounselingSortUrl('tanggal') }}"
                                class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer"
                                title="Urutkan tanggal: Terlama atau Terkini">
                                <span>Tanggal Konseling</span>
                                <span class="inline-flex items-center justify-center w-4 h-4 rounded transition {{ $cSort === 'tanggal' ? 'text-purple-deep' : 'text-slate-400 group-hover:text-slate-600' }}">
                                    @if($cSort === 'tanggal')
                                        @if($cDir === 'asc')
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
                            <a href="{{ $getNextCounselingSortUrl('klien') }}"
                                class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer"
                                title="Urutkan alfabetis: A→Z atau Z→A">
                                <span>Nama Klien</span>
                                <span class="inline-flex items-center justify-center w-4 h-4 rounded transition {{ $cSort === 'klien' ? 'text-purple-deep' : 'text-slate-400 group-hover:text-slate-600' }}">
                                    @if($cSort === 'klien')
                                        @if($cDir === 'asc')
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

                        {{-- 4. Kewarganegaraan --}}
                        <th class="px-6 py-3.5 text-left font-semibold">
                            <a href="{{ $getNextCounselingSortUrl('kewarganegaraan') }}"
                                class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer"
                                title="Urutkan alfabetis: A→Z atau Z→A">
                                <span>Kewarganegaraan</span>
                                <span class="inline-flex items-center justify-center w-4 h-4 rounded transition {{ $cSort === 'kewarganegaraan' ? 'text-purple-deep' : 'text-slate-400 group-hover:text-slate-600' }}">
                                    @if($cSort === 'kewarganegaraan')
                                        @if($cDir === 'asc')
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

                        {{-- 5. Psikolog --}}
                        <th class="px-6 py-3.5 text-left font-semibold">
                            <a href="{{ $getNextCounselingSortUrl('psikolog') }}"
                                class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer"
                                title="Urutkan nama psikolog: A→Z atau Z→A">
                                <span>Psikolog</span>
                                <span class="inline-flex items-center justify-center w-4 h-4 rounded transition {{ $cSort === 'psikolog' ? 'text-purple-deep' : 'text-slate-400 group-hover:text-slate-600' }}">
                                    @if($cSort === 'psikolog')
                                        @if($cDir === 'asc')
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

                        {{-- 6. Tipe Konseling --}}
                        <th class="px-6 py-3.5 text-left font-semibold">
                            <a href="{{ $getNextCounselingSortUrl('tipe') }}"
                                class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer"
                                title="Urutkan alfabetis: A→Z atau Z→A">
                                <span>Tipe Konseling</span>
                                <span class="inline-flex items-center justify-center w-4 h-4 rounded transition {{ $cSort === 'tipe' ? 'text-purple-deep' : 'text-slate-400 group-hover:text-slate-600' }}">
                                    @if($cSort === 'tipe')
                                        @if($cDir === 'asc')
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
                    @forelse($counselingBookings as $booking)
                        @php
                            $bookingData = buildBookingDataPayload($booking);
                            $jamSesi = $bookingData['jam_sesi'];

                            // Deteksi WNA / WNI
                            $isWna = false;
                            $rawType = strtoupper(($booking->client->counseling_type ?? '') . ' ' . ($booking->client->notes ?? '') . ' ' . ($booking->notes ?? ''));
                            if (str_contains($rawType, 'WNA')) {
                                $isWna = true;
                            }

                            // Format Tipe Konseling (Hanya jika sudah dikonfigurasi/ditetapkan)
                            $rawPackage = $booking->client->counseling_type ?? '';
                            $isConfiguredPackage = !empty($rawPackage)
                                && !in_array(strtolower($rawPackage), ['offline', 'online'])
                                && (str_contains($rawPackage, ' - ') || str_contains($rawPackage, 'Reguler') || str_contains($rawPackage, 'Pro'));

                            $metodeKategoriText = null;
                            if ($isConfiguredPackage) {
                                $method = (stripos($rawPackage, 'Online') !== false) ? 'Online' : 'Offline';
                                $cleanCat = trim(str_ireplace(['Konseling', 'Offline', 'Online', 'WNI', 'WNA', '-'], ' ', $rawPackage));
                                $cleanCat = preg_replace('/\s+/', ' ', $cleanCat);
                                $metodeKategoriText = trim($method . ' - ' . ($cleanCat ?: 'Reguler Umum'), ' -');
                            }
                        @endphp
                        <tr class="table-row-clickable hover:bg-slate-100 active:bg-slate-200 transition-colors duration-150 cursor-pointer" @click="openDetail({{ json_encode($bookingData) }})">
                            {{-- 1. ID --}}
                            <td class="px-6 py-3.5 text-black font-medium whitespace-nowrap">
                                {{ $booking->id }}
                            </td>

                            {{-- 2. Tanggal Konseling --}}
                            <td class="px-6 py-3.5 text-black font-medium whitespace-nowrap">
                                @if($booking->tanggal_dijadwalkan)
                                    {{ $booking->tanggal_dijadwalkan->format('d/m/Y') }}{{ $jamSesi ? ' ' . $jamSesi : '' }}
                                @else
                                    <span class="text-gray-400 font-medium">-</span>
                                @endif
                            </td>

                            {{-- 3. Nama Klien --}}
                            <td class="px-6 py-3.5 text-black font-medium">
                                {{ $booking->client->name ?? '-' }}
                            </td>

                            {{-- 4. Kewarganegaraan --}}
                            <td class="px-6 py-3.5 text-black font-medium whitespace-nowrap">
                                {{ $isWna ? 'WNA' : 'WNI' }}
                            </td>

                            {{-- 5. Psikolog --}}
                            <td class="px-6 py-3.5 text-black font-medium">
                                {{ $booking->counselor ? $booking->counselor->name : '-' }}
                            </td>

                            {{-- 6. Tipe Konseling --}}
                            <td class="px-6 py-3.5 text-black font-medium">
                                @if($booking->service_package_name)
                                    {{ $booking->service_package_name }}
                                @elseif($metodeKategoriText)
                                    {{ $metodeKategoriText }}
                                @else
                                    <span class="text-gray-400 font-medium">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-400 font-medium">Belum ada data booking layanan konseling.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($counselingBookings->hasPages())
        <div class="px-6 py-4 border-t border-[#EDE1FA]">
            {{ $counselingBookings->links() }}
        </div>
        @endif
    </div>

    @include('bookings.partials.detail-modal')
    @include('bookings.partials.confirm-modal')
</div>

@include('bookings.partials.script')
@endsection
