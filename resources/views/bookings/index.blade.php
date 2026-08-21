@extends('layouts.dashboard')

@section('title', 'Daftar Booking — UC PSC')
@section('page-title', 'Booking')
@section('page-subtitle', 'Kelola booking konseling & psikotes')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama klien..."
                class="px-4 py-2 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-purple-deep bg-white min-w-[200px]">
            <select name="kategori" class="px-3 py-2 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-purple-deep bg-white">
                <option value="">Semua Kategori</option>
                <option value="konseling" {{ request('kategori') === 'konseling' ? 'selected' : '' }}>Konseling</option>
                <option value="psikotes" {{ request('kategori') === 'psikotes' ? 'selected' : '' }}>Psikotes</option>
            </select>
            <select name="status" class="px-3 py-2 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-purple-deep bg-white">
                <option value="">Semua Status</option>
                <option value="baru" {{ request('status') === 'baru' ? 'selected' : '' }}>Baru</option>
                <option value="lanjutan" {{ request('status') === 'lanjutan' ? 'selected' : '' }}>Lanjutan</option>
                <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-purple-deep text-white text-sm font-semibold rounded-xl hover:bg-purple-deep/90 transition">Filter</button>
        </form>
        <a href="{{ route('bookings.create') }}" class="px-4 py-2 bg-orange text-white text-sm font-semibold rounded-xl hover:bg-orange/90 transition shadow-sm flex-shrink-0 text-center">
            + Booking Baru
        </a>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-[#F7F5FB] text-[#6B5B85] text-xs uppercase tracking-wider">
                        <th class="px-6 py-3 text-left font-semibold">ID</th>
                        <th class="px-6 py-3 text-left font-semibold">Klien</th>
                        <th class="px-6 py-3 text-left font-semibold">Kategori</th>
                        <th class="px-6 py-3 text-left font-semibold">Tanggal Dibuat</th>
                        <th class="px-6 py-3 text-left font-semibold">Dijadwalkan</th>
                        <th class="px-6 py-3 text-left font-semibold">Status</th>
                        <th class="px-6 py-3 text-left font-semibold">Konselor/Staff</th>
                        <th class="px-6 py-3 text-center font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F3EAFB]">
                    @forelse($bookings as $booking)
                    <tr class="hover:bg-[#FDFCFE] transition">
                        <td class="px-6 py-3.5 font-semibold text-[#2A2035]">
                            #{{ $booking->id }}
                            @if($booking->follow_up_of_booking_id)
                            <span class="text-[10px] text-[#6B5B85] block">↳ dari #{{ $booking->follow_up_of_booking_id }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-3.5">
                            <p class="font-semibold text-[#2A2035]">{{ $booking->client->name ?? '-' }}</p>
                            @if($booking->participants->count() > 0)
                            <p class="text-[10px] text-[#6B5B85]">{{ $booking->participants->count() }} peserta</p>
                            @endif
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                {{ $booking->kategori === 'konseling' ? 'bg-purple-deep/10 text-purple-deep' : 'bg-orange/10 text-orange' }}">
                                {{ $booking->kategori }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-[#6B5B85]">{{ $booking->tanggal_booking_dibuat->format('d M Y') }}</td>
                        <td class="px-6 py-3.5 text-[#6B5B85]">{{ $booking->tanggal_dijadwalkan ? $booking->tanggal_dijadwalkan->format('d M Y') : '-' }}</td>
                        <td class="px-6 py-3.5">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold
                                {{ $booking->status === 'selesai' ? 'bg-emerald-50 text-emerald-600' :
                                   ($booking->status === 'baru' ? 'bg-blue-50 text-blue-600' :
                                   ($booking->status === 'lanjutan' ? 'bg-amber-50 text-amber-600' : 'bg-gray-100 text-gray-600')) }}">
                                {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-[#6B5B85] text-xs">
                            @if($booking->kategori === 'konseling' && $booking->counselor)
                                {{ $booking->counselor->name }}
                            @elseif($booking->kategori === 'psikotes')
                                @if($booking->staffPenguji) P: {{ $booking->staffPenguji->name }}<br> @endif
                                @if($booking->staffKoreksi) K: {{ $booking->staffKoreksi->name }}<br> @endif
                                @if($booking->staffPelapor) L: {{ $booking->staffPelapor->name }} @endif
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-3.5 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('bookings.show', $booking) }}" class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-[#6B5B85] hover:text-purple-deep hover:bg-purple-deep/10 transition" title="Detail">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                <a href="{{ route('bookings.edit', $booking) }}" class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-[#6B5B85] hover:text-purple-deep hover:bg-purple-deep/10 transition" title="Edit">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </a>
                                @if($booking->status !== 'selesai')
                                <form action="{{ route('bookings.follow-up', $booking) }}" method="POST" class="inline-flex items-center m-0 p-0">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-amber-500 hover:text-amber-700 hover:bg-amber-50 transition cursor-pointer p-0 m-0 border-0 bg-transparent" title="Buat Follow-Up">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                    </button>
                                </form>
                                @endif
                                <form action="{{ route('bookings.destroy', $booking) }}" method="POST" class="inline-flex items-center m-0 p-0" onsubmit="return confirm('Hapus booking ini?')">
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
                        <td colspan="8" class="px-6 py-10 text-center text-[#6B5B85]">Belum ada booking.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bookings->hasPages())
        <div class="px-6 py-4 border-t border-[#EDE1FA]">
            {{ $bookings->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
