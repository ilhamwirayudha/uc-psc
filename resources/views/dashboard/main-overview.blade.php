@extends('layouts.dashboard')

@section('title', 'Dashboard — UC PSC')
@section('page-title', 'Dashboard Operasional')
@section('page-subtitle', 'Ringkasan prioritas harian, deadline laporan psikolog, antrean formulir, dan sinkronisasi kalender')

@section('content')
<div class="space-y-6">
    {{-- 4 Ringkasan Metrik Utama (Kartu abu-abu netral sesuai AGENTS.md) --}}
    <div class="glass-card rounded-2xl p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <span class="text-xs font-medium text-slate-500 mb-1 block">Antrean KILAT Aktif</span>
                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                    {{ $stats['kilat_active'] }} kandidat
                </div>
            </div>
            <div>
                <span class="text-xs font-medium text-slate-500 mb-1 block">Laporan Psikolog Terlambat</span>
                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                    {{ $stats['sla_overdue'] }} laporan
                </div>
            </div>
            <div>
                <span class="text-xs font-medium text-slate-500 mb-1 block">Formulir Belum Dibooking</span>
                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                    {{ $stats['unbooked_forms'] }} formulir
                </div>
            </div>
            <div>
                <span class="text-xs font-medium text-slate-500 mb-1 block">Jadwal GCal Belum Sinkron</span>
                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                    {{ $stats['gcal_pending'] }} jadwal
                </div>
            </div>
        </div>
    </div>

    {{-- Panel 1: Prioritas KILAT (Opsi A) --}}
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="px-6 py-4 bg-purple-deep/95 text-white flex items-center justify-between">
            <h3 class="text-base font-bold text-white">Prioritas KILAT</h3>
            <a href="{{ route('bookings.psychotest') }}" class="text-xs text-purple-100 hover:text-white underline">Lihat Semua Psikotes</a>
        </div>
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-sm font-medium">
                <thead>
                    <tr class="bg-slate-50 text-black font-semibold border-b border-[#EDE1FA]">
                        <th class="px-6 py-3 text-left font-semibold">Kandidat</th>
                        <th class="px-6 py-3 text-left font-semibold">Perusahaan / Klien</th>
                        <th class="px-6 py-3 text-left font-semibold">Psikolog</th>
                        <th class="px-6 py-3 text-left font-semibold">Deadline Klien</th>
                        <th class="px-6 py-3 text-right font-semibold">Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F3EAFB]">
                    @forelse($kilatCandidates as $kc)
                        <tr class="table-row-clickable hover:bg-slate-100 transition-colors duration-150 cursor-pointer" onclick="window.location='{{ route('psychotest-bookings.show', $kc->booking_id) }}'">
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $kc->candidate_name }}</td>
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $kc->booking?->company?->name ?? $kc->booking?->client?->name ?? '-' }}</td>
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $kc->assignedPsikolog?->name ?? '-' }}</td>
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $kc->client_deadline_date ? $kc->client_deadline_date->format('d/m/Y') : '-' }}</td>
                            <td class="px-6 py-3.5 text-right whitespace-nowrap" onclick="event.stopPropagation()">
                                <form method="POST" action="{{ route('psychotest-bookings.candidate.kilat-sent', [$kc->booking_id, $kc->id]) }}">
                                    @csrf
                                    <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-purple-deep hover:bg-purple-deep/90 text-white text-xs font-semibold shadow-xs cursor-pointer">
                                        Tandai Kilat Terkirim
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-slate-400 text-xs font-medium">Tidak ada kandidat KILAT yang menunggu pengiriman.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Panel 2: Pemantauan SLA Laporan Psikolog --}}
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="px-6 py-4 bg-purple-deep/95 text-white flex items-center justify-between">
                <h3 class="text-base font-bold text-white">Pemantauan SLA Laporan Psikolog</h3>
                <span class="text-xs text-purple-100 font-medium">1 klien hadir = 1 hari</span>
            </div>
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-sm font-medium">
                    <thead>
                        <tr class="bg-slate-50 text-black font-semibold border-b border-[#EDE1FA]">
                            <th class="px-5 py-3 text-left font-semibold">Kandidat</th>
                            <th class="px-5 py-3 text-left font-semibold">Psikolog</th>
                            <th class="px-5 py-3 text-left font-semibold">Deadline</th>
                            <th class="px-5 py-3 text-left font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F3EAFB]">
                        @forelse($slaCandidates as $sc)
                            <tr class="table-row-clickable hover:bg-slate-100 transition-colors duration-150 cursor-pointer" onclick="window.location='{{ route('psychotest-bookings.show', $sc->booking_id) }}'">
                                <td class="px-5 py-3 text-black text-xs font-medium whitespace-nowrap">{{ $sc->candidate_name }}{{ $sc->is_kilat ? ' [KILAT]' : '' }}</td>
                                <td class="px-5 py-3 text-black text-xs font-medium whitespace-nowrap">{{ $sc->assignedPsikolog?->name ?? '-' }}</td>
                                <td class="px-5 py-3 text-black text-xs font-medium whitespace-nowrap">{{ $sc->psychologist_deadline_date ? $sc->psychologist_deadline_date->format('d/m/Y') : '-' }}</td>
                                <td class="px-5 py-3 text-black text-xs font-medium whitespace-nowrap">{{ $sc->isPsychologistReportOverdue() ? 'Terlambat (Tagih)' : 'Berjalan' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-8 text-center text-slate-400 text-xs font-medium">Semua Draft HPP psikolog tepat waktu.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Panel 3: Antrean Formulir Psikotes Belum Dibooking --}}
        @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
            <div class="glass-card rounded-2xl overflow-hidden">
                <div class="px-6 py-4 bg-purple-deep/95 text-white flex items-center justify-between">
                    <h3 class="text-base font-bold text-white">Antrean Formulir Belum Dibooking</h3>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('psychotest-bookings.create') }}" class="text-xs text-purple-100 hover:text-white underline">Buat Booking Psikotes</a>
                    @endif
                </div>
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-sm font-medium">
                        <thead>
                            <tr class="bg-slate-50 text-black font-semibold border-b border-[#EDE1FA]">
                                <th class="px-5 py-3 text-left font-semibold">Nama Klien</th>
                                <th class="px-5 py-3 text-left font-semibold">Jenis Form</th>
                                <th class="px-5 py-3 text-left font-semibold">Perusahaan</th>
                                <th class="px-5 py-3 text-left font-semibold">Waktu Masuk</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F3EAFB]">
                            @forelse($unbookedForms as $uf)
                                <tr class="table-row-clickable hover:bg-slate-100 transition-colors duration-150 cursor-pointer" onclick="window.location='{{ auth()->user()->isAdmin() ? route('psychotest-bookings.create') : route('client-forms.results', ['category' => $uf->form_type]) }}'">
                                    <td class="px-5 py-3 text-black text-xs font-medium whitespace-nowrap">{{ $uf->client_name }}</td>
                                    <td class="px-5 py-3 text-black text-xs font-medium whitespace-nowrap">{{ $uf->form_type_label }}</td>
                                    <td class="px-5 py-3 text-black text-xs font-medium whitespace-nowrap">{{ $uf->company_name ?: ($uf->client?->company_name ?: '-') }}</td>
                                    <td class="px-5 py-3 text-black text-xs font-medium whitespace-nowrap">{{ $uf->created_at ? $uf->created_at->format('d/m/Y H:i') : '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-8 text-center text-slate-400 text-xs font-medium">Tidak ada formulir psikotes menunggu booking.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Panel 4: Pengingat Tagihan H-1 & Overtime Konseling --}}
        @if(auth()->user()->isAdmin() || auth()->user()->isPsikolog())
            <div class="glass-card rounded-2xl overflow-hidden">
                <div class="px-6 py-4 bg-purple-deep/95 text-white flex items-center justify-between">
                    <h3 class="text-base font-bold text-white">Pengingat Tagihan H-1 &amp; Overtime Konseling</h3>
                    <a href="{{ route('bookings.counseling') }}" class="text-xs text-purple-100 hover:text-white underline">Layanan Konseling</a>
                </div>
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-sm font-medium">
                        <thead>
                            <tr class="bg-slate-50 text-black font-semibold border-b border-[#EDE1FA]">
                                <th class="px-5 py-3 text-left font-semibold">Tanggal</th>
                                <th class="px-5 py-3 text-left font-semibold">Klien</th>
                                <th class="px-5 py-3 text-left font-semibold">Psikolog</th>
                                <th class="px-5 py-3 text-left font-semibold">Keterangan Tagihan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F3EAFB]">
                            @forelse($counselingReminders as $cr)
                                <tr class="table-row-clickable hover:bg-slate-100 transition-colors duration-150 cursor-pointer" onclick="window.location='{{ route('bookings.show', $cr) }}'">
                                    <td class="px-5 py-3 text-black text-xs font-medium whitespace-nowrap">{{ $cr->tanggal_dijadwalkan ? $cr->tanggal_dijadwalkan->format('d/m/Y') : '-' }}</td>
                                    <td class="px-5 py-3 text-black text-xs font-medium whitespace-nowrap">{{ $cr->client?->name ?? '-' }}</td>
                                    <td class="px-5 py-3 text-black text-xs font-medium whitespace-nowrap">{{ $cr->counselor?->name ?? '-' }}</td>
                                    <td class="px-5 py-3 text-black text-xs font-medium whitespace-nowrap">
                                        {{ $cr->is_overtime && $cr->overtime_payment_status !== 'lunas' ? 'Overtime Belum Lunas' : 'Tagih H-1 / Belum Lunas' }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-8 text-center text-slate-400 text-xs font-medium">Tidak ada pengingat pembayaran konseling mendesak.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Panel 5: Status Sinkronisasi Google Calendar --}}
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="px-6 py-4 bg-purple-deep/95 text-white flex items-center justify-between">
                <h3 class="text-base font-bold text-white">Status Sinkronisasi Google Calendar</h3>
                <a href="{{ route('profile.edit', ['tab' => 'pengaturan']) }}" class="text-xs text-purple-100 hover:text-white underline">Hubungkan OAuth Saya</a>
            </div>
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-sm font-medium">
                    <thead>
                        <tr class="bg-slate-50 text-black font-semibold border-b border-[#EDE1FA]">
                            <th class="px-5 py-3 text-left font-semibold">Tanggal</th>
                            <th class="px-5 py-3 text-left font-semibold">Layanan</th>
                            <th class="px-5 py-3 text-left font-semibold">Klien / Perusahaan</th>
                            <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F3EAFB]">
                        @forelse($gcalPendingBookings as $gb)
                            <tr class="table-row-clickable hover:bg-slate-100 transition-colors duration-150 cursor-pointer" onclick="window.location='{{ $gb->kategori === 'psikotes' ? route('psychotest-bookings.show', $gb) : route('bookings.show', $gb) }}'">
                                <td class="px-5 py-3 text-black text-xs font-medium whitespace-nowrap">{{ $gb->tanggal_dijadwalkan ? $gb->tanggal_dijadwalkan->format('d/m/Y') : '-' }}</td>
                                <td class="px-5 py-3 text-black text-xs font-medium whitespace-nowrap">{{ ucfirst($gb->kategori) }}</td>
                                <td class="px-5 py-3 text-black text-xs font-medium whitespace-nowrap">{{ $gb->company?->name ?? $gb->client?->name ?? '-' }}</td>
                                <td class="px-5 py-3 text-right whitespace-nowrap" onclick="event.stopPropagation()">
                                    <form method="POST" action="{{ route('bookings.sync-gcal', $gb) }}">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 rounded-lg bg-white border border-[#D9C2F0] text-purple-deep hover:bg-purple-50 text-xs font-semibold cursor-pointer">
                                            Sync ke GCal
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-8 text-center text-slate-400 text-xs font-medium">Semua jadwal terjadwal sudah tersinkron ke Google Calendar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
