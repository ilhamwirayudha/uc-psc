<aside
    id="sidebar"
    :class="[
        sidebarOpen ? 'translate-x-0' : '-translate-x-[110%] lg:translate-x-0',
        sidebarCollapsed ? 'lg:w-20' : 'lg:w-64',
        sidebarTransitionReady ? 'transition-[transform,width] duration-200 ease-out' : 'max-lg:transition-transform max-lg:duration-300 max-lg:ease-in-out'
    ]"
    class="fixed inset-y-0 left-0 max-lg:rounded-r-2xl lg:top-3 lg:bottom-3 lg:left-3 lg:rounded-2xl z-40 w-64 glass-sidebar text-white select-none flex flex-col justify-between max-lg:overflow-hidden lg:overflow-visible"
>
    <div class="flex-1 flex flex-col min-h-0">
        {{-- Floating Dock Header (Centered Logo) --}}
        <div class="relative px-4 py-2.5 flex items-center justify-center flex-shrink-0"
             :class="sidebarCollapsed ? 'lg:px-2 lg:py-3.5' : ''">
            <a href="{{ route('dashboard') }}"
               onclick="sessionStorage.setItem('show_splash_logo', '1')"
               class="flex items-center justify-center max-lg:pr-8"
               :class="sidebarCollapsed ? 'lg:hidden' : ''">
                <img src="{{ asset('images/logo-ucpsc-white.png') }}" alt="Logo Resmi UC PSC" class="h-24 w-auto object-contain">
            </a>

            {{-- Compact Official Logo (Desktop Collapsed Only) --}}
            <a href="{{ route('dashboard') }}"
               onclick="sessionStorage.setItem('show_splash_logo', '1')"
               class="hidden items-center justify-center rounded-xl p-1 hover:bg-white/10 transition"
               :class="sidebarCollapsed ? 'lg:flex' : 'lg:hidden'"
               title="UC PSC Dashboard">
                <img src="{{ asset('images/logo-ucpsc-white.png') }}" alt="Logo Resmi UC PSC" class="w-14 h-auto object-contain">
            </a>

            {{-- Mobile Drawer Close Button --}}
            <button
                type="button"
                @click="sidebarOpen = false"
                class="lg:hidden absolute right-3 top-1/2 -translate-y-1/2 p-1.5 rounded-lg bg-white/10 text-white hover:bg-white/20 transition cursor-pointer"
                aria-label="Tutup Menu"
            >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        {{-- Logo Divider with Frosted Glass Circle Collapse Button (Varian 1) --}}
        <div class="relative flex-shrink-0">
            <div class="w-full h-px bg-white/15"></div>
            <button
                type="button"
                @click="toggleSidebarCollapse()"
                :title="sidebarCollapsed ? 'Perluas Sidebar' : 'Lipat Sidebar'"
                aria-label="Toggle Sidebar"
                class="hidden lg:flex items-center justify-center absolute right-0 top-1/2 -translate-y-1/2 translate-x-1/2 z-50 w-7 h-7 rounded-full bg-white/80 backdrop-blur-md text-purple-deep hover:text-orange border border-white/90 ring-4 ring-purple-200/40 shadow-[0_6px_18px_rgba(74,35,128,0.16)] hover:bg-white/95 hover:scale-105 transition duration-200 cursor-pointer"
            >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"
                     class="transition-transform duration-200"
                     :class="sidebarCollapsed ? 'rotate-180' : ''">
                    <polyline points="11 17 6 12 11 7"/>
                    <polyline points="18 17 13 12 18 7"/>
                </svg>
            </button>
        </div>

        {{-- Navigation --}}
        <nav class="px-3 py-3.5 overflow-y-auto custom-scrollbar flex-1"
             :class="sidebarCollapsed ? 'lg:px-2.5' : ''">
            {{-- Dashboard (Pinned Standalone Top Item) --}}
            <x-ui.sidebar-link
                :href="route('dashboard')"
                :active="request()->routeIs('dashboard')"
                label="Dashboard"
            >
                <x-slot:icon>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7" rx="1"/>
                        <rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/>
                        <rect x="14" y="14" width="7" height="7" rx="1"/>
                    </svg>
                </x-slot:icon>
            </x-ui.sidebar-link>

            {{-- Layanan & Jadwal --}}
            <x-ui.sidebar-group label="Layanan & Jadwal">
                <x-ui.sidebar-link
                    :href="route('calendar.index')"
                    :active="request()->routeIs('calendar.*')"
                    label="Kalender"
                >
                    <x-slot:icon>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                            <path d="M8 14h.01"/>
                            <path d="M12 14h.01"/>
                            <path d="M16 14h.01"/>
                            <path d="M8 18h.01"/>
                            <path d="M12 18h.01"/>
                            <path d="M16 18h.01"/>
                        </svg>
                    </x-slot:icon>
                </x-ui.sidebar-link>

                <x-ui.sidebar-link
                    :href="route('bookings.counseling')"
                    :active="request()->routeIs('bookings.counseling')"
                    label="Layanan Konseling"
                >
                    <x-slot:icon>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                            <path d="M8 10h.01"/>
                            <path d="M12 10h.01"/>
                            <path d="M16 10h.01"/>
                        </svg>
                    </x-slot:icon>
                </x-ui.sidebar-link>

                <x-ui.sidebar-link
                    :href="route('bookings.psychotest')"
                    :active="request()->routeIs('bookings.psychotest')"
                    label="Layanan Psikotes"
                >
                    <x-slot:icon>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                            <rect x="9" y="3" width="6" height="4" rx="1"/>
                            <path d="m9 14 2 2 4-4"/>
                        </svg>
                    </x-slot:icon>
                </x-ui.sidebar-link>
            </x-ui.sidebar-group>

            {{-- Klien & Psikolog --}}
            <x-ui.sidebar-group label="Klien & Psikolog">
                <x-ui.sidebar-link
                    :href="route('clients.index')"
                    :active="request()->routeIs('clients.*')"
                    label="Daftar Klien"
                >
                    <x-slot:icon>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </x-slot:icon>
                </x-ui.sidebar-link>

                <x-ui.sidebar-link
                    :href="route('client-forms.index')"
                    :active="request()->routeIs('client-forms.*')"
                    label="Formulir Klien"
                >
                    <x-slot:icon>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                    </x-slot:icon>
                </x-ui.sidebar-link>

                <x-ui.sidebar-link
                    :href="route('counselors.index')"
                    :active="request()->routeIs('counselors.*')"
                    label="Psikolog"
                >
                    <x-slot:icon>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </x-slot:icon>
                </x-ui.sidebar-link>
            </x-ui.sidebar-group>

            {{-- Administrasi (Admin Only) --}}
            @if(auth()->user()->isAdmin())
            <x-ui.sidebar-group label="Administrasi">
                <x-ui.sidebar-link
                    :href="route('assignments.index')"
                    :active="request()->routeIs('assignments.*')"
                    label="Kelola Penugasan"
                >
                    <x-slot:icon>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="8.5" cy="7" r="4"/>
                            <line x1="20" y1="8" x2="20" y2="14"/>
                            <line x1="23" y1="11" x2="17" y2="11"/>
                        </svg>
                    </x-slot:icon>
                </x-ui.sidebar-link>

                <x-ui.sidebar-link
                    :href="route('psychotest-bookings.monthly-recap')"
                    :active="request()->routeIs('psychotest-bookings.monthly-recap')"
                    label="Rekap Bulanan (I/R)"
                >
                    <x-slot:icon>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="8" y1="13" x2="16" y2="13"/>
                            <line x1="8" y1="17" x2="16" y2="17"/>
                        </svg>
                    </x-slot:icon>
                </x-ui.sidebar-link>

                <x-ui.sidebar-link
                    :href="route('staff-management.index')"
                    :active="request()->routeIs('staff-management.*')"
                    label="Kelola Akun Tim"
                >
                    <x-slot:icon>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                        </svg>
                    </x-slot:icon>
                </x-ui.sidebar-link>
            </x-ui.sidebar-group>
            @endif
        </nav>
    </div>

    {{-- User Profile Mini Card + Floating Drop-Up Menu (Opsi 1) --}}
    @php
        $isProfilePage = request()->routeIs('profile.*');
        $currentProfileTab = $isProfilePage
            ? (in_array(request('tab'), ['profil', 'log-aktivitas', 'pengaturan'], true) ? request('tab') : 'profil')
            : '';
    @endphp
    <div
        class="p-3 flex-shrink-0 relative"
        x-data="{
            userMenuOpen: false,
            isProfilePage: @js($isProfilePage),
            activeProfileTab: @js($currentProfileTab),
            selectProfileTab(event, tab, fallbackUrl) {
                this.userMenuOpen = false;
                if (this.isProfilePage) {
                    event.preventDefault();
                    window.dispatchEvent(new CustomEvent('request-profile-tab', { detail: { tab: tab } }));
                    sidebarOpen = false;
                    return;
                }
                sidebarOpen = false;
            }
        }"
        @profile-tab-changed.window="activeProfileTab = $event.detail.tab"
        @keydown.escape.window="userMenuOpen = false"
        @click.outside="userMenuOpen = false"
    >
        {{-- Side Flyout Popover Melayang di Samping Kanan (Fallback Drop-Up di Mobile) --}}
        <div
            x-show="userMenuOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95 max-lg:translate-y-1.5 lg:-translate-x-1.5"
            x-transition:enter-end="opacity-100 scale-100 max-lg:translate-y-0 lg:translate-x-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100 max-lg:translate-y-0 lg:translate-x-0"
            x-transition:leave-end="opacity-0 scale-95 max-lg:translate-y-1.5 lg:-translate-x-1.5"
            class="absolute z-50 max-lg:bottom-full max-lg:mb-2 max-lg:left-3 max-lg:right-3 lg:left-full lg:bottom-3 lg:ml-3 lg:w-64 rounded-xl bg-white/95 backdrop-blur-xl border border-slate-100 shadow-[0_20px_50px_rgba(74,35,128,0.22)] overflow-hidden"
            role="menu"
            aria-label="Menu Akun Pengguna"
        >
            {{-- Header Identitas User pada Popover --}}
            <div class="px-3.5 py-3 bg-slate-50/90 border-b border-slate-100 flex items-center gap-2.5">
                @if(auth()->user()->avatar_url)
                    <img src="{{ auth()->user()->avatar_url }}"
                         alt="{{ auth()->user()->name }}"
                         class="w-8 h-8 rounded-full object-cover object-top bg-white border border-slate-200 flex-shrink-0">
                @else
                    <div class="w-8 h-8 rounded-full bg-purple-deep text-white flex items-center justify-center font-semibold text-xs flex-shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                @endif
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold text-black truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[11px] font-medium text-slate-500 truncate">{{ auth()->user()->email }}</p>
                </div>
            </div>

            <div class="p-1.5 space-y-0.5">
                {{-- Item 1: Profil --}}
                <a
                    href="{{ route('profile.edit') }}"
                    @click="selectProfileTab($event, 'profil', '{{ route('profile.edit') }}')"
                    :class="isProfilePage && activeProfileTab === 'profil'
                        ? 'bg-purple-50 text-purple-deep'
                        : 'text-black hover:bg-slate-50 hover:text-purple-deep'"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs sm:text-sm font-medium transition cursor-pointer"
                    role="menuitem"
                >
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <span>Profil</span>
                </a>

                {{-- Item 2: Log Aktivitas --}}
                <a
                    href="{{ route('profile.edit', ['tab' => 'log-aktivitas']) }}"
                    @click="selectProfileTab($event, 'log-aktivitas', '{{ route('profile.edit', ['tab' => 'log-aktivitas']) }}')"
                    :class="isProfilePage && activeProfileTab === 'log-aktivitas'
                        ? 'bg-purple-50 text-purple-deep'
                        : 'text-black hover:bg-slate-50 hover:text-purple-deep'"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs sm:text-sm font-medium transition cursor-pointer"
                    role="menuitem"
                >
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <span>Log Aktivitas</span>
                </a>

                {{-- Item 3: Pengaturan --}}
                <a
                    href="{{ route('profile.edit', ['tab' => 'pengaturan']) }}"
                    @click="selectProfileTab($event, 'pengaturan', '{{ route('profile.edit', ['tab' => 'pengaturan']) }}')"
                    :class="isProfilePage && activeProfileTab === 'pengaturan'
                        ? 'bg-purple-50 text-purple-deep'
                        : 'text-black hover:bg-slate-50 hover:text-purple-deep'"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs sm:text-sm font-medium transition cursor-pointer"
                    role="menuitem"
                >
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                    <span>Pengaturan</span>
                </a>
            </div>

            <div class="p-1.5 border-t border-slate-100">
                <button
                    type="button"
                    @click="userMenuOpen = false; sidebarOpen = false; window.dispatchEvent(new CustomEvent('open-logout-modal'))"
                    class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs sm:text-sm font-medium text-red-600 hover:bg-red-50 transition cursor-pointer text-left"
                    role="menuitem"
                >
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    <span>Keluar Akun</span>
                </button>
            </div>
        </div>

        {{-- Trigger Button Kartu User --}}
        <button
            type="button"
            @click="userMenuOpen = !userMenuOpen"
            :aria-expanded="userMenuOpen.toString()"
            aria-haspopup="menu"
            :title="sidebarCollapsed ? '{{ auth()->user()->name }} — Menu Akun' : 'Buka Menu Akun'"
            class="w-full text-left group/profile flex items-center gap-2.5 p-2.5 rounded-xl border transition cursor-pointer {{ $isProfilePage ? 'bg-white/18 border-white/25 shadow-[inset_0_1px_0_rgba(255,255,255,0.2)]' : 'bg-white/10 border-white/15 hover:bg-white/15 hover:border-white/25' }}"
            :class="[
                sidebarCollapsed ? 'lg:justify-center lg:p-1.5 lg:w-12 lg:h-12 lg:mx-auto' : '',
                userMenuOpen ? 'bg-white/18 border-white/30' : ''
            ]"
        >
            @if(auth()->user()->avatar_url)
                <img src="{{ auth()->user()->avatar_url }}"
                     alt="{{ auth()->user()->name }}"
                     class="w-9 h-9 rounded-full object-cover object-top bg-white border border-white/30 shadow-xs flex-shrink-0">
            @else
                <div class="w-9 h-9 rounded-full bg-white text-purple-deep border border-white/30 flex items-center justify-center font-bold text-sm flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
            @endif

            <div class="min-w-0 flex-1" :class="sidebarCollapsed ? 'lg:hidden' : ''">
                <p class="text-xs font-semibold text-white truncate group-hover/profile:text-orange transition-colors">
                    {{ auth()->user()->name }}
                </p>
                <p class="text-[11px] font-medium text-white/75 truncate">
                    {{ auth()->user()->isAdmin() ? 'Administrator' : 'Staff' }}
                </p>
            </div>
        </button>
    </div>
</aside>
