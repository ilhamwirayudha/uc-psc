@extends('layouts.dashboard')

@section('title', 'Profil Saya — UC PSC')
@section('page-title', 'Profil Saya')
@section('page-subtitle', 'Kelola biodata akun, ringkasan akses, dan pengaturan keamanan Anda')

@section('content')
@php
    $joinedAtText = $user->created_at
        ? $user->created_at->locale('id')->translatedFormat('j F Y, H:i')
        : '-';
    $updatedAtText = $user->updated_at
        ? $user->updated_at->locale('id')->translatedFormat('j F Y, H:i')
        : '-';

    $hasAvatarError = $errors->has('avatar');
    $hasBiodataError = $errors->has('name')
        || $errors->has('email')
        || $errors->has('employee_id')
        || $errors->has('position')
        || $errors->has('phone')
        || $errors->has('gender')
        || $errors->has('address');
    $hasPasswordError = $errors->has('current_password') || $errors->has('password');
@endphp

<script>
function userProfileHero() {
    return {
        settingsMenuOpen: false,
        editBiodataMode: @js($hasBiodataError),
        showAvatarModal: @js($hasAvatarError),
        confirmingDeleteAvatar: false,
        showPasswordModal: @js($hasPasswordError),
        showProfileUnsavedModal: false,
        pendingAction: null,

        previewUrl: null,
        hasSelectedAvatarFile: false,

        showCurrent: false,
        showNew: false,
        showConfirm: false,

        initialBiodata: {
            name: @js($user->name ?? ''),
            email: @js($user->email ?? ''),
            employee_id: @js($user->employee_id ?? ''),
            position: @js($user->position ?? ''),
            phone: @js($user->phone ?? ''),
            gender: @js($user->gender ?? ''),
            address: @js($user->address ?? ''),
        },
        biodataForm: {
            name: @js(old('name', $user->name ?? '')),
            email: @js(old('email', $user->email ?? '')),
            employee_id: @js(old('employee_id', $user->employee_id ?? '')),
            position: @js(old('position', $user->position ?? '')),
            phone: @js(old('phone', $user->phone ?? '')),
            gender: @js(old('gender', $user->gender ?? '')),
            address: @js(old('address', $user->address ?? '')),
        },
        passwordForm: {
            current_password: '',
            password: '',
            password_confirmation: '',
        },

        isBiodataDirty() {
            if (!this.editBiodataMode) return false;
            return JSON.stringify(this.biodataForm) !== JSON.stringify(this.initialBiodata);
        },

        isPasswordDirty() {
            if (!this.showPasswordModal) return false;
            return (this.passwordForm.current_password || '').length > 0
                || (this.passwordForm.password || '').length > 0
                || (this.passwordForm.password_confirmation || '').length > 0;
        },

        openEditBiodataFromMenu() {
            this.settingsMenuOpen = false;
            if (!this.editBiodataMode) {
                this.biodataForm = JSON.parse(JSON.stringify(this.initialBiodata));
                this.editBiodataMode = true;
            }
            this.$nextTick(() => {
                const input = document.getElementById('biodata_name');
                if (input) input.focus({ preventScroll: false });
            });
        },

        handleCancelBiodataEdit() {
            if (this.isBiodataDirty()) {
                this.pendingAction = 'cancel_biodata';
                this.showProfileUnsavedModal = true;
                return;
            }
            this.resetBiodataToInitial();
        },

        resetBiodataToInitial() {
            this.biodataForm = JSON.parse(JSON.stringify(this.initialBiodata));
            this.editBiodataMode = false;
        },

        openAvatarModalFromMenu() {
            this.settingsMenuOpen = false;
            if (this.isBiodataDirty()) {
                this.pendingAction = 'open_avatar_modal';
                this.showProfileUnsavedModal = true;
                return;
            }
            this.resetBiodataToInitial();
            this.confirmingDeleteAvatar = false;
            this.showAvatarModal = true;
        },

        closeAvatarModal() {
            this.showAvatarModal = false;
            this.confirmingDeleteAvatar = false;
            this.previewUrl = null;
            this.hasSelectedAvatarFile = false;
            const fileEl = document.getElementById('modal_avatar_input');
            if (fileEl) fileEl.value = '';
        },

        openPasswordModalFromMenu() {
            this.settingsMenuOpen = false;
            if (this.isBiodataDirty()) {
                this.pendingAction = 'open_password_modal';
                this.showProfileUnsavedModal = true;
                return;
            }
            this.resetBiodataToInitial();
            this.showPasswordModal = true;
        },

        handleClosePasswordModal() {
            if (this.isPasswordDirty()) {
                this.pendingAction = 'close_password_modal';
                this.showProfileUnsavedModal = true;
                return;
            }
            this.clearAndClosePasswordModal();
        },

        clearAndClosePasswordModal() {
            this.passwordForm.current_password = '';
            this.passwordForm.password = '';
            this.passwordForm.password_confirmation = '';
            this.showCurrent = false;
            this.showNew = false;
            this.showConfirm = false;
            this.showPasswordModal = false;
        },

        handleLogoutFromMenu() {
            this.settingsMenuOpen = false;
            if (this.isBiodataDirty()) {
                this.pendingAction = 'open_logout';
                this.showProfileUnsavedModal = true;
                return;
            }
            window.dispatchEvent(new CustomEvent('open-logout-modal'));
        },

        confirmDiscardUnsaved() {
            this.showProfileUnsavedModal = false;
            const action = this.pendingAction;
            this.pendingAction = null;

            if (action === 'cancel_biodata') {
                this.resetBiodataToInitial();
            } else if (action === 'open_avatar_modal') {
                this.resetBiodataToInitial();
                this.confirmingDeleteAvatar = false;
                this.showAvatarModal = true;
            } else if (action === 'open_password_modal') {
                this.resetBiodataToInitial();
                this.showPasswordModal = true;
            } else if (action === 'close_password_modal') {
                this.clearAndClosePasswordModal();
            } else if (action === 'open_logout') {
                this.resetBiodataToInitial();
                window.dispatchEvent(new CustomEvent('open-logout-modal'));
            }
        },

        formatGenderDisplay(code) {
            if (code === 'l') return 'Laki-laki';
            if (code === 'p') return 'Perempuan';
            return '-';
        },

        getPasswordStrength() {
            const pwd = this.passwordForm.password || '';
            if (!pwd.length) {
                return { score: 0, label: 'Belum diisi', barClass: 'bg-slate-200', textClass: 'text-slate-500' };
            }
            let score = 0;
            if (pwd.length >= 6) score++;
            if (pwd.length >= 10) score++;
            if (/[A-Z]/.test(pwd) && /[a-z]/.test(pwd)) score++;
            if (/[0-9]/.test(pwd) && /[^A-Za-z0-9]/.test(pwd)) score++;

            if (pwd.length < 6) {
                return { score: 1, label: 'Terlalu Pendek', barClass: 'bg-red-500', textClass: 'text-red-600' };
            }
            if (score <= 1) {
                return { score: 1, label: 'Lemah', barClass: 'bg-red-500', textClass: 'text-red-600' };
            }
            if (score === 2) {
                return { score: 2, label: 'Cukup', barClass: 'bg-amber-500', textClass: 'text-amber-600' };
            }
            if (score === 3) {
                return { score: 3, label: 'Baik', barClass: 'bg-purple-light', textClass: 'text-purple-deep' };
            }
            return { score: 4, label: 'Kuat', barClass: 'bg-emerald-500', textClass: 'text-emerald-600' };
        },

        hasLengthReq() {
            return (this.passwordForm.password || '').length >= 6;
        },

        hasMixReq() {
            const pwd = this.passwordForm.password || '';
            return /[A-Za-z]/.test(pwd) && (/[0-9]/.test(pwd) || /[^A-Za-z0-9]/.test(pwd));
        },

        passwordsMatch() {
            return (this.passwordForm.password || '').length > 0
                && this.passwordForm.password === this.passwordForm.password_confirmation;
        }
    };
}
</script>

<div x-data="userProfileHero()" class="max-w-6xl mx-auto space-y-5">

    {{-- ==================================================================== --}}
    {{-- HERO BANNER + IDENTITAS UTAMA + SETTINGS DROPDOWN                    --}}
    {{-- ==================================================================== --}}
    <div class="glass-card rounded-3xl relative">

        {{-- Decorative Hero Cover Banner --}}
        <div class="h-24 sm:h-28 rounded-t-3xl relative overflow-hidden bg-gradient-to-r from-[#4B267D] via-[#6B3FA0] to-[#E67E22]/85">
            {{-- Ambient Light Orbs --}}
            <div class="absolute -left-10 -top-12 w-56 h-56 rounded-full bg-white/15 blur-2xl pointer-events-none"></div>
            <div class="absolute right-8 -bottom-16 w-64 h-64 rounded-full bg-[#FDBA74]/25 blur-2xl pointer-events-none"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.2),transparent_55%)] pointer-events-none"></div>
        </div>

        {{-- Identity Row + Settings Dropdown Trigger --}}
        <div class="px-6 sm:px-8 pt-0 pb-6 rounded-b-3xl relative">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">

                {{-- Left: Overlapping Avatar + Name + Role + Position --}}
                <div class="flex flex-col sm:flex-row sm:items-end gap-4 -mt-10 sm:-mt-12 relative z-10">
                    <div class="flex-shrink-0">
                        @if($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover border-4 border-white shadow-md bg-white">
                        @else
                            <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-gradient-to-br from-purple-light to-purple-deep text-white font-semibold text-2xl sm:text-3xl flex items-center justify-center border-4 border-white shadow-md select-none">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    <div class="space-y-1 pt-1 sm:pt-0 sm:pb-0.5">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <h2 class="text-lg sm:text-xl font-semibold text-black leading-tight">{{ $user->name }}</h2>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->isAdmin() ? 'bg-orange/15 text-orange border border-orange/25' : 'bg-purple-deep/10 text-purple-deep border border-purple-deep/20' }}">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                <span>{{ $user->isAdmin() ? 'Administrator' : 'Staff Layanan' }}</span>
                            </span>
                        </div>
                        <p class="text-xs sm:text-sm font-medium text-slate-500">
                            {{ $user->position ?: 'Akun Internal UC PSC' }} &bull; {{ $user->email }}
                        </p>
                    </div>
                </div>

                {{-- Right: Tombol Settings Dropdown --}}
                <div class="relative z-20 self-start sm:self-end shrink-0" @click.outside="settingsMenuOpen = false">
                    <button
                        type="button"
                        @click="settingsMenuOpen = !settingsMenuOpen"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-[#D9C2F0] text-black hover:text-purple-deep hover:bg-purple-50/70 text-xs sm:text-sm font-semibold shadow-xs transition cursor-pointer"
                        :aria-expanded="settingsMenuOpen"
                    >
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                        </svg>
                        <span>Settings</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="transition-transform duration-150" :class="settingsMenuOpen ? 'rotate-180' : ''">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </button>

                    {{-- Dropdown Menu Pengaturan --}}
                    <div
                        x-show="settingsMenuOpen"
                        x-cloak
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                        class="absolute right-0 mt-2 w-60 rounded-2xl bg-white/95 backdrop-blur-xl border border-[#EDE1FA] shadow-[0_18px_40px_rgba(74,35,128,0.16)] py-2 z-30"
                    >
                        <div class="px-3.5 py-1.5 border-b border-slate-100 mb-1">
                            <span class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Menu Pengaturan</span>
                        </div>

                        <button
                            type="button"
                            @click="openEditBiodataFromMenu()"
                            class="w-full px-3.5 py-2.5 text-left flex items-center gap-2.5 text-xs sm:text-sm font-medium text-black hover:bg-purple-50 hover:text-purple-deep transition cursor-pointer"
                        >
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-purple-deep flex-shrink-0"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            <span>Edit Biodata &amp; Akun</span>
                        </button>

                        <button
                            type="button"
                            @click="openAvatarModalFromMenu()"
                            class="w-full px-3.5 py-2.5 text-left flex items-center gap-2.5 text-xs sm:text-sm font-medium text-black hover:bg-purple-50 hover:text-purple-deep transition cursor-pointer"
                        >
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-purple-deep flex-shrink-0"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                            <span>Ubah Foto Profil</span>
                        </button>

                        <button
                            type="button"
                            @click="openPasswordModalFromMenu()"
                            class="w-full px-3.5 py-2.5 text-left flex items-center gap-2.5 text-xs sm:text-sm font-medium text-black hover:bg-purple-50 hover:text-purple-deep transition cursor-pointer"
                        >
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-purple-deep flex-shrink-0"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <span>Ubah Kata Sandi</span>
                        </button>

                        <div class="my-1.5 border-t border-slate-100"></div>

                        <button
                            type="button"
                            @click="handleLogoutFromMenu()"
                            class="w-full px-3.5 py-2.5 text-left flex items-center gap-2.5 text-xs sm:text-sm font-medium text-red-600 hover:bg-red-50 transition cursor-pointer"
                        >
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                            <span>Keluar Akun</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>

    </div>

    {{-- ==================================================================== --}}
    {{-- 2-CARD VIEW/EDIT GRID                                                --}}
    {{-- ==================================================================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">

        {{-- KARTU KIRI (7/12): BIODATA USER (DEFAULT MODE VIEW / EDIT VIA SETTINGS) --}}
        <div id="biodataCard" class="lg:col-span-7 glass-card rounded-3xl p-6 space-y-5 flex flex-col">

            <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-deep/10 text-purple-deep flex items-center justify-center flex-shrink-0">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-black">Biodata User</h3>
                        <p class="text-xs font-medium text-slate-500" x-text="editBiodataMode ? 'Perbarui informasi biodata dan email Anda' : 'Ubah data melalui menu Settings di banner atas'"></p>
                    </div>
                </div>

                <span
                    class="px-3 py-1 rounded-full text-xs font-medium border"
                    :class="editBiodataMode ? 'bg-purple-50 text-purple-deep border-[#D9C2F0]' : 'bg-slate-50 text-slate-500 border-slate-200'"
                    x-text="editBiodataMode ? 'Mode Edit' : 'Mode Baca'"
                ></span>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" id="biodataForm" class="space-y-4 flex-1 flex flex-col justify-between">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    {{-- Grid 2 Kolom x 3 Baris: Identitas, Kontak, & Kepegawaian --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- 1. Nama Lengkap --}}
                        <div>
                            <label for="biodata_name" class="text-xs font-medium text-slate-500 mb-1.5 block">
                                Nama Lengkap <span x-show="editBiodataMode" x-cloak class="text-red-500">*</span>
                            </label>
                            <div x-show="!editBiodataMode" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                <span x-text="biodataForm.name || '-'"></span>
                            </div>
                            <input
                                x-show="editBiodataMode"
                                x-cloak
                                type="text"
                                id="biodata_name"
                                name="name"
                                x-model="biodataForm.name"
                                :disabled="!editBiodataMode"
                                required
                                placeholder="Masukkan nama lengkap"
                                class="w-full px-3.5 py-2.5 bg-white rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs @error('name') border-red-400 @enderror"
                            >
                            @error('name')
                                <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- 2. Alamat Email --}}
                        <div>
                            <label for="biodata_email" class="text-xs font-medium text-slate-500 mb-1.5 block">
                                Alamat Email <span x-show="editBiodataMode" x-cloak class="text-red-500">*</span>
                            </label>
                            <div x-show="!editBiodataMode" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                <span class="truncate" x-text="biodataForm.email || '-'"></span>
                            </div>
                            <input
                                x-show="editBiodataMode"
                                x-cloak
                                type="email"
                                id="biodata_email"
                                name="email"
                                x-model="biodataForm.email"
                                :disabled="!editBiodataMode"
                                required
                                placeholder="Masukkan alamat email"
                                class="w-full px-3.5 py-2.5 bg-white rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs @error('email') border-red-400 @enderror"
                            >
                            @error('email')
                                <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- 3. ID Pegawai / NIP --}}
                        <div>
                            <label for="biodata_employee_id" class="text-xs font-medium text-slate-500 mb-1.5 block">
                                ID Pegawai / NIP
                            </label>
                            <div x-show="!editBiodataMode" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                <span x-text="biodataForm.employee_id || '-'"></span>
                            </div>
                            <input
                                x-show="editBiodataMode"
                                x-cloak
                                type="text"
                                id="biodata_employee_id"
                                name="employee_id"
                                x-model="biodataForm.employee_id"
                                :disabled="!editBiodataMode"
                                placeholder="Contoh: UC-PSC-001"
                                class="w-full px-3.5 py-2.5 bg-white rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs @error('employee_id') border-red-400 @enderror"
                            >
                            @error('employee_id')
                                <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- 4. Jabatan / Divisi --}}
                        <div>
                            <label for="biodata_position" class="text-xs font-medium text-slate-500 mb-1.5 block">
                                Jabatan / Divisi
                            </label>
                            <div x-show="!editBiodataMode" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                <span x-text="biodataForm.position || '-'"></span>
                            </div>
                            <input
                                x-show="editBiodataMode"
                                x-cloak
                                type="text"
                                id="biodata_position"
                                name="position"
                                x-model="biodataForm.position"
                                :disabled="!editBiodataMode"
                                placeholder="Contoh: Staff Psikotes"
                                class="w-full px-3.5 py-2.5 bg-white rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs @error('position') border-red-400 @enderror"
                            >
                            @error('position')
                                <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- 5. No. Telepon / WhatsApp --}}
                        <div>
                            <label for="biodata_phone" class="text-xs font-medium text-slate-500 mb-1.5 block">
                                No. Telepon / WhatsApp
                            </label>
                            <div x-show="!editBiodataMode" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                <span x-text="biodataForm.phone || '-'"></span>
                            </div>
                            <input
                                x-show="editBiodataMode"
                                x-cloak
                                type="text"
                                id="biodata_phone"
                                name="phone"
                                x-model="biodataForm.phone"
                                :disabled="!editBiodataMode"
                                placeholder="Contoh: 081234567890"
                                class="w-full px-3.5 py-2.5 bg-white rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs @error('phone') border-red-400 @enderror"
                            >
                            @error('phone')
                                <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- 6. Jenis Kelamin --}}
                        <div>
                            <label for="biodata_gender" class="text-xs font-medium text-slate-500 mb-1.5 block">
                                Jenis Kelamin
                            </label>
                            <div x-show="!editBiodataMode" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                <span x-text="formatGenderDisplay(biodataForm.gender)"></span>
                            </div>
                            <select
                                x-show="editBiodataMode"
                                x-cloak
                                id="biodata_gender"
                                name="gender"
                                x-model="biodataForm.gender"
                                :disabled="!editBiodataMode"
                                class="w-full px-3.5 py-2.5 bg-white rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs @error('gender') border-red-400 @enderror"
                            >
                                <option value="">Pilih jenis kelamin</option>
                                <option value="l">Laki-laki</option>
                                <option value="p">Perempuan</option>
                            </select>
                            @error('gender')
                                <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Baris Bawah: Alamat Domisili --}}
                    <div>
                        <label for="biodata_address" class="text-xs font-medium text-slate-500 mb-1.5 block">
                            Alamat Domisili
                        </label>
                        <div x-show="!editBiodataMode" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[76px] flex items-start text-sm font-medium text-black whitespace-pre-line">
                            <span x-text="biodataForm.address || '-'"></span>
                        </div>
                        <textarea
                            x-show="editBiodataMode"
                            x-cloak
                            id="biodata_address"
                            name="address"
                            rows="3"
                            x-model="biodataForm.address"
                            :disabled="!editBiodataMode"
                            placeholder="Masukkan alamat domisili lengkap"
                            class="w-full px-3.5 py-2.5 bg-white rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[76px] shadow-xs resize-none @error('address') border-red-400 @enderror"
                        ></textarea>
                        @error('address')
                            <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Bar Tombol Simpan / Batal (Hanya Muncul di Mode Edit) --}}
                <div x-show="editBiodataMode" x-cloak class="flex flex-wrap items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button
                        type="button"
                        @click="handleCancelBiodataEdit()"
                        class="px-5 py-2.5 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-sm shadow-xs transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2.5 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-sm shadow-xs transition cursor-pointer inline-flex items-center gap-2"
                    >
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                            <polyline points="7 3 7 8 15 8"/>
                        </svg>
                        <span>Simpan Biodata</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- KARTU KANAN (5/12): RINGKASAN, KEANGGOTAAN & HAK AKSES --}}
        <div class="lg:col-span-5 glass-card rounded-3xl p-6 space-y-5 flex flex-col">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-purple-deep/10 text-purple-deep flex items-center justify-center flex-shrink-0">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-black">Ringkasan &amp; Hak Akses</h3>
                    <p class="text-xs font-medium text-slate-500">Status keanggotaan, keamanan, dan aktivitas</p>
                </div>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <span class="text-xs font-medium text-slate-500 mb-1.5 block">Peran Akses</span>
                        <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                            {{ $user->isAdmin() ? 'Administrator' : 'Staff Layanan' }}
                        </div>
                    </div>

                    <div>
                        <span class="text-xs font-medium text-slate-500 mb-1.5 block">Status Kata Sandi</span>
                        <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center justify-between text-sm font-medium text-black">
                            <span>••••••••</span>
                            <span class="text-xs font-medium text-slate-500">Aktif</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <span class="text-xs font-medium text-slate-500 mb-1.5 block">Klien Didaftarkan</span>
                        <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                            {{ $user->clients_count ?? 0 }} Klien
                        </div>
                    </div>

                    <div>
                        <span class="text-xs font-medium text-slate-500 mb-1.5 block">Klien Ditangani</span>
                        <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                            {{ $user->assigned_clients_count ?? 0 }} Klien Aktif
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <span class="text-xs font-medium text-slate-500 mb-1.5 block">Terdaftar Sejak</span>
                        <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                            {{ $joinedAtText }}
                        </div>
                    </div>

                    <div>
                        <span class="text-xs font-medium text-slate-500 mb-1.5 block">Pembaruan Terakhir</span>
                        <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                            {{ $updatedAtText }}
                        </div>
                    </div>
                </div>

                <div>
                    <span class="text-xs font-medium text-slate-500 mb-1.5 block">Cakupan Akses Sistem</span>
                    <div class="px-3.5 py-3 bg-slate-50 rounded-xl border border-slate-100 text-sm font-medium text-black leading-relaxed">
                        @if($user->isAdmin())
                            Memiliki akses penuh untuk mengelola kalender layanan, daftar klien, formulir klien, data psikolog, kelola penugasan, serta manajemen akun staff.
                        @else
                            Memiliki akses operasional untuk mengelola klien yang ditugaskan, catatan layanan, formulir pendaftaran, serta penjadwalan konseling dan psikotes.
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ==================================================================== --}}
    {{-- MODAL 1: UBAH / HAPUS FOTO PROFIL (STRICT EXPLICIT CLOSE ONLY)       --}}
    {{-- ==================================================================== --}}
    <div x-show="showAvatarModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="avatar-modal-title">
        <div class="fixed inset-0 bg-[#1F0E38]/50 backdrop-blur-md"></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-md rounded-3xl bg-white/95 backdrop-blur-xl border border-white/80 shadow-[0_30px_70px_rgba(74,35,128,0.2)] overflow-hidden">
                <div class="px-6 py-4 bg-purple-deep flex items-center justify-between text-white">
                    <h3 id="avatar-modal-title" class="text-base font-semibold">Ubah Foto Profil</h3>
                    <button type="button" @click="closeAvatarModal()" class="p-1 rounded-lg text-white/80 hover:text-white hover:bg-white/15 transition cursor-pointer" title="Tutup">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex items-center gap-4">
                        <template x-if="previewUrl">
                            <img :src="previewUrl" alt="Preview Foto" class="w-20 h-20 rounded-2xl object-cover border border-[#EDE1FA] shadow-xs flex-shrink-0">
                        </template>
                        <template x-if="!previewUrl">
                            @if($user->avatar_url)
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-20 h-20 rounded-2xl object-cover border border-[#EDE1FA] shadow-xs flex-shrink-0">
                            @else
                                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-purple-light to-purple-deep text-white font-semibold text-2xl flex items-center justify-center shadow-xs flex-shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            @endif
                        </template>
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-black">{{ $user->name }}</p>
                            <p class="text-xs font-medium text-slate-500">Format JPG, PNG, atau WEBP maksimal 2MB.</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('profile.avatar.update') }}" enctype="multipart/form-data" id="modalAvatarForm" data-no-unsaved-guard class="space-y-3">
                        @csrf
                        <label for="modal_avatar_input" class="block text-xs font-medium text-slate-500 mb-1">Pilih File Gambar</label>
                        <input
                            type="file"
                            id="modal_avatar_input"
                            name="avatar"
                            accept="image/jpeg,image/png,image/jpg,image/webp"
                            @change="
                                const file = $event.target.files[0];
                                if (file) {
                                    previewUrl = URL.createObjectURL(file);
                                    hasSelectedAvatarFile = true;
                                } else {
                                    previewUrl = null;
                                    hasSelectedAvatarFile = false;
                                }
                            "
                            class="w-full px-3.5 py-2 bg-white rounded-xl border border-[#D9C2F0] text-sm font-medium text-black file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-purple-deep/10 file:text-purple-deep hover:file:bg-purple-deep/20 cursor-pointer"
                        >
                        @error('avatar')
                            <p class="text-xs text-red-500 font-medium">{{ $message }}</p>
                        @enderror
                    </form>

                    @if($user->avatar)
                    <div x-show="confirmingDeleteAvatar" x-cloak class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                        <p class="text-sm font-medium text-black">Yakin ingin menghapus foto profil saat ini dan kembali ke inisial nama?</p>
                        <div class="flex items-center justify-end gap-2">
                            <button
                                type="button"
                                @click="confirmingDeleteAvatar = false"
                                class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs shadow-xs transition cursor-pointer"
                            >
                                Batal
                            </button>
                            <form method="POST" action="{{ route('profile.avatar.delete') }}" data-no-unsaved-guard>
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="px-4 py-2 rounded-xl bg-red-500 hover:bg-red-600 text-white font-semibold text-xs shadow-xs transition cursor-pointer inline-flex items-center gap-1.5"
                                >
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    <span>Ya, Hapus Foto</span>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        @if($user->avatar)
                        <button
                            type="button"
                            x-show="!confirmingDeleteAvatar"
                            @click="confirmingDeleteAvatar = true"
                            class="px-4 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-white font-semibold text-xs sm:text-sm shadow-xs transition cursor-pointer inline-flex items-center gap-1.5"
                        >
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            <span>Hapus Foto</span>
                        </button>
                        @endif
                    </div>

                    <div class="flex items-center gap-2.5 ml-auto">
                        <button
                            type="button"
                            @click="closeAvatarModal()"
                            class="px-5 py-2.5 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-sm shadow-xs transition cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            form="modalAvatarForm"
                            :disabled="!hasSelectedAvatarFile"
                            class="px-5 py-2.5 rounded-xl bg-purple-deep hover:bg-purple-deep/90 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold text-sm shadow-xs transition cursor-pointer inline-flex items-center gap-2"
                        >
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            <span>Simpan Foto</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================================================================== --}}
    {{-- MODAL 2: UBAH KATA SANDI (STRICT EXPLICIT CLOSE ONLY)                --}}
    {{-- ==================================================================== --}}
    <div x-show="showPasswordModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="password-modal-title">
        <div class="fixed inset-0 bg-[#1F0E38]/50 backdrop-blur-md"></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-lg rounded-3xl bg-white/95 backdrop-blur-xl border border-white/80 shadow-[0_30px_70px_rgba(74,35,128,0.2)] overflow-hidden">
                <div class="px-6 py-4 bg-purple-deep flex items-center justify-between text-white">
                    <h3 id="password-modal-title" class="text-base font-semibold">Ubah Kata Sandi</h3>
                    <button type="button" @click="handleClosePasswordModal()" class="p-1 rounded-lg text-white/80 hover:text-white hover:bg-white/15 transition cursor-pointer" title="Tutup">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('profile.password.update') }}" id="modalPasswordForm">
                    @csrf
                    @method('PUT')

                    <div class="p-6 space-y-4">
                        <div>
                            <label for="current_password" class="text-xs font-medium text-slate-500 mb-1 block">
                                Kata Sandi Saat Ini <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input
                                    :type="showCurrent ? 'text' : 'password'"
                                    id="current_password"
                                    name="current_password"
                                    x-model="passwordForm.current_password"
                                    :disabled="!showPasswordModal"
                                    required
                                    placeholder="Masukkan kata sandi saat ini"
                                    class="w-full px-3.5 py-2.5 pr-10 bg-white rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs @error('current_password') border-red-400 @enderror"
                                >
                                <button type="button" @click="showCurrent = !showCurrent" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-purple-deep p-1 transition cursor-pointer" title="Lihat/Sembunyikan Kata Sandi">
                                    <svg x-show="!showCurrent" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg x-show="showCurrent" x-cloak width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                </button>
                            </div>
                            @error('current_password')
                                <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="text-xs font-medium text-slate-500 mb-1 block">
                                Kata Sandi Baru <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input
                                    :type="showNew ? 'text' : 'password'"
                                    id="password"
                                    name="password"
                                    x-model="passwordForm.password"
                                    :disabled="!showPasswordModal"
                                    required
                                    placeholder="Minimal 6 karakter"
                                    class="w-full px-3.5 py-2.5 pr-10 bg-white rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs @error('password') border-red-400 @enderror"
                                >
                                <button type="button" @click="showNew = !showNew" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-purple-deep p-1 transition cursor-pointer" title="Lihat/Sembunyikan Kata Sandi">
                                    <svg x-show="!showNew" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg x-show="showNew" x-cloak width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="text-xs font-medium text-slate-500 mb-1 block">
                                Konfirmasi Kata Sandi Baru <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input
                                    :type="showConfirm ? 'text' : 'password'"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    x-model="passwordForm.password_confirmation"
                                    :disabled="!showPasswordModal"
                                    required
                                    placeholder="Ulangi kata sandi baru"
                                    class="w-full px-3.5 py-2.5 pr-10 bg-white rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs"
                                >
                                <button type="button" @click="showConfirm = !showConfirm" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-purple-deep p-1 transition cursor-pointer" title="Lihat/Sembunyikan Kata Sandi">
                                    <svg x-show="!showConfirm" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg x-show="showConfirm" x-cloak width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                </button>
                            </div>
                        </div>

                        {{-- Live Password Strength & Match Panel --}}
                        <div x-show="passwordForm.password.length > 0 || passwordForm.password_confirmation.length > 0" x-cloak
                             class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 space-y-2.5">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs font-medium text-slate-500">Kekuatan Kata Sandi</span>
                                <span class="text-xs font-medium" :class="getPasswordStrength().textClass" x-text="getPasswordStrength().label"></span>
                            </div>
                            <div class="grid grid-cols-4 gap-1.5">
                                <template x-for="idx in [1, 2, 3, 4]" :key="idx">
                                    <div class="h-1.5 rounded-full transition-colors duration-200"
                                         :class="idx <= getPasswordStrength().score ? getPasswordStrength().barClass : 'bg-slate-200'"></div>
                                </template>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs font-medium">
                                <span class="inline-flex items-center gap-1" :class="hasLengthReq() ? 'text-emerald-600' : 'text-slate-500'">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="20 6 9 17 4 12"/></svg>
                                    Min. 6 karakter
                                </span>
                                <span class="inline-flex items-center gap-1" :class="hasMixReq() ? 'text-emerald-600' : 'text-slate-500'">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="20 6 9 17 4 12"/></svg>
                                    Huruf &amp; angka/simbol
                                </span>
                                <span class="inline-flex items-center gap-1" :class="passwordsMatch() ? 'text-emerald-600' : (passwordForm.password_confirmation.length > 0 ? 'text-red-500' : 'text-slate-500')">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="20 6 9 17 4 12"/></svg>
                                    <span x-text="passwordsMatch() ? 'Konfirmasi cocok' : (passwordForm.password_confirmation.length > 0 ? 'Belum cocok' : 'Cocokkan konfirmasi')"></span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button
                            type="button"
                            @click="handleClosePasswordModal()"
                            class="px-5 py-2.5 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-sm shadow-xs transition cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="px-5 py-2.5 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-sm shadow-xs transition cursor-pointer inline-flex items-center gap-2"
                        >
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            <span>Perbarui Kata Sandi</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ==================================================================== --}}
    {{-- MODAL 3: KONFIRMASI PERUBAHAN BELUM DISIMPAN (UNSAVED CHANGES GUARD) --}}
    {{-- ==================================================================== --}}
    <div x-show="showProfileUnsavedModal" x-cloak class="fixed inset-0 z-[60] overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="profile-unsaved-title">
        <div class="fixed inset-0 bg-[#1F0E38]/50 backdrop-blur-md"></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-md rounded-3xl bg-white/95 backdrop-blur-xl border border-white/80 shadow-[0_30px_70px_rgba(74,35,128,0.2)] overflow-hidden">
                <div class="px-6 py-4 bg-purple-deep flex items-center justify-between text-white">
                    <h3 id="profile-unsaved-title" class="text-base font-semibold">Perubahan Belum Disimpan</h3>
                    <button type="button" @click="showProfileUnsavedModal = false; pendingAction = null;" class="p-1 rounded-lg text-white/80 hover:text-white hover:bg-white/15 transition cursor-pointer" title="Tutup">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                <div class="p-6">
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                        <p class="text-sm font-medium text-black leading-relaxed">
                            Anda memiliki perubahan yang belum disimpan. Jika melanjutkan, perubahan pada formulir akan dibatalkan.
                        </p>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button
                        type="button"
                        @click="showProfileUnsavedModal = false; pendingAction = null;"
                        class="px-5 py-2.5 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-sm shadow-xs transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="confirmDiscardUnsaved()"
                        class="px-5 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-white font-semibold text-sm shadow-xs transition cursor-pointer"
                    >
                        Ya, Tinggalkan
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
