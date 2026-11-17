@extends('layouts.dashboard')

@section('title', 'Hari Libur Nasional — UC PSC')
@section('page-title', 'Hari Libur Nasional')
@section('page-subtitle', 'Kelola tanggal merah untuk perhitungan hari kerja SLA pengiriman laporan ke klien')

@section('content')
@php
    $nextSort = function ($col) use ($sort, $dir) {
        if ($sort !== $col) {
            return request()->fullUrlWithQuery(['sort' => $col, 'dir' => 'asc']);
        }
        if ($dir === 'asc') {
            return request()->fullUrlWithQuery(['sort' => $col, 'dir' => 'desc']);
        }
        return request()->fullUrlWithQuery(['sort' => null, 'dir' => null]);
    };
@endphp

<div x-data="{
    addModalOpen: false,
    editModalOpen: false,
    deleteModalOpen: false,
    unsavedModalOpen: false,
    pendingTarget: null,
    editUrl: '',
    deleteUrl: '',
    deleteName: '',
    addForm: { date: '', name: '', notes: '' },
    editInitial: {},
    editForm: { date: '', name: '', notes: '' },
    openAdd() {
        this.addForm = { date: '', name: '', notes: '' };
        this.addModalOpen = true;
    },
    openEdit(h) {
        this.editUrl = h.update_url;
        this.editForm = { date: h.date || '', name: h.name || '', notes: h.notes || '' };
        this.editInitial = JSON.parse(JSON.stringify(this.editForm));
        this.editModalOpen = true;
    },
    openDelete(h) {
        this.deleteUrl = h.delete_url;
        this.deleteName = h.name + ' (' + h.date_display + ')';
        this.deleteModalOpen = true;
    },
    closeAdd() {
        if (this.addForm.date || this.addForm.name || this.addForm.notes) {
            this.pendingTarget = 'add';
            this.unsavedModalOpen = true;
            return;
        }
        this.addModalOpen = false;
    },
    closeEdit() {
        if (JSON.stringify(this.editForm) !== JSON.stringify(this.editInitial)) {
            this.pendingTarget = 'edit';
            this.unsavedModalOpen = true;
            return;
        }
        this.editModalOpen = false;
    },
    discard() {
        if (this.pendingTarget === 'add') this.addModalOpen = false;
        if (this.pendingTarget === 'edit') this.editModalOpen = false;
        this.pendingTarget = null;
        this.unsavedModalOpen = false;
    }
}" class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form method="GET" action="{{ route('holidays.index') }}" class="flex items-center gap-3 flex-1 max-w-md">
            <div class="relative flex-1">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama hari libur..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-lg glass-input text-sm font-medium text-black focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
            <button type="submit" class="px-4 py-2.5 rounded-lg bg-white border border-[#D9C2F0] text-black hover:text-purple-deep text-sm font-semibold shadow-xs cursor-pointer">Cari</button>
        </form>

        <button type="button" @click="openAdd()" class="px-4.5 py-2.5 rounded-lg bg-orange hover:bg-orange/90 text-white text-sm font-semibold shadow-xs transition cursor-pointer inline-flex items-center gap-2">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Tambah Hari Libur</span>
        </button>
    </div>

    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="px-6 py-4 bg-purple-deep/95 text-white flex items-center justify-between border-b border-purple-900/30">
            <h3 class="text-base font-bold text-white">Daftar Hari Libur Nasional</h3>
            <span class="text-xs text-purple-100 font-medium">{{ $holidays->total() }} Tanggal</span>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-sm font-medium">
                <thead>
                    <tr class="bg-slate-50 text-black font-semibold select-none border-b border-[#EDE1FA]">
                        <th class="px-6 py-3.5 text-left font-semibold">
                            <a href="{{ $nextSort('date') }}" class="inline-flex items-center gap-1.5 hover:text-purple-deep cursor-pointer">
                                <span>Tanggal</span>
                                <span class="text-xs {{ $sort === 'date' ? 'text-purple-deep' : 'text-slate-400' }}">
                                    {{ $sort === 'date' ? ($dir === 'asc' ? '↑' : '↓') : '↕' }}
                                </span>
                            </a>
                        </th>
                        <th class="px-6 py-3.5 text-left font-semibold">
                            <a href="{{ $nextSort('name') }}" class="inline-flex items-center gap-1.5 hover:text-purple-deep cursor-pointer">
                                <span>Keterangan Hari Libur</span>
                                <span class="text-xs {{ $sort === 'name' ? 'text-purple-deep' : 'text-slate-400' }}">
                                    {{ $sort === 'name' ? ($dir === 'asc' ? '↑' : '↓') : '↕' }}
                                </span>
                            </a>
                        </th>
                        <th class="px-6 py-3.5 text-left font-semibold">Catatan</th>
                        <th class="px-6 py-3.5 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F3EAFB]">
                    @forelse($holidays as $holiday)
                        @php
                            $payload = [
                                'date' => $holiday->date->format('Y-m-d'),
                                'date_display' => $holiday->date->format('d/m/Y'),
                                'name' => $holiday->name,
                                'notes' => $holiday->notes,
                                'update_url' => route('holidays.update', $holiday),
                                'delete_url' => route('holidays.destroy', $holiday),
                            ];
                        @endphp
                        <tr class="table-row-clickable hover:bg-slate-100 active:bg-slate-200 transition-colors duration-150 cursor-pointer" @click="openEdit(@js($payload))">
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $holiday->date->format('d/m/Y') }}</td>
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $holiday->name }}</td>
                            <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $holiday->notes ?: '-' }}</td>
                            <td class="px-6 py-3.5 text-right whitespace-nowrap" @click.stop>
                                <div class="inline-flex items-center gap-2">
                                    <button type="button" @click="openEdit(@js($payload))" class="px-3 py-1.5 rounded-lg bg-white border border-[#D9C2F0] text-black hover:text-purple-deep text-xs font-medium cursor-pointer">Ubah</button>
                                    <button type="button" @click="openDelete(@js($payload))" class="px-3 py-1.5 rounded-lg bg-red-500 hover:bg-red-600 text-white text-xs font-medium cursor-pointer inline-flex items-center gap-1">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        <span>Hapus</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-slate-400 text-xs font-medium">Belum ada tanggal libur nasional terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($holidays->hasPages())
            <div class="px-6 py-4 border-t border-[#EDE1FA]">{{ $holidays->links() }}</div>
        @endif
    </div>

    {{-- Modal Tambah --}}
    <div x-show="addModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-[#1F0E38]/50 backdrop-blur-md"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-md rounded-2xl bg-white shadow-[0_30px_70px_rgba(74,35,128,0.2)] overflow-hidden">
                <div class="px-6 py-4 bg-purple-deep flex items-center justify-between text-white">
                    <h3 class="text-base font-semibold">Tambah Hari Libur Nasional</h3>
                    <button type="button" @click="closeAdd()" class="p-1 rounded-lg text-white/80 hover:text-white hover:bg-white/15 cursor-pointer">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('holidays.store') }}" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="text-xs font-medium text-slate-500 mb-1 block">Tanggal *</label>
                        <input type="date" name="date" x-model="addForm.date" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black min-h-[42px]">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-500 mb-1 block">Nama Hari Libur *</label>
                        <input type="text" name="name" x-model="addForm.name" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black min-h-[42px]">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-500 mb-1 block">Catatan</label>
                        <textarea name="notes" x-model="addForm.notes" rows="2" class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black"></textarea>
                    </div>
                    <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button type="button" @click="closeAdd()" class="px-5 py-2.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-sm shadow-xs cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-lg bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-sm shadow-xs cursor-pointer inline-flex items-center gap-2">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            <span>Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-[#1F0E38]/50 backdrop-blur-md"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-md rounded-2xl bg-white shadow-[0_30px_70px_rgba(74,35,128,0.2)] overflow-hidden">
                <div class="px-6 py-4 bg-purple-deep flex items-center justify-between text-white">
                    <h3 class="text-base font-semibold">Ubah Hari Libur Nasional</h3>
                    <button type="button" @click="closeEdit()" class="p-1 rounded-lg text-white/80 hover:text-white hover:bg-white/15 cursor-pointer">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <form method="POST" :action="editUrl" class="p-6 space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="text-xs font-medium text-slate-500 mb-1 block">Tanggal *</label>
                        <input type="date" name="date" x-model="editForm.date" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black min-h-[42px]">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-500 mb-1 block">Nama Hari Libur *</label>
                        <input type="text" name="name" x-model="editForm.name" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black min-h-[42px]">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-500 mb-1 block">Catatan</label>
                        <textarea name="notes" x-model="editForm.notes" rows="2" class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black"></textarea>
                    </div>
                    <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button type="button" @click="closeEdit()" class="px-5 py-2.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-sm shadow-xs cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-lg bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-sm shadow-xs cursor-pointer inline-flex items-center gap-2">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            <span>Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Delete --}}
    <div x-show="deleteModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-[#1F0E38]/50 backdrop-blur-md"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-md rounded-2xl bg-white shadow-[0_30px_70px_rgba(74,35,128,0.2)] overflow-hidden">
                <div class="px-6 py-4 bg-purple-deep flex items-center justify-between text-white">
                    <h3 class="text-base font-semibold">Hapus Hari Libur</h3>
                    <button type="button" @click="deleteModalOpen = false" class="p-1 rounded-lg text-white/80 hover:text-white hover:bg-white/15 cursor-pointer">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <div class="p-6">
                    <div class="p-4 rounded-lg bg-slate-50 border border-slate-100">
                        <p class="text-sm font-medium text-black">Yakin ingin menghapus tanggal libur <span x-text="deleteName"></span>?</p>
                    </div>
                </div>
                <form method="POST" :action="deleteUrl" class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                    @csrf @method('DELETE')
                    <button type="button" @click="deleteModalOpen = false" class="px-5 py-2.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-sm shadow-xs cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold text-sm shadow-xs cursor-pointer inline-flex items-center gap-2">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        <span>Hapus</span>
                    </button>
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
                        <p class="text-sm font-medium text-black">Anda memiliki perubahan yang belum disimpan. Jika melanjutkan, perubahan akan dibatalkan.</p>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="unsavedModalOpen = false" class="px-5 py-2.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-sm shadow-xs cursor-pointer">Batal</button>
                    <button type="button" @click="discard()" class="px-5 py-2.5 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold text-sm shadow-xs cursor-pointer">Ya, Tinggalkan</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
