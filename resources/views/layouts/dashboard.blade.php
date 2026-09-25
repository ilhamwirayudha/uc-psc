<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UC PSC Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F7F5FB] min-h-screen" 
    x-data="{ 
        sidebarOpen: false, 
        showLogoutModal: false,
        showUnsavedModal: false,
        pendingUrl: null,
        isBrowserBack: false,
        proceedUnsaved() {
            window.isSubmittingForm = true;
            if (this.pendingUrl) {
                window.location.href = this.pendingUrl;
            } else if (this.isBrowserBack) {
                history.go(-2);
            } else {
                window.location.href = document.referrer || '{{ route('dashboard') }}';
            }
            this.showUnsavedModal = false;
        }
    }" 
    @keydown.escape.window="showLogoutModal = false; showUnsavedModal = false; sidebarOpen = false"
    @open-unsaved-modal.window="showUnsavedModal = true; pendingUrl = $event.detail.url; isBrowserBack = $event.detail.isBrowserBack"
>
    {{-- Splash Screen on Refresh / Load --}}
    <x-splash-screen />

    <div class="flex min-h-screen relative overflow-x-hidden z-10">
        {{-- Mobile Overlay Backdrop --}}
        <div 
            x-show="sidebarOpen" 
            x-cloak
            @click="sidebarOpen = false"
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/50 z-30 lg:hidden"
        ></div>

        {{-- ===== SIDEBAR ===== --}}
        <aside 
            id="sidebar" 
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'" 
            class="fixed inset-y-0 left-0 z-40 w-64 bg-purple-deep text-white shadow-xl transform transition-transform duration-300 select-none flex flex-col justify-between"
        >
            <div class="flex-1 flex flex-col min-h-0">
                {{-- Logo Header --}}
                <div class="px-6 py-5.5 border-b border-white/10 flex items-center flex-shrink-0">
                    <a href="{{ route('dashboard') }}" class="block" onclick="sessionStorage.setItem('show_splash_logo', '1')">
                        <img src="{{ asset('images/logo-ucpsc-white.png') }}" alt="Logo Resmi UC PSC" class="h-14 sm:h-15 w-auto object-contain">
                    </a>
                </div>

                {{-- Navigation --}}
                <nav class="px-4 py-6 space-y-1.5 overflow-y-auto flex-1">
                    <p class="text-white/40 text-[10px] uppercase tracking-widest font-extrabold px-3 mb-2">Menu Utama</p>

                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition font-medium {{ request()->routeIs('dashboard') ? 'bg-white/20 text-white font-semibold' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                        <span>Dashboard</span>
                    </a>

                    <p class="text-white/40 text-[10px] uppercase tracking-widest font-extrabold px-3 mt-6 mb-2">Data</p>

                    <a href="{{ route('clients.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition font-medium {{ request()->routeIs('clients.*') ? 'bg-white/20 text-white font-semibold' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span>Klien</span>
                    </a>

                    <a href="{{ route('counselors.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition font-medium {{ request()->routeIs('counselors.*') ? 'bg-white/20 text-white font-semibold' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>Konselor</span>
                    </a>

                    <p class="text-white/40 text-[10px] uppercase tracking-widest font-extrabold px-3 mt-6 mb-2">Operasional</p>

                
                    <a href="{{ route('test-results.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition font-medium {{ request()->routeIs('test-results.*') ? 'bg-white/20 text-white font-semibold' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        <span>Hasil Psikotes</span>
                    </a>

                    <a href="{{ route('bookings.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition font-medium {{ request()->routeIs('bookings.*') ? 'bg-white/20 text-white font-semibold' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span>Booking</span>
                    </a>

                    @if(auth()->user()->isAdmin())
                    <p class="text-white/40 text-[10px] uppercase tracking-widest font-extrabold px-3 mt-6 mb-2">Pengaturan</p>

                    <a href="{{ route('assignments.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition font-medium {{ request()->routeIs('assignments.*') ? 'bg-white/20 text-white font-semibold' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                        <span>Kelola Penugasan</span>
                    </a>

                    <a href="{{ route('staff-management.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition font-medium {{ request()->routeIs('staff-management.*') ? 'bg-white/20 text-white font-semibold' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                        <span>Kelola Staff</span>
                    </a>
                    @endif
                </nav>
            </div>

            {{-- User Profile Info & Logout at bottom of sidebar --}}
            <div class="px-4 py-3.5 border-t border-white/10 flex-shrink-0">
                <div class="flex items-center justify-between gap-2.5 px-1 py-1">
                    {{-- Avatar & Text Profile (Clickable to Profile Page) --}}
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 min-w-0 group/profile flex-1 rounded-xl p-1 -m-1 hover:bg-white/10 transition {{ request()->routeIs('profile.*') ? 'bg-white/15' : '' }}" title="Buka Profil Saya">
                        @if(auth()->user()->avatar_url)
                            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-9 h-9 rounded-full object-cover border border-white/20 shadow-xs flex-shrink-0 group-hover/profile:scale-105 transition-transform">
                        @else
                            <div class="w-9 h-9 rounded-full bg-purple-light text-white border border-white/20 flex items-center justify-center font-bold text-sm shadow-xs flex-shrink-0 group-hover/profile:scale-105 transition-transform">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-white truncate group-hover/profile:text-orange transition-colors">{{ auth()->user()->name }}</p>
                        </div>
                    </a>

                    {{-- Logout Button beside text --}}
                    <button 
                        type="button" 
                        @click="showLogoutModal = true" 
                        class="p-2 rounded-xl text-white/70 hover:text-white hover:bg-white/10 transition cursor-pointer flex-shrink-0 group" 
                        title="Keluar"
                    >
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-white/70 group-hover:text-red-400 transition-colors duration-200"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    </button>
                </div>
            </div>
        </aside>

        {{-- ===== MAIN CONTENT ===== --}}
        <div class="flex-1 lg:ml-64 flex flex-col min-w-0">
            {{-- Top Bar --}}
            <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-[#EDE1FA] px-6 py-3.5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5 sm:gap-3">
                        {{-- Mobile Hamburger Menu Button --}}
                        <button 
                            type="button" 
                            @click="sidebarOpen = !sidebarOpen" 
                            class="lg:hidden text-purple-deep hover:text-orange p-1.5 -ml-1.5 rounded-xl hover:bg-purple-deep/5 transition cursor-pointer"
                        >
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                        </button>

                        {{-- Back Button in Navbar --}}
                        @hasSection('back-url')
                            <a href="@yield('back-url')" class="w-8 h-8 rounded-xl bg-[#F7F5FB] hover:bg-purple-deep hover:text-white text-purple-deep border border-[#EDE1FA] flex items-center justify-center transition shadow-2xs cursor-pointer flex-shrink-0 group/back" title="Kembali">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="group-hover/back:-translate-x-0.5 transition-transform"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
                            </a>
                        @endif

                        <div>
                            <h1 class="text-lg font-bold text-purple-deep">@yield('page-title', 'Dashboard')</h1>
                        </div>
                    </div>
                </div>
            </header>

            {{-- ========================================================================= --}}
            {{-- NOTIFIKASI SUKSES PERSEGI PANJANG HIJAU (MUNCUL DARI BAWAH HEADER)         --}}
            {{-- ========================================================================= --}}
            @if(session('success'))
            <div x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 3500)"
                x-show="show"
                x-cloak
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 -translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-4"
                class="sticky top-[57px] z-30 px-6 pt-3 pb-1">
                <div class="w-full bg-emerald-600 text-white rounded-xl px-5 py-3.5 shadow-lg shadow-emerald-600/15 flex items-center justify-between gap-4 border border-emerald-500/40">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-7 h-7 rounded-lg bg-white/20 text-white flex items-center justify-center flex-shrink-0">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-white min-w-0">
                            {{ session('success') }}
                        </p>
                    </div>
                    <button @click="show = false" type="button" class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer flex-shrink-0" title="Tutup">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 4000)"
                x-show="show"
                x-cloak
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 -translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-4"
                class="sticky top-[57px] z-30 px-6 pt-3 pb-1">
                <div class="w-full bg-rose-600 text-white rounded-xl px-5 py-3.5 shadow-lg shadow-rose-600/15 flex items-center justify-between gap-4 border border-rose-500/40">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-7 h-7 rounded-lg bg-white/20 text-white flex items-center justify-center flex-shrink-0">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                            </svg>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-2 min-w-0 text-sm">
                            <span class="font-bold">Terjadi Kesalahan!</span>
                            <span class="text-rose-50 font-normal">{{ session('error') }}</span>
                        </div>
                    </div>
                    <button @click="show = false" type="button" class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer flex-shrink-0" title="Tutup">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
            </div>
            @endif

            {{-- Page Content --}}
            <main class="p-6">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- ===== MODAL KONFIRMASI LOGOUT (FROSTED GLASSMORPHISM) ===== --}}
    <div x-show="showLogoutModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-logout-title" role="dialog" aria-modal="true">
        {{-- Backdrop with Blur --}}
        <div x-show="showLogoutModal" 
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="showLogoutModal = false"
            class="fixed inset-0 bg-[#1F0E38]/50 backdrop-blur-md transition-opacity"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div x-show="showLogoutModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-[2rem] bg-white/75 backdrop-blur-2xl text-center shadow-[0_30px_70px_rgba(74,35,128,0.18),inset_0_1px_2px_rgba(255,255,255,0.95)] transition-all sm:my-8 sm:w-full sm:max-w-md border border-white/80 p-7 sm:p-9">
                
                {{-- Specular Top Highlight Edge --}}
                <div class="absolute top-0 inset-x-10 h-[1.5px] bg-gradient-to-r from-transparent via-white to-transparent pointer-events-none"></div>

                {{-- Centered Top Icon Badge --}}
                <div class="mx-auto w-14 h-14 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-600 flex items-center justify-center mb-5 backdrop-blur-md shadow-xs relative z-10">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </div>

                {{-- Title & Description --}}
                <h3 class="text-xl font-extrabold text-[#260E45] tracking-tight mb-2 relative z-10" id="modal-logout-title">
                    Keluar dari Sistem?
                </h3>
                <p class="text-xs sm:text-sm text-[#6B5B85] leading-relaxed max-w-xs mx-auto mb-7 relative z-10">
                    Sesi akun Anda akan diakhiri. Pastikan seluruh aktivitas dan data telah tersimpan.
                </p>

                {{-- Symmetrical Action Buttons (Grid 2 Equal Columns) --}}
                <div class="grid grid-cols-2 gap-3.5 pt-1 relative z-10">
                    <button 
                        type="button" 
                        @click="showLogoutModal = false"
                        class="w-full py-3 rounded-2xl text-xs sm:text-sm font-bold text-[#5B4A73] hover:text-[#260E45] bg-white/60 hover:bg-white border border-white/90 hover:border-purple-200/80 shadow-xs hover:shadow-sm active:scale-[0.98] transition-all duration-200 cursor-pointer flex items-center justify-center"
                    >
                        Batal
                    </button>
                    
                    <form action="{{ route('logout') }}" method="POST" class="w-full">
                        @csrf
                        <button 
                            type="submit" 
                            class="w-full py-3 bg-gradient-to-r from-red-600 via-rose-600 to-red-600 hover:from-red-700 hover:to-rose-700 text-white rounded-2xl text-xs sm:text-sm font-bold transition-all duration-200 shadow-[0_8px_20px_rgba(225,29,72,0.3),inset_0_1px_1px_rgba(255,255,255,0.4)] hover:shadow-[0_10px_25px_rgba(225,29,72,0.45)] active:scale-[0.98] cursor-pointer flex items-center justify-center border border-white/20 relative overflow-hidden"
                        >
                            <span class="relative z-10">Ya, Keluar</span>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    {{-- ===== MODAL KONFIRMASI TINGGALKAN HALAMAN (FROSTED GLASSMORPHISM) ===== --}}
    <div x-show="showUnsavedModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-unsaved-title" role="dialog" aria-modal="true">
        {{-- Backdrop with Blur --}}
        <div x-show="showUnsavedModal" 
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="showUnsavedModal = false"
            class="fixed inset-0 bg-[#1F0E38]/50 backdrop-blur-md transition-opacity"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div x-show="showUnsavedModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-[2rem] bg-white/80 backdrop-blur-2xl text-center shadow-[0_30px_70px_rgba(74,35,128,0.18),inset_0_1px_2px_rgba(255,255,255,0.95)] transition-all sm:my-8 sm:w-full sm:max-w-md border border-white/80 p-7 sm:p-9">
                
                {{-- Specular Top Highlight Edge --}}
                <div class="absolute top-0 inset-x-10 h-[1.5px] bg-gradient-to-r from-transparent via-white to-transparent pointer-events-none"></div>

                {{-- Centered Top Icon Badge --}}
                <div class="mx-auto w-14 h-14 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-600 flex items-center justify-center mb-5 backdrop-blur-md shadow-xs relative z-10">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                </div>

                {{-- Title & Description --}}
                <h3 class="text-xl font-extrabold text-[#260E45] tracking-tight mb-2 relative z-10" id="modal-unsaved-title">
                    Tinggalkan Halaman?
                </h3>
                <p class="text-xs sm:text-sm text-[#6B5B85] leading-relaxed max-w-xs mx-auto mb-7 relative z-10">
                    Data formulir yang telah Anda isi belum disimpan. Jika Anda keluar sekarang, draf pengisian akan hilang.
                </p>

                {{-- Symmetrical Action Buttons (Grid 2 Equal Columns) --}}
                <div class="grid grid-cols-2 gap-3.5 pt-1 relative z-10">
                    <button 
                        type="button" 
                        @click="showUnsavedModal = false"
                        class="w-full py-3 rounded-2xl text-xs sm:text-sm font-bold text-purple-deep hover:text-[#260E45] bg-white/70 hover:bg-white border border-[#D9C2F0]/80 hover:border-purple-300 shadow-xs hover:shadow-sm active:scale-[0.98] transition-all duration-200 cursor-pointer flex items-center justify-center"
                    >
                        Lanjut Mengisi
                    </button>
                    
                    <button 
                        type="button"
                        @click="proceedUnsaved()" 
                        class="w-full py-3 bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 hover:from-amber-600 hover:to-orange-600 text-white rounded-2xl text-xs sm:text-sm font-bold transition-all duration-200 shadow-[0_8px_20px_rgba(245,158,11,0.3),inset_0_1px_1px_rgba(255,255,255,0.4)] hover:shadow-[0_10px_25px_rgba(245,158,11,0.45)] active:scale-[0.98] cursor-pointer flex items-center justify-center border border-white/20 relative overflow-hidden"
                    >
                        <span class="relative z-10">Ya, Tinggalkan</span>
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- ===== MODAL BUAT BOOKING (GLOBAL) ===== --}}
    <x-booking-modal />

    {{-- ===== MODAL TAMBAH KLIEN (GLOBAL) ===== --}}
    <x-client-modal />

    {{-- ===== MODAL UPLOAD HASIL PSIKOTES (GLOBAL) ===== --}}
    <x-test-result-modal />

    @stack('scripts')
</body>
</html>
