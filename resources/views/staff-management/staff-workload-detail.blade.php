@extends('layouts.dashboard')

@section('title', 'Detail Akun — ' . $staff->name . ' — UC PSC')
@section('page-title', 'Detail Beban Tugas Tim')
@section('breadcrumbs')
    <x-ui.breadcrumbs :items="[
        ['label' => 'Administrasi'],
        ['label' => 'Kelola Akun Tim', 'url' => route('staff-management.index')],
        ['label' => $staff->name],
    ]" />
@endsection

@section('content')
<div class="space-y-6">
    {{-- Kartu Identitas --}}
    <div class="glass-card rounded-2xl p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <span class="text-xs font-medium text-slate-500 mb-1 block">Nama Lengkap</span>
                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                    {{ $staff->name }}
                </div>
            </div>
            <div>
                <span class="text-xs font-medium text-slate-500 mb-1 block">Email</span>
                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                    {{ $staff->email }}
                </div>
            </div>
            <div>
                <span class="text-xs font-medium text-slate-500 mb-1 block">Peran Akun</span>
                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                    {{ $staff->isPsikolog() ? 'Psikolog' : 'Staff' }}
                </div>
            </div>
            <div>
                <span class="text-xs font-medium text-slate-500 mb-1 block">Google Calendar Pribadi</span>
                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                    {{ $staff->hasGoogleCalendarConnected() ? 'Terhubung (' . ($staff->google_email ?: $staff->email) . ')' : 'Belum Terhubung' }}
                </div>
            </div>
        </div>
    </div>

    @if($staff->isPsikolog())
        {{-- Daftar Tugas Psikotes Psikolog --}}
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="px-6 py-4 bg-purple-deep/95 text-white flex items-center justify-between">
                <h3 class="text-base font-bold text-white">Kandidat Psikotes yang Ditangani</h3>
                <a href="{{ route('assignments.index', ['tab' => 'psikolog']) }}" class="text-xs text-purple-100 hover:text-white underline">Kelola Penugasan</a>
            </div>
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-sm font-medium">
                    <thead>
                        <tr class="bg-slate-50 text-black font-semibold border-b border-[#EDE1FA]">
                            <th class="px-6 py-3.5 text-left font-semibold">Tanggal Tes</th>
                            <th class="px-6 py-3.5 text-left font-semibold">Perusahaan / Klien</th>
                            <th class="px-6 py-3.5 text-left font-semibold">Kandidat</th>
                            <th class="px-6 py-3.5 text-left font-semibold">Prioritas</th>
                            <th class="px-6 py-3.5 text-left font-semibold">Deadline Laporan</th>
                            <th class="px-6 py-3.5 text-left font-semibold">Status Draft HPP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F3EAFB]">
                        @forelse($psychotestCandidates ?? [] as $cand)
                            <tr class="table-row-clickable hover:bg-slate-100 transition-colors duration-150 cursor-pointer" onclick="window.location='{{ route('psychotest-bookings.show', $cand->booking_id) }}'">
                                <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $cand->booking?->tanggal_dijadwalkan ? $cand->booking->tanggal_dijadwalkan->format('d/m/Y') : '-' }}</td>
                                <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $cand->booking?->company?->name ?? $cand->booking?->client?->name ?? '-' }}</td>
                                <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $cand->candidate_name }}</td>
                                <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $cand->is_kilat ? 'KILAT' : 'Reguler' }}</td>
                                <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $cand->psychologist_deadline_date ? $cand->psychologist_deadline_date->format('d/m/Y') : '-' }}</td>
                                <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $cand->step_draft_hpp_at ? 'Selesai' : ($cand->isAbsent() ? 'Absen' : 'Menunggu') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-8 text-center text-slate-400 text-xs font-medium">Belum ada kandidat psikotes.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Daftar Sesi Konseling Psikolog --}}
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="px-6 py-4 bg-purple-deep/95 text-white">
                <h3 class="text-base font-bold text-white">Jadwal Layanan Konseling</h3>
            </div>
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-sm font-medium">
                    <thead>
                        <tr class="bg-slate-50 text-black font-semibold border-b border-[#EDE1FA]">
                            <th class="px-6 py-3.5 text-left font-semibold">Tanggal</th>
                            <th class="px-6 py-3.5 text-left font-semibold">Nama Klien</th>
                            <th class="px-6 py-3.5 text-left font-semibold">Media</th>
                            <th class="px-6 py-3.5 text-left font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F3EAFB]">
                        @forelse($counselingTasks ?? [] as $booking)
                            <tr class="table-row-clickable hover:bg-slate-100 transition-colors duration-150 cursor-pointer" onclick="window.location='{{ route('bookings.show', $booking) }}'">
                                <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $booking->tanggal_dijadwalkan ? $booking->tanggal_dijadwalkan->format('d/m/Y') : '-' }}</td>
                                <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $booking->client?->name ?? '-' }}</td>
                                <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $booking->session_type === 'online' ? 'Online' : 'Offline' }}</td>
                                <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-8 text-center text-slate-400 text-xs font-medium">Belum ada sesi konseling.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        {{-- Daftar Tugas Staff (Tester/Skorer/Editor) --}}
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="px-6 py-4 bg-purple-deep/95 text-white flex items-center justify-between">
                <h3 class="text-base font-bold text-white">Penugasan Batch (Tester &amp; Skorer)</h3>
                <a href="{{ route('assignments.index', ['tab' => 'staff']) }}" class="text-xs text-purple-100 hover:text-white underline">Kelola Penugasan</a>
            </div>
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-sm font-medium">
                    <thead>
                        <tr class="bg-slate-50 text-black font-semibold border-b border-[#EDE1FA]">
                            <th class="px-6 py-3.5 text-left font-semibold">Tanggal Tes</th>
                            <th class="px-6 py-3.5 text-left font-semibold">Perusahaan / Klien</th>
                            <th class="px-6 py-3.5 text-left font-semibold">Peran</th>
                            <th class="px-6 py-3.5 text-left font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F3EAFB]">
                        @forelse($batchAssignments ?? [] as $assign)
                            <tr class="table-row-clickable hover:bg-slate-100 transition-colors duration-150 cursor-pointer" onclick="window.location='{{ route('psychotest-bookings.show', $assign->booking_id) }}'">
                                <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $assign->booking?->tanggal_dijadwalkan ? $assign->booking->tanggal_dijadwalkan->format('d/m/Y') : '-' }}</td>
                                <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $assign->booking?->company?->name ?? $assign->booking?->client?->name ?? '-' }}</td>
                                <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $assign->role_label }}</td>
                                <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $assign->batch_completed_at ? 'Selesai' : 'Aktif' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-8 text-center text-slate-400 text-xs font-medium">Belum ada penugasan batch.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="px-6 py-4 bg-purple-deep/95 text-white">
                <h3 class="text-base font-bold text-white">Penugasan Editor (Per Kandidat)</h3>
            </div>
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-sm font-medium">
                    <thead>
                        <tr class="bg-slate-50 text-black font-semibold border-b border-[#EDE1FA]">
                            <th class="px-6 py-3.5 text-left font-semibold">Tanggal Tes</th>
                            <th class="px-6 py-3.5 text-left font-semibold">Perusahaan / Klien</th>
                            <th class="px-6 py-3.5 text-left font-semibold">Kandidat</th>
                            <th class="px-6 py-3.5 text-left font-semibold">Status Editing</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F3EAFB]">
                        @forelse($editorCandidates ?? [] as $cand)
                            <tr class="table-row-clickable hover:bg-slate-100 transition-colors duration-150 cursor-pointer" onclick="window.location='{{ route('psychotest-bookings.show', $cand->booking_id) }}'">
                                <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $cand->booking?->tanggal_dijadwalkan ? $cand->booking->tanggal_dijadwalkan->format('d/m/Y') : '-' }}</td>
                                <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $cand->booking?->company?->name ?? $cand->booking?->client?->name ?? '-' }}</td>
                                <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $cand->candidate_name }}</td>
                                <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $cand->step_editing_at ? 'Selesai' : 'Menunggu' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-8 text-center text-slate-400 text-xs font-medium">Belum ada tugas editor.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
