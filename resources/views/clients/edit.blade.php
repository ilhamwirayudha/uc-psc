@extends('layouts.dashboard')

@section('title', 'Edit Klien — UC PSC')
@section('page-title', 'Edit Klien')
@section('page-subtitle', 'Perbarui data dan jadwal klien')

@section('content')
<form action="{{ route('clients.update', $client) }}" method="POST" x-data="{ serviceType: '{{ old('service_type', $client->service_type ?? 'konseling') }}' }" class="space-y-6">
    @csrf @method('PUT')

    <div class="grid lg:grid-cols-12 gap-6">
        {{-- Left Column: Identitas & Layanan Klien (7 cols on lg) --}}
        <div class="lg:col-span-7 space-y-6">
            {{-- Card 1: Data Identitas Klien --}}
            <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-5">
                <div class="flex items-center gap-3 pb-3 border-b border-[#EDE1FA]">
                    <div class="w-8 h-8 rounded-lg bg-purple-deep/10 text-purple-deep flex items-center justify-center font-bold text-sm">
                        1
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-purple-deep">Identitas & Kontak Klien</h3>
                        <p class="text-xs text-[#6B5B85]">Informasi dasar dan kontak klien yang dapat dihubungi</p>
                    </div>
                </div>

                <div>
                    <label for="name" class="block text-sm font-semibold text-[#5B4A73] mb-1.5">Nama Lengkap Klien <span class="text-red-400">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $client->name) }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm @error('name') border-red-300 @enderror">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="phone" class="block text-sm font-semibold text-[#5B4A73] mb-1.5">No. Telepon / WA</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone', $client->phone) }}" placeholder="08xx-xxxx-xxxx"
                            class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-semibold text-[#5B4A73] mb-1.5">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $client->email) }}" placeholder="nama@email.com"
                            class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm @error('email') border-red-300 @enderror">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="gender" class="block text-sm font-semibold text-[#5B4A73] mb-1.5">Jenis Kelamin</label>
                        <select id="gender" name="gender"
                            class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm text-[#5B4A73]">
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="l" {{ old('gender', $client->gender) === 'l' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="p" {{ old('gender', $client->gender) === 'p' ? 'selected' : '' }}>Perempuan</option>
                            <option value="non_binary" {{ old('gender', $client->gender) === 'non_binary' ? 'selected' : '' }}>Non-biner (Non-binary)</option>
                            <option value="transgender" {{ old('gender', $client->gender) === 'transgender' ? 'selected' : '' }}>Transgender</option>
                            <option value="prefer_not_to_say" {{ old('gender', $client->gender) === 'prefer_not_to_say' ? 'selected' : '' }}>Memilih Tidak Menyebutkan</option>
                            <option value="other" {{ old('gender', $client->gender) === 'other' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label for="dob" class="block text-sm font-semibold text-[#5B4A73] mb-1.5">Tanggal Lahir</label>
                        <input type="date" id="dob" name="dob" value="{{ old('dob', $client->dob ? $client->dob->format('Y-m-d') : '') }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm text-[#5B4A73]">
                    </div>
                </div>
            </div>

            {{-- Card 2: Layanan & Status Klien --}}
            <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-5">
                <div class="flex items-center gap-3 pb-3 border-b border-[#EDE1FA]">
                    <div class="w-8 h-8 rounded-lg bg-orange/10 text-orange flex items-center justify-center font-bold text-sm">
                        2
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-purple-deep">Pilihan Layanan & Sumber Rujukan</h3>
                        <p class="text-xs text-[#6B5B85]">Tentukan kategori layanan psikologi dan status klien</p>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="service_type" class="block text-sm font-semibold text-[#5B4A73] mb-1.5">Jenis Layanan <span class="text-red-400">*</span></label>
                        <select id="service_type" name="service_type" x-model="serviceType" required
                            class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm text-[#5B4A73]">
                            <option value="konseling" {{ old('service_type', $client->service_type) === 'konseling' ? 'selected' : '' }}>Konseling</option>
                            <option value="psikotes" {{ old('service_type', $client->service_type) === 'psikotes' ? 'selected' : '' }}>Psikotes</option>
                        </select>
                    </div>
                    <div>
                        <label for="counseling_type" class="block text-sm font-semibold text-[#5B4A73] mb-1.5" x-text="serviceType === 'psikotes' ? 'Jenis Psikotes / Paket' : 'Jenis Konseling / Detail'">Jenis Konseling / Detail</label>
                        
                        {{-- Dropdown Konseling --}}
                        <select x-show="serviceType === 'konseling'" id="counseling_type_konseling" name="counseling_type" :disabled="serviceType !== 'konseling'"
                            class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm text-[#5B4A73]">
                            <option value="">Pilih Jenis Konseling...</option>
                            <option value="Konseling Individu" {{ old('counseling_type', $client->counseling_type) === 'Konseling Individu' ? 'selected' : '' }}>Konseling Individu</option>
                            <option value="Konseling Pasangan / Pernikahan" {{ old('counseling_type', $client->counseling_type) === 'Konseling Pasangan / Pernikahan' ? 'selected' : '' }}>Konseling Pasangan / Pernikahan</option>
                            <option value="Konseling Pra-Nikah" {{ old('counseling_type', $client->counseling_type) === 'Konseling Pra-Nikah' ? 'selected' : '' }}>Konseling Pra-Nikah</option>
                            <option value="Konseling Anak & Remaja" {{ old('counseling_type', $client->counseling_type) === 'Konseling Anak & Remaja' ? 'selected' : '' }}>Konseling Anak & Remaja</option>
                            <option value="Konseling Keluarga" {{ old('counseling_type', $client->counseling_type) === 'Konseling Keluarga' ? 'selected' : '' }}>Konseling Keluarga</option>
                            <option value="Konseling Karir / Akademik" {{ old('counseling_type', $client->counseling_type) === 'Konseling Karir / Akademik' ? 'selected' : '' }}>Konseling Karir / Akademik</option>
                            <option value="Konseling Kelompok (Group)" {{ old('counseling_type', $client->counseling_type) === 'Konseling Kelompok (Group)' ? 'selected' : '' }}>Konseling Kelompok (Group)</option>
                            <option value="Lainnya" {{ old('counseling_type', $client->counseling_type) === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>

                        {{-- Dropdown Psikotes --}}
                        <select x-cloak x-show="serviceType === 'psikotes'" id="counseling_type_psikotes" name="counseling_type" :disabled="serviceType !== 'psikotes'"
                            class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm text-[#5B4A73]">
                            <option value="">Pilih Jenis Psikotes...</option>
                            <option value="Tes IQ & Intelegensi (Kognitif)" {{ old('counseling_type', $client->counseling_type) === 'Tes IQ & Intelegensi (Kognitif)' ? 'selected' : '' }}>Tes IQ & Intelegensi (Kognitif)</option>
                            <option value="Tes Minat Bakat & Penjurusan (Pendidikan/Karir)" {{ old('counseling_type', $client->counseling_type) === 'Tes Minat Bakat & Penjurusan (Pendidikan/Karir)' ? 'selected' : '' }}>Tes Minat Bakat & Penjurusan (Pendidikan/Karir)</option>
                            <option value="Tes Kepribadian (Personality Profile)" {{ old('counseling_type', $client->counseling_type) === 'Tes Kepribadian (Personality Profile)' ? 'selected' : '' }}>Tes Kepribadian (Personality Profile)</option>
                            <option value="Tes Kesiapan Masuk Sekolah (Anak/TK/SD)" {{ old('counseling_type', $client->counseling_type) === 'Tes Kesiapan Masuk Sekolah (Anak/TK/SD)' ? 'selected' : '' }}>Tes Kesiapan Masuk Sekolah (Anak/TK/SD)</option>
                            <option value="Asesmen Rekrutmen & Seleksi Karyawan" {{ old('counseling_type', $client->counseling_type) === 'Asesmen Rekrutmen & Seleksi Karyawan' ? 'selected' : '' }}>Asesmen Rekrutmen & Seleksi Karyawan</option>
                            <option value="Evaluasi Tumbuh Kembang & Perilaku Anak" {{ old('counseling_type', $client->counseling_type) === 'Evaluasi Tumbuh Kembang & Perilaku Anak' ? 'selected' : '' }}>Evaluasi Tumbuh Kembang & Perilaku Anak</option>
                            <option value="Pemeriksaan Klinis & Diagnostik Psikologis" {{ old('counseling_type', $client->counseling_type) === 'Pemeriksaan Klinis & Diagnostik Psikologis' ? 'selected' : '' }}>Pemeriksaan Klinis & Diagnostik Psikologis</option>
                            <option value="Lainnya" {{ old('counseling_type', $client->counseling_type) === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="source" class="block text-sm font-semibold text-[#5B4A73] mb-1.5">Sumber / Rujukan <span class="text-red-400">*</span></label>
                        <select id="source" name="source" required
                            class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm text-[#5B4A73]">
                            <option value="whatsapp" {{ old('source', $client->source) === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                            <option value="walk_in" {{ old('source', $client->source) === 'walk_in' ? 'selected' : '' }}>Walk In (Datang Langsung)</option>
                            <option value="referral" {{ old('source', $client->source) === 'referral' ? 'selected' : '' }}>Referral / Rekomendasi</option>
                            <option value="other" {{ old('source', $client->source) === 'other' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-semibold text-[#5B4A73] mb-1.5">Status Klien <span class="text-red-400">*</span></label>
                        <select id="status" name="status" required
                            class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm text-[#5B4A73]">
                            <option value="unassigned" {{ old('status', $client->status) === 'unassigned' ? 'selected' : '' }}>Belum Di-assign</option>
                            <option value="assigned" {{ old('status', $client->status) === 'assigned' ? 'selected' : '' }}>Telah Di-assign</option>
                            <option value="ongoing" {{ old('status', $client->status) === 'ongoing' ? 'selected' : '' }}>Sedang Konseling</option>
                            <option value="unpaid" {{ old('status', $client->status) === 'unpaid' ? 'selected' : '' }}>Belum Membayar</option>
                            <option value="paid" {{ old('status', $client->status) === 'paid' ? 'selected' : '' }}>Lunas</option>
                            <option value="needs_followup" {{ old('status', $client->status) === 'needs_followup' ? 'selected' : '' }}>Butuh Sesi Lanjutan</option>
                            <option value="completed" {{ old('status', $client->status) === 'completed' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Jadwal, Konselor & Catatan (5 cols on lg) --}}
        <div class="lg:col-span-5 space-y-6">
            {{-- Card 3: Jadwal & Konselor --}}
            @php
                $currentCounselorId = old('counselor_id', $upcomingRecord?->counselor_id ?? $activePairing?->counselor_id ?? '');
                $currentScheduledAt = old('scheduled_at', $upcomingRecord?->scheduled_at?->format('Y-m-d\TH:i') ?? '');
                $currentEndTime = old('end_time', $upcomingRecord?->end_time?->format('Y-m-d\TH:i') ?? '');
                $currentSessionType = old('session_type', $upcomingRecord?->type ?? 'tatap_muka');
                $currentLocation = old('location', $upcomingRecord?->location ?? '');
            @endphp
            <div class="bg-[#FBF9FE] rounded-2xl border border-[#E9DAFA] p-6 space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-[#EDE1FA]">
                    <div class="w-8 h-8 rounded-lg bg-purple-deep/10 text-purple-deep flex items-center justify-center font-bold text-sm">
                        3
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-purple-deep" x-text="serviceType === 'psikotes' ? 'Jadwal Psikotes & Tester' : 'Jadwal Konseling & Konselor'">Jadwal Sesi & Konselor</h3>
                        <p class="text-xs text-[#6B5B85]">Perbarui jadwal kegiatan dan penanggung jawab klien</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="counselor_id" class="block text-xs font-semibold text-[#5B4A73] mb-1.5" x-text="serviceType === 'psikotes' ? 'Pilih Konselor / Tester' : 'Pilih Konselor'">Pilih Konselor</label>
                        <select id="counselor_id" name="counselor_id"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] bg-white focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm text-[#5B4A73]">
                            <option value="">Belum Ditentukan (Unassigned)</option>
                            @foreach($counselors as $counselor)
                            <option value="{{ $counselor->id }}" {{ $currentCounselorId == $counselor->id ? 'selected' : '' }}>
                                {{ $counselor->name }} ({{ $counselor->specialization ?? 'Umum' }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="session_type" class="block text-xs font-semibold text-[#5B4A73] mb-1.5">Tipe Sesi</label>
                        <select id="session_type" name="session_type"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] bg-white focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm text-[#5B4A73]">
                            <option value="tatap_muka" {{ $currentSessionType === 'tatap_muka' ? 'selected' : '' }}>Tatap Muka (Offline)</option>
                            <option value="online" {{ $currentSessionType === 'online' ? 'selected' : '' }}>Online (Zoom / GMeet)</option>
                            <option value="whatsapp" {{ $currentSessionType === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                        </select>
                    </div>

                    <div class="grid sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2 gap-3">
                        <div>
                            <label for="scheduled_at" class="block text-xs font-semibold text-[#5B4A73] mb-1.5">Jadwal Mulai</label>
                            <input type="datetime-local" id="scheduled_at" name="scheduled_at" value="{{ $currentScheduledAt }}"
                                class="w-full px-3 py-2 rounded-xl border border-[#D9C2F0] bg-white focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm text-[#5B4A73]">
                        </div>
                        <div>
                            <label for="end_time" class="block text-xs font-semibold text-[#5B4A73] mb-1.5">Jadwal Selesai</label>
                            <input type="datetime-local" id="end_time" name="end_time" value="{{ $currentEndTime }}"
                                class="w-full px-3 py-2 rounded-xl border border-[#D9C2F0] bg-white focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm text-[#5B4A73]">
                        </div>
                    </div>

                    <div>
                        <label for="location" class="block text-xs font-semibold text-[#5B4A73] mb-1.5">Lokasi / Ruangan / Link Meeting</label>
                        <input type="text" id="location" name="location" value="{{ $currentLocation }}" placeholder="Contoh: Ruang Konseling 2 atau link GMeet..."
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] bg-white focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm">
                    </div>
                </div>
            </div>

            {{-- Card 4: Catatan --}}
            <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-3">
                <label for="notes" class="block text-sm font-semibold text-[#5B4A73]">Catatan & Keterangan Tambahan</label>
                <textarea id="notes" name="notes" rows="4" placeholder="Tuliskan keluhan awal, preferensi jadwal, atau catatan penting lainnya..."
                    class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm resize-none">{{ old('notes', $client->notes) }}</textarea>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('clients.index') }}" class="px-6 py-3 rounded-xl text-sm font-semibold text-[#6B5B85] border border-[#D9C2F0] hover:bg-[#F7F5FB] transition">
                    Batal
                </a>
                <button type="submit" class="bg-purple-deep text-white px-8 py-3 rounded-xl text-sm font-semibold hover:opacity-90 transition shadow-sm flex items-center gap-2">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                    Perbarui Data Klien
                </button>
            </div>
        </div>
    </div>
</form>
@endsection
