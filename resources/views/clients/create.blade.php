@extends('layouts.dashboard')

@section('title', 'Tambah Klien — UC PSC')
@section('page-title', 'Tambah Klien Baru')
@section('page-subtitle', 'Pendaftaran master data identitas dan kontak pribadi klien')

@section('content')
<div x-data="{
    jenis: '{{ old('jenis', request('jenis', 'individual')) }}',
    participants: {{ json_encode(old('participants', [''])) }},
    addParticipant() {
        this.participants.push('');
    },
    removeParticipant(i) {
        if (this.participants.length > 1) {
            this.participants.splice(i, 1);
        } else {
            this.participants = [''];
        }
    }
}" class="space-y-6 max-w-4xl">

    {{-- Breadcrumb & Back Link --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('clients.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-purple-deep hover:text-purple-deep/80 transition">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            <span>Kembali ke Daftar Klien</span>
        </a>
    </div>

    {{-- Error Summary Alert --}}
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-2xl p-4.5 shadow-xs">
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-xl bg-red-500 text-white flex items-center justify-center flex-shrink-0 mt-0.5 shadow-xs">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-bold text-red-800 mb-1">Mohon periksa isian data klien berikut:</h4>
                <ul class="list-disc list-inside text-xs text-red-700 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    {{-- Main Form --}}
    <form action="{{ route('clients.store') }}" method="POST" class="space-y-6">
        @csrf
        <input type="hidden" name="jenis" :value="jenis">

        {{-- CARD 1: TIPE KLIEN (3 OPSI: INDIVIDU, KELOMPOK, PERUSAHAAN) --}}
        <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-[#827299] mb-3">Tipe Pemohon / Klien</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-1.5 bg-[#F7F5FB] rounded-xl border border-[#EDE1FA]">
                    {{-- Opsi 1: Individu --}}
                    <button type="button"
                        @click="jenis = 'individual'"
                        :class="jenis === 'individual' ? 'bg-white text-purple-deep shadow-xs border border-[#D9C2F0]' : 'text-[#6B5B85] hover:text-purple-deep'"
                        class="flex items-center justify-center gap-2.5 py-3 px-3 rounded-lg text-xs font-bold cursor-pointer">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>Perorangan (Individu)</span>
                    </button>

                    {{-- Opsi 2: Kelompok --}}
                    <button type="button"
                        @click="jenis = 'group'"
                        :class="jenis === 'group' ? 'bg-white text-purple-deep shadow-xs border border-[#D9C2F0]' : 'text-[#6B5B85] hover:text-purple-deep'"
                        class="flex items-center justify-center gap-2.5 py-3 px-3 rounded-lg text-xs font-bold cursor-pointer">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span>Kelompok (Pasangan / Keluarga)</span>
                    </button>

                    {{-- Opsi 3: Perusahaan --}}
                    <button type="button"
                        @click="jenis = 'company'"
                        :class="jenis === 'company' ? 'bg-white text-purple-deep shadow-xs border border-[#D9C2F0]' : 'text-[#6B5B85] hover:text-purple-deep'"
                        class="flex items-center justify-center gap-2.5 py-3 px-3 rounded-lg text-xs font-bold cursor-pointer">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                        <span>Perusahaan / Instansi</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- CARD 2: IDENTITAS & KONTAK PRIBADI / PIC --}}
        <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-5">
            <div class="pb-3 border-b border-[#EDE1FA]">
                <h3 class="text-base font-bold text-purple-deep">Data Identitas & Kontak</h3>
                <p class="text-xs text-[#827299] mt-0.5">Informasi profil dasar klien (layanan, jadwal & konselor diatur saat booking)</p>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                {{-- Nama Lengkap / Nama Kelompok / Nama Perusahaan --}}
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-[#5B4A73] mb-1.5" 
                        x-text="jenis === 'company' ? 'Nama Perusahaan / Instansi *' : (jenis === 'group' ? 'Nama Kelompok / Keluarga *' : 'Nama Lengkap Klien *')"></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        :placeholder="jenis === 'company' ? 'Contoh: PT Ciputra Mitra Tbk' : (jenis === 'group' ? 'Contoh: Keluarga Bpk. Hendra Wijaya / Pasangan Anton & Siti' : 'Contoh: Ahmad Fauzi Pratama')"
                        class="w-full px-4 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('name') border-red-300 @enderror">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- PIC jika Kelompok atau Perusahaan --}}
                <div class="sm:col-span-2" x-show="jenis === 'group' || jenis === 'company'">
                    <label class="block text-xs font-bold text-[#5B4A73] mb-1.5"
                        x-text="jenis === 'group' ? 'Nama PIC / Perwakilan Kelompok *' : 'Nama PIC / Kontak Perwakilan Instansi *'"></label>
                    <input type="text" name="pic_name" value="{{ old('pic_name') }}" :required="jenis === 'group' || jenis === 'company'"
                        :placeholder="jenis === 'group' ? 'Contoh: Bpk. Hendra (Kepala Keluarga) / Ibu Siti' : 'Contoh: Ibu Maria (HR Manager)'"
                        class="w-full px-4 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                </div>

                {{-- Nomor WhatsApp --}}
                <div>
                    <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">No. Telepon / WhatsApp <span class="text-red-500">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required
                        placeholder="Contoh: 081234567890 (10-13 digit)"
                        class="w-full px-4 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('phone') border-red-300 @enderror">
                    <span class="text-[11px] text-[#827299]">Nomor telepon aktif 10 s/d 13 digit angka</span>
                    @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Alamat Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        placeholder="klien@email.com"
                        class="w-full px-4 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('email') border-red-300 @enderror">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Jenis Kelamin (Khusus Individu) --}}
                <div x-show="jenis === 'individual'">
                    <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Jenis Kelamin <span class="text-red-500">*</span></label>
                    <select name="gender" :required="jenis === 'individual'"
                        class="w-full px-4 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        <option value="l" {{ old('gender') === 'l' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="p" {{ old('gender') === 'p' ? 'selected' : '' }}>Perempuan</option>
                        <option value="non_binary" {{ old('gender') === 'non_binary' ? 'selected' : '' }}>Non-binary</option>
                        <option value="transgender" {{ old('gender') === 'transgender' ? 'selected' : '' }}>Transgender</option>
                        <option value="prefer_not_to_say" {{ old('gender') === 'prefer_not_to_say' ? 'selected' : '' }}>Memilih tidak menyebutkan</option>
                        <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>

                {{-- Tanggal Lahir (Khusus Individu) --}}
                <div x-show="jenis === 'individual'">
                    <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Tanggal Lahir <span class="text-red-500">*</span></label>
                    <input type="date" name="dob" value="{{ old('dob') }}" :required="jenis === 'individual'"
                        class="w-full px-4 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                </div>

                {{-- Sumber Informasi / Rujukan --}}
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Sumber Informasi / Rujukan <span class="text-red-500">*</span></label>
                    <select name="source" required
                        class="w-full px-4 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">
                        <option value="whatsapp" {{ old('source', 'whatsapp') === 'whatsapp' ? 'selected' : '' }}>WhatsApp PSC</option>
                        <option value="walk_in" {{ old('source') === 'walk_in' ? 'selected' : '' }}>Datang Langsung (Walk In)</option>
                        <option value="referral" {{ old('source') === 'referral' ? 'selected' : '' }}>Rujukan / Rekomendasi (Dosen / Teman / Instansi)</option>
                        <option value="other" {{ old('source') === 'other' ? 'selected' : '' }}>Media Sosial / Lainnya</option>
                    </select>
                </div>

                {{-- Catatan / Keterangan Klien --}}
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Catatan & Keterangan Tambahan <span class="text-[#827299] font-normal">(Opsional)</span></label>
                    <textarea name="notes" rows="3"
                        placeholder="Contoh: Alamat domisili, latar belakang pengajuan konsultasi, atau kebutuhan khusus lainnya..."
                        class="w-full px-4 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        {{-- CARD 3: DAFTAR ANGGOTA / PESERTA (KHUSUS KELOMPOK & PERUSAHAAN) --}}
        <div x-show="jenis === 'group' || jenis === 'company'" class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-4">
            <div class="pb-3 border-b border-[#EDE1FA] flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-purple-deep"
                        x-text="jenis === 'company' ? 'Daftar Karyawan / Peserta Instansi' : 'Daftar Anggota / Peserta Kelompok'"></h3>
                    <p class="text-xs text-[#827299]">Masukkan nama masing-masing anggota atau peserta yang didaftarkan</p>
                </div>
                <button type="button" @click="addParticipant()" class="text-xs font-bold text-purple-deep hover:underline cursor-pointer flex items-center gap-1">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>Tambah Anggota / Peserta</span>
                </button>
            </div>

            <div class="space-y-3">
                <template x-for="(p, i) in participants" :key="i">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-[#6B5B85] w-6" x-text="(i + 1) + '.'"></span>
                        <input type="text" :name="'participants[' + i + ']'" x-model="participants[i]"
                            :placeholder="jenis === 'company' ? 'Nama lengkap karyawan / kandidat...' : 'Nama lengkap anggota keluarga / pasangan...'"
                            class="flex-1 px-4 py-2 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                        <button type="button" @click="removeParticipant(i)" x-show="participants.length > 1"
                            class="p-2 text-red-400 hover:text-red-600 transition rounded-lg hover:bg-red-50 cursor-pointer">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        {{-- ACTION BUTTONS --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('clients.index') }}" 
                class="px-6 py-2.5 text-center text-sm font-semibold text-[#6B5B85] border border-[#D9C2F0] rounded-xl hover:bg-[#F7F5FB] transition">
                Batal
            </a>

            <button type="submit"
                class="px-6 py-2.5 bg-purple-deep text-white text-sm font-bold rounded-xl hover:bg-purple-deep/90 transition shadow-sm cursor-pointer flex items-center justify-center gap-2">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                <span>Simpan Data Klien</span>
            </button>
        </div>
    </form>
</div>
@endsection
