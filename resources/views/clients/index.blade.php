@extends('layouts.dashboard')

@section('title', 'Klien — UC PSC')
@section('page-title', 'Data Klien')
@section('page-subtitle', 'Kelola data klien UC PSC')

@section('content')
<div x-data="{
    search: '{{ addslashes(request('search', '')) }}',
    jenis: '{{ request('jenis', '') }}',
    sort: '{{ request('sort', 'created_at') }}',
    direction: '{{ request('direction', 'desc') }}',
    loading: false,
    timer: null,

    onSearch() {
        clearTimeout(this.timer);
        this.timer = setTimeout(() => {
            this.fetchResults();
        }, 300);
    },

    clearSearch() {
        this.search = '';
        this.fetchResults();
    },

    async fetchResults() {
        this.loading = true;
        try {
            const params = new URLSearchParams();
            if (this.search && this.search.trim() !== '') params.set('search', this.search.trim());
            if (this.jenis && this.jenis !== '') params.set('jenis', this.jenis);
            if (this.sort) params.set('sort', this.sort);
            if (this.direction) params.set('direction', this.direction);

            const queryString = params.toString();
            const url = '{{ route('clients.index') }}' + (queryString ? '?' + queryString : '');

            window.history.replaceState({}, '', url);

            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error('Fetch failed');

            const text = await response.text();
            const doc = new DOMParser().parseFromString(text, 'text/html');

            const newContent = doc.getElementById('client-data-container');
            const target = document.getElementById('client-data-container');

            if (newContent && target) {
                target.innerHTML = newContent.innerHTML;
            }
        } catch (e) {
            console.error('Error fetching clients:', e);
        } finally {
            this.loading = false;
        }
    }
}">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <form @submit.prevent="fetchResults()" method="GET" class="flex flex-wrap items-center gap-3 flex-1 max-w-3xl">
            <div class="relative flex-1 min-w-[220px] sm:min-w-[280px]">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#6B5B85]" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text"
                    name="search"
                    x-model="search"
                    @input="onSearch()"
                    @keydown.enter.prevent="fetchResults()"
                    placeholder="Cari nama klien..."
                    class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-[#D9C2F0] bg-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent">

                {{-- Clear button saat ada teks --}}
                <button type="button"
                    x-show="search.length > 0 && !loading"
                    @click="clearSearch()"
                    x-cloak
                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-[#827299] hover:text-purple-deep transition p-0.5 rounded-md hover:bg-purple-deep/10"
                    title="Hapus pencarian">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>

                {{-- Loading spinner saat pencarian --}}
                <div x-show="loading" x-cloak class="absolute right-3.5 top-1/2 -translate-y-1/2 text-purple-deep flex items-center justify-center">
                    <svg class="animate-spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                </div>
            </div>

            {{-- Filter Tipe Klien --}}
            <select name="jenis"
                x-model="jenis"
                @change="fetchResults()"
                class="px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] bg-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#5B4A73] flex-shrink-0">
                <option value="">Semua Tipe Klien</option>
                <option value="individual">Individu</option>
                <option value="group">Kelompok</option>
                <option value="company">Perusahaan</option>
            </select>
        </form>
        <button type="button" @click="$dispatch('open-client-modal')" class="bg-orange text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:opacity-90 transition flex items-center gap-2 whitespace-nowrap cursor-pointer">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Klien
        </button>
    </div>

    {{-- Dynamic Content Area (Filter Indicator + Table + Pagination) --}}
    <div id="client-data-container" :class="{ 'opacity-70 pointer-events-none transition-opacity duration-150': loading }">
        {{-- Active Filter / Sort Indicators --}}
        @if(request('sort') || request('search') || request('jenis'))
        <div class="flex flex-wrap items-center gap-2 mb-4 text-xs text-[#6B5B85] bg-white px-4 py-2.5 rounded-xl border border-[#EDE1FA]">
            <span class="font-semibold text-[#5B4A73] flex items-center gap-1">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Filter/Urutan:
            </span>
            @if(request('search'))
                <span class="bg-gray-100 text-[#5B4A73] px-2.5 py-1 rounded-lg font-medium">Cari: "{{ request('search') }}"</span>
            @endif
            @if(request('jenis'))
                <span class="bg-gray-100 text-[#5B4A73] px-2.5 py-1 rounded-lg font-medium">
                    Tipe: {{ request('jenis') === 'individual' ? 'Individu' : (request('jenis') === 'group' ? 'Kelompok' : 'Perusahaan') }}
                </span>
            @endif
            @if(request('sort') === 'id')
                <span class="bg-gray-100 text-[#5B4A73] px-2.5 py-1 rounded-lg font-medium">Urut ID: {{ $direction === 'asc' ? 'Terkecil (1 → 9)' : 'Terbesar (9 → 1)' }}</span>
            @elseif(request('sort') === 'name')
                <span class="bg-gray-100 text-[#5B4A73] px-2.5 py-1 rounded-lg font-medium">Urut Nama: {{ $direction === 'asc' ? 'A → Z' : 'Z → A' }}</span>
            @elseif(request('sort') === 'jenis')
                <span class="bg-gray-100 text-[#5B4A73] px-2.5 py-1 rounded-lg font-medium">
                    @if($direction === 'group')
                        Urut Tipe: Kelompok → Individu → Perusahaan
                    @elseif($direction === 'company' || $direction === 'desc')
                        Urut Tipe: Perusahaan → Kelompok → Individu
                    @else
                        Urut Tipe: Individu → Kelompok → Perusahaan
                    @endif
                </span>
            @elseif(request('sort') === 'creator')
                <span class="bg-gray-100 text-[#5B4A73] px-2.5 py-1 rounded-lg font-medium">Urut Pembuat: {{ $direction === 'asc' ? 'A → Z' : 'Z → A' }}</span>
            @elseif(request('sort') === 'created_at')
                <span class="bg-gray-100 text-[#5B4A73] px-2.5 py-1 rounded-lg font-medium">Urut Waktu: {{ $direction === 'asc' ? 'Paling Awal (Terlama)' : 'Paling Baru (Terkini)' }}</span>
            @endif
            <a href="{{ route('clients.index') }}" class="text-[#6B5B85] hover:text-red-500 hover:underline font-semibold ml-auto flex items-center gap-1 transition">
                <span>Reset Filter & Urutan</span>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </a>
        </div>
        @endif

        {{-- Table --}}
        <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#F7F5FB] text-[#5B4A73] select-none">
                            {{-- No. Column with Numeric Sort --}}
                            <th class="text-center align-middle px-3 py-3.5 font-semibold text-xs w-16">
                                <div class="flex items-center justify-center">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'id', 'direction' => ($sort === 'id' && $direction === 'asc') ? 'desc' : 'asc', 'page' => 1]) }}"
                                        class="inline-flex items-center gap-1 hover:text-purple-deep group transition cursor-pointer"
                                        title="Urutkan nomor urut angka: 1→9 atau 9→1">
                                        <span class="{{ $sort === 'id' ? 'text-purple-deep font-bold' : 'text-[#827299]' }}">No.</span>
                                        <span class="inline-flex items-center justify-center w-4 h-4 rounded-md transition {{ $sort === 'id' ? 'bg-purple-deep/10 text-purple-deep' : 'text-[#B4A5C7] group-hover:text-purple-deep group-hover:bg-purple-deep/5' }}">
                                            @if($sort === 'id')
                                                @if($direction === 'asc')
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                                @else
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                                @endif
                                            @else
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                            @endif
                                        </span>
                                    </a>
                                </div>
                            </th>
                            
                            {{-- Nama Column with Alphabetical Sort --}}
                            <th class="text-left align-middle px-6 py-3.5 font-semibold">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => ($sort === 'name' && $direction === 'asc') ? 'desc' : 'asc', 'page' => 1]) }}"
                                    class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer"
                                    title="Urutkan alfabetis A-Z / Z-A">
                                    <span class="{{ $sort === 'name' ? 'text-purple-deep font-bold' : '' }}">Nama Klien</span>
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition {{ $sort === 'name' ? 'bg-purple-deep/10 text-purple-deep' : 'text-[#B4A5C7] group-hover:text-purple-deep group-hover:bg-purple-deep/5' }}">
                                        @if($sort === 'name')
                                            @if($direction === 'asc')
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                            @else
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                            @endif
                                        @else
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                        @endif
                                    </span>
                                </a>
                            </th>

                            {{-- Tipe Klien Column Sort (3-State Rotation: Individu -> Kelompok -> Perusahaan) --}}
                            @php
                                $nextJenisDirection = 'individual';
                                if ($sort === 'jenis') {
                                    if ($direction === 'individual' || $direction === 'asc') {
                                        $nextJenisDirection = 'group';
                                    } elseif ($direction === 'group') {
                                        $nextJenisDirection = 'company';
                                    } else {
                                        $nextJenisDirection = 'individual';
                                    }
                                }
                            @endphp
                            <th class="text-center align-middle px-4 py-3.5 font-semibold">
                                <div class="flex items-center justify-center">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'jenis', 'direction' => $nextJenisDirection, 'page' => 1]) }}"
                                        class="inline-flex items-center justify-center gap-1.5 hover:text-purple-deep group transition cursor-pointer whitespace-nowrap"
                                        title="Rotasi urutan tipe klien: Individu → Kelompok → Perusahaan">
                                        <span class="w-5 shrink-0" aria-hidden="true"></span>
                                        <span class="{{ $sort === 'jenis' ? 'text-purple-deep font-bold' : '' }}">Tipe Klien</span>
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-md shrink-0 transition {{ $sort === 'jenis' ? 'bg-purple-deep/10 text-purple-deep' : 'text-[#B4A5C7] group-hover:text-purple-deep group-hover:bg-purple-deep/5' }}">
                                            @if($sort === 'jenis')
                                                @if($direction === 'group')
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                                @elseif($direction === 'company' || $direction === 'desc')
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                                @else
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                                @endif
                                            @else
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                            @endif
                                        </span>
                                    </a>
                                </div>
                            </th>

                            {{-- Creator Column Sort --}}
                            <th class="text-center align-middle px-4 py-3.5 font-semibold">
                                <div class="flex items-center justify-center">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'creator', 'direction' => ($sort === 'creator' && $direction === 'asc') ? 'desc' : 'asc', 'page' => 1]) }}"
                                        class="inline-flex items-center justify-center gap-1.5 hover:text-purple-deep group transition cursor-pointer whitespace-nowrap"
                                        title="Urutkan berdasarkan pembuat">
                                        <span class="w-5 shrink-0" aria-hidden="true"></span>
                                        <span class="{{ $sort === 'creator' ? 'text-purple-deep font-bold' : '' }}">Dibuat oleh</span>
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-md shrink-0 transition {{ $sort === 'creator' ? 'bg-purple-deep/10 text-purple-deep' : 'text-[#B4A5C7] group-hover:text-purple-deep group-hover:bg-purple-deep/5' }}">
                                            @if($sort === 'creator')
                                                @if($direction === 'asc')
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                                @else
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                                @endif
                                            @else
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                            @endif
                                        </span>
                                    </a>
                                </div>
                            </th>

                            {{-- Waktu Pendaftaran Column with Oldest/Newest Sort --}}
                            <th class="text-center align-middle px-4 py-3.5 font-semibold">
                                <div class="flex items-center justify-center">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => ($sort === 'created_at' && $direction === 'asc') ? 'desc' : 'asc', 'page' => 1]) }}"
                                        class="inline-flex items-center justify-center gap-1.5 hover:text-purple-deep group transition cursor-pointer whitespace-nowrap"
                                        title="Urutkan waktu pendaftaran: Paling Baru atau Paling Awal/Tua">
                                        <span class="w-5 shrink-0" aria-hidden="true"></span>
                                        <span class="{{ $sort === 'created_at' ? 'text-purple-deep font-bold' : '' }}">Waktu Pendaftaran</span>
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-md shrink-0 transition {{ $sort === 'created_at' ? 'bg-purple-deep/10 text-purple-deep' : 'text-[#B4A5C7] group-hover:text-purple-deep group-hover:bg-purple-deep/5' }}">
                                            @if($sort === 'created_at')
                                                @if($direction === 'asc')
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                                @else
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                                @endif
                                            @else
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                            @endif
                                        </span>
                                    </a>
                                </div>
                            </th>

                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F3EAFB]">
                        @forelse($clients as $client)
                        <tr class="hover:bg-[#FDFBFF] transition">
                            <td class="text-center align-middle px-3 py-3.5 text-[#6B5B85] font-medium">
                                {{ $loop->iteration + ($clients->currentPage() - 1) * $clients->perPage() }}
                            </td>
                            <td class="text-left align-middle px-6 py-3.5">
                                <a href="{{ route('clients.show', $client) }}" class="font-semibold text-purple-deep hover:text-orange transition block">{{ $client->name }}</a>
                            </td>
                            <td class="text-center align-middle px-4 py-3.5 text-[#6B5B85] whitespace-nowrap">
                                {{ ($client->jenis ?? 'individual') === 'company' ? 'Perusahaan' : (($client->jenis ?? 'individual') === 'group' ? 'Kelompok' : 'Individu') }}
                            </td>
                            <td class="text-center align-middle px-4 py-3.5 text-[#6B5B85] whitespace-nowrap">{{ $client->creator->name ?? '-' }}</td>
                            <td class="text-center align-middle px-4 py-3.5 text-[#6B5B85] whitespace-nowrap">
                                {{ $client->created_at ? $client->created_at->format('d M Y, H:i') : '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-[#6B5B85]">
                                <svg class="mx-auto mb-3 text-[#D9C2F0]" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                Belum ada data klien.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($clients->hasPages())
            <div class="px-6 py-4 border-t border-[#EDE1FA]">
                {{ $clients->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
