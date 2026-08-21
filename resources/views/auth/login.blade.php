@extends('layouts.app')

@section('title', 'Login — UC PSC Management')

@section('content')
<div x-data="{ showForgotModal: false, showPassword: false }" class="min-h-screen flex items-center justify-center bg-[#F7F5FB] px-6 py-12 relative">
    <div class="max-w-md w-full">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-4 sm:gap-5 mb-2">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl sm:rounded-3xl grad-purple flex items-center justify-center shadow-lg flex-shrink-0">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" class="sm:w-10 sm:h-10">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                        <path d="M2 17l10 5 10-5"/>
                        <path d="M2 12l10 5 10-5"/>
                    </svg>
                </div>
                <div class="text-left">
                    <span class="text-3xl sm:text-4xl font-extrabold tracking-wide leading-none block">
                        <span class="text-[#3A2952]">UC </span><span class="text-orange">PSC</span>
                    </span>
                    <p class="text-[#5B4A73] text-xs sm:text-sm tracking-[0.2em] uppercase font-bold mt-1.5">Management System</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-xl p-8 border border-[#EDE1FA]">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-purple-deep mb-1.5">Masuk ke Sistem</h2>
                <p class="text-[#6B5B85] text-sm">Sistem Informasi Manajemen UC PSC</p>
            </div>

            @if($errors->any())
                <div class="bg-red-50 text-red-500 p-4 rounded-xl text-sm mb-6 flex items-center gap-2.5 border border-red-100">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-semibold text-[#5B4A73] mb-2">Email</label>
                    <div class="relative">
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                            placeholder="Masukkan email di sini"
                            class="w-full pl-4 pr-10 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm placeholder:text-[#A89CB8]">
                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-[#827299]">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-sm font-semibold text-[#5B4A73]">Password</label>
                    </div>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required
                            placeholder="Masukkan password di sini"
                            class="w-full pl-4 pr-11 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm placeholder:text-[#A89CB8]">
                        <button type="button" @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#827299] hover:text-purple-deep transition cursor-pointer">
                            <svg x-show="!showPassword" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg x-show="showPassword" x-cloak width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-purple-deep rounded border-[#D9C2F0] focus:ring-purple-deep accent-purple-deep">
                        <span class="text-xs sm:text-sm text-[#6B5B85]">Ingat saya</span>
                    </label>

                    {{-- Opsi Lupa Password --}}
                    <button type="button" @click="showForgotModal = true"
                        class="text-xs sm:text-sm font-semibold text-purple-deep hover:text-purple-deep/80 hover:underline transition cursor-pointer">
                        Lupa password?
                    </button>
                </div>

                <button type="submit" class="w-full grad-purple text-white font-bold py-3.5 rounded-xl hover:opacity-90 transition shadow-lg cursor-pointer flex items-center justify-center gap-2 mt-2">
                    <span>Masuk</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </button>
            </form>
        </div>

        <p class="text-center mt-6 text-xs text-[#6B5B85]">© {{ date('Y') }} Universitas Ciputra — Psychological Services Center</p>
    </div>

    {{-- ===== MODAL LUPA PASSWORD ===== --}}
    <div x-show="showForgotModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        {{-- Backdrop --}}
        <div x-show="showForgotModal" 
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="showForgotModal = false"
            class="fixed inset-0 bg-black/40 backdrop-blur-xs transition-opacity"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div x-show="showForgotModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-[#EDE1FA] p-6 sm:p-7">
                
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-purple-deep/10 text-purple-deep flex items-center justify-center flex-shrink-0">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-bold text-purple-deep" id="modal-title">Lupa Password Akun?</h3>
                        <p class="text-xs text-[#6B5B85] mt-1">Panduan reset kata sandi sistem UC PSC</p>
                    </div>
                    <button type="button" @click="showForgotModal = false" class="text-[#827299] hover:text-purple-deep p-1 rounded-lg transition cursor-pointer">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                <div class="mt-5 space-y-3.5 text-sm text-[#5B4A73]">
                    <div class="bg-[#F7F5FB] rounded-xl p-4 border border-[#EDE1FA] text-xs leading-relaxed">
                        <p class="font-semibold text-purple-deep mb-1">Akun Internal Terkelola</p>
                        <p>Untuk menjaga keamanan data psikologi dan rekam medis klien, reset kata sandi dilakukan secara terpusat oleh <strong>Administrator Sistem UC PSC</strong>.</p>
                    </div>

                    <div class="space-y-2 text-xs">
                        <p class="font-semibold text-[#5B4A73]">Langkah yang dapat Anda lakukan:</p>
                        <ol class="list-decimal list-inside space-y-1.5 text-[#6B5B85]">
                            <li>Hubungi Administrator atau Supervisor UC PSC melalui WhatsApp / email resmi.</li>
                            <li>Sebutkan nama lengkap dan alamat email yang terdaftar pada sistem.</li>
                            <li>Admin akan melakukan verifikasi dan memperbarui kata sandi baru untuk akun Anda.</li>
                        </ol>
                    </div>
                </div>

                <div class="mt-6 flex flex-col sm:flex-row items-center justify-end gap-2.5 pt-4 border-t border-[#EDE1FA]">
                    <button type="button" @click="showForgotModal = false"
                        class="w-full sm:w-auto px-5 py-2.5 rounded-xl text-xs font-semibold text-[#6B5B85] border border-[#D9C2F0] hover:bg-[#F7F5FB] transition cursor-pointer">
                        Kembali ke Login
                    </button>
                    <a href="mailto:psc@ciputra.ac.id?subject=Permintaan%20Reset%20Password%20Akun%20UC%20PSC"
                        class="w-full sm:w-auto bg-purple-deep text-white px-5 py-2.5 rounded-xl text-xs font-semibold hover:opacity-90 transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        Hubungi Admin via Email
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
