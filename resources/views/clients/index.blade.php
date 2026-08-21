@extends('layouts.dashboard')

@section('title', 'Klien — UC PSC')
@section('page-title', 'Data Klien')
@section('page-subtitle', 'Kelola data klien UC PSC')

@section('content')
<div x-data="{
    search: '{{ addslashes(request('search', '')) }}',
    status: '{{ request('status', '') }}',
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
            if (this.status && this.status !== '') params.set('status', this.status);
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
        <form @submit.prevent="fetchResults()" method="GET" class="flex items-center gap-3 flex-1 max-w-2xl">
            <div class="relative flex-1 min-w-[240px] sm:min-w-[300px]">
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

            <select name="status"
                x-model="status"
                @change="fetchResults()"
                class="px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] bg-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#5B4A73] flex-shrink-0">
                <option value="">Semua Status</option>
                <option value="unassigned">Belum Di-assign</option>
                <option value="assigned">Telah Di-assign</option>
                <option value="ongoing">Sedang Konseling</option>
                <option value="unpaid">Belum Membayar</option>
                <option value="paid">Lunas</option>
                <option value="needs_followup">Butuh Sesi Lanjutan</option>
                <option value="completed">Selesai</option>
                <option value="trashed">Data Terhapus</option>
            </select>
        </form>
        <a href="{{ route('clients.create') }}" class="bg-orange text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:opacity-90 transition flex items-center gap-2 whitespace-nowrap">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Klien
        </a>
    </div>

    {{-- Dynamic Content Area (Filter Indicator + Table + Pagination) --}}
    <div id="client-data-container" :class="{ 'opacity-70 pointer-events-none transition-opacity duration-150': loading }">
        {{-- Active Filter / Sort Indicators --}}
        @if(request('sort') || request('search') || request('status'))
        <div class="flex flex-wrap items-center gap-2 mb-4 text-xs text-[#6B5B85] bg-white px-4 py-2.5 rounded-xl border border-[#EDE1FA]">
            <span class="font-semibold text-purple-deep flex items-center gap-1">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Filter/Urutan:
            </span>
            @if(request('search'))
                <span class="bg-purple-deep/10 text-purple-deep px-2.5 py-1 rounded-lg font-medium">Cari: "{{ request('search') }}"</span>
            @endif
            @if(request('status'))
                <span class="bg-purple-deep/10 text-purple-deep px-2.5 py-1 rounded-lg font-medium">Status: {{ ucfirst(str_replace('_', ' ', request('status'))) }}</span>
            @endif
            @if(request('sort') === 'name')
                <span class="bg-orange/10 text-orange px-2.5 py-1 rounded-lg font-semibold">Urut Nama: {{ $direction === 'asc' ? 'A → Z' : 'Z → A' }}</span>
            @elseif(request('sort') === 'status')
                <span class="bg-orange/10 text-orange px-2.5 py-1 rounded-lg font-semibold">Urut Status: {{ $direction === 'asc' ? 'A → Z' : 'Z → A' }}</span>
            @elseif(request('sort') === 'creator')
                <span class="bg-orange/10 text-orange px-2.5 py-1 rounded-lg font-semibold">Urut Pembuat: {{ $direction === 'asc' ? 'A → Z' : 'Z → A' }}</span>
            @elseif(request('sort') === 'created_at')
                <span class="bg-orange/10 text-orange px-2.5 py-1 rounded-lg font-semibold">Urut Tanggal: {{ $direction === 'asc' ? 'Paling Awal (Terlama)' : 'Paling Baru (Terkini)' }}</span>
            @endif
            <a href="{{ route('clients.index') }}" class="text-orange hover:underline font-bold ml-auto flex items-center gap-1">
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
                            <th class="text-center align-middle px-3 py-3.5 font-semibold text-xs text-[#827299] w-14">No.</th>
                            
                            {{-- Nama Column with Alphabetical Sort --}}
                            <th class="text-left align-middle px-6 py-3.5 font-semibold">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => ($sort === 'name' && $direction === 'asc') ? 'desc' : 'asc', 'page' => 1]) }}"
                                    class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer"
                                    title="Urutkan alfabetis A-Z / Z-A">
                                    <span class="{{ $sort === 'name' ? 'text-purple-deep font-bold' : '' }}">Nama & Layanan</span>
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

                            {{-- Status Column Sort --}}
                            <th class="text-center align-middle px-4 py-3.5 font-semibold w-44">
                                <div class="flex items-center justify-center">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'direction' => ($sort === 'status' && $direction === 'asc') ? 'desc' : 'asc', 'page' => 1]) }}"
                                        class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer"
                                        title="Urutkan berdasarkan status">
                                        <span class="{{ $sort === 'status' ? 'text-purple-deep font-bold' : '' }}">Status</span>
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition {{ $sort === 'status' ? 'bg-purple-deep/10 text-purple-deep' : 'text-[#B4A5C7] group-hover:text-purple-deep group-hover:bg-purple-deep/5' }}">
                                            @if($sort === 'status')
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

                            {{-- Creator Column Sort --}}
                            <th class="text-center align-middle px-4 py-3.5 font-semibold w-40">
                                <div class="flex items-center justify-center">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'creator', 'direction' => ($sort === 'creator' && $direction === 'asc') ? 'desc' : 'asc', 'page' => 1]) }}"
                                        class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer"
                                        title="Urutkan berdasarkan pembuat">
                                        <span class="{{ $sort === 'creator' ? 'text-purple-deep font-bold' : '' }}">Dibuat oleh</span>
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition {{ $sort === 'creator' ? 'bg-purple-deep/10 text-purple-deep' : 'text-[#B4A5C7] group-hover:text-purple-deep group-hover:bg-purple-deep/5' }}">
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

                            {{-- Tanggal Terdaftar Column with Oldest/Newest Sort --}}
                            <th class="text-center align-middle px-4 py-3.5 font-semibold w-40">
                                <div class="flex items-center justify-center">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => ($sort === 'created_at' && $direction === 'asc') ? 'desc' : 'asc', 'page' => 1]) }}"
                                        class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer"
                                        title="Urutkan tanggal terdaftar: Paling Baru atau Paling Awal/Tua">
                                        <span class="{{ $sort === 'created_at' ? 'text-purple-deep font-bold' : '' }}">Tanggal Terdaftar</span>
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition {{ $sort === 'created_at' ? 'bg-purple-deep/10 text-purple-deep' : 'text-[#B4A5C7] group-hover:text-purple-deep group-hover:bg-purple-deep/5' }}">
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

                            <th class="text-center align-middle px-4 py-3.5 font-semibold w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F3EAFB]">
                        @forelse($clients as $client)
                        <tr class="hover:bg-[#FDFBFF] transition">
                            <td class="text-center align-middle px-3 py-3.5 text-[#6B5B85]">{{ $loop->iteration + ($clients->currentPage() - 1) * $clients->perPage() }}</td>
                            <td class="text-left align-middle px-6 py-3.5">
                                @if($client->trashed())
                                <span class="font-semibold text-gray-500">{{ $client->name }}</span>
                                @else
                                <a href="{{ route('clients.show', $client) }}" class="font-semibold text-purple-deep hover:text-orange transition block">{{ $client->name }}</a>
                                @endif
                                @if($client->service_type)
                                <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-deep/10 text-purple-deep">
                                    {{ ucfirst($client->service_type) }}{{ $client->counseling_type ? ': ' . $client->counseling_type : '' }}
                                </span>
                                @endif
                            </td>
                            <td class="text-center align-middle px-4 py-3.5">
                                <div class="flex items-center justify-center">
                                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full text-[11px] font-semibold whitespace-nowrap
                                        {{ $client->status === 'unassigned' ? 'bg-gray-100 text-gray-600' :
                                           ($client->status === 'assigned' ? 'bg-indigo-50 text-indigo-600' :
                                           ($client->status === 'unpaid' ? 'bg-red-50 text-red-600' :
                                           ($client->status === 'paid' ? 'bg-emerald-50 text-emerald-600' :
                                           ($client->status === 'needs_followup' ? 'bg-blue-50 text-blue-600' :
                                           ($client->status === 'ongoing' ? 'bg-amber-50 text-amber-600' :
                                           ($client->status === 'completed' ? 'bg-purple-deep/10 text-purple-deep' : 'bg-gray-100 text-gray-600')))))) }}">
                                        {{ $client->status === 'unassigned' ? 'Belum Di-assign' :
                                           ($client->status === 'assigned' ? 'Telah Di-assign' :
                                           ($client->status === 'unpaid' ? 'Belum Membayar' :
                                           ($client->status === 'paid' ? 'Lunas' :
                                           ($client->status === 'needs_followup' ? 'Butuh Sesi Lanjutan' :
                                           ($client->status === 'ongoing' ? 'Sedang Konseling' :
                                           ($client->status === 'completed' ? 'Selesai' : $client->status)))))) }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-center align-middle px-4 py-3.5 text-[#6B5B85]">{{ $client->creator->name ?? '-' }}</td>
                            <td class="text-center align-middle px-4 py-3.5 text-[#6B5B85]">{{ $client->created_at->format('d M Y') }}</td>
                            <td class="text-center align-middle px-4 py-3.5">
                                <div class="flex items-center justify-center gap-1.5">
                                    @if($client->trashed())
                                    <form action="{{ route('clients.restore', $client->id) }}" method="POST" class="inline-flex items-center m-0 p-0">
                                        @csrf
                                        <button type="submit" class="bg-emerald-50 text-emerald-600 hover:bg-emerald-100 px-3 py-1.5 rounded-lg text-xs font-semibold transition" title="Kembalikan">
                                            Restore
                                        </button>
                                    </form>
                                    <form action="{{ route('clients.force-delete', $client->id) }}" method="POST" class="inline-flex items-center m-0 p-0" onsubmit="return confirm('Peringatan: Data ini akan dihapus permanen dan tidak bisa dikembalikan. Lanjutkan?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="bg-red-50 text-red-500 hover:bg-red-100 px-3 py-1.5 rounded-lg text-xs font-semibold transition" title="Hapus Permanen">
                                            Hapus
                                        </button>
                                    </form>
                                    @else
                                    <a href="{{ route('clients.show', $client) }}" class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-[#6B5B85] hover:text-purple-deep hover:bg-purple-deep/10 transition" title="Detail">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                    <a href="{{ route('clients.edit', $client) }}" class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-[#6B5B85] hover:text-purple-deep hover:bg-purple-deep/10 transition" title="Edit">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </a>
                                    <form action="{{ route('clients.destroy', $client) }}" method="POST" class="inline-flex items-center m-0 p-0" onsubmit="return confirm('Yakin ingin menghapus klien ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-[#6B5B85] hover:text-red-500 hover:bg-red-50 transition cursor-pointer p-0 m-0 border-0 bg-transparent" title="Hapus">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-[#6B5B85]">
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
