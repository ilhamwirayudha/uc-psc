<!DOCTYPE html>
<html lang="id" class="h-full bg-[#FAF9FD]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Berhasil - UC Psychological Service Center</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-ucpsc.png') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS / Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-full flex flex-col justify-center items-center p-4 sm:p-6 text-slate-800 antialiased selection:bg-purple-deep selection:text-white relative"
      x-data="{ 
          copied: false, 
          showConfirmModal: false,
          init() { 
              try { localStorage.removeItem('ucpsc_draft_anak'); } catch(e) {} 
          }, 
          copyTicket() { 
              navigator.clipboard.writeText('{{ $clientForm->ticket_number }}'); 
              this.copied = true; 
              setTimeout(() => this.copied = false, 2500); 
          } 
      }">

    {{-- Ambient UC PSC Brand Background Atmosphere --}}
    <div class="fixed inset-0 pointer-events-none -z-10 overflow-hidden">
        <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-gradient-to-b from-[#F3EAFB] via-[#FAF9FD]/80 to-transparent rounded-full blur-3xl opacity-90"></div>
        <div class="absolute top-48 -right-20 w-80 h-80 bg-purple-200/20 rounded-full blur-3xl"></div>
        <div class="absolute bottom-10 -left-20 w-72 h-72 bg-amber-100/30 rounded-full blur-3xl"></div>
    </div>

    <div class="w-full max-w-lg bg-white rounded-3xl p-6 sm:p-10 border border-[#EDE1FA] shadow-xl text-center relative overflow-hidden">
        
        {{-- Background decorative gradient --}}
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-[#F3EAFB] rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-emerald-50 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10">
            {{-- Success Icon Animation --}}
            <div class="w-20 h-20 bg-emerald-50 text-emerald-600 rounded-3xl mx-auto flex items-center justify-center mb-6 shadow-inner ring-8 ring-emerald-50/50">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>

            <span class="inline-block px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold uppercase tracking-wider mb-2">Pendaftaran Terkirim</span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mb-2">Terima Kasih!</h1>
            <p class="text-slate-600 text-sm sm:text-base leading-relaxed mb-6">
                Formulir riwayat hidup untuk ananda <strong class="text-slate-900">{{ $clientForm->client_name }}</strong> telah berhasil kami terima.
            </p>

            {{-- Ringkasan Pendaftaran Box --}}
            <div class="bg-[#FAF9FD] border border-[#EDE1FA] rounded-2xl p-4 mb-6 text-left">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 block mb-2">Ringkasan Pendaftaran</span>
                <div class="space-y-1.5 text-xs text-slate-700">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Nama Pendaftar:</span>
                        <strong class="text-slate-900 text-sm">{{ $clientForm->client_name }}</strong>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Layanan:</span>
                        <strong class="text-purple-deep">{{ $clientForm->form_type_label }}</strong>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">WhatsApp / Kontak:</span>
                        <strong class="text-slate-900 font-mono">{{ $clientForm->client_phone }}</strong>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Preferensi Konseling:</span>
                        <strong class="capitalize text-slate-800">{{ $clientForm->service_preference ?? 'Offline di UC PSC' }}</strong>
                    </div>
                </div>
            </div>


            {{-- Next Steps Notice --}}
            <div class="bg-purple-50/60 border border-purple-100 rounded-2xl p-4 mb-6 text-left text-xs leading-relaxed text-purple-900 space-y-1.5">
                <p class="font-bold flex items-center gap-1.5 text-purple-deep">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    Langkah Selanjutnya:
                </p>
                <p>1. Data Anda sedang ditinjau oleh staf operasional UC PSC untuk pencocokan konselor yang paling tepat.</p>
                <p>2. Admin kami akan menghubungi Anda via WhatsApp di nomor <strong class="underline">{{ $clientForm->client_phone }}</strong> dalam 1x24 jam kerja untuk penjadwalan.</p>
            </div>

            {{-- Back to Form Button --}}
            <div class="pt-1">
                <button type="button" 
                        @click="showConfirmModal = true"
                        class="w-full py-3.5 px-6 rounded-2xl bg-purple-deep hover:bg-[#3D1D66] active:scale-[0.99] text-white font-bold text-sm sm:text-base shadow-lg shadow-purple-deep/20 transition-all flex items-center justify-center gap-2 cursor-pointer group">
                    <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>
                    </svg>
                    <span>Kembali ke Halaman Utama Formulir</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Confirmation Modal --}}
    <div x-show="showConfirmModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="showConfirmModal = false">
        
        <div class="w-full max-w-sm bg-white rounded-3xl p-6 sm:p-7 shadow-2xl border border-purple-100 text-center relative"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            
            {{-- Question / Warning Icon --}}
            <div class="flex items-center justify-center mb-4 text-amber-500">
                <svg class="w-12 h-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>

            <h3 class="text-lg font-bold text-slate-900 mb-2">Kembali ke Halaman Utama?</h3>
            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-6">
                Apakah Anda yakin ingin kembali ke halaman utama formulir pendaftaran?
            </p>

            <div class="flex items-center gap-3">
                <button type="button" 
                        @click="showConfirmModal = false"
                        class="flex-1 py-2.5 px-4 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs sm:text-sm transition cursor-pointer">
                    Batal
                </button>
                <a href="{{ route('public.client-form.anak') }}"
                   class="flex-1 py-2.5 px-4 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-bold text-xs sm:text-sm transition shadow-sm shadow-purple-deep/20 inline-flex items-center justify-center">
                    Ya, Kembali
                </a>
            </div>
        </div>
    </div>

</body>
</html>
