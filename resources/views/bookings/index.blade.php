@extends('layouts.dashboard')

@section('title', 'Daftar Booking — UC PSC')
@section('page-title', 'Booking')
@section('page-subtitle', 'Kelola booking konseling & psikotes')

@section('content')
<div 
    x-data="bookingManager()"
    x-cloak
    class="space-y-6"
>
    {{-- Header & Filter Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama klien..."
                class="px-4 py-2 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white min-w-[200px]">
            <select name="kategori" class="px-3 py-2 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">
                <option value="">Semua Kategori</option>
                <option value="konseling" {{ request('kategori') === 'konseling' ? 'selected' : '' }}>Konseling</option>
                <option value="psikotes" {{ request('kategori') === 'psikotes' ? 'selected' : '' }}>Psikotes</option>
            </select>
            <select name="status" class="px-3 py-2 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">
                <option value="">Semua Status</option>
                <option value="baru" {{ request('status') === 'baru' ? 'selected' : '' }}>Baru</option>
                <option value="lanjutan" {{ request('status') === 'lanjutan' ? 'selected' : '' }}>Lanjutan</option>
                <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-purple-deep text-white text-sm font-semibold rounded-xl hover:bg-purple-deep/90 transition cursor-pointer">Filter</button>
            @if(request()->hasAny(['search', 'kategori', 'status']))
            <a href="{{ route('bookings.index') }}" class="text-xs text-[#827299] hover:underline">Reset</a>
            @endif
        </form>
        <button type="button" @click="$dispatch('open-booking-modal')" class="px-4 py-2 bg-orange text-white text-sm font-semibold rounded-xl hover:bg-orange/90 transition shadow-xs flex-shrink-0 text-center cursor-pointer flex items-center gap-1.5">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>+ Booking Baru</span>
        </button>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-[#F7F5FB] text-[#6B5B85] text-xs uppercase tracking-wider">
                        <th class="px-6 py-3.5 text-left font-semibold">ID</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Klien</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Kategori</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Tanggal & Jam Dibuat</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Jadwal & Jam Pelaksanaan</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Status</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Konselor/Staff</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F3EAFB]">
                    @forelse($bookings as $booking)
                    @php
                        // Ekstraksi jam sesi bila tercatat di notes
                        $jamSesi = null;
                        if (!empty($booking->notes) && preg_match('/(?:Waktu|Jam|Pukul)\s*:\s*([0-9]{1,2}[:.][0-9]{2}(?:\s*-\s*[0-9]{1,2}[:.][0-9]{2})?)/i', $booking->notes, $matches)) {
                            $jamSesi = str_replace('.', ':', trim($matches[1]));
                            if (!str_contains(strtoupper($jamSesi), 'WIB')) {
                                $jamSesi .= ' WIB';
                            }
                        }
                        $jamDibuat = $booking->created_at ? $booking->created_at->format('H:i') . ' WIB' : '-';

                        // Data Klien Lengkap untuk Pop-Up Klien
                        $clientData = $booking->client ? [
                            'id' => $booking->client->id,
                            'name' => $booking->client->name,
                            'jenis' => $booking->client->jenis ?? 'individual',
                            'pic_name' => $booking->client->pic_name,
                            'phone' => $booking->client->phone ?? '-',
                            'email' => $booking->client->email ?? '-',
                            'gender' => $booking->client->gender_label ?? '-',
                            'dob' => $booking->client->dob ? $booking->client->dob->locale('id')->isoFormat('D MMMM Y') : '-',
                            'age' => $booking->client->dob ? $booking->client->dob->age . ' tahun' : null,
                            'birth_place' => $booking->client->birth_place ?? '-',
                            'religion' => $booking->client->religion ?? '-',
                            'occupation' => $booking->client->occupation ?? '-',
                            'education' => $booking->client->education_label ?? '-',
                            'marital_status' => $booking->client->marital_status ?? '-',
                            'city' => $booking->client->city ?? '',
                            'province' => $booking->client->province ?? '',
                            'country' => $booking->client->country ?? 'Indonesia',
                            'address' => $booking->client->address ?? '-',
                            'status' => $booking->client->status ?? 'unassigned',
                            'status_label' => ucfirst(str_replace('_', ' ', $booking->client->status ?? 'unassigned')),
                            'service_type' => $booking->client->service_type ?? '-',
                            'counseling_type' => $booking->client->counseling_type ?? '-',
                            'notes' => $booking->client->notes ?? '',
                            'assigned_staff' => $booking->client->assignedStaff ? [
                                'name' => $booking->client->assignedStaff->name,
                                'email' => $booking->client->assignedStaff->email,
                            ] : null,
                            'active_counselor' => $booking->client->pairings->where('status', 'active')->first()?->counselor ? [
                                'name' => $booking->client->pairings->where('status', 'active')->first()->counselor->name,
                                'specialization' => $booking->client->pairings->where('status', 'active')->first()->counselor->specialization,
                            ] : null,
                            'participants' => $booking->client->clientParticipants->pluck('nama_peserta')->values()->toArray(),
                            'bookings' => $booking->client->bookings->map(fn($b) => [
                                'id' => $b->id,
                                'kategori' => $b->kategori,
                                'status' => $b->status,
                                'status_label' => ucfirst(str_replace('_', ' ', $b->status)),
                                'tanggal_dibuat' => $b->tanggal_booking_dibuat->format('d M Y'),
                                'tanggal_dijadwalkan' => $b->tanggal_dijadwalkan ? $b->tanggal_dijadwalkan->format('d M Y') : 'Belum Dijadwalkan',
                                'counselor_name' => $b->counselor?->name,
                                'staff_penguji_name' => $b->staffPenguji?->name,
                                'notes' => $b->notes,
                                'update_url' => route('bookings.update', $b),
                            ])->values()->toArray(),
                            'test_results' => $booking->client->testResults->map(fn($t) => [
                                'id' => $t->id,
                                'test_name' => $t->test_name,
                                'status' => $t->status,
                                'status_label' => $t->status_label ?? ucfirst(str_replace('_', ' ', $t->status)),
                                'summary' => $t->result_summary,
                                'url' => route('test-results.show', $t),
                            ])->values()->toArray(),
                            'show_url' => route('clients.show', $booking->client),
                            'edit_url' => route('clients.edit', $booking->client),
                        ] : null;

                        // Data Booking Lengkap untuk Pop-Up Detail Booking
                        $bookingData = [
                            'id' => $booking->id,
                            'kategori' => $booking->kategori,
                            'status' => $booking->status,
                            'status_label' => ucfirst(str_replace('_', ' ', $booking->status)),
                            'tanggal_booking_dibuat' => $booking->tanggal_booking_dibuat->format('Y-m-d'),
                            'tanggal_booking_dibuat_formatted' => $booking->tanggal_booking_dibuat->locale('id')->isoFormat('D MMMM Y'),
                            'jam_dibuat' => $jamDibuat,
                            'tanggal_dijadwalkan' => $booking->tanggal_dijadwalkan ? $booking->tanggal_dijadwalkan->format('Y-m-d') : null,
                            'tanggal_dijadwalkan_formatted' => $booking->tanggal_dijadwalkan ? $booking->tanggal_dijadwalkan->locale('id')->isoFormat('dddd, D MMMM Y') : 'Belum Dijadwalkan',
                            'jam_sesi' => $jamSesi,
                            'follow_up_of_id' => $booking->follow_up_of_booking_id,
                            'client' => $clientData,
                            'counselor' => $booking->counselor ? [
                                'id' => $booking->counselor->id,
                                'name' => $booking->counselor->name,
                                'specialization' => $booking->counselor->specialization ?? '-',
                                'phone' => $booking->counselor->phone ?? '-',
                                'email' => $booking->counselor->email ?? '-',
                            ] : null,
                            'staff_penguji' => $booking->staffPenguji ? [
                                'id' => $booking->staffPenguji->id,
                                'name' => $booking->staffPenguji->name,
                                'email' => $booking->staffPenguji->email ?? '-',
                            ] : null,
                            'staff_koreksi' => $booking->staffKoreksi ? [
                                'id' => $booking->staffKoreksi->id,
                                'name' => $booking->staffKoreksi->name,
                                'email' => $booking->staffKoreksi->email ?? '-',
                            ] : null,
                            'staff_pelapor' => $booking->staffPelapor ? [
                                'id' => $booking->staffPelapor->id,
                                'name' => $booking->staffPelapor->name,
                                'email' => $booking->staffPelapor->email ?? '-',
                            ] : null,
                            'payment' => $booking->paymentTransaction ? [
                                'id' => $booking->paymentTransaction->id,
                                'amount_formatted' => 'Rp ' . number_format($booking->paymentTransaction->jumlah, 0, ',', '.'),
                                'status' => $booking->paymentTransaction->status,
                                'method' => $booking->paymentTransaction->metode_pembayaran ?? '-',
                            ] : null,
                            'participants' => $booking->participants->pluck('nama_peserta')->values()->toArray(),
                            'follow_ups' => $booking->followUps->map(fn($fu) => [
                                'id' => $fu->id,
                                'date_formatted' => $fu->tanggal_booking_dibuat->locale('id')->isoFormat('D MMMM Y'),
                                'status' => $fu->status,
                                'status_label' => ucfirst(str_replace('_', ' ', $fu->status)),
                            ])->values()->toArray(),
                            'notes' => $booking->notes ?? '',
                            'update_url' => route('bookings.update', $booking),
                            'destroy_url' => route('bookings.destroy', $booking),
                            'follow_up_url' => route('bookings.follow-up', $booking),
                        ];
                    @endphp
                    <tr class="hover:bg-[#FDFCFE] transition group cursor-pointer" @click="openDetail({{ json_encode($bookingData) }})">
                        {{-- ID Booking (Teks Biasa, Bukan Tombol Terpisah) --}}
                        <td class="px-6 py-3.5 font-bold text-[#5B4A73]">
                            <span>#{{ $booking->id }}</span>
                            @if($booking->follow_up_of_booking_id)
                            <span class="text-[10px] text-[#827299] block font-normal mt-0.5">↳ dari #{{ $booking->follow_up_of_booking_id }}</span>
                            @endif
                        </td>

                        {{-- Klien: Klik Nama Klien Langsung Muncul Pop-Up Detail Klien & Jadwal --}}
                        <td class="px-6 py-3.5">
                            @if($booking->client)
                            <button 
                                type="button" 
                                @click="openDetail({{ json_encode($bookingData) }})"
                                class="font-extrabold text-purple-deep hover:underline text-left transition cursor-pointer flex items-center gap-1.5"
                                title="Klik untuk Buka Detail Klien & Jadwal"
                            >
                                <span>{{ $booking->client->name }}</span>
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="opacity-60 shrink-0"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            </button>
                            <span class="text-[10px] text-[#827299] capitalize block">
                                {{ $booking->client->jenis ?? 'individual' }}
                                @if($booking->client->pic_name)
                                • PIC: {{ $booking->client->pic_name }}
                                @endif
                            </span>
                            @else
                            <span class="text-gray-400 italic">-</span>
                            @endif

                            @if($booking->participants->count() > 0)
                            <p class="text-[10px] text-[#827299] mt-0.5">{{ $booking->participants->count() }} peserta tercatat</p>
                            @endif
                        </td>

                        {{-- Kategori --}}
                        <td class="px-6 py-3.5 cursor-pointer" @click="openDetail({{ json_encode($bookingData) }})">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                {{ $booking->kategori === 'konseling' ? 'bg-purple-deep/10 text-purple-deep' : 'bg-orange/10 text-orange' }}">
                                {{ $booking->kategori }}
                            </span>
                        </td>

                        {{-- Tanggal Dibuat & Jam Booking Dibuat --}}
                        <td class="px-6 py-3.5 cursor-pointer" @click="openDetail({{ json_encode($bookingData) }})">
                            <div class="font-medium text-[#2A2035]">{{ $booking->tanggal_booking_dibuat->format('d M Y') }}</div>
                            <div class="text-[11px] text-[#827299] flex items-center gap-1 mt-0.5 font-mono">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0 text-purple-deep"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                <span>{{ $jamDibuat }}</span>
                            </div>
                        </td>

                        {{-- Tanggal Dijadwalkan & Jam Sesi --}}
                        <td class="px-6 py-3.5 cursor-pointer" @click="openDetail({{ json_encode($bookingData) }})">
                            @if($booking->tanggal_dijadwalkan)
                                <div class="font-medium text-[#2A2035]">{{ $booking->tanggal_dijadwalkan->format('d M Y') }}</div>
                                @if($jamSesi)
                                    <div class="text-[11px] text-purple-deep font-semibold flex items-center gap-1 mt-0.5 font-mono bg-purple-50 px-1.5 py-0.5 rounded-md w-fit">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                        <span>{{ $jamSesi }}</span>
                                    </div>
                                @else
                                    <div class="text-[11px] text-[#827299] flex items-center gap-1 mt-0.5 font-mono">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0 opacity-40"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                        <span>Jam belum diatur</span>
                                    </div>
                                @endif
                            @else
                                <span class="text-gray-400 italic text-xs">Belum Dijadwalkan</span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-3.5 cursor-pointer" @click="openDetail({{ json_encode($bookingData) }})">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold
                                {{ $booking->status === 'selesai' ? 'bg-emerald-50 text-emerald-600' :
                                   ($booking->status === 'baru' ? 'bg-blue-50 text-blue-600' :
                                   ($booking->status === 'lanjutan' ? 'bg-amber-50 text-amber-600' : 'bg-gray-100 text-gray-600')) }}">
                                {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                            </span>
                        </td>

                        {{-- Konselor / Staff --}}
                        <td class="px-6 py-3.5 text-[#6B5B85] text-xs cursor-pointer" @click="openDetail({{ json_encode($bookingData) }})">
                            @if($booking->kategori === 'konseling' && $booking->counselor)
                                <div class="font-medium text-[#2A2035]">{{ $booking->counselor->name }}</div>
                                <span class="text-[10px] text-[#827299] block">{{ $booking->counselor->specialization ?? 'Konselor' }}</span>
                            @elseif($booking->kategori === 'psikotes')
                                @if($booking->staffPenguji) <div><span class="font-bold text-orange text-[10px]">P:</span> {{ $booking->staffPenguji->name }}</div> @endif
                                @if($booking->staffKoreksi) <div><span class="font-bold text-amber-600 text-[10px]">K:</span> {{ $booking->staffKoreksi->name }}</div> @endif
                                @if($booking->staffPelapor) <div><span class="font-bold text-emerald-600 text-[10px]">L:</span> {{ $booking->staffPelapor->name }}</div> @endif
                            @else
                                <span class="text-gray-400 italic">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-[#6B5B85]">Belum ada data booking.</td>
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

    {{-- ========================================================================= --}}
    {{-- POP-UP MODAL DETAIL KLIEN & JADWAL BOOKING (TERPADU)                      --}}
    {{-- ========================================================================= --}}
    <div 
        x-show="detailModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-2xs"
        @keydown.escape.window="detailModalOpen = false"
    >
        <div 
            @click.away="detailModalOpen = false"
            class="bg-white w-full max-w-3xl rounded-3xl shadow-2xl border border-[#EDE1FA] overflow-hidden flex flex-col max-h-[92vh]"
        >
            {{-- Header Pop-Up: Avatar Klien, Nama Klien, Badge Booking & Status --}}
            <div class="p-5 border-b border-[#EDE1FA] bg-gradient-to-r from-purple-50/70 via-[#FAF8FD] to-white flex items-center justify-between">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-purple-deep text-white flex items-center justify-center font-black text-lg shadow-xs shrink-0">
                        <span x-text="selectedBooking?.client?.name ? selectedBooking.client.name.charAt(0).toUpperCase() : 'K'"></span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="font-extrabold text-base text-[#2A2035]" x-text="selectedBooking?.client?.name || 'Detail Klien'"></h3>
                            <span 
                                class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-purple-deep/10 text-purple-deep"
                                x-text="selectedBooking?.client?.jenis || 'individual'"
                            ></span>
                            <span 
                                class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                :class="selectedBooking?.kategori === 'konseling' ? 'bg-purple-deep/10 text-purple-deep' : 'bg-orange/15 text-orange'"
                                x-text="selectedBooking?.kategori || ''"
                            ></span>
                            <span 
                                class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                                :class="{
                                    'bg-emerald-50 text-emerald-600': selectedBooking?.status === 'selesai',
                                    'bg-blue-50 text-blue-600': selectedBooking?.status === 'baru',
                                    'bg-amber-50 text-amber-600': selectedBooking?.status === 'lanjutan',
                                    'bg-gray-100 text-gray-600': !['selesai', 'baru', 'lanjutan'].includes(selectedBooking?.status)
                                }"
                                x-text="selectedBooking?.status_label || ''"
                            ></span>
                        </div>
                        <p class="text-xs text-[#827299] mt-0.5 flex items-center gap-2 flex-wrap">
                            <span class="font-bold text-[#5B4A73]" x-text="'Booking #' + (selectedBooking?.id || '')"></span>
                            <template x-if="selectedBooking?.client?.pic_name">
                                <span>• PIC: <strong class="text-[#2A2035]" x-text="selectedBooking.client.pic_name"></strong></span>
                            </template>
                            <span>• Dibuat: <span class="font-medium text-[#2A2035]" x-text="selectedBooking?.tanggal_booking_dibuat_formatted || '-'"></span> <span class="font-mono text-[11px]" x-text="'(' + (selectedBooking?.jam_dibuat || '') + ')'"></span></span>
                            <template x-if="selectedBooking?.follow_up_of_id">
                                <span class="px-2 py-0.5 bg-purple-50 text-purple-deep rounded-md text-[10px] font-semibold">
                                    ↳ Lanjutan dari #<span x-text="selectedBooking?.follow_up_of_id"></span>
                                </span>
                            </template>
                        </p>
                    </div>
                </div>

                <button 
                    type="button" 
                    @click="detailModalOpen = false"
                    class="w-8 h-8 rounded-full hover:bg-gray-100 text-gray-400 hover:text-gray-700 flex items-center justify-center transition cursor-pointer"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            {{-- Body Detail: Berisi Profil, Layanan, dan Detail Jadwal Lengkap --}}
            <div class="p-6 overflow-y-auto space-y-5 text-sm">

                {{-- 1. BIODATA & PROFIL KLIEN --}}
                <div class="bg-[#FAF8FD] rounded-2xl p-4 border border-[#EDE1FA]">
                    <h4 class="font-bold text-xs uppercase tracking-wider text-[#5B4A73] pb-2.5 mb-3 border-b border-[#EDE1FA] flex items-center gap-1.5">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Biodata & Profil Klien
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-xs">
                        <div>
                            <span class="text-[#827299] block font-medium">WhatsApp / Telepon</span>
                            <template x-if="selectedBooking?.client?.phone && selectedBooking.client.phone !== '-'">
                                <a :href="'https://wa.me/' + selectedBooking.client.phone.replace(/[^0-9]/g, '')" target="_blank" class="font-bold text-purple-deep hover:underline flex items-center gap-1 mt-0.5">
                                    <span x-text="selectedBooking.client.phone"></span>
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                </a>
                            </template>
                            <template x-if="!selectedBooking?.client?.phone || selectedBooking.client.phone === '-'">
                                <span class="font-semibold text-gray-400">-</span>
                            </template>
                        </div>
                        <div>
                            <span class="text-[#827299] block font-medium">Email</span>
                            <span class="font-semibold text-[#2A2035]" x-text="selectedBooking?.client?.email || '-'"></span>
                        </div>
                        <div>
                            <span class="text-[#827299] block font-medium">Jenis Kelamin</span>
                            <span class="font-semibold text-[#2A2035]" x-text="selectedBooking?.client?.gender || '-'"></span>
                        </div>
                        <div>
                            <span class="text-[#827299] block font-medium">Lahir & Usia</span>
                            <span class="font-semibold text-[#2A2035]" x-text="selectedBooking?.client?.dob || '-'"></span>
                            <template x-if="selectedBooking?.client?.age">
                                <span class="text-[#827299] block text-[11px]" x-text="'(' + selectedBooking.client.age + ')'"></span>
                            </template>
                        </div>
                        <div>
                            <span class="text-[#827299] block font-medium">Pendidikan & Pekerjaan</span>
                            <span class="font-semibold text-[#2A2035]" x-text="(selectedBooking?.client?.education || '-') + ' • ' + (selectedBooking?.client?.occupation || '-')"></span>
                        </div>
                        <div>
                            <span class="text-[#827299] block font-medium">Wilayah</span>
                            <span class="font-semibold text-[#2A2035]" x-text="(selectedBooking?.client?.city ? selectedBooking.client.city + ', ' : '') + (selectedBooking?.client?.province || selectedBooking?.client?.country || '-')"></span>
                        </div>
                        <div class="sm:col-span-2 lg:col-span-3">
                            <span class="text-[#827299] block font-medium">Alamat Lengkap</span>
                            <span class="font-normal text-[#2A2035]" x-text="selectedBooking?.client?.address || '-'"></span>
                        </div>
                    </div>
                </div>

                {{-- 2. LAYANAN & PENUGASAN TIM --}}
                <div class="bg-white rounded-2xl p-4 border border-[#EDE1FA]">
                    <h4 class="font-bold text-xs uppercase tracking-wider text-[#5B4A73] pb-2.5 mb-3 border-b border-[#EDE1FA] flex items-center gap-1.5">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Layanan & Penugasan Tim
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="bg-[#FAF8FD] p-3 rounded-xl border border-[#EDE1FA]">
                            <span class="text-[#827299] block font-medium">Layanan yang Diambil</span>
                            <p class="font-bold text-purple-deep mt-0.5 text-sm uppercase" x-text="selectedBooking?.kategori || '-'"></p>
                            <p class="text-[11px] text-[#5B4A73]" x-text="selectedBooking?.client?.counseling_type || selectedBooking?.client?.service_type || '-'"></p>
                        </div>
                        <div class="bg-[#FAF8FD] p-3 rounded-xl border border-[#EDE1FA]">
                            <span class="text-[#827299] block font-medium">Staff Pendamping Klien</span>
                            <p class="font-bold text-[#2A2035] mt-0.5 text-sm" x-text="selectedBooking?.client?.assigned_staff?.name || 'Belum Diassign'"></p>
                            <p class="text-[11px] text-[#827299]" x-text="selectedBooking?.client?.assigned_staff?.email || ''"></p>
                        </div>
                    </div>

                    {{-- Konselor Praktik (Khusus Konseling) --}}
                    <template x-if="selectedBooking?.kategori === 'konseling'">
                        <div class="mt-3 p-3 bg-purple-50/50 rounded-xl border border-purple-100 flex items-center justify-between text-xs">
                            <div>
                                <span class="text-[#827299] block text-[11px] font-medium">Konselor Praktik</span>
                                <p class="font-bold text-purple-deep text-sm" x-text="selectedBooking.counselor?.name || 'Belum ditentukan'"></p>
                                <p class="text-[11px] text-[#5B4A73]" x-text="selectedBooking.counselor?.specialization || 'Konselor Praktik'"></p>
                            </div>
                            <span class="px-2.5 py-1 bg-purple-deep/10 text-purple-deep font-semibold rounded-lg text-[10px]">Konselor Aktif</span>
                        </div>
                    </template>

                    {{-- Tim Psikotes (Khusus Psikotes) --}}
                    <template x-if="selectedBooking?.kategori === 'psikotes'">
                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                            <div class="p-2.5 bg-[#FAF8FD] rounded-xl border border-[#EDE1FA]">
                                <span class="text-[#827299] block text-[10px] font-semibold uppercase">Staff Penguji</span>
                                <p class="font-bold text-[#2A2035] text-xs" x-text="selectedBooking.staff_penguji?.name || '-'"></p>
                            </div>
                            <div class="p-2.5 bg-[#FAF8FD] rounded-xl border border-[#EDE1FA]">
                                <span class="text-[#827299] block text-[10px] font-semibold uppercase">Staff Koreksi</span>
                                <p class="font-bold text-[#2A2035] text-xs" x-text="selectedBooking.staff_koreksi?.name || '-'"></p>
                            </div>
                            <div class="p-2.5 bg-[#FAF8FD] rounded-xl border border-[#EDE1FA]">
                                <span class="text-[#827299] block text-[10px] font-semibold uppercase">Staff Pelapor</span>
                                <p class="font-bold text-[#2A2035] text-xs" x-text="selectedBooking.staff_pelapor?.name || '-'"></p>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- 3. DETAIL JADWAL & WAKTU PELAKSANAAN --}}
                <div class="bg-white rounded-2xl p-4 border border-[#EDE1FA]">
                    <h4 class="font-bold text-xs uppercase tracking-wider text-[#5B4A73] pb-2.5 mb-3 border-b border-[#EDE1FA] flex items-center gap-1.5">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Detail Jadwal & Waktu Pelaksanaan
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="bg-[#FAF8FD] p-3 rounded-xl border border-[#EDE1FA]">
                            <span class="text-[#827299] block font-medium">Tanggal Booking Dibuat</span>
                            <span class="font-semibold text-[#2A2035] text-sm block mt-0.5" x-text="selectedBooking?.tanggal_booking_dibuat_formatted || '-'"></span>
                            <span class="text-[11px] text-[#827299] block font-mono mt-0.5" x-text="'Pukul ' + (selectedBooking?.jam_dibuat || '-')"></span>
                        </div>
                        <div class="bg-purple-50/60 p-3 rounded-xl border border-purple-100">
                            <span class="text-purple-deep block font-medium">Jadwal Pelaksanaan Sesi</span>
                            <span class="font-extrabold text-purple-deep text-sm block mt-0.5" x-text="selectedBooking?.tanggal_dijadwalkan_formatted || 'Belum Dijadwalkan'"></span>
                            <template x-if="selectedBooking?.jam_sesi">
                                <span class="text-xs text-purple-deep font-semibold block font-mono mt-0.5" x-text="'Waktu: ' + selectedBooking.jam_sesi"></span>
                            </template>
                            <template x-if="!selectedBooking?.jam_sesi">
                                <span class="text-xs text-gray-500 italic block mt-0.5">Jam belum diatur</span>
                            </template>
                        </div>
                    </div>

                    {{-- Catatan Sesi --}}
                    <div class="mt-3">
                        <span class="text-[#827299] block font-medium text-xs mb-1">Catatan Sesi & Informasi Tambahan</span>
                        <template x-if="selectedBooking?.notes">
                            <div class="p-3 bg-[#FAF8FD] rounded-xl border border-[#EDE1FA] text-xs leading-relaxed text-[#2A2035] whitespace-pre-line font-mono" x-text="selectedBooking.notes"></div>
                        </template>
                        <template x-if="!selectedBooking?.notes">
                            <p class="text-xs text-gray-500 italic">Tidak ada catatan tambahan untuk booking ini.</p>
                        </template>
                    </div>

                    {{-- Peserta Tambahan (Bila ada) --}}
                    <template x-if="selectedBooking?.participants && selectedBooking.participants.length > 0">
                        <div class="mt-3 pt-3 border-t border-[#EDE1FA]">
                            <span class="text-[#827299] block font-medium text-xs mb-1.5" x-text="'Daftar Peserta (' + selectedBooking.participants.length + ' orang)'"></span>
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="(nama, idx) in selectedBooking.participants" :key="idx">
                                    <span class="px-2.5 py-1 bg-[#FBF9FE] rounded-lg border border-[#EDE1FA] text-xs font-medium text-[#2A2035]" x-text="(idx + 1) + '. ' + nama"></span>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Informasi Pembayaran (Bila ada) --}}
                    <template x-if="selectedBooking?.payment">
                        <div class="mt-3 pt-3 border-t border-[#EDE1FA] grid grid-cols-3 gap-2 text-xs">
                            <div>
                                <span class="text-[#827299] block">Nominal</span>
                                <span class="font-bold text-[#2A2035]" x-text="selectedBooking.payment.amount_formatted"></span>
                            </div>
                            <div>
                                <span class="text-[#827299] block">Status Bayar</span>
                                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold uppercase mt-0.5" :class="selectedBooking.payment.status === 'paid' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600'" x-text="selectedBooking.payment.status"></span>
                            </div>
                            <div>
                                <span class="text-[#827299] block">Metode</span>
                                <span class="font-semibold text-[#2A2035]" x-text="selectedBooking.payment.method"></span>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- 4. RIWAYAT BOOKING LAIN KLIEN (JIKA ADA > 1 BOOKING) --}}
                <template x-if="selectedBooking?.client?.bookings && selectedBooking.client.bookings.length > 1">
                    <div class="bg-white rounded-2xl p-4 border border-[#EDE1FA]">
                        <h4 class="font-bold text-xs uppercase tracking-wider text-[#5B4A73] pb-2.5 mb-3 border-b border-[#EDE1FA] flex items-center justify-between">
                            <span class="flex items-center gap-1.5">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                Riwayat Sesi Booking Klien Lainnya
                            </span>
                            <span class="text-[11px] font-semibold text-[#827299]" x-text="selectedBooking.client.bookings.length + ' sesi tercatat'"></span>
                        </h4>
                        <div class="divide-y divide-[#EDE1FA]">
                            <template x-for="b in selectedBooking.client.bookings" :key="b.id">
                                <div class="py-2.5 flex items-center justify-between text-xs" :class="b.id === selectedBooking.id ? 'opacity-60 bg-purple-50/30 px-2 rounded-lg' : ''">
                                    <div>
                                        <span class="font-bold text-purple-deep" x-text="'Booking #' + b.id"></span>
                                        <template x-if="b.id === selectedBooking.id">
                                            <span class="ml-1 text-[10px] font-semibold text-purple-deep">(Sesi ini)</span>
                                        </template>
                                        <span class="text-[#827299] ml-2" x-text="b.tanggal_dijadwalkan"></span>
                                        <template x-if="b.counselor_name">
                                            <span class="text-[#5B4A73] ml-1" x-text="'• ' + b.counselor_name"></span>
                                        </template>
                                    </div>
                                    <span 
                                        class="px-2 py-0.5 rounded-full text-[10px] font-semibold"
                                        :class="b.status === 'selesai' ? 'bg-emerald-50 text-emerald-600' : 'bg-blue-50 text-blue-600'"
                                        x-text="b.status_label"
                                    ></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- 5. BERKAS HASIL ASESMEN PSIKOTES (JIKA ADA) --}}
                <template x-if="selectedBooking?.client?.test_results && selectedBooking.client.test_results.length > 0">
                    <div class="bg-white rounded-2xl p-4 border border-[#EDE1FA]">
                        <h4 class="font-bold text-xs uppercase tracking-wider text-[#5B4A73] pb-2.5 mb-3 border-b border-[#EDE1FA] flex items-center gap-1.5">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            Berkas & Hasil Asesmen Klien
                        </h4>
                        <div class="divide-y divide-[#EDE1FA]">
                            <template x-for="t in selectedBooking.client.test_results" :key="t.id">
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

            {{-- Footer: Bagian Paling Bawah Tersedia Tombol Edit Jadwal, Hapus Data, Tutup (dan Edit Profil Klien) --}}
            <div class="p-4 border-t border-[#EDE1FA] bg-[#FAF8FD] flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2 flex-wrap">
                    {{-- Tombol Edit Jadwal --}}
                    <button 
                        type="button" 
                        @click="openEdit(selectedBooking)"
                        class="px-4 py-2 bg-purple-deep text-white text-xs font-bold rounded-xl hover:opacity-90 transition cursor-pointer flex items-center gap-1.5 shadow-xs"
                    >
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span>Edit Jadwal</span>
                    </button>

                    {{-- Tombol Hapus Data --}}
                    <form :action="selectedBooking?.destroy_url || ('/bookings/' + (selectedBooking?.id || ''))" method="POST" class="inline m-0 p-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data booking ini?')">
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

                    {{-- Tombol Edit Profil Klien --}}
                    <template x-if="selectedBooking?.client?.edit_url">
                        <a 
                            :href="selectedBooking.client.edit_url" 
                            class="px-3.5 py-2 border border-[#D9C2F0] bg-white text-[#5B4A73] text-xs font-bold rounded-xl hover:bg-[#F7F5FB] transition cursor-pointer flex items-center gap-1.5"
                        >
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            <span>Edit Profil Klien</span>
                        </a>
                    </template>

                    {{-- Buka Halaman Lengkap Klien --}}
                    <template x-if="selectedBooking?.client?.show_url">
                        <a 
                            :href="selectedBooking.client.show_url" 
                            class="px-3 py-2 text-xs font-bold text-purple-deep hover:underline flex items-center gap-1"
                        >
                            <span>Buka Halaman Lengkap</span>
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        </a>
                    </template>
                </div>

                {{-- Tombol Tutup --}}
                <button 
                    type="button" 
                    @click="detailModalOpen = false"
                    class="px-5 py-2 bg-white border border-[#D9C2F0] text-[#5B4A73] text-xs font-bold rounded-xl hover:bg-[#F3EAFB] transition cursor-pointer"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 3. POP-UP MODAL EDIT BOOKING                                              --}}
    {{-- ========================================================================= --}}
    <div 
        x-show="editModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-2xs"
        @keydown.escape.window="editModalOpen = false"
    >
        <div 
            @click.away="editModalOpen = false"
            class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl border border-[#EDE1FA] overflow-hidden flex flex-col max-h-[90vh]"
        >
            {{-- Header Edit --}}
            <div class="p-5 border-b border-[#EDE1FA] bg-[#FAF8FD] flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-purple-deep/10 text-purple-deep flex items-center justify-center font-bold text-sm">
                        ✏️
                    </div>
                    <div>
                        <h3 class="font-extrabold text-base text-[#2A2035]">
                            Edit Booking <span class="text-purple-deep" x-text="'#' + (editForm.id || '')"></span>
                        </h3>
                        <p class="text-xs text-[#827299]">Perbarui data booking, jadwal sesi, dan penugasan</p>
                    </div>
                </div>

                <button 
                    type="button" 
                    @click="editModalOpen = false"
                    class="w-8 h-8 rounded-full hover:bg-gray-100 text-gray-400 hover:text-gray-700 flex items-center justify-center transition cursor-pointer"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            {{-- Form Edit --}}
            <form :action="editForm.action_url" method="POST" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                @method('PUT')

                <div class="p-6 overflow-y-auto space-y-4 text-xs flex-1">
                    
                    {{-- Klien --}}
                    <div>
                        <label class="block font-semibold text-[#5B4A73] mb-1.5">Klien <span class="text-red-500">*</span></label>
                        <select 
                            name="client_id" 
                            x-model="editForm.client_id" 
                            @change="onClientChange($event)"
                            required
                            class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white"
                        >
                            <option value="">Pilih Klien...</option>
                            @foreach($clients as $client)
                            <option value="{{ $client->id }}" data-jenis="{{ $client->jenis }}">
                                {{ $client->name }} ({{ ucfirst($client->jenis) }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Kategori & Status --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-[#5B4A73] mb-1.5">Kategori <span class="text-red-500">*</span></label>
                            <select 
                                name="kategori" 
                                x-model="editForm.kategori" 
                                required
                                class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white"
                            >
                                <option value="konseling">Konseling</option>
                                <option value="psikotes">Psikotes</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-[#5B4A73] mb-1.5">Status <span class="text-red-500">*</span></label>
                            <select 
                                name="status" 
                                x-model="editForm.status" 
                                required
                                class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white"
                            >
                                <option value="baru">Baru</option>
                                <option value="lanjutan">Lanjutan</option>
                                <option value="selesai">Selesai</option>
                            </select>
                        </div>
                    </div>

                    {{-- Tanggal Dibuat & Tanggal Dijadwalkan --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-[#5B4A73] mb-1.5">Tanggal Booking Dibuat <span class="text-red-500">*</span></label>
                            <input 
                                type="date" 
                                name="tanggal_booking_dibuat" 
                                x-model="editForm.tanggal_booking_dibuat" 
                                required
                                class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white"
                            >
                        </div>
                        <div>
                            <label class="block font-semibold text-[#5B4A73] mb-1.5">Tanggal Dijadwalkan</label>
                            <input 
                                type="date" 
                                name="tanggal_dijadwalkan" 
                                x-model="editForm.tanggal_dijadwalkan" 
                                class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white"
                            >
                        </div>
                    </div>

                    {{-- Konseling: Konselor --}}
                    <div x-show="editForm.kategori === 'konseling'" class="bg-[#FAF8FD] p-4 rounded-2xl border border-[#EDE1FA]">
                        <label class="block font-semibold text-[#5B4A73] mb-1.5">Pilih Konselor Praktik</label>
                        <select 
                            name="counselor_id" 
                            x-model="editForm.counselor_id"
                            class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white"
                        >
                            <option value="">Belum Ditentukan (Unassigned)</option>
                            @foreach($counselors as $counselor)
                            <option value="{{ $counselor->id }}">
                                {{ $counselor->name }} ({{ $counselor->specialization ?? 'Psikolog' }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Psikotes: Penugasan 3 Peran Staff --}}
                    <div x-show="editForm.kategori === 'psikotes'" class="bg-[#FAF8FD] p-4 rounded-2xl border border-[#EDE1FA] space-y-3">
                        <span class="block font-bold text-orange uppercase tracking-wider text-[11px]">Penugasan Staff Psikotes</span>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block font-semibold text-[#5B4A73] mb-1">Staff Penguji</label>
                                <select 
                                    name="staff_penguji_id" 
                                    x-model="editForm.staff_penguji_id"
                                    class="w-full px-3 py-2 border border-[#D9C2F0] rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white"
                                >
                                    <option value="">Pilih Staff...</option>
                                    @foreach($staffs as $staff)
                                    <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold text-[#5B4A73] mb-1">Staff Koreksi</label>
                                <select 
                                    name="staff_koreksi_id" 
                                    x-model="editForm.staff_koreksi_id"
                                    class="w-full px-3 py-2 border border-[#D9C2F0] rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white"
                                >
                                    <option value="">Pilih Staff...</option>
                                    @foreach($staffs as $staff)
                                    <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold text-[#5B4A73] mb-1">Staff Pelapor</label>
                                <select 
                                    name="staff_pelapor_id" 
                                    x-model="editForm.staff_pelapor_id"
                                    class="w-full px-3 py-2 border border-[#D9C2F0] rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white"
                                >
                                    <option value="">Pilih Staff...</option>
                                    @foreach($staffs as $staff)
                                    <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Peserta (Jika Company / Kelompok) --}}
                    <div x-show="editForm.client_jenis === 'company'" class="bg-[#FAF8FD] p-4 rounded-2xl border border-[#EDE1FA] space-y-2.5">
                        <label class="block font-semibold text-[#5B4A73]">Daftar Peserta Perusahaan / Kelompok</label>
                        <template x-for="(p, i) in editForm.participants" :key="i">
                            <div class="flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-purple-deep/10 text-purple-deep font-bold text-[10px] flex items-center justify-center shrink-0" x-text="i + 1"></span>
                                <input 
                                    type="text" 
                                    :name="'participants[' + i + ']'" 
                                    x-model="editForm.participants[i]" 
                                    placeholder="Nama peserta..."
                                    class="flex-1 px-3 py-2 border border-[#D9C2F0] rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white"
                                >
                                <button 
                                    type="button" 
                                    @click="removeParticipant(i)" 
                                    x-show="editForm.participants.length > 1"
                                    class="text-red-400 hover:text-red-600 p-1 cursor-pointer"
                                >
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </div>
                        </template>
                        <button 
                            type="button" 
                            @click="addParticipant()" 
                            class="text-xs text-purple-deep font-bold hover:underline inline-flex items-center gap-1 mt-1 cursor-pointer"
                        >
                            <span>+ Tambah Peserta</span>
                        </button>
                    </div>

                    {{-- Catatan --}}
                    <div>
                        <label class="block font-semibold text-[#5B4A73] mb-1.5">Catatan / Detail Sesi (Waktu, Tipe, Lokasi)</label>
                        <textarea 
                            name="notes" 
                            x-model="editForm.notes" 
                            rows="3" 
                            class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white font-mono"
                            placeholder="Catatan tambahan, waktu sesi, atau tautan meeting..."
                        ></textarea>
                    </div>

                </div>

                {{-- Footer Edit Buttons --}}
                <div class="p-4 border-t border-[#EDE1FA] bg-[#FAF8FD] flex items-center justify-end gap-2.5">
                    <button 
                        type="button" 
                        @click="editModalOpen = false" 
                        class="px-4 py-2 border border-[#D9C2F0] text-[#6B5B85] text-xs font-bold rounded-xl hover:bg-white transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="px-5 py-2 bg-purple-deep text-white text-xs font-bold rounded-xl hover:opacity-90 transition shadow-xs cursor-pointer"
                    >
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

{{-- Alpine.js Booking Manager Script --}}
<script>
function bookingManager() {
    return {
        detailModalOpen: false,
        editModalOpen: false,
        selectedBooking: null,
        editForm: {
            id: null,
            client_id: '',
            client_jenis: 'individual',
            kategori: 'konseling',
            tanggal_booking_dibuat: '',
            tanggal_dijadwalkan: '',
            status: 'baru',
            counselor_id: '',
            staff_penguji_id: '',
            staff_koreksi_id: '',
            staff_pelapor_id: '',
            participants: [''],
            notes: '',
            action_url: '',
        },

        init() {
            // Optional URL param support (?detail=123)
            const urlParams = new URLSearchParams(window.location.search);
            const detailId = urlParams.get('detail');
            if (detailId) {
                const triggerBtn = document.querySelector(`button[data-booking-id="${detailId}"]`);
                if (triggerBtn) triggerBtn.click();
            }
        },

        openDetail(booking) {
            this.selectedBooking = booking;
            this.detailModalOpen = true;
            this.editModalOpen = false;
        },

        openEdit(booking) {
            this.editForm.id = booking.id;
            this.editForm.client_id = booking.client?.id || '';
            this.editForm.client_jenis = booking.client?.jenis || 'individual';
            this.editForm.kategori = booking.kategori || 'konseling';
            this.editForm.tanggal_booking_dibuat = booking.tanggal_booking_dibuat || '';
            this.editForm.tanggal_dijadwalkan = booking.tanggal_dijadwalkan || '';
            this.editForm.status = booking.status || 'baru';
            this.editForm.counselor_id = booking.counselor ? booking.counselor.id : '';
            this.editForm.staff_penguji_id = booking.staff_penguji ? booking.staff_penguji.id : '';
            this.editForm.staff_koreksi_id = booking.staff_koreksi ? booking.staff_koreksi.id : '';
            this.editForm.staff_pelapor_id = booking.staff_pelapor ? booking.staff_pelapor.id : '';
            this.editForm.participants = (booking.participants && booking.participants.length > 0) ? [...booking.participants] : [''];
            this.editForm.notes = booking.notes || '';
            this.editForm.action_url = booking.update_url;

            this.detailModalOpen = false;
            this.editModalOpen = true;
        },

        onClientChange(event) {
            const opt = event.target.selectedOptions[0];
            if (opt) {
                this.editForm.client_jenis = opt.dataset.jenis || 'individual';
            }
        },

        addParticipant() {
            this.editForm.participants.push('');
        },

        removeParticipant(index) {
            if (this.editForm.participants.length > 1) {
                this.editForm.participants.splice(index, 1);
            }
        }
    };
}
</script>
@endsection
