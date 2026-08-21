@extends('layouts.dashboard')

@section('title', 'Edit Catatan Konseling — UC PSC')
@section('page-title', 'Edit Catatan Konseling')
@section('page-subtitle', 'Perbarui data catatan konseling')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6">
        <form action="{{ route('counseling-records.update', $counselingRecord) }}" method="POST" class="space-y-5">
            @csrf @method('PUT')

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label for="client_id" class="block text-sm font-semibold text-[#5B4A73] mb-2">Klien <span class="text-red-400">*</span></label>
                    <select id="client_id" name="client_id" required
                        class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm text-[#5B4A73]">
                        @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ old('client_id', $counselingRecord->client_id) == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="counselor_id" class="block text-sm font-semibold text-[#5B4A73] mb-2">Konselor <span class="text-red-400">*</span></label>
                    <select id="counselor_id" name="counselor_id" required
                        class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm text-[#5B4A73]">
                        @foreach($counselors as $counselor)
                        <option value="{{ $counselor->id }}" {{ old('counselor_id', $counselingRecord->counselor_id) == $counselor->id ? 'selected' : '' }}>{{ $counselor->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label for="type" class="block text-sm font-semibold text-[#5B4A73] mb-2">Tipe <span class="text-red-400">*</span></label>
                    <select id="type" name="type" required class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm text-[#5B4A73]">
                        <option value="whatsapp" {{ old('type', $counselingRecord->type) === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                        <option value="tatap_muka" {{ old('type', $counselingRecord->type) === 'tatap_muka' ? 'selected' : '' }}>Tatap Muka</option>
                        <option value="online" {{ old('type', $counselingRecord->type) === 'online' ? 'selected' : '' }}>Online</option>
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-sm font-semibold text-[#5B4A73] mb-2">Status <span class="text-red-400">*</span></label>
                    <select id="status" name="status" required class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm text-[#5B4A73]">
                        <option value="scheduled" {{ old('status', $counselingRecord->status) === 'scheduled' ? 'selected' : '' }}>Terjadwal</option>
                        <option value="completed" {{ old('status', $counselingRecord->status) === 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="cancelled" {{ old('status', $counselingRecord->status) === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label for="scheduled_at" class="block text-sm font-semibold text-[#5B4A73] mb-2">Jadwal Mulai</label>
                    <input type="datetime-local" id="scheduled_at" name="scheduled_at" value="{{ old('scheduled_at', $counselingRecord->scheduled_at?->format('Y-m-d\TH:i')) }}"
                        class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm text-[#5B4A73]">
                </div>
                <div>
                    <label for="end_time" class="block text-sm font-semibold text-[#5B4A73] mb-2">Jadwal Selesai</label>
                    <input type="datetime-local" id="end_time" name="end_time" value="{{ old('end_time', $counselingRecord->end_time?->format('Y-m-d\TH:i')) }}"
                        class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm text-[#5B4A73]">
                </div>
            </div>

            <div>
                <label for="location" class="block text-sm font-semibold text-[#5B4A73] mb-2">Lokasi / Link Ruangan</label>
                <input type="text" id="location" name="location" value="{{ old('location', $counselingRecord->location) }}" placeholder="Contoh: Ruang 1 atau link Google Meet"
                    class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm">
            </div>

            <div>
                <label for="summary" class="block text-sm font-semibold text-[#5B4A73] mb-2">Ringkasan</label>
                <textarea id="summary" name="summary" rows="4"
                    class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm resize-none">{{ old('summary', $counselingRecord->summary) }}</textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="bg-purple-deep text-white px-6 py-3 rounded-xl text-sm font-semibold hover:opacity-90 transition">Perbarui</button>
                <a href="{{ route('counseling-records.index') }}" class="px-6 py-3 rounded-xl text-sm font-semibold text-[#6B5B85] border border-[#D9C2F0] hover:bg-[#F7F5FB] transition">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
