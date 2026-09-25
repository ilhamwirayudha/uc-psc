@extends('layouts.dashboard')

@section('title', 'Tambah Konselor — UC PSC')
@section('page-title', 'Tambah Konselor Baru')
@section('page-subtitle', 'Daftarkan data psikolog atau konselor pendamping baru')
@section('back-url', route('counselors.index'))

@section('content')
@include('clients.partials.address-script')

<div class="max-w-4xl" x-data="{
    name: @js(old('name', '')),
    phone: @js(old('phone', '')),
    email: @js(old('email', '')),
    country: @js(old('country', 'Indonesia')),
    province: @js(old('province', '')),
    city: @js(old('city', '')),
    address: @js(old('address', '')),
    specialization: @js(old('specialization', '')),
    sipp_number: @js(old('sipp_number', '')),
    str_number: @js(old('str_number', '')),
    status: @js(old('status', 'active')),
    notes: @js(old('notes', '')),

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
    }
}">
    <form action="{{ route('counselors.store') }}" method="POST" class="space-y-6">
        @csrf

        {{-- CARD 1: DATA IDENTITAS & KONTAK --}}
        <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-5">
            <div class="flex items-center gap-3 pb-3 border-b border-[#EDE1FA]">
                <div class="w-8 h-8 rounded-lg bg-orange/10 text-orange flex items-center justify-center font-bold text-sm">
                    1
                </div>
                <div>
                    <h3 class="text-base font-bold text-purple-deep">Data Identitas & Kontak</h3>
                    <p class="text-xs text-[#6B5B85]">Informasi nama lengkap dan kontak aktif konselor</p>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                {{-- Nama Konselor --}}
                <div class="sm:col-span-2">
                    <label for="name" class="block text-xs font-bold text-[#5B4A73] mb-1.5">Nama Konselor / Psikolog <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" x-model="name" required
                        placeholder="Contoh: Dr. Amanda Wijaya, M.Psi., Psikolog"
                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035] @error('name') border-red-300 @enderror">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- No Telepon / WA --}}
                <div>
                    <label for="phone" class="block text-xs font-bold text-[#5B4A73] mb-1.5">No. Telepon / WhatsApp</label>
                    <input type="text" id="phone" name="phone" x-model="phone"
                        inputmode="numeric"
                        maxlength="13"
                        @input="phone = $el.value.replace(/[^0-9]/g, '').slice(0, 13); $el.value = phone"
                        placeholder="Contoh: 081234567890 (maks. 13 digit)"
                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035] @error('phone') border-red-300 @enderror">
                    @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-xs font-bold text-[#5B4A73] mb-1.5">Email</label>
                    <input type="email" id="email" name="email" x-model="email"
                        placeholder="Contoh: amanda.wijaya@uc.ac.id"
                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035]">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- CARD 2: ALAMAT DOMISILI --}}
        <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-5">
            <div class="flex items-center gap-3 pb-3 border-b border-[#EDE1FA]">
                <div class="w-8 h-8 rounded-lg bg-orange/10 text-orange flex items-center justify-center font-bold text-sm">
                    2
                </div>
                <div>
                    <h3 class="text-base font-bold text-purple-deep">Alamat Domisili</h3>
                    <p class="text-xs text-[#6B5B85]">Informasi domisili dan alamat lengkap tempat tinggal</p>
                </div>
            </div>

            <div class="grid sm:grid-cols-3 gap-4 pt-1">
                {{-- 1. NEGARA --}}
                <div class="relative" @click.outside="countryOpen = false">
                    <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Negara <span class="text-red-500">*</span></label>
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
                        <span x-show="country === 'Indonesia'" class="text-red-500">*</span>
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
                        <span x-show="country === 'Indonesia'" class="text-red-500">*</span>
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
            <div class="flex items-center gap-3 pb-3 border-b border-[#EDE1FA]">
                <div class="w-8 h-8 rounded-lg bg-orange/10 text-orange flex items-center justify-center font-bold text-sm">
                    3
                </div>
                <div>
                    <h3 class="text-base font-bold text-purple-deep">Kualifikasi, Izin Praktik & Status</h3>
                    <p class="text-xs text-[#6B5B85]">Bidang keahlian, nomor izin legalitas psikolog, dan status keaktifan</p>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                {{-- Spesialisasi --}}
                <div class="sm:col-span-2">
                    <label for="specialization" class="block text-xs font-bold text-[#5B4A73] mb-1.5">Bidang Spesialisasi / Keahlian</label>
                    <select id="specialization" name="specialization" x-model="specialization"
                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035]">
                        <option value="">-- Pilih Bidang Spesialisasi / Keahlian --</option>
                        @foreach(\App\Models\Counselor::SPECIALIZATIONS as $spec)
                            <option value="{{ $spec }}">{{ $spec }}</option>
                        @endforeach
                    </select>
                    @error('specialization') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Nomor SIPP --}}
                <div>
                    <label for="sipp_number" class="block text-xs font-bold text-[#5B4A73] mb-1.5">Nomor SIPP (Surat Izin Praktik Psikologi)</label>
                    <input type="text" id="sipp_number" name="sipp_number" x-model="sipp_number"
                        placeholder="Contoh: SIPP-12345/2024"
                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035]">
                    @error('sipp_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Nomor STR --}}
                <div>
                    <label for="str_number" class="block text-xs font-bold text-[#5B4A73] mb-1.5">Nomor STR (Surat Tanda Registrasi)</label>
                    <input type="text" id="str_number" name="str_number" x-model="str_number"
                        placeholder="Contoh: STR-98765/2024"
                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035]">
                    @error('str_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Status Keaktifan Konselor --}}
                <div class="sm:col-span-2">
                    <label for="status" class="block text-xs font-bold text-[#5B4A73] mb-1.5">Status Keaktifan Konselor <span class="text-red-500">*</span></label>
                    <select id="status" name="status" x-model="status" required
                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035]">
                        <option value="active" class="text-emerald-600 font-semibold">Aktif (Siap Menerima Pasangan Klien / Sesi)</option>
                        <option value="inactive" class="text-red-500 font-semibold">Nonaktif (Sedang Tidak Bertugas)</option>
                    </select>
                    @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- CARD 4: CATATAN TAMBAHAN --}}
        <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-4">
            <div class="flex items-center gap-3 pb-3 border-b border-[#EDE1FA]">
                <div class="w-8 h-8 rounded-lg bg-orange/10 text-orange flex items-center justify-center font-bold text-sm">
                    4
                </div>
                <div>
                    <h3 class="text-base font-bold text-purple-deep">Catatan & Keterangan Tambahan</h3>
                    <p class="text-xs text-[#6B5B85]">Informasi jadwal ketersediaan atau catatan khusus konselor (opsional)</p>
                </div>
            </div>

            <div>
                <textarea id="notes" name="notes" rows="3" x-model="notes"
                    placeholder="Contoh: Bersedia praktek hari Selasa & Kamis pukul 09:00 - 15:00 WIB..."
                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035] leading-relaxed"></textarea>
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
                <span>Simpan Konselor</span>
            </button>
        </div>
    </form>
</div>
@endsection
