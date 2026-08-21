@extends('layouts.dashboard')

@section('title', 'Dashboard — UC PSC')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan operasional UC PSC')

@section('content')
{{-- Stat Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
    <div class="bg-white rounded-2xl shadow-sm p-5 border border-[#EDE1FA] card-hover">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-deep/10 flex items-center justify-center">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#4A2380" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
                <p class="text-xs text-[#6B5B85] font-medium">Total Klien</p>
                <p class="text-2xl font-bold text-purple-deep">{{ $stats['total_clients'] }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-5 border border-[#EDE1FA] card-hover">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-rose-50 flex items-center justify-center">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#E11D48" stroke-width="1.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="23" y1="11" x2="17" y2="17"/><line x1="17" y1="11" x2="23" y2="17"/></svg>
            </div>
            <div>
                <p class="text-xs text-[#6B5B85] font-medium">Klien Belum Di-assign</p>
                <p class="text-2xl font-bold text-purple-deep">{{ $stats['unassigned_clients'] }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-5 border border-[#EDE1FA] card-hover">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-orange/10 flex items-center justify-center">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#E38B2C" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <div>
                <p class="text-xs text-[#6B5B85] font-medium">Konselor Aktif</p>
                <p class="text-2xl font-bold text-purple-deep">{{ $stats['total_counselors'] }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-5 border border-[#EDE1FA] card-hover">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
            <div>
                <p class="text-xs text-[#6B5B85] font-medium">Total Sesi Konseling</p>
                <p class="text-2xl font-bold text-purple-deep">{{ $stats['total_sessions'] }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-5 border border-[#EDE1FA] card-hover">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="1.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
            </div>
            <div>
                <p class="text-xs text-[#6B5B85] font-medium">Klien Ter-assign</p>
                <p class="text-2xl font-bold text-purple-deep">{{ $stats['assigned_clients'] }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-5 border border-[#EDE1FA] card-hover">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div>
                <p class="text-xs text-[#6B5B85] font-medium">Sesi Terjadwal</p>
                <p class="text-2xl font-bold text-purple-deep">{{ $stats['scheduled_sessions'] }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-5 border border-[#EDE1FA] card-hover">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div>
                <p class="text-xs text-[#6B5B85] font-medium">Sesi Selesai</p>
                <p class="text-2xl font-bold text-purple-deep">{{ $stats['completed_sessions'] }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Recent Activity --}}
<div class="grid lg:grid-cols-2 gap-6">
    {{-- Recent Counseling Records --}}
    <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden flex flex-col">
        <div class="px-6 py-4 border-b border-[#EDE1FA] flex items-center justify-between bg-white z-10">
            <div class="flex items-center gap-2.5">
                <h2 class="font-bold text-purple-deep">Konseling Terbaru</h2>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-deep/10 text-purple-deep">{{ $recentRecords->count() }}</span>
            </div>
            <a href="{{ route('counseling-records.index') }}" class="text-xs text-orange font-semibold hover:underline">Lihat Semua →</a>
        </div>
        <div class="divide-y divide-[#F3EAFB] max-h-[380px] overflow-y-auto custom-scrollbar">
            @forelse($recentRecords as $record)
            <div class="px-6 py-3.5 flex items-center gap-3 hover:bg-[#FAF8FD] transition">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0 {{ $record->type === 'whatsapp' ? 'bg-emerald-500' : ($record->type === 'online' ? 'bg-blue-500' : 'bg-purple-deep') }}">
                    {{ $record->type === 'whatsapp' ? 'WA' : ($record->type === 'online' ? 'ON' : 'TM') }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-[#2A2035] truncate">{{ $record->client->name ?? '-' }}</p>
                    <p class="text-xs text-[#6B5B85] truncate">Konselor: {{ $record->counselor->name ?? '-' }}</p>
                </div>
                <span class="px-2 py-1 rounded-full text-[10px] font-semibold flex-shrink-0 {{ $record->status === 'completed' ? 'bg-emerald-50 text-emerald-600' : ($record->status === 'scheduled' ? 'bg-amber-50 text-amber-600' : 'bg-red-50 text-red-500') }}">
                    {{ ucfirst($record->status) }}
                </span>
            </div>
            @empty
            <div class="px-6 py-8 text-center text-[#6B5B85] text-sm">Belum ada catatan konseling.</div>
            @endforelse
        </div>
    </div>

    {{-- Recent Clients --}}
    <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden flex flex-col">
        <div class="px-6 py-4 border-b border-[#EDE1FA] flex items-center justify-between bg-white z-10">
            <div class="flex items-center gap-2.5">
                <h2 class="font-bold text-purple-deep">Klien Terbaru</h2>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-deep/10 text-purple-deep">{{ $recentClients->count() }}</span>
            </div>
            <a href="{{ route('clients.index') }}" class="text-xs text-orange font-semibold hover:underline">Lihat Semua →</a>
        </div>
        <div class="divide-y divide-[#F3EAFB] max-h-[380px] overflow-y-auto custom-scrollbar">
            @forelse($recentClients as $client)
            <div class="px-6 py-3.5 flex items-center gap-3 hover:bg-[#FAF8FD] transition">
                <div class="w-8 h-8 rounded-full bg-purple-deep/10 flex items-center justify-center text-purple-deep text-xs font-bold flex-shrink-0">
                    {{ substr($client->name, 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-[#2A2035] truncate">{{ $client->name }}</p>
                    <p class="text-xs text-[#6B5B85]">{{ $client->phone ?? 'Tanpa nomor' }} · {{ ucfirst(str_replace('_', ' ', $client->source)) }}</p>
                </div>
                <p class="text-[11px] text-[#6B5B85] flex-shrink-0">{{ $client->created_at->diffForHumans() }}</p>
            </div>
            @empty
            <div class="px-6 py-8 text-center text-[#6B5B85] text-sm">Belum ada klien terdaftar.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
