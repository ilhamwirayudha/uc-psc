@extends('layouts.dashboard')

@section('title', 'Kasus Asesmen Psikotes — ' . $testResult->test_name . ' — UC PSC')
@section('page-title', 'Detail Kasus Psikotes')
@section('page-subtitle', 'Manajemen siklus pelaksanaan, koreksi, laporan psikologis, dan penyerahan hasil')
@section('back-url', route('test-results.index'))

@section('content')
<div x-data="{
    assignStaffModalOpen: false,
    uploadDocModalOpen: false,
    revisionModalOpen: false,
    deliveryModalOpen: false,
    confirmDeleteModalOpen: false,

    staffPengujiId: '{{ $testResult->booking?->staff_penguji_id ?? '' }}',
    staffKoreksiId: '{{ $testResult->booking?->staff_koreksi_id ?? '' }}',
    staffPelaporId: '{{ $testResult->booking?->staff_pelapor_id ?? '' }}',

    deliveryMethod: 'whatsapp',
    deliveryNotes: '',
    revisionNotes: ''
}" class="space-y-6">

    {{-- ========================================================================= --}}
    {{-- TOP HEADER KASUS PSIKOTES                                                 --}}
    {{-- ========================================================================= --}}
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-[#EDE1FA] shadow-xs">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            
            <div class="flex items-start gap-4 min-w-0">
                <div class="w-12 h-12 rounded-2xl bg-orange/15 text-orange flex items-center justify-center font-bold flex-shrink-0 shadow-xs">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                </div>

                <div class="min-w-0 space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-lg sm:text-xl font-extrabold text-[#2A2035] truncate">{{ $testResult->test_name }}</h1>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $testResult->status_badge_class }}">
                            {{ $testResult->status_label }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $testResult->method === 'online' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-purple-50 text-purple-700 border border-purple-200' }}">
                            {{ $testResult->method_label }}
                        </span>
                        @if($testResult->is_overdue)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-600 animate-pulse"></span>
                            Terlambat
                        </span>
                        @endif
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-y-1 gap-x-3 text-xs text-[#6B5B85]">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-[#827299]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            Klien: <a href="{{ route('clients.show', $testResult->client) }}" class="font-bold text-purple-deep hover:underline">{{ $testResult->client->name }}</a>
                        </span>
                        <span>•</span>
                        <span>Tes: <strong class="text-[#2A2035]">{{ $testResult->tested_at ? $testResult->tested_at->format('d M Y') : 'Belum dilaksanakan' }}</strong></span>
                        <span>•</span>
                        <span>Dibuat: {{ $testResult->created_at->format('d M Y, H:i') }}</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 pt-2 lg:pt-0 border-t lg:border-t-0 border-[#EDE1FA]">
                {{-- Tombol Download Berkas Utama jika tersedia --}}
                @if($testResult->file_path)
                <a href="{{ route('test-results.download', $testResult) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-white text-purple-deep border border-[#D9C2F0] hover:bg-[#F7F5FB] transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    <span>Unduh Laporan Utama</span>
                </a>
                @endif

                {{-- Hapus Kasus (Hanya Admin) --}}
                @if(auth()->user()->isAdmin())
                <button type="button" @click="confirmDeleteModalOpen = true" class="px-3 py-2 rounded-xl text-xs font-semibold text-red-600 hover:bg-red-50 border border-red-200 transition cursor-pointer flex items-center gap-1" title="Hapus Kasus Psikotes">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    <span>Hapus</span>
                </button>
                @endif
            </div>

        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- CONTEXTUAL ACTION BAR / LIFECYCLE TRANSITION STEPPER                      --}}
    {{-- ========================================================================= --}}
    <div class="bg-gradient-to-r from-purple-deep via-[#3D1D66] to-[#260E45] rounded-2xl p-5 text-white shadow-md space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <span class="text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-md bg-white/15 text-white inline-block mb-1">
                    Tahapan Saat Ini
                </span>
                <h3 class="text-base sm:text-lg font-bold flex items-center gap-2">
                    <span>{{ $testResult->status_label }}</span>
                    @if($testResult->status === \App\Models\TestResult::STATUS_REVISION)
                    <span class="text-rose-300 text-xs font-normal">⚠️ Memerlukan revisi dan perbaikan data</span>
                    @endif
                </h3>
            </div>

            {{-- Action buttons based on current status --}}
            <div class="flex flex-wrap items-center gap-2">
                @if($testResult->status === \App\Models\TestResult::STATUS_SCHEDULED)
                <form action="{{ route('test-results.status', $testResult) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="test_completed">
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-white text-purple-deep hover:bg-white/90 transition shadow-sm cursor-pointer flex items-center gap-1.5">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Tandai Tes Selesai</span>
                    </button>
                </form>

                @elseif($testResult->status === \App\Models\TestResult::STATUS_TEST_COMPLETED)
                <form action="{{ route('test-results.status', $testResult) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="waiting_assessment">
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-amber-400 text-purple-deep hover:bg-amber-300 transition shadow-sm cursor-pointer flex items-center gap-1.5">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Terima Hasil / Siap Dinilai</span>
                    </button>
                </form>

                @elseif($testResult->status === \App\Models\TestResult::STATUS_WAITING_ASSESSMENT)
                    @if(auth()->user()->isAdmin())
                    <button type="button" @click="assignStaffModalOpen = true" class="px-4 py-2 rounded-xl text-xs font-bold bg-amber-400 text-purple-deep hover:bg-amber-300 transition shadow-sm cursor-pointer flex items-center gap-1.5">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                        <span>Assign Staff Tim</span>
                    </button>
                    @endif
                    <form action="{{ route('test-results.status', $testResult) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="in_review">
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-white text-purple-deep hover:bg-white/90 transition shadow-sm cursor-pointer flex items-center gap-1.5">
                            <span>Mulai Penilaian →</span>
                        </button>
                    </form>

                @elseif($testResult->status === \App\Models\TestResult::STATUS_ASSIGNED)
                <form action="{{ route('test-results.status', $testResult) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="in_review">
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-white text-purple-deep hover:bg-white/90 transition shadow-sm cursor-pointer flex items-center gap-1.5">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        <span>Mulai Koreksi / Review</span>
                    </button>
                </form>

                @elseif($testResult->status === \App\Models\TestResult::STATUS_IN_REVIEW || $testResult->status === \App\Models\TestResult::STATUS_REVISION)
                <button type="button" @click="revisionModalOpen = true" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-rose-500/30 hover:bg-rose-500/40 text-rose-200 border border-rose-400/30 transition cursor-pointer flex items-center gap-1.5">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m18 15-6-6-6 6"/></svg>
                    <span>Perlu Revisi</span>
                </button>
                <form action="{{ route('test-results.status', $testResult) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="review_completed">
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-400 text-purple-deep hover:bg-emerald-300 transition shadow-sm cursor-pointer flex items-center gap-1.5">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Selesaikan Review</span>
                    </button>
                </form>

                @elseif($testResult->status === \App\Models\TestResult::STATUS_REVIEW_COMPLETED)
                <button type="button" @click="revisionModalOpen = true" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-white/10 hover:bg-white/20 text-white transition cursor-pointer">
                    Minta Revisi
                </button>
                <form action="{{ route('test-results.status', $testResult) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="result_ready">
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-400 text-purple-deep hover:bg-emerald-300 transition shadow-sm cursor-pointer flex items-center gap-1.5">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Tandai Hasil Siap Dikirim</span>
                    </button>
                </form>

                @elseif($testResult->status === \App\Models\TestResult::STATUS_RESULT_READY)
                <button type="button" @click="deliveryModalOpen = true" class="px-4 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-amber-400 to-orange-400 text-purple-deep hover:opacity-95 transition shadow-sm cursor-pointer flex items-center gap-1.5">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 2L11 13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    <span>Kirim Hasil ke Klien</span>
                </button>

                @elseif($testResult->status === \App\Models\TestResult::STATUS_RESULT_SENT)
                <form action="{{ route('test-results.status', $testResult) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-400 text-purple-deep hover:bg-emerald-300 transition shadow-sm cursor-pointer flex items-center gap-1.5">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <span>Tandai Seluruh Kasus Selesai</span>
                    </button>
                </form>

                @elseif($testResult->status === \App\Models\TestResult::STATUS_COMPLETED)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Kasus Tuntas Selesai</span>
                </span>
                @endif
            </div>
        </div>

        {{-- Mini Stepper Timeline Visual --}}
        @php
            $stages = [
                'scheduled' => 'Terjadwal',
                'test_completed' => 'Tes Selesai',
                'in_review' => 'Dinilai',
                'review_completed' => 'Review Selesai',
                'result_ready' => 'Siap Kirim',
                'result_sent' => 'Terkirim',
                'completed' => 'Selesai',
            ];
            $stageKeys = array_keys($stages);
            $currentStatus = $testResult->status;
            if ($currentStatus === 'waiting_assessment' || $currentStatus === 'assigned' || $currentStatus === 'revision') {
                $currentStatus = 'in_review';
            }
            $currentIdx = array_search($currentStatus, $stageKeys);
            if ($currentIdx === false) $currentIdx = 0;
        @endphp
        <div class="pt-2 border-t border-white/15">
            <div class="grid grid-cols-7 gap-1 text-center">
                @foreach($stages as $key => $label)
                @php $idx = array_search($key, $stageKeys); @endphp
                <div class="flex flex-col items-center">
                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold mb-1 transition
                        {{ $idx < $currentIdx ? 'bg-emerald-400 text-purple-deep' : ($idx === $currentIdx ? 'bg-amber-400 text-purple-deep ring-2 ring-white' : 'bg-white/10 text-white/50') }}">
                        @if($idx < $currentIdx) ✓ @else {{ $loop->iteration }} @endif
                    </div>
                    <span class="text-[9px] font-semibold truncate w-full {{ $idx <= $currentIdx ? 'text-white' : 'text-white/40' }}">{{ $label }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Catatan Revisi jika ada --}}
    @if($testResult->revision_notes)
    <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 shadow-xs">
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <div class="flex-1 text-xs">
                <h4 class="font-bold text-rose-900 mb-0.5">Catatan Permintaan Revisi:</h4>
                <p class="text-rose-800 leading-relaxed">{{ $testResult->revision_notes }}</p>
            </div>
        </div>
    </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- MAIN 2-COLUMN DETAILS GRID                                                --}}
    {{-- ========================================================================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- ==================== KOLOM KIRI (7 COLS) ==================== --}}
        <div class="lg:col-span-7 space-y-6">

            {{-- PANEL 1: INFORMASI LAYANAN & TARGET HASIL --}}
            <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-[#EDE1FA] pb-3">
                    <h3 class="font-bold text-purple-deep flex items-center gap-2 text-sm">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span>Informasi Layanan & Target Hasil</span>
                    </h3>
                    @if($testResult->booking)
                    <a href="{{ route('bookings.show', $testResult->booking) }}" class="text-xs font-bold text-orange hover:underline">
                        Lihat Booking #{{ $testResult->booking->id }} →
                    </a>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-[#827299] font-medium block">Klien</span>
                        <a href="{{ route('clients.show', $testResult->client) }}" class="font-bold text-purple-deep hover:underline mt-0.5 block">
                            {{ $testResult->client->name }}
                        </a>
                        <span class="text-[10px] text-[#827299] capitalize">{{ $testResult->client->jenis ?? 'Individu' }}</span>
                    </div>

                    <div>
                        <span class="text-[#827299] font-medium block">Metode Pelaksanaan</span>
                        <span class="font-bold text-[#2A2035] mt-0.5 inline-block">{{ $testResult->method_label }}</span>
                        <p class="text-[10px] text-[#827299] mt-0.5">
                            {{ $testResult->method === 'online' ? 'Klien mengerjakan formulir online / lembar digital' : 'Tes fisik langsung dengan kertas/lembar di UC PSC' }}
                        </p>
                    </div>

                    <div>
                        <span class="text-[#827299] font-medium block">Tanggal Pelaksanaan</span>
                        <span class="font-bold text-[#2A2035] mt-0.5 block">
                            {{ $testResult->tested_at ? $testResult->tested_at->format('d M Y') : 'Belum ditentukan' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-[#827299] font-medium block">Target Hasil</span>
                        <div class="mt-1 flex items-center gap-2">
                            <span class="font-bold text-[#2A2035]">
                                {{ $testResult->result_due_date ? $testResult->result_due_date->format('d M Y') : 'Tanpa target' }}
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] border {{ $testResult->sla_badge['class'] }}">
                                {{ $testResult->sla_badge['label'] }}
                            </span>
                        </div>
                    </div>
                </div>

                @if($testResult->result_summary)
                <div class="pt-3 border-t border-[#EDE1FA] text-xs">
                    <span class="text-[#827299] font-medium block mb-1">Ringkasan Hasil / Catatan:</span>
                    <p class="text-[#2A2035] leading-relaxed bg-[#F7F5FB] p-3 rounded-xl border border-[#EDE1FA]">
                        {{ $testResult->result_summary }}
                    </p>
                </div>
                @endif
            </div>

            {{-- PANEL 2: MANAJEMEN DOKUMEN & BERKAS ASESMEN --}}
            <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-[#EDE1FA] pb-3">
                    <div>
                        <h3 class="font-bold text-purple-deep flex items-center gap-2 text-sm">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                            <span>Dokumen & Berkas Kasus ({{ $testResult->documents->count() }})</span>
                        </h3>
                        <p class="text-[11px] text-[#827299]">Lembar jawaban, berkas koreksi/skoring, laporan psikologis, dan berkas final</p>
                    </div>

                    <button type="button" @click="uploadDocModalOpen = true" class="px-3 py-1.5 rounded-xl text-xs font-bold bg-purple-deep text-white hover:opacity-90 transition shadow-xs flex items-center gap-1 cursor-pointer">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        <span>Unggah Dokumen</span>
                    </button>
                </div>

                {{-- Daftar Dokumen --}}
                <div class="divide-y divide-[#F3EAFB]">
                    @forelse($testResult->documents as $doc)
                    <div class="py-3 flex items-center justify-between gap-3 hover:bg-[#FAF8FD] px-2 -mx-2 rounded-xl transition">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-purple-deep/10 text-purple-deep flex items-center justify-center font-bold text-xs flex-shrink-0">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-[#2A2035] truncate">{{ $doc->original_name }}</p>
                                <div class="flex items-center gap-2 text-[10px] text-[#827299]">
                                    <span class="px-1.5 py-0.2 rounded bg-purple-deep/10 text-purple-deep font-semibold">{{ $doc->document_type_label }}</span>
                                    <span>•</span>
                                    <span>{{ $doc->formatted_file_size }}</span>
                                    <span>•</span>
                                    <span>Oleh: {{ $doc->uploader->name ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('test-results.documents.download', [$testResult, $doc]) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold bg-orange/15 text-orange hover:bg-orange hover:text-white transition flex-shrink-0 flex items-center gap-1">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            <span>Unduh</span>
                        </a>
                    </div>
                    @empty
                    @if(!$testResult->file_path)
                    <div class="py-8 text-center text-xs text-[#827299]">
                        <p class="font-semibold text-[#2A2035] mb-1">Belum ada berkas dokumen yang diunggah</p>
                        <p>Klik tombol "+ Unggah Dokumen" di atas untuk menambahkan lembar jawaban, skoring, atau laporan psikologis.</p>
                    </div>
                    @endif
                    @endforelse

                    {{-- Berkas utama arsip lama jika belum ada di tabel documents --}}
                    @if($testResult->file_path && $testResult->documents->isEmpty())
                    <div class="py-3 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-orange/15 text-orange flex items-center justify-center font-bold text-xs flex-shrink-0">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-[#2A2035] truncate">{{ basename($testResult->file_path) }}</p>
                                <span class="text-[10px] text-[#827299]">Dokumen Hasil Psikotes (Arsip Utama)</span>
                            </div>
                        </div>

                        <a href="{{ route('test-results.download', $testResult) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold bg-orange/15 text-orange hover:bg-orange hover:text-white transition flex items-center gap-1">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            <span>Unduh</span>
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            {{-- PANEL 3: PENYERAHAN HASIL (DELIVERY STATUS) --}}
            <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-[#EDE1FA] pb-3">
                    <h3 class="font-bold text-purple-deep flex items-center gap-2 text-sm">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        <span>Distribusi & Penyerahan Hasil ke Klien</span>
                    </h3>

                    @if(in_array($testResult->status, [\App\Models\TestResult::STATUS_RESULT_READY, \App\Models\TestResult::STATUS_REVIEW_COMPLETED]))
                    <button type="button" @click="deliveryModalOpen = true" class="px-3 py-1.5 rounded-xl text-xs font-bold bg-gradient-to-r from-amber-500 to-orange-500 text-white hover:opacity-90 transition shadow-xs cursor-pointer">
                        Konfirmasi Penyerahan
                    </button>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <span class="text-[#827299] font-medium block">Status Penyerahan</span>
                        @if($testResult->delivered_at)
                        <span class="inline-flex items-center gap-1 font-bold text-emerald-700 mt-1">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Sudah Diserahkan
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 font-bold text-amber-700 mt-1">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            Belum Diserahkan
                        </span>
                        @endif
                    </div>

                    <div>
                        <span class="text-[#827299] font-medium block">Metode Pengiriman</span>
                        <span class="font-bold text-[#2A2035] mt-1 block">
                            {{ $testResult->delivery_method_label ?? '-' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-[#827299] font-medium block">Waktu & Petugas</span>
                        <span class="font-bold text-[#2A2035] mt-1 block">
                            @if($testResult->delivered_at)
                            {{ $testResult->delivered_at->format('d M Y, H:i') }}
                            <span class="text-[10px] text-[#827299] block font-normal">Oleh: {{ $testResult->deliveredBy->name ?? '-' }}</span>
                            @else
                            -
                            @endif
                        </span>
                    </div>
                </div>
            </div>

        </div>

        {{-- ==================== KOLOM KANAN (5 COLS) ==================== --}}
        <div class="lg:col-span-5 space-y-6">

            {{-- TIM PSIKOTES ASSIGNMENT CARD --}}
            <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-[#EDE1FA] pb-3">
                    <h3 class="font-bold text-purple-deep flex items-center gap-2 text-sm">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span>Tim Penanggung Jawab</span>
                    </h3>
                    @if(auth()->user()->isAdmin())
                    <button type="button" @click="assignStaffModalOpen = true" class="text-xs font-bold text-purple-deep hover:underline cursor-pointer">
                        Ubah Tim
                    </button>
                    @endif
                </div>

                <div class="space-y-3 text-xs">
                    {{-- 1. Staff Penguji --}}
                    <div class="p-3 rounded-xl bg-[#F7F5FB] border border-[#EDE1FA] flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-purple-deep/10 text-purple-deep flex items-center justify-center font-bold text-xs flex-shrink-0">
                                {{ substr($testResult->staff_penguji->name ?? '?', 0, 1) }}
                            </div>
                            <div>
                                <span class="text-[10px] text-[#827299] font-medium block">Staff Penguji</span>
                                <span class="font-bold text-[#2A2035]">{{ $testResult->staff_penguji->name ?? 'Belum diassign' }}</span>
                            </div>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded-full {{ $testResult->staff_penguji ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-500' }}">
                            Penguji
                        </span>
                    </div>

                    {{-- 2. Staff Koreksi --}}
                    <div class="p-3 rounded-xl bg-[#F7F5FB] border border-[#EDE1FA] flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                {{ substr($testResult->staff_koreksi->name ?? '?', 0, 1) }}
                            </div>
                            <div>
                                <span class="text-[10px] text-[#827299] font-medium block">Staff Koreksi</span>
                                <span class="font-bold text-[#2A2035]">{{ $testResult->staff_koreksi->name ?? 'Belum diassign' }}</span>
                            </div>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded-full {{ $testResult->staff_koreksi ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                            Koreksi
                        </span>
                    </div>

                    {{-- 3. Staff Pelapor --}}
                    <div class="p-3 rounded-xl bg-[#F7F5FB] border border-[#EDE1FA] flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                {{ substr($testResult->staff_pelapor->name ?? '?', 0, 1) }}
                            </div>
                            <div>
                                <span class="text-[10px] text-[#827299] font-medium block">Staff Pelapor</span>
                                <span class="font-bold text-[#2A2035]">{{ $testResult->staff_pelapor->name ?? 'Belum diassign' }}</span>
                            </div>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded-full {{ $testResult->staff_pelapor ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                            Pelapor
                        </span>
                    </div>
                </div>
            </div>

            {{-- JEJAK AKTIVITAS / AUDIT TRAIL TIMELINE --}}
            <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-4">
                <div class="border-b border-[#EDE1FA] pb-3">
                    <h3 class="font-bold text-purple-deep flex items-center gap-2 text-sm">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                        <span>Riwayat Aktivitas & Audit Trail</span>
                    </h3>
                    <p class="text-[11px] text-[#827299]">Pencatatan kronologis lifecycle asesmen psikotes</p>
                </div>

                <div class="relative pl-6 space-y-4 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-[#EDE1FA] max-h-96 overflow-y-auto pr-1">
                    @forelse($testResult->activities as $act)
                    <div class="relative text-xs">
                        <span class="absolute -left-6 top-1 w-2.5 h-2.5 rounded-full bg-purple-deep ring-4 ring-white"></span>
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-bold text-[#2A2035]">{{ $act->user->name ?? 'Sistem' }}</span>
                            <span class="text-[10px] text-[#827299]">{{ $act->created_at->format('d M, H:i') }}</span>
                        </div>
                        <p class="text-[#6B5B85] mt-0.5 leading-relaxed">{{ $act->description }}</p>
                    </div>
                    @empty
                    <div class="text-xs text-[#827299] py-4 text-center">Belum ada riwayat aktivitas tercatat.</div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL: ASSIGN STAFF TIM                                                   --}}
    {{-- ========================================================================= --}}
    <div x-cloak x-show="assignStaffModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="assignStaffModalOpen" x-transition.opacity class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="assignStaffModalOpen = false"></div>
        <div x-show="assignStaffModalOpen" x-transition class="relative bg-white rounded-2xl shadow-2xl border border-[#EDE1FA] max-w-md w-full p-6 z-10 space-y-4">
            <div class="flex items-center justify-between border-b border-[#EDE1FA] pb-3">
                <h3 class="font-bold text-purple-deep text-base">Tugaskan Tim Psikotes</h3>
                <button type="button" @click="assignStaffModalOpen = false" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>

            <form action="{{ route('test-results.assign-staff', $testResult) }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-[#5B4A73] mb-1">Staff Penguji</label>
                    <select name="staff_penguji_id" x-model="staffPengujiId" class="w-full px-3 py-2 border border-[#D9C2F0] rounded-xl bg-white text-[#2A2035] focus:ring-2 focus:ring-purple-deep">
                        <option value="">-- Belum Ditentukan --</option>
                        @foreach($staffList as $staff)
                        <option value="{{ $staff->id }}">{{ $staff->name }} ({{ $staff->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-[#5B4A73] mb-1">Staff Koreksi</label>
                    <select name="staff_koreksi_id" x-model="staffKoreksiId" class="w-full px-3 py-2 border border-[#D9C2F0] rounded-xl bg-white text-[#2A2035] focus:ring-2 focus:ring-purple-deep">
                        <option value="">-- Belum Ditentukan --</option>
                        @foreach($staffList as $staff)
                        <option value="{{ $staff->id }}">{{ $staff->name }} ({{ $staff->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-[#5B4A73] mb-1">Staff Pelapor</label>
                    <select name="staff_pelapor_id" x-model="staffPelaporId" class="w-full px-3 py-2 border border-[#D9C2F0] rounded-xl bg-white text-[#2A2035] focus:ring-2 focus:ring-purple-deep">
                        <option value="">-- Belum Ditentukan --</option>
                        @foreach($staffList as $staff)
                        <option value="{{ $staff->id }}">{{ $staff->name }} ({{ $staff->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#EDE1FA]">
                    <button type="button" @click="assignStaffModalOpen = false" class="px-4 py-2 rounded-xl font-semibold text-[#6B5B85] border border-[#D9C2F0] hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl font-bold bg-purple-deep text-white hover:opacity-90 shadow-sm">Simpan Penugasan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL: UNGGAH DOKUMEN BARU                                                --}}
    {{-- ========================================================================= --}}
    <div x-cloak x-show="uploadDocModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="uploadDocModalOpen" x-transition.opacity class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="uploadDocModalOpen = false"></div>
        <div x-show="uploadDocModalOpen" x-transition class="relative bg-white rounded-2xl shadow-2xl border border-[#EDE1FA] max-w-md w-full p-6 z-10 space-y-4">
            <div class="flex items-center justify-between border-b border-[#EDE1FA] pb-3">
                <h3 class="font-bold text-purple-deep text-base">Unggah Berkas Dokumen</h3>
                <button type="button" @click="uploadDocModalOpen = false" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>

            <form action="{{ route('test-results.documents.store', $testResult) }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-[#5B4A73] mb-1">Jenis Dokumen <span class="text-red-500">*</span></label>
                    <select name="document_type" required class="w-full px-3 py-2 border border-[#D9C2F0] rounded-xl bg-white text-[#2A2035] focus:ring-2 focus:ring-purple-deep">
                        <option value="test_form">Formulir / Lembar Tes Fisik</option>
                        <option value="answer_sheet">Lembar Jawaban / Scan Lembar Jawaban</option>
                        <option value="scoring">Skoring & Perhitungan Penilaian</option>
                        <option value="psychological_report">Draf Laporan Psikologis</option>
                        <option value="final_result" selected>Laporan Hasil Psikotes Final</option>
                        <option value="supporting_document">Dokumen Pendukung Lainnya</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-[#5B4A73] mb-1">Pilih Berkas <span class="text-red-500">*</span></label>
                    <input type="file" name="file" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls"
                        class="w-full px-3 py-2 border border-[#D9C2F0] rounded-xl bg-white text-xs">
                    <span class="text-[10px] text-[#827299] mt-1 block">Maksimal 10 MB (PDF, DOC, DOCX, JPG, PNG, XLSX)</span>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#EDE1FA]">
                    <button type="button" @click="uploadDocModalOpen = false" class="px-4 py-2 rounded-xl font-semibold text-[#6B5B85] border border-[#D9C2F0] hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl font-bold bg-purple-deep text-white hover:opacity-90 shadow-sm">Unggah Dokumen</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL: PERMINTAAN REVISI                                                  --}}
    {{-- ========================================================================= --}}
    <div x-cloak x-show="revisionModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="revisionModalOpen" x-transition.opacity class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="revisionModalOpen = false"></div>
        <div x-show="revisionModalOpen" x-transition class="relative bg-white rounded-2xl shadow-2xl border border-rose-200 max-w-md w-full p-6 z-10 space-y-4">
            <div class="flex items-center justify-between border-b border-rose-100 pb-3">
                <h3 class="font-bold text-rose-900 text-base flex items-center gap-2">
                    <span>⚠️ Minta Revisi Laporan</span>
                </h3>
                <button type="button" @click="revisionModalOpen = false" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>

            <form action="{{ route('test-results.status', $testResult) }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="status" value="revision">

                <div>
                    <label class="block font-bold text-rose-900 mb-1">Catatan Revisi / Bagian yang Perlu Diperbaiki <span class="text-red-500">*</span></label>
                    <textarea name="revision_notes" rows="4" required x-model="revisionNotes"
                        placeholder="Jelaskan secara spesifik poin atau bagian analisis/skoring yang perlu diperbaiki oleh tim..."
                        class="w-full px-3 py-2 border border-rose-300 rounded-xl bg-white text-[#2A2035] focus:ring-2 focus:ring-rose-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-rose-100">
                    <button type="button" @click="revisionModalOpen = false" class="px-4 py-2 rounded-xl font-semibold text-[#6B5B85] border border-gray-200 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl font-bold bg-rose-600 text-white hover:bg-rose-700 shadow-sm">Kirim Permintaan Revisi</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL: KONFIRMASI PENYERAHAN HASIL (DELIVERY)                             --}}
    {{-- ========================================================================= --}}
    <div x-cloak x-show="deliveryModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="deliveryModalOpen" x-transition.opacity class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="deliveryModalOpen = false"></div>
        <div x-show="deliveryModalOpen" x-transition class="relative bg-white rounded-2xl shadow-2xl border border-[#EDE1FA] max-w-md w-full p-6 z-10 space-y-4">
            <div class="flex items-center justify-between border-b border-[#EDE1FA] pb-3">
                <h3 class="font-bold text-purple-deep text-base">Konfirmasi Penyerahan Hasil</h3>
                <button type="button" @click="deliveryModalOpen = false" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>

            <p class="text-xs text-[#6B5B85] leading-relaxed">
                Apakah hasil psikotes ini telah selesai diperiksa dan secara resmi diberikan kepada klien <strong>{{ $testResult->client->name }}</strong>?
            </p>

            <form action="{{ route('test-results.deliver', $testResult) }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-[#5B4A73] mb-1.5">Metode Penyerahan <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="border rounded-xl p-2.5 text-center cursor-pointer transition flex flex-col items-center gap-1"
                            :class="deliveryMethod === 'whatsapp' ? 'border-emerald-500 bg-emerald-50 text-emerald-800 font-bold' : 'border-gray-200 text-[#6B5B85]'">
                            <input type="radio" name="delivery_method" value="whatsapp" x-model="deliveryMethod" class="hidden">
                            <span>WhatsApp</span>
                        </label>
                        <label class="border rounded-xl p-2.5 text-center cursor-pointer transition flex flex-col items-center gap-1"
                            :class="deliveryMethod === 'email' ? 'border-blue-500 bg-blue-50 text-blue-800 font-bold' : 'border-gray-200 text-[#6B5B85]'">
                            <input type="radio" name="delivery_method" value="email" x-model="deliveryMethod" class="hidden">
                            <span>Email</span>
                        </label>
                        <label class="border rounded-xl p-2.5 text-center cursor-pointer transition flex flex-col items-center gap-1"
                            :class="deliveryMethod === 'offline' ? 'border-purple-500 bg-purple-50 text-purple-800 font-bold' : 'border-gray-200 text-[#6B5B85]'">
                            <input type="radio" name="delivery_method" value="offline" x-model="deliveryMethod" class="hidden">
                            <span>Offline Fisik</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-[#5B4A73] mb-1">Catatan Penyerahan (Opsional)</label>
                    <textarea name="notes" rows="2" x-model="deliveryNotes" placeholder="Catatan bukti penyerahan, penerima, atau nomor resi..."
                        class="w-full px-3 py-2 border border-[#D9C2F0] rounded-xl bg-white text-[#2A2035] focus:ring-2 focus:ring-purple-deep"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#EDE1FA]">
                    <button type="button" @click="deliveryModalOpen = false" class="px-4 py-2 rounded-xl font-semibold text-[#6B5B85] border border-[#D9C2F0] hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl font-bold bg-gradient-to-r from-amber-500 to-orange-500 text-white hover:opacity-90 shadow-sm">Ya, Konfirmasi Penyerahan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL: KONFIRMASI HAPUS KASUS                                             --}}
    {{-- ========================================================================= --}}
    @if(auth()->user()->isAdmin())
    <div x-cloak x-show="confirmDeleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="confirmDeleteModalOpen" x-transition.opacity class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="confirmDeleteModalOpen = false"></div>
        <div x-show="confirmDeleteModalOpen" x-transition class="relative bg-white rounded-2xl shadow-2xl border border-red-200 max-w-sm w-full p-6 z-10 space-y-4 text-center">
            <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
            </div>
            <div>
                <h3 class="font-bold text-red-900 text-base">Hapus Kasus Psikotes?</h3>
                <p class="text-xs text-[#6B5B85] mt-1">Tindakan ini akan menghapus data kasus psikotes serta seluruh berkas dokumen terkait secara permanen.</p>
            </div>
            <div class="flex items-center justify-center gap-2 pt-2">
                <button type="button" @click="confirmDeleteModalOpen = false" class="w-1/2 py-2 rounded-xl text-xs font-semibold text-[#6B5B85] border border-gray-200 hover:bg-gray-50">Batal</button>
                <form action="{{ route('test-results.destroy', $testResult) }}" method="POST" class="w-1/2 m-0 p-0">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full py-2 rounded-xl text-xs font-bold bg-red-600 text-white hover:bg-red-700 shadow-sm cursor-pointer">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
