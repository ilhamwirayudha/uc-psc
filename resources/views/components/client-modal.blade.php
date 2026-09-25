@include('clients.partials.address-script')

<div x-data="{
    clientModalOpen: false,
    confirmDiscardModalOpen: false,
    jenis: '{{ old('jenis', request('jenis', 'individual')) }}',
    name: @js(old('name', '')),
    pic_name: @js(old('pic_name', '')),
    dob: @js(old('dob', '')),
    gender: @js(old('gender', '')),
    religion: @js(old('religion', '')),
    marital_status: @js(old('marital_status', '')),
    education: @js(old('education', '')),
    occupation: @js(old('occupation', '')),
    phone: @js(old('phone', '')),
    email: @js(old('email', '')),
    country: @js(old('country', '')),
    province: @js(old('province', '')),
    city: @js(old('city', '')),
    address: @js(old('address', '')),
    notes: @js(old('notes', '')),
    participants: {{ json_encode(old('participants', ['', ''])) }},

    countryOpen: false,
    countrySearch: '',
    provinceOpen: false,
    provinceSearch: '',
    cityOpen: false,
    citySearch: '',

    init() {
        @if($errors->any() && old('from_client_modal'))
            this.clientModalOpen = true;
        @endif
    },

    isDirty() {
        if (this.name && this.name.trim().length > 0) return true;
        if (this.pic_name && this.pic_name.trim().length > 0) return true;
        if (this.dob) return true;
        if (this.gender) return true;
        if (this.religion) return true;
        if (this.marital_status) return true;
        if (this.education) return true;
        if (this.occupation) return true;
        if (this.phone && this.phone.trim().length > 0) return true;
        if (this.email && this.email.trim().length > 0) return true;
        if (this.country && this.country !== 'Indonesia') return true;
        if (this.province) return true;
        if (this.city) return true;
        if (this.address && this.address.trim().length > 0) return true;
        if (this.notes && this.notes.trim().length > 0) return true;
        if (this.participants && this.participants.some(p => p && p.trim().length > 0)) return true;
        if (this.jenis !== 'individual') return true;
        return false;
    },

    requestClose() {
        if (this.isDirty()) {
            this.confirmDiscardModalOpen = true;
        } else {
            this.forceClose();
        }
    },

    cancelDiscard() {
        this.confirmDiscardModalOpen = false;
    },

    executeDiscard() {
        this.confirmDiscardModalOpen = false;
        this.forceClose();
    },

    forceClose() {
        this.clientModalOpen = false;
        this.resetForm();
    },

    resetForm() {
        this.name = '';
        this.pic_name = '';
        this.dob = '';
        this.gender = '';
        this.religion = '';
        this.marital_status = '';
        this.education = '';
        this.occupation = '';
        this.phone = '';
        this.email = '';
        this.country = '';
        this.province = '';
        this.city = '';
        this.address = '';
        this.notes = '';
        this.participants = ['', ''];
        this.jenis = 'individual';
        this.countryOpen = false;
        this.provinceOpen = false;
        this.cityOpen = false;
    },

    openModal(initialJenis = null) {
        this.clientModalOpen = true;
        if (initialJenis) {
            this.jenis = initialJenis;
        }
    },

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
            this.$refs.clientCountrySearchInput && this.$refs.clientCountrySearchInput.focus();
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
            this.$refs.clientProvinceSearchInput && this.$refs.clientProvinceSearchInput.focus();
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
            this.$refs.clientCitySearchInput && this.$refs.clientCitySearchInput.focus();
        });
    },
    selectCity(val) {
        this.city = val;
        this.cityOpen = false;
        this.citySearch = '';
    },

    getFullAddressPreview() {
        let raw = (this.address || '').trim();
        let c = (this.city || '').trim();
        let p = (this.province || '').trim();
        let cntry = (this.country && this.country !== 'Indonesia') ? this.country.trim() : (this.country === 'Indonesia' ? 'Indonesia' : '');

        if (!raw && !c && !p && !cntry) return '-';

        let postalCode = '';
        const match = raw.match(/(?:kode\s*pos\s*[:.-]?\s*)?(\b\d{5}\b)/i);
        if (match) {
            postalCode = match[1];
            raw = raw.replace(new RegExp('(?:,\\s*)?(?:kode\\s*pos\\s*[:.-]?\\s*)?\\b' + postalCode + '\\b', 'ig'), '').trim();
            raw = raw.replace(/,\s*,/g, ',').replace(/^,\s*|,\s*$/g, '');
        }

        const parts = [];
        if (raw) parts.push(raw.replace(/,+$/, ''));
        if (c && !raw.toLowerCase().includes(c.toLowerCase())) parts.push(c);
        if (p && !raw.toLowerCase().includes(p.toLowerCase())) parts.push(p);
        if (cntry && !raw.toLowerCase().includes(cntry.toLowerCase())) parts.push(cntry);
        if (postalCode) parts.push(postalCode);

        return parts.filter(Boolean).join(', ') || '-';
    },

    get isPicNameEnabled() {
        return !!this.name && this.name.trim().length > 0;
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
        if (this.jenis === 'individual') {
            return this.isEducationEnabled && !!this.education;
        }
        return this.isPicNameEnabled && !!this.pic_name && this.pic_name.trim().length > 0;
    },
    get isEmailEnabled() {
        return this.isPhoneEnabled && !!this.phone && this.phone.trim().length >= 10;
    },
    get isContactCompleted() {
        if (this.jenis === 'individual') {
            return !!this.name && this.name.trim().length > 0
                && !!this.dob
                && !!this.gender
                && !!this.religion
                && !!this.marital_status
                && !!this.education
                && !!this.phone && this.phone.trim().length >= 10
                && !!this.email && this.email.trim().length > 0;
        } else {
            return !!this.name && this.name.trim().length > 0
                && !!this.pic_name && this.pic_name.trim().length > 0
                && !!this.phone && this.phone.trim().length >= 10
                && !!this.email && this.email.trim().length > 0;
        }
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
    get isNotesEnabled() {
        return this.isAddressCompleted;
    },

    addParticipant() {
        this.participants.push('');
    },
    removeParticipant(i) {
        if (this.participants.length > 2) {
            this.participants.splice(i, 1);
        } else {
            this.participants[i] = '';
        }
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
}"
@open-client-modal.window="openModal($event.detail?.jenis)"
@keydown.escape.window="if (confirmDiscardModalOpen) { cancelDiscard(); } else if (clientModalOpen) { requestClose(); }"
class="relative z-50"
aria-labelledby="client-modal-title" role="dialog" aria-modal="true" x-cloak>

    {{-- Background backdrop --}}
    <div x-show="clientModalOpen" x-transition.opacity class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"></div>
    
    {{-- Modal panel --}}
    <div x-show="clientModalOpen" x-transition class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0" @click.self="requestClose()">
            <div class="relative transform overflow-hidden rounded-2xl bg-[#F7F5FB] text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-4xl">
                
                <!-- Header -->
                <div class="bg-purple-deep px-6 py-4 rounded-t-2xl flex items-center justify-between shadow-sm">
                    <h3 class="text-lg font-bold leading-6 text-white" id="client-modal-title">Tambah Klien Baru</h3>
                    <button type="button" @click="requestClose()" class="text-white hover:text-gray-200 transition cursor-pointer" title="Tutup formulir">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <!-- Body -->
                <div class="px-6 pt-5 pb-6 max-h-[85vh] overflow-y-auto">
                    
                    {{-- Error Summary Alert --}}
                    @if($errors->any() && old('from_client_modal'))
                    <div class="bg-red-50 border border-red-200 rounded-2xl p-4.5 shadow-xs mb-6">
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
                    <form action="{{ route('clients.store') }}" method="POST" data-unsaved-guard class="space-y-6">
                        @csrf
                        <input type="hidden" name="from_client_modal" value="1">
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

                        {{-- CARD 2: IDENTITAS, DEMOGRAFI & KONTAK --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-5">
                            <div class="pb-3 border-b border-[#EDE1FA]">
                                <h3 class="text-base font-bold text-purple-deep">Data Identitas & Kontak</h3>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">
                                {{-- Nama Lengkap / Nama Kelompok / Nama Perusahaan --}}
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">
                                        <span x-text="jenis === 'company' ? 'Nama Perusahaan / Instansi' : (jenis === 'group' ? 'Nama Kelompok / Keluarga' : 'Nama Lengkap')"></span> <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="name" x-model="name" value="{{ old('name') }}" required
                                        @input="capitalizeInput($event)"
                                        @keydown="if ($event.key >= '0' && $event.key <= '9') $event.preventDefault()"
                                        :placeholder="jenis === 'company' ? 'Contoh: PT Ciputra Mitra Tbk' : (jenis === 'group' ? 'Contoh: Keluarga Bpk. Hendra Wijaya / Pasangan Anton & Siti' : 'Contoh: Ahmad Fauzi Pratama')"
                                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('name') border-red-300 @enderror">
                                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                {{-- PIC jika Kelompok atau Perusahaan --}}
                                <div class="sm:col-span-2" x-show="jenis === 'group' || jenis === 'company'">
                                    <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">
                                        <span x-text="jenis === 'group' ? 'Nama PIC / Perwakilan Kelompok' : 'Nama PIC / Kontak Perwakilan Instansi'"></span> <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="pic_name" x-model="pic_name"
                                        :disabled="!isPicNameEnabled"
                                        :class="isPicNameEnabled ? 'bg-white border-[#D9C2F0]' : 'bg-[#F7F4FB] border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'"
                                        value="{{ old('pic_name') }}" :required="jenis === 'group' || jenis === 'company'"
                                        @input="capitalizeInput($event)"
                                        @keydown="if ($event.key >= '0' && $event.key <= '9') $event.preventDefault()"
                                        :placeholder="isPicNameEnabled ? (jenis === 'group' ? 'Contoh: Bpk. Hendra (Kepala Keluarga) / Ibu Siti' : 'Contoh: Ibu Maria (HR Manager)') : ''"
                                        class="w-full px-4 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                                </div>

                                {{-- KHUSUS INDIVIDU: LAYOUT 2 KOLOM (KIRI & KANAN) --}}
                                <div x-show="jenis === 'individual'" class="sm:col-span-2 grid sm:grid-cols-2 gap-5">
                                    {{-- KOLOM KIRI --}}
                                    <div class="space-y-4">
                                        {{-- 1. Tanggal Lahir --}}
                                        <div>
                                            <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Tanggal Lahir <span class="text-red-500">*</span></label>
                                            <input type="date" name="dob" x-model="dob"
                                                :disabled="!isDobEnabled"
                                                :class="isDobEnabled ? 'bg-white border-[#D9C2F0]' : 'bg-[#F7F4FB] border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'"
                                                value="{{ old('dob') }}" :required="jenis === 'individual'"
                                                class="w-full px-4 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                                            @error('dob') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>

                                        {{-- 2. Jenis Kelamin --}}
                                        <div>
                                            <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Jenis Kelamin <span class="text-red-500">*</span></label>
                                            <select name="gender" x-model="gender"
                                                :disabled="!isGenderEnabled"
                                                :class="isGenderEnabled ? 'bg-white border-[#D9C2F0]' : 'bg-[#F7F4FB] border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'"
                                                :required="jenis === 'individual'"
                                                class="w-full px-4 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                                                <option value="" x-text="isGenderEnabled ? '-- Pilih Jenis Kelamin --' : ''"></option>
                                                <option value="l" {{ old('gender') === 'l' ? 'selected' : '' }}>Laki-laki</option>
                                                <option value="p" {{ old('gender') === 'p' ? 'selected' : '' }}>Perempuan</option>
                                                <option value="non_binary" {{ old('gender') === 'non_binary' ? 'selected' : '' }}>Non-binary</option>
                                                <option value="transgender" {{ old('gender') === 'transgender' ? 'selected' : '' }}>Transgender</option>
                                                <option value="prefer_not_to_say" {{ old('gender') === 'prefer_not_to_say' ? 'selected' : '' }}>Memilih tidak menyebutkan</option>
                                                <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Lainnya</option>
                                            </select>
                                            @error('gender') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>

                                        {{-- 3. Agama (Dropdown) --}}
                                        <div>
                                            <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Agama <span class="text-red-500">*</span></label>
                                            <select name="religion" x-model="religion"
                                                :disabled="!isReligionEnabled"
                                                :class="isReligionEnabled ? 'bg-white border-[#D9C2F0]' : 'bg-[#F7F4FB] border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'"
                                                :required="jenis === 'individual'"
                                                class="w-full px-4 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('religion') border-red-300 @enderror">
                                                <option value="" x-text="isReligionEnabled ? '-- Pilih Agama --' : ''"></option>
                                                <option value="Islam" {{ old('religion') === 'Islam' ? 'selected' : '' }}>Islam</option>
                                                <option value="Kristen" {{ old('religion') === 'Kristen' ? 'selected' : '' }}>Kristen Protestan</option>
                                                <option value="Katolik" {{ old('religion') === 'Katolik' ? 'selected' : '' }}>Katolik</option>
                                                <option value="Hindu" {{ old('religion') === 'Hindu' ? 'selected' : '' }}>Hindu</option>
                                                <option value="Buddha" {{ old('religion') === 'Buddha' ? 'selected' : '' }}>Buddha</option>
                                                <option value="Khonghucu" {{ old('religion') === 'Khonghucu' ? 'selected' : '' }}>Khonghucu</option>
                                                <option value="Penghayat Kepercayaan" {{ old('religion') === 'Penghayat Kepercayaan' ? 'selected' : '' }}>Penghayat Kepercayaan</option>
                                                <option value="Lainnya" {{ old('religion') === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                            </select>
                                            @error('religion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>

                                        {{-- 4. Status Perkawinan --}}
                                        <div>
                                            <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Status Perkawinan <span class="text-red-500">*</span></label>
                                            <select name="marital_status" x-model="marital_status"
                                                :disabled="!isMaritalStatusEnabled"
                                                :class="isMaritalStatusEnabled ? 'bg-white border-[#D9C2F0]' : 'bg-[#F7F4FB] border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'"
                                                :required="jenis === 'individual'"
                                                class="w-full px-4 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('marital_status') border-red-300 @enderror">
                                                <option value="" x-text="isMaritalStatusEnabled ? '-- Pilih Status Perkawinan --' : ''"></option>
                                                <option value="belum_menikah" {{ old('marital_status') === 'belum_menikah' ? 'selected' : '' }}>Belum Menikah (Lajang)</option>
                                                <option value="menikah" {{ old('marital_status') === 'menikah' ? 'selected' : '' }}>Menikah</option>
                                                <option value="cerai_hidup" {{ old('marital_status') === 'cerai_hidup' ? 'selected' : '' }}>Cerai Hidup</option>
                                                <option value="cerai_mati" {{ old('marital_status') === 'cerai_mati' ? 'selected' : '' }}>Cerai Mati</option>
                                            </select>
                                            @error('marital_status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>
                                    </div>

                                    {{-- KOLOM KANAN --}}
                                    <div class="space-y-4">
                                        {{-- 1. Pendidikan Terakhir --}}
                                        <div>
                                            <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Pendidikan Terakhir <span class="text-red-500">*</span></label>
                                            <select name="education" x-model="education"
                                                :disabled="!isEducationEnabled"
                                                :class="isEducationEnabled ? 'bg-white border-[#D9C2F0]' : 'bg-[#F7F4FB] border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'"
                                                :required="jenis === 'individual'"
                                                class="w-full px-4 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('education') border-red-300 @enderror">
                                                <option value="" x-text="isEducationEnabled ? '-- Pilih Pendidikan Terakhir --' : ''"></option>
                                                <option value="sd" {{ old('education') === 'sd' ? 'selected' : '' }}>SD / Sederajat</option>
                                                <option value="smp" {{ old('education') === 'smp' ? 'selected' : '' }}>SMP / Sederajat</option>
                                                <option value="sma_smk" {{ old('education') === 'sma_smk' ? 'selected' : '' }}>SMA / SMK / Sederajat</option>
                                                <option value="d3" {{ old('education') === 'd3' ? 'selected' : '' }}>Diploma / D3</option>
                                                <option value="s1" {{ old('education') === 's1' ? 'selected' : '' }}>Sarjana / S1 / D4</option>
                                                <option value="s2" {{ old('education') === 's2' ? 'selected' : '' }}>Magister / S2</option>
                                                <option value="s3" {{ old('education') === 's3' ? 'selected' : '' }}>Doktor / S3</option>
                                                <option value="lainnya" {{ old('education') === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                                            </select>
                                            @error('education') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>

                                        {{-- 2. Pekerjaan --}}
                                        <div>
                                            <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Pekerjaan</label>
                                            <select name="occupation" x-model="occupation"
                                                :disabled="!isOccupationEnabled"
                                                :class="isOccupationEnabled ? 'bg-white border-[#D9C2F0]' : 'bg-[#F7F4FB] border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'"
                                                class="w-full px-4 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                                                <option value="" x-text="isOccupationEnabled ? '-- Pilih Pekerjaan --' : ''"></option>
                                                <option value="Belum Bekerja" {{ old('occupation') === 'Belum Bekerja' ? 'selected' : '' }}>Belum Bekerja</option>
                                                <option value="Pelajar" {{ old('occupation') === 'Pelajar' ? 'selected' : '' }}>Pelajar</option>
                                                <option value="Mahasiswa" {{ old('occupation') === 'Mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                                                <option value="Ibu Rumah Tangga" {{ old('occupation') === 'Ibu Rumah Tangga' ? 'selected' : '' }}>Ibu Rumah Tangga</option>
                                                <option value="Wiraswasta" {{ old('occupation') === 'Wiraswasta' ? 'selected' : '' }}>Wiraswasta</option>
                                                <option value="Penyedia Jasa (Guru/Dokter/Lawyer/Peneliti/Lainnya)" {{ old('occupation') === 'Penyedia Jasa (Guru/Dokter/Lawyer/Peneliti/Lainnya)' ? 'selected' : '' }}>Penyedia Jasa (Guru/Dokter/Lawyer/Peneliti/Lainnya)</option>
                                                <option value="Freelance" {{ old('occupation') === 'Freelance' ? 'selected' : '' }}>Freelance</option>
                                                <option value="Karyawan Swasta" {{ old('occupation') === 'Karyawan Swasta' ? 'selected' : '' }}>Karyawan Swasta</option>
                                                <option value="Pegawai Negeri Sipil" {{ old('occupation') === 'Pegawai Negeri Sipil' ? 'selected' : '' }}>Pegawai Negeri Sipil</option>
                                                <option value="BUMN" {{ old('occupation') === 'BUMN' ? 'selected' : '' }}>BUMN</option>
                                                <option value="Lainnya" {{ old('occupation') === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                            </select>
                                        </div>

                                        {{-- 3. No. Telepon / WhatsApp --}}
                                        <div>
                                            <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">No. Telepon / WhatsApp <span class="text-red-500">*</span></label>
                                            <input type="text" :name="jenis === 'individual' ? 'phone' : 'phone_ind_disabled'"
                                                x-model="phone"
                                                :disabled="jenis !== 'individual' || !isPhoneEnabled"
                                                :class="(jenis === 'individual' && isPhoneEnabled) ? 'bg-white border-[#D9C2F0]' : 'bg-[#F7F4FB] border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'"
                                                value="{{ old('phone') }}" :required="jenis === 'individual'"
                                                maxlength="13"
                                                @input="phone = $el.value.replace(/[^0-9]/g, '').slice(0, 13); $el.value = phone"
                                                :placeholder="(jenis === 'individual' && isPhoneEnabled) ? 'Contoh: 081234567890 (10-13 digit)' : ''"
                                                class="w-full px-4 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('phone') border-red-300 @enderror">
                                            @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>

                                        {{-- 4. Email --}}
                                        <div>
                                            <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Email <span class="text-red-500">*</span></label>
                                            <input type="email" :name="jenis === 'individual' ? 'email' : 'email_ind_disabled'"
                                                x-model="email"
                                                :disabled="jenis !== 'individual' || !isEmailEnabled"
                                                :class="(jenis === 'individual' && isEmailEnabled) ? 'bg-white border-[#D9C2F0]' : 'bg-[#F7F4FB] border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'"
                                                value="{{ old('email') }}" :required="jenis === 'individual'"
                                                @input="email = $el.value.toLowerCase(); $el.value = email"
                                                :placeholder="(jenis === 'individual' && isEmailEnabled) ? 'klien@email.com' : ''"
                                                class="w-full px-4 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('email') border-red-300 @enderror">
                                            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- KHUSUS KELOMPOK / PERUSAHAAN: KONTAK PIC --}}
                                <div x-show="jenis === 'group' || jenis === 'company'" class="sm:col-span-2 grid sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">No. Telepon / WhatsApp PIC <span class="text-red-500">*</span></label>
                                        <input type="text" :name="jenis !== 'individual' ? 'phone' : 'phone_grp_disabled'"
                                            x-model="phone"
                                            :disabled="jenis === 'individual' || !isPhoneEnabled"
                                            :class="(jenis !== 'individual' && isPhoneEnabled) ? 'bg-white border-[#D9C2F0]' : 'bg-[#F7F4FB] border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'"
                                            value="{{ old('phone') }}" :required="jenis !== 'individual'"
                                            maxlength="13"
                                            @input="phone = $el.value.replace(/[^0-9]/g, '').slice(0, 13); $el.value = phone"
                                            :placeholder="(jenis !== 'individual' && isPhoneEnabled) ? 'Contoh: 081234567890 (10-13 digit)' : ''"
                                            class="w-full px-4 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('phone') border-red-300 @enderror">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Alamat Email PIC <span class="text-red-500">*</span></label>
                                        <input type="email" :name="jenis !== 'individual' ? 'email' : 'email_grp_disabled'"
                                            x-model="email"
                                            :disabled="jenis === 'individual' || !isEmailEnabled"
                                            :class="(jenis !== 'individual' && isEmailEnabled) ? 'bg-white border-[#D9C2F0]' : 'bg-[#F7F4FB] border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'"
                                            value="{{ old('email') }}" :required="jenis !== 'individual'"
                                            @input="email = $el.value.toLowerCase(); $el.value = email"
                                            :placeholder="(jenis !== 'individual' && isEmailEnabled) ? 'pic@instansi.com' : ''"
                                            class="w-full px-4 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('email') border-red-300 @enderror">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- CARD: DATA ALAMAT --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-5">
                            <div class="pb-3 border-b border-[#EDE1FA]">
                                <h3 class="text-base font-bold text-purple-deep" x-text="jenis === 'company' ? 'Alamat Perusahaan' : 'Alamat'">Alamat</h3>
                            </div>

                            <div class="grid sm:grid-cols-3 gap-4">
                                {{-- 1. NEGARA (Dropdown dengan Search Box di Atas) --}}
                                <div class="relative" @click.outside="countryOpen = false">
                                    <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Negara <span class="text-red-500">*</span></label>
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
                                                    x-ref="clientCountrySearchInput"
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
                                    <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">
                                        Provinsi 
                                        <span x-show="country === 'Indonesia'" class="text-red-500">*</span>
                                        <span x-show="country !== 'Indonesia'" class="text-[10px] font-normal text-[#9D8EB0]">(Khusus Indonesia)</span>
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
                                                    x-ref="clientProvinceSearchInput"
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
                                    <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">
                                        Kota / Kabupaten
                                        <span x-show="country === 'Indonesia'" class="text-red-500">*</span>
                                        <span x-show="country !== 'Indonesia'" class="text-[10px] font-normal text-[#9D8EB0]">(Khusus Indonesia)</span>
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
                                                    x-ref="clientCitySearchInput"
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

                                {{-- Alamat Lengkap --}}
                                <div class="sm:col-span-3">
                                    <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Alamat Lengkap <span class="text-red-500">*</span> <span class="text-[#827299] font-normal">(Jalan, Nomor, RT/RW, Kelurahan, Kecamatan, Kode Pos)</span></label>
                                    <textarea name="address" rows="2" required
                                        x-model="address"
                                        :disabled="!isAddressEnabled"
                                        :class="isAddressEnabled 
                                            ? 'bg-white border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep @error('address') border-red-400 @enderror' 
                                            : 'bg-[#F7F4FB] border border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'"
                                        :placeholder="isAddressEnabled ? 'Contoh: Jl. Soekarno Hatta No. 112, RT 002/RW 005, Kel. Jatimulyo, Kec. Lowokwaru' : ''"
                                        class="w-full px-4 py-2.5 rounded-xl text-sm transition">{{ old('address') }}</textarea>
                                    @error('address')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror

                                    {{-- Live Preview Format Penulisan Alamat Standar --}}
                                    <div x-show="isAddressEnabled && (address || city || province)" class="mt-2 p-3 rounded-xl bg-gray-50 border border-gray-200 text-xs space-y-1">
                                        <div class="flex items-center gap-1.5 font-bold text-purple-deep text-[11px]">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                            <span>Format Alamat Lengkap Otomatis:</span>
                                        </div>
                                        <p class="text-black font-semibold leading-relaxed" 
                                           x-text="getFullAddressPreview()"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- CARD: CATATAN & KETERANGAN TAMBAHAN --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-4">
                            <div class="pb-3 border-b border-[#EDE1FA]">
                                <h3 class="text-base font-bold text-purple-deep">Catatan & Keterangan Tambahan (Opsional)</h3>
                            </div>

                            <div>
                                <textarea name="notes" rows="3"
                                    x-model="notes"
                                    :disabled="!isNotesEnabled"
                                    :class="isNotesEnabled 
                                        ? 'bg-white border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep' 
                                        : 'bg-[#F7F4FB] border border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'"
                                    :placeholder="isNotesEnabled ? 'Contoh: Latar belakang pengajuan konsultasi atau kebutuhan khusus lainnya...' : ''"
                                    class="w-full px-4 py-2.5 rounded-xl text-sm transition">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        {{-- CARD 3: DAFTAR ANGGOTA / PESERTA (KHUSUS KELOMPOK & PERUSAHAAN) --}}
                        <div x-show="jenis === 'group' || jenis === 'company'" class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-4">
                            <div class="pb-3 border-b border-[#EDE1FA] flex items-center justify-between">
                                <div>
                                    <h3 class="text-base font-bold text-purple-deep flex items-center gap-1">
                                        <span x-text="jenis === 'company' ? 'Daftar Karyawan / Peserta Instansi' : 'Daftar Anggota / Peserta Kelompok'"></span>
                                        <span class="text-red-500">*</span>
                                    </h3>
                                </div>
                            </div>

                            {{-- Error dari server --}}
                            @error('participants')
                            <div class="flex items-center gap-2 p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                {{ $message }}
                            </div>
                            @enderror

                            <div class="space-y-3">
                                <template x-for="(p, i) in participants" :key="i">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-[#6B5B85] w-6" x-text="(i + 1) + '.'"></span>
                                        <input type="text" :name="'participants[' + i + ']'" x-model="participants[i]"
                                            :disabled="!isNotesEnabled"
                                            @input="participants[i] = $event.target.value.replace(/[0-9]/g, '').replace(/\b\w/g, c => c.toUpperCase())"
                                            @keydown="if ($event.key >= '0' && $event.key <= '9') $event.preventDefault()"
                                            :placeholder="jenis === 'company' ? 'Nama lengkap karyawan / kandidat...' : 'Nama lengkap anggota keluarga / pasangan...'"
                                            class="flex-1 px-4 py-2 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep transition disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50">
                                        <button type="button" @click="removeParticipant(i)" x-show="participants.length > 2"
                                            class="p-2 text-red-400 hover:text-red-600 transition rounded-lg hover:bg-red-50 cursor-pointer">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        </button>
                                        <div x-show="participants.length <= 2" class="w-[36px]"></div>
                                    </div>
                                </template>

                                {{-- Tombol Tambah Anggota --}}
                                <div class="flex justify-center">
                                    <button type="button" @click="addParticipant()" 
                                        :disabled="participants[participants.length - 1].trim() === ''"
                                        :class="participants[participants.length - 1].trim() === '' 
                                            ? 'border-[#EDE1FA] text-[#C4B5D9] cursor-not-allowed opacity-50' 
                                            : 'border-[#D9C2F0] text-purple-deep hover:border-purple-deep hover:bg-purple-deep/5 cursor-pointer'"
                                        class="flex items-center gap-1.5 px-3 py-1.5 border border-dashed rounded-lg text-xs font-semibold transition">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                        <span>Tambah Anggota</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- ACTION BUTTONS --}}
                        <div class="flex items-center justify-end gap-3 pt-4">
                            <button type="button" @click="requestClose()" 
                                class="px-6 py-2.5 text-center text-sm font-semibold text-[#6B5B85] border border-[#D9C2F0] rounded-xl hover:bg-[#F7F5FB] transition cursor-pointer">
                                Batal
                            </button>

                            <button type="submit"
                                class="px-6 py-2.5 bg-purple-deep text-white text-sm font-bold rounded-xl hover:bg-purple-deep/90 transition shadow-sm cursor-pointer flex items-center justify-center gap-2">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                <span>Simpan Data Klien</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL KONFIRMASI BATALKAN PENGISIAN DATA (PELINDUNG DATA INPUT)          --}}
    {{-- ========================================================================= --}}
    <div x-cloak x-show="confirmDiscardModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4" @click.self="cancelDiscard()">
        {{-- Backdrop --}}
        <div x-show="confirmDiscardModalOpen" 
            x-transition:enter="ease-out duration-200" 
            x-transition:enter-start="opacity-0" 
            x-transition:enter-end="opacity-100" 
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-[#1F0E38]/60 backdrop-blur-sm" 
            @click.stop="cancelDiscard()"></div>

        {{-- Dialog Box --}}
        <div x-show="confirmDiscardModalOpen" 
            @click.stop
            x-transition:enter="ease-out duration-200" 
            x-transition:enter-start="opacity-0 scale-95" 
            x-transition:enter-end="opacity-100 scale-100" 
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative bg-white rounded-3xl shadow-2xl border border-[#EDE1FA] max-w-sm w-full p-6 sm:p-7 z-10 space-y-4 text-center overflow-hidden">
            
            {{-- Specular Highlight Line --}}
            <div class="absolute top-0 inset-x-8 h-[1.5px] bg-gradient-to-r from-transparent via-amber-400 to-transparent pointer-events-none"></div>

            {{-- Warning Icon Badge --}}
            <div class="mx-auto w-14 h-14 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-600 flex items-center justify-center shadow-xs">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
            
            <div class="space-y-2">
                <h3 class="text-lg font-extrabold text-[#260E45] tracking-tight">Tinggalkan Pengisian Data?</h3>
                <p class="text-xs sm:text-sm text-[#6B5B85] leading-relaxed">
                    Data klien yang telah Anda masukkan belum disimpan. Jika Anda keluar sekarang, seluruh draf pengisian formulir akan hilang.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-2">
                <button type="button" @click.stop="cancelDiscard()" 
                    class="w-full py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold text-purple-deep hover:text-[#260E45] bg-[#F7F5FB] hover:bg-[#EDE1FA] border border-[#D9C2F0] transition cursor-pointer">
                    Lanjut Mengisi
                </button>
                <button type="button" @click.stop="executeDiscard()" 
                    class="w-full py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold text-white bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 hover:from-amber-600 hover:to-orange-600 transition shadow-xs cursor-pointer">
                    Ya, Batalkan
                </button>
            </div>
        </div>
    </div>
</div>
