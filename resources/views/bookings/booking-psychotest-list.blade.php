@extends('layouts.dashboard')

@section('title', 'Layanan Psikotes — UC PSC')
@section('page-title', 'Layanan Psikotes')
@section('page-subtitle', 'Kelola jadwal asesmen, pelacakan per kandidat, dan progres tim psikotes')

@section('content')
@php
    $getNextPsychotestSortUrl = function ($column) use ($pSort, $pDir) {
        if ($pSort !== $column) {
            return request()->fullUrlWithQuery(['p_sort' => $column, 'p_dir' => 'asc']);
        }
        if ($pDir === 'asc') {
            return request()->fullUrlWithQuery(['p_sort' => $column, 'p_dir' => 'desc']);
        }
        return request()->fullUrlWithQuery(['p_sort' => null, 'p_dir' => null]);
    };
@endphp

<div class="space-y-6">
    {{-- Header & Action Bar --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <form method="GET" action="{{ route('bookings.psychotest') }}" class="w-full lg:w-96 relative">
            <div class="relative w-full">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama klien atau perusahaan..."
                    class="w-full pl-11 pr-10 py-2.5 glass-input rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-black placeholder:text-gray-400 font-medium">
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
        </form>

        <div class="flex flex-wrap items-center gap-3">
            @if(auth()->user()->isAdmin())
                <a href="{{ route('psychotest-bookings.monthly-recap') }}"
                    class="px-4 py-2.5 bg-white border border-[#D9C2F0] text-black hover:text-purple-deep text-sm font-semibold rounded-lg shadow-xs transition inline-flex items-center gap-2">
                    <span>Rekap Bulanan (I/R)</span>
                </a>
                <a href="{{ route('psychotest-bookings.create') }}"
                    class="px-4.5 py-2.5 bg-orange hover:bg-orange/90 text-white text-sm font-semibold rounded-lg transition shadow-xs inline-flex items-center gap-2">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>Booking Psikotes Baru</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Info Antrean Formulir Belum Dibooking --}}
    @if((auth()->user()->isAdmin() || auth()->user()->isStaff()) && $unbookedFormsCount > 0)
        <div class="glass-card rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-black">
                    Terdapat {{ $unbookedFormsCount }} formulir pendaftaran Psikotes berstatus Belum Dibooking.
                </p>
                <p class="text-xs font-medium text-slate-500 mt-0.5">
                    @if(auth()->user()->isAdmin())
                        Pilih perusahaan dan centang kandidat pada form pembuatan booking psikotes untuk menggabungkannya ke dalam 1 jadwal.
                    @else
                        Formulir dapat ditinjau di menu Formulir Klien untuk membantu Admin mencocokkan daftar kandidat PIC.
                    @endif
                </p>
            </div>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('psychotest-bookings.create') }}" class="px-4 py-2 rounded-lg bg-purple-deep hover:bg-purple-deep/90 text-white text-xs font-semibold shadow-xs whitespace-nowrap">
                    Proses ke Booking Psikotes
                </a>
            @endif
        </div>
    @endif

    {{-- Panel Prioritas KILAT (Opsi A) --}}
    @if($kilatQueue->isNotEmpty())
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="px-6 py-4 bg-purple-deep/95 text-white flex items-center justify-between border-b border-purple-900/30">
                <h3 class="text-base font-bold text-white">Prioritas KILAT (Butuh Penanganan Cepat)</h3>
                <span class="text-xs text-purple-100 font-medium">{{ $kilatQueue->count() }} Kandidat Aktif</span>
            </div>
            <div class="p-4 sm:p-5 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach($kilatQueue as $kc)
                    @php
                        $deadlineKlien = $kc->client_deadline_date;
                        $daysLeft = $deadlineKlien ? now()->startOfDay()->diffInDays($deadlineKlien->startOfDay(), false) : null;
                        $countdownLabel = $daysLeft === null ? '-' : ($daysLeft < 0 ? 'Terlambat ' . abs($daysLeft) . ' hari' : ($daysLeft === 0 ? 'Jatuh Tempo Hari Ini' : 'H-' . $daysLeft));
                    @endphp
                    <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 space-y-3 flex flex-col justify-between">
                        <div class="space-y-1.5">
                            <div class="flex items-start justify-between gap-2">
                                <a href="{{ route('psychotest-bookings.show', $kc->booking_id) }}" class="text-sm font-medium text-black hover:text-purple-deep">
                                    {{ $kc->candidate_name }}
                                </a>
                                <span class="text-xs font-medium text-slate-500 whitespace-nowrap">{{ $countdownLabel }}</span>
                            </div>
                            <p class="text-xs font-medium text-slate-500">
                                Klien: <span class="text-black">{{ $kc->booking?->company?->name ?? $kc->booking?->client?->name ?? '-' }}</span>
                            </p>
                            <p class="text-xs font-medium text-slate-500">
                                Psikolog: <span class="text-black">{{ $kc->assignedPsikolog?->name ?? '-' }}</span>
                                · Deadline Klien: <span class="text-black">{{ $deadlineKlien ? $deadlineKlien->locale('id')->isoFormat('D MMMM Y') : '-' }}</span>
                            </p>
                        </div>

                        <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-200/70">
                            <a href="{{ route('psychotest-bookings.show', $kc->booking_id) }}" class="text-xs font-medium text-purple-deep hover:underline">
                                Detail Booking
                            </a>
                            @if(auth()->user()->isAdmin())
                                <form method="POST" action="{{ route('psychotest-bookings.candidate.kilat-sent', [$kc->booking_id, $kc->id]) }}">
                                    @csrf
                                    <button type="submit" class="px-3.5 py-2 rounded-lg bg-purple-deep hover:bg-purple-deep/90 text-white text-xs font-semibold shadow-xs cursor-pointer">
                                        Tandai Kilat Terkirim
                                    </button>
                                </form>
                            @else
                                <span class="text-xs font-medium text-slate-500">Menunggu Kirim Admin</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Tabel Layanan Psikotes --}}
    <div class="glass-card rounded-2xl overflow-hidden flex flex-col justify-between">
        <div class="px-6 py-4 bg-purple-deep/95 text-white flex items-center justify-between border-b border-purple-900/30">
            <h3 class="text-base font-bold text-white tracking-tight">Daftar Layanan Psikotes</h3>
            <span class="text-xs text-purple-100 font-medium">{{ $psikotesBookings->total() }} Data</span>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-sm font-medium">
                <thead>
                    <tr class="bg-slate-50 text-black font-semibold select-none border-b border-[#EDE1FA]">
                        <th class="px-6 py-3.5 text-left font-semibold">
                            <a href="{{ $getNextPsychotestSortUrl('id') }}" class="inline-flex items-center gap-1.5 hover:text-purple-deep cursor-pointer">
                                <span>ID</span>
                                <span class="text-xs {{ $pSort === 'id' ? 'text-purple-deep' : 'text-slate-400' }}">
                                    {{ $pSort === 'id' ? ($pDir === 'asc' ? '↑' : '↓') : '↕' }}
                                </span>
                            </a>
                        </th>
                        <th class="px-6 py-3.5 text-left font-semibold">
                            <a href="{{ $getNextPsychotestSortUrl('tanggal') }}" class="inline-flex items-center gap-1.5 hover:text-purple-deep cursor-pointer">
                                <span>Tanggal Tes</span>
                                <span class="text-xs {{ $pSort === 'tanggal' ? 'text-purple-deep' : 'text-slate-400' }}">
                                    {{ $pSort === 'tanggal' ? ($pDir === 'asc' ? '↑' : '↓') : '↕' }}
                                </span>
                            </a>
                        </th>
                        <th class="px-6 py-3.5 text-left font-semibold">
                            <a href="{{ $getNextPsychotestSortUrl('klien') }}" class="inline-flex items-center gap-1.5 hover:text-purple-deep cursor-pointer">
                                <span>Klien / Perusahaan</span>
                                <span class="text-xs {{ $pSort === 'klien' ? 'text-purple-deep' : 'text-slate-400' }}">
                                    {{ $pSort === 'klien' ? ($pDir === 'asc' ? '↑' : '↓') : '↕' }}
                                </span>
                            </a>
                        </th>
                        <th class="px-6 py-3.5 text-left font-semibold">PIC</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Jenis Paket</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Psikolog</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Invoice</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Progres Kandidat</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Bayar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F3EAFB]">
                    @forelse($psikotesBookings as $booking)
                        @php
                            $clientDisplay = $booking->company?->name ?? $booking->client?->name ?? '-';
                            $picDisplay = $booking->pic_name ?? $booking->client?->pic_name ?? '-';
                            $totalCand = $booking->candidates->count();
                            $doneCand = $booking->candidates->filter(fn ($c) => $c->isCompletedForDelivery())->count();
                            $progressText = $totalCand > 0 ? "{$doneCand}/{$totalCand} selesai" : ucfirst(str_replace('_', ' ', $booking->status));
                        @endphp
                        <tr class="table-row-clickable hover:bg-slate-100 active:bg-slate-200 transition-colors duration-150 cursor-pointer" onclick="window.location='{{ route('psychotest-bookings.show', $booking) }}'">
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $booking->id }}</td>
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">
                                {{ $booking->tanggal_dijadwalkan ? $booking->tanggal_dijadwalkan->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $clientDisplay }}</td>
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $picDisplay }}</td>
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $booking->service_package_name ?: '-' }}</td>
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $booking->formatPsychologistQuotaSummary() }}</td>
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $booking->billing_scheme ?: 'v' }}</td>
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $progressText }}</td>
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $booking->isPaid() ? 'Lunas' : 'Belum' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-10 text-center text-slate-400 text-xs font-medium">Belum ada data booking layanan psikotes.</td>
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
</div>
@endsection
