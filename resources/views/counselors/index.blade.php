@extends('layouts.dashboard')

@section('title', 'Konselor — UC PSC')
@section('page-title', 'Data Konselor')
@section('page-subtitle', 'Kelola data konselor / psikolog')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <form method="GET" class="flex items-center gap-3 flex-1 max-w-lg">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-[#6B5B85]" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, spesialisasi..."
                class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-[#D9C2F0] bg-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent">
        </div>
        <select name="status" class="px-3 py-2.5 rounded-xl border border-[#D9C2F0] bg-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#5B4A73]" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <option value="active" class="text-emerald-600 font-semibold bg-white" style="color: #059669;" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
            <option value="inactive" class="text-red-500 font-semibold bg-white" style="color: #ef4444;" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
            <option value="trashed" class="text-gray-500 bg-white" {{ request('status') === 'trashed' ? 'selected' : '' }}>Data Terhapus</option>
        </select>
        <button type="submit" class="bg-purple-deep text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:opacity-90 transition">Cari</button>
    </form>
    <a href="{{ route('counselors.create') }}" class="bg-orange text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:opacity-90 transition flex items-center gap-2 whitespace-nowrap">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Konselor
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#F7F5FB] text-[#5B4A73]">
                    <th class="text-center px-4 py-3.5 font-semibold w-14">No.</th>
                    <th class="text-left px-6 py-3.5 font-semibold">Nama</th>
                    <th class="text-left px-6 py-3.5 font-semibold">Telepon</th>
                    <th class="text-left px-6 py-3.5 font-semibold">Spesialisasi</th>
                    <th class="text-left px-6 py-3.5 font-semibold">SIPP</th>
                    <th class="text-center px-6 py-3.5 font-semibold">Status</th>
                    <th class="text-center px-6 py-3.5 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F3EAFB]">
                @forelse($counselors as $counselor)
                <tr class="hover:bg-[#FDFBFF] transition">
                    <td class="text-center px-4 py-3.5 text-[#6B5B85]">{{ $loop->iteration + ($counselors->currentPage() - 1) * $counselors->perPage() }}</td>
                    <td class="px-6 py-3.5 font-semibold text-[#2A2035]" x-data="{ edit: false }">
                        <div x-show="!edit" class="flex items-center gap-2 group cursor-pointer" @click="edit = true">
                            <span>{{ $counselor->name }}</span>
                            <button class="text-transparent group-hover:text-[#D9C2F0] hover:!text-purple-deep transition">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </button>
                        </div>
                        <form x-cloak x-show="edit" action="{{ route('counselors.update', $counselor) }}" method="POST" class="flex items-center gap-2">
                            @csrf @method('PUT')
                            <input type="text" name="name" value="{{ $counselor->name }}" class="px-2 py-1 border border-[#D9C2F0] rounded text-sm w-36 focus:outline-none focus:ring-1 focus:ring-purple-deep" required @click.outside="edit = false" @keydown.escape="edit = false">
                            <button type="submit" class="text-emerald-500 hover:text-emerald-700">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                            </button>
                        </form>
                    </td>
                    <td class="px-6 py-3.5 text-[#6B5B85]">{{ $counselor->phone ?? '-' }}</td>
                    <td class="px-6 py-3.5 text-[#6B5B85]">{{ $counselor->specialization ?? '-' }}</td>
                    <td class="px-6 py-3.5 text-[#6B5B85]">{{ $counselor->sipp_number ?? '-' }}</td>
                    <td class="text-center px-6 py-3.5">
                        @if($counselor->trashed())
                        <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-600">
                            Terhapus
                        </span>
                        @else
                        <form action="{{ route('counselors.update', $counselor) }}" method="POST">
                            @csrf @method('PUT')
                            <select name="status" onchange="this.form.submit()" class="px-2.5 py-1 rounded-full text-[11px] font-semibold focus:outline-none cursor-pointer transition {{ $counselor->status === 'active' ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-500' }}">
                                <option value="active" class="text-emerald-600 font-semibold bg-white" style="color: #059669; background-color: #ffffff;" {{ $counselor->status === 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive" class="text-red-500 font-semibold bg-white" style="color: #ef4444; background-color: #ffffff;" {{ $counselor->status === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </form>
                        @endif
                    </td>
                    <td class="px-6 py-3.5">
                        <div class="flex items-center justify-center gap-1.5">
                            @if($counselor->trashed())
                            <form action="{{ route('counselors.restore', $counselor->id) }}" method="POST" class="inline-flex items-center m-0 p-0">
                                @csrf
                                <button type="submit" class="bg-emerald-50 text-emerald-600 hover:bg-emerald-100 px-3 py-1.5 rounded-lg text-xs font-semibold transition" title="Kembalikan">
                                    Restore
                                </button>
                            </form>
                            <form action="{{ route('counselors.force-delete', $counselor->id) }}" method="POST" class="inline-flex items-center m-0 p-0" onsubmit="return confirm('Peringatan: Data ini akan dihapus permanen dan tidak bisa dikembalikan. Lanjutkan?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="bg-red-50 text-red-500 hover:bg-red-100 px-3 py-1.5 rounded-lg text-xs font-semibold transition" title="Hapus Permanen">
                                    Hapus
                                </button>
                            </form>
                            @else
                            <a href="{{ route('counselors.edit', $counselor) }}" class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-[#6B5B85] hover:text-purple-deep hover:bg-purple-deep/10 transition" title="Edit">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>
                            <form action="{{ route('counselors.destroy', $counselor) }}" method="POST" class="inline-flex items-center m-0 p-0" onsubmit="return confirm('Yakin ingin menghapus konselor ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-[#6B5B85] hover:text-red-500 hover:bg-red-50 transition cursor-pointer p-0 m-0 border-0 bg-transparent" title="Hapus">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-[#6B5B85]">
                        <svg class="mx-auto mb-3 text-[#D9C2F0]" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Belum ada data konselor.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($counselors->hasPages())
    <div class="px-6 py-4 border-t border-[#EDE1FA]">{{ $counselors->links() }}</div>
    @endif
</div>
@endsection
