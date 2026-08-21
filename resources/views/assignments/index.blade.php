@extends('layouts.dashboard')

@section('title', 'Kelola Penugasan — UC PSC')
@section('page-title', 'Kelola Penugasan Staff')

@section('content')
<div x-data="{
    reassignModalOpen: false,
    unassignModalOpen: false,
    selectedClient: null,
    selectedClientName: '',
    currentStaffName: '',
    openReassign(client, staffName) {
        this.selectedClient = client;
        this.selectedClientName = client.name;
        this.currentStaffName = staffName;
        this.reassignModalOpen = true;
    },
    openUnassign(client, staffName) {
        this.selectedClient = client;
        this.selectedClientName = client.name;
        this.currentStaffName = staffName;
        this.unassignModalOpen = true;
    }
}" class="space-y-6">

    {{-- ========================================================================= --}}
    {{-- 1. BAGIAN ATAS: DAFTAR KLIEN YANG SEDANG DITUGASKAN                       --}}
    {{-- ========================================================================= --}}
    <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
        <div class="px-6 py-4 border-b border-[#EDE1FA] flex items-center justify-between">
            <h3 class="font-bold text-base text-purple-deep">Daftar Klien yang Sedang Ditugaskan</h3>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-deep/10 text-purple-deep">
                {{ $assignedClients->count() }} Klien Ter-assign
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-[#F7F5FB] text-[#5B4A73]">
                        <th class="text-center px-4 py-3.5 font-semibold w-14">No.</th>
                        <th class="text-left px-5 py-3.5 font-semibold">Nama Klien</th>
                        <th class="text-center px-4 py-3.5 font-semibold">Layanan</th>
                        <th class="text-left px-5 py-3.5 font-semibold">Staff Penanggung Jawab</th>
                        <th class="text-center px-4 py-3.5 font-semibold">Status Klien</th>
                        <th class="text-center px-5 py-3.5 font-semibold">Aksi Pengelolaan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F3EAFB]">
                    @forelse($assignedClients as $aClient)
                    <tr class="hover:bg-[#FDFBFF] transition">
                        <td class="text-center px-4 py-3.5 text-[#6B5B85] font-medium">{{ $loop->iteration }}</td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-purple-deep/10 flex items-center justify-center text-purple-deep text-xs font-bold flex-shrink-0">
                                    {{ substr($aClient->name, 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <a href="{{ route('clients.show', $aClient) }}" class="font-bold text-[#2A2035] hover:text-purple-deep transition truncate block">
                                        {{ $aClient->name }}
                                    </a>
                                    <p class="text-[11px] text-[#827299] truncate">{{ $aClient->phone ?? 'Tanpa nomor' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="text-center px-4 py-3.5">
                            <span class="inline-block px-2.5 py-0.5 rounded text-[11px] font-bold {{ $aClient->service_type === 'konseling' ? 'bg-indigo-50 text-indigo-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ ucfirst($aClient->service_type ?? 'konseling') }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-orange/20 flex items-center justify-center text-orange text-xs font-bold flex-shrink-0">
                                    {{ substr($aClient->assignedStaff->name ?? 'S', 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    @if($aClient->assignedStaff)
                                    <a href="{{ route('staff-management.show', $aClient->assignedStaff) }}" class="text-xs font-bold text-purple-deep hover:underline truncate block">
                                        {{ $aClient->assignedStaff->name }}
                                    </a>
                                    @else
                                    <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-center px-4 py-3.5">
                            <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                {{ $aClient->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : 
                                  ($aClient->status === 'ongoing' ? 'bg-blue-50 text-blue-700' : 
                                  ($aClient->status === 'assigned' ? 'bg-purple-50 text-purple-700' : 'bg-gray-100 text-gray-700')) }}">
                                {{ str_replace('_', ' ', $aClient->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <div class="inline-flex items-center justify-center gap-1.5">
                                {{-- Detail Klien --}}
                                <a href="{{ route('clients.show', $aClient) }}" 
                                    class="p-1.5 rounded-lg text-[#6B5B85] hover:text-purple-deep hover:bg-purple-deep/10 transition" title="Lihat Detail Klien">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>

                                {{-- Pindahkan (Reassign) --}}
                                <button type="button" 
                                    @click="openReassign({ id: {{ $aClient->id }}, name: '{{ addslashes($aClient->name) }}' }, '{{ addslashes($aClient->assignedStaff->name ?? 'Staff') }}')"
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-amber-50 text-amber-700 hover:bg-amber-100 transition cursor-pointer" title="Alihkan klien ke staff lain">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 1l4 4-4 4"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><path d="M7 23l-4-4 4-4"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                                    <span>Pindahkan</span>
                                </button>

                                {{-- Lepaskan (Unassign) --}}
                                <button type="button" 
                                    @click="openUnassign({ id: {{ $aClient->id }}, name: '{{ addslashes($aClient->name) }}' }, '{{ addslashes($aClient->assignedStaff->name ?? 'Staff') }}')"
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-red-50 text-red-600 hover:bg-red-100 transition cursor-pointer" title="Hapus/lepaskan klien dari staff penanggung jawab">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    <span>Lepas</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-[#6B5B85]">Belum ada klien yang ditugaskan ke staff.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 2. BAGIAN BAWAH: RINGKASAN PER STAFF & KLIEN MENUNGGU PENUGASAN            --}}
    {{-- ========================================================================= --}}
    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Ringkasan Staff --}}
        <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-[#EDE1FA] flex items-center justify-between">
                <h3 class="font-bold text-purple-deep">Ringkasan per Staff</h3>
                <a href="{{ route('staff-management.index') }}" class="text-xs text-orange font-semibold hover:underline">Kelola Staff →</a>
            </div>
            <div class="divide-y divide-[#F3EAFB] max-h-96 overflow-y-auto custom-scrollbar">
                @forelse($staffSummary as $staff)
                <div class="px-6 py-3.5 flex items-center justify-between hover:bg-[#FAF8FD] transition">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-purple-deep/10 flex items-center justify-center text-purple-deep text-xs font-bold flex-shrink-0">
                            {{ substr($staff->name, 0, 1) }}
                        </div>
                        <div class="min-w-0">
                            <a href="{{ route('staff-management.show', $staff) }}" class="text-sm font-semibold text-[#2A2035] hover:text-purple-deep truncate block">
                                {{ $staff->name }}
                            </a>
                            <p class="text-xs text-[#6B5B85] truncate">{{ $staff->email }}</p>
                        </div>
                    </div>
                    <a href="{{ route('staff-management.show', $staff) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-purple-deep/10 text-purple-deep hover:bg-purple-deep/20 transition flex-shrink-0">
                        <span>{{ $staff->active_clients_count }}</span>
                        <span class="text-[10px]">klien</span>
                    </a>
                </div>
                @empty
                <div class="px-6 py-6 text-center text-[#6B5B85] text-sm">Belum ada staff terdaftar.</div>
                @endforelse
            </div>
        </div>

        {{-- Klien Belum Ditugaskan --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-[#EDE1FA] flex items-center justify-between">
                <h3 class="font-bold text-purple-deep">Klien Menunggu Penugasan</h3>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $unassignedClients->count() > 0 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                    {{ $unassignedClients->count() }} Klien
                </span>
            </div>

            <div class="divide-y divide-[#F3EAFB] max-h-96 overflow-y-auto custom-scrollbar">
                @forelse($unassignedClients as $client)
                <div class="px-6 py-4" x-data="{ open: false }">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-purple-deep/10 flex items-center justify-center text-purple-deep font-bold flex-shrink-0">
                                {{ substr($client->name, 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <a href="{{ route('clients.show', $client) }}" class="text-sm font-semibold text-[#2A2035] hover:text-purple-deep truncate block">
                                    {{ $client->name }}
                                </a>
                                <p class="text-xs text-[#6B5B85] truncate">
                                    {{ ucfirst(str_replace('_', ' ', $client->source)) }}
                                    · <span class="font-medium text-purple-deep">{{ ucfirst($client->service_type ?? 'konseling') }}</span>
                                    · Terdaftar {{ $client->created_at ? $client->created_at->format('d M Y') : '-' }}
                                </p>
                            </div>
                        </div>
                        <button @click="open = !open" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-purple-deep text-white hover:opacity-90 transition cursor-pointer flex-shrink-0">
                            <span x-text="open ? 'Tutup' : 'Tugaskan Staff'"></span>
                        </button>
                    </div>

                    <form x-cloak x-show="open" x-transition action="{{ route('assignments.assign', $client) }}" method="POST" class="mt-3 flex flex-col sm:flex-row items-stretch sm:items-end gap-2.5 bg-[#F7F5FB] p-3 rounded-xl border border-[#EDE1FA]">
                        @csrf
                        <div class="flex-1">
                            <label class="text-[11px] font-semibold text-[#5B4A73] mb-1 block">Pilih Staff <span class="text-red-400">*</span></label>
                            <select name="staff_id" required class="w-full px-3 py-1.5 border border-[#D9C2F0] rounded-lg text-xs bg-white focus:outline-none focus:ring-1 focus:ring-purple-deep">
                                <option value="">-- Pilih Staff --</option>
                                @foreach($allStaff as $staff)
                                <option value="{{ $staff->id }}">{{ $staff->name }} ({{ $staff->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-1">
                            <label class="text-[11px] font-semibold text-[#5B4A73] mb-1 block">Catatan Penugasan</label>
                            <input type="text" name="notes" class="w-full px-3 py-1.5 border border-[#D9C2F0] rounded-lg text-xs bg-white focus:outline-none focus:ring-1 focus:ring-purple-deep" placeholder="Catatan opsional...">
                        </div>
                        <button type="submit" class="px-4 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-700 transition flex-shrink-0 cursor-pointer">
                            Simpan
                        </button>
                    </form>
                </div>
                @empty
                <div class="px-6 py-10 text-center">
                    <div class="w-12 h-12 mx-auto mb-2 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <p class="text-sm font-semibold text-[#2A2035]">Semua klien sudah ditugaskan!</p>
                    <p class="text-xs text-[#6B5B85] mt-0.5">Tidak ada klien yang menunggu penugasan saat ini.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ===== MODAL REASSIGN ===== --}}
    <div x-show="reassignModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-xs transition-opacity" @click="reassignModalOpen = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:w-full sm:max-w-md border border-[#EDE1FA] p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-[#EDE1FA]">
                    <h3 class="text-base font-bold text-purple-deep">Pindahkan Klien ke Staff Lain</h3>
                    <button type="button" @click="reassignModalOpen = false" class="text-[#827299] hover:text-purple-deep">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                <form action="#" method="POST" x-ref="globalReassignForm"
                    @submit.prevent="
                        if (!selectedClient) return;
                        $el.action = '{{ url('assignments') }}/' + selectedClient.id + '/reassign';
                        $el.submit();
                    "
                    class="space-y-4">
                    @csrf

                    <div class="bg-[#F7F5FB] p-3 rounded-xl border border-[#EDE1FA] text-xs">
                        <p class="text-[#827299]">Klien:</p>
                        <p class="text-sm font-bold text-purple-deep mt-0.5" x-text="selectedClientName"></p>
                        <p class="text-[11px] text-[#6B5B85] mt-1">Staff saat ini: <span class="font-semibold" x-text="currentStaffName"></span></p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#5B4A73] mb-1.5">Pilih Staff Baru <span class="text-red-400">*</span></label>
                        <select name="staff_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] text-sm text-[#5B4A73] focus:outline-none focus:ring-2 focus:ring-purple-deep">
                            <option value="">-- Pilih Staff --</option>
                            @foreach($allStaff as $stf)
                            <option value="{{ $stf->id }}">{{ $stf->name }} ({{ $stf->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#5B4A73] mb-1.5">Catatan / Alasan Pemindahan</label>
                        <input type="text" name="notes" placeholder="Contoh: Reassignment tugas operasional..." class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-2">
                        <button type="button" @click="reassignModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-[#6B5B85] border border-[#D9C2F0] hover:bg-[#F7F5FB] transition">
                            Batal
                        </button>
                        <button type="submit" class="bg-amber-600 text-white px-5 py-2 rounded-xl text-xs font-semibold hover:opacity-90 transition shadow-xs">
                            Pindahkan Klien
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===== MODAL UNASSIGN ===== --}}
    <div x-show="unassignModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-xs transition-opacity" @click="unassignModalOpen = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:w-full sm:max-w-md border border-[#EDE1FA] p-6 space-y-4">
                <div class="flex items-start gap-3 pb-3 border-b border-[#EDE1FA]">
                    <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-[#2A2035]">Lepaskan Klien dari Staff?</h3>
                        <p class="text-xs text-[#827299] mt-0.5">Klien akan dikembalikan ke status belum ditugaskan (*unassigned*)</p>
                    </div>
                </div>

                <form action="#" method="POST" x-ref="globalUnassignForm"
                    @submit.prevent="
                        if (!selectedClient) return;
                        $el.action = '{{ url('assignments') }}/' + selectedClient.id + '/unassign';
                        $el.submit();
                    "
                    class="space-y-4">
                    @csrf

                    <p class="text-xs text-[#5B4A73] leading-relaxed">
                        Anda akan melepaskan penugasan klien <strong class="text-purple-deep" x-text="selectedClientName"></strong> dari staff <strong x-text="currentStaffName"></strong>.
                    </p>

                    <div>
                        <label class="block text-xs font-semibold text-[#5B4A73] mb-1.5">Catatan / Alasan Pelepasan (Opsional)</label>
                        <input type="text" name="notes" placeholder="Contoh: Selesai pendampingan / Penyesuaian tugas..." class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-2">
                        <button type="button" @click="unassignModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-[#6B5B85] border border-[#D9C2F0] hover:bg-[#F7F5FB] transition">
                            Batal
                        </button>
                        <button type="submit" class="bg-red-600 text-white px-5 py-2 rounded-xl text-xs font-semibold hover:bg-red-700 transition shadow-xs">
                            Ya, Lepaskan Klien
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
