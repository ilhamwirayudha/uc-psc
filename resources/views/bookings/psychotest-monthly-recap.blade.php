@extends('layouts.dashboard')

@section('title', 'Rekap Bulanan Invoice (I / R) — UC PSC')
@section('page-title', 'Rekap Tagihan Akhir Bulan (I / R)')
@section('page-subtitle', 'Daftar layanan psikotes skema Rekap Internal UCS (I) dan Rekap Perusahaan Tertentu (R) untuk penagihan akhir bulan')

@section('content')
@php
    $totalBase = $recapBookings->sum(fn ($b) => (float) ($b->price ?? 0));
    $totalFinal = $recapBookings->sum(fn ($b) => (float) ($b->final_price ?? $b->price ?? 0));
@endphp

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <form method="GET" action="{{ route('psychotest-bookings.monthly-recap') }}" class="flex flex-wrap items-center gap-3">
            <div>
                <input type="month" name="month" value="{{ $month }}" class="px-3.5 py-2.5 rounded-lg glass-input text-sm font-medium text-black">
            </div>
            <div>
                <select name="scheme" class="px-3.5 py-2.5 rounded-lg glass-input text-sm font-medium text-black">
                    <option value="">Semua Skema (I &amp; R)</option>
                    <option value="I" {{ $scheme === 'I' ? 'selected' : '' }}>Rekap Internal UCS (I)</option>
                    <option value="R" {{ $scheme === 'R' ? 'selected' : '' }}>Rekap Perusahaan Tertentu (R)</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2.5 rounded-lg bg-purple-deep hover:bg-purple-deep/90 text-white text-sm font-semibold shadow-xs cursor-pointer">
                Tampilkan Rekap
            </button>
        </form>

        <a href="{{ route('bookings.psychotest') }}" class="px-4 py-2.5 rounded-lg bg-white border border-[#D9C2F0] text-black hover:text-purple-deep text-sm font-semibold shadow-xs">
            ← Kembali ke Layanan Psikotes
        </a>
    </div>

    {{-- Ringkasan Total --}}
    <div class="glass-card rounded-2xl p-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <span class="text-xs font-medium text-slate-500 mb-1 block">Periode Rekap</span>
            <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                {{ $start->locale('id')->isoFormat('MMMM Y') }} ({{ $recapBookings->count() }} Order)
            </div>
        </div>
        <div>
            <span class="text-xs font-medium text-slate-500 mb-1 block">Total Harga Dasar (Sebelum Pajak)</span>
            <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                Rp {{ number_format($totalBase, 0, ',', '.') }}
            </div>
        </div>
        <div>
            <span class="text-xs font-medium text-slate-500 mb-1 block">Estimasi Total Tagihan Final</span>
            <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                Rp {{ number_format($totalFinal, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="px-6 py-4 bg-purple-deep/95 text-white flex items-center justify-between">
            <h3 class="text-base font-bold text-white">Rincian Order Rekap Bulanan</h3>
            <span class="text-xs text-purple-100 font-medium">{{ $recapBookings->count() }} Booking</span>
        </div>
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-sm font-medium">
                <thead>
                    <tr class="bg-slate-50 text-black font-semibold border-b border-[#EDE1FA]">
                        <th class="px-6 py-3.5 text-left font-semibold">Tanggal Tes</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Kode</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Perusahaan / Klien</th>
                        <th class="px-6 py-3.5 text-left font-semibold">PIC</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Paket &amp; Kandidat</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Harga Dasar</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Nominal Final</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Status Bayar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F3EAFB]">
                    @forelse($recapBookings as $b)
                        <tr class="table-row-clickable hover:bg-slate-100 active:bg-slate-200 transition-colors duration-150 cursor-pointer" onclick="window.location='{{ route('psychotest-bookings.show', $b) }}'">
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $b->tanggal_dijadwalkan ? $b->tanggal_dijadwalkan->format('d/m/Y') : '-' }}</td>
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $b->billing_scheme }}</td>
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $b->company?->name ?? $b->client?->name ?? '-' }}</td>
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $b->pic_name ?? '-' }}</td>
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $b->service_package_name ?: '-' }} ({{ $b->candidates->count() }} org)</td>
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">Rp {{ number_format((float) ($b->price ?? 0), 0, ',', '.') }}</td>
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $b->final_price ? 'Rp ' . number_format((float) $b->final_price, 0, ',', '.') : '-' }}</td>
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">
                                {{ $b->billing_scheme === '-' ? 'Gratis / Deal Khusus' : match($b->payment_workflow_status) {
                                    'lunas' => 'Lunas',
                                    'lunas_overtime' => 'Lunas (Overtime)',
                                    'draft_ke_finance' => 'Pengajuan Nota (Finance)',
                                    'menunggu_bayar' => 'Menunggu VA',
                                    default => ($b->isPaid() ? 'Lunas' : 'Belum Ada Tagihan'),
                                } }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-slate-400 text-xs font-medium">Tidak ada order berkode I atau R pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
