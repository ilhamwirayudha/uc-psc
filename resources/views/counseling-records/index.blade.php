@extends('layouts.dashboard')

@section('title', 'Catatan Konseling — UC PSC')
@section('page-title', 'Catatan Konseling')
@section('page-subtitle', 'Pencatatan sesi konseling via WhatsApp, online, atau tatap muka')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <form method="GET" class="flex items-center gap-3 flex-1 max-w-xl">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-[#6B5B85]" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama klien..."
                class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-[#D9C2F0] bg-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent">
        </div>
        <select name="type" class="px-3 py-2.5 rounded-xl border border-[#D9C2F0] bg-white text-sm text-[#5B4A73]" onchange="this.form.submit()">
            <option value="">Semua Tipe</option>
            <option value="whatsapp" {{ request('type') === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
            <option value="tatap_muka" {{ request('type') === 'tatap_muka' ? 'selected' : '' }}>Tatap Muka</option>
            <option value="online" {{ request('type') === 'online' ? 'selected' : '' }}>Online</option>
        </select>
        <select name="status" class="px-3 py-2.5 rounded-xl border border-[#D9C2F0] bg-white text-sm text-[#5B4A73]" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Terjadwal</option>
            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
        </select>
        <button type="submit" class="bg-purple-deep text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:opacity-90 transition">Cari</button>
    </form>
    <a href="{{ route('counseling-records.create') }}" class="bg-orange text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:opacity-90 transition flex items-center gap-2 whitespace-nowrap">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Catatan
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#F7F5FB] text-[#5B4A73]">
                    <th class="text-center px-4 py-3.5 font-semibold w-14">No.</th>
                    <th class="text-left px-6 py-3.5 font-semibold">Klien</th>
                    <th class="text-left px-6 py-3.5 font-semibold">Konselor</th>
                    <th class="text-center px-6 py-3.5 font-semibold">Tipe</th>
                    <th class="text-center px-6 py-3.5 font-semibold">Jadwal</th>
                    <th class="text-center px-6 py-3.5 font-semibold">Status</th>
                    <th class="text-left px-6 py-3.5 font-semibold">Admin</th>
                    <th class="text-center px-6 py-3.5 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F3EAFB]">
                @forelse($records as $record)
                <tr class="hover:bg-[#FDFBFF] transition">
                    <td class="text-center px-4 py-3.5 text-[#6B5B85]">{{ $loop->iteration + ($records->currentPage() - 1) * $records->perPage() }}</td>
                    <td class="px-6 py-3.5 font-semibold text-[#2A2035]">{{ $record->client->name ?? '-' }}</td>
                    <td class="px-6 py-3.5 text-[#6B5B85]">{{ $record->counselor->name ?? '-' }}</td>
                    <td class="text-center px-6 py-3.5">
                        <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $record->type === 'whatsapp' ? 'bg-emerald-50 text-emerald-600' : ($record->type === 'online' ? 'bg-blue-50 text-blue-600' : 'bg-purple-deep/10 text-purple-deep') }}">
                            {{ $record->type === 'whatsapp' ? 'WhatsApp' : ($record->type === 'online' ? 'Online' : 'Tatap Muka') }}
                        </span>
                    </td>
                    <td class="text-center px-6 py-3.5 text-[#6B5B85]">{{ $record->scheduled_at ? $record->scheduled_at->format('d M Y, H:i') : '-' }}</td>
                    <td class="text-center px-6 py-3.5">
                        <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $record->status === 'completed' ? 'bg-emerald-50 text-emerald-600' : ($record->status === 'scheduled' ? 'bg-amber-50 text-amber-600' : 'bg-red-50 text-red-500') }}">
                            {{ $record->status === 'completed' ? 'Selesai' : ($record->status === 'scheduled' ? 'Terjadwal' : 'Dibatalkan') }}
                        </span>
                    </td>
                    <td class="px-6 py-3.5 text-[#6B5B85]">{{ $record->admin->name ?? '-' }}</td>
                    <td class="px-6 py-3.5">
                        <div class="flex items-center justify-center gap-1.5">
                            <a href="{{ route('counseling-records.edit', $record) }}" class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-[#6B5B85] hover:text-purple-deep hover:bg-purple-deep/10 transition" title="Edit">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>
                            <form action="{{ route('counseling-records.destroy', $record) }}" method="POST" class="inline-flex items-center m-0 p-0" onsubmit="return confirm('Yakin ingin menghapus catatan ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-[#6B5B85] hover:text-red-500 hover:bg-red-50 transition cursor-pointer p-0 m-0 border-0 bg-transparent" title="Hapus">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-[#6B5B85]">Belum ada catatan konseling.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($records->hasPages())
    <div class="px-6 py-4 border-t border-[#EDE1FA]">{{ $records->links() }}</div>
    @endif
</div>
@endsection
