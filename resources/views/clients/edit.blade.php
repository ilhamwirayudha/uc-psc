@extends('layouts.dashboard')

@section('title', 'Edit Klien — UC PSC')
@section('page-title', 'Edit Klien')
@section('back-url', route('clients.show', $client))

@section('content')
@include('clients.partials.address-script')
<form action="{{ route('clients.update', $client) }}" method="POST" x-data="{
    name: @js(old('name', $client->name ?? '')),
    dob: @js(old('dob', $client->dob ? $client->dob->format('Y-m-d') : '')),
    gender: @js(old('gender', $client->gender ?? '')),
    religion: @js(old('religion', $client->religion ?? '')),
    marital_status: @js(old('marital_status', $client->marital_status ?? '')),
    education: @js(old('education', $client->education ?? '')),
    occupation: @js(old('occupation', $client->occupation ?? '')),
    phone: @js(old('phone', $client->phone ?? '')),
    email: @js(old('email', $client->email ?? '')),
    country: @js(old('country', $client->country ?? '')),
    province: @js(old('province', $client->province ?? '')),
    city: @js(old('city', $client->city ?? '')),
    address: @js(old('address', $client->address ?? '')),

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
        if (!this.isAddressAllowed) return;
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
        if (!this.isAddressAllowed || this.country !== 'Indonesia') return;
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
        if (!this.isAddressAllowed || this.country !== 'Indonesia' || !this.province) return;
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

    get isDobEnabled() {
        return !!this.name && this.name.trim().length > 0;
    },
    get isGenderEnabled() {
        return this.isDobEnabled && !!this.dob;
    },
    get isReligionEnabled() {
        return this.isGenderEnabled && !!this.gender;
    },
    get isMaritalStatusEnabled() {
        return this.isReligionEnabled && !!this.religion;
    },
    get isEducationEnabled() {
        return this.isMaritalStatusEnabled && !!this.marital_status;
    },
    get isOccupationEnabled() {
        return this.isEducationEnabled && !!this.education;
    },
    get isPhoneEnabled() {
        return this.isEducationEnabled && !!this.education;
    },
    get isEmailEnabled() {
        return this.isPhoneEnabled && !!this.phone && this.phone.trim().length >= 10;
    },
    get isContactCompleted() {
        return !!this.name && this.name.trim().length > 0
            && !!this.dob
            && !!this.gender
            && !!this.religion
            && !!this.marital_status
            && !!this.education
            && !!this.phone && this.phone.trim().length >= 10
            && !!this.email && this.email.trim().length > 0;
    },
    get isAddressAllowed() {
        return this.isContactCompleted;
    },
    get isAddressEnabled() {
        if (!this.isAddressAllowed) return false;
        if (!this.country) return false;
        if (this.country === 'Indonesia') {
            return !!this.province && !!this.city;
        }
        return true;
    },
    get isAddressCompleted() {
        if (!this.isAddressEnabled) return false;
        return !!this.address && this.address.trim().length > 0;
    },

    capitalizeInput(e) {
        const el = e.target;
        const start = el.selectionStart;
        const end = el.selectionEnd;
        const prevLen = el.value.length;
        
        // Hapus angka (0-9), hanya teks dan simbol yang diperbolehkan
        el.value = el.value.replace(/[0-9]/g, '');
        const diff = prevLen - el.value.length;
        
        // Capitalize huruf pertama setiap kata
        el.value = el.value.replace(/\b\w/g, c => c.toUpperCase());
        const modelName = el.getAttribute('name');
        if (modelName && this.hasOwnProperty(modelName)) {
            this[modelName] = el.value;
        }
        el.setSelectionRange(Math.max(0, start - diff), Math.max(0, end - diff));
    }
}" class="space-y-6">
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
                    <input type="text" id="name" name="name" x-model="name" value="{{ old('name', $client->name) }}" required
                        @input="capitalizeInput($event)"
                        @keydown="if ($event.key >= '0' && $event.key <= '9') $event.preventDefault()"
                        class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm @error('name') border-red-300 @enderror">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Layout 2 Kolom (Kiri & Kanan) --}}
                <div class="grid sm:grid-cols-2 gap-5">
                    {{-- KOLOM KIRI --}}
                    <div class="space-y-4">
                        {{-- 1. Tanggal Lahir --}}
                        <div>
                            <label for="dob" class="block text-sm font-semibold text-[#5B4A73] mb-1.5">Tanggal Lahir <span class="text-red-400">*</span></label>
                            <input type="date" id="dob" name="dob" x-model="dob"
                                :disabled="!isDobEnabled"
                                :class="isDobEnabled ? 'bg-white border-[#D9C2F0]' : 'bg-[#F7F4FB] border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'"
                                value="{{ old('dob', $client->dob ? $client->dob->format('Y-m-d') : '') }}"
                                class="w-full px-4 py-2.5 rounded-xl border focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm text-[#5B4A73]">
                        </div>

                        {{-- 2. Jenis Kelamin --}}
                        <div>
                            <label for="gender" class="block text-sm font-semibold text-[#5B4A73] mb-1.5">Jenis Kelamin <span class="text-red-400">*</span></label>
                            <select id="gender" name="gender" x-model="gender"
                                :disabled="!isGenderEnabled"
                                :class="isGenderEnabled ? 'bg-white border-[#D9C2F0]' : 'bg-[#F7F4FB] border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'"
                                class="w-full px-4 py-2.5 rounded-xl border focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm text-[#5B4A73]">
                                <option value="" x-text="isGenderEnabled ? 'Pilih Jenis Kelamin' : ''"></option>
                                <option value="l" {{ old('gender', $client->gender) === 'l' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="p" {{ old('gender', $client->gender) === 'p' ? 'selected' : '' }}>Perempuan</option>
                                <option value="non_binary" {{ old('gender', $client->gender) === 'non_binary' ? 'selected' : '' }}>Non-biner (Non-binary)</option>
                                <option value="transgender" {{ old('gender', $client->gender) === 'transgender' ? 'selected' : '' }}>Transgender</option>
                                <option value="prefer_not_to_say" {{ old('gender', $client->gender) === 'prefer_not_to_say' ? 'selected' : '' }}>Memilih Tidak Menyebutkan</option>
                                <option value="other" {{ old('gender', $client->gender) === 'other' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                        </div>

                        {{-- 3. Agama --}}
                        <div>
                            <label for="religion" class="block text-sm font-semibold text-[#5B4A73] mb-1.5">Agama <span class="text-red-400">*</span></label>
                            <select id="religion" name="religion" x-model="religion" required
                                :disabled="!isReligionEnabled"
                                :class="isReligionEnabled ? 'bg-white border-[#D9C2F0]' : 'bg-[#F7F4FB] border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'"
                                class="w-full px-4 py-2.5 rounded-xl border focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm text-[#5B4A73] @error('religion') border-red-300 @enderror">
                                <option value="" x-text="isReligionEnabled ? '-- Pilih Agama --' : ''"></option>
                                <option value="Islam" {{ old('religion', $client->religion) === 'Islam' ? 'selected' : '' }}>Islam</option>
                                <option value="Kristen" {{ old('religion', $client->religion) === 'Kristen' ? 'selected' : '' }}>Kristen Protestan</option>
                                <option value="Katolik" {{ old('religion', $client->religion) === 'Katolik' ? 'selected' : '' }}>Katolik</option>
                                <option value="Hindu" {{ old('religion', $client->religion) === 'Hindu' ? 'selected' : '' }}>Hindu</option>
                                <option value="Buddha" {{ old('religion', $client->religion) === 'Buddha' ? 'selected' : '' }}>Buddha</option>
                                <option value="Khonghucu" {{ old('religion', $client->religion) === 'Khonghucu' ? 'selected' : '' }}>Khonghucu</option>
                                <option value="Penghayat Kepercayaan" {{ old('religion', $client->religion) === 'Penghayat Kepercayaan' ? 'selected' : '' }}>Penghayat Kepercayaan</option>
                                <option value="Lainnya" {{ old('religion', $client->religion) === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                            @error('religion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- 4. Status Perkawinan --}}
                        <div>
                            <label for="marital_status" class="block text-sm font-semibold text-[#5B4A73] mb-1.5">Status Perkawinan <span class="text-red-400">*</span></label>
                            <select id="marital_status" name="marital_status" x-model="marital_status" required
                                :disabled="!isMaritalStatusEnabled"
                                :class="isMaritalStatusEnabled ? 'bg-white border-[#D9C2F0]' : 'bg-[#F7F4FB] border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'"
                                class="w-full px-4 py-2.5 rounded-xl border focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm text-[#5B4A73] @error('marital_status') border-red-300 @enderror">
                                <option value="" x-text="isMaritalStatusEnabled ? 'Pilih Status...' : ''"></option>
                                <option value="belum_menikah" {{ old('marital_status', $client->marital_status) === 'belum_menikah' ? 'selected' : '' }}>Belum Menikah</option>
                                <option value="menikah" {{ old('marital_status', $client->marital_status) === 'menikah' ? 'selected' : '' }}>Menikah</option>
                                <option value="cerai_hidup" {{ old('marital_status', $client->marital_status) === 'cerai_hidup' ? 'selected' : '' }}>Cerai Hidup</option>
                                <option value="cerai_mati" {{ old('marital_status', $client->marital_status) === 'cerai_mati' ? 'selected' : '' }}>Cerai Mati</option>
                            </select>
                            @error('marital_status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- KOLOM KANAN --}}
                    <div class="space-y-4">
                        {{-- 1. Pendidikan Terakhir --}}
                        <div>
                            <label for="education" class="block text-sm font-semibold text-[#5B4A73] mb-1.5">Pendidikan Terakhir <span class="text-red-400">*</span></label>
                            <select id="education" name="education" x-model="education" required
                                :disabled="!isEducationEnabled"
                                :class="isEducationEnabled ? 'bg-white border-[#D9C2F0]' : 'bg-[#F7F4FB] border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'"
                                class="w-full px-4 py-2.5 rounded-xl border focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm text-[#5B4A73] @error('education') border-red-300 @enderror">
                                <option value="" x-text="isEducationEnabled ? '-- Pilih Pendidikan Terakhir --' : ''"></option>
                                <option value="sd" {{ old('education', $client->education) === 'sd' ? 'selected' : '' }}>SD / Sederajat</option>
                                <option value="smp" {{ old('education', $client->education) === 'smp' ? 'selected' : '' }}>SMP / Sederajat</option>
                                <option value="sma_smk" {{ old('education', $client->education) === 'sma_smk' ? 'selected' : '' }}>SMA / SMK / Sederajat</option>
                                <option value="d3" {{ old('education', $client->education) === 'd3' ? 'selected' : '' }}>Diploma / D3</option>
                                <option value="s1" {{ old('education', $client->education) === 's1' ? 'selected' : '' }}>Sarjana / S1 / D4</option>
                                <option value="s2" {{ old('education', $client->education) === 's2' ? 'selected' : '' }}>Magister / S2</option>
                                <option value="s3" {{ old('education', $client->education) === 's3' ? 'selected' : '' }}>Doktor / S3</option>
                                <option value="lainnya" {{ old('education', $client->education) === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                            @error('education') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- 2. Pekerjaan --}}
                        <div>
                            <label for="occupation" class="block text-sm font-semibold text-[#5B4A73] mb-1.5">Pekerjaan</label>
                            <select id="occupation" name="occupation" x-model="occupation"
                                :disabled="!isOccupationEnabled"
                                :class="isOccupationEnabled ? 'bg-white border-[#D9C2F0]' : 'bg-[#F7F4FB] border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'"
                                class="w-full px-4 py-2.5 rounded-xl border focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm text-[#5B4A73]">
                                <option value="" x-text="isOccupationEnabled ? '-- Pilih Pekerjaan --' : ''"></option>
                                <option value="Belum Bekerja" {{ old('occupation', $client->occupation) === 'Belum Bekerja' ? 'selected' : '' }}>Belum Bekerja</option>
                                <option value="Pelajar" {{ old('occupation', $client->occupation) === 'Pelajar' ? 'selected' : '' }}>Pelajar</option>
                                <option value="Mahasiswa" {{ old('occupation', $client->occupation) === 'Mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                                <option value="Ibu Rumah Tangga" {{ old('occupation', $client->occupation) === 'Ibu Rumah Tangga' ? 'selected' : '' }}>Ibu Rumah Tangga</option>
                                <option value="Wiraswasta" {{ old('occupation', $client->occupation) === 'Wiraswasta' ? 'selected' : '' }}>Wiraswasta</option>
                                <option value="Penyedia Jasa (Guru/Dokter/Lawyer/Peneliti/Lainnya)" {{ old('occupation', $client->occupation) === 'Penyedia Jasa (Guru/Dokter/Lawyer/Peneliti/Lainnya)' ? 'selected' : '' }}>Penyedia Jasa (Guru/Dokter/Lawyer/Peneliti/Lainnya)</option>
                                <option value="Freelance" {{ old('occupation', $client->occupation) === 'Freelance' ? 'selected' : '' }}>Freelance</option>
                                <option value="Karyawan Swasta" {{ old('occupation', $client->occupation) === 'Karyawan Swasta' ? 'selected' : '' }}>Karyawan Swasta</option>
                                <option value="Pegawai Negeri Sipil" {{ old('occupation', $client->occupation) === 'Pegawai Negeri Sipil' ? 'selected' : '' }}>Pegawai Negeri Sipil</option>
                                <option value="BUMN" {{ old('occupation', $client->occupation) === 'BUMN' ? 'selected' : '' }}>BUMN</option>
                                <option value="Lainnya" {{ old('occupation', $client->occupation) === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                        </div>

                        {{-- 3. No. Telepon / WA --}}
                        <div>
                            <label for="phone" class="block text-sm font-semibold text-[#5B4A73] mb-1.5">No. Telepon / WA <span class="text-red-400">*</span></label>
                            <input type="text" id="phone" name="phone" x-model="phone"
                                :disabled="!isPhoneEnabled"
                                :class="isPhoneEnabled ? 'bg-white border-[#D9C2F0]' : 'bg-[#F7F4FB] border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'"
                                value="{{ old('phone', $client->phone) }}"
                                :placeholder="isPhoneEnabled ? '08xx-xxxx-xxxx' : ''"
                                maxlength="13"
                                @input="phone = $el.value.replace(/[^0-9]/g, '').slice(0, 13); $el.value = phone"
                                class="w-full px-4 py-2.5 rounded-xl border focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm">
                        </div>

                        {{-- 4. Email --}}
                        <div>
                            <label for="email" class="block text-sm font-semibold text-[#5B4A73] mb-1.5">Email <span class="text-red-400">*</span></label>
                            <input type="email" id="email" name="email" x-model="email"
                                :disabled="!isEmailEnabled"
                                :class="isEmailEnabled ? 'bg-white border-[#D9C2F0]' : 'bg-[#F7F4FB] border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'"
                                value="{{ old('email', $client->email) }}"
                                :placeholder="isEmailEnabled ? 'nama@email.com' : ''"
                                @input="email = $el.value.toLowerCase(); $el.value = email"
                                class="w-full px-4 py-2.5 rounded-xl border focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm @error('email') border-red-300 @enderror">
                            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card: Alamat & Domisili --}}
            <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-5">
                <div class="flex items-center gap-3 pb-3 border-b border-[#EDE1FA]">
                    <div class="w-8 h-8 rounded-lg bg-purple-deep/10 text-purple-deep flex items-center justify-center font-bold text-sm">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-purple-deep" x-text="jenis === 'company' ? 'Alamat Perusahaan' : 'Alamat'">Alamat</h3>
                        <p class="text-xs text-[#6B5B85]" x-text="jenis === 'company' ? 'Informasi lokasi dan alamat kantor perusahaan' : 'Informasi lokasi dan tempat tinggal klien'">Informasi lokasi dan tempat tinggal klien</p>
                    </div>
                </div>

                <div class="grid sm:grid-cols-3 gap-4">
                    {{-- 1. NEGARA (Dropdown dengan Search Box di Atas) --}}
                    <div class="relative" @click.outside="countryOpen = false">
                        <label class="block text-sm font-semibold text-[#5B4A73] mb-1.5">Negara <span class="text-red-500">*</span></label>
                        <input type="hidden" name="country" :value="country" required>
                        
                        {{-- Form Field Button --}}
                        <button type="button" 
                            :disabled="!isAddressAllowed"
                            @click="isAddressAllowed && (countryOpen ? (countryOpen = false) : openCountry())" 
                            class="w-full px-4 py-2.5 rounded-xl text-sm flex items-center justify-between text-left transition"
                            :class="isAddressAllowed
                                ? 'bg-white border hover:border-[#B59BD6] focus:outline-none focus:ring-2 focus:ring-purple-deep cursor-pointer text-[#2A2035] @error('country') border-red-400 @else border-[#D9C2F0] @enderror' 
                                : 'bg-[#F7F4FB] border border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'">
                            <span :class="isAddressAllowed ? (country ? 'text-[#2A2035] font-semibold' : 'text-[#827299]') : 'text-[#A093B3]'" 
                                  x-text="isAddressAllowed ? (country || '-- Pilih Negara --') : ''"></span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="transition-transform duration-200" 
                                 :class="isAddressAllowed ? 'text-[#827299]' : 'text-[#C4B7D6]'" 
                                 :class="{ 'rotate-180': countryOpen }">
                                <polyline points="6 9 12 15 18 9"/>
                            </svg>
                        </button>
                        @error('country')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror

                        {{-- Dropdown Popup Panel --}}
                        <div x-cloak x-show="isAddressAllowed && countryOpen"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-1"
                            class="absolute z-50 mt-1.5 w-full bg-white rounded-xl shadow-xl border border-[#EDE1FA] overflow-hidden">
                            
                            {{-- Sticky Search Input Paling Atas --}}
                            <div class="p-2.5 border-b border-[#EDE1FA] bg-[#FAF8FD]">
                                <div class="relative">
                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-[#827299]" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                    </svg>
                                    <input type="text"
                                        x-ref="countrySearchInput"
                                        x-model="countrySearch"
                                        placeholder="Cari negara..."
                                        class="w-full pl-8 pr-7 py-1.5 bg-white border border-[#D9C2F0] rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-purple-deep text-[#2A2035]">
                                    <button x-show="countrySearch" @click="countrySearch = ''" type="button" class="absolute right-2 top-1/2 -translate-y-1/2 text-[#827299] hover:text-red-500">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    </button>
                                </div>
                            </div>

                            {{-- List Opsi Negara (Scrollable) --}}
                            <div class="max-h-52 overflow-y-auto divide-y divide-[#FAF8FD] py-1 custom-scrollbar">
                                <template x-for="c in countryList" :key="c">
                                    <div @click="selectCountry(c)"
                                        class="px-3.5 py-2 text-xs cursor-pointer flex items-center justify-between transition hover:bg-[#FAF8FD]"
                                        :class="{ 'bg-[#F3EBFC] text-purple-deep font-bold': country === c, 'text-[#2A2035]': country !== c }">
                                        <span x-text="c"></span>
                                        <svg x-show="country === c" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-purple-deep flex-shrink-0"><polyline points="20 6 9 17 4 12"/></svg>
                                    </div>
                                </template>
                                <div x-show="countryList.length === 0" class="px-3.5 py-4 text-center text-xs text-[#827299]">
                                    <p>Tidak ditemukan "<span x-text="countrySearch"></span>"</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. PROVINSI (Dropdown dengan Search Box di Atas) --}}
                    <div class="relative" @click.outside="provinceOpen = false">
                        <label class="block text-sm font-semibold text-[#5B4A73] mb-1.5">
                            Provinsi 
                            <span x-show="country === 'Indonesia'" class="text-red-500">*</span>
                            <span x-show="country !== 'Indonesia'" class="text-xs font-normal text-[#9D8EB0]">(Khusus Indonesia)</span>
                        </label>
                        <input type="hidden" name="province" :value="country === 'Indonesia' ? province : ''">
                        
                        {{-- Form Field Button --}}
                        <button type="button" 
                            :disabled="!isAddressAllowed || country !== 'Indonesia'"
                            @click="(isAddressAllowed && country === 'Indonesia') && (provinceOpen ? (provinceOpen = false) : openProvince())" 
                            class="w-full px-4 py-2.5 rounded-xl text-sm flex items-center justify-between text-left transition"
                            :class="(isAddressAllowed && country === 'Indonesia') 
                                ? 'bg-white border hover:border-[#B59BD6] focus:outline-none focus:ring-2 focus:ring-purple-deep cursor-pointer text-[#2A2035] @error('province') border-red-400 @else border-[#D9C2F0] @enderror' 
                                : 'bg-[#F7F4FB] border border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'">
                            <span :class="(isAddressAllowed && country === 'Indonesia') ? (province ? 'text-[#2A2035] font-semibold' : 'text-[#827299]') : 'text-[#A093B3]'" 
                                  x-text="isAddressAllowed ? (country === 'Indonesia' ? (province || '-- Pilih Provinsi --') : '-') : ''"></span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="transition-transform duration-200" 
                                 :class="(isAddressAllowed && country === 'Indonesia') ? 'text-[#827299]' : 'text-[#C4B7D6]'" 
                                 :class="{ 'rotate-180': provinceOpen }">
                                <polyline points="6 9 12 15 18 9"/>
                            </svg>
                        </button>
                        @error('province')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror

                        {{-- Dropdown Popup Panel --}}
                        <div x-cloak x-show="isAddressAllowed && country === 'Indonesia' && provinceOpen"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-1"
                            class="absolute z-50 mt-1.5 w-full bg-white rounded-xl shadow-xl border border-[#EDE1FA] overflow-hidden">
                            
                            {{-- Sticky Search Input Paling Atas --}}
                            <div class="p-2.5 border-b border-[#EDE1FA] bg-[#FAF8FD]">
                                <div class="relative">
                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-[#827299]" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                    </svg>
                                    <input type="text"
                                        x-ref="provinceSearchInput"
                                        x-model="provinceSearch"
                                        placeholder="Cari provinsi..."
                                        class="w-full pl-8 pr-7 py-1.5 bg-white border border-[#D9C2F0] rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-purple-deep text-[#2A2035]">
                                    <button x-show="provinceSearch" @click="provinceSearch = ''" type="button" class="absolute right-2 top-1/2 -translate-y-1/2 text-[#827299] hover:text-red-500">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    </button>
                                </div>
                            </div>

                            {{-- List Opsi Provinsi (Scrollable) --}}
                            <div class="max-h-52 overflow-y-auto divide-y divide-[#FAF8FD] py-1 custom-scrollbar">
                                <template x-for="p in provinceList" :key="p">
                                    <div @click="selectProvince(p)"
                                        class="px-3.5 py-2 text-xs cursor-pointer flex items-center justify-between transition hover:bg-[#FAF8FD]"
                                        :class="{ 'bg-[#F3EBFC] text-purple-deep font-bold': province === p, 'text-[#2A2035]': province !== p }">
                                        <span x-text="p"></span>
                                        <svg x-show="province === p" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-purple-deep flex-shrink-0"><polyline points="20 6 9 17 4 12"/></svg>
                                    </div>
                                </template>
                                <div x-show="provinceList.length === 0" class="px-3.5 py-4 text-center text-xs text-[#827299]">
                                    <p>Tidak ditemukan "<span x-text="provinceSearch"></span>"</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. KOTA / KABUPATEN (Dropdown dengan Search Box di Atas) --}}
                    <div class="relative" @click.outside="cityOpen = false">
                        <label class="block text-sm font-semibold text-[#5B4A73] mb-1.5">
                            Kota / Kabupaten
                            <span x-show="country === 'Indonesia'" class="text-red-500">*</span>
                            <span x-show="country !== 'Indonesia'" class="text-xs font-normal text-[#9D8EB0]">(Khusus Indonesia)</span>
                        </label>
                        <input type="hidden" name="city" :value="country === 'Indonesia' ? city : ''">
                        
                        {{-- Form Field Button --}}
                        <button type="button" 
                            :disabled="!isAddressAllowed || country !== 'Indonesia' || !province"
                            @click="(isAddressAllowed && country === 'Indonesia' && province) && (cityOpen ? (cityOpen = false) : openCity())" 
                            class="w-full px-4 py-2.5 rounded-xl text-sm flex items-center justify-between text-left transition"
                            :class="(isAddressAllowed && country === 'Indonesia' && province)
                                ? 'bg-white border hover:border-[#B59BD6] focus:outline-none focus:ring-2 focus:ring-purple-deep cursor-pointer text-[#2A2035] @error('city') border-red-400 @else border-[#D9C2F0] @enderror' 
                                : 'bg-[#F7F4FB] border border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'">
                            <span :class="(isAddressAllowed && country === 'Indonesia' && province) ? (city ? 'text-[#2A2035] font-semibold' : 'text-[#827299]') : 'text-[#A093B3]'" 
                                  x-text="isAddressAllowed ? (country === 'Indonesia' ? (province ? (city || '-- Pilih Kota / Kabupaten --') : '') : '-') : ''"></span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="transition-transform duration-200" 
                                 :class="(isAddressAllowed && country === 'Indonesia' && province) ? 'text-[#827299]' : 'text-[#C4B7D6]'" 
                                 :class="{ 'rotate-180': cityOpen }">
                                <polyline points="6 9 12 15 18 9"/>
                            </svg>
                        </button>
                        @error('city')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror

                        {{-- Dropdown Popup Panel --}}
                        <div x-cloak x-show="isAddressAllowed && country === 'Indonesia' && province && cityOpen"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-1"
                            class="absolute z-50 mt-1.5 w-full bg-white rounded-xl shadow-xl border border-[#EDE1FA] overflow-hidden">
                            
                            {{-- Sticky Search Input Paling Atas --}}
                            <div class="p-2.5 border-b border-[#EDE1FA] bg-[#FAF8FD]">
                                <div class="relative">
                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-[#827299]" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                    </svg>
                                    <input type="text"
                                        x-ref="citySearchInput"
                                        x-model="citySearch"
                                        :placeholder="province ? 'Cari kota di ' + province + '...' : 'Cari kota / kabupaten...'"
                                        class="w-full pl-8 pr-7 py-1.5 bg-white border border-[#D9C2F0] rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-purple-deep text-[#2A2035]">
                                    <button x-show="citySearch" @click="citySearch = ''" type="button" class="absolute right-2 top-1/2 -translate-y-1/2 text-[#827299] hover:text-red-500">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    </button>
                                </div>
                            </div>

                            {{-- List Opsi Kota (Scrollable) --}}
                            <div class="max-h-52 overflow-y-auto divide-y divide-[#FAF8FD] py-1 custom-scrollbar">
                                <template x-for="c in cityList" :key="c">
                                    <div @click="selectCity(c)"
                                        class="px-3.5 py-2 text-xs cursor-pointer flex items-center justify-between transition hover:bg-[#FAF8FD]"
                                        :class="{ 'bg-[#F3EBFC] text-purple-deep font-bold': city === c, 'text-[#2A2035]': city !== c }">
                                        <span x-text="c"></span>
                                        <svg x-show="city === c" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-purple-deep flex-shrink-0"><polyline points="20 6 9 17 4 12"/></svg>
                                    </div>
                                </template>
                                <div x-show="cityList.length === 0" class="px-3.5 py-4 text-center text-xs text-[#827299]">
                                    <p>Tidak ditemukan "<span x-text="citySearch"></span>"</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="address" class="block text-sm font-semibold text-[#5B4A73] mb-1.5">Alamat Lengkap <span class="text-red-500">*</span> <span class="text-xs text-[#827299] font-normal">(Jalan, Nomor, RT/RW, Kelurahan, Kecamatan, Kode Pos)</span></label>
                        <textarea id="address" name="address" rows="2" required
                            x-model="address"
                            :disabled="!isAddressEnabled"
                            :class="isAddressEnabled 
                                ? 'bg-white border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep @error('address') border-red-400 @enderror' 
                                : 'bg-[#F7F4FB] border border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'"
                            :placeholder="isAddressEnabled ? 'Contoh: Jl. Soekarno Hatta No. 112, RT 002/RW 005, Kel. Jatimulyo, Kec. Lowokwaru' : ''"
                            class="w-full px-4 py-2.5 rounded-xl border transition text-sm text-[#5B4A73]">{{ old('address', $client->address) }}</textarea>
                        @error('address')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror

                        {{-- Live Preview Format Penulisan Alamat Standar --}}
                        <div x-show="isAddressEnabled && (address || city || province)" class="mt-2 p-3 rounded-xl bg-[#FAF8FD] border border-[#EDE1FA] text-xs space-y-1">
                            <div class="flex items-center gap-1.5 font-bold text-purple-deep text-[11px]">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                <span>Format Alamat Lengkap Otomatis:</span>
                            </div>
                            <p class="text-black font-semibold leading-relaxed" 
                               x-text="[address.trim(), city.trim(), province.trim(), (country && country !== 'Indonesia' ? country.trim() : (country === 'Indonesia' ? 'Indonesia' : ''))].filter(Boolean).join(', ') || '-'"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Jadwal, Konselor & Catatan (5 cols on lg) --}}
        <div class="lg:col-span-5 space-y-6">
            {{-- Card 2: Jadwal & Konselor --}}
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
                        2
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
