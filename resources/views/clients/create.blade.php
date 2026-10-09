@extends('layouts.dashboard')

@section('title', 'Tambah Klien Baru — UC PSC')
@section('page-title', 'Tambah Klien Baru')
@section('page-subtitle', 'Pendaftaran data klien berdasarkan jenis formulir layanan UC Psychological Service Center')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{
    jenis: @js(old('jenis', $initialJenis ?? '')),
    form_type: @js(old('form_type', $initialFormType ?? '')),

    setJenis(newJenis) {
        this.jenis = newJenis;
        if (newJenis === 'industri') {
            this.form_type = 'industri';
        } else if (newJenis === 'individu') {
            if (this.form_type === 'industri') {
                this.form_type = '';
            }
        } else {
            this.form_type = '';
        }
    },

    setFormType(type) {
        this.form_type = type;
        if (type === 'industri') {
            this.jenis = 'industri';
        } else if (type) {
            this.jenis = 'individu';
        }
    }
}">

    {{-- Tombol Kembali --}}
    <div class="flex items-center justify-start pb-2">
        <a href="{{ route('clients.index') }}"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-[#D9C2F0] bg-white text-sm font-semibold text-black hover:text-purple-deep hover:border-purple-deep shadow-xs transition cursor-pointer w-fit">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            <span>Kembali ke Daftar Klien</span>
        </a>
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

        {{-- SELEKTOR UTAMA: DROPDOWN TIPE KLIEN & FORMULIR LAYANAN --}}
        <div class="bg-white rounded-2xl shadow-xs border border-[#EDE1FA] p-5 sm:p-6 space-y-4">
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

                    {{-- Saat Belum Memilih Tipe Pemohon --}}
                    <div x-show="!jenis">
                        <select disabled
                            class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] bg-slate-50 text-sm font-medium text-gray-400 cursor-not-allowed">
                            <option value="">-- Pilih Tipe Pemohon Terlebih Dahulu --</option>
                        </select>
                    </div>

                    {{-- Pilihan saat Tipe Individu --}}
                    <div x-show="jenis === 'individu'" x-cloak>
                        <select id="select_form_type_individu"
                            x-model="form_type"
                            @change="setFormType($event.target.value)"
                            class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] bg-white text-sm font-medium text-black focus:outline-none focus:ring-2 focus:ring-purple-deep cursor-pointer">
                            <option value="">-- Pilih Formulir Layanan Klien --</option>
                            <option value="dewasa">Formulir Riwayat Hidup Konseling - Dewasa</option>
                            <option value="anak">Formulir Riwayat Hidup Konseling - Anak</option>
                            <option value="pra_nikah">Formulir Riwayat Hidup Konseling - Pra Nikah</option>
                            <option value="pernikahan">Formulir Riwayat Hidup Konseling - Pernikahan</option>
                            <option value="non_industri">Formulir Riwayat Hidup - Non-Industri</option>
                            <option value="biography_en">Biography Form (English)</option>
                        </select>
                    </div>

                    {{-- Pilihan saat Tipe Industri --}}
                    <div x-show="jenis === 'industri'" x-cloak>
                        <select id="select_form_type_industri"
                            x-model="form_type"
                            @change="setFormType($event.target.value)"
                            class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] bg-white text-sm font-medium text-black focus:outline-none focus:ring-2 focus:ring-purple-deep cursor-pointer">
                            <option value="">-- Pilih Formulir Layanan Klien --</option>
                            <option value="industri">Formulir Riwayat Hidup - Industri</option>
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
            <a href="{{ route('clients.index') }}"
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
                <span>Simpan Data Klien</span>
            </button>
        </div>

    </form>
</div>
@endsection
