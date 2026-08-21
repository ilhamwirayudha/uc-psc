<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'UC PSC — Sistem Informasi Manajemen')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F7F5FB] min-h-screen">
    <div class="flex min-h-screen">
        {{-- ===== SIDEBAR ===== --}}
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 grad-purple transform transition-transform duration-300 lg:translate-x-0 -translate-x-full">
            {{-- Logo --}}
            <div class="flex items-center gap-3 px-6 py-6 border-b border-white/10">
                <div class="w-9 h-9 rounded-xl bg-orange flex items-center justify-center">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                </div>
                <div>
                    <span class="text-white font-bold text-lg tracking-wide">UC <span class="text-orange">PSC</span></span>
                    <p class="text-white/40 text-[10px] tracking-wider uppercase">Management System</p>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="px-4 py-6 space-y-1 overflow-y-auto" style="max-height: calc(100vh - 160px);">
                <p class="text-white/30 text-[10px] uppercase tracking-widest font-semibold px-3 mb-3">Menu Utama</p>

                <a href="{{ route('dashboard') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-white/15 text-white font-semibold' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                    Dashboard
                </a>

                <p class="text-white/30 text-[10px] uppercase tracking-widest font-semibold px-3 mt-6 mb-3">Data</p>

                <a href="{{ route('clients.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-200 {{ request()->routeIs('clients.*') ? 'bg-white/15 text-white font-semibold' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Klien
                </a>

                <a href="{{ route('counselors.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-200 {{ request()->routeIs('counselors.*') ? 'bg-white/15 text-white font-semibold' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Konselor
                </a>

                <p class="text-white/30 text-[10px] uppercase tracking-widest font-semibold px-3 mt-6 mb-3">Operasional</p>

                <a href="{{ route('counseling-records.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-200 {{ request()->routeIs('counseling-records.*') ? 'bg-white/15 text-white font-semibold' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    Catatan Konseling
                </a>

                <a href="{{ route('test-results.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-200 {{ request()->routeIs('test-results.*') ? 'bg-white/15 text-white font-semibold' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    Hasil Tes
                </a>

                <a href="{{ route('bookings.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-200 {{ request()->routeIs('bookings.*') ? 'bg-white/15 text-white font-semibold' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    Booking
                </a>

                @if(auth()->user()->isAdmin())
                <p class="text-white/30 text-[10px] uppercase tracking-widest font-semibold px-3 mt-6 mb-3">Pengaturan</p>

                <a href="{{ route('assignments.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-200 {{ request()->routeIs('assignments.*') ? 'bg-white/15 text-white font-semibold' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                    Kelola Penugasan
                </a>

                <a href="{{ route('staff-management.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-200 {{ request()->routeIs('staff-management.*') ? 'bg-white/15 text-white font-semibold' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    Kelola Staff
                </a>
                @endif
            </nav>

            {{-- Logout button at bottom of sidebar --}}
            <div class="absolute bottom-0 left-0 right-0 px-4 py-3.5 border-t border-white/10">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-white/70 hover:bg-red-500/20 hover:text-red-200 transition-all duration-200 cursor-pointer group" title="Keluar">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="group-hover:translate-x-0.5 transition-transform"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        <span class="font-medium">Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- ===== MAIN CONTENT ===== --}}
        <div class="flex-1 lg:ml-64">
            {{-- Top Bar --}}
            <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-[#EDE1FA] px-6 py-3.5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        {{-- Mobile menu button --}}
                        <button id="sidebar-toggle" class="lg:hidden text-purple-deep">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                        </button>
                        <div>
                            <h1 class="text-lg font-bold text-purple-deep">@yield('page-title', 'Dashboard')</h1>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-3">
                            <div class="text-right hidden sm:block">
                                <p class="text-sm font-bold text-purple-deep leading-tight">{{ auth()->user()->name }}</p>
                                <span class="inline-block px-2 py-0.5 mt-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ auth()->user()->isAdmin() ? 'bg-orange/15 text-orange' : 'bg-purple-deep/10 text-purple-deep' }}">
                                    {{ auth()->user()->role }}
                                </span>
                            </div>
                            <div class="w-9 h-9 rounded-full bg-orange flex items-center justify-center text-white font-bold text-sm shadow-xs flex-shrink-0">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- ========================================================================= --}}
            {{-- FLOATING POP-UP TOAST NOTIFICATION (UNTUK SEMUA AKSI SIMPAN & NOTIFIKASI)  --}}
            {{-- ========================================================================= --}}
            @if(session('success'))
            <div x-data="{ show: true, timeout: null, progress: 100 }"
                x-init="
                    timeout = setTimeout(() => show = false, 4500);
                    let start = Date.now();
                    let interval = setInterval(() => {
                        let elapsed = Date.now() - start;
                        progress = Math.max(0, 100 - (elapsed / 4500 * 100));
                        if (progress <= 0) clearInterval(interval);
                    }, 30);
                "
                x-show="show"
                x-cloak
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-[-20px] scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-[-20px] scale-95"
                class="fixed top-6 right-6 z-50 max-w-md w-[calc(100vw-3rem)] sm:w-96 bg-white rounded-2xl shadow-2xl border border-emerald-100 overflow-hidden ring-1 ring-black/5">
                
                <div class="p-4 flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center flex-shrink-0 shadow-md shadow-emerald-500/20">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <div class="flex-1 min-w-0 pt-0.5">
                        <h4 class="text-sm font-bold text-[#2A2035] leading-none mb-1">Berhasil!</h4>
                        <p class="text-xs text-[#5B4A73] leading-relaxed">{{ session('success') }}</p>
                    </div>
                    <button @click="show = false; clearTimeout(timeout)" class="text-[#827299] hover:text-purple-deep p-1 rounded-lg hover:bg-[#F7F5FB] transition cursor-pointer" title="Tutup">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                {{-- Progress Bar Auto-Dismiss --}}
                <div class="h-1 bg-emerald-100 w-full">
                    <div class="h-full bg-emerald-500 transition-all duration-75" :style="'width: ' + progress + '%'"></div>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div x-data="{ show: true, timeout: null, progress: 100 }"
                x-init="
                    timeout = setTimeout(() => show = false, 4500);
                    let start = Date.now();
                    let interval = setInterval(() => {
                        let elapsed = Date.now() - start;
                        progress = Math.max(0, 100 - (elapsed / 4500 * 100));
                        if (progress <= 0) clearInterval(interval);
                    }, 30);
                "
                x-show="show"
                x-cloak
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-[-20px] scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-[-20px] scale-95"
                class="fixed top-6 right-6 z-50 max-w-md w-[calc(100vw-3rem)] sm:w-96 bg-white rounded-2xl shadow-2xl border border-red-100 overflow-hidden ring-1 ring-black/5">
                
                <div class="p-4 flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-red-500 text-white flex items-center justify-center flex-shrink-0 shadow-md shadow-red-500/20">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    </div>
                    <div class="flex-1 min-w-0 pt-0.5">
                        <h4 class="text-sm font-bold text-[#2A2035] leading-none mb-1">Terjadi Kesalahan</h4>
                        <p class="text-xs text-[#5B4A73] leading-relaxed">{{ session('error') }}</p>
                    </div>
                    <button @click="show = false; clearTimeout(timeout)" class="text-[#827299] hover:text-purple-deep p-1 rounded-lg hover:bg-[#F7F5FB] transition cursor-pointer" title="Tutup">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                {{-- Progress Bar Auto-Dismiss --}}
                <div class="h-1 bg-red-100 w-full">
                    <div class="h-full bg-red-500 transition-all duration-75" :style="'width: ' + progress + '%'"></div>
                </div>
            </div>
            @endif

            {{-- Page Content --}}
            <main class="p-6">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Mobile sidebar overlay --}}
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-30 lg:hidden hidden"></div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const toggle = document.getElementById('sidebar-toggle');
        const overlay = document.getElementById('sidebar-overlay');

        if (toggle) {
            toggle.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            });
        }

        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
