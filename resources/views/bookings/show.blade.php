@extends('layouts.dashboard')

@section('title', 'Detail Booking #' . $booking->id . ' — UC PSC')
@section('page-title', 'Detail Booking #' . $booking->id)
@section('page-subtitle', 'Informasi lengkap booking')

@section('content')
<div class="max-w-4xl space-y-6">
    {{-- Info Booking --}}
    <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 rounded-lg text-xs font-bold uppercase
                    {{ $booking->kategori === 'konseling' ? 'bg-purple-deep/10 text-purple-deep' : 'bg-orange/10 text-orange' }}">
                    {{ $booking->kategori }}
                </span>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold
                    {{ $booking->status === 'selesai' ? 'bg-emerald-50 text-emerald-600' :
                       ($booking->status === 'baru' ? 'bg-blue-50 text-blue-600' :
                       ($booking->status === 'lanjutan' ? 'bg-amber-50 text-amber-600' : 'bg-gray-100 text-gray-600')) }}">
                    {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('bookings.edit', $booking) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-blue-600 border border-blue-200 hover:bg-blue-50 transition">Edit</a>
                @if($booking->status !== 'selesai')
                <form action="{{ route('bookings.follow-up', $booking) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-amber-600 border border-amber-200 hover:bg-amber-50 transition">Buat Follow-Up</button>
                </form>
                @endif
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-[#6B5B85] text-xs font-medium mb-1">Klien</p>
                <p class="text-[#2A2035] font-semibold">{{ $booking->client->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-[#6B5B85] text-xs font-medium mb-1">Tanggal Dibuat</p>
                <p class="text-[#2A2035] font-semibold">{{ $booking->tanggal_booking_dibuat->format('d M Y') }}</p>
            </div>
            <div>
                <p class="text-[#6B5B85] text-xs font-medium mb-1">Tanggal Dijadwalkan</p>
                <p class="text-[#2A2035] font-semibold">{{ $booking->tanggal_dijadwalkan ? $booking->tanggal_dijadwalkan->format('d M Y') : '-' }}</p>
            </div>
            @if($booking->followUpOf)
            <div>
                <p class="text-[#6B5B85] text-xs font-medium mb-1">Lanjutan Dari</p>
                <p class="text-[#2A2035] font-semibold">
                    <a href="{{ route('bookings.show', $booking->followUpOf) }}" class="text-purple-deep hover:underline">Booking #{{ $booking->follow_up_of_booking_id }}</a>
                </p>
            </div>
            @endif

            @if($booking->kategori === 'konseling' && $booking->counselor)
            <div>
                <p class="text-[#6B5B85] text-xs font-medium mb-1">Konselor</p>
                <p class="text-[#2A2035] font-semibold">{{ $booking->counselor->name }}</p>
            </div>
            @endif

            @if($booking->kategori === 'psikotes')
            <div>
                <p class="text-[#6B5B85] text-xs font-medium mb-1">Staff Penguji</p>
                <p class="text-[#2A2035] font-semibold">{{ $booking->staffPenguji->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-[#6B5B85] text-xs font-medium mb-1">Staff Koreksi</p>
                <p class="text-[#2A2035] font-semibold">{{ $booking->staffKoreksi->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-[#6B5B85] text-xs font-medium mb-1">Staff Pelapor</p>
                <p class="text-[#2A2035] font-semibold">{{ $booking->staffPelapor->name ?? '-' }}</p>
            </div>
            @endif

            @if($booking->paymentTransaction)
            <div>
                <p class="text-[#6B5B85] text-xs font-medium mb-1">Pembayaran</p>
                <p class="text-[#2A2035] font-semibold">
                    Rp {{ number_format($booking->paymentTransaction->jumlah, 0, ',', '.') }}
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold ml-1
                        {{ $booking->paymentTransaction->status === 'paid' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">
                        {{ ucfirst($booking->paymentTransaction->status) }}
                    </span>
                </p>
            </div>
            @endif

            @if($booking->notes)
            <div class="md:col-span-2">
                <p class="text-[#6B5B85] text-xs font-medium mb-1">Catatan</p>
                <p class="text-[#2A2035]">{{ $booking->notes }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Peserta (Company) --}}
    @if($booking->participants->count() > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
        <div class="px-6 py-4 border-b border-[#EDE1FA]">
            <h3 class="font-bold text-purple-deep">Peserta ({{ $booking->participants->count() }} orang)</h3>
        </div>
        <div class="divide-y divide-[#F3EAFB]">
            @foreach($booking->participants as $i => $p)
            <div class="px-6 py-3 flex items-center gap-3">
                <span class="w-6 h-6 rounded-full bg-purple-deep/10 flex items-center justify-center text-purple-deep text-[10px] font-bold flex-shrink-0">{{ $i + 1 }}</span>
                <p class="text-sm text-[#2A2035]">{{ $p->nama_peserta }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Follow-ups --}}
    @if($booking->followUps->count() > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
        <div class="px-6 py-4 border-b border-[#EDE1FA]">
            <h3 class="font-bold text-purple-deep">Follow-Up ({{ $booking->followUps->count() }})</h3>
        </div>
        <div class="divide-y divide-[#F3EAFB]">
            @foreach($booking->followUps as $fu)
            <div class="px-6 py-3.5 flex items-center justify-between">
                <div>
                    <a href="{{ route('bookings.show', $fu) }}" class="text-sm font-semibold text-purple-deep hover:underline">Booking #{{ $fu->id }}</a>
                    <p class="text-xs text-[#6B5B85]">{{ $fu->tanggal_booking_dibuat->format('d M Y') }}</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold
                    {{ $fu->status === 'selesai' ? 'bg-emerald-50 text-emerald-600' :
                       ($fu->status === 'baru' ? 'bg-blue-50 text-blue-600' : 'bg-amber-50 text-amber-600') }}">
                    {{ ucfirst(str_replace('_', ' ', $fu->status)) }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div>
        <a href="{{ route('bookings.index') }}" class="px-4 py-2 text-sm font-semibold text-[#6B5B85] border border-[#D9C2F0] rounded-xl hover:bg-[#F7F5FB] transition">← Kembali</a>
    </div>
</div>
@endsection
