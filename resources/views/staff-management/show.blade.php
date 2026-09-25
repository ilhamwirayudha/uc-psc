@extends('layouts.dashboard')

@section('title', 'Detail Staff — ' . $staff->name . ' — UC PSC')
@section('page-title', 'Detail & Penugasan Staff')
@section('back-url', route('staff-management.index'))

@section('content')
<div x-data="{ 
    assignModalOpen: false, 
    reassignModalOpen: false, 
    unassignModalOpen: false,
    selectedClient: null,
    selectedClientName: '',
    openReassign(client) {
        this.selectedClient = client;
        this.selectedClientName = client.name;
        this.reassignModalOpen = true;
    },
    openUnassign(client) {
        this.selectedClient = client;
        this.selectedClientName = client.name;
        this.unassignModalOpen = true;
    }
}" class="space-y-6">

    {{-- Quick Action Header --}}
    <div class="flex items-center justify-end">
        <button type="button" @click="assignModalOpen = true" class="bg-purple-deep text-white px-4 py-2 rounded-xl text-xs font-semibold hover:opacity-90 transition shadow-xs flex items-center gap-1.5 cursor-pointer">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Tugaskan Klien Baru</span>
        </button>
    </div>

    {{-- Staff Profile Card & Quick Stats --}}
    <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl grad-purple flex items-center justify-center text-white text-2xl font-bold shadow-md flex-shrink-0">
                    {{ substr($staff->name, 0, 1) }}
                </div>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-xl font-bold text-purple-deep">{{ $staff->name }}</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-deep/10 text-purple-deep uppercase">
                            {{ $staff->role }}
                        </span>
                    </div>
                    <p class="text-xs text-[#6B5B85] mt-1 flex items-center gap-1.5">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <span>{{ $staff->email }}</span>
                        <span>·</span>
                        <span>Terdaftar sejak {{ $staff->created_at ? $staff->created_at->format('d M Y') : '-' }}</span>
                    </p>
                </div>
            </div>

            {{-- Stat badges --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <div class="bg-[#F7F5FB] rounded-xl p-3.5 border border-[#EDE1FA] text-center">
                    <p class="text-[11px] font-semibold text-[#6B5B85]">Klien Aktif</p>
                    <p class="text-xl font-bold text-purple-deep mt-0.5">{{ $activeClients->count() }}</p>
                </div>
                <div class="bg-[#F7F5FB] rounded-xl p-3.5 border border-[#EDE1FA] text-center">
                    <p class="text-[11px] font-semibold text-[#6B5B85]">Penugasan Kasus</p>
                    <p class="text-xl font-bold text-purple-deep mt-0.5">{{ \App\Models\Booking::where('staff_penguji_id', $staff->id)->orWhere('staff_koreksi_id', $staff->id)->orWhere('staff_pelapor_id', $staff->id)->count() }}</p>
                </div>
                <div class="bg-[#F7F5FB] rounded-xl p-3.5 border border-[#EDE1FA] text-center col-span-2 sm:col-span-1">
                    <p class="text-[11px] font-semibold text-[#6B5B85]">Riwayat Penugasan</p>
                    <p class="text-xl font-bold text-purple-deep mt-0.5">{{ $historyAssignments->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Section: Daftar Klien Sedang Ditugaskan --}}
    <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
        <div class="px-6 py-4 border-b border-[#EDE1FA] flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white">
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="font-bold text-base text-purple-deep">Klien yang Sedang Ditugaskan</h3>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-deep/10 text-purple-deep">
                        {{ $activeClients->count() }} Klien
                    </span>
                </div>
                <p class="text-xs text-[#6B5B85] mt-0.5">Kelola penugasan klien yang saat ini ditangani oleh {{ $staff->name }}</p>
            </div>
            
            <a href="{{ route('assignments.index') }}" class="text-xs text-orange font-semibold hover:underline">
                Kelola Semua Penugasan →
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-[#F7F5FB] text-[#5B4A73]">
                        <th class="text-center px-4 py-3.5 font-semibold w-12">No.</th>
                        <th class="text-left px-5 py-3.5 font-semibold">Nama Klien</th>
                        <th class="text-left px-4 py-3.5 font-semibold">Kontak</th>
                        <th class="text-center px-4 py-3.5 font-semibold">Layanan</th>
                        <th class="text-center px-4 py-3.5 font-semibold">Status</th>
                        <th class="text-center px-5 py-3.5 font-semibold">Aksi Pengelolaan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F3EAFB]">
                    @forelse($activeClients as $client)
                    <tr class="hover:bg-[#FDFBFF] transition">
                        <td class="text-center px-4 py-3.5 text-[#6B5B85] font-medium">{{ $loop->iteration }}</td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-purple-deep/10 flex items-center justify-center text-purple-deep text-xs font-bold flex-shrink-0">
                                    {{ substr($client->name, 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <a href="{{ route('clients.show', $client) }}" class="font-bold text-[#2A2035] hover:text-purple-deep transition truncate block">
                                        {{ $client->name }}
                                    </a>
                                    <span class="text-[11px] text-[#827299]">
                                        {{ $client->jenis === 'company' ? 'Perusahaan' : 'Perorangan' }} · {{ $client->counseling_type ?? 'Umum' }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-xs text-[#6B5B85]">
                            <p>{{ $client->phone ?? '-' }}</p>
                            <p class="truncate text-[11px]">{{ $client->email ?? '-' }}</p>
                        </td>
                        <td class="text-center px-4 py-3.5">
                            <span class="inline-block px-2.5 py-1 rounded-md text-[11px] font-bold {{ $client->service_type === 'konseling' ? 'bg-indigo-50 text-indigo-700' : ($client->service_type === 'psikotes' ? 'bg-amber-50 text-amber-700' : 'bg-gray-100 text-gray-600') }}">
                                {{ $client->service_type ? ucfirst($client->service_type) : 'Belum Booking' }}
                            </span>
                        </td>
                        <td class="text-center px-4 py-3.5">
                            <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                {{ $client->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : 
                                  ($client->status === 'ongoing' ? 'bg-blue-50 text-blue-700' : 
                                  ($client->status === 'assigned' ? 'bg-purple-50 text-purple-700' : 'bg-gray-100 text-gray-700')) }}">
                                {{ str_replace('_', ' ', $client->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <div class="inline-flex items-center justify-center gap-1.5">
                                {{-- Tombol Lihat --}}
                                <a href="{{ route('clients.show', $client) }}" 
                                    class="p-1.5 rounded-lg text-[#6B5B85] hover:text-purple-deep hover:bg-purple-deep/10 transition" title="Lihat Detail Klien">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>

                                {{-- Tombol Pindahkan Staff (Reassign) --}}
                                <button type="button" @click="openReassign({ id: {{ $client->id }}, name: '{{ addslashes($client->name) }}' })"
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-amber-50 text-amber-700 hover:bg-amber-100 transition cursor-pointer" title="Pindahkan klien ke staff lain">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 1l4 4-4 4"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><path d="M7 23l-4-4 4-4"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                                    <span>Pindahkan</span>
                                </button>

                                {{-- Tombol Hapus/Lepaskan Klien (Unassign) --}}
                                <button type="button" @click="openUnassign({ id: {{ $client->id }}, name: '{{ addslashes($client->name) }}' })"
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-red-50 text-red-600 hover:bg-red-100 transition cursor-pointer" title="Lepaskan penugasan klien dari staff ini">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    <span>Lepas Klien</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-[#6B5B85]">
                            <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-purple-deep/10 flex items-center justify-center text-purple-deep">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                            <p class="text-sm font-semibold text-[#2A2035]">Belum ada klien yang ditugaskan ke staff ini.</p>
                            <p class="text-xs text-[#827299] mt-1">Klik tombol "Tugaskan Klien Baru" di atas untuk menambahkan klien.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Section: Riwayat Penugasan Lampau --}}
    @if($historyAssignments->count() > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-[#EDE1FA]">
            <div>
                <h3 class="font-bold text-purple-deep text-sm">Riwayat Penugasan Lampau</h3>
                <p class="text-xs text-[#827299]">Daftar klien yang sebelumnya pernah ditugaskan ke {{ $staff->name }}</p>
            </div>
            <span class="text-xs font-semibold text-[#6B5B85]">{{ $historyAssignments->count() }} Riwayat</span>
        </div>

        <div class="divide-y divide-[#F3EAFB] max-h-64 overflow-y-auto custom-scrollbar">
            @foreach($historyAssignments as $history)
            <div class="py-3 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 text-xs font-bold">
                        {{ substr($history->client->name ?? 'K', 0, 1) }}
                    </div>
                    <div>
                        <p class="text-xs font-bold text-[#2A2035]">{{ $history->client->name ?? 'Klien Terhapus' }}</p>
                        <p class="text-[11px] text-[#827299]">
                            Ditugaskan {{ $history->assigned_at ? $history->assigned_at->format('d M Y') : '-' }} s/d {{ $history->ended_at ? $history->ended_at->format('d M Y') : '-' }}
                            @if($history->notes)
                            · <span class="italic text-purple-deep">{{ $history->notes }}</span>
                            @endif
                        </p>
                    </div>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-600">Selesai / Dialihkan</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ===== MODAL 1: TUGASKAN KLIEN BARU KE STAFF INI ===== --}}
    <div x-show="assignModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-xs transition-opacity" @click="assignModalOpen = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:w-full sm:max-w-md border border-[#EDE1FA] p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-[#EDE1FA]">
                    <h3 class="text-base font-bold text-purple-deep">Tugaskan Klien ke {{ $staff->name }}</h3>
                    <button type="button" @click="assignModalOpen = false" class="text-[#827299] hover:text-purple-deep">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                @if($unassignedClients->count() > 0)
                <form action="#" method="POST" x-ref="directAssignForm"
                    @submit.prevent="
                        const clientId = $refs.unassignedClientSelect.value;
                        if (!clientId) { alert('Pilih klien terlebih dahulu'); return; }
                        $el.action = '{{ url('assignments') }}/' + clientId + '/assign';
                        $el.submit();
                    "
                    class="space-y-4">
                    @csrf
                    <input type="hidden" name="staff_id" value="{{ $staff->id }}">

                    <div>
                        <label class="block text-xs font-semibold text-[#5B4A73] mb-1.5">Pilih Klien yang Belum Ditugaskan <span class="text-red-400">*</span></label>
                        <select x-ref="unassignedClientSelect" required class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] text-sm text-[#5B4A73] focus:outline-none focus:ring-2 focus:ring-purple-deep">
                            <option value="">-- Pilih Klien --</option>
                            @foreach($unassignedClients as $uClient)
                            <option value="{{ $uClient->id }}">{{ $uClient->name }} ({{ $uClient->service_type ? ucfirst($uClient->service_type) : 'Belum Booking' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#5B4A73] mb-1.5">Catatan Penugasan (Opsional)</label>
                        <input type="text" name="notes" placeholder="Contoh: Tangani tindak lanjut asesmen..." class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-2">
                        <button type="button" @click="assignModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-[#6B5B85] border border-[#D9C2F0] hover:bg-[#F7F5FB] transition">
                            Batal
                        </button>
                        <button type="submit" class="bg-purple-deep text-white px-5 py-2 rounded-xl text-xs font-semibold hover:opacity-90 transition shadow-xs">
                            Simpan Penugasan
                        </button>
                    </div>
                </form>
                @else
                <div class="py-6 text-center text-[#6B5B85] text-xs">
                    <p class="font-semibold text-sm text-[#2A2035] mb-1">Tidak ada klien tanpa penugasan</p>
                    <p>Semua klien saat ini sudah memiliki staff penanggung jawab.</p>
                </div>
                <div class="flex justify-end pt-2">
                    <button type="button" @click="assignModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-[#6B5B85] border border-[#D9C2F0]">
                        Tutup
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ===== MODAL 2: PINDAHKAN KLIEN KE STAFF LAIN (REASSIGN) ===== --}}
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

                <form action="#" method="POST" x-ref="reassignForm"
                    @submit.prevent="
                        if (!selectedClient) return;
                        $el.action = '{{ url('assignments') }}/' + selectedClient.id + '/reassign';
                        $el.submit();
                    "
                    class="space-y-4">
                    @csrf

                    <div class="bg-[#F7F5FB] p-3 rounded-xl border border-[#EDE1FA] text-xs">
                        <p class="text-[#827299]">Klien yang akan dipindahkan:</p>
                        <p class="text-sm font-bold text-purple-deep mt-0.5" x-text="selectedClientName"></p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#5B4A73] mb-1.5">Pilih Staff Pengganti <span class="text-red-400">*</span></label>
                        <select name="staff_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] text-sm text-[#5B4A73] focus:outline-none focus:ring-2 focus:ring-purple-deep">
                            <option value="">-- Pilih Staff --</option>
                            @foreach($otherStaffs as $oStaff)
                            <option value="{{ $oStaff->id }}">{{ $oStaff->name }} ({{ $oStaff->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#5B4A73] mb-1.5">Alasan / Catatan Pemindahan</label>
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

    {{-- ===== MODAL 3: HAPUS / LEPAS KLIEN DARI STAFF (UNASSIGN) ===== --}}
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

                <form action="#" method="POST" x-ref="unassignForm"
                    @submit.prevent="
                        if (!selectedClient) return;
                        $el.action = '{{ url('assignments') }}/' + selectedClient.id + '/unassign';
                        $el.submit();
                    "
                    class="space-y-4">
                    @csrf

                    <p class="text-xs text-[#5B4A73] leading-relaxed">
                        Anda akan melepaskan penugasan klien <strong class="text-purple-deep" x-text="selectedClientName"></strong> dari staff <strong>{{ $staff->name }}</strong>.
                    </p>

                    <div>
                        <label class="block text-xs font-semibold text-[#5B4A73] mb-1.5">Catatan / Alasan Pelepasan (Opsional)</label>
                        <input type="text" name="notes" placeholder="Contoh: Selesai pendampingan / Reorganisasi..." class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
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
