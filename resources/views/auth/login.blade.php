@extends('layouts.app')

@section('content')
<div x-data="{ showForgotModal: false, showPassword: false, remember: false }" class="min-h-screen flex flex-col items-center justify-center bg-[#F3EBF9] px-6 py-12 relative overflow-hidden selection:bg-purple-light selection:text-white">
    
    {{-- ===== HIGH-PERFORMANCE STATIC AURORA MESH BACKGROUND (0% CPU/GPU OVERHEAD) ===== --}}
    {{-- Top-Left Purple / Lavender Gradient Glow --}}
    <div class="absolute -top-[20%] -left-[15%] w-[850px] h-[850px] bg-gradient-to-br from-[#8E51E8]/35 via-[#B07DF5]/25 to-transparent rounded-full blur-[100px] pointer-events-none transform-gpu"></div>
    
    {{-- Bottom-Right Warm Peach / Orange Glow --}}
    <div class="absolute -bottom-[20%] -right-[15%] w-[900px] h-[900px] bg-gradient-to-tl from-[#FB923C]/30 via-[#FDBA74]/25 to-[#C084FC]/15 rounded-full blur-[110px] pointer-events-none transform-gpu"></div>
    
    {{-- Subtle Center Atmospheric Tint --}}
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[700px] bg-gradient-to-tr from-[#E9D5FF]/25 via-[#FED7AA]/15 to-transparent rounded-full blur-[90px] pointer-events-none transform-gpu"></div>

    <div class="max-w-md w-full relative z-10">
        
        {{-- ===== OFFICIAL UC PSC LOGO (CENTERED ABOVE CONTAINER) ===== --}}
        <div class="text-center mb-7 select-none">
            <img src="{{ asset('images/logo-ucpsc.png') }}" alt="Logo Resmi UC PSC" class="h-32 sm:h-38 w-auto mx-auto object-contain">
        </div>

        {{-- ===== TRANSLUCENT GLASSMORPHISM CARD CONTAINER (GPU OPTIMIZED) ===== --}}
        <div class="relative">
            {{-- Soft Outer Ambient Glow --}}
            <div class="absolute -inset-2 bg-gradient-to-br from-white/70 via-purple-200/30 to-orange/20 rounded-[2.8rem] blur-xl opacity-70 pointer-events-none"></div>

            <div class="relative bg-white/45 backdrop-blur-xl rounded-[2.5rem] shadow-[0_30px_70px_rgba(74,35,128,0.15),0_0_0_1px_rgba(255,255,255,0.9)_inset,0_20px_40px_rgba(255,255,255,0.7)_inset] p-8 sm:p-10 border border-white/80 overflow-hidden transform-gpu">
                
                {{-- Specular Top Highlight Edge --}}
                <div class="absolute top-0 inset-x-12 h-[1.5px] bg-gradient-to-r from-transparent via-white to-transparent pointer-events-none"></div>

                {{-- Title & Subtitle --}}
                <div class="text-center mb-7 relative z-10">
                    <h2 class="text-2xl font-extrabold text-[#2A1D42] mb-1 tracking-tight">Masuk ke Sistem</h2>
                    <p class="text-[#6B5B85] text-sm font-medium">Sistem Manajemen UC PSC</p>
                </div>

                @if($errors->any())
                    <div class="bg-red-50/90 text-red-600 p-4 rounded-2xl text-sm mb-6 flex items-center gap-2.5 border border-red-200/80 shadow-xs relative z-10">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" class="space-y-5 relative z-10">
                    @csrf

                    {{-- Kolom Email --}}
                    <div>
                        <label for="email" class="block text-xs sm:text-sm font-bold text-[#3B2C52] mb-2">Email</label>
                        <div class="relative">
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="{{ old('email') }}" 
                                required 
                                placeholder="Masukkan email di sini"
                                class="w-full px-4 py-3.5 rounded-2xl bg-white/60 hover:bg-white/80 focus:bg-white/95 text-sm text-[#2A1D42] placeholder:text-[#8E7D9E]/40 font-medium border border-white/90 focus:outline-none focus:ring-4 focus:ring-purple-light/20 focus:border-purple-light transition-all shadow-[inset_0_1px_3px_rgba(255,255,255,0.9),0_2px_8px_rgba(74,35,128,0.02)]"
                            >
                        </div>
                    </div>

                    {{-- Kolom Password --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="password" class="block text-xs sm:text-sm font-bold text-[#3B2C52]">Password</label>
                        </div>
                        <div class="relative">
                            <input 
                                :type="showPassword ? 'text' : 'password'" 
                                id="password" 
                                name="password" 
                                required
                                placeholder="Masukkan password di sini"
                                class="w-full pl-4 pr-12 py-3.5 rounded-2xl bg-white/60 hover:bg-white/80 focus:bg-white/95 text-sm text-[#2A1D42] placeholder:text-[#8E7D9E]/40 font-medium border border-white/90 focus:outline-none focus:ring-4 focus:ring-purple-light/20 focus:border-purple-light transition-all shadow-[inset_0_1px_3px_rgba(255,255,255,0.9),0_2px_8px_rgba(74,35,128,0.02)]"
                            >
                            {{-- Interactive Password Toggle Button --}}
                            <button 
                                type="button" 
                                @click="showPassword = !showPassword"
                                :aria-label="showPassword ? 'Sembunyikan password' : 'Lihat password'"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#827299] hover:text-purple-deep transition cursor-pointer rounded-lg p-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-purple-deep"
                            >
                                {{-- Mata Tersilang saat Password Tertutupi --}}
                                <svg x-show="!showPassword" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                {{-- Mata Terbuka saat Password Terlihat --}}
                                <svg x-show="showPassword" x-cloak width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Ingat Saya & Lupa Password --}}
                    <div class="flex items-center justify-between pt-1">
                        {{-- Custom Styled Frosted Checkbox with Crisp Checkmark --}}
                        <label class="flex items-center gap-2.5 cursor-pointer select-none group py-1.5 transition-colors">
                            <div class="relative flex items-center justify-center">
                                <input 
                                    type="checkbox" 
                                    name="remember" 
                                    x-model="remember"
                                    class="sr-only"
                                >
                                <div 
                                    :class="remember ? 'border-purple-deep bg-purple-deep shadow-xs' : 'border-purple-200/90 bg-white/70 hover:bg-white'" 
                                    class="w-5 h-5 rounded-md border-2 transition-all flex items-center justify-center"
                                >
                                    <svg x-show="remember" x-cloak class="w-3.5 h-3.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                </div>
                            </div>
                            <span class="text-xs sm:text-sm font-medium text-[#5B4A73] group-hover:text-[#2A1D42] transition-colors">Ingat saya</span>
                        </label>

                        {{-- Forgot Password Link (Pure Text Transition) --}}
                        <button 
                            type="button" 
                            @click="showForgotModal = true"
                            class="text-xs sm:text-sm font-bold text-purple-deep hover:text-orange transition-colors cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-purple-deep"
                        >
                            Lupa password?
                        </button>
                    </div>

                    {{-- Tombol Masuk --}}
                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-[#4A2380] via-[#5B2C98] to-[#7C4DBE] hover:from-[#260E45] hover:via-[#35145E] hover:to-[#451B78] text-white font-bold py-3.5 px-6 rounded-2xl active:scale-[0.98] transition-all duration-200 shadow-[0_12px_28px_rgba(74,35,128,0.3),0_1px_2px_rgba(255,255,255,0.4)_inset] hover:shadow-[0_12px_28px_rgba(38,14,69,0.45)] cursor-pointer flex items-center justify-center gap-2 mt-2 focus:outline-none focus-visible:ring-4 focus-visible:ring-purple-light/30 min-h-[48px] relative overflow-hidden border border-white/25"
                    >
                        {{-- Gloss Light Sheen Reflection --}}
                        <div class="absolute top-0 inset-x-0 h-1/2 bg-gradient-to-b from-white/30 via-white/10 to-transparent pointer-events-none rounded-t-2xl"></div>
                        <span class="relative z-10 tracking-wide">Masuk</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="relative z-10"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </button>
                </form>
            </div>
        </div>

        {{-- Footer Copyright --}}
        <p class="text-center mt-6 text-xs text-[#6B5B85] font-medium">© Copyright {{ date('Y') }}, Universitas Ciputra</p>
    </div>

    {{-- ===== MODAL LUPA PASSWORD (CONSISTENT GLASS STYLE) ===== --}}
    <div x-show="showForgotModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        {{-- Backdrop with Blur --}}
        <div x-show="showForgotModal" 
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="showForgotModal = false"
            class="fixed inset-0 bg-[#2A2035]/50 backdrop-blur-md transition-opacity"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div x-show="showForgotModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-[2.4rem] bg-white/90 backdrop-blur-xl text-left shadow-[0_30px_70px_rgba(74,35,128,0.18),0_0_0_1px_rgba(255,255,255,0.95)_inset,0_2px_6px_rgba(255,255,255,0.85)_inset] transition-all sm:my-8 sm:w-full sm:max-w-md border border-white/90 p-6 sm:p-7">
                
                {{-- Specular Top Sheen Highlight --}}
                <div class="absolute top-0 inset-x-10 h-[1.5px] bg-gradient-to-r from-transparent via-white to-transparent pointer-events-none"></div>

                {{-- Close Button --}}
                <button type="button" @click="showForgotModal = false" class="absolute top-5 right-5 text-[#827299] hover:text-purple-deep p-1.5 rounded-xl hover:bg-white/60 transition cursor-pointer z-10" title="Tutup">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>

                {{-- Centered 3D Liquid Gem Lock Icon --}}
                <div class="text-center mb-5">
                    <div class="w-14 h-14 rounded-[1.4rem] grad-purple text-white flex items-center justify-center shadow-[0_12px_24px_rgba(74,35,128,0.22),0_0_0_1px_rgba(255,255,255,0.5)_inset,0_2px_4px_rgba(255,255,255,0.7)_inset] mx-auto mb-3.5 relative overflow-hidden">
                        {{-- Convex Glass Sheen --}}
                        <div class="absolute top-0 inset-x-0 h-1/2 bg-gradient-to-b from-white/35 via-white/10 to-transparent pointer-events-none rounded-t-[1.4rem]"></div>
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="relative z-10 drop-shadow-xs"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <h3 class="text-xl font-extrabold text-[#2A2035] tracking-tight mb-1" id="modal-title">Lupa Password Akun?</h3>
                    <p class="text-xs text-[#6B5B85]">Panduan reset kata sandi sistem UC PSC</p>
                </div>

                {{-- Single Minimalist Frosted Info Card --}}
                <div class="bg-white/70 rounded-2xl p-4 sm:p-5 border border-white/80 text-xs sm:text-[13px] leading-relaxed mb-6 shadow-[inset_0_2px_4px_rgba(74,35,128,0.02)] space-y-2.5 text-justify">
                    <p class="text-[#4A3E5C] font-semibold">
                        Demi menjaga keamanan data dan rekam medis klien, reset kata sandi dikelola secara terpusat oleh <strong class="text-purple-deep">Administrator UC PSC</strong>.
                    </p>
                    <p class="text-[#6B5B85]">
                        Silakan hubungi admin melalui WhatsApp resmi untuk verifikasi identitas dan penerbitan kata sandi akun baru Anda.
                    </p>
                </div>

                {{-- Symmetrical 2-Column Action Buttons --}}
                <div class="grid grid-cols-2 gap-3 pt-3 border-t border-purple-100/70">
                    <button type="button" @click="showForgotModal = false"
                        class="w-full py-2.5 sm:py-3 rounded-xl text-xs sm:text-sm font-bold text-[#6B5B85] hover:text-[#2A2035] bg-white/80 hover:bg-white border border-[#EDE1FA] shadow-xs hover:shadow-sm transition cursor-pointer flex items-center justify-center">
                        Kembali
                    </button>
                    <a href="https://wa.me/6288232013931?text=Halo%20Admin%20UC%20PSC,%20saya%20membutuhkan%20bantuan%20reset%20password%20akun%20saya."
                        target="_blank"
                        rel="noopener noreferrer"
                        class="w-full py-2.5 sm:py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white rounded-xl text-xs sm:text-sm font-bold transition shadow-[0_10px_22px_rgba(16,185,129,0.28)] border border-white/20 flex items-center justify-center gap-1.5 cursor-pointer relative overflow-hidden group">
                        {{-- Gloss Sheen Reflection --}}
                        <div class="absolute top-0 inset-x-0 h-1/2 bg-gradient-to-b from-white/30 via-white/10 to-transparent pointer-events-none rounded-t-xl"></div>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="relative z-10"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                        <span class="relative z-10">WhatsApp Admin</span>
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
