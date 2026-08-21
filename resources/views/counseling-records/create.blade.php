@extends('layouts.dashboard')

@section('title', 'Tambah Catatan Konseling — UC PSC')
@section('page-title', 'Tambah Catatan Konseling')
@section('page-subtitle', 'Catat sesi konseling baru')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6">
        <form action="{{ route('counseling-records.store') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label for="client_id" class="block text-sm font-semibold text-[#5B4A73] mb-2">Klien <span class="text-red-400">*</span></label>
                    <select id="client_id" name="client_id" required
                        class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm text-[#5B4A73]">
                        <option value="">Pilih klien...</option>
                        @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                        @endforeach
                    </select>
                    @error('client_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="counselor_id" class="block text-sm font-semibold text-[#5B4A73] mb-2">Konselor <span class="text-red-400">*</span></label>
                    <select id="counselor_id" name="counselor_id" required
                        class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm text-[#5B4A73]">
                        <option value="">Pilih konselor...</option>
                        @foreach($counselors as $counselor)
                        <option value="{{ $counselor->id }}" {{ old('counselor_id') == $counselor->id ? 'selected' : '' }}>{{ $counselor->name }} — {{ $counselor->specialization ?? 'Umum' }}</option>
                        @endforeach
                    </select>
                    @error('counselor_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label for="type" class="block text-sm font-semibold text-[#5B4A73] mb-2">Tipe Konseling <span class="text-red-400">*</span></label>
                    <select id="type" name="type" required
                        class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm text-[#5B4A73]">
                        <option value="whatsapp" {{ old('type') === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                        <option value="tatap_muka" {{ old('type') === 'tatap_muka' ? 'selected' : '' }}>Tatap Muka</option>
                        <option value="online" {{ old('type') === 'online' ? 'selected' : '' }}>Online</option>
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-sm font-semibold text-[#5B4A73] mb-2">Status <span class="text-red-400">*</span></label>
                    <select id="status" name="status" required
                        class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm text-[#5B4A73]">
                        <option value="scheduled" {{ old('status') === 'scheduled' ? 'selected' : '' }}>Terjadwal</option>
                        <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label for="scheduled_at" class="block text-sm font-semibold text-[#5B4A73] mb-2">Jadwal Mulai</label>
                    <input type="datetime-local" id="scheduled_at" name="scheduled_at" value="{{ old('scheduled_at') }}"
                        class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm text-[#5B4A73]">
                </div>
                <div>
                    <label for="end_time" class="block text-sm font-semibold text-[#5B4A73] mb-2">Jadwal Selesai</label>
                    <input type="datetime-local" id="end_time" name="end_time" value="{{ old('end_time') }}"
                        class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm text-[#5B4A73]">
                </div>
            </div>

            <div>
                <label for="location" class="block text-sm font-semibold text-[#5B4A73] mb-2">Lokasi / Link Ruangan</label>
                <input type="text" id="location" name="location" value="{{ old('location') }}" placeholder="Contoh: Ruang 1 atau link Google Meet"
                    class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm">
            </div>

            <div>
                <label for="summary" class="block text-sm font-semibold text-[#5B4A73] mb-2">Ringkasan / Catatan</label>
                <textarea id="summary" name="summary" rows="4" placeholder="Ringkasan konseling..."
                    class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm resize-none">{{ old('summary') }}</textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="bg-purple-deep text-white px-6 py-3 rounded-xl text-sm font-semibold hover:opacity-90 transition">Simpan Catatan</button>
                <a href="{{ route('counseling-records.index') }}" class="px-6 py-3 rounded-xl text-sm font-semibold text-[#6B5B85] border border-[#D9C2F0] hover:bg-[#F7F5FB] transition">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
