@extends('layouts.dashboard')

@section('title', 'Profil Pengguna — UC PSC')
@section('page-title', 'Profil Pengguna')
@section('back-url', route('dashboard'))

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- ===== KARTU UTAMA PROFIL (SIMETRIS & SEIMBANG) ===== --}}
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#EDE1FA] shadow-xs relative overflow-hidden" x-data="{ previewUrl: null }">
        {{-- Ambient decorative background blur --}}
        <div class="absolute -right-10 -top-10 w-48 h-48 bg-purple-deep/5 rounded-full blur-2xl pointer-events-none"></div>

        <div class="flex flex-col sm:flex-row items-center justify-between gap-6 relative z-10">
            
            {{-- Sisi Kiri: Avatar + Nama + Role Badge (Sejajar & Rapi) --}}
            <div class="flex flex-col sm:flex-row items-center gap-5 text-center sm:text-left">
                {{-- Foto Profil --}}
                <div class="relative group flex-shrink-0">
                    <template x-if="previewUrl">
                        <img :src="previewUrl" alt="Preview Foto" class="w-20 h-20 rounded-2xl object-cover border-2 border-purple-light/20 shadow-xs">
                    </template>
                    <template x-if="!previewUrl">
                        @if($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-20 h-20 rounded-2xl object-cover border-2 border-purple-light/20 shadow-xs">
                        @else
                            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-purple-light to-purple-deep text-white font-extrabold text-2xl flex items-center justify-center shadow-xs">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                        @endif
                    </template>

                    {{-- Hover Overlay Klik Ganti Foto --}}
                    <label for="avatar_input" class="absolute inset-0 bg-black/40 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer text-white" title="Klik untuk ganti foto">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                    </label>
                </div>

                {{-- Detail Nama & Role --}}
                <div class="space-y-1.5">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-[#2A2035] leading-tight">{{ $user->name }}</h1>
                    <div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider {{ $user->isAdmin() ? 'bg-orange/15 text-orange border border-orange/20' : 'bg-purple-deep/10 text-purple-deep border border-purple-deep/20' }}">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            <span>{{ $user->isAdmin() ? 'Administrator' : 'Staff Layanan' }}</span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Sisi Kanan: Aksi Ganti / Hapus Foto --}}
            <div class="flex flex-col items-center sm:items-end gap-1.5 flex-shrink-0">
                <div class="flex items-center gap-2">
                    <form method="POST" action="{{ route('profile.avatar.update') }}" enctype="multipart/form-data" id="avatarForm">
                        @csrf
                        <input 
                            type="file" 
                            id="avatar_input" 
                            name="avatar" 
                            accept="image/jpeg,image/png,image/jpg,image/webp" 
                            class="hidden"
                            @change="
                                const file = $event.target.files[0];
                                if (file) {
                                    previewUrl = URL.createObjectURL(file);
                                    $nextTick(() => $el.form.submit());
                                }
                            "
                        >
                        <label for="avatar_input" class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white text-xs font-bold transition cursor-pointer inline-flex items-center gap-2 shadow-2xs">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <span>Ganti Foto</span>
                        </label>
                    </form>

                    @if($user->avatar)
                    <form method="POST" action="{{ route('profile.avatar.delete') }}" onsubmit="return confirm('Hapus foto profil dan gunakan inisial nama?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-2 rounded-xl bg-red-50 hover:bg-red-500 text-red-500 hover:text-white text-xs font-bold transition cursor-pointer flex items-center gap-1.5 shadow-2xs" title="Hapus Foto">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            <span>Hapus</span>
                        </button>
                    </form>
                    @endif
                </div>
                <span class="text-[10px] text-[#827299]">JPG, PNG, WEBP (Maks. 2MB)</span>
                @error('avatar')
                <p class="text-xs text-red-500 font-medium">{{ $message }}</p>
                @enderror
            </div>

        </div>
    </div>

    {{-- ===== GRID 2 FORM: GANTI EMAIL & GANTI PASSWORD ===== --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">

        {{-- FORM 1: GANTI ALAMAT EMAIL --}}
        <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EDE1FA] shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-2xl bg-purple-deep/10 text-purple-deep flex items-center justify-center flex-shrink-0">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-[#2A2035]">Ganti Alamat Email</h2>
                    </div>
                </div>

                <form method="POST" action="{{ route('profile.email.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="email" class="block text-xs font-bold text-[#2A2035] mb-1.5">Alamat Email <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="{{ old('email') }}" 
                                required
                                class="w-full px-4 py-2.5 rounded-xl border border-[#EDE1FA] bg-[#FAF8FD] text-sm text-[#2A2035] placeholder:text-[#827299]/50 focus:bg-white focus:outline-none focus:border-purple-deep focus:ring-2 focus:ring-purple-deep/10 transition @error('email') border-red-400 bg-red-50/30 @enderror"
                                placeholder="Masukkan email baru"
                            >
                        </div>
                        <p class="text-[11px] text-[#827299]/70 mt-1.5">
                            Email saat ini: <strong class="font-bold text-purple-deep">{{ $user->email }}</strong>
                        </p>
                        @error('email')
                        <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2">
                        <button 
                            type="submit" 
                            class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-purple-deep text-white font-bold text-xs hover:bg-[#3D1D66] transition shadow-xs cursor-pointer"
                        >
                            Simpan Email Baru
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- FORM 2: GANTI KATA SANDI (WARNA & ICON SENADA DENGAN FORM EMAIL) --}}
        <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EDE1FA] shadow-xs flex flex-col justify-between" x-data="{ showCurrent: false, showNew: false, showConfirm: false }">
            <div>
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-2xl bg-purple-deep/10 text-purple-deep flex items-center justify-center flex-shrink-0">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-[#2A2035]">Ganti Kata Sandi</h2>
                    </div>
                </div>

                <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    {{-- Kata Sandi Saat Ini --}}
                    <div>
                        <label for="current_password" class="block text-xs font-bold text-[#2A2035] mb-1.5">Kata Sandi Saat Ini <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input 
                                :type="showCurrent ? 'text' : 'password'" 
                                id="current_password" 
                                name="current_password" 
                                required
                                class="w-full px-4 py-2.5 pr-10 rounded-xl border border-[#EDE1FA] bg-[#FAF8FD] text-sm text-[#2A2035] placeholder:text-[#827299]/50 focus:bg-white focus:outline-none focus:border-purple-deep focus:ring-2 focus:ring-purple-deep/10 transition @error('current_password') border-red-400 bg-red-50/30 @enderror"
                                placeholder="Masukkan kata sandi lama"
                            >
                            <button 
                                type="button" 
                                @click="showCurrent = !showCurrent" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-[#827299] hover:text-purple-deep p-1 transition cursor-pointer"
                                title="Lihat/Sembunyikan Kata Sandi"
                            >
                                <svg x-show="!showCurrent" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg x-show="showCurrent" x-cloak width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                        @error('current_password')
                        <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kata Sandi Baru --}}
                    <div>
                        <label for="password" class="block text-xs font-bold text-[#2A2035] mb-1.5">Kata Sandi Baru <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input 
                                :type="showNew ? 'text' : 'password'" 
                                id="password" 
                                name="password" 
                                required
                                class="w-full px-4 py-2.5 pr-10 rounded-xl border border-[#EDE1FA] bg-[#FAF8FD] text-sm text-[#2A2035] placeholder:text-[#827299]/50 focus:bg-white focus:outline-none focus:border-purple-deep focus:ring-2 focus:ring-purple-deep/10 transition @error('password') border-red-400 bg-red-50/30 @enderror"
                                placeholder="Minimal 6 karakter"
                            >
                            <button 
                                type="button" 
                                @click="showNew = !showNew" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-[#827299] hover:text-purple-deep p-1 transition cursor-pointer"
                                title="Lihat/Sembunyikan Kata Sandi"
                            >
                                <svg x-show="!showNew" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg x-show="showNew" x-cloak width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                        @error('password')
                        <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Konfirmasi Kata Sandi Baru --}}
                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold text-[#2A2035] mb-1.5">Konfirmasi Kata Sandi Baru <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input 
                                :type="showConfirm ? 'text' : 'password'" 
                                id="password_confirmation" 
                                name="password_confirmation" 
                                required
                                class="w-full px-4 py-2.5 pr-10 rounded-xl border border-[#EDE1FA] bg-[#FAF8FD] text-sm text-[#2A2035] placeholder:text-[#827299]/50 focus:bg-white focus:outline-none focus:border-purple-deep focus:ring-2 focus:ring-purple-deep/10 transition"
                                placeholder="Ulangi kata sandi baru"
                            >
                            <button 
                                type="button" 
                                @click="showConfirm = !showConfirm" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-[#827299] hover:text-purple-deep p-1 transition cursor-pointer"
                                title="Lihat/Sembunyikan Kata Sandi"
                            >
                                <svg x-show="!showConfirm" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg x-show="showConfirm" x-cloak width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button 
                            type="submit" 
                            class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-purple-deep text-white font-bold text-xs hover:bg-[#3D1D66] transition shadow-xs cursor-pointer"
                        >
                            Perbarui Kata Sandi
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

</div>
@endsection
