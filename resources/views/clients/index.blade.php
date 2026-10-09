@extends('layouts.dashboard')

@section('title', 'Daftar Klien — UC PSC')
@section('page-title', 'Daftar Klien')
@section('page-subtitle', 'Kelola direktori data klien UC PSC')


@section('content')
<div x-data="{
    search: '{{ addslashes(request('search', '')) }}',
    sort: '{{ request('sort', 'created_at') }}',
    direction: '{{ request('direction', 'desc') }}',
    perPage: '{{ request('per_page', $perPage ?? 25) }}',
    loading: false,
    timer: null,

    init() {
        window.addEventListener('popstate', () => {
            window.location.reload();
        });
    },

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

    setPerPage(val) {
        this.perPage = val;
        this.fetchResults();
    },

    async goToUrl(url) {
        this.loading = true;
        try {
            window.history.pushState({}, '', url);
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
            window.location.href = url;
        } finally {
            this.loading = false;
        }
    },

    async fetchResults() {
        this.loading = true;
        try {
            const params = new URLSearchParams();
            if (this.search && this.search.trim() !== '') params.set('search', this.search.trim());
            if (this.sort) params.set('sort', this.sort);
            if (this.direction) params.set('direction', this.direction);
            if (this.perPage) params.set('per_page', this.perPage);

            const queryString = params.toString();
            const url = '{{ route('clients.index') }}' + (queryString ? '?' + queryString : '');

            window.history.pushState({}, '', url);

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
                    placeholder="Cari nama, no. HP, atau email klien..."
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
        </form>

        {{-- Tombol Tambah Klien Baru --}}
        <a href="{{ route('clients.create') }}"
            class="px-4 py-2.5 bg-orange text-white text-sm font-semibold rounded-xl hover:bg-orange/90 transition shadow-xs flex-shrink-0 text-center cursor-pointer flex items-center justify-center gap-2">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Tambah Klien Baru</span>
        </a>
    </div>


    {{-- Dynamic Content Area (Filter Indicator + Table + Pagination) --}}
    <div id="client-data-container" :class="{ 'opacity-70 pointer-events-none transition-opacity duration-150': loading }">
        {{-- Active Filter / Sort Indicators --}}
        @if(request('sort') || request('search'))
        <div class="flex flex-wrap items-center gap-2 mb-4 text-xs text-[#6B5B85] bg-white px-4 py-2.5 rounded-xl border border-[#EDE1FA]">
            <span class="font-semibold text-[#5B4A73] flex items-center gap-1">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Filter/Urutan:
            </span>
            @if(request('search'))
                <span class="bg-gray-100 text-[#5B4A73] px-2.5 py-1 rounded-lg font-medium">Cari: "{{ request('search') }}"</span>
            @endif
            @if(request('sort') === 'id')
                <span class="bg-gray-100 text-[#5B4A73] px-2.5 py-1 rounded-lg font-medium">Urut ID: {{ $direction === 'asc' ? 'Terkecil (1 → 9)' : 'Terbesar (9 → 1)' }}</span>
            @elseif(request('sort') === 'name')
                <span class="bg-gray-100 text-[#5B4A73] px-2.5 py-1 rounded-lg font-medium">Urut Nama: {{ $direction === 'asc' ? 'A → Z' : 'Z → A' }}</span>
            @elseif(request('sort') === 'jenis')
                <span class="bg-gray-100 text-[#5B4A73] px-2.5 py-1 rounded-lg font-medium">
                    Urut Industri: {{ $direction === 'desc' ? 'Industri Terlebih Dahulu' : 'Bukan Industri Terlebih Dahulu' }}
                </span>
            @elseif(request('sort') === 'creator')
                <span class="bg-gray-100 text-[#5B4A73] px-2.5 py-1 rounded-lg font-medium">Urut Pembuat: {{ $direction === 'asc' ? 'A → Z' : 'Z → A' }}</span>
            @elseif(request('sort') === 'phone')
                <span class="bg-gray-100 text-[#5B4A73] px-2.5 py-1 rounded-lg font-medium">Urut No. HP: {{ $direction === 'asc' ? '0 → 9' : '9 → 0' }}</span>
            @elseif(request('sort') === 'email')
                <span class="bg-gray-100 text-[#5B4A73] px-2.5 py-1 rounded-lg font-medium">Urut Email: {{ $direction === 'asc' ? 'A → Z' : 'Z → A' }}</span>
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
                <table class="w-full text-sm font-medium">
                    <thead>
                        <tr class="bg-purple-deep text-white font-semibold select-none border-b border-purple-900/40">
                            {{-- No. Column with Numeric Sort --}}
                            <th class="text-center align-middle px-3 py-3.5 font-semibold text-xs w-16">
                                <div class="flex items-center justify-center">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'id', 'direction' => ($sort === 'id' && $direction === 'asc') ? 'desc' : 'asc', 'page' => 1]) }}"
                                        @click.prevent="goToUrl($event.currentTarget.href)"
                                        class="inline-flex items-center gap-1 hover:text-white group transition cursor-pointer"
                                        title="Urutkan nomor urut angka: 1→9 atau 9→1">
                                        <span class="text-white font-semibold">No.</span>
                                        <span class="inline-flex items-center justify-center w-4 h-4 rounded-md transition {{ $sort === 'id' ? 'bg-white/20 text-white' : 'text-purple-200 group-hover:text-white group-hover:bg-white/10' }}">
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
                                    @click.prevent="goToUrl($event.currentTarget.href)"
                                    class="inline-flex items-center gap-1.5 hover:text-white group transition cursor-pointer"
                                    title="Urutkan alfabetis A-Z / Z-A">
                                    <span class="text-white font-semibold">Nama Klien</span>
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition {{ $sort === 'name' ? 'bg-white/20 text-white' : 'text-purple-200 group-hover:text-white group-hover:bg-white/10' }}">
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

                            {{-- Kolom Industri --}}
                            <th class="text-left align-middle px-4 py-3.5 font-semibold">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'jenis', 'direction' => ($sort === 'jenis' && $direction === 'asc') ? 'desc' : 'asc', 'page' => 1]) }}"
                                    @click.prevent="goToUrl($event.currentTarget.href)"
                                    class="inline-flex items-center gap-1.5 hover:text-white group transition cursor-pointer whitespace-nowrap"
                                    title="Urutkan klien industri">
                                    <span class="text-white font-semibold">Industri</span>
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-md shrink-0 transition {{ $sort === 'jenis' ? 'bg-white/20 text-white' : 'text-purple-200 group-hover:text-white group-hover:bg-white/10' }}">
                                        @if($sort === 'jenis')
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

                            {{-- No. HP Column Sort --}}
                            <th class="text-left align-middle px-4 py-3.5 font-semibold">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'phone', 'direction' => ($sort === 'phone' && $direction === 'asc') ? 'desc' : 'asc', 'page' => 1]) }}"
                                    @click.prevent="goToUrl($event.currentTarget.href)"
                                    class="inline-flex items-center gap-1.5 hover:text-white group transition cursor-pointer whitespace-nowrap"
                                    title="Urutkan nomor HP">
                                    <span class="text-white font-semibold">No. HP</span>
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-md shrink-0 transition {{ $sort === 'phone' ? 'bg-white/20 text-white' : 'text-purple-200 group-hover:text-white group-hover:bg-white/10' }}">
                                        @if($sort === 'phone')
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

                            {{-- Email Column Sort --}}
                            <th class="text-left align-middle px-4 py-3.5 font-semibold">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'email', 'direction' => ($sort === 'email' && $direction === 'asc') ? 'desc' : 'asc', 'page' => 1]) }}"
                                    @click.prevent="goToUrl($event.currentTarget.href)"
                                    class="inline-flex items-center gap-1.5 hover:text-white group transition cursor-pointer whitespace-nowrap"
                                    title="Urutkan alamat email">
                                    <span class="text-white font-semibold">Email</span>
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-md shrink-0 transition {{ $sort === 'email' ? 'bg-white/20 text-white' : 'text-purple-200 group-hover:text-white group-hover:bg-white/10' }}">
                                        @if($sort === 'email')
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

                            {{-- Waktu Pendaftaran Column with Oldest/Newest Sort --}}
                            <th class="text-center align-middle px-4 py-3.5 font-semibold">
                                <div class="flex items-center justify-center">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => ($sort === 'created_at' && $direction === 'asc') ? 'desc' : 'asc', 'page' => 1]) }}"
                                        @click.prevent="goToUrl($event.currentTarget.href)"
                                        class="inline-flex items-center justify-center gap-1.5 hover:text-white group transition cursor-pointer whitespace-nowrap"
                                        title="Urutkan waktu pendaftaran: Paling Baru atau Paling Awal/Tua">
                                        <span class="w-5 shrink-0" aria-hidden="true"></span>
                                        <span class="text-white font-semibold">Waktu Pendaftaran</span>
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-md shrink-0 transition {{ $sort === 'created_at' ? 'bg-white/20 text-white' : 'text-purple-200 group-hover:text-white group-hover:bg-white/10' }}">
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
                            <td class="text-center align-middle px-3 py-3.5 text-black font-medium">
                                {{ $loop->iteration + ($clients->currentPage() - 1) * $clients->perPage() }}
                            </td>
                            <td class="text-left align-middle px-6 py-3.5">
                                <a href="{{ route('clients.show', $client) }}" class="font-medium text-black hover-orange hover:text-orange transition block cursor-pointer">{{ $client->name }}</a>
                            </td>
                            <td class="text-left align-middle px-4 py-3.5 whitespace-nowrap">
                                @if($client->company_name)
                                    <span class="font-medium text-black block">
                                        {{ $client->company_name }}
                                    </span>
                                @else
                                    <span class="font-medium text-gray-400">-</span>
                                @endif
                            </td>

                            {{-- No. HP --}}
                            <td class="text-left align-middle px-4 py-3.5 whitespace-nowrap">
                                @if($client->phone)
                                    <span class="font-medium text-black block">
                                        {{ $client->phone }}
                                    </span>
                                @else
                                    <span class="font-medium text-gray-400">-</span>
                                @endif
                            </td>

                            {{-- Email --}}
                            <td class="text-left align-middle px-4 py-3.5 whitespace-nowrap">
                                @if($client->email)
                                    <span class="font-medium text-black block">
                                        {{ $client->email }}
                                    </span>
                                @else
                                    <span class="font-medium text-gray-400">-</span>
                                @endif
                            </td>

                            <td class="text-center align-middle px-4 py-3.5 text-black font-medium whitespace-nowrap">
                                {{ $client->created_at ? $client->created_at->format('d/m/Y H:i:s') : '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-black font-medium">
                                <svg class="mx-auto mb-3 text-[#D9C2F0]" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                Belum ada data klien.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Pagination & View Limit Footer --}}
            @php
                $currentPage = $clients->currentPage();
                $lastPage = $clients->lastPage();
                $total = $clients->total();
                $delta = 2;

                $range = [];
                for ($i = max(2, $currentPage - $delta); $i <= min($lastPage - 1, $currentPage + $delta); $i++) {
                    $range[] = $i;
                }

                $pageNumbers = [1];
                if (!empty($range)) {
                    if ($range[0] > 2) {
                        $pageNumbers[] = '...';
                    }
                    foreach ($range as $p) {
                        $pageNumbers[] = $p;
                    }
                    if (end($range) < $lastPage - 1) {
                        $pageNumbers[] = '...';
                    }
                } elseif ($lastPage > 2) {
                    $pageNumbers[] = '...';
                }

                if ($lastPage > 1) {
                    $pageNumbers[] = $lastPage;
                }
            @endphp

            <div class="px-6 py-4 border-t border-[#EDE1FA] bg-white relative flex flex-col md:flex-row items-center justify-between gap-4">
                {{-- Left Side: Per-Page Selector --}}
                <div class="flex items-center gap-2 text-xs text-black font-medium w-full md:w-auto justify-start">
                    <span class="text-xs font-medium text-black">Tampilkan</span>
                    <select
                        @change="setPerPage($event.target.value)"
                        class="px-2.5 py-1 text-xs rounded-lg border border-[#D9C2F0] bg-white text-black font-medium focus:outline-none focus:ring-2 focus:ring-purple-deep cursor-pointer">
                        <option value="25" {{ ($perPage ?? 25) == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ ($perPage ?? 25) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ ($perPage ?? 25) == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span class="text-xs font-medium text-black">per hal.</span>
                </div>

                {{-- Center: Item Counter (Posisi tepat di tengah diantara per-page dan pagination) --}}
                <div class="text-xs text-black font-medium text-center md:absolute md:left-1/2 md:-translate-x-1/2">
                    Menampilkan
                    <span class="font-medium text-black">{{ $clients->firstItem() ?? 0 }}</span>–<span class="font-medium text-black">{{ $clients->lastItem() ?? 0 }}</span>
                    dari
                    <span class="font-medium text-black">{{ $total }}</span> klien
                </div>

                {{-- Right Side: Numbered Pagination with Total Pages Indicator --}}
                <div class="flex items-center gap-1.5 flex-wrap justify-center font-medium">
                    {{-- Previous Page Button --}}
                    @if ($clients->onFirstPage())
                        <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-[#EDE1FA] bg-gray-50 text-gray-400 text-xs font-medium cursor-not-allowed select-none" title="Halaman pertama">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
                            <span class="hidden md:inline">Prev</span>
                        </span>
                    @else
                        <a href="{{ $clients->previousPageUrl() }}"
                           @click.prevent="goToUrl('{{ $clients->previousPageUrl() }}')"
                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-[#EDE1FA] bg-white text-black hover:bg-gray-100 hover:border-gray-300 text-xs font-medium transition cursor-pointer select-none"
                           title="Halaman sebelumnya">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
                            <span class="hidden md:inline">Prev</span>
                        </a>
                    @endif

                    {{-- Page Numbers --}}
                    @foreach ($pageNumbers as $p)
                        @if ($p === '...')
                            <span class="w-8 h-8 flex items-center justify-center text-xs text-gray-400 font-medium select-none">…</span>
                        @elseif ($p == $currentPage)
                            <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-purple-deep text-white text-xs font-medium shadow-sm select-none" aria-current="page">
                                {{ $p }}
                            </span>
                        @else
                            <a href="{{ $clients->url($p) }}"
                               @click.prevent="goToUrl('{{ $clients->url($p) }}')"
                               class="w-8 h-8 flex items-center justify-center rounded-lg border border-[#EDE1FA] bg-white text-black hover:bg-gray-100 hover:border-gray-300 text-xs font-medium transition cursor-pointer select-none"
                               title="Halaman {{ $p }}">
                                {{ $p }}
                            </a>
                        @endif
                    @endforeach

                    {{-- Next Page Button --}}
                    @if ($clients->hasMorePages())
                        <a href="{{ $clients->nextPageUrl() }}"
                           @click.prevent="goToUrl('{{ $clients->nextPageUrl() }}')"
                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-[#EDE1FA] bg-white text-black hover:bg-gray-100 hover:border-gray-300 text-xs font-medium transition cursor-pointer select-none"
                           title="Halaman berikutnya">
                            <span class="hidden md:inline">Next</span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                        </a>
                    @else
                        <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-[#EDE1FA] bg-gray-50 text-gray-400 text-xs font-medium cursor-not-allowed select-none" title="Halaman terakhir">
                            <span class="hidden md:inline">Next</span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                        </span>
                    @endif

                    {{-- Total Pages indicator badge --}}
                    <span class="text-xs text-black font-medium ml-1.5 px-2.5 py-1 rounded-md bg-[#F7F5FB] border border-[#EDE1FA] whitespace-nowrap">
                        Hal. <span class="font-medium text-black">{{ $currentPage }}</span> dari <span class="font-medium text-black">{{ $lastPage }}</span>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
