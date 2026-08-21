@extends('layouts.dashboard')

@section('title', 'Hasil Tes — UC PSC')
@section('page-title', 'Hasil Tes Psikologi')
@section('page-subtitle', 'Upload dan kelola hasil tes klien')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <form method="GET" class="flex items-center gap-3 flex-1 max-w-lg">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-[#6B5B85]" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama tes atau klien..."
                class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-[#D9C2F0] bg-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent">
        </div>
        <button type="submit" class="bg-purple-deep text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:opacity-90 transition">Cari</button>
    </form>
    <a href="{{ route('test-results.create') }}" class="bg-orange text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:opacity-90 transition flex items-center gap-2 whitespace-nowrap">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Upload Hasil Tes
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#F7F5FB] text-[#5B4A73]">
                    <th class="text-center px-4 py-3.5 font-semibold w-14">No.</th>
                    <th class="text-left px-6 py-3.5 font-semibold">Klien</th>
                    <th class="text-left px-6 py-3.5 font-semibold">Nama Tes</th>
                    <th class="text-center px-6 py-3.5 font-semibold">Tanggal Tes</th>
                    <th class="text-left px-6 py-3.5 font-semibold">Oleh</th>
                    <th class="text-center px-6 py-3.5 font-semibold">File</th>
                    <th class="text-center px-6 py-3.5 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F3EAFB]">
                @forelse($testResults as $test)
                <tr class="hover:bg-[#FDFBFF] transition">
                    <td class="text-center px-4 py-3.5 text-[#6B5B85]">{{ $loop->iteration + ($testResults->currentPage() - 1) * $testResults->perPage() }}</td>
                    <td class="px-6 py-3.5 font-semibold text-[#2A2035]">{{ $test->client->name ?? '-' }}</td>
                    <td class="px-6 py-3.5 text-[#6B5B85]">{{ $test->test_name }}</td>
                    <td class="text-center px-6 py-3.5 text-[#6B5B85]">{{ $test->tested_at ? $test->tested_at->format('d M Y') : '-' }}</td>
                    <td class="px-6 py-3.5 text-[#6B5B85]">{{ $test->administrator->name ?? '-' }}</td>
                    <td class="px-6 py-3.5">
                        <div class="flex items-center justify-center">
                            @if($test->file_path)
                            <a href="{{ asset('storage/' . $test->file_path) }}" target="_blank" class="text-orange text-xs font-semibold hover:underline inline-flex items-center gap-1">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                Download
                            </a>
                            @else
                            <span class="text-[#6B5B85] text-xs">Tidak ada file</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-3.5">
                        <div class="flex items-center justify-center">
                            <form action="{{ route('test-results.destroy', $test) }}" method="POST" class="inline-flex items-center m-0 p-0" onsubmit="return confirm('Yakin ingin menghapus hasil tes ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-[#6B5B85] hover:text-red-500 hover:bg-red-50 transition cursor-pointer p-0 m-0 border-0 bg-transparent" title="Hapus">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-12 text-center text-[#6B5B85]">Belum ada hasil tes.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($testResults->hasPages())
    <div class="px-6 py-4 border-t border-[#EDE1FA]">{{ $testResults->links() }}</div>
    @endif
</div>
@endsection
