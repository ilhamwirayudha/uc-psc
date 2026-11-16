<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'UC PSC Management System')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-ucpsc.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F3EBF9] min-h-screen relative overflow-x-hidden selection:bg-purple-light selection:text-white" 
    x-data="{ 
        sidebarOpen: false, 
        sidebarCollapsed: localStorage.getItem('sidebar_collapsed') === 'true',
        sidebarTransitionReady: false,
        showLogoutModal: false,
        showUnsavedModal: false,
        pendingUrl: null,
        isBrowserBack: false,
        init() {
            this.$nextTick(() => {
                this.sidebarTransitionReady = true;
            });
        },
        toggleSidebarCollapse() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            localStorage.setItem('sidebar_collapsed', this.sidebarCollapsed ? 'true' : 'false');
        },
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
    @open-logout-modal.window="showLogoutModal = true"
>
    {{-- Splash Screen on Refresh / Load --}}
    <x-splash-screen />

    {{-- ===== HIGH-PERFORMANCE STATIC AURORA MESH BACKGROUND (FOR GLASSMORPHISM REFRACTION) ===== --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0" aria-hidden="true">
        {{-- Top-Left Purple / Lavender Glow --}}
        <div class="absolute -top-[18%] -left-[10%] w-[780px] h-[780px] bg-gradient-to-br from-[#8E51E8]/24 via-[#B07DF5]/16 to-transparent rounded-full blur-[110px] transform-gpu"></div>
        {{-- Bottom-Right Warm Peach / Orange Glow --}}
        <div class="absolute -bottom-[18%] -right-[10%] w-[820px] h-[820px] bg-gradient-to-tl from-[#FB923C]/22 via-[#FDBA74]/16 to-[#C084FC]/10 rounded-full blur-[115px] transform-gpu"></div>
        {{-- Center-Right Atmospheric Tint --}}
        <div class="absolute top-1/3 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[680px] h-[680px] bg-gradient-to-tr from-[#E9D5FF]/32 via-[#FED7AA]/14 to-transparent rounded-full blur-[95px] transform-gpu"></div>
    </div>

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
        <x-ui.sidebar />

        {{-- ===== MAIN CONTENT ===== --}}
        <div id="dashboard-main"
             :class="[
                 sidebarCollapsed ? 'lg:ml-[92px]' : 'lg:ml-[268px]',
                 sidebarTransitionReady ? 'transition-[margin] duration-200 ease-out' : ''
             ]"
             class="flex-1 flex flex-col min-w-0">
            {{-- Borderless Editorial Header (Desktop Transparent on Aurora Canvas, Sticky Glass on Mobile) --}}
            <header class="z-30 flex items-center justify-between gap-4 px-6 max-lg:sticky max-lg:top-0 max-lg:h-16 max-lg:glass-topbar lg:pt-5 lg:pb-1">
                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                    {{-- Mobile Hamburger (Only on mobile < lg) --}}
                    <button 
                        type="button" 
                        @click="sidebarOpen = true" 
                        class="lg:hidden text-purple-deep hover:text-orange p-1.5 -ml-1.5 rounded-lg hover:bg-purple-deep/5 transition cursor-pointer flex items-center justify-center flex-shrink-0"
                        title="Buka Menu"
                    >
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                    </button>

                    <div class="min-w-0">
                        @hasSection('breadcrumbs')
                            @yield('breadcrumbs')
                        @else
                            <x-ui.breadcrumbs />
                        @endif
                        <h1 class="text-lg lg:text-2xl font-bold tracking-tight text-purple-deep truncate">
                            @yield('page-title', 'Dashboard')
                        </h1>
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
                class="sticky top-16 lg:top-4 z-30 px-6 pt-3 pb-1">
                <div class="w-full bg-emerald-600/90 backdrop-blur-md text-white rounded-lg px-5 py-3.5 shadow-lg shadow-emerald-600/15 flex items-center justify-between gap-4 border border-white/25">
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

            @if(session('error') || $errors->any())
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
                class="sticky top-16 lg:top-4 z-30 px-6 pt-3 pb-1">
                <div class="w-full bg-rose-600/90 backdrop-blur-md text-white rounded-lg px-5 py-3.5 shadow-lg shadow-rose-600/15 flex items-center justify-between gap-4 border border-white/25">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-7 h-7 rounded-lg bg-white/20 text-white flex items-center justify-center flex-shrink-0">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                            </svg>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-2 min-w-0 text-sm">
                            <span class="font-bold">Terjadi Kesalahan!</span>
                            <span class="text-rose-50 font-normal">{{ session('error') ?: $errors->first() }}</span>
                        </div>
                    </div>
                    <button @click="show = false" type="button" class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer flex-shrink-0" title="Tutup">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
            </div>
            @endif

            {{-- Page Content --}}
            <main class="p-6 lg:pt-4">
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
            class="fixed inset-0 bg-[#1F0E38]/50 backdrop-blur-md transition-opacity"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div x-show="showLogoutModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-[2rem] bg-white/75 backdrop-blur-2xl text-center shadow-[0_30px_70px_rgba(74,35,128,0.18)] transition-all sm:my-8 sm:w-full sm:max-w-md p-7 sm:p-9">
                
                {{-- Centered Top Icon Badge --}}
                <div class="mx-auto w-14 h-14 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 flex items-center justify-center mb-5 backdrop-blur-md shadow-xs relative z-10">
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
                        class="w-full py-3 rounded-xl text-xs sm:text-sm font-bold text-[#5B4A73] hover:text-[#260E45] bg-white/60 hover:bg-white border border-white/90 hover:border-purple-200/80 shadow-xs hover:shadow-sm active:scale-[0.98] transition-all duration-200 cursor-pointer flex items-center justify-center"
                    >
                        Batal
                    </button>
                    
                    <form action="{{ route('logout') }}" method="POST" class="w-full"
                          @submit.prevent="
                              fetch('{{ route('logout') }}', {
                                  method: 'POST',
                                  headers: {
                                      'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                      'Accept': 'application/json'
                                  }
                              }).finally(() => {
                                  window.location.replace('{{ route('login') }}');
                              })
                          ">
                        @csrf
                        <button 
                            type="submit" 
                            class="w-full py-3 bg-gradient-to-r from-red-600 via-rose-600 to-red-600 hover:from-red-700 hover:to-rose-700 text-white rounded-xl text-xs sm:text-sm font-bold transition-all duration-200 shadow-[0_8px_20px_rgba(225,29,72,0.3),inset_0_1px_1px_rgba(255,255,255,0.4)] hover:shadow-[0_10px_25px_rgba(225,29,72,0.45)] active:scale-[0.98] cursor-pointer flex items-center justify-center border border-white/20 relative overflow-hidden"
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
            class="fixed inset-0 bg-[#1F0E38]/50 backdrop-blur-md transition-opacity"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div x-show="showUnsavedModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-[2rem] bg-white/80 backdrop-blur-2xl text-center shadow-[0_30px_70px_rgba(74,35,128,0.18)] transition-all sm:my-8 sm:w-full sm:max-w-md p-7 sm:p-9">
                
                {{-- Centered Top Icon Badge --}}
                <div class="mx-auto w-14 h-14 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-600 flex items-center justify-center mb-5 backdrop-blur-md shadow-xs relative z-10">
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
                        class="w-full py-3 rounded-xl text-xs sm:text-sm font-bold text-purple-deep hover:text-[#260E45] bg-white/70 hover:bg-white border border-[#D9C2F0]/80 hover:border-purple-300 shadow-xs hover:shadow-sm active:scale-[0.98] transition-all duration-200 cursor-pointer flex items-center justify-center"
                    >
                        Lanjut Mengisi
                    </button>
                    
                    <button 
                        type="button"
                        @click="proceedUnsaved()" 
                        class="w-full py-3 bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 hover:from-amber-600 hover:to-orange-600 text-white rounded-xl text-xs sm:text-sm font-bold transition-all duration-200 shadow-[0_8px_20px_rgba(245,158,11,0.3),inset_0_1px_1px_rgba(255,255,255,0.4)] hover:shadow-[0_10px_25px_rgba(245,158,11,0.45)] active:scale-[0.98] cursor-pointer flex items-center justify-center border border-white/20 relative overflow-hidden"
                    >
                        <span class="relative z-10">Ya, Tinggalkan</span>
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- ===== MODAL BUAT BOOKING (GLOBAL) ===== --}}
    <x-booking-modal />

    {{-- ===== MODAL UPLOAD HASIL PSIKOTES (GLOBAL) ===== --}}
    


    @stack('scripts')

    {{-- Prevent bfcache from displaying stale authenticated state --}}
    <script>
        window.addEventListener('pageshow', function (event) {
            if (event.persisted || (window.performance && window.performance.getEntriesByType && window.performance.getEntriesByType("navigation")[0]?.type === "back_forward")) {
                window.location.reload();
            }
        });
    </script>

    {{-- Intercept browser Back button on Dashboard and offer Logout confirmation --}}
    @if(request()->routeIs('dashboard'))
    <script>
        (function () {
            if (window.history && window.history.pushState) {
                history.pushState({ dashboardGuard: true }, '', window.location.href);

                window.addEventListener('popstate', function (e) {
                    if (window.checkFormDirty && window.checkFormDirty()) {
                        return;
                    }

                    try {
                        history.pushState({ dashboardGuard: true }, '', window.location.href);
                    } catch (err) {}

                    window.dispatchEvent(new CustomEvent('open-logout-modal'));
                });
            }
        })();
    </script>
    @endif
</body>
</html>
