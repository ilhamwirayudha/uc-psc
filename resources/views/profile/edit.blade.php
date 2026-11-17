@extends('layouts.dashboard')

@section('title', 'Profil Saya — UC PSC')
@section('page-title', 'Profil Saya')
@section('page-subtitle', 'Kelola biodata akun, pengaturan keamanan, dan log aktivitas Anda')

@section('content')
@php
    $hasAnyError = $errors->hasAny([
        'avatar',
        'hero_banner',
        'name',
        'email',
        'employee_id',
        'position',
        'phone',
        'gender',
    ]);
    $hasPasswordError = $errors->hasAny(['current_password', 'password']);
    $requestedTab = request('tab');
    $initialTab = $hasPasswordError
        ? 'pengaturan'
        : ($hasAnyError
            ? 'profil'
            : (in_array($requestedTab, ['profil', 'pengaturan', 'log-aktivitas'], true) ? $requestedTab : 'profil'));
@endphp

<script>
function userProfileHero() {
    return {
        activeTab: @js($initialTab),
        pendingTabTarget: null,

        editMode: @js($hasAnyError),
        passwordEditMode: @js($hasPasswordError),
        showProfileUnsavedModal: false,

        hasSavedAvatar: @js((bool) $user->avatar),
        hasSavedBanner: @js((bool) $user->hero_banner),
        removeAvatar: @js(old('remove_avatar') === '1'),
        removeHeroBanner: @js(old('remove_hero_banner') === '1'),

        previewUrl: null,
        hasSelectedAvatarFile: false,
        previewBannerUrl: null,
        hasSelectedBannerFile: false,

        initialBiodata: {
            name: @js($user->name ?? ''),
            email: @js($user->email ?? ''),
            employee_id: @js($user->employee_id ?? ''),
            position: @js($user->position ?? ''),
            phone: @js($user->phone ?? ''),
            gender: @js($user->gender ?? ''),
        },
        biodataForm: {
            name: @js(old('name', $user->name ?? '')),
            email: @js(old('email', $user->email ?? '')),
            employee_id: @js(old('employee_id', $user->employee_id ?? '')),
            position: @js(old('position', $user->position ?? '')),
            phone: @js(old('phone', $user->phone ?? '')),
            gender: @js(old('gender', $user->gender ?? '')),
        },

        passwordForm: {
            current_password: '',
            password: '',
            password_confirmation: '',
        },
        showCurrentPassword: false,
        showNewPassword: false,
        showConfirmPassword: false,

        activityFilter: 'semua',

        isFormDirty() {
            if (!this.editMode) return false;
            const biodataDirty = JSON.stringify(this.biodataForm) !== JSON.stringify(this.initialBiodata);
            return this.hasSelectedAvatarFile
                || this.hasSelectedBannerFile
                || this.removeAvatar
                || this.removeHeroBanner
                || biodataDirty;
        },

        isPasswordDirty() {
            if (!this.passwordEditMode) return false;
            return (this.passwordForm.current_password || '').length > 0
                || (this.passwordForm.password || '').length > 0
                || (this.passwordForm.password_confirmation || '').length > 0;
        },

        hasAnyUnsavedChanges() {
            if (this.activeTab === 'profil') {
                return this.isFormDirty();
            }
            if (this.activeTab === 'pengaturan') {
                return this.isPasswordDirty();
            }
            return false;
        },

        switchTab(targetTab) {
            if (this.activeTab === targetTab) return;

            if (this.hasAnyUnsavedChanges()) {
                this.pendingTabTarget = targetTab;
                this.showProfileUnsavedModal = true;
                return;
            }

            if (this.editMode) {
                this.resetToViewMode();
            }
            if (this.passwordEditMode) {
                this.resetPasswordForm();
            }

            this.applyTabChange(targetTab);
        },

        applyTabChange(targetTab) {
            this.activeTab = targetTab;
            const url = new URL(window.location.href);
            if (targetTab === 'profil') {
                url.searchParams.delete('tab');
            } else {
                url.searchParams.set('tab', targetTab);
            }
            window.history.replaceState({}, '', url.toString());
            window.dispatchEvent(new CustomEvent('profile-tab-changed', { detail: { tab: targetTab } }));
        },

        markRemoveAvatar() {
            this.previewUrl = null;
            this.hasSelectedAvatarFile = false;
            const fileEl = document.getElementById('inline_avatar_input');
            if (fileEl) fileEl.value = '';
            this.removeAvatar = this.hasSavedAvatar;
        },

        markRemoveBanner() {
            this.previewBannerUrl = null;
            this.hasSelectedBannerFile = false;
            const bannerEl = document.getElementById('inline_banner_input');
            if (bannerEl) bannerEl.value = '';
            this.removeHeroBanner = this.hasSavedBanner;
        },

        enterEditMode() {
            this.biodataForm = JSON.parse(JSON.stringify(this.initialBiodata));
            this.editMode = true;
            this.$nextTick(() => {
                const input = document.getElementById('biodata_name');
                if (input) input.focus({ preventScroll: false });
            });
        },

        handleCancelEdit() {
            if (this.isFormDirty()) {
                this.pendingTabTarget = null;
                this.showProfileUnsavedModal = true;
                return;
            }
            this.resetToViewMode();
        },

        enterPasswordEditMode() {
            this.passwordForm = {
                current_password: '',
                password: '',
                password_confirmation: '',
            };
            this.passwordEditMode = true;
            this.$nextTick(() => {
                const input = document.getElementById('current_password');
                if (input) input.focus({ preventScroll: false });
            });
        },

        handleCancelPassword() {
            if (this.isPasswordDirty()) {
                this.pendingTabTarget = null;
                this.showProfileUnsavedModal = true;
                return;
            }
            this.resetPasswordForm();
        },

        cancelUnsavedModal() {
            this.showProfileUnsavedModal = false;
            this.pendingTabTarget = null;
        },

        resetToViewMode() {
            this.editMode = false;
            this.removeAvatar = false;
            this.removeHeroBanner = false;
            this.previewUrl = null;
            this.hasSelectedAvatarFile = false;
            this.previewBannerUrl = null;
            this.hasSelectedBannerFile = false;
            const fileEl = document.getElementById('inline_avatar_input');
            if (fileEl) fileEl.value = '';
            const bannerEl = document.getElementById('inline_banner_input');
            if (bannerEl) bannerEl.value = '';

            this.biodataForm = JSON.parse(JSON.stringify(this.initialBiodata));
        },

        resetPasswordForm() {
            this.passwordEditMode = false;
            this.passwordForm = {
                current_password: '',
                password: '',
                password_confirmation: '',
            };
            this.showCurrentPassword = false;
            this.showNewPassword = false;
            this.showConfirmPassword = false;
        },

        confirmDiscardUnsaved() {
            this.showProfileUnsavedModal = false;
            this.resetToViewMode();
            this.resetPasswordForm();

            if (this.pendingTabTarget) {
                const nextTab = this.pendingTabTarget;
                this.pendingTabTarget = null;
                this.applyTabChange(nextTab);
            }
        },

        passwordStrengthLevel() {
            const pwd = this.passwordForm.password || '';
            if (!pwd) return 0;
            let score = 0;
            if (pwd.length >= 6) score++;
            if (pwd.length >= 10) score++;
            if (/[A-Z]/.test(pwd) && /[a-z]/.test(pwd)) score++;
            if (/[0-9]/.test(pwd) || /[^A-Za-z0-9]/.test(pwd)) score++;
            return Math.min(score, 4);
        },

        passwordStrengthLabel() {
            const lvl = this.passwordStrengthLevel();
            if (lvl === 0) return '';
            if (lvl === 1) return 'Lemah (minimal 6 karakter)';
            if (lvl === 2) return 'Cukup';
            if (lvl === 3) return 'Kuat';
            return 'Sangat Kuat';
        },

        requestLogout() {
            window.dispatchEvent(new CustomEvent('open-logout-modal'));
        },

        formatGenderDisplay(code) {
            if (code === 'l') return 'Laki-laki';
            if (code === 'p') return 'Perempuan';
            return '-';
        }
    };
}
</script>

<div
    x-data="userProfileHero()"
    @request-profile-tab.window="switchTab($event.detail.tab)"
    class="max-w-5xl mx-auto space-y-5"
>

    {{-- Form Terpusat untuk Profil (Hero Banner, Foto Profil, & Biodata User) --}}
    <form
        method="POST"
        action="{{ route('profile.update') }}"
        enctype="multipart/form-data"
        id="unifiedProfileForm"
        @submit="
            const avEl = document.getElementById('inline_avatar_input');
            const bnEl = document.getElementById('inline_banner_input');
            if (hasSelectedAvatarFile && (!avEl || !avEl.files || avEl.files.length === 0)) {
                $event.preventDefault();
                alert('File foto profil belum terbaca oleh browser. Silakan pilih ulang foto profil Anda.');
                return;
            }
            if (hasSelectedBannerFile && (!bnEl || !bnEl.files || bnEl.files.length === 0)) {
                $event.preventDefault();
                alert('File background hero belum terbaca oleh browser. Silakan pilih ulang gambar Anda.');
                return;
            }
        "
    >
        @csrf
        @method('PUT')
        <input type="hidden" name="remove_avatar" :value="removeAvatar ? '1' : '0'">
        <input type="hidden" name="remove_hero_banner" :value="removeHeroBanner ? '1' : '0'">

        <input
            type="file"
            id="inline_avatar_input"
            name="avatar"
            accept="image/jpeg,image/png,image/jpg,image/webp"
            class="hidden"
            @change="
                const file = $event.target.files[0];
                if (file) {
                    previewUrl = URL.createObjectURL(file);
                    hasSelectedAvatarFile = true;
                    removeAvatar = false;
                } else {
                    previewUrl = null;
                    hasSelectedAvatarFile = false;
                }
            "
        >
        <input
            type="file"
            id="inline_banner_input"
            name="hero_banner"
            accept="image/jpeg,image/png,image/jpg,image/webp"
            class="hidden"
            @change="
                const file = $event.target.files[0];
                if (file) {
                    previewBannerUrl = URL.createObjectURL(file);
                    hasSelectedBannerFile = true;
                    removeHeroBanner = false;
                } else {
                    previewBannerUrl = null;
                    hasSelectedBannerFile = false;
                }
            "
        >

        {{-- ==================================================================== --}}
        {{-- HERO CARD                                                            --}}
        {{-- ==================================================================== --}}
        <div class="glass-card rounded-2xl relative">

            {{-- Decorative / Custom Hero Cover Banner + Edit Overlay --}}
            <div class="h-24 sm:h-28 rounded-t-2xl relative overflow-hidden bg-gradient-to-r from-[#4B267D] via-[#6B3FA0] to-[#E67E22]/85 group/banner">
                <template x-if="previewBannerUrl">
                    <img :src="previewBannerUrl" alt="Preview Background Hero" class="w-full h-full object-cover">
                </template>
                <template x-if="!previewBannerUrl && !removeHeroBanner && hasSavedBanner">
                    <img src="{{ $user->hero_banner_url }}" alt="Background Hero" class="w-full h-full object-cover absolute inset-0">
                </template>
                <template x-if="!previewBannerUrl && (removeHeroBanner || !hasSavedBanner)">
                    <div class="w-full h-full">
                        <div class="absolute -left-10 -top-12 w-56 h-56 rounded-full bg-white/15 blur-2xl pointer-events-none"></div>
                        <div class="absolute right-8 -bottom-16 w-64 h-64 rounded-full bg-[#FDBA74]/25 blur-2xl pointer-events-none"></div>
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.2),transparent_55%)] pointer-events-none"></div>
                    </div>
                </template>

                {{-- Overlay Klik Ganti Background Hero Saat Edit Mode Aktif --}}
                <label
                    x-show="editMode && activeTab === 'profil'"
                    x-cloak
                    for="inline_banner_input"
                    class="absolute inset-0 z-10 flex items-center justify-center bg-black/25 hover:bg-black/55 cursor-pointer transition-all duration-200 select-none"
                    title="Klik untuk mengganti background hero"
                >
                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-black/45 group-hover/banner:bg-black/70 text-white text-xs font-medium backdrop-blur-xs border border-white/25 shadow-sm transition">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                        <span>Ganti Background</span>
                    </span>
                </label>

                <button
                    type="button"
                    x-show="editMode && activeTab === 'profil' && (hasSelectedBannerFile || (hasSavedBanner && !removeHeroBanner))"
                    x-cloak
                    @click.prevent.stop="markRemoveBanner()"
                    title="Hapus Background Hero"
                    class="absolute top-3 right-3 z-20 px-2.5 py-1.5 rounded-lg bg-red-500/90 hover:bg-red-600 text-white text-xs font-semibold shadow-sm inline-flex items-center gap-1 transition cursor-pointer"
                >
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    <span>Hapus Background</span>
                </button>
            </div>

            {{-- Identity Row --}}
            <div class="px-6 sm:px-8 py-5 rounded-b-2xl relative space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center gap-3.5 sm:gap-5 relative z-20">
                    <div class="relative flex-shrink-0 -mt-14 sm:-mt-12">
                        <div class="relative w-20 h-20 sm:w-24 sm:h-24 rounded-xl overflow-hidden border-4 border-white shadow-md bg-white group/avatar">
                            <template x-if="previewUrl">
                                <img :src="previewUrl" alt="Preview Foto Profil" class="w-full h-full object-cover object-top">
                            </template>
                            <template x-if="!previewUrl && !removeAvatar && hasSavedAvatar">
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover object-top">
                            </template>
                            <template x-if="!previewUrl && (removeAvatar || !hasSavedAvatar)">
                                <div class="w-full h-full bg-gradient-to-br from-purple-light to-purple-deep text-white font-semibold text-2xl sm:text-3xl flex items-center justify-center select-none">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            </template>

                            {{-- Overlay Klik Ganti Foto Profil Saat Edit Mode Aktif --}}
                            <label
                                x-show="editMode && activeTab === 'profil'"
                                x-cloak
                                for="inline_avatar_input"
                                class="absolute inset-0 z-10 flex flex-col items-center justify-center gap-1 bg-black/30 hover:bg-black/60 text-white cursor-pointer transition-all duration-200 select-none"
                                title="Klik untuk mengganti foto profil"
                            >
                                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                                <span class="text-[11px] font-medium leading-none">Ganti Foto</span>
                            </label>
                        </div>

                        <button
                            type="button"
                            x-show="editMode && activeTab === 'profil' && (hasSelectedAvatarFile || (hasSavedAvatar && !removeAvatar))"
                            x-cloak
                            @click.prevent.stop="markRemoveAvatar()"
                            title="Hapus Foto Profil"
                            class="absolute -top-1.5 -right-1.5 z-30 w-6 h-6 rounded-full bg-red-500 hover:bg-red-600 text-white border-2 border-white shadow-md flex items-center justify-center transition cursor-pointer"
                        >
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </div>

                    <div class="flex flex-col items-start gap-1">
                        <h2 class="text-lg sm:text-xl font-semibold text-black leading-tight">{{ $user->name }}</h2>
                        <p class="text-sm font-medium text-slate-500">{{ $user->isAdmin() ? 'Administrator' : 'Staff Layanan' }}</p>
                    </div>
                </div>

                @error('avatar')
                    <p class="text-xs text-red-500 font-medium">{{ $message }}</p>
                @enderror
                @error('hero_banner')
                    <p class="text-xs text-red-500 font-medium">{{ $message }}</p>
                @enderror
            </div>

        </div>
    </form>

    {{-- ==================================================================== --}}
    {{-- KARTU TERPADU DENGAN SOFT HEADER BAR + SUBCARD TERBINGKAI            --}}
    {{-- ==================================================================== --}}
    <div class="glass-card rounded-2xl overflow-hidden">

        {{-- Soft Header Bar (Tab Navigasi + Tombol Aksi) --}}
        <div class="px-5 sm:px-7 py-3 bg-slate-50/80 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden select-none">
                <button
                    type="button"
                    @click="switchTab('profil')"
                    class="px-4 py-2 rounded-lg text-xs sm:text-sm transition-all flex items-center gap-2 cursor-pointer whitespace-nowrap"
                    :class="activeTab === 'profil'
                        ? 'bg-purple-deep text-white border border-purple-deep shadow-xs font-semibold'
                        : 'text-slate-500 hover:text-purple-deep hover:bg-white/70 border border-transparent font-medium'"
                >
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <span>Profil</span>
                </button>

                <button
                    type="button"
                    @click="switchTab('log-aktivitas')"
                    class="px-4 py-2 rounded-lg text-xs sm:text-sm transition-all flex items-center gap-2 cursor-pointer whitespace-nowrap"
                    :class="activeTab === 'log-aktivitas'
                        ? 'bg-purple-deep text-white border border-purple-deep shadow-xs font-semibold'
                        : 'text-slate-500 hover:text-purple-deep hover:bg-white/70 border border-transparent font-medium'"
                >
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <span>Log Aktivitas</span>
                </button>

                <button
                    type="button"
                    @click="switchTab('pengaturan')"
                    class="px-4 py-2 rounded-lg text-xs sm:text-sm transition-all flex items-center gap-2 cursor-pointer whitespace-nowrap"
                    :class="activeTab === 'pengaturan'
                        ? 'bg-purple-deep text-white border border-purple-deep shadow-xs font-semibold'
                        : 'text-slate-500 hover:text-purple-deep hover:bg-white/70 border border-transparent font-medium'"
                >
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                    <span>Pengaturan</span>
                </button>
            </div>

            {{-- Toolbar Aksi Profil --}}
            <div x-show="activeTab === 'profil'" class="flex items-center gap-2.5 ml-auto">
                <button
                    x-show="!editMode"
                    type="button"
                    @click="enterEditMode()"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white border border-[#D9C2F0] text-black hover:text-purple-deep hover:bg-purple-50/70 text-xs sm:text-sm font-semibold shadow-xs transition cursor-pointer"
                >
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    <span>Edit</span>
                </button>

                <button
                    x-show="editMode"
                    x-cloak
                    type="button"
                    @click="handleCancelEdit()"
                    class="px-4 py-2 rounded-lg bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs sm:text-sm shadow-xs transition cursor-pointer"
                >
                    Batal
                </button>

                <button
                    x-show="editMode"
                    x-cloak
                    type="submit"
                    form="unifiedProfileForm"
                    class="px-4 py-2 rounded-lg bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs sm:text-sm shadow-xs transition cursor-pointer inline-flex items-center gap-2"
                >
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/>
                        <polyline points="7 3 7 8 15 8"/>
                    </svg>
                    <span>Simpan</span>
                </button>
            </div>
        </div>

        {{-- Body Container --}}
        <div class="p-6 sm:p-7 bg-white">

            {{-- ==================================================================== --}}
            {{-- ISI TAB 1: PROFIL (2 SUBCARD TERBINGKAI + IKON HALUS)                --}}
            {{-- ==================================================================== --}}
            <div x-show="activeTab === 'profil'" x-cloak id="biodataCard" class="grid grid-cols-1 lg:grid-cols-2 gap-5 items-stretch">

                {{-- Subcard 1: Identitas & Kontak --}}
                <div class="rounded-xl border border-slate-100 p-5 sm:p-6 space-y-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                        <svg class="w-4 h-4 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <h3 class="text-sm font-semibold text-black">Identitas &amp; Kontak</h3>
                    </div>

                    <div class="space-y-4">
                        {{-- Nama Lengkap --}}
                        <div>
                            <label for="biodata_name" class="text-xs font-medium text-slate-500 mb-1.5 block">
                                Nama Lengkap <span x-show="editMode" x-cloak class="text-red-500">*</span>
                            </label>
                            <div x-show="!editMode" class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                <span class="truncate" x-text="biodataForm.name || '-'"></span>
                            </div>
                            <input
                                x-show="editMode"
                                x-cloak
                                type="text"
                                form="unifiedProfileForm"
                                id="biodata_name"
                                name="name"
                                x-model="biodataForm.name"
                                :disabled="!editMode"
                                required
                                placeholder="Masukkan nama lengkap"
                                class="w-full px-3.5 py-2.5 bg-white rounded-lg border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs @error('name') border-red-400 @enderror"
                            >
                            @error('name')
                                <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Jenis Kelamin --}}
                        <div>
                            <label for="biodata_gender" class="text-xs font-medium text-slate-500 mb-1.5 block">
                                Jenis Kelamin
                            </label>
                            <div x-show="!editMode" class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                <span x-text="formatGenderDisplay(biodataForm.gender)"></span>
                            </div>
                            <select
                                x-show="editMode"
                                x-cloak
                                form="unifiedProfileForm"
                                id="biodata_gender"
                                name="gender"
                                x-model="biodataForm.gender"
                                :disabled="!editMode"
                                class="w-full px-3.5 py-2.5 bg-white rounded-lg border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs @error('gender') border-red-400 @enderror"
                            >
                                <option value="">Pilih jenis kelamin</option>
                                <option value="l">Laki-laki</option>
                                <option value="p">Perempuan</option>
                            </select>
                            @error('gender')
                                <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="biodata_email" class="text-xs font-medium text-slate-500 mb-1.5 block">
                                Email <span x-show="editMode" x-cloak class="text-red-500">*</span>
                            </label>
                            <div x-show="!editMode" class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                <span class="truncate" x-text="biodataForm.email || '-'"></span>
                            </div>
                            <input
                                x-show="editMode"
                                x-cloak
                                type="email"
                                form="unifiedProfileForm"
                                id="biodata_email"
                                name="email"
                                x-model="biodataForm.email"
                                :disabled="!editMode"
                                required
                                placeholder="Masukkan email"
                                class="w-full px-3.5 py-2.5 bg-white rounded-lg border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs @error('email') border-red-400 @enderror"
                            >
                            @error('email')
                                <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- No. Telepon / WhatsApp --}}
                        <div>
                            <label for="biodata_phone" class="text-xs font-medium text-slate-500 mb-1.5 block">
                                No. Telepon / WhatsApp
                            </label>
                            <div x-show="!editMode" class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                <span x-text="biodataForm.phone || '-'"></span>
                            </div>
                            <input
                                x-show="editMode"
                                x-cloak
                                type="text"
                                form="unifiedProfileForm"
                                id="biodata_phone"
                                name="phone"
                                x-model="biodataForm.phone"
                                :disabled="!editMode"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                title="Hanya boleh diisi angka (0-9)"
                                @input="biodataForm.phone = $el.value.replace(/[^0-9]/g, ''); $el.value = biodataForm.phone"
                                @keydown="if (!/[0-9]/.test($event.key) && !['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab', 'Home', 'End', 'Enter'].includes($event.key) && !$event.ctrlKey && !$event.metaKey) $event.preventDefault()"
                                placeholder="Contoh: 081234567890"
                                class="w-full px-3.5 py-2.5 bg-white rounded-lg border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs @error('phone') border-red-400 @enderror"
                            >
                            @error('phone')
                                <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Subcard 2: Kepegawaian & Akses --}}
                <div class="rounded-xl border border-slate-100 p-5 sm:p-6 space-y-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                        <svg class="w-4 h-4 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                        <h3 class="text-sm font-semibold text-black">Kepegawaian &amp; Akses</h3>
                    </div>

                    <div class="space-y-4">
                        {{-- NIP --}}
                        <div>
                            <label for="biodata_employee_id" class="text-xs font-medium text-slate-500 mb-1.5 block">
                                NIP
                            </label>
                            <div x-show="!editMode" class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                <span class="truncate" x-text="biodataForm.employee_id || '-'"></span>
                            </div>
                            <input
                                x-show="editMode"
                                x-cloak
                                type="text"
                                form="unifiedProfileForm"
                                id="biodata_employee_id"
                                name="employee_id"
                                x-model="biodataForm.employee_id"
                                :disabled="!editMode"
                                placeholder="Contoh: UC-PSC-001"
                                class="w-full px-3.5 py-2.5 bg-white rounded-lg border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs @error('employee_id') border-red-400 @enderror"
                            >
                            @error('employee_id')
                                <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Jabatan --}}
                        <div>
                            <label for="biodata_position" class="text-xs font-medium text-slate-500 mb-1.5 block">
                                Jabatan
                            </label>
                            <div x-show="!editMode" class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                <span class="truncate" x-text="biodataForm.position || '-'"></span>
                            </div>
                            <input
                                x-show="editMode"
                                x-cloak
                                type="text"
                                form="unifiedProfileForm"
                                id="biodata_position"
                                name="position"
                                x-model="biodataForm.position"
                                :disabled="!editMode"
                                placeholder="Contoh: Staff Psikotes"
                                class="w-full px-3.5 py-2.5 bg-white rounded-lg border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs @error('position') border-red-400 @enderror"
                            >
                            @error('position')
                                <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- User Role --}}
                        <div>
                            <span class="text-xs font-medium text-slate-500 mb-1.5 block">
                                User Role
                            </span>
                            <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                <span>{{ $user->isAdmin() ? 'Administrator' : 'Staff Layanan' }}</span>
                            </div>
                        </div>

                        {{-- Terdaftar Sejak --}}
                        <div>
                            <span class="text-xs font-medium text-slate-500 mb-1.5 block">
                                Terdaftar Sejak
                            </span>
                            <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                <span class="truncate">{{ $joinedAtText }}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ==================================================================== --}}
            {{-- ISI TAB 2: LOG AKTIVITAS (SUBCARD TERBINGKAI)                        --}}
            {{-- ==================================================================== --}}
            <div x-show="activeTab === 'log-aktivitas'" x-cloak class="rounded-xl border border-slate-100 p-5 sm:p-6 space-y-4">

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-100">
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach ([
                            'semua' => 'Semua',
                            'akun' => 'Akun',
                            'klien' => 'Klien',
                            'penugasan' => 'Penugasan',
                            'layanan' => 'Layanan',
                        ] as $filterKey => $filterLabel)
                            <button
                                type="button"
                                @click="activityFilter = '{{ $filterKey }}'"
                                class="px-3.5 py-1.5 rounded-lg text-xs font-medium transition cursor-pointer"
                                :class="activityFilter === '{{ $filterKey }}'
                                    ? 'bg-purple-deep text-white shadow-xs'
                                    : 'bg-white border border-[#D9C2F0] text-black hover:text-purple-deep hover:bg-purple-50/70'"
                            >
                                {{ $filterLabel }}
                            </button>
                        @endforeach
                    </div>

                    <span class="text-xs font-medium text-slate-500">
                        Total: {{ $activityLogs->count() }} aktivitas
                    </span>
                </div>

                <div class="space-y-3 max-h-[480px] overflow-y-auto pr-1 custom-scrollbar">
                    @forelse ($activityLogs as $log)
                        <div
                            x-show="activityFilter === 'semua' || activityFilter === @js($log['category'])"
                            class="p-4 rounded-lg bg-slate-50 border border-slate-100 space-y-1.5"
                        >
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                <p class="text-sm font-medium text-black">{{ $log['title'] }}</p>
                                <span class="text-xs font-medium text-slate-500 whitespace-nowrap">{{ $log['timestamp_text'] }}</span>
                            </div>
                            <p class="text-sm font-medium text-black">{{ $log['description'] }}</p>
                            <div class="flex flex-wrap items-center gap-2 text-xs font-medium text-slate-500 pt-0.5">
                                <span>Kategori: {{ $log['category_label'] }}</span>
                                <span>•</span>
                                <span>{{ $log['meta'] }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 rounded-lg bg-slate-50 border border-slate-100 text-center">
                            <p class="text-sm font-medium text-black">Belum ada log aktivitas yang tercatat untuk akun Anda.</p>
                        </div>
                    @endforelse

                    @if ($activityLogs->isNotEmpty())
                        @php
                            $availableCategories = $activityLogs->pluck('category')->unique()->values()->all();
                        @endphp
                        <div
                            x-show="activityFilter !== 'semua' && !@js($availableCategories).includes(activityFilter)"
                            x-cloak
                            class="p-6 rounded-lg bg-slate-50 border border-slate-100 text-center"
                        >
                            <p class="text-sm font-medium text-black">Tidak ada aktivitas pada kategori ini.</p>
                        </div>
                    @endif
                </div>

            </div>

            {{-- ==================================================================== --}}
            {{-- ISI TAB 3: PENGATURAN (SECURITY & SESSION DASHBOARD EQUAL-HEIGHT)     --}}
            {{-- ==================================================================== --}}
            <div x-show="activeTab === 'pengaturan'" x-cloak class="grid grid-cols-1 lg:grid-cols-2 gap-5 items-stretch">

                {{-- Subcard 1: Ubah Kata Sandi (Mode View Terkunci vs Mode Edit) --}}
                <div class="rounded-xl border border-slate-100 p-5 sm:p-6 flex flex-col">
                    <div class="flex items-center gap-2 pb-3 mb-4 border-b border-slate-100">
                        <svg class="w-4 h-4 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <h3 class="text-sm font-semibold text-black">Ubah Kata Sandi</h3>
                    </div>

                    <form method="POST" action="{{ route('profile.password.update') }}" id="passwordUpdateForm" class="flex-1 flex flex-col justify-between gap-5">
                        @csrf
                        @method('PUT')

                        <div class="space-y-4">
                            {{-- Mode View (Terkunci — Tidak Bisa Langsung Mengetik) --}}
                            <div x-show="!passwordEditMode" class="space-y-4">
                                <div>
                                    <span class="text-xs font-medium text-slate-500 mb-1.5 block">Kata Sandi Akun</span>
                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                        <span>••••••••••••</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Mode Edit (Aktif Hanya Setelah Klik Tombol Ubah Kata Sandi) --}}
                            <div x-show="passwordEditMode" x-cloak class="space-y-4">
                                {{-- Kata Sandi Saat Ini --}}
                                <div>
                                    <label for="current_password" class="text-xs font-medium text-slate-500 mb-1.5 block">
                                        Kata Sandi Saat Ini <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input
                                            :type="showCurrentPassword ? 'text' : 'password'"
                                            id="current_password"
                                            name="current_password"
                                            x-model="passwordForm.current_password"
                                            :disabled="!passwordEditMode"
                                            autocomplete="current-password"
                                            required
                                            placeholder="Masukkan kata sandi saat ini"
                                            class="w-full px-3.5 py-2.5 pr-11 bg-white rounded-lg border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs @error('current_password') border-red-400 @enderror"
                                        >
                                        <button
                                            type="button"
                                            @click="showCurrentPassword = !showCurrentPassword"
                                            class="absolute inset-y-0 right-0 px-3.5 flex items-center text-slate-400 hover:text-purple-deep transition cursor-pointer"
                                            title="Tampilkan / sembunyikan kata sandi"
                                        >
                                            <svg x-show="!showCurrentPassword" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                            <svg x-show="showCurrentPassword" x-cloak width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                        </button>
                                    </div>
                                    @error('current_password')
                                        <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Kata Sandi Baru --}}
                                <div>
                                    <label for="new_password" class="text-xs font-medium text-slate-500 mb-1.5 block">
                                        Kata Sandi Baru <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input
                                            :type="showNewPassword ? 'text' : 'password'"
                                            id="new_password"
                                            name="password"
                                            x-model="passwordForm.password"
                                            :disabled="!passwordEditMode"
                                            autocomplete="new-password"
                                            required
                                            minlength="6"
                                            placeholder="Minimal 6 karakter"
                                            class="w-full px-3.5 py-2.5 pr-11 bg-white rounded-lg border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs @error('password') border-red-400 @enderror"
                                        >
                                        <button
                                            type="button"
                                            @click="showNewPassword = !showNewPassword"
                                            class="absolute inset-y-0 right-0 px-3.5 flex items-center text-slate-400 hover:text-purple-deep transition cursor-pointer"
                                            title="Tampilkan / sembunyikan kata sandi"
                                        >
                                            <svg x-show="!showNewPassword" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                            <svg x-show="showNewPassword" x-cloak width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                        </button>
                                    </div>
                                    <div x-show="passwordForm.password.length > 0" x-cloak class="mt-2 space-y-1">
                                        <div class="grid grid-cols-4 gap-1.5">
                                            <div class="h-1.5 rounded-full transition-colors" :class="passwordStrengthLevel() >= 1 ? (passwordStrengthLevel() === 1 ? 'bg-red-400' : (passwordStrengthLevel() === 2 ? 'bg-amber-400' : 'bg-purple-deep')) : 'bg-slate-200'"></div>
                                            <div class="h-1.5 rounded-full transition-colors" :class="passwordStrengthLevel() >= 2 ? (passwordStrengthLevel() === 2 ? 'bg-amber-400' : 'bg-purple-deep') : 'bg-slate-200'"></div>
                                            <div class="h-1.5 rounded-full transition-colors" :class="passwordStrengthLevel() >= 3 ? 'bg-purple-deep' : 'bg-slate-200'"></div>
                                            <div class="h-1.5 rounded-full transition-colors" :class="passwordStrengthLevel() >= 4 ? 'bg-purple-deep' : 'bg-slate-200'"></div>
                                        </div>
                                        <p class="text-xs font-medium text-slate-500">Kekuatan sandi: <span class="text-black" x-text="passwordStrengthLabel()"></span></p>
                                    </div>
                                    @error('password')
                                        <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Konfirmasi Kata Sandi Baru --}}
                                <div>
                                    <label for="new_password_confirmation" class="text-xs font-medium text-slate-500 mb-1.5 block">
                                        Konfirmasi Kata Sandi Baru <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input
                                            :type="showConfirmPassword ? 'text' : 'password'"
                                            id="new_password_confirmation"
                                            name="password_confirmation"
                                            x-model="passwordForm.password_confirmation"
                                            :disabled="!passwordEditMode"
                                            autocomplete="new-password"
                                            required
                                            minlength="6"
                                            placeholder="Ulangi kata sandi baru"
                                            class="w-full px-3.5 py-2.5 pr-11 bg-white rounded-lg border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs"
                                        >
                                        <button
                                            type="button"
                                            @click="showConfirmPassword = !showConfirmPassword"
                                            class="absolute inset-y-0 right-0 px-3.5 flex items-center text-slate-400 hover:text-purple-deep transition cursor-pointer"
                                            title="Tampilkan / sembunyikan kata sandi"
                                        >
                                            <svg x-show="!showConfirmPassword" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                            <svg x-show="showConfirmPassword" x-cloak width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                        </button>
                                    </div>
                                    <p
                                        x-show="passwordForm.password_confirmation.length > 0 && passwordForm.password !== passwordForm.password_confirmation"
                                        x-cloak
                                        class="text-xs font-medium text-red-500 mt-1"
                                    >
                                        Konfirmasi kata sandi tidak cocok dengan kata sandi baru.
                                    </p>
                                </div>
                            </div>

                            {{-- Panduan Keamanan Kata Sandi --}}
                            <div class="p-3.5 rounded-lg bg-slate-50 border border-slate-100 space-y-1.5">
                                <span class="text-xs font-medium text-slate-500 block">Panduan Keamanan Kata Sandi</span>
                                <ul class="text-xs font-medium text-black space-y-1 list-disc list-inside">
                                    <li>Minimal 6 karakter; gunakan kombinasi huruf besar, huruf kecil, dan angka.</li>
                                    <li>Hindari menggunakan kata sandi yang sama dengan layanan lain.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100 flex flex-wrap items-center justify-end gap-3">
                            <button
                                x-show="!passwordEditMode"
                                type="button"
                                @click="enterPasswordEditMode()"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-white border border-[#D9C2F0] text-black hover:text-purple-deep hover:bg-purple-50/70 text-sm font-semibold shadow-xs transition cursor-pointer"
                            >
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                <span>Ubah Kata Sandi</span>
                            </button>

                            <button
                                x-show="passwordEditMode"
                                x-cloak
                                type="button"
                                @click="handleCancelPassword()"
                                class="px-5 py-2.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-sm shadow-xs transition cursor-pointer"
                            >
                                Batal
                            </button>

                            <button
                                x-show="passwordEditMode"
                                x-cloak
                                type="submit"
                                class="px-5 py-2.5 rounded-lg bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-sm shadow-xs transition cursor-pointer inline-flex items-center gap-2"
                            >
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                    <polyline points="17 21 17 13 7 13 7 21"/>
                                    <polyline points="7 3 7 8 15 8"/>
                                </svg>
                                <span>Simpan Kata Sandi</span>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Subcard 2: Informasi Sesi & Keamanan --}}
                <div class="rounded-xl border border-slate-100 p-5 sm:p-6 flex flex-col justify-between gap-5">
                    <div>
                        <div class="flex items-center gap-2 pb-3 mb-4 border-b border-slate-100">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                            <h3 class="text-sm font-semibold text-black">Informasi Sesi &amp; Keamanan</h3>
                        </div>

                        <div class="space-y-3.5">
                            <div>
                                <span class="text-xs font-medium text-slate-500 mb-1.5 block">Email Masuk</span>
                                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                    <span class="truncate">{{ $user->email }}</span>
                                </div>
                            </div>

                            <div>
                                <span class="text-xs font-medium text-slate-500 mb-1.5 block">Perangkat Saat Ini</span>
                                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                    <span>{{ $sessionInfo['device'] }}</span>
                                </div>
                            </div>

                            <div>
                                <span class="text-xs font-medium text-slate-500 mb-1.5 block">Sesi Login Aktif</span>
                                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                    <span>{{ $sessionInfo['active_count'] }} sesi aktif ({{ $sessionInfo['ip'] }})</span>
                                </div>
                            </div>

                            <div>
                                <span class="text-xs font-medium text-slate-500 mb-1.5 block">Ubah Kata Sandi Terakhir</span>
                                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                    <span>{{ $sessionInfo['last_password_change_text'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100">
                        <button
                            type="button"
                            @click="requestLogout()"
                            class="w-full px-5 py-2.5 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold text-sm shadow-xs transition cursor-pointer inline-flex items-center justify-center gap-2"
                        >
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                <polyline points="16 17 21 12 16 7"/>
                                <line x1="21" y1="12" x2="9" y2="12"/>
                            </svg>
                            <span>Keluar Akun</span>
                        </button>
                    </div>
                </div>

            </div>

        </div>{{-- End Body Container --}}

    </div>{{-- End Unified Card --}}

    {{-- ==================================================================== --}}
    {{-- MODAL KONFIRMASI PERUBAHAN BELUM DISIMPAN (UNSAVED CHANGES GUARD)    --}}
    {{-- ==================================================================== --}}
    <div x-show="showProfileUnsavedModal" x-cloak class="fixed inset-0 z-[60] overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="profile-unsaved-title">
        <div class="fixed inset-0 bg-[#1F0E38]/50 backdrop-blur-md"></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-md rounded-2xl bg-white/95 backdrop-blur-xl shadow-[0_30px_70px_rgba(74,35,128,0.2)] overflow-hidden">
                <div class="px-6 py-4 bg-purple-deep flex items-center justify-between text-white">
                    <h3 id="profile-unsaved-title" class="text-base font-semibold">Perubahan Belum Disimpan</h3>
                    <button type="button" @click="cancelUnsavedModal()" class="p-1 rounded-lg text-white/80 hover:text-white hover:bg-white/15 transition cursor-pointer" title="Tutup">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                <div class="p-6">
                    <div class="p-4 rounded-lg bg-slate-50 border border-slate-100">
                        <p class="text-sm font-medium text-black leading-relaxed">
                            Anda memiliki perubahan yang belum disimpan. Jika melanjutkan, perubahan pada formulir akan dibatalkan.
                        </p>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button
                        type="button"
                        @click="cancelUnsavedModal()"
                        class="px-5 py-2.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-sm shadow-xs transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="confirmDiscardUnsaved()"
                        class="px-5 py-2.5 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold text-sm shadow-xs transition cursor-pointer"
                    >
                        Ya, Tinggalkan
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
