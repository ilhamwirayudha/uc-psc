@extends('layouts.dashboard')

@section('title', 'Manajemen Kasus Asesmen & Hasil Psikotes — UC PSC')
@section('page-title', 'Kasus Asesmen & Hasil Psikotes')
@section('page-subtitle', 'Monitoring lifecycle pelaksanaan, koreksi, laporan psikologis, dan penyerahan hasil')

@section('content')
{{-- ========================================================================= --}}
{{-- METRIK MONITORING OPERASIONAL KASUS PSIKOTES                              --}}
{{-- ========================================================================= --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
    {{-- Card 1: Total (Light Gray) --}}
    <div style="background-color: #f3f4f6;" class="bg-gray-100 rounded-2xl p-4 border border-gray-200 shadow-xs flex flex-col justify-between">
        <span class="text-[11px] font-bold text-[#827299]">Total Kasus</span>
        <div class="flex items-baseline justify-between mt-1">
            <span class="text-2xl font-extrabold text-gray-900">{{ $stats['total'] }}</span>
            <span class="text-[10px] font-bold text-gray-700 bg-white border border-gray-200 px-2 py-0.5 rounded-full shadow-2xs">Semua</span>
        </div>
    </div>

    {{-- Card 2: Terjadwal (Light Gray) --}}
    <div style="background-color: #f3f4f6;" class="bg-gray-100 rounded-2xl p-4 border border-gray-200 shadow-xs flex flex-col justify-between">
        <span class="text-[11px] font-bold text-[#827299]">Terjadwal</span>
        <div class="flex items-baseline justify-between mt-1">
            <span class="text-2xl font-extrabold text-gray-900">{{ $stats['scheduled'] }}</span>
            <span class="text-[10px] font-bold text-gray-700 bg-white border border-gray-200 px-2 py-0.5 rounded-full shadow-2xs">Jadwal</span>
        </div>
    </div>

    {{-- Card 3: Sedang Dinilai (Light Gray) --}}
    <div style="background-color: #f3f4f6;" class="bg-gray-100 rounded-2xl p-4 border border-gray-200 shadow-xs flex flex-col justify-between">
        <span class="text-[11px] font-bold text-[#827299]">Sedang Dinilai</span>
        <div class="flex items-baseline justify-between mt-1">
            <span class="text-2xl font-extrabold text-gray-900">{{ $stats['in_review'] }}</span>
            <span class="text-[10px] font-bold text-gray-700 bg-white border border-gray-200 px-2 py-0.5 rounded-full shadow-2xs">Review</span>
        </div>
    </div>

    {{-- Card 4: Perlu Revisi (Kuning) --}}
    <div style="background-color: #eab308;" class="bg-yellow-500 rounded-2xl p-4 shadow-xs flex flex-col justify-between text-white">
        <span class="text-[11px] font-bold text-white/90 drop-shadow-xs">Perlu Revisi</span>
        <div class="flex items-baseline justify-between mt-1">
            <span class="text-2xl font-extrabold text-white drop-shadow-xs">{{ $stats['revision'] }}</span>
            <span style="background-color: rgba(0, 0, 0, 0.15);" class="text-[10px] font-bold text-white bg-black/15 px-2 py-0.5 rounded-full drop-shadow-xs">Revisi</span>
        </div>
    </div>

    {{-- Card 5: Siap Dikirim (Hijau) --}}
    <div style="background-color: #059669;" class="bg-emerald-600 rounded-2xl p-4 shadow-xs flex flex-col justify-between text-white">
        <span class="text-[11px] font-bold text-white/80">Siap Dikirim</span>
        <div class="flex items-baseline justify-between mt-1">
            <span class="text-2xl font-extrabold text-white">{{ $stats['ready'] }}</span>
            <span style="background-color: rgba(255, 255, 255, 0.2);" class="text-[10px] font-bold text-white bg-white/20 px-2 py-0.5 rounded-full">Final</span>
        </div>
    </div>

    {{-- Card 6: Terlambat / Overdue (Merah) --}}
    <div style="background-color: #dc2626;" class="bg-red-600 rounded-2xl p-4 shadow-xs flex flex-col justify-between text-white">
        <span class="text-[11px] font-bold text-white/80">Terlambat</span>
        <div class="flex items-baseline justify-between mt-1">
            <span class="text-2xl font-extrabold text-white">{{ $stats['overdue'] }}</span>
            <span style="background-color: rgba(255, 255, 255, 0.2);" class="text-[10px] font-bold text-white bg-white/20 px-2 py-0.5 rounded-full">Overdue</span>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- FILTER & SEARCH CONTROLS                                                  --}}
{{-- ========================================================================= --}}
<div class="bg-white rounded-2xl p-4 border border-[#EDE1FA] shadow-xs mb-6">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
        <form method="GET" action="{{ route('test-results.index') }}" class="flex flex-col md:flex-row md:items-center gap-3 flex-1">
            
            {{-- Search Bar --}}
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-[#827299]" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama tes atau klien..."
                    class="w-full pl-10 pr-4 py-2 rounded-xl border border-[#D9C2F0] bg-white text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            {{-- Filter Status --}}
            <div class="w-full md:w-44">
                <select name="status" class="w-full px-3 py-2 rounded-xl border border-[#D9C2F0] bg-white text-xs focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035]">
                    <option value="">-- Semua Status --</option>
                    @foreach(\App\Models\TestResult::STATUS_LABELS as $key => $label)
                    <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Metode --}}
            <div class="w-full md:w-32">
                <select name="method" class="w-full px-3 py-2 rounded-xl border border-[#D9C2F0] bg-white text-xs focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035]">
                    <option value="">-- Semua Metode --</option>
                    <option value="offline" {{ request('method') === 'offline' ? 'selected' : '' }}>Offline</option>
                    <option value="online" {{ request('method') === 'online' ? 'selected' : '' }}>Online</option>
                </select>
            </div>

            {{-- Filter Staff --}}
            <div class="w-full md:w-44">
                <select name="staff_id" class="w-full px-3 py-2 rounded-xl border border-[#D9C2F0] bg-white text-xs focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035]">
                    <option value="">-- Semua Staff Tim --</option>
                    @foreach($staffList as $staff)
                    <option value="{{ $staff->id }}" {{ request('staff_id') == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-purple-deep text-white hover:opacity-90 transition cursor-pointer">
                    Terapkan
                </button>
                @if(request()->hasAny(['search', 'status', 'method', 'staff_id', 'overdue', 'date_from', 'date_to']))
                <a href="{{ route('test-results.index') }}" class="px-3 py-2 rounded-xl text-xs font-semibold text-[#6B5B85] hover:bg-[#F7F5FB] transition" title="Reset Filter">
                    Reset
                </a>
                @endif
            </div>
        </form>

        <div class="flex items-center justify-end">
            <button type="button" @click="$dispatch('open-test-result-modal')" class="bg-purple-deep text-white px-4 py-2 rounded-xl text-xs font-bold hover:opacity-90 transition flex items-center gap-1.5 shadow-xs cursor-pointer whitespace-nowrap">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Upload Hasil / Buat Kasus</span>
            </button>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- TABEL MONITORING KASUS ASESMEN PSIKOTES                                    --}}
{{-- ========================================================================= --}}
<div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-xs sm:text-sm">
            <thead>
                <tr class="bg-[#F7F5FB] text-[#5B4A73] border-b border-[#EDE1FA]">
                    <th class="text-center px-4 py-3.5 font-semibold w-12">No.</th>
                    <th class="text-left px-5 py-3.5 font-semibold">Klien</th>
                    <th class="text-left px-5 py-3.5 font-semibold">Nama Tes</th>
                    <th class="text-center px-4 py-3.5 font-semibold">Pelaksanaan</th>
                    <th class="text-center px-3 py-3.5 font-semibold">Metode</th>
                    <th class="text-left px-4 py-3.5 font-semibold">Tim Staff</th>
                    <th class="text-center px-4 py-3.5 font-semibold">Target Hasil</th>
                    <th class="text-center px-4 py-3.5 font-semibold">Status</th>
                    <th class="text-center px-4 py-3.5 font-semibold w-28">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F3EAFB]">
                @forelse($testResults as $test)
                <tr class="hover:bg-[#FDFBFF] transition">
                    {{-- 1. No --}}
                    <td class="text-center px-4 py-3.5 text-[#6B5B85] font-medium">
                        {{ $loop->iteration + ($testResults->currentPage() - 1) * $testResults->perPage() }}
                    </td>

                    {{-- 2. Klien --}}
                    <td class="px-5 py-3.5">
                        <a href="{{ route('clients.show', $test->client) }}" class="font-bold text-[#2A2035] hover:text-purple-deep hover:underline block truncate max-w-[160px]">
                            {{ $test->client->name ?? '-' }}
                        </a>
                        <span class="text-[10px] text-[#827299] capitalize">{{ $test->client->jenis ?? 'Individu' }}</span>
                    </td>

                    {{-- 3. Nama Tes --}}
                    <td class="px-5 py-3.5">
                        <a href="{{ route('test-results.show', $test) }}" class="font-bold text-purple-deep hover:underline block truncate max-w-[170px]">
                            {{ $test->test_name }}
                        </a>
                        @if($test->result_summary)
                        <span class="text-[10px] text-[#827299] truncate block max-w-[170px]">{{ $test->result_summary }}</span>
                        @endif
                    </td>

                    {{-- 4. Tanggal Tes --}}
                    <td class="text-center px-4 py-3.5 text-[#6B5B85] whitespace-nowrap">
                        {{ $test->tested_at ? $test->tested_at->format('d M Y') : '-' }}
                    </td>

                    {{-- 5. Metode --}}
                    <td class="text-center px-3 py-3.5 whitespace-nowrap">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $test->method === 'online' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-purple-50 text-purple-700 border-purple-200' }}">
                            {{ $test->method_label }}
                        </span>
                    </td>

                    {{-- 6. Tim Staff --}}
                    <td class="px-4 py-3.5 text-[11px] leading-tight">
                        <div class="space-y-0.5">
                            <div><span class="text-[#827299]">Koreksi:</span> <strong class="text-[#2A2035]">{{ $test->staff_koreksi->name ?? 'Belum diassign' }}</strong></div>
                            <div><span class="text-[#827299]">Pelapor:</span> <strong class="text-[#2A2035]">{{ $test->staff_pelapor->name ?? 'Belum diassign' }}</strong></div>
                        </div>
                    </td>

                    {{-- 7. Target Hasil --}}
                    <td class="text-center px-4 py-3.5 whitespace-nowrap">
                        <span class="px-2.5 py-1 rounded-full text-[10px] border {{ $test->sla_badge['class'] }}">
                            {{ $test->sla_badge['label'] }}
                        </span>
                        @if($test->result_due_date)
                        <span class="text-[10px] text-[#827299] block mt-0.5">{{ $test->result_due_date->format('d M Y') }}</span>
                        @endif
                    </td>

                    {{-- 8. Status Lifecycle --}}
                    <td class="text-center px-4 py-3.5 whitespace-nowrap">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $test->status_badge_class }}">
                            {{ $test->status_label }}
                        </span>
                    </td>

                    {{-- 9. Aksi --}}
                    <td class="px-4 py-3.5 whitespace-nowrap">
                        <div class="flex items-center justify-center gap-1.5">
                            {{-- Tombol Detail --}}
                            <a href="{{ route('test-results.show', $test) }}" class="p-1.5 rounded-lg text-purple-deep hover:bg-purple-deep/10 transition" title="Lihat Detail Kasus">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>

                            {{-- Tombol Unduh Berkas jika ada --}}
                            @if($test->file_path)
                            <a href="{{ route('test-results.download', $test) }}" class="p-1.5 rounded-lg text-orange hover:bg-orange/10 transition" title="Unduh Berkas Laporan">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            </a>
                            @endif

                            {{-- Tombol Hapus (Hanya Admin) --}}
                            @if(auth()->user()->isAdmin())
                            <form action="{{ route('test-results.destroy', $test) }}" method="POST" class="inline-flex items-center m-0 p-0" onsubmit="return confirm('Yakin ingin menghapus kasus asesmen psikotes ini beserta seluruh berkasnya?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-lg text-[#827299] hover:text-red-600 hover:bg-red-50 transition cursor-pointer border-0 bg-transparent" title="Hapus Kasus">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-12 text-center text-[#827299]">
                        <p class="font-bold text-[#2A2035] mb-1">Belum ada kasus asesmen psikotes yang sesuai</p>
                        <p class="text-xs">Klik tombol "+ Upload Hasil / Buat Kasus" di atas untuk menambahkan data baru.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($testResults->hasPages())
    <div class="px-6 py-4 border-t border-[#EDE1FA] bg-[#FAF8FD]">
        {{ $testResults->links() }}
    </div>
    @endif
</div>
@endsection
