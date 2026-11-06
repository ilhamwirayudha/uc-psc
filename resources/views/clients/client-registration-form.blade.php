@extends('layouts.dashboard')

@section('title', !empty($existingClient) ? 'Buat Layanan Baru — ' . $existingClient->name . ' — UC PSC' : 'Tambah Klien Baru — UC PSC')
@section('page-title', !empty($existingClient) ? 'Buat Layanan Baru' : 'Tambah Klien Baru')
@section('page-subtitle', !empty($existingClient) ? 'Pendaftaran formulir layanan baru untuk klien terdaftar: ' . $existingClient->name : 'Pendaftaran data klien berdasarkan jenis formulir layanan UC Psychological Service Center')

@section('content')
@php
    $resolvedJenis = old('jenis', $initialJenis ?: (!empty($existingClient) ? ($existingClient->jenis ?: 'individu') : ''));
    $resolvedFormType = old('form_type', $initialFormType ?? '');
@endphp

<script>
function clientRegistrationForm() {
    return {
        jenis: @js($resolvedJenis),
        form_type: @js($resolvedFormType),

        init() {
            if (!this.jenis && @js(!empty($existingClient))) {
                this.jenis = @js(!empty($existingClient) ? ($existingClient->jenis ?: 'individu') : '');
            }
            if (!this.form_type) {
                if (this.jenis === 'industri') {
                    this.form_type = 'industri';
                } else if (this.jenis === 'individu' && @js(!empty($initialFormType))) {
                    this.form_type = @js($initialFormType ?? '');
                }
            }
            this.updateFormOptions();

            this.$watch('form_type', (val) => {
                const select = document.getElementById('select_form_type');
                if (select && select.value !== (val || '')) {
                    select.value = val || '';
                }
            });
        },

        updateFormOptions() {
            const select = document.getElementById('select_form_type');
            if (!select) return;

            const currentVal = this.form_type;
            select.innerHTML = '';

            if (!this.jenis) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = '-- Pilih Tipe Pemohon Terlebih Dahulu --';
                select.appendChild(opt);
                select.disabled = true;
                select.className = "w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] bg-slate-50 text-sm font-medium text-gray-400 cursor-not-allowed transition";
                return;
            }

            select.disabled = false;
            select.className = "w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] bg-white text-sm font-medium text-black focus:outline-none focus:ring-2 focus:ring-purple-deep cursor-pointer transition";

            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = '-- Pilih Formulir Layanan Klien --';
            select.appendChild(defaultOpt);

            let options = [];
            if (this.jenis === 'individu') {
                options = [
                    { value: 'dewasa', text: 'Formulir Riwayat Hidup Konseling - Dewasa' },
                    { value: 'anak', text: 'Formulir Riwayat Hidup Konseling - Anak' },
                    { value: 'pra_nikah', text: 'Formulir Riwayat Hidup Konseling - Pra Nikah' },
                    { value: 'pernikahan', text: 'Formulir Riwayat Hidup Konseling - Pernikahan' },
                    { value: 'non_industri', text: 'Formulir Riwayat Hidup - Non-Industri' },
                    { value: 'biography_en', text: 'Biography Form (English)' }
                ];
            } else if (this.jenis === 'industri') {
                options = [
                    { value: 'industri', text: 'Formulir Riwayat Hidup - Industri' }
                ];
            }

            options.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.value;
                opt.textContent = item.text;
                select.appendChild(opt);
            });

            // Restore currentVal if it exists in valid options
            const validValues = options.map(o => o.value);
            if (validValues.includes(currentVal)) {
                select.value = currentVal;
            } else if (this.jenis === 'industri') {
                select.value = 'industri';
                this.form_type = 'industri';
            } else {
                select.value = '';
                this.form_type = '';
            }
        },

        setJenis(newJenis) {
            this.jenis = newJenis;
            if (newJenis === 'industri') {
                this.form_type = 'industri';
            } else if (newJenis === 'individu') {
                if (this.form_type === 'industri' || !this.form_type) {
                    this.form_type = @js($initialFormType && $initialFormType !== 'industri' ? $initialFormType : '');
                }
            } else {
                this.form_type = '';
            }
            this.updateFormOptions();
        },

        setFormType(type) {
            this.form_type = type;
            if (type === 'industri') {
                this.jenis = 'industri';
            } else if (type) {
                this.jenis = 'individu';
            }
        }
    };
}
</script>

<div class="max-w-5xl mx-auto space-y-6" x-data="clientRegistrationForm()">

    {{-- Tombol Kembali --}}
    <div class="flex items-center justify-between pb-2">
        <a href="{{ !empty($existingClient) ? route('clients.show', ['client' => $existingClient->id, 'tab' => $initialJenis === 'industri' ? 'psychotest' : 'konseling']) : route('clients.index') }}"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-[#D9C2F0] bg-white text-sm font-semibold text-black hover:text-purple-deep hover:border-purple-deep shadow-xs transition cursor-pointer w-fit">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            <span>{{ !empty($existingClient) ? 'Kembali ke Detail Klien' : 'Kembali ke Daftar Klien' }}</span>
        </a>

        @if(!empty($existingClient))
        <div class="flex items-center gap-2 text-xs font-semibold text-purple-deep bg-purple-50 px-3.5 py-1.5 rounded-xl border border-[#EDE1FA]">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <span>Pendaftaran Klien: <span class="font-bold underline">{{ $existingClient->name }}</span></span>
        </div>
        @endif
    </div>

    {{-- Error Summary Alert --}}
    @if($errors->any())
    <div class="bg-rose-50 border border-rose-200 rounded-2xl p-5 shadow-xs">
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center flex-shrink-0 mt-0.5 shadow-xs">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-semibold text-rose-800 mb-1">Mohon lengkapi isian formulir berikut:</h4>
                <ul class="list-disc list-inside text-xs font-medium text-rose-700 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    {{-- Form Utama Pendaftaran Klien --}}
    <form action="{{ route('clients.store') }}" method="POST" data-unsaved-guard class="space-y-6">
        @csrf
        <input type="hidden" name="jenis" :value="jenis">
        <input type="hidden" name="form_type" :value="form_type">
        @if(!empty($existingClient))
            <input type="hidden" name="existing_client_id" value="{{ $existingClient->id }}">
        @endif

        {{-- SELEKTOR UTAMA: DROPDOWN TIPE KLIEN & FORMULIR LAYANAN --}}
        <div class="bg-white rounded-2xl shadow-xs border border-[#EDE1FA] overflow-hidden">
            <div class="bg-purple-deep px-6 py-4 flex items-center gap-2">
                <h3 class="text-base font-semibold text-white">Pilih Jenis Layanan & Formulir Klien</h3>
            </div>
            <div class="p-5 sm:p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                {{-- Dropdown 1: Tipe Pemohon / Klien --}}
                <div>
                    <label for="select_jenis" class="block text-xs font-medium text-gray-500 mb-1.5">
                        Tipe Pemohon / Klien
                    </label>
                    <select id="select_jenis"
                        x-model="jenis"
                        @change="setJenis($event.target.value)"
                        class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] bg-white text-sm font-medium text-black focus:outline-none focus:ring-2 focus:ring-purple-deep cursor-pointer">
                        <option value="">-- Pilih Tipe Pemohon / Klien --</option>
                        <option value="individu">Individu</option>
                        <option value="industri">Industri</option>
                    </select>
                </div>

                    {{-- Dropdown 2: Formulir Layanan Klien --}}
                    <div>
                        <label for="select_form_type" class="block text-xs font-medium text-gray-500 mb-1.5">
                            Formulir Layanan Klien
                        </label>
                        <select id="select_form_type"
                            @change="setFormType($event.target.value)"
                            class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] bg-white text-sm font-medium text-black focus:outline-none focus:ring-2 focus:ring-purple-deep cursor-pointer transition">
                            <option value="">-- Pilih Formulir Layanan Klien --</option>
                        </select>
                    </div>

            </div>
        </div>
    </div>

        {{-- EMPTY STATE: TAMPILKAN SAAT BELUM MEMILIH FORMULIR --}}
        <div x-show="!form_type" class="bg-white rounded-2xl border border-dashed border-[#D9C2F0] p-10 text-center space-y-3 shadow-xs">
            <div class="w-14 h-14 rounded-2xl bg-purple-50 text-purple-deep flex items-center justify-center mx-auto">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </div>
            <h4 class="text-base font-semibold text-black">Pilih Tipe dan Formulir Layanan Klien</h4>
            <p class="text-xs font-medium text-gray-500 max-w-md mx-auto">Silakan tentukan tipe pemohon dan formulir layanan klien pada pilihan di atas untuk menampilkan lembar formulir pendaftaran.</p>
        </div>

        {{-- CONTAINER KARTU ISIAN LENGKAP FORMULIR TERPILIH --}}
        <div x-show="form_type" x-cloak>
            {{-- 1. Formulir Dewasa --}}
            <div x-show="form_type === 'dewasa'" x-cloak>
                <fieldset :disabled="form_type !== 'dewasa'">
                    @include('components.client-forms.dewasa-fields')
                </fieldset>
            </div>

            {{-- 2. Formulir Anak --}}
            <div x-show="form_type === 'anak'" x-cloak>
                <fieldset :disabled="form_type !== 'anak'">
                    @include('components.client-forms.anak-fields')
                </fieldset>
            </div>

            {{-- 3. Formulir Pra-Nikah --}}
            <div x-show="form_type === 'pra_nikah'" x-cloak>
                <fieldset :disabled="form_type !== 'pra_nikah'">
                    @include('components.client-forms.pra-nikah-fields')
                </fieldset>
            </div>

            {{-- 4. Formulir Pernikahan --}}
            <div x-show="form_type === 'pernikahan'" x-cloak>
                <fieldset :disabled="form_type !== 'pernikahan'">
                    @include('components.client-forms.pernikahan-fields')
                </fieldset>
            </div>

            {{-- 5. Formulir Non-Industri --}}
            <div x-show="form_type === 'non_industri'" x-cloak>
                <fieldset :disabled="form_type !== 'non_industri'">
                    @include('components.client-forms.non-industri-fields')
                </fieldset>
            </div>

            {{-- 6. Biography Form (English) --}}
            <div x-show="form_type === 'biography_en'" x-cloak>
                <fieldset :disabled="form_type !== 'biography_en'">
                    @include('components.client-forms.biography-en-fields')
                </fieldset>
            </div>

            {{-- 7. Formulir Industri --}}
            <div x-show="form_type === 'industri'" x-cloak>
                <fieldset :disabled="form_type !== 'industri'">
                    @include('components.client-forms.industri-fields')
                </fieldset>
            </div>
        </div>

        {{-- TOMBOL AKSI BAWAH (HANYA TAMPIL SAAT FORMULIR TERPILIH) --}}
        <div x-show="form_type" x-cloak class="flex items-center justify-end gap-3 pt-3 pb-8">
            <a href="{{ !empty($existingClient) ? route('clients.show', ['client' => $existingClient->id, 'tab' => $initialJenis === 'industri' ? 'psychotest' : 'konseling']) : route('clients.index') }}"
                class="px-6 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 text-sm font-semibold transition text-center shadow-xs">
                Batal
            </a>
            <button type="submit"
                class="px-7 py-2.5 bg-orange hover:bg-orange/90 text-white text-sm font-semibold rounded-xl shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    <polyline points="17 21 17 13 7 13 7 21"/>
                    <polyline points="7 3 7 8 15 8"/>
                </svg>
                <span>{{ !empty($existingClient) ? 'Simpan Layanan Klien' : 'Simpan Data Klien' }}</span>
            </button>
        </div>

    </form>
</div>

@if(!empty($existingClient))
<script>
document.addEventListener('DOMContentLoaded', function() {
    const clientData = @json($existingClient);

    function populateExistingClientData() {
        if (!clientData) return;

        // Pemetaan field standar dari data profil utama klien
        const fieldMapping = {
            // Nama Lengkap
            'nama_lengkap': clientData.name,
            'full_name': clientData.name,

            // Jenis Kelamin
            'jenis_kelamin': clientData.gender === 'l' ? 'Laki-Laki' : (clientData.gender === 'p' ? 'Perempuan' : ''),
            'gender': clientData.gender === 'l' ? 'Male' : (clientData.gender === 'p' ? 'Female' : ''),

            // Tempat Lahir
            'tempat_lahir': clientData.birth_place,
            'asal_kota': clientData.birth_place,
            'city_of_origin': clientData.birth_place,

            // Tanggal Lahir (YYYY-MM-DD)
            'tanggal_lahir': clientData.dob ? clientData.dob.substring(0, 10) : '',

            // Tempat, Tanggal Lahir (Format gabungan untuk form tertentu)
            'tempat_tanggal_lahir': (clientData.birth_place || '') + (clientData.dob ? ', ' + clientData.dob.substring(0, 10) : ''),
            'birth_place_date': (clientData.birth_place || '') + (clientData.dob ? ', ' + clientData.dob.substring(0, 10) : ''),

            // Kontak & Alamat
            'phone': clientData.phone,
            'email': clientData.email,
            'alamat': clientData.address,
            'alamat_sekarang': clientData.address,
            'current_address': clientData.address,

            // Agama & Kepercayaan
            'agama': clientData.religion,
            'religion': clientData.religion,

            // Pendidikan & Pekerjaan
            'pendidikan_terakhir': clientData.education,
            'last_education': clientData.education,
            'pekerjaan': clientData.occupation,
            'pekerjaan_saat_ini': clientData.occupation,
            'nama_perusahaan': clientData.company_name || clientData.pic_name,
        };

        Object.keys(fieldMapping).forEach(fieldName => {
            const val = fieldMapping[fieldName];
            if (!val) return;

            // Cari semua input, select, dan textarea yang namanya fieldName
            const elements = document.querySelectorAll(`[name="${fieldName}"]`);
            elements.forEach(el => {
                if (el.tagName === 'SELECT') {
                    for (let opt of el.options) {
                        if (opt.value && (opt.value.toLowerCase() === String(val).toLowerCase() || opt.text.toLowerCase() === String(val).toLowerCase())) {
                            opt.selected = true;
                            break;
                        }
                    }
                } else if (el.type === 'radio') {
                    if (el.value.toLowerCase() === String(val).toLowerCase()) {
                        el.checked = true;
                    }
                } else {
                    if (!el.value) {
                        el.value = val;
                    }
                }
            });
        });
    }

    // Jalankan saat load dan setiap kali ada pergantian tipe form
    populateExistingClientData();
    window.addEventListener('alpine:initialized', () => {
        setTimeout(populateExistingClientData, 100);
    });

    // Observer untuk mendeteksi saat fieldset formulir baru ditampilkan oleh Alpine
    const observer = new MutationObserver((mutations) => {
        populateExistingClientData();
    });
    const formRoot = document.querySelector('form[action*="clients"]');
    if (formRoot) {
        observer.observe(formRoot, { childList: true, subtree: true, attributes: true, attributeFilter: ['style', 'class'] });
    }
});
</script>
@endif
@endsection
