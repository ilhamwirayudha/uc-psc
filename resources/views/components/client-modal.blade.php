<div x-data="{
    clientModalOpen: false,
    confirmDiscardModalOpen: false,
    jenis: '{{ old('jenis', request('jenis', 'individu')) }}',
    name: @js(old('name', '')),
    pic_name: @js(old('pic_name', '')),
    gender: @js(old('gender', '')),
    birth_place: @js(old('birth_place', '')),
    dob: @js(old('dob', '')),
    religion: @js(old('religion', '')),
    suku_bangsa: @js(old('suku_bangsa', '')),
    marital_status: @js(old('marital_status', '')),
    education: @js(old('education', '')),
    occupation: @js(old('occupation', '')),
    posisi_dituju: @js(old('posisi_dituju', '')),
    phone: @js(old('phone', '')),
    email: @js(old('email', '')),
    address: @js(old('address', '')),
    kontak_darurat: @js(old('kontak_darurat', '')),
    alasan_konseling: @js(old('alasan_konseling', '')),
    notes: @js(old('notes', '')),
    participants: {{ json_encode(old('participants', [''])) }},

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
        if (this.birth_place && this.birth_place.trim().length > 0) return true;
        if (this.religion) return true;
        if (this.suku_bangsa && this.suku_bangsa.trim().length > 0) return true;
        if (this.marital_status) return true;
        if (this.education) return true;
        if (this.occupation && this.occupation.trim().length > 0) return true;
        if (this.posisi_dituju && this.posisi_dituju.trim().length > 0) return true;
        if (this.phone && this.phone.trim().length > 0) return true;
        if (this.email && this.email.trim().length > 0) return true;
        if (this.address && this.address.trim().length > 0) return true;
        if (this.kontak_darurat && this.kontak_darurat.trim().length > 0) return true;
        if (this.alasan_konseling && this.alasan_konseling.trim().length > 0) return true;
        if (this.notes && this.notes.trim().length > 0) return true;
        if (this.participants && this.participants.some(p => p && p.trim().length > 0)) return true;
        if (this.jenis !== 'individu' && this.jenis !== 'individual') return true;
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
        this.birth_place = '';
        this.religion = '';
        this.suku_bangsa = '';
        this.marital_status = '';
        this.education = '';
        this.occupation = '';
        this.posisi_dituju = '';
        this.phone = '';
        this.email = '';
        this.address = '';
        this.kontak_darurat = '';
        this.alasan_konseling = '';
        this.notes = '';
        this.participants = [''];
        this.jenis = 'individu';
    },

    openModal(initialJenis = null) {
        this.clientModalOpen = true;
        if (initialJenis) {
            this.jenis = initialJenis;
        }
    },

    addParticipant() {
        this.participants.push('');
    },
    removeParticipant(i) {
        if (this.participants.length > 1) {
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
        
        // Hapus angka (0-9) untuk nama orang perorangan
        if (this.jenis === 'individu' || this.jenis === 'individual') {
            el.value = el.value.replace(/[0-9]/g, '');
        }
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
                    <div>
                        <h3 class="text-lg font-bold leading-6 text-white" id="client-modal-title">Tambah Klien Baru</h3>
                        <p class="text-xs text-purple-200 mt-0.5">Formulir pendaftaran data klien UC Psychological Service Center</p>
                    </div>
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

                        {{-- CARD 1: TIPE KLIEN (INDIVIDU & INDUSTRI) --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-[#827299] mb-2.5">Tipe Pemohon / Klien</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-1.5 bg-[#F7F5FB] rounded-xl border border-[#EDE1FA]">
                                    {{-- Opsi 1: Individu --}}
                                    <button type="button"
                                        @click="jenis = 'individu'"
                                        :class="(jenis === 'individu' || jenis === 'individual') ? 'bg-white text-purple-deep shadow-xs border border-[#D9C2F0]' : 'text-[#6B5B85] hover:text-purple-deep'"
                                        class="flex items-center justify-center gap-2.5 py-3.5 px-4 rounded-xl text-sm sm:text-base font-bold cursor-pointer transition">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                        <span>Individu</span>
                                    </button>

                                    {{-- Opsi 2: Industri --}}
                                    <button type="button"
                                        @click="jenis = 'industri'"
                                        :class="(jenis === 'industri' || jenis === 'company') ? 'bg-white text-purple-deep shadow-xs border border-[#D9C2F0]' : 'text-[#6B5B85] hover:text-purple-deep'"
                                        class="flex items-center justify-center gap-2.5 py-3.5 px-4 rounded-xl text-sm sm:text-base font-bold cursor-pointer transition">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                        <span>Industri</span>
                                    </button>
                                </div>
                                <p class="text-xs text-[#827299] mt-2" x-show="jenis === 'individu' || jenis === 'individual'">
                                    * Formulir pendaftaran untuk klien perseorangan (konseling dewasa, remaja, anak, pasangan, atau psikotes mandiri).
                                </p>
                                <p class="text-xs text-[#827299] mt-2" x-show="jenis === 'industri' || jenis === 'company'">
                                    * Formulir pendaftaran untuk perusahaan, instansi, atau organisasi (asesmen rekrutmen, evaluasi kerja, atau konseling karyawan).
                                </p>
                            </div>
                        </div>

                        {{-- ========================================================================= --}}
                        {{-- BLOK 1: ISIAN KHUSUS INDIVIDU (SESUAI FORMULIR INDIVIDU / DEWASA)        --}}
                        {{-- ========================================================================= --}}
                        <div x-show="jenis === 'individu' || jenis === 'individual'" class="space-y-6">
                            
                            {{-- CARD 1: DATA IDENTITAS & DIRI --}}
                            <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-5">
                                <div class="pb-3 border-b border-[#EDE1FA] flex items-center justify-between">
                                    <h3 class="text-base font-bold text-purple-deep flex items-center gap-2">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                        <span>Data Identitas & Diri</span>
                                    </h3>
                                    <span class="text-xs text-[#827299]">Formulir Individu</span>
                                </div>

                                <div class="grid sm:grid-cols-2 gap-4">
                                    {{-- Nama Lengkap --}}
                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">
                                            Nama Lengkap <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="name" x-model="name" value="{{ old('name') }}"
                                            :disabled="jenis !== 'individu' && jenis !== 'individual'"
                                            @input="capitalizeInput($event)"
                                            @keydown="if ($event.key >= '0' && $event.key <= '9') $event.preventDefault()"
                                            placeholder="Contoh: Ahmad Fauzi Pratama"
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('name') border-red-300 @enderror"
                                            :required="jenis === 'individu' || jenis === 'individual'">
                                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    {{-- Jenis Kelamin --}}
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">
                                            Jenis Kelamin <span class="text-red-500">*</span>
                                        </label>
                                        <select name="gender" x-model="gender"
                                            :disabled="jenis !== 'individu' && jenis !== 'individual'"
                                            :required="jenis === 'individu' || jenis === 'individual'"
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('gender') border-red-300 @enderror">
                                            <option value="">-- Pilih Jenis Kelamin --</option>
                                            <option value="l" {{ old('gender') === 'l' ? 'selected' : '' }}>Laki-laki</option>
                                            <option value="p" {{ old('gender') === 'p' ? 'selected' : '' }}>Perempuan</option>
                                            <option value="non_binary" {{ old('gender') === 'non_binary' ? 'selected' : '' }}>Non-binary</option>
                                            <option value="transgender" {{ old('gender') === 'transgender' ? 'selected' : '' }}>Transgender</option>
                                            <option value="prefer_not_to_say" {{ old('gender') === 'prefer_not_to_say' ? 'selected' : '' }}>Memilih tidak menyebutkan</option>
                                            <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Lainnya</option>
                                        </select>
                                        @error('gender') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    {{-- Tanggal Lahir --}}
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">
                                            Tanggal Lahir <span class="text-red-500">*</span>
                                        </label>
                                        <input type="date" name="dob" x-model="dob" value="{{ old('dob') }}"
                                            :disabled="jenis !== 'individu' && jenis !== 'individual'"
                                            :required="jenis === 'individu' || jenis === 'individual'"
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('dob') border-red-300 @enderror">
                                        @error('dob') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    {{-- Tempat Lahir --}}
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">
                                            Tempat Lahir
                                        </label>
                                        <input type="text" name="birth_place" x-model="birth_place" value="{{ old('birth_place') }}"
                                            :disabled="jenis !== 'individu' && jenis !== 'individual'"
                                            placeholder="Contoh: Surabaya"
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                                    </div>

                                    {{-- Agama --}}
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">
                                            Agama <span class="text-red-500">*</span>
                                        </label>
                                        <select name="religion" x-model="religion"
                                            :disabled="jenis !== 'individu' && jenis !== 'individual'"
                                            :required="jenis === 'individu' || jenis === 'individual'"
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('religion') border-red-300 @enderror">
                                            <option value="">-- Pilih Agama --</option>
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

                                    {{-- Suku Bangsa --}}
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">
                                            Suku Bangsa <span class="text-xs text-[#827299] font-normal">(Opsional)</span>
                                        </label>
                                        <input type="text" name="suku_bangsa" x-model="suku_bangsa" value="{{ old('suku_bangsa') }}"
                                            :disabled="jenis !== 'individu' && jenis !== 'individual'"
                                            placeholder="Contoh: Jawa, Tionghoa, Batak, Sunda"
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                                    </div>

                                    {{-- Status Perkawinan --}}
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">
                                            Status Perkawinan <span class="text-red-500">*</span>
                                        </label>
                                        <select name="marital_status" x-model="marital_status"
                                            :disabled="jenis !== 'individu' && jenis !== 'individual'"
                                            :required="jenis === 'individu' || jenis === 'individual'"
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('marital_status') border-red-300 @enderror">
                                            <option value="">-- Pilih Status Perkawinan --</option>
                                            <option value="belum_menikah" {{ old('marital_status') === 'belum_menikah' ? 'selected' : '' }}>Belum Menikah (Lajang)</option>
                                            <option value="menikah" {{ old('marital_status') === 'menikah' ? 'selected' : '' }}>Menikah</option>
                                            <option value="cerai_hidup" {{ old('marital_status') === 'cerai_hidup' ? 'selected' : '' }}>Cerai Hidup</option>
                                            <option value="cerai_mati" {{ old('marital_status') === 'cerai_mati' ? 'selected' : '' }}>Cerai Mati</option>
                                        </select>
                                        @error('marital_status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    {{-- Pendidikan Terakhir --}}
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">
                                            Pendidikan Terakhir <span class="text-red-500">*</span>
                                        </label>
                                        <select name="education" x-model="education"
                                            :disabled="jenis !== 'individu' && jenis !== 'individual'"
                                            :required="jenis === 'individu' || jenis === 'individual'"
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('education') border-red-300 @enderror">
                                            <option value="">-- Pilih Pendidikan Terakhir --</option>
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

                                    {{-- Pekerjaan --}}
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">
                                            Pekerjaan Saat Ini
                                        </label>
                                        <select name="occupation" x-model="occupation"
                                            :disabled="jenis !== 'individu' && jenis !== 'individual'"
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                                            <option value="">-- Pilih Pekerjaan --</option>
                                            <option value="Belum Bekerja" {{ old('occupation') === 'Belum Bekerja' ? 'selected' : '' }}>Belum Bekerja</option>
                                            <option value="Pelajar" {{ old('occupation') === 'Pelajar' ? 'selected' : '' }}>Pelajar</option>
                                            <option value="Mahasiswa" {{ old('occupation') === 'Mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                                            <option value="Ibu Rumah Tangga" {{ old('occupation') === 'Ibu Rumah Tangga' ? 'selected' : '' }}>Ibu Rumah Tangga</option>
                                            <option value="Wiraswasta" {{ old('occupation') === 'Wiraswasta' ? 'selected' : '' }}>Wiraswasta</option>
                                            <option value="Karyawan Swasta" {{ old('occupation') === 'Karyawan Swasta' ? 'selected' : '' }}>Karyawan Swasta</option>
                                            <option value="Pegawai Negeri Sipil" {{ old('occupation') === 'Pegawai Negeri Sipil' ? 'selected' : '' }}>Pegawai Negeri Sipil</option>
                                            <option value="BUMN" {{ old('occupation') === 'BUMN' ? 'selected' : '' }}>BUMN</option>
                                            <option value="Penyedia Jasa (Guru/Dokter/Lawyer/Peneliti/Lainnya)" {{ old('occupation') === 'Penyedia Jasa (Guru/Dokter/Lawyer/Peneliti/Lainnya)' ? 'selected' : '' }}>Penyedia Jasa</option>
                                            <option value="Freelance" {{ old('occupation') === 'Freelance' ? 'selected' : '' }}>Freelance</option>
                                            <option value="Lainnya" {{ old('occupation') === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- CARD 2: ALAMAT TEMPAT TINGGAL (LANGSUNG DI BAWAH DATA IDENTITAS & DIRI) --}}
                            <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-4">
                                <div class="pb-3 border-b border-[#EDE1FA] flex items-center justify-between">
                                    <h3 class="text-base font-bold text-purple-deep flex items-center gap-2">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                        <span>Alamat Tempat Tinggal</span>
                                    </h3>
                                    <span class="text-xs text-[#827299]">Domisili Klien</span>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">
                                        <span>Alamat Tempat Tinggal Lengkap</span> <span class="text-red-500">*</span>
                                        <span class="text-[#827299] font-normal">(Jalan, Nomor, RT/RW, Kelurahan, Kecamatan, Kota/Kabupaten, Kode Pos)</span>
                                    </label>
                                    <textarea name="address" rows="3"
                                        x-model="address"
                                        :disabled="jenis !== 'individu' && jenis !== 'individual'"
                                        :required="jenis === 'individu' || jenis === 'individual'"
                                        placeholder="Contoh: Jl. Soekarno Hatta No. 112, RT 002/RW 005, Kel. Jatimulyo, Kec. Lowokwaru, Kota Malang 65141"
                                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep transition @error('address') border-red-400 @enderror">{{ old('address') }}</textarea>
                                    @error('address')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- CARD 3: KONTAK & KONTAK DARURAT (INDIVIDU) --}}
                            <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-4">
                                <div class="pb-3 border-b border-[#EDE1FA]">
                                    <h3 class="text-base font-bold text-purple-deep flex items-center gap-2">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                        <span>Kontak & Kontak Darurat</span>
                                    </h3>
                                </div>

                                <div class="grid sm:grid-cols-2 gap-4">
                                    {{-- No. WhatsApp --}}
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">
                                            No. WhatsApp / Telepon <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="phone" x-model="phone" value="{{ old('phone') }}"
                                            :disabled="jenis !== 'individu' && jenis !== 'individual'"
                                            maxlength="13"
                                            @input="phone = $el.value.replace(/[^0-9]/g, '').slice(0, 13); $el.value = phone"
                                            placeholder="Contoh: 081234567890 (10-13 digit)"
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('phone') border-red-300 @enderror"
                                            :required="jenis === 'individu' || jenis === 'individual'">
                                        @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    {{-- Email --}}
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">
                                            Alamat Email <span class="text-red-500">*</span>
                                        </label>
                                        <input type="email" name="email" x-model="email" value="{{ old('email') }}"
                                            :disabled="jenis !== 'individu' && jenis !== 'individual'"
                                            @input="email = $el.value.toLowerCase(); $el.value = email"
                                            placeholder="Contoh: klien@email.com"
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('email') border-red-300 @enderror"
                                            :required="jenis === 'individu' || jenis === 'individual'">
                                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    {{-- Kontak Darurat --}}
                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">
                                            Kontak Darurat <span class="text-xs text-[#827299] font-normal">(Nama, Hubungan / Relasi & No. HP)</span>
                                        </label>
                                        <input type="text" name="kontak_darurat" x-model="kontak_darurat" value="{{ old('kontak_darurat') }}"
                                            :disabled="jenis !== 'individu' && jenis !== 'individual'"
                                            placeholder="Contoh: Ibu Rina (Ibu Kandung) - 081234567899"
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                                    </div>
                                </div>
                            </div>

                            {{-- CARD 4: KEBUTUHAN LAYANAN & CATATAN (INDIVIDU) --}}
                            <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-4">
                                <div class="pb-3 border-b border-[#EDE1FA]">
                                    <h3 class="text-base font-bold text-purple-deep flex items-center gap-2">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                        <span>Kebutuhan Layanan & Catatan</span>
                                    </h3>
                                </div>

                                <div class="space-y-4">
                                    {{-- Alasan Konseling / Kebutuhan --}}
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">
                                            Alasan Konseling / Kebutuhan Layanan
                                        </label>
                                        <textarea name="alasan_konseling" rows="3" x-model="alasan_konseling"
                                            :disabled="jenis !== 'individu' && jenis !== 'individual'"
                                            placeholder="Ceritakan kendala, keluhan utama, atau tujuan yang ingin dicapai melalui konseling..."
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep transition">{{ old('alasan_konseling') }}</textarea>
                                    </div>

                                    {{-- Catatan Tambahan --}}
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">
                                            Catatan Tambahan (Opsional)
                                        </label>
                                        <textarea name="notes" rows="2" x-model="notes"
                                            :disabled="jenis !== 'individu' && jenis !== 'individual'"
                                            placeholder="Informasi tambahan lain dari klien atau catatan staf..."
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep transition">{{ old('notes') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ========================================================================= --}}
                        {{-- BLOK 2: ISIAN KHUSUS INDUSTRI (SESUAI FORMULIR INDUSTRI / PERUSAHAAN)     --}}
                        {{-- ========================================================================= --}}
                        <div x-show="jenis === 'industri' || jenis === 'company'" class="space-y-6">
                            
                            {{-- CARD 1: DATA PERUSAHAAN & PIC --}}
                            <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-5">
                                <div class="pb-3 border-b border-[#EDE1FA] flex items-center justify-between">
                                    <h3 class="text-base font-bold text-purple-deep flex items-center gap-2">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                        <span>Data Instansi & Narahubung (PIC)</span>
                                    </h3>
                                    <span class="text-xs text-[#827299]">Formulir Industri</span>
                                </div>

                                <div class="grid sm:grid-cols-2 gap-4">
                                    {{-- Nama Perusahaan / Instansi --}}
                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">
                                            Nama Perusahaan / Instansi <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="name" x-model="name" value="{{ old('name') }}"
                                            :disabled="jenis !== 'industri' && jenis !== 'company'"
                                            placeholder="Contoh: PT Ciputra Development Tbk"
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('name') border-red-300 @enderror"
                                            :required="jenis === 'industri' || jenis === 'company'">
                                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    {{-- Nama PIC --}}
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">
                                            Nama PIC / Narahubung HR Instansi <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="pic_name" x-model="pic_name" value="{{ old('pic_name') }}"
                                            :disabled="jenis !== 'industri' && jenis !== 'company'"
                                            @input="capitalizeInput($event)"
                                            @keydown="if ($event.key >= '0' && $event.key <= '9') $event.preventDefault()"
                                            placeholder="Contoh: Ibu Maria (HR Manager)"
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('pic_name') border-red-300 @enderror"
                                            :required="jenis === 'industri' || jenis === 'company'">
                                        @error('pic_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    {{-- Posisi yang Dituju --}}
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">
                                            Posisi / Jabatan yang Dituju (Asesmen)
                                        </label>
                                        <input type="text" name="posisi_dituju" x-model="posisi_dituju" value="{{ old('posisi_dituju') }}"
                                            :disabled="jenis !== 'industri' && jenis !== 'company'"
                                            placeholder="Contoh: Supervisor Operasional / Staff Marketing"
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                                    </div>

                                    {{-- Pekerjaan / Divisi Saat Ini --}}
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">
                                            Pekerjaan / Divisi Saat Ini
                                        </label>
                                        <input type="text" name="occupation" x-model="occupation" value="{{ old('occupation') }}"
                                            :disabled="jenis !== 'industri' && jenis !== 'company'"
                                            placeholder="Contoh: Divisi HRD / Finance / Operasional"
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                                    </div>

                                    {{-- Kontak Darurat PIC --}}
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">
                                            Narahubung Alternatif / Kontak Darurat
                                        </label>
                                        <input type="text" name="kontak_darurat" x-model="kontak_darurat" value="{{ old('kontak_darurat') }}"
                                            :disabled="jenis !== 'industri' && jenis !== 'company'"
                                            placeholder="Contoh: Bpk. Budi (HR Director) - 081234567891"
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                                    </div>

                                    {{-- No. WhatsApp PIC --}}
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">
                                            No. WhatsApp / Telepon PIC <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="phone" x-model="phone" value="{{ old('phone') }}"
                                            :disabled="jenis !== 'industri' && jenis !== 'company'"
                                            maxlength="13"
                                            @input="phone = $el.value.replace(/[^0-9]/g, '').slice(0, 13); $el.value = phone"
                                            placeholder="Contoh: 081234567890 (10-13 digit)"
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('phone') border-red-300 @enderror"
                                            :required="jenis === 'industri' || jenis === 'company'">
                                        @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    {{-- Email PIC / Instansi --}}
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">
                                            Alamat Email PIC / Instansi <span class="text-red-500">*</span>
                                        </label>
                                        <input type="email" name="email" x-model="email" value="{{ old('email') }}"
                                            :disabled="jenis !== 'industri' && jenis !== 'company'"
                                            @input="email = $el.value.toLowerCase(); $el.value = email"
                                            placeholder="Contoh: hr@perusahaan.com"
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('email') border-red-300 @enderror"
                                            :required="jenis === 'industri' || jenis === 'company'">
                                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- CARD 2: ALAMAT KANTOR / INSTANSI (LANGSUNG DI BAWAH DATA INSTANSI & NARAHUBUNG) --}}
                            <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-4">
                                <div class="pb-3 border-b border-[#EDE1FA] flex items-center justify-between">
                                    <h3 class="text-base font-bold text-purple-deep flex items-center gap-2">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                        <span>Alamat Kantor / Instansi</span>
                                    </h3>
                                    <span class="text-xs text-[#827299]">Lokasi Kantor</span>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">
                                        <span>Alamat Kantor Lengkap</span> <span class="text-red-500">*</span>
                                        <span class="text-[#827299] font-normal">(Nama Gedung, Lantai, Jalan, Kota/Kabupaten, Kode Pos)</span>
                                    </label>
                                    <textarea name="address" rows="3"
                                        x-model="address"
                                        :disabled="jenis !== 'industri' && jenis !== 'company'"
                                        :required="jenis === 'industri' || jenis === 'company'"
                                        placeholder="Contoh: Gedung Graha Famili Lt. 5, Jl. Mayjend Yono Soewoyo Kav. 3, Kota Surabaya 60226"
                                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep transition @error('address') border-red-400 @enderror">{{ old('address') }}</textarea>
                                    @error('address')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- CARD 3: DATA DEMOGRAFI KANDIDAT / KARYAWAN (OPSIONAL UNTUK ASESMEN INDUSTRI) --}}
                            <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-4">
                                <div class="pb-3 border-b border-[#EDE1FA] flex items-center justify-between">
                                    <h3 class="text-base font-bold text-purple-deep flex items-center gap-2">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                        <span>Data Demografi Kandidat / Peserta</span>
                                    </h3>
                                    <span class="text-xs text-[#827299] font-normal">(Opsional jika pendaftaran perorangan kandidat)</span>
                                </div>

                                <div class="grid sm:grid-cols-2 gap-4">
                                    {{-- Jenis Kelamin --}}
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Jenis Kelamin</label>
                                        <select name="gender" x-model="gender"
                                            :disabled="jenis !== 'industri' && jenis !== 'company'"
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                                            <option value="">-- Pilih Jenis Kelamin --</option>
                                            <option value="l" {{ old('gender') === 'l' ? 'selected' : '' }}>Laki-laki</option>
                                            <option value="p" {{ old('gender') === 'p' ? 'selected' : '' }}>Perempuan</option>
                                        </select>
                                    </div>

                                    {{-- Tanggal Lahir --}}
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Tanggal Lahir</label>
                                        <input type="date" name="dob" x-model="dob" value="{{ old('dob') }}"
                                            :disabled="jenis !== 'industri' && jenis !== 'company'"
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                                    </div>

                                    {{-- Tempat Lahir --}}
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Tempat Lahir</label>
                                        <input type="text" name="birth_place" x-model="birth_place" value="{{ old('birth_place') }}"
                                            :disabled="jenis !== 'industri' && jenis !== 'company'"
                                            placeholder="Contoh: Surabaya"
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                                    </div>

                                    {{-- Pendidikan Terakhir --}}
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Pendidikan Terakhir</label>
                                        <select name="education" x-model="education"
                                            :disabled="jenis !== 'industri' && jenis !== 'company'"
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                                            <option value="">-- Pilih Pendidikan --</option>
                                            <option value="sma_smk" {{ old('education') === 'sma_smk' ? 'selected' : '' }}>SMA / SMK / Sederajat</option>
                                            <option value="d3" {{ old('education') === 'd3' ? 'selected' : '' }}>Diploma / D3</option>
                                            <option value="s1" {{ old('education') === 's1' ? 'selected' : '' }}>Sarjana / S1 / D4</option>
                                            <option value="s2" {{ old('education') === 's2' ? 'selected' : '' }}>Magister / S2</option>
                                            <option value="s3" {{ old('education') === 's3' ? 'selected' : '' }}>Doktor / S3</option>
                                            <option value="lainnya" {{ old('education') === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- CARD 4: DAFTAR KARYAWAN / PESERTA KOLEKTIF (INDUSTRI) --}}
                            <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-4">
                                <div class="pb-3 border-b border-[#EDE1FA] flex items-center justify-between">
                                    <div>
                                        <h3 class="text-base font-bold text-purple-deep flex items-center gap-2">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                            <span>Daftar Karyawan / Peserta Asesmen Kolektif</span>
                                        </h3>
                                        <p class="text-xs text-[#827299] mt-0.5">Dapat diisi jika pendaftaran berupa batch / kelompok karyawan instansi</p>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <template x-for="(p, i) in participants" :key="i">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-[#6B5B85] w-6" x-text="(i + 1) + '.'"></span>
                                            <input type="text" :name="'participants[' + i + ']'" x-model="participants[i]"
                                                :disabled="jenis !== 'industri' && jenis !== 'company'"
                                                @input="participants[i] = $event.target.value.replace(/[0-9]/g, '').replace(/\b\w/g, c => c.toUpperCase())"
                                                @keydown="if ($event.key >= '0' && $event.key <= '9') $event.preventDefault()"
                                                placeholder="Nama lengkap karyawan / kandidat..."
                                                class="flex-1 px-4 py-2 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep transition">
                                            <button type="button" @click="removeParticipant(i)" x-show="participants.length > 1"
                                                class="p-2 text-red-400 hover:text-red-600 transition rounded-lg hover:bg-red-50 cursor-pointer"
                                                title="Hapus baris">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                            </button>
                                            <div x-show="participants.length <= 1" class="w-[36px]"></div>
                                        </div>
                                    </template>

                                    {{-- Tombol Tambah Peserta --}}
                                    <div class="flex justify-center pt-1">
                                        <button type="button" @click="addParticipant()" 
                                            class="flex items-center gap-1.5 px-3.5 py-1.5 border border-dashed border-[#D9C2F0] rounded-lg text-xs font-semibold text-purple-deep hover:border-purple-deep hover:bg-purple-deep/5 cursor-pointer transition">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                            <span>Tambah Baris Karyawan / Peserta</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- CARD 5: KEBUTUHAN ASESMEN & CATATAN (INDUSTRI) --}}
                            <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-4">
                                <div class="pb-3 border-b border-[#EDE1FA]">
                                    <h3 class="text-base font-bold text-purple-deep flex items-center gap-2">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                        <span>Kebutuhan Asesmen & Catatan Khusus</span>
                                    </h3>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">
                                        Deskripsi Kebutuhan Asesmen / Catatan Tambahan (Opsional)
                                    </label>
                                    <textarea name="notes" rows="3" x-model="notes"
                                        :disabled="jenis !== 'industri' && jenis !== 'company'"
                                        placeholder="Contoh: Kebutuhan asesmen rekrutmen 5 kandidat posisi Sales Manager, atau evaluasi promosi internal..."
                                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep transition">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- ACTION BUTTONS --}}
                        <div class="flex items-center justify-end gap-3 pt-3">
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
