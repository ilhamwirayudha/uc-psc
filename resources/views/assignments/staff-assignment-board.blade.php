@extends('layouts.dashboard')

@section('title', 'Kelola Penugasan — UC PSC')
@section('page-title', 'Kelola Penugasan')
@section('page-subtitle', 'Rekap beban tugas Psikolog dan Staff yang tersinkron otomatis dari Layanan Psikotes dan Konseling')

@section('content')
<div x-data="{
    activeTab: '{{ $activeTab }}',
    expandedPersonId: null,
    reassignModalOpen: false,
    reassignTaskType: '',
    reassignTargetId: '',
    reassignTitle: '',
    reassignRoleKind: 'psikolog',
    newAssigneeId: '',

    togglePerson(key) {
        this.expandedPersonId = this.expandedPersonId === key ? null : key;
    },
    openReassign(taskType, targetId, title, roleKind) {
        this.reassignTaskType = taskType;
        this.reassignTargetId = targetId;
        this.reassignTitle = title;
        this.reassignRoleKind = roleKind;
        this.newAssigneeId = '';
        this.reassignModalOpen = true;
    }
}" class="space-y-6">

    {{-- Tab Selector --}}
    <div class="flex flex-wrap items-center gap-2">
        <button type="button" @click="activeTab = 'psikolog'"
            :class="activeTab === 'psikolog' ? 'bg-purple-deep text-white' : 'bg-white border border-[#D9C2F0] text-black hover:text-purple-deep'"
            class="px-5 py-2.5 rounded-lg text-sm font-semibold shadow-xs transition cursor-pointer">
            Beban Tugas Psikolog ({{ $psychologistWorkloads->count() }})
        </button>
        <button type="button" @click="activeTab = 'staff'"
            :class="activeTab === 'staff' ? 'bg-purple-deep text-white' : 'bg-white border border-[#D9C2F0] text-black hover:text-purple-deep'"
            class="px-5 py-2.5 rounded-lg text-sm font-semibold shadow-xs transition cursor-pointer">
            Beban Tugas Staff ({{ $staffWorkloads->count() }})
        </button>
    </div>

    {{-- TAB 1: PSIKOLOG --}}
    <div x-show="activeTab === 'psikolog'" class="glass-card rounded-2xl overflow-hidden">
        <div class="px-6 py-4 bg-purple-deep/95 text-white flex items-center justify-between">
            <h3 class="text-base font-bold text-white">Daftar Beban Tugas Psikolog (Konseling &amp; Psikotes)</h3>
            <span class="text-xs text-purple-100 font-medium">Klik baris untuk melihat rincian tugas dan mengalihkan petugas</span>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-sm font-medium">
                <thead>
                    <tr class="bg-slate-50 text-black font-semibold border-b border-[#EDE1FA]">
                        <th class="px-6 py-3.5 text-left font-semibold">Nama Psikolog</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Konseling Aktif</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Kandidat Psikotes Aktif</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Prioritas KILAT</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Terlambat SLA</th>
                        <th class="px-6 py-3.5 text-right font-semibold">Total Tugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F3EAFB]">
                    @forelse($psychologistWorkloads as $item)
                        @php $p = $item['counselor']; @endphp
                        <tr class="table-row-clickable hover:bg-slate-100 active:bg-slate-200 transition-colors duration-150 cursor-pointer" @click="togglePerson('p-{{ $p->id }}')">
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $p->name }}</td>
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $item['active_counseling_count'] }} sesi</td>
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $item['active_candidate_count'] }} kandidat</td>
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $item['kilat_count'] }}</td>
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $item['overdue_count'] }}</td>
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap text-right">{{ $item['total_active'] }} tugas</td>
                        </tr>
                        <tr x-show="expandedPersonId === 'p-{{ $p->id }}'" x-cloak>
                            <td colspan="6" class="px-6 py-4 bg-slate-50">
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-xs font-medium text-slate-500 mb-2">Kandidat Psikotes yang Ditangani</p>
                                        @if($item['psychotest_candidates']->isNotEmpty())
                                            <div class="space-y-2">
                                                @foreach($item['psychotest_candidates'] as $cand)
                                                    <div class="p-3 bg-white rounded-lg border border-slate-200 flex flex-wrap items-center justify-between gap-3">
                                                        <div>
                                                            <a href="{{ route('psychotest-bookings.show', $cand->booking_id) }}" class="text-xs font-medium text-black hover:text-purple-deep">
                                                                {{ $cand->candidate_name }}{{ $cand->is_kilat ? ' [KILAT]' : '' }} — {{ $cand->booking?->company?->name ?? $cand->booking?->client?->name ?? '-' }}
                                                            </a>
                                                            <p class="text-[11px] font-medium text-slate-500">
                                                                Tanggal Tes: {{ $cand->booking?->tanggal_dijadwalkan ? $cand->booking->tanggal_dijadwalkan->format('d/m/Y') : '-' }} ·
                                                                Deadline Laporan: {{ $cand->psychologist_deadline_date ? $cand->psychologist_deadline_date->format('d/m/Y') : '-' }}
                                                                {{ $cand->isPsychologistReportOverdue() ? ' (Terlambat)' : '' }}
                                                            </p>
                                                        </div>
                                                        <button type="button"
                                                            @click="openReassign('candidate_psikolog', {{ $cand->id }}, '{{ addslashes($cand->candidate_name) }}', 'psikolog')"
                                                            class="px-3 py-1.5 rounded-lg bg-white border border-[#D9C2F0] text-purple-deep hover:bg-purple-50 text-xs font-semibold cursor-pointer">
                                                            Alihkan Psikolog
                                                        </button>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-xs font-medium text-slate-400">Tidak ada kandidat psikotes aktif.</p>
                                        @endif
                                    </div>

                                    <div>
                                        <p class="text-xs font-medium text-slate-500 mb-2">Jadwal Konseling Aktif</p>
                                        @if($item['counseling_tasks']->isNotEmpty())
                                            <div class="space-y-2">
                                                @foreach($item['counseling_tasks'] as $cb)
                                                    <div class="p-3 bg-white rounded-lg border border-slate-200 flex flex-wrap items-center justify-between gap-3">
                                                        <div>
                                                            <a href="{{ route('bookings.show', $cb) }}" class="text-xs font-medium text-black hover:text-purple-deep">
                                                                Konseling: {{ $cb->client?->name ?? '-' }}
                                                            </a>
                                                            <p class="text-[11px] font-medium text-slate-500">
                                                                Tanggal: {{ $cb->tanggal_dijadwalkan ? $cb->tanggal_dijadwalkan->format('d/m/Y') : 'Belum Dijadwalkan' }} · Status: {{ ucfirst(str_replace('_', ' ', $cb->status)) }}
                                                            </p>
                                                        </div>
                                                        <button type="button"
                                                            @click="openReassign('counseling_counselor', {{ $cb->id }}, '{{ addslashes($cb->client?->name ?? 'Klien') }}', 'psikolog')"
                                                            class="px-3 py-1.5 rounded-lg bg-white border border-[#D9C2F0] text-purple-deep hover:bg-purple-50 text-xs font-semibold cursor-pointer">
                                                            Alihkan Konselor
                                                        </button>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-xs font-medium text-slate-400">Tidak ada sesi konseling aktif.</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-10 text-center text-slate-400 text-xs font-medium">Belum ada Psikolog aktif.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- TAB 2: STAFF --}}
    <div x-show="activeTab === 'staff'" x-cloak class="glass-card rounded-2xl overflow-hidden">
        <div class="px-6 py-4 bg-purple-deep/95 text-white flex items-center justify-between">
            <h3 class="text-base font-bold text-white">Daftar Beban Tugas Staff (Tester, Skorer, Editor)</h3>
            <span class="text-xs text-purple-100 font-medium">Klik baris untuk melihat rincian tugas dan mengalihkan staff</span>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-sm font-medium">
                <thead>
                    <tr class="bg-slate-50 text-black font-semibold border-b border-[#EDE1FA]">
                        <th class="px-6 py-3.5 text-left font-semibold">Nama Staff</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Tugas Batch (Tester / Skorer)</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Tugas Editor (Kandidat)</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Prioritas KILAT</th>
                        <th class="px-6 py-3.5 text-right font-semibold">Total Tugas Aktif</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F3EAFB]">
                    @forelse($staffWorkloads as $item)
                        @php $s = $item['staff']; @endphp
                        <tr class="table-row-clickable hover:bg-slate-100 active:bg-slate-200 transition-colors duration-150 cursor-pointer" @click="togglePerson('s-{{ $s->id }}')">
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $s->name }}</td>
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $item['batch_count'] }} batch</td>
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $item['editor_count'] }} kandidat</td>
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $item['kilat_count'] }}</td>
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap text-right">{{ $item['total_active'] }} tugas</td>
                        </tr>
                        <tr x-show="expandedPersonId === 's-{{ $s->id }}'" x-cloak>
                            <td colspan="5" class="px-6 py-4 bg-slate-50">
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-xs font-medium text-slate-500 mb-2">Tugas Kolektif Batch (Tester / Skorer Manual / Skorer Software)</p>
                                        @if($item['batch_tasks']->isNotEmpty())
                                            <div class="space-y-2">
                                                @foreach($item['batch_tasks'] as $bt)
                                                    <div class="p-3 bg-white rounded-lg border border-slate-200 flex flex-wrap items-center justify-between gap-3">
                                                        <div>
                                                            <a href="{{ route('psychotest-bookings.show', $bt->booking_id) }}" class="text-xs font-medium text-black hover:text-purple-deep">
                                                                {{ $bt->role_label }} — {{ $bt->booking?->company?->name ?? $bt->booking?->client?->name ?? '-' }}
                                                            </a>
                                                            <p class="text-[11px] font-medium text-slate-500">
                                                                Tanggal Tes: {{ $bt->booking?->tanggal_dijadwalkan ? $bt->booking->tanggal_dijadwalkan->format('d/m/Y') : '-' }}
                                                            </p>
                                                        </div>
                                                        <button type="button"
                                                            @click="openReassign('batch_assignee', {{ $bt->id }}, '{{ addslashes($bt->role_label . ' - ' . ($bt->booking?->company?->name ?? $bt->booking?->client?->name ?? 'Booking')) }}', 'staff')"
                                                            class="px-3 py-1.5 rounded-lg bg-white border border-[#D9C2F0] text-purple-deep hover:bg-purple-50 text-xs font-semibold cursor-pointer">
                                                            Alihkan Staff
                                                        </button>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-xs font-medium text-slate-400">Tidak ada tugas batch aktif.</p>
                                        @endif
                                    </div>

                                    <div>
                                        <p class="text-xs font-medium text-slate-500 mb-2">Tugas Editor Per-Kandidat</p>
                                        @if($item['editor_candidates']->isNotEmpty())
                                            <div class="space-y-2">
                                                @foreach($item['editor_candidates'] as $ec)
                                                    <div class="p-3 bg-white rounded-lg border border-slate-200 flex flex-wrap items-center justify-between gap-3">
                                                        <div>
                                                            <a href="{{ route('psychotest-bookings.show', $ec->booking_id) }}" class="text-xs font-medium text-black hover:text-purple-deep">
                                                                Editor: {{ $ec->candidate_name }}{{ $ec->is_kilat ? ' [KILAT]' : '' }} — {{ $ec->booking?->company?->name ?? $ec->booking?->client?->name ?? '-' }}
                                                            </a>
                                                            <p class="text-[11px] font-medium text-slate-500">
                                                                Deadline Klien: {{ $ec->client_deadline_date ? $ec->client_deadline_date->format('d/m/Y') : '-' }}
                                                            </p>
                                                        </div>
                                                        <button type="button"
                                                            @click="openReassign('candidate_editor', {{ $ec->id }}, '{{ addslashes($ec->candidate_name) }}', 'staff')"
                                                            class="px-3 py-1.5 rounded-lg bg-white border border-[#D9C2F0] text-purple-deep hover:bg-purple-50 text-xs font-semibold cursor-pointer">
                                                            Alihkan Editor
                                                        </button>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-xs font-medium text-slate-400">Tidak ada tugas editor aktif.</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-10 text-center text-slate-400 text-xs font-medium">Belum ada Staff terdaftar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Reassign Tugas --}}
    <div x-show="reassignModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-[#1F0E38]/50 backdrop-blur-md"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-md rounded-2xl bg-white shadow-[0_30px_70px_rgba(74,35,128,0.2)] overflow-hidden">
                <div class="px-6 py-4 bg-purple-deep flex items-center justify-between text-white">
                    <h3 class="text-base font-semibold">Alihkan Penugasan</h3>
                    <button type="button" @click="reassignModalOpen = false" class="p-1 rounded-lg text-white/80 hover:text-white hover:bg-white/15 cursor-pointer">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('assignments.reassign-task') }}" class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="task_type" x-model="reassignTaskType">
                    <input type="hidden" name="target_id" x-model="reassignTargetId">

                    <div class="p-3.5 rounded-lg bg-slate-50 border border-slate-100">
                        <span class="text-xs font-medium text-slate-500 block">Tugas yang Dialihkan</span>
                        <span class="text-sm font-medium text-black" x-text="reassignTitle"></span>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-slate-500 mb-1 block">Pilih Petugas Pengganti *</label>
                        <select name="new_assignee_id" x-model="newAssigneeId" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black min-h-[42px]">
                            <option value="">-- Pilih Pengganti --</option>
                            <template x-if="reassignRoleKind === 'psikolog'">
                                <optgroup label="Psikolog">
                                    @foreach($counselors as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </optgroup>
                            </template>
                            <template x-if="reassignRoleKind === 'staff'">
                                <optgroup label="Staff">
                                    @foreach($staffUsers as $su)
                                        <option value="{{ $su->id }}">{{ $su->name }}</option>
                                    @endforeach
                                </optgroup>
                            </template>
                        </select>
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button type="button" @click="reassignModalOpen = false" class="px-5 py-2.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-sm shadow-xs cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-lg bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-sm shadow-xs cursor-pointer inline-flex items-center gap-2">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            <span>Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
