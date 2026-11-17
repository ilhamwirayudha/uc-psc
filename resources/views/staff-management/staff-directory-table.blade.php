@extends('layouts.dashboard')

@section('title', 'Kelola Akun Tim — UC PSC')
@section('page-title', 'Kelola Akun Tim')
@section('page-subtitle', 'Kelola akun login Staff (Tester/Skorer/Editor) dan Psikolog UC PSC')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <form method="GET" class="flex flex-wrap items-center gap-3 flex-1 max-w-xl">
        <div class="relative flex-1 min-w-[200px]">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..."
                class="w-full pl-10 pr-4 py-2.5 rounded-lg glass-input text-sm font-medium text-black focus:outline-none focus:ring-2 focus:ring-purple-deep">
        </div>
        <select name="role" onchange="this.form.submit()" class="px-3.5 py-2.5 rounded-lg glass-input text-sm font-medium text-black">
            <option value="">Semua Role</option>
            <option value="staff" {{ $roleFilter === 'staff' ? 'selected' : '' }}>Staff</option>
            <option value="psikolog" {{ $roleFilter === 'psikolog' ? 'selected' : '' }}>Psikolog</option>
        </select>
        <button type="submit" class="px-4 py-2.5 rounded-lg bg-white border border-[#D9C2F0] text-black hover:text-purple-deep text-sm font-semibold shadow-xs transition cursor-pointer">Cari</button>
    </form>

    <a href="{{ route('staff-management.create') }}" class="bg-orange hover:bg-orange/90 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-xs transition flex items-center gap-2 whitespace-nowrap">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        <span>Tambah Akun Tim</span>
    </a>
</div>

<div class="glass-card rounded-2xl overflow-hidden">
    <div class="px-6 py-4 bg-purple-deep/95 text-white flex items-center justify-between border-b border-purple-900/30">
        <h3 class="text-base font-bold text-white">Daftar Akun Staff &amp; Psikolog</h3>
        <span class="text-xs text-purple-100 font-medium">{{ $staffs->total() }} Akun</span>
    </div>

    <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-sm font-medium">
            <thead>
                <tr class="bg-slate-50 text-black font-semibold select-none border-b border-[#EDE1FA]">
                    <th class="text-center px-4 py-3.5 font-semibold w-14">No.</th>
                    <th class="text-left px-6 py-3.5 font-semibold">Nama</th>
                    <th class="text-left px-6 py-3.5 font-semibold">Email</th>
                    <th class="text-left px-6 py-3.5 font-semibold">Role</th>
                    <th class="text-left px-6 py-3.5 font-semibold">Tugas Aktif</th>
                    <th class="text-left px-6 py-3.5 font-semibold">Google Calendar</th>
                    <th class="text-left px-6 py-3.5 font-semibold">Tanggal Dibuat</th>
                    <th class="text-right px-6 py-3.5 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F3EAFB]">
                @forelse($staffs as $staff)
                    <tr class="table-row-clickable hover:bg-slate-100 active:bg-slate-200 transition-colors duration-150 cursor-pointer" onclick="window.location='{{ route('staff-management.show', $staff) }}'">
                        <td class="text-center px-4 py-3.5 text-black text-xs font-medium">{{ $loop->iteration + ($staffs->currentPage() - 1) * $staffs->perPage() }}</td>
                        <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $staff->name }}</td>
                        <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $staff->email }}</td>
                        <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $staff->isPsikolog() ? 'Psikolog' : 'Staff' }}</td>
                        <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $taskCounts[$staff->id] ?? 0 }} tugas</td>
                        <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $staff->hasGoogleCalendarConnected() ? 'Terhubung' : 'Belum Terhubung' }}</td>
                        <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">{{ $staff->created_at ? $staff->created_at->format('d/m/Y H:i') : '-' }}</td>
                        <td class="px-6 py-3.5 text-right whitespace-nowrap" onclick="event.stopPropagation()">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('staff-management.show', $staff) }}" class="px-3 py-1.5 rounded-lg bg-white border border-[#D9C2F0] text-black hover:text-purple-deep text-xs font-medium">
                                    Detail
                                </a>
                                <form action="{{ route('staff-management.destroy', $staff) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus akun {{ $staff->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-500 hover:bg-red-600 text-white text-xs font-medium cursor-pointer inline-flex items-center gap-1">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        <span>Hapus</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400 text-xs font-medium">Belum ada akun Staff atau Psikolog.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($staffs->hasPages())
        <div class="px-6 py-4 border-t border-[#EDE1FA]">{{ $staffs->links() }}</div>
    @endif
</div>
@endsection
