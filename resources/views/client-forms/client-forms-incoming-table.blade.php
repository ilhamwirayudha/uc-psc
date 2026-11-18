@extends('layouts.dashboard')

@section('title', 'Formulir Klien — UC PSC')
@section('page-title', 'Formulir Klien')
@section('page-subtitle', 'Kelola daftar tautan formulir pendaftaran mandiri & akses lembar hasil respon')

@section('content')
<div x-data="{
    search: '{{ addslashes(request('search', '')) }}',
    status: '{{ request('status', '') }}',
    copiedId: null,
    waClickedId: null,
    copyTimer: null,
    waTimer: null,

    copyToClipboard(url, id) {
        this.doCopy(url);
        this.copiedId = id;
        clearTimeout(this.copyTimer);
        this.copyTimer = setTimeout(() => this.copiedId = null, 3000);
    },

    triggerWhatsApp(id, url) {
        if (url) {
            this.doCopy(url);
        }
        this.waClickedId = id;
        clearTimeout(this.waTimer);
        this.waTimer = setTimeout(() => this.waClickedId = null, 3500);
    },

    doCopy(text) {
        try {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).catch(() => {
                    this.fallbackCopy(text);
                });
                return;
            }
        } catch (e) {
            // ignore
        }
        this.fallbackCopy(text);
    },

    fallbackCopy(text) {
        try {
            const el = document.createElement('textarea');
            el.value = text;
            el.setAttribute('readonly', '');
            el.style.position = 'fixed';
            el.style.top = '-9999px';
            el.style.left = '-9999px';
            document.body.appendChild(el);
            el.focus();
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
        } catch (err) {
            console.warn('Copy fallback error:', err);
        }
    }
}">


    {{-- Main Table Card (Exact same design language as Data Klien & Data Konselor) --}}
    <div class="glass-card rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm font-medium">
                <thead>
                    <tr class="bg-purple-deep/95 text-white font-semibold select-none border-b border-purple-900/30">
                        {{-- No. --}}
                        <th class="text-center align-middle px-3 py-3.5 font-semibold w-16 text-white">
                            No.
                        </th>

                        {{-- Nama Formulir --}}
                        <th class="text-left align-middle px-6 py-3.5 font-semibold text-white">
                            Nama Formulir
                        </th>

                        {{-- Aksi (Paling Kanan) --}}
                        <th class="text-center align-middle px-6 py-3.5 font-semibold w-64 min-w-[220px] text-white">
                            <span class="sr-only">Aksi</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F3EAFB]">
                    @forelse($forms as $index => $form)
                    @php
                        $invitationMsg = rawurlencode("Halo Bapak/Ibu, silakan lengkapi {$form['title']} UC PSC melalui tautan resmi kami berikut:\n{$form['url']}\n\nTerima kasih.");
                    @endphp
                    <tr class="hover:bg-[#FDFBFF] transition group">
                        {{-- No. --}}
                        <td class="text-center align-middle px-3 py-3.5 text-black font-medium">
                            {{ $loop->iteration }}
                        </td>

                        {{-- Nama Formulir --}}
                        <td class="text-left align-middle px-6 py-3.5">
                            <a href="{{ $form['url'] }}" target="_blank" class="font-medium text-black hover:text-orange transition block">
                                {{ $form['title'] }}
                            </a>
                        </td>

                        {{-- QUICK ACTIONS BUTTONS (HANYA ICON TANPA WARNA) --}}
                        <td class="text-center align-middle px-6 py-3.5 whitespace-nowrap">
                            <div class="inline-flex items-center justify-center gap-1.5">
                                {{-- 1. Kirim Link ke WhatsApp --}}
                                <div class="relative inline-flex items-center justify-center">
                                    <a href="https://wa.me/?text={{ $invitationMsg }}" target="_blank"
                                       @click="triggerWhatsApp({{ $form['id'] }}, '{{ $form['url'] }}')"
                                       class="w-9 h-9 rounded-lg border transition flex items-center justify-center shadow-2xs"
                                       :class="waClickedId === {{ $form['id'] }} ? 'border-emerald-500 bg-emerald-50 text-emerald-600' : 'border-slate-200 bg-white hover:bg-slate-100 text-slate-600 hover:text-slate-900'"
                                       title="Kirim link ke WhatsApp">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                    </a>

                                    {{-- Pop-up Tooltip WhatsApp --}}
                                    <div x-show="waClickedId === {{ $form['id'] }}"
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
                                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                         x-transition:leave="transition ease-in duration-150"
                                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                         x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
                                         class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 z-50 pointer-events-none whitespace-nowrap bg-purple-deep text-white text-[11px] font-bold px-3 py-1.5 rounded-lg shadow-xl">
                                        <span>Membuka WhatsApp...</span>
                                        <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-purple-deep"></div>
                                    </div>
                                </div>

                                {{-- 2. Copy Link --}}
                                <div class="relative inline-flex items-center justify-center">
                                    <button type="button"
                                            @click="copyToClipboard('{{ $form['url'] }}', {{ $form['id'] }})"
                                            class="w-9 h-9 rounded-lg border transition flex items-center justify-center shadow-2xs cursor-pointer"
                                            :class="copiedId === {{ $form['id'] }} ? 'border-emerald-500 bg-emerald-50 text-emerald-600' : 'border-slate-200 bg-white hover:bg-slate-100 text-slate-600 hover:text-slate-900'"
                                            :title="copiedId === {{ $form['id'] }} ? 'Tersalin ke clipboard!' : 'Copy Link'">
                                        <template x-if="copiedId === {{ $form['id'] }}">
                                            <svg class="text-emerald-600" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                        </template>
                                        <template x-if="copiedId !== {{ $form['id'] }}">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                        </template>
                                    </button>

                                    {{-- Pop-up Tooltip Copy Link --}}
                                    <div x-show="copiedId === {{ $form['id'] }}"
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
                                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                         x-transition:leave="transition ease-in duration-150"
                                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                         x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
                                         class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 z-50 pointer-events-none whitespace-nowrap bg-purple-deep text-white text-[11px] font-bold px-3 py-1.5 rounded-lg shadow-xl">
                                        <span>Link Tersalin!</span>
                                        <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-purple-deep"></div>
                                    </div>
                                </div>
                                {{-- 3. View Form --}}
                                <a href="{{ $form['url'] }}" target="_blank"
                                   class="w-9 h-9 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-slate-600 hover:text-slate-900 transition flex items-center justify-center shadow-2xs"
                                   title="View Form (Buka formulir di tab baru)">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                </a>

                                {{-- 4. View Results (Membuka sheets hasil) --}}
                                <a href="{{ route('client-forms.results', ['category' => $form['key']]) }}"
                                   class="w-9 h-9 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-slate-600 hover:text-slate-900 transition flex items-center justify-center shadow-2xs"
                                   title="View Results (Buka sheets hasil respon)">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M3 15h18"/><path d="M9 3v18"/><path d="M15 3v18"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-12 text-black font-medium">
                            Tidak ada formulir yang sesuai dengan pencarian.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</div>
@endsection
