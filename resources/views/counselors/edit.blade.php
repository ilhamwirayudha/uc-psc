@extends('layouts.dashboard')

@section('title', 'Edit Konselor — UC PSC')
@section('page-title', 'Edit Data Konselor')
@section('page-subtitle', 'Perbarui data identitas, kualifikasi, atau status konselor')
@section('back-url', route('counselors.index'))

@section('content')
@include('clients.partials.address-script')

<div class="max-w-4xl" x-data="{
    photoPreview: '{{ $counselor->photo_url }}',
    country: @js(old('country', $counselor->country ?: 'Indonesia')),
    province: @js(old('province', $counselor->province ?? '')),
    city: @js(old('city', $counselor->city ?? '')),
    address: @js(old('address', $counselor->address ?? '')),

    countryOpen: false,
    countrySearch: '',
    provinceOpen: false,
    provinceSearch: '',
    cityOpen: false,
    citySearch: '',

    get countryList() {
        const list = [...(window.addressData?.countries || [])].sort((a, b) => a.localeCompare('id', { sensitivity: 'base' }));
        if (!this.countrySearch) return list;
        return list.filter(c => c.toLowerCase().includes(this.countrySearch.toLowerCase()));
    },
    get provinceList() {
        const list = [...(window.addressData?.provinces || [])].sort((a, b) => a.localeCompare('id', { sensitivity: 'base' }));
        if (!this.provinceSearch) return list;
        return list.filter(p => p.toLowerCase().includes(this.provinceSearch.toLowerCase()));
    },
    get cityList() {
        if (!this.province || !window.addressData?.citiesByProvince || !window.addressData.citiesByProvince[this.province]) {
            return [];
        }
        const list = [...window.addressData.citiesByProvince[this.province]].sort((a, b) => a.localeCompare('id', { sensitivity: 'base' }));
        if (!this.citySearch) return list;
        return list.filter(c => c.toLowerCase().includes(this.citySearch.toLowerCase()));
    },

    openCountry() {
        this.countryOpen = true;
        this.countrySearch = '';
        this.provinceOpen = false;
        this.cityOpen = false;
        this.$nextTick(() => {
            this.$refs.countrySearchInput && this.$refs.countrySearchInput.focus();
        });
    },
    selectCountry(val) {
        this.country = val;
        this.countryOpen = false;
        this.countrySearch = '';
        if (val !== 'Indonesia') {
            this.province = '';
            this.provinceSearch = '';
            this.provinceOpen = false;
            this.city = '';
            this.citySearch = '';
            this.cityOpen = false;
        }
    },
    openProvince() {
        if (this.country !== 'Indonesia') return;
        this.provinceOpen = true;
        this.provinceSearch = '';
        this.countryOpen = false;
        this.cityOpen = false;
        this.$nextTick(() => {
            this.$refs.provinceSearchInput && this.$refs.provinceSearchInput.focus();
        });
    },
    selectProvince(val) {
        this.province = val;
        this.provinceOpen = false;
        this.provinceSearch = '';
        this.city = '';
        this.citySearch = '';
    },
    openCity() {
        if (this.country !== 'Indonesia' || !this.province) return;
        this.cityOpen = true;
        this.citySearch = '';
        this.countryOpen = false;
        this.provinceOpen = false;
        this.$nextTick(() => {
            this.$refs.citySearchInput && this.$refs.citySearchInput.focus();
        });
    },
    selectCity(val) {
        this.city = val;
        this.cityOpen = false;
        this.citySearch = '';
    },

    handlePhotoSelect(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran file maksimal 2 MB.');
                e.target.value = '';
                return;
            }
            this.photoPreview = URL.createObjectURL(file);
        }
    }
}">
    <form action="{{ route('counselors.update', $counselor) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- FOTO PROFIL KONSELOR (FRAME LINGKARAN) --}}
        <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6">
            <div class="flex flex-col sm:flex-row items-center gap-6">
                {{-- Circular Avatar Frame Preview --}}
                <div class="relative group flex-shrink-0">
                    <template x-if="photoPreview">
                        <img :src="photoPreview" alt="Foto Profil" class="w-24 h-24 rounded-full object-cover shadow-sm border-2 border-white ring-4 ring-[#EDE1FA]">
                    </template>
                    <template x-if="!photoPreview">
                        <div class="w-24 h-24 rounded-full grad-purple text-white flex items-center justify-center font-bold text-3xl shadow-sm border-2 border-white ring-4 ring-[#EDE1FA]">
                            {{ strtoupper(substr($counselor->name, 0, 1)) }}
                        </div>
                    </template>
                </div>

                <div class="flex-1 text-center sm:text-left space-y-2">
                    <h3 class="text-base font-bold text-purple-deep">Foto Profil Konselor</h3>
                    <p class="text-xs text-[#827299]">Format foto JPG, PNG, atau WebP. Resolusi rasio 1:1 direkomendasikan. Maksimal 2 MB.</p>
                    
                    <div class="pt-1">
                        <input type="file" id="edit_photo_input" name="photo" accept="image/*" class="hidden" @change="handlePhotoSelect($event)">
                        <label for="edit_photo_input" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-[#D9C2F0] bg-white text-xs font-semibold text-purple-deep hover:bg-[#FAF8FD] transition cursor-pointer shadow-2xs">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                            <span x-text="photoPreview ? 'Ganti Foto Profil' : 'Unggah Foto Profil'"></span>
                        </label>
                    </div>
                    @error('photo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- CARD 1: DATA IDENTITAS & KONTAK --}}
        <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-5">
            <div class="pb-3 border-b border-[#EDE1FA]">
                <h3 class="text-base font-bold text-purple-deep">Data Identitas & Kontak</h3>
                <p class="text-xs text-[#6B5B85]">Informasi nama lengkap dan kontak aktif konselor</p>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                {{-- Nama Konselor --}}
                <div class="sm:col-span-2">
                    <label for="name" class="block text-xs font-bold text-[#5B4A73] mb-1.5">Nama Konselor / Psikolog <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $counselor->name) }}" required
                        placeholder="Contoh: Dr. Amanda Wijaya, M.Psi., Psikolog"
                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035] @error('name') border-red-300 @enderror">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- No Telepon / WA --}}
                <div>
                    <label for="phone" class="block text-xs font-bold text-[#5B4A73] mb-1.5">No. Telepon / WhatsApp</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $counselor->phone) }}"
                        inputmode="numeric"
                        maxlength="13"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 13)"
                        onkeydown="if (!/[0-9]/.test(event.key) && !['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab', 'Home', 'End'].includes(event.key) && !event.ctrlKey && !event.metaKey) event.preventDefault()"
                        placeholder="Contoh: 081234567890 (maks. 13 digit)"
                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035] @error('phone') border-red-300 @enderror">
                    @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-xs font-bold text-[#5B4A73] mb-1.5">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $counselor->email) }}"
                        placeholder="Contoh: amanda.wijaya@uc.ac.id"
                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035]">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- CARD 2: ALAMAT DOMISILI --}}
        <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-5">
            <div class="pb-3 border-b border-[#EDE1FA]">
                <h3 class="text-base font-bold text-purple-deep">Alamat Domisili</h3>
                <p class="text-xs text-[#6B5B85]">Informasi domisili dan alamat lengkap tempat tinggal</p>
            </div>

            <div class="grid sm:grid-cols-3 gap-4 pt-1">
                {{-- 1. NEGARA --}}
                <div class="relative" @click.outside="countryOpen = false">
                    <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Negara</label>
                    <input type="hidden" name="country" :value="country">
                    <button type="button" @click="countryOpen ? (countryOpen = false) : openCountry()"
                        class="w-full px-4 py-2.5 rounded-xl text-sm flex items-center justify-between text-left transition bg-white border border-[#D9C2F0] hover:border-[#B59BD6] cursor-pointer text-[#2A2035] focus:outline-none focus:ring-2 focus:ring-purple-deep">
                        <span x-text="country || 'Indonesia'"></span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[#827299] transition-transform duration-200" :class="{ 'rotate-180': countryOpen }"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div x-cloak x-show="countryOpen" class="absolute z-50 mt-1.5 w-full bg-white rounded-xl shadow-xl border border-[#EDE1FA] overflow-hidden">
                        <div class="p-2.5 border-b border-[#EDE1FA] bg-[#FAF8FD]">
                            <input type="text" x-ref="countrySearchInput" x-model="countrySearch" placeholder="Cari negara..." class="w-full px-3 py-1.5 bg-white border border-[#D9C2F0] rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-purple-deep text-[#2A2035]">
                        </div>
                        <div class="max-h-48 overflow-y-auto divide-y divide-[#FAF8FD] py-1 custom-scrollbar">
                            <template x-for="c in countryList" :key="c">
                                <div @click="selectCountry(c)" class="px-4 py-2 text-xs sm:text-sm cursor-pointer flex items-center justify-between transition hover:bg-[#FAF8FD]" :class="{ 'bg-[#F3EBFC] text-purple-deep font-bold': country === c, 'text-[#2A2035]': country !== c }">
                                    <span x-text="c"></span>
                                    <svg x-show="country === c" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-purple-deep"><polyline points="20 6 9 17 4 12"/></svg>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- 2. PROVINSI --}}
                <div class="relative" @click.outside="provinceOpen = false">
                    <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">
                        Provinsi
                        <span x-show="country !== 'Indonesia'" class="text-[11px] font-normal text-[#9D8EB0]">(Khusus Indonesia)</span>
                    </label>
                    <input type="hidden" name="province" :value="country === 'Indonesia' ? province : ''">
                    <button type="button" :disabled="country !== 'Indonesia'" @click="country === 'Indonesia' && (provinceOpen ? (provinceOpen = false) : openProvince())"
                        class="w-full px-4 py-2.5 rounded-xl text-sm flex items-center justify-between text-left transition"
                        :class="country === 'Indonesia' ? 'bg-white border border-[#D9C2F0] hover:border-[#B59BD6] cursor-pointer text-[#2A2035] focus:outline-none focus:ring-2 focus:ring-purple-deep' : 'bg-[#F7F4FB] border border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'">
                        <span x-text="country === 'Indonesia' ? (province || '-- Pilih Provinsi --') : '-'"></span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[#827299] transition-transform duration-200" :class="{ 'rotate-180': provinceOpen }"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div x-cloak x-show="country === 'Indonesia' && provinceOpen" class="absolute z-50 mt-1.5 w-full bg-white rounded-xl shadow-xl border border-[#EDE1FA] overflow-hidden">
                        <div class="p-2.5 border-b border-[#EDE1FA] bg-[#FAF8FD]">
                            <input type="text" x-ref="provinceSearchInput" x-model="provinceSearch" placeholder="Cari provinsi..." class="w-full px-3 py-1.5 bg-white border border-[#D9C2F0] rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-purple-deep text-[#2A2035]">
                        </div>
                        <div class="max-h-48 overflow-y-auto divide-y divide-[#FAF8FD] py-1 custom-scrollbar">
                            <template x-for="p in provinceList" :key="p">
                                <div @click="selectProvince(p)" class="px-4 py-2 text-xs sm:text-sm cursor-pointer flex items-center justify-between transition hover:bg-[#FAF8FD]" :class="{ 'bg-[#F3EBFC] text-purple-deep font-bold': province === p, 'text-[#2A2035]': province !== p }">
                                    <span x-text="p"></span>
                                    <svg x-show="province === p" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-purple-deep"><polyline points="20 6 9 17 4 12"/></svg>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- 3. KOTA / KABUPATEN --}}
                <div class="relative" @click.outside="cityOpen = false">
                    <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">
                        Kota / Kabupaten
                        <span x-show="country !== 'Indonesia'" class="text-[11px] font-normal text-[#9D8EB0]">(Khusus Indonesia)</span>
                    </label>
                    <input type="hidden" name="city" :value="country === 'Indonesia' ? city : ''">
                    <button type="button" :disabled="country !== 'Indonesia' || !province" @click="country === 'Indonesia' && province && (cityOpen ? (cityOpen = false) : openCity())"
                        class="w-full px-4 py-2.5 rounded-xl text-sm flex items-center justify-between text-left transition"
                        :class="(country === 'Indonesia' && province) ? 'bg-white border border-[#D9C2F0] hover:border-[#B59BD6] cursor-pointer text-[#2A2035] focus:outline-none focus:ring-2 focus:ring-purple-deep' : 'bg-[#F7F4FB] border border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'">
                        <span x-text="country === 'Indonesia' ? (province ? (city || '-- Pilih Kota / Kabupaten --') : '-') : '-'"></span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[#827299] transition-transform duration-200" :class="{ 'rotate-180': cityOpen }"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div x-cloak x-show="country === 'Indonesia' && province && cityOpen" class="absolute z-50 mt-1.5 w-full bg-white rounded-xl shadow-xl border border-[#EDE1FA] overflow-hidden">
                        <div class="p-2.5 border-b border-[#EDE1FA] bg-[#FAF8FD]">
                            <input type="text" x-ref="citySearchInput" x-model="citySearch" :placeholder="province ? 'Cari kota di ' + province + '...' : 'Cari kota / kabupaten...'" class="w-full px-3 py-1.5 bg-white border border-[#D9C2F0] rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-purple-deep text-[#2A2035]">
                        </div>
                        <div class="max-h-48 overflow-y-auto divide-y divide-[#FAF8FD] py-1 custom-scrollbar">
                            <template x-for="c in cityList" :key="c">
                                <div @click="selectCity(c)" class="px-4 py-2 text-xs sm:text-sm cursor-pointer flex items-center justify-between transition hover:bg-[#FAF8FD]" :class="{ 'bg-[#F3EBFC] text-purple-deep font-bold': city === c, 'text-[#2A2035]': city !== c }">
                                    <span x-text="c"></span>
                                    <svg x-show="city === c" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-purple-deep"><polyline points="20 6 9 17 4 12"/></svg>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- 4. ALAMAT LENGKAP --}}
                <div class="sm:col-span-3">
                    <label for="address" class="block text-xs font-bold text-[#5B4A73] mb-1.5">Alamat Lengkap <span class="text-[#827299] font-normal">(Jalan, Nomor, RT/RW, Kelurahan, Kecamatan, Kode Pos)</span></label>
                    <textarea id="address" name="address" rows="2" x-model="address"
                        placeholder="Contoh: Jl. CitraLand CBD Boulevard No. 8, Sambikerep, Surabaya, 60219"
                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035] transition"></textarea>
                    @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- CARD 3: KUALIFIKASI & LEGALITAS PROFESI --}}
        <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-5">
            <div class="pb-3 border-b border-[#EDE1FA]">
                <h3 class="text-base font-bold text-purple-deep">Kualifikasi, Izin Praktik & Status</h3>
                <p class="text-xs text-[#6B5B85]">Bidang keahlian, nomor izin legalitas psikolog, dan status keaktifan</p>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                {{-- Spesialisasi --}}
                <div class="sm:col-span-2">
                    <label for="specialization" class="block text-xs font-bold text-[#5B4A73] mb-1.5">Bidang Spesialisasi / Keahlian</label>
                    <select id="specialization" name="specialization"
                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035]">
                        <option value="">-- Pilih Bidang Spesialisasi / Keahlian --</option>
                        @foreach(\App\Models\Counselor::SPECIALIZATIONS as $spec)
                            <option value="{{ $spec }}" {{ old('specialization', $counselor->specialization) === $spec ? 'selected' : '' }}>{{ $spec }}</option>
                        @endforeach
                    </select>
                    @error('specialization') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Nomor SIPP --}}
                <div>
                    <label for="sipp_number" class="block text-xs font-bold text-[#5B4A73] mb-1.5">Nomor SIPP (Surat Izin Praktik Psikologi)</label>
                    <input type="text" id="sipp_number" name="sipp_number" value="{{ old('sipp_number', $counselor->sipp_number) }}"
                        placeholder="Contoh: SIPP-12345/2024"
                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035]">
                    @error('sipp_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Nomor STR --}}
                <div>
                    <label for="str_number" class="block text-xs font-bold text-[#5B4A73] mb-1.5">Nomor STR (Surat Tanda Registrasi)</label>
                    <input type="text" id="str_number" name="str_number" value="{{ old('str_number', $counselor->str_number) }}"
                        placeholder="Contoh: STR-98765/2024"
                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035]">
                    @error('str_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Status Keaktifan Konselor --}}
                <div class="sm:col-span-2">
                    <label for="status" class="block text-xs font-bold text-[#5B4A73] mb-1.5">Status Keaktifan Konselor <span class="text-red-500">*</span></label>
                    <select id="status" name="status" required
                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035]">
                        <option value="active" class="text-emerald-600 font-semibold" {{ old('status', $counselor->status) === 'active' ? 'selected' : '' }}>Aktif (Siap Menerima Pasangan Klien / Sesi)</option>
                        <option value="inactive" class="text-red-500 font-semibold" {{ old('status', $counselor->status) === 'inactive' ? 'selected' : '' }}>Nonaktif (Sedang Tidak Bertugas)</option>
                    </select>
                    @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- CARD 4: CATATAN TAMBAHAN --}}
        <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-4">
            <div class="pb-3 border-b border-[#EDE1FA]">
                <h3 class="text-base font-bold text-purple-deep">Catatan & Keterangan Tambahan</h3>
                <p class="text-xs text-[#6B5B85]">Informasi jadwal ketersediaan atau catatan khusus konselor (opsional)</p>
            </div>

            <div>
                <textarea id="notes" name="notes" rows="3"
                    placeholder="Contoh: Bersedia praktek hari Selasa & Kamis pukul 09:00 - 15:00 WIB..."
                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035] leading-relaxed">{{ old('notes', $counselor->notes) }}</textarea>
                @error('notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- ACTION BUTTONS --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('counselors.index') }}" 
                class="px-5 py-2.5 rounded-xl text-sm font-semibold text-[#6B5B85] border border-[#D9C2F0] hover:bg-white transition cursor-pointer">
                Batal
            </a>
            <button type="submit" 
                class="px-6 py-2.5 rounded-xl text-sm font-bold bg-purple-deep text-white hover:opacity-90 transition shadow-sm flex items-center gap-2 cursor-pointer">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                <span>Simpan Perubahan</span>
            </button>
        </div>
    </form>
</div>
@endsection
