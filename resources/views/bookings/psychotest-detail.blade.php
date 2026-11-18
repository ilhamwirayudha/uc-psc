@extends('layouts.dashboard')

@section('title', 'Detail Psikotes #' . $booking->id . ' — UC PSC')
@section('page-title', 'Detail Layanan Psikotes #' . $booking->id)
@section('page-subtitle', ($booking->company?->name ?? $booking->client?->name ?? 'Klien Psikotes') . ' — Pelacakan progres per kandidat dan penugasan tim')

@section('content')
@inject('workflowService', 'App\Services\PsychotestWorkflowService')
@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin();
    $clientDisplay = $booking->company?->name ?? $booking->client?->name ?? '-';
    $picDisplay = $booking->pic_name ?? $booking->client?->pic_name ?? '-';
    $kilatCandidates = $booking->candidates->where('is_kilat', true)->where('attendance_status', '!=', 'absen');
    $batchAssignees = $booking->assignees->whereIn('role_type', ['tester', 'skorer_manual', 'skorer_software']);
    $selectedTesterIds = $booking->assignees->where('role_type', 'tester')->pluck('user_id')->filter()->values()->all();
    $selectedSkorerManualIds = $booking->assignees->where('role_type', 'skorer_manual')->pluck('user_id')->filter()->values()->all();
    $selectedSkorerSoftwareIds = $booking->assignees->where('role_type', 'skorer_software')->pluck('user_id')->filter()->values()->all();
@endphp

<div x-data="{
    editSchedule: false,
    editAssignees: false,
    editFinance: false,
    resiModalOpen: false,
    resiActionUrl: '',
    resiCandidateName: '',
    resiDate: '{{ now()->toDateString() }}',
    resiNumber: '',
    unsavedModalOpen: false,
    pendingCancelSection: null,
    openResiModal(url, name, existingDate, existingNum) {
        this.resiActionUrl = url;
        this.resiCandidateName = name;
        this.resiDate = existingDate || '{{ now()->toDateString() }}';
        this.resiNumber = existingNum || '';
        this.resiModalOpen = true;
    },
    confirmCancelSection(sec) {
        this.pendingCancelSection = sec;
        this.unsavedModalOpen = true;
    },
    discardSectionEdit() {
        if (this.pendingCancelSection === 'schedule') this.editSchedule = false;
        if (this.pendingCancelSection === 'assignees') this.editAssignees = false;
        if (this.pendingCancelSection === 'finance') this.editFinance = false;
        this.pendingCancelSection = null;
        this.unsavedModalOpen = false;
    }
}" class="space-y-6">

    {{-- Top Navigation & Sync Status --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <a href="{{ route('bookings.psychotest') }}" class="px-4 py-2 rounded-lg bg-white border border-[#D9C2F0] text-black hover:text-purple-deep text-sm font-semibold shadow-xs inline-flex items-center gap-2 w-fit">
            <span>← Kembali ke Layanan Psikotes</span>
        </a>

        <div class="flex flex-wrap items-center gap-3">
            <span class="px-3.5 py-2 rounded-lg bg-slate-50 border border-slate-200 text-xs font-medium text-black">
                Status:
                {{ match($booking->status) {
                    'selesai' => 'Selesai',
                    'batal' => 'Dibatalkan',
                    default => 'Berjalan',
                } }}
            </span>

            <span class="px-3.5 py-2 rounded-lg bg-slate-50 border border-slate-200 text-xs font-medium text-black">
                Google Calendar:
                @if($booking->gcal_sync_status === 'synced')
                    Tersinkron ({{ $booking->last_gcal_synced_at ? $booking->last_gcal_synced_at->locale('id')->isoFormat('D MMMM Y, HH:mm') : '-' }})
                @elseif($booking->gcal_sync_status === 'pending_auth' || $booking->gcal_sync_status === 'failed')
                    GCal belum sinkron
                @else
                    Belum dijadwalkan
                @endif
            </span>

            @if($booking->tanggal_dijadwalkan)
                <form method="POST" action="{{ route('bookings.sync-gcal', $booking) }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-lg bg-white border border-[#D9C2F0] text-black hover:text-purple-deep text-xs font-semibold shadow-xs cursor-pointer">
                        Sync ke GCal
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Panel Prioritas KILAT (Jika Ada Kandidat KILAT) --}}
    @if($kilatCandidates->isNotEmpty())
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="px-6 py-4 bg-purple-deep/95 text-white flex items-center justify-between">
                <h3 class="text-base font-bold text-white">Prioritas KILAT (Jalur Kirim Cepat)</h3>
                <span class="text-xs text-purple-100 font-medium">{{ $kilatCandidates->count() }} Kandidat KILAT</span>
            </div>
            <div class="p-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($kilatCandidates as $kc)
                    <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex flex-col justify-between gap-3">
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-black">{{ $kc->candidate_name }}</p>
                            <p class="text-xs font-medium text-slate-500">
                                Psikolog: <span class="text-black">{{ $kc->assignedPsikolog?->name ?? '-' }}</span> ·
                                Deadline Klien: <span class="text-black">{{ $kc->client_deadline_date ? $kc->client_deadline_date->locale('id')->isoFormat('D MMMM Y') : '-' }}</span>
                            </p>
                            <p class="text-xs font-medium text-slate-500">
                                Status Kirim Kilat:
                                <span class="text-black">
                                    {{ $kc->kilat_sent_at ? 'Terkirim pada ' . $kc->kilat_sent_at->locale('id')->isoFormat('D MMMM Y, HH:mm') : 'Belum dikirim' }}
                                </span>
                            </p>
                        </div>
                        @if($isAdmin)
                            <form method="POST" action="{{ route('psychotest-bookings.candidate.kilat-sent', [$booking, $kc]) }}">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 rounded-lg {{ $kc->kilat_sent_at ? 'bg-white border border-[#D9C2F0] text-purple-deep hover:bg-purple-50' : 'bg-purple-deep hover:bg-purple-deep/90 text-white' }} text-xs font-semibold shadow-xs cursor-pointer">
                                    {{ $kc->kilat_sent_at ? 'Batalkan Kirim Kilat' : 'Tandai Kilat Terkirim' }}
                                </button>
                            </form>
                        @else
                            <div class="text-xs font-medium text-slate-500 pt-1">
                                {{ $kc->kilat_sent_at ? 'Sudah ditandai terkirim oleh Admin.' : 'Menunggu konfirmasi kirim oleh Admin.' }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Panel Konfirmasi Tugas Kolektif Batch (Tester, Skorer Manual, Skorer Software) --}}
    @if($batchAssignees->isNotEmpty())
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="px-6 py-4 bg-purple-deep/95 text-white flex items-center justify-between">
                <h3 class="text-base font-bold text-white">Konfirmasi Tugas Kolektif Tim (Tester &amp; Skorer)</h3>
                <span class="text-xs text-purple-100 font-medium">Seluruh Skorer wajib konfirmasi selesai sebelum tahap Skoring terbuka penuh</span>
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($batchAssignees as $assignee)
                    @php
                        $canToggle = $workflowService->canUserToggleAssignee($user, $assignee);
                    @endphp
                    <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <span class="text-xs font-medium text-slate-500 block">{{ $assignee->role_label }}</span>
                            <p class="text-sm font-medium text-black truncate">{{ $assignee->assignee_name }}</p>
                            <p class="text-[11px] font-medium text-slate-500 mt-0.5">
                                {{ $assignee->batch_completed_at ? 'Selesai ' . $assignee->batch_completed_at->locale('id')->isoFormat('D MMMM Y, HH:mm') : 'Menunggu konfirmasi' }}
                            </p>
                        </div>
                        @if($canToggle)
                            <form method="POST" action="{{ route('psychotest-bookings.assignee.toggle', [$booking, $assignee]) }}">
                                @csrf
                                <button type="submit" class="px-3.5 py-2 rounded-lg {{ $assignee->batch_completed_at ? 'bg-white border border-[#D9C2F0] text-purple-deep' : 'bg-purple-deep text-white' }} text-xs font-semibold shadow-xs cursor-pointer whitespace-nowrap">
                                    {{ $assignee->batch_completed_at ? 'Selesai ✓' : 'Tandai Selesai' }}
                                </button>
                            </form>
                        @else
                            <span class="text-xs font-medium text-black">{{ $assignee->batch_completed_at ? 'Selesai' : 'Proses' }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Tabel Pelacakan Progres Per-Kandidat (Sequential Gate) --}}
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="px-6 py-4 bg-purple-deep/95 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <h3 class="text-base font-bold text-white">Pelacakan Progres Per-Kandidat</h3>
            <span class="text-xs text-purple-100 font-medium">
                Skema: {{ $booking->billing_scheme_label }} · Kirim: {{ $booking->delivery_mode_label }} ·
                Surat Keluar: {{ $booking->requires_outgoing_letter ? 'Wajib' : 'Tidak Perlu' }}
            </span>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-sm font-medium">
                <thead>
                    <tr class="bg-slate-50 text-black font-semibold select-none border-b border-[#EDE1FA]">
                        <th class="px-4 py-3.5 text-left font-semibold whitespace-nowrap">Kandidat</th>
                        <th class="px-4 py-3.5 text-left font-semibold whitespace-nowrap">Kehadiran</th>
                        <th class="px-4 py-3.5 text-left font-semibold whitespace-nowrap">Psikolog &amp; SLA</th>
                        <th class="px-4 py-3.5 text-left font-semibold whitespace-nowrap">Editor</th>
                        <th class="px-4 py-3.5 text-left font-semibold whitespace-nowrap">SLA Klien</th>
                        <th class="px-3 py-3.5 text-center font-semibold whitespace-nowrap">Psikotes</th>
                        <th class="px-3 py-3.5 text-center font-semibold whitespace-nowrap">Skoring</th>
                        <th class="px-3 py-3.5 text-center font-semibold whitespace-nowrap">Surat Keluar</th>
                        <th class="px-3 py-3.5 text-center font-semibold whitespace-nowrap">Draft HPP</th>
                        <th class="px-3 py-3.5 text-center font-semibold whitespace-nowrap">Editing</th>
                        <th class="px-3 py-3.5 text-center font-semibold whitespace-nowrap">Cetak</th>
                        <th class="px-3 py-3.5 text-center font-semibold whitespace-nowrap">Approval</th>
                        @if($booking->delivery_mode === 'both')
                            <th class="px-3 py-3.5 text-center font-semibold whitespace-nowrap">Softcopy</th>
                        @endif
                        <th class="px-3 py-3.5 text-center font-semibold whitespace-nowrap">{{ $booking->delivery_mode === 'both' ? 'Kirim (Hardcopy & Invoice)' : 'Kirim' }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F3EAFB]">
                    @forelse($booking->candidates as $cand)
                        @php
                            $canAttendance = $workflowService->canUserManageCandidateAttendance($user, $booking, $cand);

                            $steps = [
                                'psikotes' => ['applicable' => true, 'at' => $cand->step_psikotes_at],
                                'skoring' => ['applicable' => true, 'at' => $cand->step_skoring_at],
                                'surat_keluar' => ['applicable' => (bool) $booking->requires_outgoing_letter, 'at' => $cand->step_surat_keluar_at],
                                'draft_hpp' => ['applicable' => true, 'at' => $cand->step_draft_hpp_at],
                                'editing' => ['applicable' => true, 'at' => $cand->step_editing_at],
                                'cetak' => ['applicable' => in_array($booking->delivery_mode, ['hardcopy', 'both'], true), 'at' => $cand->step_cetak_at],
                                'approval' => ['applicable' => true, 'at' => $cand->step_approval_at],
                                'kirim' => ['applicable' => true, 'at' => $cand->step_kirim_at],
                            ];
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3.5 text-black text-xs font-medium whitespace-nowrap">
                                {{ $cand->candidate_name }}{{ $cand->is_kilat ? ' [KILAT]' : '' }}
                                @if($cand->target_position)
                                    <span class="block text-[11px] text-slate-500 font-medium">{{ $cand->target_position }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-black text-xs font-medium whitespace-nowrap">
                                @if($canAttendance)
                                    <form method="POST" action="{{ route('psychotest-bookings.candidate.attendance', [$booking, $cand]) }}">
                                        @csrf
                                        <select name="attendance_status" onchange="this.form.submit()" class="px-2.5 py-1.5 rounded-lg border border-[#D9C2F0] bg-white text-xs font-medium text-black">
                                            <option value="pending" {{ $cand->attendance_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="hadir" {{ $cand->attendance_status === 'hadir' ? 'selected' : '' }}>Hadir</option>
                                            <option value="absen" {{ $cand->attendance_status === 'absen' ? 'selected' : '' }}>Absen</option>
                                        </select>
                                    </form>
                                @else
                                    {{ ucfirst($cand->attendance_status) }}
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-black text-xs font-medium whitespace-nowrap">
                                {{ $cand->assignedPsikolog?->name ?? '-' }}
                                <span class="block text-[11px] {{ $cand->isPsychologistReportOverdue() ? 'text-red-600' : 'text-slate-500' }} font-medium">
                                    @if($cand->isAbsent())
                                        Gugur (Absen)
                                    @elseif($cand->is_kilat && $cand->kilat_sent_at)
                                        Terkirim (KILAT)
                                    @elseif($cand->psychologist_deadline_date)
                                        {{ $cand->psychologist_deadline_date->format('d/m/Y') . ($cand->isPsychologistReportOverdue() ? ' (Terlambat)' : '') }}
                                    @else
                                        -
                                    @endif
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $cand->assignedEditor?->name ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-black text-xs font-medium whitespace-nowrap">
                                {{ $cand->isAbsent() ? '-' : ($cand->client_deadline_date ? $cand->client_deadline_date->format('d/m/Y') : '-') }}
                            </td>

                            @foreach($steps as $stepKey => $stepMeta)
                                @if($stepKey === 'kirim' && $booking->delivery_mode === 'both')
                                    <td class="px-3 py-3.5 text-center whitespace-nowrap">
                                        @if($cand->isAbsent())
                                            <span class="text-slate-400 text-xs font-medium">-</span>
                                        @elseif($isAdmin)
                                            <form method="POST" action="{{ route('psychotest-bookings.candidate.softcopy-sent', [$booking, $cand]) }}">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 rounded-lg {{ $cand->softcopy_sent_at ? 'bg-purple-deep text-white' : 'bg-white border border-[#D9C2F0] text-black hover:text-purple-deep' }} text-xs font-medium cursor-pointer" title="Centang jika Softcopy sudah dikirim via email (tanggal kirim final tetap mengikuti Hardcopy + Invoice)">
                                                    {{ $cand->softcopy_sent_at ? '✓' : 'Centang' }}
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs font-medium text-black">{{ $cand->softcopy_sent_at ? '✓' : '-' }}</span>
                                        @endif
                                    </td>
                                @endif
                                <td class="px-3 py-3.5 text-center whitespace-nowrap">
                                    @if($cand->isAbsent() && $stepKey !== 'psikotes')
                                        <span class="text-slate-400 text-xs font-medium">-</span>
                                    @elseif(!$stepMeta['applicable'])
                                        <span class="text-slate-400 text-xs font-medium">-</span>
                                    @else
                                        @php
                                            $canStep = $workflowService->canUserToggleCandidateStep($user, $booking, $cand, $stepKey);
                                            $isDone = !is_null($stepMeta['at']);
                                        @endphp
                                        @if($stepKey === 'kirim' && in_array($booking->delivery_mode, ['hardcopy', 'both'], true) && !$isDone && $canStep)
                                            <button type="button"
                                                @click="openResiModal('{{ route('psychotest-bookings.candidate.step', [$booking, $cand]) }}', '{{ addslashes($cand->candidate_name) }}', '{{ $cand->resi_date ? $cand->resi_date->format('Y-m-d') : '' }}', '{{ addslashes($cand->resi_number ?? '') }}')"
                                                class="px-2.5 py-1 rounded-lg bg-white border border-[#D9C2F0] text-purple-deep hover:bg-purple-50 text-xs font-medium cursor-pointer">
                                                Kirim + Resi
                                            </button>
                                        @elseif($canStep)
                                            <form method="POST" action="{{ route('psychotest-bookings.candidate.step', [$booking, $cand]) }}">
                                                @csrf
                                                <input type="hidden" name="step" value="{{ $stepKey }}">
                                                <button type="submit" class="px-2.5 py-1 rounded-lg {{ $isDone ? 'bg-purple-deep text-white' : 'bg-white border border-[#D9C2F0] text-black hover:text-purple-deep' }} text-xs font-medium cursor-pointer">
                                                    {{ $isDone ? '✓' : 'Centang' }}
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs font-medium text-black">{{ $isDone ? '✓' : '-' }}</span>
                                        @endif
                                        @if($stepKey === 'kirim' && $isDone && ($cand->resi_number || $cand->resi_date))
                                            <span class="block text-[10px] text-slate-500 font-medium mt-0.5">{{ trim(($cand->resi_date ? $cand->resi_date->format('d/m/Y') : '') . ($cand->resi_number ? ' · ' . $cand->resi_number : ''), ' ·') }}</span>
                                        @endif
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $booking->delivery_mode === 'both' ? 14 : 13 }}" class="px-6 py-8 text-center text-slate-400 text-xs font-medium">Belum ada kandidat tercatat pada booking ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 3 Card Pengelolaan Admin (View vs Edit sesuai AGENTS.md) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Card A: Jadwal & Pengiriman --}}
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="px-6 py-4 bg-purple-deep flex items-center justify-between text-white">
                <h3 class="text-base font-semibold">Jadwal &amp; Pengiriman Laporan</h3>
                @if($isAdmin)
                    <div class="flex items-center gap-2">
                        <button type="button" x-show="!editSchedule" @click="editSchedule = true" class="px-3 py-1 rounded-lg bg-white/15 hover:bg-white/25 text-white text-xs font-semibold cursor-pointer">Ubah</button>
                        <button type="button" x-show="editSchedule" x-cloak @click="confirmCancelSection('schedule')" class="px-3 py-1 rounded-lg bg-white text-purple-deep text-xs font-semibold cursor-pointer">Batal</button>
                    </div>
                @endif
            </div>
            <div class="p-6">
                <div x-show="!editSchedule" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <span class="text-xs font-medium text-slate-500 mb-1 block">Klien / Perusahaan</span>
                        <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">{{ $clientDisplay }}</div>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-slate-500 mb-1 block">Nama PIC</span>
                        <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">{{ $picDisplay }}</div>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-slate-500 mb-1 block">Tanggal Pelaksanaan</span>
                        <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">{{ $booking->tanggal_dijadwalkan ? $booking->tanggal_dijadwalkan->locale('id')->isoFormat('D MMMM Y') : '-' }}</div>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-slate-500 mb-1 block">Metode &amp; Lokasi</span>
                        <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">{{ ($booking->session_type === 'online' ? 'Online' : 'Offline') . ($booking->location ? ' · ' . $booking->location : '') }}</div>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-slate-500 mb-1 block">Metode Pengiriman Hasil</span>
                        <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">{{ $booking->delivery_mode_label }}</div>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-slate-500 mb-1 block">Perlu Surat Keluar?</span>
                        <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">{{ $booking->requires_outgoing_letter ? 'Ya (Wajib)' : 'Tidak Perlu' }}</div>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="text-xs font-medium text-slate-500 mb-1 block">Status Booking Psikotes</span>
                        <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                            {{ match($booking->status) {
                                'selesai' => 'Selesai',
                                'batal' => 'Dibatalkan',
                                default => 'Berjalan',
                            } }}
                        </div>
                    </div>
                </div>

                @if($isAdmin)
                    <form x-show="editSchedule" x-cloak method="POST" action="{{ route('psychotest-bookings.update-meta', $booking) }}" class="space-y-4">
                        @csrf @method('PUT')
                        <input type="hidden" name="section" value="schedule">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-medium text-slate-500 mb-1 block">Tanggal Pelaksanaan *</label>
                                <input type="date" name="tanggal_dijadwalkan" value="{{ $booking->tanggal_dijadwalkan ? $booking->tanggal_dijadwalkan->format('Y-m-d') : '' }}" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black min-h-[42px]">
                            </div>
                            <div>
                                <label class="text-xs font-medium text-slate-500 mb-1 block">Jam Mulai - Selesai</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="time" name="session_start" value="{{ $booking->session_start ? substr($booking->session_start, 0, 5) : '' }}" class="w-full px-2.5 py-2 rounded-lg border border-[#D9C2F0] bg-white text-xs font-medium text-black min-h-[42px]">
                                    <input type="time" name="session_end" value="{{ $booking->session_end ? substr($booking->session_end, 0, 5) : '' }}" class="w-full px-2.5 py-2 rounded-lg border border-[#D9C2F0] bg-white text-xs font-medium text-black min-h-[42px]">
                                </div>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-slate-500 mb-1 block">Metode Pelaksanaan</label>
                                <select name="session_type" class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black min-h-[42px]">
                                    <option value="online" {{ $booking->session_type === 'online' ? 'selected' : '' }}>Online</option>
                                    <option value="tatap_muka" {{ $booking->session_type !== 'online' ? 'selected' : '' }}>Offline (Tatap Muka)</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-slate-500 mb-1 block">Lokasi / Link</label>
                                <input type="text" name="location" value="{{ $booking->location }}" class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black min-h-[42px]">
                            </div>
                            <div>
                                <label class="text-xs font-medium text-slate-500 mb-1 block">Metode Pengiriman Hasil</label>
                                <select name="delivery_mode" class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black min-h-[42px]">
                                    <option value="softcopy" {{ $booking->delivery_mode === 'softcopy' ? 'selected' : '' }}>Softcopy</option>
                                    <option value="hardcopy" {{ $booking->delivery_mode === 'hardcopy' ? 'selected' : '' }}>Hardcopy</option>
                                    <option value="both" {{ $booking->delivery_mode === 'both' ? 'selected' : '' }}>Softcopy &amp; Hardcopy</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <label class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white min-h-[42px] flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="requires_outgoing_letter" value="1" {{ $booking->requires_outgoing_letter ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-purple-deep">
                                    <span class="text-sm font-medium text-black">Perlu Surat Keluar?</span>
                                </label>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="text-xs font-medium text-slate-500 mb-1 block">Status Booking Psikotes</label>
                                <select name="status" class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black min-h-[42px]">
                                    <option value="baru" {{ $booking->status === 'baru' ? 'selected' : '' }}>Berjalan</option>
                                    <option value="selesai" {{ $booking->status === 'selesai' ? 'selected' : '' }}>Selesai</option>
                                    <option value="batal" {{ $booking->status === 'batal' ? 'selected' : '' }}>Dibatalkan (Batal)</option>
                                </select>
                                <p class="text-[11px] font-medium text-slate-500 mt-1">Mengubah status menjadi Dibatalkan akan melepas formulir kandidat kembali ke antrean Belum Dibooking dan menghapus jadwal dari Google Calendar.</p>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-5 py-2.5 rounded-lg bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-sm shadow-xs cursor-pointer inline-flex items-center gap-2">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                <span>Simpan</span>
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>

        {{-- Card B: Invoice, Nominal Final UC Finance, & Bukti Bayar --}}
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="px-6 py-4 bg-purple-deep flex items-center justify-between text-white">
                <h3 class="text-base font-semibold">Nota &amp; Pembayaran UC Finance</h3>
                @if($isAdmin)
                    <div class="flex items-center gap-2">
                        <button type="button" x-show="!editFinance" @click="editFinance = true" class="px-3 py-1 rounded-lg bg-white/15 hover:bg-white/25 text-white text-xs font-semibold cursor-pointer">Ubah</button>
                        <button type="button" x-show="editFinance" x-cloak @click="confirmCancelSection('finance')" class="px-3 py-1 rounded-lg bg-white text-purple-deep text-xs font-semibold cursor-pointer">Batal</button>
                    </div>
                @endif
            </div>
            <div class="p-6">
                <div x-show="!editFinance" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <span class="text-xs font-medium text-slate-500 mb-1 block">Harga Dasar Pricelist (Sebelum Pajak)</span>
                        <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                            {{ $booking->price ? 'Rp ' . number_format((float) $booking->price, 0, ',', '.') : '-' }}
                        </div>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-slate-500 mb-1 block">Nominal Final UC Finance</span>
                        <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                            {{ $booking->final_price ? 'Rp ' . number_format((float) $booking->final_price, 0, ',', '.') : 'Menunggu balasan Finance' }}
                        </div>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-slate-500 mb-1 block">Skema Penagihan (Invoice)</span>
                        <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                            {{ $booking->billing_scheme_label }}
                        </div>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-slate-500 mb-1 block">Status Pembayaran</span>
                        <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                            {{ match($booking->payment_workflow_status) {
                                'draft_ke_finance' => 'Pengajuan Nota (Finance)',
                                'menunggu_bayar' => 'Menunggu Pembayaran (VA)',
                                'lunas' => 'Lunas',
                                default => 'Belum Ada Tagihan',
                            } }}
                        </div>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-slate-500 mb-1 block">Nomor Invoice / VA Finance</span>
                        <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                            {{ trim(($booking->finance_invoice_number ?: '') . ($booking->va_number ? ' · VA: ' . $booking->va_number : ''), ' ·') ?: '-' }}
                        </div>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-slate-500 mb-1 block">Catatan Bukti Transfer</span>
                        <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                            {{ $booking->payment_proof_notes ?: '-' }}
                        </div>
                    </div>
                </div>

                @if($isAdmin)
                    <form x-show="editFinance" x-cloak method="POST" action="{{ route('psychotest-bookings.update-meta', $booking) }}" class="space-y-4">
                        @csrf @method('PUT')
                        <input type="hidden" name="section" value="finance">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-medium text-slate-500 mb-1 block">Skema Penagihan *</label>
                                <select name="billing_scheme" class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black min-h-[42px]">
                                    <option value="v" {{ $booking->billing_scheme === 'v' ? 'selected' : '' }}>Reguler / Per-Order (v)</option>
                                    <option value="I" {{ $booking->billing_scheme === 'I' ? 'selected' : '' }}>Rekap Internal UCS (I)</option>
                                    <option value="R" {{ $booking->billing_scheme === 'R' ? 'selected' : '' }}>Rekap Perusahaan (R)</option>
                                    <option value="-" {{ $booking->billing_scheme === '-' ? 'selected' : '' }}>Gratis / Deal Khusus (-)</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-slate-500 mb-1 block">Status Pembayaran *</label>
                                <select name="payment_workflow_status" class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black min-h-[42px]">
                                    <option value="menunggu_sesi_selesai" {{ $booking->payment_workflow_status === 'menunggu_sesi_selesai' ? 'selected' : '' }}>Belum Ada Tagihan</option>
                                    <option value="draft_ke_finance" {{ $booking->payment_workflow_status === 'draft_ke_finance' ? 'selected' : '' }}>Pengajuan Nota (Email ke Finance)</option>
                                    <option value="menunggu_bayar" {{ $booking->payment_workflow_status === 'menunggu_bayar' ? 'selected' : '' }}>Menunggu Pembayaran (Invoice + VA)</option>
                                    <option value="lunas" {{ $booking->payment_workflow_status === 'lunas' ? 'selected' : '' }}>Lunas</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-slate-500 mb-1 block">Nominal Final dari UC Finance (Rp)</label>
                                <input type="number" name="final_price" value="{{ $booking->final_price }}" placeholder="Nominal akhir termasuk pajak / tarif khusus" class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black min-h-[42px]">
                            </div>
                            <div>
                                <label class="text-xs font-medium text-slate-500 mb-1 block">Nomor Invoice Finance</label>
                                <input type="text" name="finance_invoice_number" value="{{ $booking->finance_invoice_number }}" class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black min-h-[42px]">
                            </div>
                            <div>
                                <label class="text-xs font-medium text-slate-500 mb-1 block">Nomor Virtual Account (VA)</label>
                                <input type="text" name="va_number" value="{{ $booking->va_number }}" class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black min-h-[42px]">
                            </div>
                            <div>
                                <label class="text-xs font-medium text-slate-500 mb-1 block">Catatan Bukti Transfer</label>
                                <input type="text" name="payment_proof_notes" value="{{ $booking->payment_proof_notes }}" placeholder="Tanggal terima & diteruskan ke Finance" class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black min-h-[42px]">
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-5 py-2.5 rounded-lg bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-sm shadow-xs cursor-pointer inline-flex items-center gap-2">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                <span>Simpan</span>
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Card C: Ringkasan & Edit Penugasan 5 Peran --}}
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="px-6 py-4 bg-purple-deep flex items-center justify-between text-white">
            <h3 class="text-base font-semibold">Penugasan Tim 5 Peran (Sinkron ke Kelola Penugasan)</h3>
            @if($isAdmin)
                <div class="flex items-center gap-2">
                    <button type="button" x-show="!editAssignees" @click="editAssignees = true" class="px-3 py-1 rounded-lg bg-white/15 hover:bg-white/25 text-white text-xs font-semibold cursor-pointer">Ubah Penugasan</button>
                    <button type="button" x-show="editAssignees" x-cloak @click="confirmCancelSection('assignees')" class="px-3 py-1 rounded-lg bg-white text-purple-deep text-xs font-semibold cursor-pointer">Batal</button>
                </div>
            @endif
        </div>
        <div class="p-6">
            <div x-show="!editAssignees" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <span class="text-xs font-medium text-slate-500 mb-1 block">Psikolog (Kuota)</span>
                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">{{ $booking->formatAssigneeSummary('psikolog') }}</div>
                </div>
                <div>
                    <span class="text-xs font-medium text-slate-500 mb-1 block">Tester</span>
                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">{{ $booking->formatAssigneeSummary('tester') }}</div>
                </div>
                <div>
                    <span class="text-xs font-medium text-slate-500 mb-1 block">Skorer Manual</span>
                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">{{ $booking->formatAssigneeSummary('skorer_manual') }}</div>
                </div>
                <div>
                    <span class="text-xs font-medium text-slate-500 mb-1 block">Skorer Software</span>
                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">{{ $booking->formatAssigneeSummary('skorer_software') }}</div>
                </div>
                <div>
                    <span class="text-xs font-medium text-slate-500 mb-1 block">Editor (Kuota)</span>
                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">{{ $booking->formatAssigneeSummary('editor') }}</div>
                </div>
            </div>

            @if($isAdmin)
                <form x-show="editAssignees" x-cloak method="POST" action="{{ route('psychotest-bookings.update-meta', $booking) }}" class="space-y-5">
                    @csrf @method('PUT')
                    <input type="hidden" name="section" value="assignees">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @if(!$booking->isSpecialNonIndustri())
                            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 space-y-2">
                                <span class="text-xs font-medium text-slate-500 block">Tester (Multi-Select)</span>
                                <div class="max-h-32 overflow-y-auto custom-scrollbar space-y-1.5">
                                    @foreach($staffs as $st)
                                        <label class="flex items-center gap-2 text-xs font-medium text-black cursor-pointer">
                                            <input type="checkbox" name="tester_ids[]" value="{{ $st->id }}" {{ in_array($st->id, $selectedTesterIds) ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-purple-deep">
                                            <span>{{ $st->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 space-y-2">
                            <span class="text-xs font-medium text-slate-500 block">Skorer Manual (Multi-Select)</span>
                            <div class="max-h-32 overflow-y-auto custom-scrollbar space-y-1.5">
                                @foreach($staffs as $st)
                                    <label class="flex items-center gap-2 text-xs font-medium text-black cursor-pointer">
                                        <input type="checkbox" name="skorer_manual_ids[]" value="{{ $st->id }}" {{ in_array($st->id, $selectedSkorerManualIds) ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-purple-deep">
                                        <span>{{ $st->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 space-y-2">
                            <span class="text-xs font-medium text-slate-500 block">Skorer Software (Multi-Select)</span>
                            <div class="max-h-32 overflow-y-auto custom-scrollbar space-y-1.5">
                                @foreach($staffs as $st)
                                    <label class="flex items-center gap-2 text-xs font-medium text-black cursor-pointer">
                                        <input type="checkbox" name="skorer_software_ids[]" value="{{ $st->id }}" {{ in_array($st->id, $selectedSkorerSoftwareIds) ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-purple-deep">
                                        <span>{{ $st->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto border border-slate-200 rounded-xl">
                        <table class="w-full text-sm font-medium">
                            <thead>
                                <tr class="bg-slate-50 text-black font-semibold border-b border-slate-200">
                                    <th class="px-4 py-3 text-left font-semibold">Kandidat</th>
                                    @if(!$booking->isSpecialNonIndustri())
                                        <th class="px-4 py-3 text-left font-semibold">Psikolog</th>
                                    @endif
                                    <th class="px-4 py-3 text-left font-semibold">Editor</th>
                                    <th class="px-4 py-3 text-center font-semibold">KILAT</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($booking->candidates as $cand)
                                    <tr>
                                        <td class="px-4 py-2.5 text-xs font-medium text-black">{{ $cand->candidate_name }}</td>
                                        @if(!$booking->isSpecialNonIndustri())
                                            <td class="px-4 py-2.5">
                                                @if(!$cand->isSpecialPackage())
                                                    <select name="candidate_psikolog[{{ $cand->id }}]" class="w-full px-3 py-1.5 rounded-lg border border-[#D9C2F0] bg-white text-xs font-medium text-black">
                                                        <option value="">-- Pilih Psikolog --</option>
                                                        @foreach($counselors as $c)
                                                            <option value="{{ $c->id }}" {{ (int) $cand->assigned_psikolog_id === $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    <span class="text-xs font-medium text-slate-400">Khusus (Admin)</span>
                                                @endif
                                            </td>
                                        @endif
                                        <td class="px-4 py-2.5">
                                            <select name="candidate_editor[{{ $cand->id }}]" class="w-full px-3 py-1.5 rounded-lg border border-[#D9C2F0] bg-white text-xs font-medium text-black">
                                                <option value="">-- Pilih Editor --</option>
                                                @foreach($staffs as $s)
                                                    <option value="{{ $s->id }}" {{ (int) $cand->assigned_editor_id === $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-4 py-2.5 text-center">
                                            <input type="checkbox" name="candidate_kilat[{{ $cand->id }}]" value="1" {{ $cand->is_kilat ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-purple-deep">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-5 py-2.5 rounded-lg bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-sm shadow-xs cursor-pointer inline-flex items-center gap-2">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            <span>Simpan Penugasan</span>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    {{-- Modal Input Resi Pengiriman Hardcopy --}}
    <div x-show="resiModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-[#1F0E38]/50 backdrop-blur-md"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-md rounded-2xl bg-white shadow-[0_30px_70px_rgba(74,35,128,0.2)] overflow-hidden">
                <div class="px-6 py-4 bg-purple-deep flex items-center justify-between text-white">
                    <h3 class="text-base font-semibold">Konfirmasi Kirim &amp; Nomor Resi</h3>
                    <button type="button" @click="resiModalOpen = false" class="p-1 rounded-lg text-white/80 hover:text-white hover:bg-white/15 cursor-pointer">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <form method="POST" :action="resiActionUrl" class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="step" value="kirim">
                    <div class="p-3.5 rounded-lg bg-slate-50 border border-slate-100">
                        <span class="text-xs font-medium text-slate-500 block">Kandidat</span>
                        <span class="text-sm font-medium text-black" x-text="resiCandidateName"></span>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-500 mb-1 block">Tanggal Resi *</label>
                        <input type="date" name="resi_date" x-model="resiDate" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black min-h-[42px]">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-500 mb-1 block">Nomor Resi Ekspedisi *</label>
                        <input type="text" name="resi_number" x-model="resiNumber" required placeholder="Contoh: J00123456789" class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black min-h-[42px]">
                    </div>
                    @if($booking->delivery_mode === 'both')
                        <p class="text-[11px] font-medium text-slate-500">Untuk metode Softcopy &amp; Hardcopy, tanggal kirim resmi mengikuti pengiriman fisik Hardcopy (+ Invoice).</p>
                    @endif
                    <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button type="button" @click="resiModalOpen = false" class="px-5 py-2.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-sm shadow-xs cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-lg bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-sm shadow-xs cursor-pointer inline-flex items-center gap-2">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            <span>Simpan &amp; Tandai Terkirim</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Unsaved Changes --}}
    <div x-show="unsavedModalOpen" x-cloak class="fixed inset-0 z-[60] overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-[#1F0E38]/50 backdrop-blur-md"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-md rounded-2xl bg-white shadow-[0_30px_70px_rgba(74,35,128,0.2)] overflow-hidden">
                <div class="px-6 py-4 bg-purple-deep flex items-center justify-between text-white">
                    <h3 class="text-base font-semibold">Perubahan Belum Disimpan</h3>
                    <button type="button" @click="unsavedModalOpen = false" class="p-1 rounded-lg text-white/80 hover:text-white hover:bg-white/15 cursor-pointer">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <div class="p-6">
                    <div class="p-4 rounded-lg bg-slate-50 border border-slate-100">
                        <p class="text-sm font-medium text-black">Batalkan mode ubah? Perubahan yang belum disimpan akan diabaikan.</p>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="unsavedModalOpen = false" class="px-5 py-2.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-sm shadow-xs cursor-pointer">Batal</button>
                    <button type="button" @click="discardSectionEdit()" class="px-5 py-2.5 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold text-sm shadow-xs cursor-pointer">Ya, Tinggalkan</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
