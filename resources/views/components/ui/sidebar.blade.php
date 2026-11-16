<aside
    id="sidebar"
    :class="[
        sidebarOpen ? 'translate-x-0' : '-translate-x-[110%] lg:translate-x-0',
        sidebarCollapsed ? 'lg:w-20' : 'lg:w-64',
        sidebarTransitionReady ? 'transition-[transform,width] duration-200 ease-out' : 'max-lg:transition-transform max-lg:duration-300 max-lg:ease-in-out'
    ]"
    class="fixed inset-y-0 left-0 max-lg:rounded-r-3xl lg:top-3 lg:bottom-3 lg:left-3 lg:rounded-3xl z-40 w-64 glass-sidebar text-white select-none flex flex-col justify-between max-lg:overflow-hidden lg:overflow-visible"
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

            {{-- Compact Brand Emblem (Desktop Collapsed Only) --}}
            <a href="{{ route('dashboard') }}"
               onclick="sessionStorage.setItem('show_splash_logo', '1')"
               class="hidden w-11 h-11 rounded-2xl bg-white/15 border border-white/20 items-center justify-center hover:bg-white/25 transition"
               :class="sidebarCollapsed ? 'lg:flex' : 'lg:hidden'"
               title="UC PSC Dashboard">
                <span class="text-xs font-extrabold tracking-tight leading-none">
                    <span class="text-orange">U</span><span class="text-white">P</span>
                </span>
            </a>

            {{-- Mobile Drawer Close Button --}}
            <button
                type="button"
                @click="sidebarOpen = false"
                class="lg:hidden absolute right-3 top-1/2 -translate-y-1/2 p-1.5 rounded-xl bg-white/10 text-white hover:bg-white/20 transition cursor-pointer"
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
                    :href="route('staff-management.index')"
                    :active="request()->routeIs('staff-management.*')"
                    label="Kelola Staff"
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

    {{-- User Profile Mini Card inside Floating Dock --}}
    <div class="p-3 flex-shrink-0">
        <a href="{{ route('profile.edit') }}"
           :title="sidebarCollapsed ? '{{ auth()->user()->name }} — Buka Profil Saya' : 'Buka Profil Saya'"
           class="group/profile flex items-center gap-2.5 p-2.5 rounded-2xl border transition {{ request()->routeIs('profile.*') ? 'bg-white/18 border-white/25 shadow-[inset_0_1px_0_rgba(255,255,255,0.2)]' : 'bg-white/10 border-white/15 hover:bg-white/15 hover:border-white/25' }}"
           :class="sidebarCollapsed ? 'lg:justify-center lg:p-1.5 lg:w-12 lg:h-12 lg:mx-auto' : ''">
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
        </a>
    </div>
</aside>
