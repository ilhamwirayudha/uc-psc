@extends('layouts.dashboard')

@section('title', 'Layanan — UC PSC')
@section('page-title', 'Layanan')
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
                class="px-4 py-2 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep glass-input min-w-[200px]">
            <select name="kategori" class="px-3 py-2 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep glass-input">
                <option value="">Semua Kategori</option>
                <option value="konseling" {{ request('kategori') === 'konseling' ? 'selected' : '' }}>Konseling</option>
                <option value="psikotes" {{ request('kategori') === 'psikotes' ? 'selected' : '' }}>Psikotes</option>
                <option value="pending" {{ request('kategori') === 'pending' ? 'selected' : '' }}>Pending (Biography EN)</option>
            </select>
            <select name="status" class="px-3 py-2 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep glass-input">
                <option value="">Semua Status</option>
                <option value="data_masuk" {{ request('status') === 'data_masuk' ? 'selected' : '' }}>Data Masuk</option>
                <option value="waiting_list" {{ request('status') === 'waiting_list' ? 'selected' : '' }}>Waiting List</option>
                <option value="menunggu_konfirmasi" {{ request('status') === 'menunggu_konfirmasi' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                <option value="terjadwal_gocal" {{ request('status') === 'terjadwal_gocal' ? 'selected' : '' }}>Terjadwal (GCal)</option>
                <option value="reminder_h1" {{ request('status') === 'reminder_h1' ? 'selected' : '' }}>Reminder H-1</option>
                <option value="perlu_bayar_overtime" {{ request('status') === 'perlu_bayar_overtime' ? 'selected' : '' }}>Perlu Bayar Overtime</option>
                <option value="perlu_sesi_lanjutan" {{ request('status') === 'perlu_sesi_lanjutan' ? 'selected' : '' }}>Perlu Sesi Lanjutan</option>
                <option value="baru" {{ request('status') === 'baru' ? 'selected' : '' }}>Psikotes Berjalan</option>
                <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                <option value="batal" {{ request('status') === 'batal' ? 'selected' : '' }}>Dibatalkan</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-purple-deep text-white text-sm font-semibold rounded-lg hover:bg-purple-deep/90 transition cursor-pointer">Filter</button>
            @if(request()->hasAny(['search', 'kategori', 'status']))
            <a href="{{ route('bookings.index') }}" class="text-xs text-[#827299] hover:underline">Reset</a>
            @endif
        </form>
        @if(auth()->user()->isAdmin())
        <button type="button" @click="$dispatch('open-booking-modal')" class="px-4 py-2 bg-orange text-white text-sm font-semibold rounded-lg hover:bg-orange/90 transition shadow-xs flex-shrink-0 text-center cursor-pointer flex items-center gap-1.5">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Booking Baru</span>
        </button>
        @endif
    </div>

    {{-- Table --}}
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-sm font-medium">
                <thead>
                    <tr class="bg-purple-deep/95 text-white font-semibold select-none border-b border-purple-900/30">
                        <th class="px-6 py-3.5 text-left font-semibold text-white">ID</th>
                        <th class="px-6 py-3.5 text-left font-semibold text-white">Klien</th>
                        <th class="px-6 py-3.5 text-left font-semibold text-white">Kategori</th>
                        <th class="px-6 py-3.5 text-left font-semibold text-white">Tanggal &amp; Jam Dibuat</th>
                        <th class="px-6 py-3.5 text-left font-semibold text-white">Jadwal &amp; Jam Pelaksanaan</th>
                        <th class="px-6 py-3.5 text-left font-semibold text-white">Status</th>
                        <th class="px-6 py-3.5 text-left font-semibold text-white">Psikolog/Staff</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F3EAFB]">
                    @forelse($bookings as $booking)
                    @php
                        $jamSesi = null;
                        if (!empty($booking->session_start)) {
                            $jamSesi = substr($booking->session_start, 0, 5);
                            if (!empty($booking->session_end)) {
                                $jamSesi .= ' - ' . substr($booking->session_end, 0, 5);
                            }
                        } elseif (!empty($booking->notes) && preg_match('/(?:Waktu|Jam|Pukul)\s*:\s*([0-9]{1,2}[:.][0-9]{2}(?:\s*-\s*[0-9]{1,2}[:.][0-9]{2})?)/i', $booking->notes, $matches)) {
                            $jamSesi = str_replace('.', ':', trim($matches[1]));
                        }
                        $jamDibuat = $booking->created_at ? $booking->created_at->format('H:i') : '-';
                        $dibuatTabular = $booking->created_at
                            ? $booking->created_at->format('d/m/Y H:i')
                            : ($booking->tanggal_booking_dibuat ? $booking->tanggal_booking_dibuat->format('d/m/Y') : '-');
                        $dijadwalkanTabular = $booking->tanggal_dijadwalkan
                            ? trim($booking->tanggal_dijadwalkan->format('d/m/Y') . ($jamSesi ? ' ' . $jamSesi : ''))
                            : 'Belum Dijadwalkan';

                        $clientData = $booking->client ? [
                            'id' => $booking->client->id,
                            'name' => $booking->client->name,
                            'jenis' => $booking->client->jenis ?? 'individu',
                            'pic_name' => $booking->client->pic_name,
                            'phone' => $booking->client->phone ?? '-',
                            'email' => $booking->client->email ?? '-',
                            'gender' => $booking->client->gender_label ?? '-',
                            'dob' => $booking->client->dob ? $booking->client->dob->locale('id')->isoFormat('D MMMM Y') : '-',
                            'birth_place' => $booking->client->birth_place ?? '-',
                            'religion' => $booking->client->religion ?? '-',
                            'occupation' => $booking->client->occupation ?? '-',
                            'education' => $booking->client->education_label ?? '-',
                            'marital_status' => $booking->client->marital_status ?? '-',
                            'address' => $booking->client->address ?? '-',
                            'status' => $booking->client->status ?? 'unassigned',
                            'status_label' => ucfirst(str_replace('_', ' ', $booking->client->status ?? 'unassigned')),
                            'service_type' => $booking->client->service_type ?? '-',
                            'counseling_type' => $booking->client->counseling_type ?? '-',
                            'notes' => $booking->client->notes ?? '',
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
                                'tanggal_dibuat' => $b->tanggal_booking_dibuat?->locale('id')->isoFormat('D MMMM Y') ?? '-',
                                'tanggal_dijadwalkan' => $b->tanggal_dijadwalkan ? $b->tanggal_dijadwalkan->locale('id')->isoFormat('D MMMM Y') : 'Belum Dijadwalkan',
                                'counselor_name' => $b->counselor?->name,
                                'staff_penguji_name' => $b->staffPenguji?->name,
                                'notes' => $b->notes,
                                'update_url' => route('bookings.update', $b),
                            ])->values()->toArray(),
                            'show_url' => route('clients.show', $booking->client),
                            'edit_url' => route('clients.edit', $booking->client),
                        ] : null;

                        $bookingData = [
                            'id' => $booking->id,
                            'kategori' => $booking->kategori,
                            'status' => $booking->status,
                            'status_label' => ucfirst(str_replace('_', ' ', $booking->status)),
                            'tanggal_booking_dibuat' => $booking->tanggal_booking_dibuat->format('Y-m-d'),
                            'tanggal_booking_dibuat_formatted' => $booking->tanggal_booking_dibuat->locale('id')->isoFormat('D MMMM Y') . ($jamDibuat !== '-' ? ', ' . $jamDibuat : ''),
                            'jam_dibuat' => $jamDibuat,
                            'tanggal_dijadwalkan' => $booking->tanggal_dijadwalkan ? $booking->tanggal_dijadwalkan->format('Y-m-d') : null,
                            'tanggal_dijadwalkan_formatted' => $booking->tanggal_dijadwalkan
                                ? ($booking->tanggal_dijadwalkan->locale('id')->isoFormat('D MMMM Y') . ($jamSesi ? ', ' . $jamSesi : ''))
                                : 'Belum Dijadwalkan',
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
                    <tr class="table-row-clickable hover:bg-slate-100 active:bg-slate-200 transition-colors duration-150 group cursor-pointer" onclick="window.location='{{ $booking->kategori === 'psikotes' ? route('psychotest-bookings.show', $booking) : route('bookings.show', $booking) }}'">
                        {{-- ID Booking --}}
                        <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap">
                            <span>#{{ $booking->id }}</span>
                            @if($booking->follow_up_of_booking_id)
                            <span class="text-[11px] text-slate-500 block font-medium mt-0.5">↳ dari #{{ $booking->follow_up_of_booking_id }}</span>
                            @endif
                        </td>

                        {{-- Klien --}}
                        <td class="px-6 py-3.5 text-black text-xs font-medium" @click.stop>
                            @if($booking->client)
                            <button
                                type="button"
                                @click.stop="openDetail({{ json_encode($bookingData) }})"
                                class="text-xs font-medium text-black hover:text-orange text-left transition cursor-pointer flex items-center gap-1.5"
                                title="Klik untuk Buka Detail Klien & Jadwal"
                            >
                                <span>{{ $booking->client->name }}</span>
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-slate-400 shrink-0"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            </button>
                            <div class="text-[11px] text-slate-500 font-medium capitalize mt-0.5">
                                {{ in_array($booking->client->jenis ?? '', ['industri', 'company', 'perusahaan']) ? 'Industri' : 'Individu' }}
                                @if($booking->client->pic_name)
                                • PIC: {{ $booking->client->pic_name }}
                                @endif
                            </div>
                            @else
                            <span class="text-slate-500 text-xs font-medium">-</span>
                            @endif

                            @if($booking->participants->count() > 0)
                            <div class="text-[11px] text-slate-500 font-medium mt-0.5">{{ $booking->participants->count() }} peserta tercatat</div>
                            @endif
                        </td>

                        {{-- Kategori --}}
                        <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap cursor-pointer" @click.stop="openDetail({{ json_encode($bookingData) }})">
                            {{ ucfirst($booking->kategori) }}
                        </td>

                        {{-- Tanggal & Jam Dibuat --}}
                        <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap cursor-pointer" @click.stop="openDetail({{ json_encode($bookingData) }})">
                            {{ $dibuatTabular }}
                        </td>

                        {{-- Jadwal & Jam Pelaksanaan --}}
                        <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap cursor-pointer" @click.stop="openDetail({{ json_encode($bookingData) }})">
                            {{ $dijadwalkanTabular }}
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-3.5 text-black text-xs font-medium whitespace-nowrap cursor-pointer" @click.stop="openDetail({{ json_encode($bookingData) }})">
                            <div>{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</div>
                            @if($booking->kategori === 'konseling' && $booking->is_realized)
                            <div class="text-[11px] text-slate-500 font-medium mt-0.5">
                                {{ $booking->is_overtime ? 'Overtime' : 'Tepat Waktu' }}
                            </div>
                            @endif
                        </td>

                        {{-- Psikolog / Staff --}}
                        <td class="px-6 py-3.5 text-black text-xs font-medium cursor-pointer" @click.stop="openDetail({{ json_encode($bookingData) }})">
                            @if($booking->kategori === 'konseling' && $booking->counselor)
                                <div>{{ $booking->counselor->name }}</div>
                                <div class="text-[11px] text-slate-500 font-medium">{{ $booking->counselor->specialization ?? 'Psikolog' }}</div>
                            @elseif($booking->kategori === 'psikotes')
                                @php
                                    $summaryPsych = $booking->formatPsychologistQuotaSummary();
                                    $summaryTester = $booking->formatAssigneeSummary('tester');
                                @endphp
                                @if($summaryPsych !== '-')
                                    <div>Psikolog: {{ $summaryPsych }}</div>
                                @endif
                                @if($summaryTester !== '-')
                                    <div class="text-[11px] text-slate-500 font-medium">Tester: {{ $summaryTester }}</div>
                                @endif
                                @if($summaryPsych === '-' && $summaryTester === '-')
                                    @if($booking->staffPenguji) <div>Penguji: {{ $booking->staffPenguji->name }}</div> @endif
                                    @if($booking->staffKoreksi) <div class="text-[11px] text-slate-500 font-medium">Koreksi: {{ $booking->staffKoreksi->name }}</div> @endif
                                    @if($booking->staffPelapor) <div class="text-[11px] text-slate-500 font-medium">Pelapor: {{ $booking->staffPelapor->name }}</div> @endif
                                    @if(!$booking->staffPenguji && !$booking->staffKoreksi && !$booking->staffPelapor) - @endif
                                @endif
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-black font-medium">Belum ada data booking.</td>
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
    >
        <div 
            class="bg-white w-full max-w-3xl rounded-2xl shadow-2xl border border-[#EDE1FA] overflow-hidden flex flex-col max-h-[92vh]"
        >
            {{-- Header Pop-Up --}}
            <div class="p-5 border-b border-[#EDE1FA] bg-[#FAF8FD] flex items-center justify-between">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-xl bg-purple-deep text-white flex items-center justify-center font-bold text-lg shadow-xs shrink-0">
                        <span x-text="selectedBooking?.client?.name ? selectedBooking.client.name.charAt(0).toUpperCase() : 'K'"></span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="font-bold text-base text-[#2A2035]" x-text="selectedBooking?.client?.name || 'Detail Klien'"></h3>
                            <span class="text-xs font-medium text-slate-500" x-text="'Booking #' + (selectedBooking?.id || '')"></span>
                        </div>
                        <p class="text-xs text-slate-500 font-medium mt-0.5 flex items-center gap-2 flex-wrap">
                            <span x-text="['industri', 'company', 'perusahaan'].includes(selectedBooking?.client?.jenis) ? 'Industri' : 'Individu'"></span>
                            <span>•</span>
                            <span x-text="selectedBooking?.kategori ? selectedBooking.kategori.toUpperCase() : '-'"></span>
                            <span>•</span>
                            <span x-text="selectedBooking?.status_label || '-'"></span>
                            <template x-if="selectedBooking?.client?.pic_name">
                                <span x-text="'• PIC: ' + selectedBooking.client.pic_name"></span>
                            </template>
                        </p>
                    </div>
                </div>

                <button 
                    type="button" 
                    @click="detailModalOpen = false"
                    class="w-8 h-8 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-black flex items-center justify-center transition cursor-pointer"
                    title="Tutup"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            {{-- Body Detail --}}
            <div class="p-6 overflow-y-auto custom-scrollbar space-y-5 text-sm">

                {{-- BIODATA & PROFIL KLIEN --}}
                <div class="bg-white rounded-2xl p-5 border border-[#EDE1FA]">
                    <h4 class="font-bold text-xs uppercase tracking-wider text-purple-deep pb-2.5 mb-3.5 border-b border-[#EDE1FA] flex items-center gap-1.5">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Biodata &amp; Profil Klien
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <span class="text-xs font-medium text-slate-500 mb-1 block">No. Telepon</span>
                            <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center">
                                <template x-if="selectedBooking?.client?.phone && selectedBooking.client.phone !== '-'">
                                    <a :href="'https://wa.me/' + selectedBooking.client.phone.replace(/[^0-9]/g, '')" target="_blank" class="text-sm font-medium text-black hover:text-purple-deep hover:underline flex items-center gap-1">
                                        <span x-text="selectedBooking.client.phone"></span>
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                    </a>
                                </template>
                                <template x-if="!selectedBooking?.client?.phone || selectedBooking.client.phone === '-'">
                                    <span class="text-sm font-medium text-black">-</span>
                                </template>
                            </div>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-slate-500 mb-1 block">Email</span>
                            <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black break-all" x-text="selectedBooking?.client?.email || '-'"></div>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-slate-500 mb-1 block">Jenis Kelamin</span>
                            <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black" x-text="selectedBooking?.client?.gender || '-'"></div>
                        </div>
                        <div class="sm:col-span-2 lg:col-span-1">
                            <span class="text-xs font-medium text-slate-500 mb-1 block">Tempat &amp; Tanggal Lahir</span>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black" x-text="selectedBooking?.client?.birth_place || '-'"></div>
                                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black" x-text="selectedBooking?.client?.dob || '-'"></div>
                            </div>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="text-xs font-medium text-slate-500 mb-1 block">Pendidikan &amp; Pekerjaan</span>
                            <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black" x-text="(selectedBooking?.client?.education || '-') + ' • ' + (selectedBooking?.client?.occupation || '-')"></div>
                        </div>
                        <div class="sm:col-span-2 lg:col-span-3">
                            <span class="text-xs font-medium text-slate-500 mb-1 block">Alamat Lengkap</span>
                            <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black" x-text="selectedBooking?.client?.address || '-'"></div>
                        </div>
                    </div>
                </div>

                {{-- LAYANAN & PENUGASAN TIM --}}
                <div class="bg-white rounded-2xl p-5 border border-[#EDE1FA]">
                    <h4 class="font-bold text-xs uppercase tracking-wider text-purple-deep pb-2.5 mb-3.5 border-b border-[#EDE1FA] flex items-center gap-1.5">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Layanan &amp; Penugasan Tim
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <span class="text-xs font-medium text-slate-500 mb-1 block">Kategori Layanan</span>
                            <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black uppercase" x-text="selectedBooking?.kategori || '-'"></div>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-slate-500 mb-1 block">Paket / Jenis Layanan</span>
                            <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black" x-text="selectedBooking?.client?.counseling_type || selectedBooking?.client?.service_type || '-'"></div>
                        </div>
                    </div>

                    <template x-if="selectedBooking?.kategori === 'konseling'">
                        <div class="mt-4">
                            <span class="text-xs font-medium text-slate-500 mb-1 block">Psikolog Praktik</span>
                            <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center justify-between text-sm font-medium text-black">
                                <span x-text="selectedBooking.counselor?.name || 'Belum ditentukan'"></span>
                                <span class="text-xs text-slate-500 font-medium" x-text="selectedBooking.counselor?.specialization || ''"></span>
                            </div>
                        </div>
                    </template>

                    <template x-if="selectedBooking?.kategori === 'psikotes'">
                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <span class="text-xs font-medium text-slate-500 mb-1 block">Staff Penguji</span>
                                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black" x-text="selectedBooking.staff_penguji?.name || '-'"></div>
                            </div>
                            <div>
                                <span class="text-xs font-medium text-slate-500 mb-1 block">Staff Koreksi</span>
                                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black" x-text="selectedBooking.staff_koreksi?.name || '-'"></div>
                            </div>
                            <div>
                                <span class="text-xs font-medium text-slate-500 mb-1 block">Staff Pelapor</span>
                                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black" x-text="selectedBooking.staff_pelapor?.name || '-'"></div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- DETAIL JADWAL & WAKTU PELAKSANAAN --}}
                <div class="bg-white rounded-2xl p-5 border border-[#EDE1FA]">
                    <h4 class="font-bold text-xs uppercase tracking-wider text-purple-deep pb-2.5 mb-3.5 border-b border-[#EDE1FA] flex items-center gap-1.5">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Detail Jadwal &amp; Waktu Pelaksanaan
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <span class="text-xs font-medium text-slate-500 mb-1 block">Tanggal Booking Dibuat</span>
                            <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black" x-text="selectedBooking?.tanggal_booking_dibuat_formatted || '-'"></div>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-slate-500 mb-1 block">Jadwal Pelaksanaan Sesi</span>
                            <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black" x-text="selectedBooking?.tanggal_dijadwalkan_formatted || 'Belum Dijadwalkan'"></div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <span class="text-xs font-medium text-slate-500 mb-1 block">Catatan Sesi &amp; Informasi Tambahan</span>
                        <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] text-sm font-medium text-black whitespace-pre-line" x-text="selectedBooking?.notes || '-'"></div>
                    </div>

                    <template x-if="selectedBooking?.participants && selectedBooking.participants.length > 0">
                        <div class="mt-4 pt-4 border-t border-[#EDE1FA]">
                            <span class="text-xs font-medium text-slate-500 mb-2 block" x-text="'Daftar Peserta (' + selectedBooking.participants.length + ' orang)'"></span>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="(nama, idx) in selectedBooking.participants" :key="idx">
                                    <span class="px-3 py-1.5 bg-slate-50 rounded-lg border border-slate-100 text-xs font-medium text-black" x-text="(idx + 1) + '. ' + nama"></span>
                                </template>
                            </div>
                        </div>
                    </template>

                    <template x-if="selectedBooking?.payment">
                        <div class="mt-4 pt-4 border-t border-[#EDE1FA] grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <span class="text-xs font-medium text-slate-500 mb-1 block">Nominal</span>
                                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black" x-text="selectedBooking.payment.amount_formatted"></div>
                            </div>
                            <div>
                                <span class="text-xs font-medium text-slate-500 mb-1 block">Status Bayar</span>
                                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black uppercase" x-text="selectedBooking.payment.status"></div>
                            </div>
                            <div>
                                <span class="text-xs font-medium text-slate-500 mb-1 block">Metode</span>
                                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black" x-text="selectedBooking.payment.method"></div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- RIWAYAT BOOKING LAIN KLIEN --}}
                <template x-if="selectedBooking?.client?.bookings && selectedBooking.client.bookings.length > 1">
                    <div class="bg-white rounded-2xl p-5 border border-[#EDE1FA]">
                        <h4 class="font-bold text-xs uppercase tracking-wider text-purple-deep pb-2.5 mb-3.5 border-b border-[#EDE1FA] flex items-center justify-between">
                            <span class="flex items-center gap-1.5">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                Riwayat Sesi Booking Klien Lainnya
                            </span>
                            <span class="text-xs font-medium text-slate-500" x-text="selectedBooking.client.bookings.length + ' sesi tercatat'"></span>
                        </h4>
                        <div class="divide-y divide-[#EDE1FA]">
                            <template x-for="b in selectedBooking.client.bookings" :key="b.id">
                                <div class="py-2.5 flex items-center justify-between text-sm font-medium text-black">
                                    <div>
                                        <span x-text="'Booking #' + b.id"></span>
                                        <template x-if="b.id === selectedBooking.id">
                                            <span class="ml-1 text-xs text-slate-500">(Sesi ini)</span>
                                        </template>
                                        <span class="text-slate-500 text-xs ml-2" x-text="b.tanggal_dijadwalkan"></span>
                                        <template x-if="b.counselor_name">
                                            <span class="text-slate-500 text-xs ml-1" x-text="'• ' + b.counselor_name"></span>
                                        </template>
                                    </div>
                                    <span class="text-xs font-medium text-black" x-text="b.status_label"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

            </div>

            {{-- Footer Modal Detail --}}
            <div class="p-4 border-t border-[#EDE1FA] bg-[#FAF8FD] flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2 flex-wrap">
                    @if(auth()->user()->isAdmin())
                    <button 
                        type="button" 
                        @click="openEdit(selectedBooking)"
                        class="px-4 py-2 bg-purple-deep text-white text-xs font-semibold rounded-lg hover:bg-purple-deep/90 transition cursor-pointer flex items-center gap-1.5 shadow-xs"
                    >
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span>Edit Jadwal</span>
                    </button>
                    @endif

                    @if(!auth()->user()->isPsikolog())
                    <template x-if="selectedBooking?.client?.edit_url">
                        <a 
                            :href="selectedBooking.client.edit_url" 
                            class="px-3.5 py-2 border border-[#D9C2F0] bg-white text-purple-deep text-xs font-semibold rounded-lg hover:bg-purple-50 transition cursor-pointer flex items-center gap-1.5 shadow-xs"
                        >
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            <span>Edit Profil Klien</span>
                        </a>
                    </template>
                    @endif

                    <template x-if="selectedBooking?.client?.show_url">
                        <a 
                            :href="selectedBooking.client.show_url" 
                            class="px-3 py-2 text-xs font-semibold text-purple-deep hover:underline flex items-center gap-1"
                        >
                            <span>Buka Halaman Lengkap</span>
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        </a>
                    </template>
                </div>

                <button 
                    type="button" 
                    @click="detailModalOpen = false"
                    class="px-5 py-2 bg-white hover:bg-purple-50 text-purple-deep border border-[#D9C2F0] text-xs font-semibold rounded-lg shadow-xs transition cursor-pointer"
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
    >
        <div 
            class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl border border-[#EDE1FA] overflow-hidden flex flex-col max-h-[90vh]"
        >
            {{-- Header Edit --}}
            <div class="p-5 border-b border-[#EDE1FA] bg-[#FAF8FD] flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-deep/10 text-purple-deep flex items-center justify-center font-bold text-sm">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-[#2A2035]">
                            Edit Booking <span class="text-purple-deep" x-text="'#' + (editForm.id || '')"></span>
                        </h3>
                        <p class="text-xs text-slate-500 font-medium">Perbarui data booking, jadwal sesi, dan penugasan</p>
                    </div>
                </div>

                <button 
                    type="button" 
                    @click="requestCloseEdit()"
                    class="w-8 h-8 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-black flex items-center justify-center transition cursor-pointer"
                    title="Tutup"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            {{-- Form Edit --}}
            <form :action="editForm.action_url" method="POST" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                @method('PUT')

                <div class="p-6 overflow-y-auto custom-scrollbar space-y-4 flex-1">
                    
                    {{-- Klien --}}
                    <div>
                        <label class="text-xs font-medium text-slate-500 mb-1 block">Klien <span class="text-red-500">*</span></label>
                        <select 
                            name="client_id" 
                            x-model="editForm.client_id" 
                            @change="onClientChange($event)"
                            required
                            class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black min-h-[42px] focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white"
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
                            <label class="text-xs font-medium text-slate-500 mb-1 block">Kategori <span class="text-red-500">*</span></label>
                            <select 
                                name="kategori" 
                                x-model="editForm.kategori" 
                                required
                                class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black min-h-[42px] focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white"
                            >
                                <option value="konseling">Konseling</option>
                                <option value="psikotes">Psikotes</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-slate-500 mb-1 block">Status <span class="text-red-500">*</span></label>
                            <select 
                                name="status" 
                                x-model="editForm.status" 
                                required
                                class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black min-h-[42px] focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white"
                            >
                                <template x-if="editForm.kategori === 'konseling'">
                                    <optgroup label="Status Konseling">
                                        <option value="data_masuk">Data Masuk</option>
                                        <option value="waiting_list">Waiting List</option>
                                        <option value="menunggu_konfirmasi">Menunggu Konfirmasi</option>
                                        <option value="terjadwal_gocal">Terjadwal &amp; Konfirmasi (GCal)</option>
                                        <option value="reminder_h1">Reminder H-1</option>
                                        <option value="perlu_bayar_overtime">Perlu Bayar Overtime</option>
                                        <option value="perlu_sesi_lanjutan">Perlu Sesi Lanjutan</option>
                                        <option value="selesai">Selesai</option>
                                        <option value="batal">Dibatalkan</option>
                                    </optgroup>
                                </template>
                                <template x-if="editForm.kategori !== 'konseling'">
                                    <optgroup label="Status Psikotes">
                                        <option value="baru">Berjalan</option>
                                        <option value="selesai">Selesai</option>
                                        <option value="batal">Dibatalkan</option>
                                    </optgroup>
                                </template>
                            </select>
                        </div>
                    </div>

                    {{-- Tanggal Dibuat & Tanggal Dijadwalkan --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-slate-500 mb-1 block">Tanggal Booking Dibuat <span class="text-red-500">*</span></label>
                            <input 
                                type="date" 
                                name="tanggal_booking_dibuat" 
                                x-model="editForm.tanggal_booking_dibuat" 
                                required
                                class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black min-h-[42px] focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white"
                            >
                        </div>
                        <div>
                            <label class="text-xs font-medium text-slate-500 mb-1 block">Tanggal Dijadwalkan</label>
                            <input 
                                type="date" 
                                name="tanggal_dijadwalkan" 
                                x-model="editForm.tanggal_dijadwalkan" 
                                class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black min-h-[42px] focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white"
                            >
                        </div>
                    </div>

                    {{-- Konseling: Konselor --}}
                    <div x-show="editForm.kategori === 'konseling'" class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <label class="text-xs font-medium text-slate-500 mb-1 block">Pilih Psikolog Praktik</label>
                        <select 
                            name="counselor_id" 
                            x-model="editForm.counselor_id"
                            class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black min-h-[42px] focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white"
                        >
                            <option value="">Belum Ditentukan (Unassigned)</option>
                            @foreach($counselors as $counselor)
                            <option value="{{ $counselor->id }}">
                                {{ $counselor->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Psikotes: Penugasan Staff --}}
                    <div x-show="editForm.kategori === 'psikotes'" class="bg-slate-50 p-4 rounded-xl border border-slate-100 space-y-3">
                        <span class="block text-xs font-medium text-slate-500 uppercase tracking-wider">Penugasan Staff Psikotes</span>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="text-xs font-medium text-slate-500 mb-1 block">Staff Penguji</label>
                                <select 
                                    name="staff_penguji_id" 
                                    x-model="editForm.staff_penguji_id"
                                    class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black min-h-[42px] focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white"
                                >
                                    <option value="">Pilih Staff...</option>
                                    @foreach($staffs as $staff)
                                    <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-slate-500 mb-1 block">Staff Koreksi</label>
                                <select 
                                    name="staff_koreksi_id" 
                                    x-model="editForm.staff_koreksi_id"
                                    class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black min-h-[42px] focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white"
                                >
                                    <option value="">Pilih Staff...</option>
                                    @foreach($staffs as $staff)
                                    <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-slate-500 mb-1 block">Staff Pelapor</label>
                                <select 
                                    name="staff_pelapor_id" 
                                    x-model="editForm.staff_pelapor_id"
                                    class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black min-h-[42px] focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white"
                                >
                                    <option value="">Pilih Staff...</option>
                                    @foreach($staffs as $staff)
                                    <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Peserta --}}
                    <div x-show="['industri', 'company'].includes(editForm.client_jenis)" class="bg-slate-50 p-4 rounded-xl border border-slate-100 space-y-2.5">
                        <label class="text-xs font-medium text-slate-500 mb-1 block">Daftar Peserta / Karyawan Industri</label>
                        <template x-for="(p, i) in editForm.participants" :key="i">
                            <div class="flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-slate-200 text-black font-medium text-[10px] flex items-center justify-center shrink-0" x-text="i + 1"></span>
                                <input 
                                    type="text" 
                                    :name="'participants[' + i + ']'" 
                                    x-model="editForm.participants[i]" 
                                    placeholder="Nama peserta..."
                                    class="flex-1 px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black min-h-[42px] focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white"
                                >
                                <button 
                                    type="button" 
                                    @click="removeParticipant(i)" 
                                    x-show="editForm.participants.length > 1"
                                    class="text-red-500 hover:text-red-600 p-1 cursor-pointer"
                                    title="Hapus Peserta"
                                >
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </div>
                        </template>
                        <button 
                            type="button" 
                            @click="addParticipant()" 
                            class="text-xs text-purple-deep font-semibold hover:underline inline-flex items-center gap-1 mt-1 cursor-pointer"
                        >
                            <span>+ Tambah Peserta</span>
                        </button>
                    </div>

                    {{-- Catatan --}}
                    <div>
                        <label class="text-xs font-medium text-slate-500 mb-1 block">Catatan / Detail Sesi (Waktu, Tipe, Lokasi)</label>
                        <textarea 
                            name="notes" 
                            x-model="editForm.notes" 
                            rows="3" 
                            class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white"
                            placeholder="Catatan tambahan, waktu sesi, atau tautan meeting..."
                        ></textarea>
                    </div>

                </div>

                {{-- Footer Edit Buttons --}}
                <div class="p-4 border-t border-[#EDE1FA] bg-[#FAF8FD] flex items-center justify-end gap-2.5">
                    <button 
                        type="button" 
                        @click="requestCloseEdit()" 
                        class="px-4 py-2 border border-[#D9C2F0] bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold rounded-lg shadow-xs transition cursor-pointer inline-flex items-center gap-1.5"
                    >
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        <span>Batal</span>
                    </button>
                    <button 
                        type="submit" 
                        class="px-5 py-2 bg-purple-deep hover:bg-purple-deep/90 text-white text-xs font-semibold rounded-lg transition shadow-xs cursor-pointer flex items-center gap-1.5"
                    >
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Konfirmasi Unsaved Changes --}}
    <div x-show="unsavedModalOpen" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
        <div class="bg-white rounded-2xl shadow-[0_30px_70px_rgba(74,35,128,0.2)] max-w-md w-full p-6 space-y-4">
            <h3 class="text-lg font-bold text-purple-deep">Perubahan Belum Disimpan</h3>
            <p class="text-sm text-slate-600">Anda memiliki perubahan data booking yang belum disimpan. Tetap tinggalkan form edit ini?</p>
            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" @click="unsavedModalOpen = false" class="px-4 py-2 rounded-lg bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-sm cursor-pointer">Kembali Edit</button>
                <button type="button" @click="discardEditChanges()" class="px-5 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold text-sm shadow-xs cursor-pointer">Ya, Tinggalkan</button>
            </div>
        </div>
    </div>

</div>

{{-- Alpine.js Booking Manager Script --}}
<script>
function bookingManager() {
    return {
        detailModalOpen: false,
        editModalOpen: false,
        unsavedModalOpen: false,
        editSnapshot: null,
        selectedBooking: null,
        editForm: {
            id: null,
            client_id: '',
            client_jenis: 'individu',
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
            this.editForm.client_jenis = booking.client?.jenis || 'individu';
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
            this.editSnapshot = JSON.stringify(this.editForm);

            this.detailModalOpen = false;
            this.editModalOpen = true;
        },

        isEditDirty() {
            return this.editSnapshot !== null && JSON.stringify(this.editForm) !== this.editSnapshot;
        },

        requestCloseEdit() {
            if (this.isEditDirty()) {
                this.unsavedModalOpen = true;
                return;
            }
            this.editModalOpen = false;
        },

        discardEditChanges() {
            this.unsavedModalOpen = false;
            this.editModalOpen = false;
            this.editSnapshot = null;
        },

        onClientChange(event) {
            const opt = event.target.selectedOptions[0];
            if (opt) {
                this.editForm.client_jenis = opt.dataset.jenis || 'individu';
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
