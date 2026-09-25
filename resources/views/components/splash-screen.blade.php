{{-- ===== UC PSC PREMIUM SPLASH / LOADING SCREEN ===== --}}
<div 
    id="uc-psc-splash" 
    class="fixed inset-0 z-[9999] flex flex-col items-center justify-center bg-[#F9F7FD] transition-all duration-500 ease-out overflow-hidden"
    style="opacity: 0; visibility: hidden; pointer-events: none;"
>
    {{-- Ambient Aurora Glow in Background --}}
    <div class="absolute -top-32 -left-32 w-80 h-80 bg-purple-300/40 rounded-full blur-3xl pointer-events-none animate-pulse"></div>
    <div class="absolute -bottom-32 -right-32 w-80 h-80 bg-orange/20 rounded-full blur-3xl pointer-events-none"></div>

    {{-- Center Splash Content --}}
    <div class="relative z-10 flex flex-col items-center text-center px-6">
        
        {{-- Official UC PSC Logo --}}
        <div class="relative mb-7">
            <img src="{{ asset('images/logo-ucpsc.png') }}" alt="Logo Resmi UC PSC" class="h-28 sm:h-36 w-auto object-contain animate-pulse" style="animation-duration: 2s;">
        </div>

        {{-- ===== CIRCULAR LOOP SPINNER ===== --}}
        <div class="relative flex items-center justify-center">
            {{-- Outer Track --}}
            <svg class="w-10 h-10 transform -rotate-90 animate-spin" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" style="animation-duration: 1s;">
                <circle cx="22" cy="22" r="18" stroke="#E4D2F5" stroke-width="3.5" class="opacity-40" />
                <circle cx="22" cy="22" r="18" stroke="url(#splash_gradient)" stroke-width="3.5" stroke-linecap="round" stroke-dasharray="113.097" stroke-dashoffset="75" />
                <defs>
                    <linearGradient id="splash_gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#4A2380" />
                        <stop offset="60%" stop-color="#7C4DBE" />
                        <stop offset="100%" stop-color="#E38B2C" />
                    </linearGradient>
                </defs>
            </svg>
        </div>
    </div>
</div>

<script>
    (function() {
        // 1. Detect if transition is from Login -> Main Page or Logout -> Login Page
        const isAuthTransition = {{ session('show_splash') ? 'true' : 'false' }};

        // 2. Detect if triggered by clicking the UC PSC logo
        const isLogoClick = sessionStorage.getItem('show_splash_logo') === '1';
        if (isLogoClick) {
            sessionStorage.removeItem('show_splash_logo');
        }

        const shouldShowSplash = isAuthTransition || isLogoClick;

        const splash = document.getElementById('uc-psc-splash');
        if (shouldShowSplash && splash) {
            // Reveal splash screen ONLY for auth transitions (login/logout) or logo clicks
            splash.style.opacity = '1';
            splash.style.visibility = 'visible';
            splash.style.pointerEvents = 'auto';

            window.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => {
                    splash.style.opacity = '0';
                    splash.style.visibility = 'hidden';
                    setTimeout(() => splash.remove(), 500);
                }, 400);
            });

            // Fallback safety timeout (1.2s max)
            setTimeout(() => {
                if (splash && splash.parentNode) {
                    splash.style.opacity = '0';
                    splash.style.visibility = 'hidden';
                    setTimeout(() => splash.remove(), 500);
                }
            }, 1200);
        } else if (splash) {
            // Remove immediately for any normal feature navigation, form submit, pagination, reload, etc.
            splash.remove();
        }
    })();
</script>
