@extends('layouts.dashboard')

@section('title', 'Detail Booking #' . $booking->id . ' — UC PSC')
@section('page-title', 'Detail Booking #' . $booking->id)
@section('back-url', route('bookings.index'))

@section('content')
<div 
    x-data="{ 
        clientModalOpen: false, 
        selectedClient: {{ json_encode($clientData) }} 
    }" 
    class="max-w-4xl space-y-6"
>
    {{-- Info Booking (Hapus Tombol Aksi) --}}
    <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6">
        <div class="flex items-center justify-between mb-6 pb-4 border-b border-[#EDE1FA]">
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
            <div class="text-xs text-[#827299]">
                Booking #{{ $booking->id }}
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4 text-sm">
            {{-- Klien: Klik Nama Klien Langsung Muncul Pop-Up Detail Klien --}}
            <div>
                <p class="text-[#6B5B85] text-xs font-medium mb-1">Klien</p>
                @if($booking->client)
                <button 
                    type="button" 
                    @click="clientModalOpen = true"
                    class="font-bold text-purple-deep hover:underline cursor-pointer flex items-center gap-1.5 text-sm text-left transition"
                    title="Klik untuk Buka Detail Klien"
                >
                    <span>{{ $booking->client->name }}</span>
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="opacity-60 shrink-0"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                </button>
                <span class="text-[11px] text-[#827299] capitalize block mt-0.5">
                    {{ $booking->client->jenis ?? 'individual' }}
                    @if($booking->client->pic_name) • PIC: {{ $booking->client->pic_name }} @endif
                </span>
                @else
                <p class="text-[#2A2035] font-semibold">-</p>
                @endif
            </div>

            {{-- Tanggal & Jam Dibuat --}}
            <div>
                <p class="text-[#6B5B85] text-xs font-medium mb-1">Tanggal & Jam Dibuat</p>
                <p class="text-[#2A2035] font-semibold flex items-center gap-2">
                    <span>{{ $booking->tanggal_booking_dibuat->format('d M Y') }}</span>
                    <span class="text-xs text-[#827299] font-mono bg-gray-50 px-2 py-0.5 rounded-md border border-gray-200">
                        {{ $jamDibuat }}
                    </span>
                </p>
            </div>

            {{-- Tanggal & Jam Dijadwalkan --}}
            <div>
                <p class="text-[#6B5B85] text-xs font-medium mb-1">Tanggal & Jam Dijadwalkan</p>
                @if($booking->tanggal_dijadwalkan)
                <p class="text-[#2A2035] font-semibold flex items-center gap-2 flex-wrap">
                    <span>{{ $booking->tanggal_dijadwalkan->format('d M Y') }}</span>
                    @if($jamSesi)
                    <span class="text-xs text-purple-deep font-semibold font-mono bg-purple-50 px-2 py-0.5 rounded-md border border-purple-200 flex items-center gap-1">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span>{{ $jamSesi }}</span>
                    </span>
                    @else
                    <span class="text-xs text-[#827299] font-mono bg-gray-50 px-2 py-0.5 rounded-md border border-gray-200">
                        Jam belum diatur
                    </span>
                    @endif
                </p>
                @else
                <p class="text-gray-400 italic">Belum Dijadwalkan</p>
                @endif
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
                <p class="text-xs text-[#827299]">{{ $booking->counselor->specialization ?? 'Konselor' }}</p>
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
                <div class="p-3 bg-[#FAF8FD] rounded-xl border border-[#EDE1FA] text-xs leading-relaxed text-[#2A2035] whitespace-pre-line font-mono">{{ $booking->notes }}</div>
            </div>
            @endif
        </div>

        {{-- Footer Detail Booking: Edit Jadwal, Hapus Data, Tutup --}}
        <div class="mt-6 pt-5 border-t border-[#EDE1FA] flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2 flex-wrap">
                {{-- Tombol Edit Jadwal --}}
                <a 
                    href="{{ route('bookings.edit', $booking) }}"
                    class="px-4 py-2 bg-purple-deep text-white text-xs font-bold rounded-xl hover:opacity-90 transition flex items-center gap-1.5 shadow-xs"
                >
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span>Edit Jadwal</span>
                </a>

                {{-- Tombol Hapus Data --}}
                <form action="{{ route('bookings.destroy', $booking) }}" method="POST" class="inline m-0 p-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data booking ini?')">
                    @csrf
                    @method('DELETE')
                    <button 
                        type="submit" 
                        class="px-4 py-2 bg-red-50 border border-red-200 text-red-600 text-xs font-bold rounded-xl hover:bg-red-100 transition cursor-pointer flex items-center gap-1.5"
                    >
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        <span>Hapus Data</span>
                    </button>
                </form>
            </div>

            {{-- Tombol Tutup --}}
            <a 
                href="{{ route('bookings.index') }}"
                class="px-5 py-2 bg-white border border-[#D9C2F0] text-[#5B4A73] text-xs font-bold rounded-xl hover:bg-[#F3EAFB] transition"
            >
                Tutup
            </a>
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

    {{-- ========================================================================= --}}
    {{-- POP-UP MODAL DETAIL KLIEN (SEMUA AKSES TOMBOL DIUBAH KE DALAM SINI)        --}}
    {{-- ========================================================================= --}}
    <div 
        x-show="clientModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-2xs"
        @keydown.escape.window="clientModalOpen = false"
    >
        <div 
            @click.away="clientModalOpen = false"
            class="bg-white w-full max-w-3xl rounded-3xl shadow-2xl border border-[#EDE1FA] overflow-hidden flex flex-col max-h-[92vh]"
        >
            {{-- Header Detail Klien --}}
            <div class="p-5 border-b border-[#EDE1FA] bg-gradient-to-r from-purple-50/70 via-[#FAF8FD] to-white flex items-center justify-between">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-purple-deep text-white flex items-center justify-center font-extrabold text-base shadow-xs">
                        <span x-text="selectedClient?.name ? selectedClient.name.charAt(0).toUpperCase() : 'K'"></span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="font-extrabold text-base text-[#2A2035]" x-text="selectedClient?.name || 'Detail Klien'"></h3>
                            <span 
                                class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-purple-deep/10 text-purple-deep"
                                x-text="selectedClient?.jenis || 'individual'"
                            ></span>
                            <span 
                                class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                                :class="{
                                    'bg-emerald-50 text-emerald-600': selectedClient?.status === 'completed' || selectedClient?.status === 'paid',
                                    'bg-blue-50 text-blue-600': selectedClient?.status === 'assigned' || selectedClient?.status === 'ongoing',
                                    'bg-amber-50 text-amber-600': selectedClient?.status === 'unpaid' || selectedClient?.status === 'needs_followup',
                                    'bg-gray-100 text-gray-600': selectedClient?.status === 'unassigned'
                                }"
                                x-text="selectedClient?.status_label || ''"
                            ></span>
                        </div>
                        <template x-if="selectedClient?.pic_name">
                            <p class="text-xs text-[#827299] mt-0.5">
                                PIC: <span class="font-bold text-[#5B4A73]" x-text="selectedClient.pic_name"></span>
                            </p>
                        </template>
                    </div>
                </div>

                <button 
                    type="button" 
                    @click="clientModalOpen = false"
                    class="w-8 h-8 rounded-full hover:bg-gray-100 text-gray-400 hover:text-gray-700 flex items-center justify-center transition cursor-pointer"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            {{-- Body Pop-Up Detail Klien --}}
            <div class="p-6 overflow-y-auto space-y-5 text-sm">

                {{-- 1. Profil & Informasi Kontak Klien --}}
                <div class="bg-[#FAF8FD] rounded-2xl p-4 border border-[#EDE1FA]">
                    <h4 class="font-bold text-xs uppercase tracking-wider text-[#5B4A73] pb-2.5 mb-3 border-b border-[#EDE1FA] flex items-center gap-1.5">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Biodata & Kontak Klien
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-xs">
                        <div>
                            <span class="text-[#827299] block font-medium">WhatsApp / Telepon</span>
                            <template x-if="selectedClient?.phone && selectedClient.phone !== '-'">
                                <a :href="'https://wa.me/' + selectedClient.phone.replace(/[^0-9]/g, '')" target="_blank" class="font-bold text-purple-deep hover:underline flex items-center gap-1 mt-0.5">
                                    <span x-text="selectedClient.phone"></span>
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                </a>
                            </template>
                            <template x-if="!selectedClient?.phone || selectedClient.phone === '-'">
                                <span class="font-semibold text-gray-400">-</span>
                            </template>
                        </div>
                        <div>
                            <span class="text-[#827299] block font-medium">Email</span>
                            <span class="font-semibold text-[#2A2035]" x-text="selectedClient?.email || '-'"></span>
                        </div>
                        <div>
                            <span class="text-[#827299] block font-medium">Jenis Kelamin</span>
                            <span class="font-semibold text-[#2A2035]" x-text="selectedClient?.gender || '-'"></span>
                        </div>
                        <div>
                            <span class="text-[#827299] block font-medium">Lahir & Usia</span>
                            <span class="font-semibold text-[#2A2035]" x-text="selectedClient?.dob || '-'"></span>
                            <template x-if="selectedClient?.age">
                                <span class="text-[#827299] block text-[11px]" x-text="'(' + selectedClient.age + ')'"></span>
                            </template>
                        </div>
                        <div>
                            <span class="text-[#827299] block font-medium">Pendidikan & Pekerjaan</span>
                            <span class="font-semibold text-[#2A2035]" x-text="(selectedClient?.education || '-') + ' • ' + (selectedClient?.occupation || '-')"></span>
                        </div>
                        <div>
                            <span class="text-[#827299] block font-medium">Wilayah</span>
                            <span class="font-semibold text-[#2A2035]" x-text="(selectedClient?.city ? selectedClient.city + ', ' : '') + (selectedClient?.province || selectedClient?.country || '-')"></span>
                        </div>
                        <div class="sm:col-span-2 lg:col-span-3">
                            <span class="text-[#827299] block font-medium">Alamat Lengkap</span>
                            <span class="font-normal text-[#2A2035]" x-text="selectedClient?.address || '-'"></span>
                        </div>
                    </div>
                </div>

                {{-- 2. Layanan & Penugasan Tim --}}
                <div class="bg-white rounded-2xl p-4 border border-[#EDE1FA]">
                    <h4 class="font-bold text-xs uppercase tracking-wider text-[#5B4A73] pb-2.5 mb-3 border-b border-[#EDE1FA] flex items-center gap-1.5">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Layanan & Penugasan Tim
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="bg-[#FAF8FD] p-3 rounded-xl border border-[#EDE1FA]">
                            <span class="text-[#827299] block font-medium">Layanan yang Diambil</span>
                            <p class="font-bold text-purple-deep mt-0.5 text-sm" x-text="selectedClient?.service_type ? selectedClient.service_type.toUpperCase() : '-'"></p>
                            <p class="text-[11px] text-[#5B4A73]" x-text="selectedClient?.counseling_type || '-'"></p>
                        </div>
                        <div class="bg-[#FAF8FD] p-3 rounded-xl border border-[#EDE1FA]">
                            <span class="text-[#827299] block font-medium">Staff Pendamping</span>
                            <p class="font-bold text-[#2A2035] mt-0.5 text-sm" x-text="selectedClient?.assigned_staff?.name || 'Belum Diassign'"></p>
                            <p class="text-[11px] text-[#827299]" x-text="selectedClient?.assigned_staff?.email || ''"></p>
                        </div>
                    </div>
                </div>

                {{-- 3. Riwayat Booking Klien & Akses Edit Booking dari Sini --}}
                <div class="bg-white rounded-2xl p-4 border border-[#EDE1FA]">
                    <div class="flex items-center justify-between pb-2.5 mb-3 border-b border-[#EDE1FA]">
                        <h4 class="font-bold text-xs uppercase tracking-wider text-[#5B4A73] flex items-center gap-1.5">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            Riwayat Booking Klien
                        </h4>
                        <a 
                            href="{{ route('clients.show', $booking->client_id ?? 0) }}"
                            class="px-2.5 py-1 bg-orange text-white rounded-lg text-xs font-bold hover:opacity-90 transition cursor-pointer flex items-center gap-1"
                        >
                            <span>+ Buat Booking Klien Ini</span>
                        </a>
                    </div>

                    <template x-if="selectedClient?.bookings && selectedClient.bookings.length > 0">
                        <div class="divide-y divide-[#EDE1FA]">
                            <template x-for="b in selectedClient.bookings" :key="b.id">
                                <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-extrabold text-purple-deep" x-text="'#' + b.id"></span>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase" :class="b.kategori === 'konseling' ? 'bg-purple-deep/10 text-purple-deep' : 'bg-orange/10 text-orange'" x-text="b.kategori"></span>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold" :class="b.status === 'selesai' ? 'bg-emerald-50 text-emerald-600' : 'bg-blue-50 text-blue-600'" x-text="b.status_label"></span>
                                        </div>
                                        <p class="text-xs text-[#827299] mt-0.5">
                                            Jadwal: <span class="font-medium text-[#2A2035]" x-text="b.tanggal_dijadwalkan"></span>
                                            <template x-if="b.counselor_name">
                                                <span class="ml-2 text-purple-deep font-semibold" x-text="'• Konselor: ' + b.counselor_name"></span>
                                            </template>
                                        </p>
                                    </div>

                                    {{-- Akses Tombol Edit Booking dari Pop-Up Detail Klien --}}
                                    <div class="flex items-center gap-2">
                                        <a 
                                            :href="'/bookings/' + b.id + '/edit'"
                                            class="px-3 py-1.5 bg-purple-deep text-white rounded-lg text-xs font-bold hover:opacity-90 transition cursor-pointer flex items-center gap-1"
                                        >
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            <span>Edit Booking Ini</span>
                                        </a>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="!selectedClient?.bookings || selectedClient.bookings.length === 0">
                        <p class="text-xs text-gray-500 italic">Belum ada riwayat booking untuk klien ini.</p>
                    </template>
                </div>

                {{-- 4. Riwayat Hasil Tes / Asesmen --}}
                <template x-if="selectedClient?.test_results && selectedClient.test_results.length > 0">
                    <div class="bg-white rounded-2xl p-4 border border-[#EDE1FA]">
                        <h4 class="font-bold text-xs uppercase tracking-wider text-[#5B4A73] pb-2.5 mb-3 border-b border-[#EDE1FA] flex items-center gap-1.5">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            Berkas & Hasil Asesmen Psikotes
                        </h4>
                        <div class="divide-y divide-[#EDE1FA]">
                            <template x-for="t in selectedClient.test_results" :key="t.id">
                                <div class="py-2.5 flex items-center justify-between text-xs">
                                    <div>
                                        <p class="font-bold text-[#2A2035]" x-text="t.test_name"></p>
                                        <p class="text-[11px] text-[#827299] line-clamp-1" x-text="t.summary || 'Tidak ada ringkasan'"></p>
                                    </div>
                                    <a :href="t.url" target="_blank" class="px-2.5 py-1 bg-purple-50 text-purple-deep rounded-lg text-xs font-bold hover:bg-purple-100 transition">
                                        Buka Berkas
                                    </a>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

            </div>

            {{-- Footer Detail Klien: SEMUA AKSES TOMBOL DIUBAH KE SINI --}}
            <div class="p-4 border-t border-[#EDE1FA] bg-[#FAF8FD] flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2 flex-wrap">
                    {{-- Akses Buka Halaman Klien Lengkap --}}
                    <template x-if="selectedClient?.show_url">
                        <a 
                            :href="selectedClient.show_url" 
                            class="px-4 py-2 bg-purple-deep text-white text-xs font-bold rounded-xl hover:opacity-90 transition cursor-pointer flex items-center gap-1.5 shadow-xs"
                        >
                            <span>Buka Halaman Lengkap Klien</span>
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        </a>
                    </template>

                    {{-- Akses Edit Klien --}}
                    <template x-if="selectedClient?.edit_url">
                        <a 
                            :href="selectedClient.edit_url" 
                            class="px-3.5 py-2 border border-[#D9C2F0] bg-white text-[#5B4A73] text-xs font-bold rounded-xl hover:bg-[#F7F5FB] transition cursor-pointer flex items-center gap-1.5"
                        >
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            <span>Edit Profil Klien</span>
                        </a>
                    </template>

                    {{-- Akses Buat Booking Baru --}}
                    <template x-if="selectedClient?.show_url">
                        <a 
                            :href="selectedClient.show_url"
                            class="px-3.5 py-2 bg-orange text-white text-xs font-bold rounded-xl hover:opacity-90 transition cursor-pointer flex items-center gap-1.5 shadow-xs"
                        >
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            <span>+ Booking Baru</span>
                        </a>
                    </template>
                </div>

                {{-- Tutup Modal Klien --}}
                <button 
                    type="button" 
                    @click="clientModalOpen = false"
                    class="px-5 py-2 bg-white border border-[#D9C2F0] text-[#5B4A73] text-xs font-bold rounded-xl hover:bg-[#F3EAFB] transition cursor-pointer"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
