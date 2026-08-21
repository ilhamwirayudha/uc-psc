@extends('layouts.dashboard')

@section('title', 'Edit Booking — UC PSC')
@section('page-title', 'Edit Booking #' . $booking->id)
@section('page-subtitle', 'Perbarui data booking')

@section('content')
<div class="max-w-3xl" x-data="{
    kategori: '{{ old('kategori', $booking->kategori) }}',
    jenisKlien: '{{ $booking->client->jenis ?? 'individual' }}',
    participants: {{ json_encode($booking->participants->pluck('nama_peserta')->count() > 0 ? $booking->participants->pluck('nama_peserta')->toArray() : ['']) }},
    addParticipant() { this.participants.push('') },
    removeParticipant(i) { this.participants.splice(i, 1) }
}">
    <form action="{{ route('bookings.update', $booking) }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-5">
        @csrf @method('PUT')

        {{-- Klien --}}
        <div>
            <label class="block text-xs font-medium text-[#6B5B85] mb-1">Klien <span class="text-red-500">*</span></label>
            <select name="client_id" required @change="jenisKlien = $event.target.selectedOptions[0].dataset.jenis || 'individual'"
                class="w-full px-3 py-2 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-purple-deep">
                <option value="">Pilih Klien...</option>
                @foreach($clients as $client)
                <option value="{{ $client->id }}" data-jenis="{{ $client->jenis }}"
                    {{ old('client_id', $booking->client_id) == $client->id ? 'selected' : '' }}>
                    {{ $client->name }} ({{ ucfirst($client->jenis) }})
                </option>
                @endforeach
            </select>
        </div>

        {{-- Kategori --}}
        <div>
            <label class="block text-xs font-medium text-[#6B5B85] mb-1">Kategori <span class="text-red-500">*</span></label>
            <select name="kategori" x-model="kategori" required
                class="w-full px-3 py-2 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-purple-deep">
                <option value="konseling">Konseling</option>
                <option value="psikotes">Psikotes</option>
            </select>
        </div>

        {{-- Tanggal --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-[#6B5B85] mb-1">Tanggal Booking Dibuat <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal_booking_dibuat" value="{{ old('tanggal_booking_dibuat', $booking->tanggal_booking_dibuat->format('Y-m-d')) }}" required
                    class="w-full px-3 py-2 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-purple-deep">
            </div>
            <div>
                <label class="block text-xs font-medium text-[#6B5B85] mb-1">Tanggal Dijadwalkan</label>
                <input type="date" name="tanggal_dijadwalkan" value="{{ old('tanggal_dijadwalkan', $booking->tanggal_dijadwalkan?->format('Y-m-d')) }}"
                    class="w-full px-3 py-2 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-purple-deep">
            </div>
        </div>

        {{-- Status --}}
        <div>
            <label class="block text-xs font-medium text-[#6B5B85] mb-1">Status <span class="text-red-500">*</span></label>
            <select name="status" required
                class="w-full px-3 py-2 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-purple-deep">
                <option value="baru" {{ old('status', $booking->status) === 'baru' ? 'selected' : '' }}>Baru</option>
                <option value="lanjutan" {{ old('status', $booking->status) === 'lanjutan' ? 'selected' : '' }}>Lanjutan</option>
                <option value="selesai" {{ old('status', $booking->status) === 'selesai' ? 'selected' : '' }}>Selesai</option>
            </select>
        </div>

        {{-- Konseling: Konselor --}}
        <div x-show="kategori === 'konseling'" x-transition>
            <label class="block text-xs font-medium text-[#6B5B85] mb-1">Konselor</label>
            <select name="counselor_id" class="w-full px-3 py-2 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-purple-deep">
                <option value="">Pilih Konselor...</option>
                @foreach($counselors as $counselor)
                <option value="{{ $counselor->id }}" {{ old('counselor_id', $booking->counselor_id) == $counselor->id ? 'selected' : '' }}>{{ $counselor->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Psikotes: Staff --}}
        <div x-show="kategori === 'psikotes'" x-transition class="space-y-4">
            <div>
                <label class="block text-xs font-medium text-[#6B5B85] mb-1">Staff Penguji</label>
                <select name="staff_penguji_id" class="w-full px-3 py-2 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-purple-deep">
                    <option value="">Pilih Staff...</option>
                    @foreach($staffs as $staff)
                    <option value="{{ $staff->id }}" {{ old('staff_penguji_id', $booking->staff_penguji_id) == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-[#6B5B85] mb-1">Staff Koreksi</label>
                <select name="staff_koreksi_id" class="w-full px-3 py-2 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-purple-deep">
                    <option value="">Pilih Staff...</option>
                    @foreach($staffs as $staff)
                    <option value="{{ $staff->id }}" {{ old('staff_koreksi_id', $booking->staff_koreksi_id) == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-[#6B5B85] mb-1">Staff Pelapor</label>
                <select name="staff_pelapor_id" class="w-full px-3 py-2 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-purple-deep">
                    <option value="">Pilih Staff...</option>
                    @foreach($staffs as $staff)
                    <option value="{{ $staff->id }}" {{ old('staff_pelapor_id', $booking->staff_pelapor_id) == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Peserta (Company) --}}
        <div x-show="jenisKlien === 'company'" x-transition class="space-y-3">
            <label class="block text-xs font-medium text-[#6B5B85]">Peserta (Company)</label>
            <template x-for="(p, i) in participants" :key="i">
                <div class="flex items-center gap-2">
                    <input type="text" :name="'participants[' + i + ']'" x-model="participants[i]" placeholder="Nama peserta..."
                        class="flex-1 px-3 py-2 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-purple-deep">
                    <button type="button" @click="removeParticipant(i)" x-show="participants.length > 1"
                        class="text-red-400 hover:text-red-600 transition p-1">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
            </template>
            <button type="button" @click="addParticipant()" class="text-xs text-purple-deep font-semibold hover:underline">+ Tambah Peserta</button>
        </div>

        {{-- Notes --}}
        <div>
            <label class="block text-xs font-medium text-[#6B5B85] mb-1">Catatan</label>
            <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-purple-deep">{{ old('notes', $booking->notes) }}</textarea>
        </div>

        {{-- Buttons --}}
        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="px-5 py-2 bg-purple-deep text-white text-sm font-semibold rounded-xl hover:bg-purple-deep/90 transition shadow-sm">Simpan Perubahan</button>
            <a href="{{ route('bookings.index') }}" class="px-5 py-2 text-sm font-semibold text-[#6B5B85] border border-[#D9C2F0] rounded-xl hover:bg-[#F7F5FB] transition">Batal</a>
        </div>
    </form>
</div>
@endsection
