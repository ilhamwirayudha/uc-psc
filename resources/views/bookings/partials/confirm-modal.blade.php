    {{-- ========================================================================= --}}
    {{-- SUB-MODAL: KONFIRMASI KELUAR / BATALKAN SAAT SEDANG DALAM MODE EDIT       --}}
    {{-- STRICT RULE: Dilarang menutup modal saat klik backdrop/overlay            --}}
    {{-- ========================================================================= --}}
    <div x-cloak x-show="showConfirmCloseModal" class="fixed inset-0 z-[70] flex items-center justify-center p-4">
        {{-- Backdrop blur overlay (TANPA handler penutup) --}}
        <div x-show="showConfirmCloseModal"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>

        {{-- Dialog Box --}}
        <div x-show="showConfirmCloseModal"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative bg-white rounded-2xl shadow-2xl border border-[#EDE1FA] max-w-md w-full p-6 sm:p-7 text-center space-y-4 z-10"
            @click.stop>
            
            {{-- Centered Top Warning Badge --}}
            <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center mx-auto shadow-xs">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>

            <div class="space-y-1.5">
                <h3 class="text-lg font-bold text-[#2A2035]">
                    Batalkan Perubahan di <span class="text-purple-deep" x-text="editingCardTitle"></span>?
                </h3>
                <p class="text-xs sm:text-sm text-gray-500 leading-relaxed max-w-xs mx-auto">
                    Anda sedang berada dalam mode edit data. Jika menutup formulir sekarang, seluruh perubahan yang belum disimpan akan dibatalkan.
                </p>
            </div>

            {{-- 50:50 Symmetrical Action Buttons --}}
            <div class="grid grid-cols-2 gap-3 pt-2">
                <button type="button" @click="cancelCloseAttempt()"
                    class="w-full py-2.5 rounded-xl border border-[#D9C2F0] text-black font-semibold text-sm hover:border-purple-deep hover:text-purple-deep transition cursor-pointer">
                    Lanjut Mengedit
                </button>
                <button type="button" @click="confirmDiscardAndClose()"
                    class="w-full py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-semibold transition shadow-xs cursor-pointer">
                    Ya, Batalkan
                </button>
            </div>
        </div>
    </div>
