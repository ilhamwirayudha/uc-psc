@extends('layouts.dashboard')

@section('title', 'Kelola Staff — UC PSC')
@section('page-title', 'Kelola Staff')
@section('page-subtitle', 'Hanya Admin yang dapat mengelola akun staff')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <form method="GET" class="flex items-center gap-3 flex-1 max-w-lg">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-[#6B5B85]" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..."
                class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-[#D9C2F0] bg-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent">
        </div>
        <button type="submit" class="bg-purple-deep text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:opacity-90 transition">Cari</button>
    </form>
    <a href="{{ route('staff-management.create') }}" class="bg-orange text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:opacity-90 transition flex items-center gap-2 whitespace-nowrap">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Staff
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#F7F5FB] text-[#5B4A73]">
                    <th class="text-center px-4 py-3.5 font-semibold w-14">No.</th>
                    <th class="text-left px-6 py-3.5 font-semibold">Nama Staff</th>
                    <th class="text-left px-6 py-3.5 font-semibold">Email</th>
                    <th class="text-center px-6 py-3.5 font-semibold">Klien Ditugaskan</th>
                    <th class="text-center px-6 py-3.5 font-semibold">Tanggal Dibuat</th>
                    <th class="text-center px-6 py-3.5 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F3EAFB]">
                @forelse($staffs as $staff)
                <tr class="hover:bg-[#FDFBFF] transition">
                    <td class="text-center px-4 py-3.5 text-[#6B5B85]">{{ $loop->iteration + ($staffs->currentPage() - 1) * $staffs->perPage() }}</td>
                    <td class="px-6 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-purple-deep/10 flex items-center justify-center text-purple-deep text-xs font-bold">{{ substr($staff->name, 0, 1) }}</div>
                            <span class="font-semibold text-[#2A2035]">{{ $staff->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-3.5 text-[#6B5B85]">{{ $staff->email }}</td>
                    <td class="text-center px-6 py-3.5">
                        <a href="{{ route('staff-management.show', $staff) }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ ($staff->assigned_clients_count ?? 0) > 0 ? 'bg-purple-deep/10 text-purple-deep hover:bg-purple-deep/20' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }} transition">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            <span>{{ $staff->assigned_clients_count ?? 0 }} Klien</span>
                        </a>
                    </td>
                    <td class="text-center px-6 py-3.5 text-[#6B5B85]">{{ $staff->created_at->format('d M Y') }}</td>
                    <td class="px-6 py-3.5">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('staff-management.show', $staff) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-purple-deep text-white hover:opacity-90 transition shadow-xs" title="Kelola detail & penugasan klien">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <span>Kelola</span>
                            </a>
                            <form action="{{ route('staff-management.destroy', $staff) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus staff {{ $staff->name }}? Seluruh penugasan aktif staff ini akan otomatis dilepaskan.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-lg text-[#6B5B85] hover:text-red-500 hover:bg-red-50 transition cursor-pointer" title="Hapus Akun Staff">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center text-[#6B5B85]">Belum ada staff.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($staffs->hasPages())
    <div class="px-6 py-4 border-t border-[#EDE1FA]">{{ $staffs->links() }}</div>
    @endif
</div>
@endsection
