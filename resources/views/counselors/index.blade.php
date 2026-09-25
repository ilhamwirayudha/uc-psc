@extends('layouts.dashboard')

@section('title', 'Konselor — UC PSC')
@section('page-title', 'Data Konselor')
@section('page-subtitle', 'Kelola data psikolog & konselor pendamping UC PSC')

@section('content')
@include('clients.partials.address-script')

<div x-data="{
    search: '{{ addslashes(request('search', '')) }}',
    status: '{{ request('status', '') }}',
    sort: '{{ request('sort', 'created_at') }}',
    direction: '{{ request('direction', 'desc') }}',
    loading: false,
    timer: null,

    // Modal Create State
    createModalOpen: {{ $errors->any() ? 'true' : 'false' }},
    createDiscardModalOpen: false,
    photoPreview: null,
    newCounselor: {
        name: @js(old('name', '')),
        phone: @js(old('phone', '')),
        email: @js(old('email', '')),
        country: @js(old('country', 'Indonesia')),
        province: @js(old('province', '')),
        city: @js(old('city', '')),
        address: @js(old('address', '')),
        specialization: @js(old('specialization', '')),
        sipp_number: @js(old('sipp_number', '')),
        str_number: @js(old('str_number', '')),
        status: @js(old('status', 'active')),
        notes: @js(old('notes', ''))
    },
    createCountryOpen: false,
    createCountrySearch: '',
    createProvinceOpen: false,
    createProvinceSearch: '',
    createCityOpen: false,
    createCitySearch: '',

    // Modal Detail & Edit State
    editModalOpen: false,
    isEditing: false,
    editDiscardModalOpen: false,
    deleteCounselorModalOpen: false,
    editPhotoPreview: null,
    editRemovePhoto: false,
    editingCounselor: {
        id: '',
        name: '',
        phone: '',
        email: '',
        country: 'Indonesia',
        province: '',
        city: '',
        address: '',
        full_address: '',
        specialization: '',
        sipp_number: '',
        str_number: '',
        status: 'active',
        notes: '',
        photo_url: null,
        update_url: ''
    },
    originalCounselor: {},
    editCountryOpen: false,
    editCountrySearch: '',
    editProvinceOpen: false,
    editProvinceSearch: '',
    editCityOpen: false,
    editCitySearch: '',

    // Address Helpers for Create Modal
    get createCountryList() {
        const list = [...(window.addressData?.countries || [])].sort((a, b) => a.localeCompare('id', { sensitivity: 'base' }));
        if (!this.createCountrySearch) return list;
        return list.filter(c => c.toLowerCase().includes(this.createCountrySearch.toLowerCase()));
    },
    get createProvinceList() {
        const list = [...(window.addressData?.provinces || [])].sort((a, b) => a.localeCompare('id', { sensitivity: 'base' }));
        if (!this.createProvinceSearch) return list;
        return list.filter(p => p.toLowerCase().includes(this.createProvinceSearch.toLowerCase()));
    },
    get createCityList() {
        if (!this.newCounselor.province || !window.addressData?.citiesByProvince || !window.addressData.citiesByProvince[this.newCounselor.province]) {
            return [];
        }
        const list = [...window.addressData.citiesByProvince[this.newCounselor.province]].sort((a, b) => a.localeCompare('id', { sensitivity: 'base' }));
        if (!this.createCitySearch) return list;
        return list.filter(c => c.toLowerCase().includes(this.createCitySearch.toLowerCase()));
    },
    openCreateCountry() {
        this.createCountryOpen = true;
        this.createCountrySearch = '';
        this.createProvinceOpen = false;
        this.createCityOpen = false;
        this.$nextTick(() => { this.$refs.createCountrySearchInput && this.$refs.createCountrySearchInput.focus(); });
    },
    selectCreateCountry(val) {
        this.newCounselor.country = val;
        this.createCountryOpen = false;
        this.createCountrySearch = '';
        if (val !== 'Indonesia') {
            this.newCounselor.province = '';
            this.newCounselor.city = '';
            this.createProvinceOpen = false;
            this.createCityOpen = false;
        }
    },
    openCreateProvince() {
        if (this.newCounselor.country !== 'Indonesia') return;
        this.createProvinceOpen = true;
        this.createProvinceSearch = '';
        this.createCountryOpen = false;
        this.createCityOpen = false;
        this.$nextTick(() => { this.$refs.createProvinceSearchInput && this.$refs.createProvinceSearchInput.focus(); });
    },
    selectCreateProvince(val) {
        this.newCounselor.province = val;
        this.createProvinceOpen = false;
        this.createProvinceSearch = '';
        this.newCounselor.city = '';
        this.createCitySearch = '';
    },
    openCreateCity() {
        if (this.newCounselor.country !== 'Indonesia' || !this.newCounselor.province) return;
        this.createCityOpen = true;
        this.createCitySearch = '';
        this.createCountryOpen = false;
        this.createProvinceOpen = false;
        this.$nextTick(() => { this.$refs.createCitySearchInput && this.$refs.createCitySearchInput.focus(); });
    },
    selectCreateCity(val) {
        this.newCounselor.city = val;
        this.createCityOpen = false;
        this.createCitySearch = '';
    },

    // Address Helpers for Edit Modal
    get editCountryList() {
        const list = [...(window.addressData?.countries || [])].sort((a, b) => a.localeCompare('id', { sensitivity: 'base' }));
        if (!this.editCountrySearch) return list;
        return list.filter(c => c.toLowerCase().includes(this.editCountrySearch.toLowerCase()));
    },
    get editProvinceList() {
        const list = [...(window.addressData?.provinces || [])].sort((a, b) => a.localeCompare('id', { sensitivity: 'base' }));
        if (!this.editProvinceSearch) return list;
        return list.filter(p => p.toLowerCase().includes(this.editProvinceSearch.toLowerCase()));
    },
    get editCityList() {
        if (!this.editingCounselor.province || !window.addressData?.citiesByProvince || !window.addressData.citiesByProvince[this.editingCounselor.province]) {
            return [];
        }
        const list = [...window.addressData.citiesByProvince[this.editingCounselor.province]].sort((a, b) => a.localeCompare('id', { sensitivity: 'base' }));
        if (!this.editCitySearch) return list;
        return list.filter(c => c.toLowerCase().includes(this.editCitySearch.toLowerCase()));
    },
    openEditCountry() {
        this.editCountryOpen = true;
        this.editCountrySearch = '';
        this.editProvinceOpen = false;
        this.editCityOpen = false;
        this.$nextTick(() => { this.$refs.editCountrySearchInput && this.$refs.editCountrySearchInput.focus(); });
    },
    selectEditCountry(val) {
        this.editingCounselor.country = val;
        this.editCountryOpen = false;
        this.editCountrySearch = '';
        if (val !== 'Indonesia') {
            this.editingCounselor.province = '';
            this.editingCounselor.city = '';
            this.editProvinceOpen = false;
            this.editCityOpen = false;
        }
    },
    openEditProvince() {
        if (this.editingCounselor.country !== 'Indonesia') return;
        this.editProvinceOpen = true;
        this.editProvinceSearch = '';
        this.editCountryOpen = false;
        this.editCityOpen = false;
        this.$nextTick(() => { this.$refs.editProvinceSearchInput && this.$refs.editProvinceSearchInput.focus(); });
    },
    selectEditProvince(val) {
        this.editingCounselor.province = val;
        this.editProvinceOpen = false;
        this.editProvinceSearch = '';
        this.editingCounselor.city = '';
        this.editCitySearch = '';
    },
    openEditCity() {
        if (this.editingCounselor.country !== 'Indonesia' || !this.editingCounselor.province) return;
        this.editCityOpen = true;
        this.editCitySearch = '';
        this.editCountryOpen = false;
        this.editProvinceOpen = false;
        this.$nextTick(() => { this.$refs.editCitySearchInput && this.$refs.editCitySearchInput.focus(); });
    },
    selectEditCity(val) {
        this.editingCounselor.city = val;
        this.editCityOpen = false;
        this.editCitySearch = '';
    },

    // Sequential Form Validation & Enablers (Atas ke Bawah)
    get isPhoneEnabled() {
        return !!this.newCounselor.name && this.newCounselor.name.trim().length > 0;
    },
    get isEmailEnabled() {
        return this.isPhoneEnabled && !!this.newCounselor.phone && this.newCounselor.phone.trim().length >= 8;
    },
    get isAddressEnabled() {
        return this.isEmailEnabled && !!this.newCounselor.email && this.newCounselor.email.trim().length > 0;
    },
    get isSpecializationEnabled() {
        return this.isAddressEnabled;
    },
    get isSippEnabled() {
        return this.isSpecializationEnabled && !!this.newCounselor.specialization && this.newCounselor.specialization.trim().length > 0;
    },
    get isStrEnabled() {
        return this.isSippEnabled && !!this.newCounselor.sipp_number && this.newCounselor.sipp_number.trim().length > 0;
    },
    get isStatusEnabled() {
        return this.isStrEnabled && !!this.newCounselor.str_number && this.newCounselor.str_number.trim().length > 0;
    },
    get isNotesEnabled() {
        return this.isStatusEnabled;
    },

    isFormDirty() {
        return !!(
            (this.newCounselor.name && this.newCounselor.name.trim() !== '') ||
            (this.newCounselor.phone && this.newCounselor.phone.trim() !== '') ||
            (this.newCounselor.email && this.newCounselor.email.trim() !== '') ||
            (this.newCounselor.country && this.newCounselor.country !== 'Indonesia') ||
            (this.newCounselor.province && this.newCounselor.province.trim() !== '') ||
            (this.newCounselor.city && this.newCounselor.city.trim() !== '') ||
            (this.newCounselor.address && this.newCounselor.address.trim() !== '') ||
            (this.newCounselor.specialization && this.newCounselor.specialization.trim() !== '') ||
            (this.newCounselor.sipp_number && this.newCounselor.sipp_number.trim() !== '') ||
            (this.newCounselor.str_number && this.newCounselor.str_number.trim() !== '') ||
            (this.newCounselor.notes && this.newCounselor.notes.trim() !== '') ||
            (this.photoPreview !== null)
        );
    },

    openCreateModal() {
        this.createModalOpen = true;
        this.createDiscardModalOpen = false;
        this.$nextTick(() => {
            const input = document.getElementById('create_name');
            if (input) input.focus();
        });
    },

    closeCreateModal(force = false) {
        if (!force && this.isFormDirty()) {
            this.createDiscardModalOpen = true;
        } else {
            this.createModalOpen = false;
            this.createDiscardModalOpen = false;
            this.resetCreateForm();
        }
    },

    resetCreateForm() {
        this.photoPreview = null;
        this.newCounselor = {
            name: '',
            phone: '',
            email: '',
            country: 'Indonesia',
            province: '',
            city: '',
            address: '',
            specialization: '',
            sipp_number: '',
            str_number: '',
            status: 'active',
            notes: ''
        };
        this.createCountryOpen = false;
        this.createCountrySearch = '';
        this.createProvinceOpen = false;
        this.createProvinceSearch = '';
        this.createCityOpen = false;
        this.createCitySearch = '';
        const fileInput = document.getElementById('create_photo_input');
        if (fileInput) fileInput.value = '';
    },

    handlePhotoSelect(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran file maksimal 2 MB.');
                e.target.value = '';
                return;
            }
            this.photoPreview = URL.createObjectURL(file);
        }
    },

    removePhoto() {
        this.photoPreview = null;
        const fileInput = document.getElementById('create_photo_input');
        if (fileInput) fileInput.value = '';
    },

    openEditModal(data) {
        this.editingCounselor = {
            id: data.id,
            name: data.name || '',
            phone: data.phone || '',
            email: data.email || '',
            country: data.country || 'Indonesia',
            province: data.province || '',
            city: data.city || '',
            address: data.address || '',
            full_address: data.full_address || '',
            specialization: data.specialization || '',
            sipp_number: data.sipp_number || '',
            str_number: data.str_number || '',
            status: data.status || 'active',
            notes: data.notes || '',
            photo_url: data.photo_url || null,
            update_url: '{{ url('counselors') }}/' + data.id
        };
        this.originalCounselor = JSON.parse(JSON.stringify(this.editingCounselor));
        this.editPhotoPreview = data.photo_url || null;
        this.editRemovePhoto = false;
        this.isEditing = false;
        this.editModalOpen = true;
        this.editDiscardModalOpen = false;
        this.deleteCounselorModalOpen = false;
        this.editCountryOpen = false;
        this.editCountrySearch = '';
        this.editProvinceOpen = false;
        this.editProvinceSearch = '';
        this.editCityOpen = false;
        this.editCitySearch = '';
        const fileInput = document.getElementById('edit_photo_input');
        if (fileInput) fileInput.value = '';
    },

    startEditing() {
        this.isEditing = true;
        this.$nextTick(() => {
            const input = document.getElementById('edit_name');
            if (input) input.focus();
        });
    },

    cancelEditing(force = false) {
        if (!force && this.isEditFormDirty()) {
            this.editDiscardModalOpen = true;
        } else {
            this.editingCounselor = JSON.parse(JSON.stringify(this.originalCounselor));
            this.editPhotoPreview = this.originalCounselor.photo_url || null;
            this.editRemovePhoto = false;
            this.isEditing = false;
            this.editDiscardModalOpen = false;
            this.editCountryOpen = false;
            this.editProvinceOpen = false;
            this.editCityOpen = false;
            const fileInput = document.getElementById('edit_photo_input');
            if (fileInput) fileInput.value = '';
        }
    },

    isEditFormDirty() {
        if (!this.editModalOpen || !this.isEditing) return false;
        if (this.editRemovePhoto) return true;
        if (this.editPhotoPreview !== (this.originalCounselor.photo_url || null)) return true;
        
        return (
            this.editingCounselor.name !== this.originalCounselor.name ||
            this.editingCounselor.phone !== this.originalCounselor.phone ||
            this.editingCounselor.email !== this.originalCounselor.email ||
            this.editingCounselor.country !== this.originalCounselor.country ||
            this.editingCounselor.province !== this.originalCounselor.province ||
            this.editingCounselor.city !== this.originalCounselor.city ||
            this.editingCounselor.address !== this.originalCounselor.address ||
            this.editingCounselor.specialization !== this.originalCounselor.specialization ||
            this.editingCounselor.sipp_number !== this.originalCounselor.sipp_number ||
            this.editingCounselor.str_number !== this.originalCounselor.str_number ||
            this.editingCounselor.status !== this.originalCounselor.status ||
            this.editingCounselor.notes !== this.originalCounselor.notes
        );
    },

    closeEditModal(force = false) {
        if (this.isEditing && !force && this.isEditFormDirty()) {
            this.editDiscardModalOpen = true;
        } else {
            this.editModalOpen = false;
            this.isEditing = false;
            this.editDiscardModalOpen = false;
            this.deleteCounselorModalOpen = false;
            this.editPhotoPreview = null;
            this.editRemovePhoto = false;
        }
    },

    handleEditPhotoSelect(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran file maksimal 2 MB.');
                e.target.value = '';
                return;
            }
            this.editPhotoPreview = URL.createObjectURL(file);
            this.editRemovePhoto = false;
        }
    },

    removeEditPhoto() {
        this.editPhotoPreview = null;
        this.editRemovePhoto = true;
        const fileInput = document.getElementById('edit_photo_input');
        if (fileInput) fileInput.value = '';
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

    async fetchResults() {
        this.loading = true;
        try {
            const params = new URLSearchParams();
            if (this.search && this.search.trim() !== '') params.set('search', this.search.trim());
            if (this.status && this.status !== '') params.set('status', this.status);
            if (this.sort) params.set('sort', this.sort);
            if (this.direction) params.set('direction', this.direction);

            const queryString = params.toString();
            const url = '{{ route('counselors.index') }}' + (queryString ? '?' + queryString : '');

            window.history.replaceState({}, '', url);

            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error('Fetch failed');

            const text = await response.text();
            const doc = new DOMParser().parseFromString(text, 'text/html');

            const newContent = doc.getElementById('counselor-data-container');
            const target = document.getElementById('counselor-data-container');

            if (newContent && target) {
                target.innerHTML = newContent.innerHTML;
            }
        } catch (e) {
            console.error('Error fetching counselors:', e);
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
                    placeholder="Cari nama, spesialisasi, SIPP, STR..."
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

            {{-- Filter Status Konselor --}}
            <select name="status"
                x-model="status"
                @change="fetchResults()"
                class="px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] bg-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#5B4A73] flex-shrink-0">
                <option value="">Semua Status Konselor</option>
                <option value="active">Aktif</option>
                <option value="inactive">Nonaktif</option>
            </select>
        </form>

        {{-- Tombol Tambah Konselor (Membuka Modal Pop-up) --}}
        <button type="button" 
            @click="openCreateModal()" 
            class="bg-orange text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:opacity-90 transition flex items-center gap-2 whitespace-nowrap shadow-xs cursor-pointer">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Tambah Konselor</span>
        </button>
    </div>

    {{-- Dynamic Content Area (Filter Indicator + Table + Pagination) --}}
    <div id="counselor-data-container" :class="{ 'opacity-70 pointer-events-none transition-opacity duration-150': loading }">
        {{-- Active Filter / Sort Indicators --}}
        @if(request('sort') || request('search') || request('status'))
        <div class="flex flex-wrap items-center gap-2 mb-4 text-xs text-[#6B5B85] bg-white px-4 py-2.5 rounded-xl border border-[#EDE1FA]">
            <span class="font-semibold text-[#5B4A73] flex items-center gap-1">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Filter/Urutan:
            </span>
            @if(request('search'))
                <span class="bg-gray-100 text-[#5B4A73] px-2.5 py-1 rounded-lg font-medium">Cari: "{{ request('search') }}"</span>
            @endif
            @if(request('status'))
                <span class="bg-gray-100 text-[#5B4A73] px-2.5 py-1 rounded-lg font-medium">
                    Status: {{ request('status') === 'active' ? 'Aktif' : 'Nonaktif' }}
                </span>
            @endif
            @if(request('sort') === 'id')
                <span class="bg-gray-100 text-[#5B4A73] px-2.5 py-1 rounded-lg font-medium">Urut ID: {{ $direction === 'asc' ? 'Terkecil (1 → 9)' : 'Terbesar (9 → 1)' }}</span>
            @elseif(request('sort') === 'name')
                <span class="bg-gray-100 text-[#5B4A73] px-2.5 py-1 rounded-lg font-medium">Urut Nama: {{ $direction === 'asc' ? 'A → Z' : 'Z → A' }}</span>
            @elseif(request('sort') === 'specialization')
                <span class="bg-gray-100 text-[#5B4A73] px-2.5 py-1 rounded-lg font-medium">Urut Spesialisasi: {{ $direction === 'asc' ? 'A → Z' : 'Z → A' }}</span>
            @elseif(request('sort') === 'status')
                <span class="bg-gray-100 text-[#5B4A73] px-2.5 py-1 rounded-lg font-medium">Urut Status: {{ $direction === 'asc' ? 'A → Z' : 'Z → A' }}</span>
            @elseif(request('sort') === 'created_at')
                <span class="bg-gray-100 text-[#5B4A73] px-2.5 py-1 rounded-lg font-medium">Urut Waktu: {{ $direction === 'asc' ? 'Paling Awal (Terlama)' : 'Paling Baru (Terkini)' }}</span>
            @endif
            <a href="{{ route('counselors.index') }}" class="text-[#6B5B85] hover:text-red-500 hover:underline font-semibold ml-auto flex items-center gap-1 transition">
                <span>Reset Filter & Urutan</span>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </a>
        </div>
        @endif

        {{-- Table Card --}}
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
                                        title="Urutkan nomor urut: 1→9 atau 9→1">
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
                            
                            {{-- Nama Konselor Column with Alphabetical Sort --}}
                            <th class="text-left align-middle px-6 py-3.5 font-semibold">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => ($sort === 'name' && $direction === 'asc') ? 'desc' : 'asc', 'page' => 1]) }}"
                                    class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer"
                                    title="Urutkan alfabetis A-Z / Z-A">
                                    <span class="{{ $sort === 'name' ? 'text-purple-deep font-bold' : '' }}">Nama Konselor</span>
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

                            {{-- Kontak (Telepon & Email) --}}
                            <th class="text-left align-middle px-5 py-3.5 font-semibold">
                                Kontak
                            </th>

                            {{-- Spesialisasi --}}
                            <th class="text-left align-middle px-5 py-3.5 font-semibold">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'specialization', 'direction' => ($sort === 'specialization' && $direction === 'asc') ? 'desc' : 'asc', 'page' => 1]) }}"
                                    class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer"
                                    title="Urutkan berdasarkan spesialisasi">
                                    <span class="{{ $sort === 'specialization' ? 'text-purple-deep font-bold' : '' }}">Spesialisasi</span>
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition {{ $sort === 'specialization' ? 'bg-purple-deep/10 text-purple-deep' : 'text-[#B4A5C7] group-hover:text-purple-deep group-hover:bg-purple-deep/5' }}">
                                        @if($sort === 'specialization')
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

                            {{-- Status Konselor Column Sort --}}
                            <th class="text-center align-middle px-4 py-3.5 font-semibold">
                                <div class="flex items-center justify-center">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'direction' => ($sort === 'status' && $direction === 'asc') ? 'desc' : 'asc', 'page' => 1]) }}"
                                        class="inline-flex items-center justify-center gap-1.5 hover:text-purple-deep group transition cursor-pointer whitespace-nowrap"
                                        title="Urutkan status konselor">
                                        <span class="w-5 shrink-0" aria-hidden="true"></span>
                                        <span class="{{ $sort === 'status' ? 'text-purple-deep font-bold' : '' }}">Status</span>
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-md shrink-0 transition {{ $sort === 'status' ? 'bg-purple-deep/10 text-purple-deep' : 'text-[#B4A5C7] group-hover:text-purple-deep group-hover:bg-purple-deep/5' }}">
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

                            {{-- Waktu Terdaftar Column Sort --}}
                            <th class="text-center align-middle px-4 py-3.5 font-semibold">
                                <div class="flex items-center justify-center">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => ($sort === 'created_at' && $direction === 'asc') ? 'desc' : 'asc', 'page' => 1]) }}"
                                        class="inline-flex items-center justify-center gap-1.5 hover:text-purple-deep group transition cursor-pointer whitespace-nowrap"
                                        title="Urutkan waktu terdaftar">
                                        <span class="w-5 shrink-0" aria-hidden="true"></span>
                                        <span class="{{ $sort === 'created_at' ? 'text-purple-deep font-bold' : '' }}">Waktu Terdaftar</span>
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
                        @forelse($counselors as $counselor)
                        <tr class="hover:bg-[#FDFBFF] transition"
                            x-data="{
                                counselorData: {
                                    id: {{ $counselor->id }},
                                    name: @js($counselor->name),
                                    phone: @js($counselor->phone),
                                    email: @js($counselor->email),
                                    country: @js($counselor->country ?: 'Indonesia'),
                                    province: @js($counselor->province),
                                    city: @js($counselor->city),
                                    address: @js($counselor->address),
                                    full_address: @js($counselor->full_address),
                                    specialization: @js($counselor->specialization),
                                    sipp_number: @js($counselor->sipp_number),
                                    str_number: @js($counselor->str_number),
                                    status: @js($counselor->status),
                                    notes: @js($counselor->notes),
                                    photo_url: @js($counselor->photo_url)
                                },
                                open: false,
                                updating: false,
                                async updateStatus(newStatus) {
                                    if (this.counselorData.status === newStatus || this.updating) return;
                                    this.updating = true;
                                    try {
                                        const res = await fetch('{{ route('counselors.update', $counselor) }}', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'X-Requested-With': 'XMLHttpRequest',
                                                'Accept': 'application/json'
                                            },
                                            body: JSON.stringify({
                                                _method: 'PUT',
                                                status: newStatus
                                            })
                                        });
                                        if (res.ok) {
                                            this.counselorData.status = newStatus;
                                            if (editingCounselor && editingCounselor.id === this.counselorData.id) {
                                                editingCounselor.status = newStatus;
                                                originalCounselor.status = newStatus;
                                            }
                                        } else {
                                            alert('Gagal memperbarui status konselor.');
                                        }
                                    } catch (e) {
                                        console.error(e);
                                        alert('Terjadi kesalahan koneksi.');
                                    } finally {
                                        this.updating = false;
                                    }
                                }
                            }">
                            {{-- No. --}}
                            <td class="text-center align-middle px-3 py-3.5 text-[#6B5B85] font-medium">
                                {{ $loop->iteration + ($counselors->currentPage() - 1) * $counselors->perPage() }}
                            </td>

                            {{-- Nama Konselor Profile (Lingkaran Sempurna dengan Foto / Inisial) --}}
                            <td class="text-left align-middle px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    @if($counselor->photo_url)
                                    <img src="{{ $counselor->photo_url }}" alt="{{ $counselor->name }}" class="w-9 h-9 rounded-full object-cover shadow-xs border border-[#EDE1FA] flex-shrink-0">
                                    @else
                                    <div class="w-9 h-9 rounded-full grad-purple text-white flex items-center justify-center font-bold text-sm shadow-xs flex-shrink-0">
                                        {{ strtoupper(substr($counselor->name, 0, 1)) }}
                                    </div>
                                    @endif
                                    <div class="min-w-0">
                                        <button type="button"
                                            @click="openEditModal(counselorData)"
                                            class="font-semibold text-purple-deep hover:text-orange transition truncate block text-left cursor-pointer">
                                            {{ $counselor->name }}
                                        </button>
                                    </div>
                                </div>
                            </td>

                            {{-- Kontak --}}
                            <td class="text-left align-middle px-5 py-3.5 text-sm text-[#6B5B85]">
                                <div class="space-y-1">
                                    <p class="font-medium text-[#2A2035] flex items-center gap-2">
                                        <svg class="text-[#827299] shrink-0" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                        <span>{{ $counselor->phone ?: '-' }}</span>
                                    </p>
                                    <p class="text-[#827299] truncate flex items-center gap-2 text-sm">
                                        <svg class="text-[#827299] shrink-0" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                        <span>{{ $counselor->email ?: '-' }}</span>
                                    </p>
                                </div>
                            </td>

                            {{-- Spesialisasi --}}
                            <td class="text-left align-middle px-5 py-3.5 text-sm text-[#2A2035] font-medium">
                                {{ $counselor->specialization ?: '-' }}
                            </td>

                            {{-- Status Konselor (Custom Dropdown Pilihan Hijau & Merah) --}}
                            <td class="text-center align-middle px-4 py-3.5 whitespace-nowrap">
                                <div class="relative inline-block text-left" @click.outside="open = false">
                                    {{-- Status Trigger Button --}}
                                    <button type="button"
                                        @click="open = !open"
                                        :disabled="updating"
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition border focus:outline-none cursor-pointer shadow-2xs select-none"
                                        :class="counselorData.status === 'active' 
                                            ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100/80' 
                                            : 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100/80'">
                                        <span class="w-1.5 h-1.5 rounded-full shrink-0"
                                            :class="counselorData.status === 'active' ? 'bg-emerald-500' : 'bg-red-500'"></span>
                                        <span x-text="counselorData.status === 'active' ? 'Aktif' : 'Nonaktif'"></span>
                                        
                                        <template x-if="!updating">
                                            <svg class="transition-transform duration-200 shrink-0" :class="open ? 'rotate-180' : ''" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                                        </template>
                                        <template x-if="updating">
                                            <svg class="animate-spin text-purple-deep shrink-0" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                                        </template>
                                    </button>

                                    {{-- Custom Redesigned Dropdown Menu --}}
                                    <div x-show="open" 
                                        x-cloak
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="transform opacity-0 scale-95 -translate-y-1"
                                        x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
                                        x-transition:leave-end="transform opacity-0 scale-95 -translate-y-1"
                                        class="absolute left-1/2 -translate-x-1/2 mt-1.5 w-34 bg-white rounded-xl shadow-lg border border-[#EDE1FA] p-1.5 z-40 space-y-1">
                                        
                                        {{-- Option 1: Aktif (Selalu Warna Hijau) --}}
                                        <button type="button"
                                            @click="updateStatus('active'); open = false"
                                            class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer"
                                            :class="counselorData.status === 'active' 
                                                ? 'bg-emerald-50 text-emerald-800 border border-emerald-200/60' 
                                                : 'text-emerald-700 hover:bg-emerald-50/70'">
                                            <span class="flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                                <span>Aktif</span>
                                            </span>
                                            <svg x-show="counselorData.status === 'active'" class="text-emerald-600" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                        </button>

                                        {{-- Option 2: Nonaktif (Selalu Warna Merah) --}}
                                        <button type="button"
                                            @click="updateStatus('inactive'); open = false"
                                            class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer"
                                            :class="counselorData.status === 'inactive' 
                                                ? 'bg-red-50 text-red-800 border border-red-200/60' 
                                                : 'text-red-700 hover:bg-red-50/70'">
                                            <span class="flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                                <span>Nonaktif</span>
                                            </span>
                                            <svg x-show="counselorData.status === 'inactive'" class="text-red-600" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </td>

                            {{-- Waktu Terdaftar --}}
                            <td class="text-center align-middle px-4 py-3.5 text-[#6B5B85] whitespace-nowrap text-sm font-medium">
                                {{ $counselor->created_at ? $counselor->created_at->format('d M Y, H:i') : '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-[#6B5B85]">
                                <svg class="mx-auto mb-3 text-[#D9C2F0]" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                Belum ada data konselor.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($counselors->hasPages())
            <div class="px-6 py-4 border-t border-[#EDE1FA]">
                {{ $counselors->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL POP-UP TAMBAH KONSELOR BARU                                         --}}
    {{-- ========================================================================= --}}
    <div x-cloak x-show="createModalOpen" @keydown.escape.window="closeCreateModal()" class="fixed inset-0 z-50 overflow-y-auto">
        {{-- Backdrop blur --}}
        <div x-show="createModalOpen" 
            x-transition:enter="ease-out duration-200" 
            x-transition:enter-start="opacity-0" 
            x-transition:enter-end="opacity-100" 
            class="fixed inset-0 bg-black/50 backdrop-blur-xs" 
            @click="closeCreateModal()"></div>

        <div class="min-h-full flex items-center justify-center p-4">
            <div x-show="createModalOpen" 
                x-transition:enter="ease-out duration-200" 
                x-transition:enter-start="opacity-0 scale-95" 
                x-transition:enter-end="opacity-100 scale-100" 
                class="relative bg-white rounded-2xl shadow-2xl border border-[#EDE1FA] max-w-3xl w-full max-h-[90vh] flex flex-col z-10 my-6"
                @click.stop>
                
                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-6 py-4.5 border-b border-[#EDE1FA] bg-white rounded-t-2xl">
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-purple-deep">Tambah Konselor Baru</h3>
                        <p class="text-xs sm:text-[13px] text-[#6B5B85] mt-0.5">Daftarkan data psikolog atau konselor pendamping baru ke UC PSC</p>
                    </div>
                    <button type="button" @click="closeCreateModal()" class="w-8 h-8 rounded-lg text-[#827299] hover:text-purple-deep hover:bg-purple-deep/10 flex items-center justify-center transition cursor-pointer" title="Tutup">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                {{-- Modal Scrollable Body (Card-based design matching clients/create.blade.php) --}}
                <form action="{{ route('counselors.store') }}" method="POST" enctype="multipart/form-data" class="flex-1 overflow-y-auto bg-[#FAF8FD] p-6 space-y-5" data-no-guard="true">
                    @csrf

                    {{-- CARD 1: DATA IDENTITAS & KONTAK --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-5">
                        <div class="pb-3 border-b border-[#EDE1FA]">
                            <h3 class="text-base font-bold text-purple-deep">Data Identitas & Kontak</h3>
                        </div>

                        {{-- Foto Profil Konselor --}}
                        <div class="flex flex-col sm:flex-row items-center gap-5 p-4 rounded-xl bg-gray-50 border border-gray-200">
                            <div class="relative group flex-shrink-0">
                                <template x-if="photoPreview">
                                    <img :src="photoPreview" alt="Preview Foto" class="w-20 h-20 rounded-full object-cover shadow-sm border-2 border-white ring-2 ring-gray-200">
                                </template>
                                <template x-if="!photoPreview">
                                    <div class="w-20 h-20 rounded-full grad-purple text-white flex items-center justify-center font-bold text-2xl shadow-sm border-2 border-white ring-2 ring-gray-200">
                                        <span x-text="newCounselor.name ? newCounselor.name.trim().charAt(0).toUpperCase() : '?'"></span>
                                    </div>
                                </template>

                                <button type="button" 
                                    x-show="photoPreview" 
                                    @click="removePhoto()" 
                                    class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full p-1 shadow-sm hover:bg-red-600 transition cursor-pointer" 
                                    title="Hapus Foto">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </div>

                            <div class="flex-1 text-center sm:text-left space-y-1.5">
                                <label class="block text-xs sm:text-[13px] font-bold text-[#2A2035]">Foto Profil Konselor</label>
                                <p class="text-xs text-gray-500">Format JPG, PNG, atau WebP. Ukuran maksimal 2 MB.</p>
                                
                                <div class="pt-1">
                                    <input type="file" id="create_photo_input" name="photo" accept="image/*" class="hidden" @change="handlePhotoSelect($event)">
                                    <label for="create_photo_input" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border border-gray-300 bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50 transition cursor-pointer shadow-2xs">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                                        <span x-text="photoPreview ? 'Ganti Foto Profil' : 'Unggah Foto Profil'"></span>
                                    </label>
                                </div>
                                @error('photo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Field: Nama Konselor --}}
                        <div>
                            <label for="create_name" class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">Nama Konselor / Psikolog <span class="text-red-500">*</span></label>
                            <input type="text" id="create_name" name="name" x-model="newCounselor.name" required
                                placeholder="Contoh: Dr. Amanda Wijaya, M.Psi., Psikolog"
                                class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035] @error('name') border-red-300 @enderror">
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Kontak: No Telepon & Email --}}
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label for="create_phone" class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">No. Telepon / WhatsApp <span class="text-red-500">*</span></label>
                                <input type="text" id="create_phone" name="phone" x-model="newCounselor.phone"
                                    inputmode="numeric"
                                    maxlength="13"
                                    @input="newCounselor.phone = $el.value.replace(/[^0-9]/g, '').slice(0, 13); $el.value = newCounselor.phone"
                                    @keydown="if (!/[0-9]/.test($event.key) && !['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab', 'Home', 'End'].includes($event.key) && !$event.ctrlKey && !$event.metaKey) $event.preventDefault()"
                                    :disabled="!isPhoneEnabled"
                                    :class="isPhoneEnabled ? 'bg-white border-[#D9C2F0]' : 'bg-[#F7F4FB] border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'"
                                    :placeholder="isPhoneEnabled ? 'Contoh: 081234567890 (maks. 13 digit)' : ''"
                                    class="w-full px-4 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035] @error('phone') border-red-300 @enderror">
                                @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="create_email" class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">Email <span class="text-red-500">*</span></label>
                                <input type="email" id="create_email" name="email" x-model="newCounselor.email"
                                    :disabled="!isEmailEnabled"
                                    :class="isEmailEnabled ? 'bg-white border-[#D9C2F0]' : 'bg-[#F7F4FB] border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'"
                                    :placeholder="isEmailEnabled ? 'Contoh: amanda.wijaya@uc.ac.id' : ''"
                                    class="w-full px-4 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035]">
                                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- CARD 2: ALAMAT DOMISILI --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-5">
                        <div class="pb-3 border-b border-[#EDE1FA]">
                            <h3 class="text-base font-bold text-purple-deep">Alamat Domisili</h3>
                        </div>

                        <div class="grid sm:grid-cols-3 gap-4 pt-1">
                            {{-- 1. NEGARA --}}
                            <div class="relative" @click.outside="createCountryOpen = false">
                                <label class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">Negara <span class="text-red-500">*</span></label>
                                <input type="hidden" name="country" :value="newCounselor.country">
                                <button type="button" :disabled="!isAddressEnabled" @click="isAddressEnabled && (createCountryOpen ? (createCountryOpen = false) : openCreateCountry())"
                                    class="w-full px-4 py-2.5 rounded-xl text-sm flex items-center justify-between text-left transition"
                                    :class="isAddressEnabled ? 'bg-white border border-[#D9C2F0] hover:border-[#B59BD6] cursor-pointer text-[#2A2035] focus:outline-none focus:ring-2 focus:ring-purple-deep' : 'bg-[#F7F4FB] border border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'">
                                    <span x-text="newCounselor.country || 'Indonesia'"></span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[#827299] transition-transform duration-200" :class="{ 'rotate-180': createCountryOpen }"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div x-cloak x-show="createCountryOpen" class="absolute z-50 mt-1.5 w-full bg-white rounded-xl shadow-xl border border-[#EDE1FA] overflow-hidden">
                                    <div class="p-2.5 border-b border-[#EDE1FA] bg-[#FAF8FD]">
                                        <input type="text" x-ref="createCountrySearchInput" x-model="createCountrySearch" placeholder="Cari negara..." class="w-full px-3 py-1.5 bg-white border border-[#D9C2F0] rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-purple-deep text-[#2A2035]">
                                    </div>
                                    <div class="max-h-48 overflow-y-auto divide-y divide-[#FAF8FD] py-1 custom-scrollbar">
                                        <template x-for="c in createCountryList" :key="c">
                                            <div @click="selectCreateCountry(c)" class="px-4 py-2 text-xs sm:text-sm cursor-pointer flex items-center justify-between transition hover:bg-[#FAF8FD]" :class="{ 'bg-[#F3EBFC] text-purple-deep font-bold': newCounselor.country === c, 'text-[#2A2035]': newCounselor.country !== c }">
                                                <span x-text="c"></span>
                                                <svg x-show="newCounselor.country === c" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-purple-deep"><polyline points="20 6 9 17 4 12"/></svg>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- 2. PROVINSI --}}
                            <div class="relative" @click.outside="createProvinceOpen = false">
                                <label class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">
                                    Provinsi
                                    <span x-show="newCounselor.country === 'Indonesia'" class="text-red-500">*</span>
                                    <span x-show="newCounselor.country !== 'Indonesia'" class="text-[11px] font-normal text-[#9D8EB0]">(Khusus Indonesia)</span>
                                </label>
                                <input type="hidden" name="province" :value="newCounselor.country === 'Indonesia' ? newCounselor.province : ''">
                                <button type="button" :disabled="!isAddressEnabled || newCounselor.country !== 'Indonesia'" @click="isAddressEnabled && newCounselor.country === 'Indonesia' && (createProvinceOpen ? (createProvinceOpen = false) : openCreateProvince())"
                                    class="w-full px-4 py-2.5 rounded-xl text-sm flex items-center justify-between text-left transition"
                                    :class="(isAddressEnabled && newCounselor.country === 'Indonesia') ? 'bg-white border border-[#D9C2F0] hover:border-[#B59BD6] cursor-pointer text-[#2A2035] focus:outline-none focus:ring-2 focus:ring-purple-deep' : 'bg-[#F7F4FB] border border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'">
                                    <span x-text="newCounselor.country === 'Indonesia' ? (newCounselor.province || '-- Pilih Provinsi --') : '-'"></span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[#827299] transition-transform duration-200" :class="{ 'rotate-180': createProvinceOpen }"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div x-cloak x-show="newCounselor.country === 'Indonesia' && createProvinceOpen" class="absolute z-50 mt-1.5 w-full bg-white rounded-xl shadow-xl border border-[#EDE1FA] overflow-hidden">
                                    <div class="p-2.5 border-b border-[#EDE1FA] bg-[#FAF8FD]">
                                        <input type="text" x-ref="createProvinceSearchInput" x-model="createProvinceSearch" placeholder="Cari provinsi..." class="w-full px-3 py-1.5 bg-white border border-[#D9C2F0] rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-purple-deep text-[#2A2035]">
                                    </div>
                                    <div class="max-h-48 overflow-y-auto divide-y divide-[#FAF8FD] py-1 custom-scrollbar">
                                        <template x-for="p in createProvinceList" :key="p">
                                            <div @click="selectCreateProvince(p)" class="px-4 py-2 text-xs sm:text-sm cursor-pointer flex items-center justify-between transition hover:bg-[#FAF8FD]" :class="{ 'bg-[#F3EBFC] text-purple-deep font-bold': newCounselor.province === p, 'text-[#2A2035]': newCounselor.province !== p }">
                                                <span x-text="p"></span>
                                                <svg x-show="newCounselor.province === p" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-purple-deep"><polyline points="20 6 9 17 4 12"/></svg>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- 3. KOTA / KABUPATEN --}}
                            <div class="relative" @click.outside="createCityOpen = false">
                                <label class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">
                                    Kota / Kabupaten
                                    <span x-show="newCounselor.country === 'Indonesia'" class="text-red-500">*</span>
                                    <span x-show="newCounselor.country !== 'Indonesia'" class="text-[11px] font-normal text-[#9D8EB0]">(Khusus Indonesia)</span>
                                </label>
                                <input type="hidden" name="city" :value="newCounselor.country === 'Indonesia' ? newCounselor.city : ''">
                                <button type="button" :disabled="!isAddressEnabled || newCounselor.country !== 'Indonesia' || !newCounselor.province" @click="isAddressEnabled && newCounselor.country === 'Indonesia' && newCounselor.province && (createCityOpen ? (createCityOpen = false) : openCreateCity())"
                                    class="w-full px-4 py-2.5 rounded-xl text-sm flex items-center justify-between text-left transition"
                                    :class="(isAddressEnabled && newCounselor.country === 'Indonesia' && newCounselor.province) ? 'bg-white border border-[#D9C2F0] hover:border-[#B59BD6] cursor-pointer text-[#2A2035] focus:outline-none focus:ring-2 focus:ring-purple-deep' : 'bg-[#F7F4FB] border border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'">
                                    <span x-text="newCounselor.country === 'Indonesia' ? (newCounselor.province ? (newCounselor.city || '-- Pilih Kota / Kabupaten --') : '-') : '-'"></span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[#827299] transition-transform duration-200" :class="{ 'rotate-180': createCityOpen }"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div x-cloak x-show="newCounselor.country === 'Indonesia' && newCounselor.province && createCityOpen" class="absolute z-50 mt-1.5 w-full bg-white rounded-xl shadow-xl border border-[#EDE1FA] overflow-hidden">
                                    <div class="p-2.5 border-b border-[#EDE1FA] bg-[#FAF8FD]">
                                        <input type="text" x-ref="createCitySearchInput" x-model="createCitySearch" :placeholder="newCounselor.province ? 'Cari kota di ' + newCounselor.province + '...' : 'Cari kota / kabupaten...'" class="w-full px-3 py-1.5 bg-white border border-[#D9C2F0] rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-purple-deep text-[#2A2035]">
                                    </div>
                                    <div class="max-h-48 overflow-y-auto divide-y divide-[#FAF8FD] py-1 custom-scrollbar">
                                        <template x-for="c in createCityList" :key="c">
                                            <div @click="selectCreateCity(c)" class="px-4 py-2 text-xs sm:text-sm cursor-pointer flex items-center justify-between transition hover:bg-[#FAF8FD]" :class="{ 'bg-[#F3EBFC] text-purple-deep font-bold': newCounselor.city === c, 'text-[#2A2035]': newCounselor.city !== c }">
                                                <span x-text="c"></span>
                                                <svg x-show="newCounselor.city === c" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-purple-deep"><polyline points="20 6 9 17 4 12"/></svg>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- 4. ALAMAT LENGKAP --}}
                            <div class="sm:col-span-3">
                                <label for="create_address" class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">Alamat Lengkap <span class="text-[#827299] font-normal">(Jalan, Nomor, RT/RW, Kelurahan, Kecamatan, Kode Pos)</span></label>
                                <textarea id="create_address" name="address" rows="2" x-model="newCounselor.address"
                                    :disabled="!isAddressEnabled"
                                    :class="isAddressEnabled ? 'bg-white border-[#D9C2F0]' : 'bg-[#F7F4FB] border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'"
                                    :placeholder="isAddressEnabled ? 'Contoh: Jl. CitraLand CBD Boulevard No. 8, Sambikerep, Surabaya, 60219' : ''"
                                    class="w-full px-4 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035] transition"></textarea>
                                @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- CARD 3: KUALIFIKASI, IZIN PRAKTIK & STATUS --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-5">
                        <div class="pb-3 border-b border-[#EDE1FA]">
                            <h3 class="text-base font-bold text-purple-deep">Kualifikasi, Izin Praktik & Status</h3>
                        </div>

                        {{-- Bidang Spesialisasi --}}
                        <div>
                            <label for="create_specialization" class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">Bidang Spesialisasi / Keahlian <span class="text-red-500">*</span></label>
                            <select id="create_specialization" name="specialization" x-model="newCounselor.specialization" required
                                :disabled="!isSpecializationEnabled"
                                :class="isSpecializationEnabled ? 'bg-white border-[#D9C2F0]' : 'bg-[#F7F4FB] border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'"
                                class="w-full px-4 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035]">
                                <option value="" disabled selected>-- Pilih Bidang Spesialisasi / Keahlian --</option>
                                @foreach(\App\Models\Counselor::SPECIALIZATIONS as $spec)
                                    <option value="{{ $spec }}">{{ $spec }}</option>
                                @endforeach
                            </select>
                            @error('specialization') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Nomor SIPP & STR --}}
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label for="create_sipp_number" class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">Nomor SIPP (Surat Izin Praktik) <span class="text-red-500">*</span></label>
                                <input type="text" id="create_sipp_number" name="sipp_number" x-model="newCounselor.sipp_number"
                                    :disabled="!isSippEnabled"
                                    :class="isSippEnabled ? 'bg-white border-[#D9C2F0]' : 'bg-[#F7F4FB] border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'"
                                    :placeholder="isSippEnabled ? 'Contoh: SIPP-12345/2024' : ''"
                                    class="w-full px-4 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035]">
                                @error('sipp_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="create_str_number" class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">Nomor STR (Surat Tanda Registrasi) <span class="text-red-500">*</span></label>
                                <input type="text" id="create_str_number" name="str_number" x-model="newCounselor.str_number"
                                    :disabled="!isStrEnabled"
                                    :class="isStrEnabled ? 'bg-white border-[#D9C2F0]' : 'bg-[#F7F4FB] border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'"
                                    :placeholder="isStrEnabled ? 'Contoh: STR-98765/2024' : ''"
                                    class="w-full px-4 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035]">
                                @error('str_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Status Keaktifan --}}
                        <div>
                            <label for="create_status" class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">Status Keaktifan Konselor <span class="text-red-500">*</span></label>
                            <select id="create_status" name="status" x-model="newCounselor.status" required
                                :disabled="!isStatusEnabled"
                                :class="isStatusEnabled ? 'bg-white border-[#D9C2F0]' : 'bg-[#F7F4FB] border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'"
                                class="w-full px-4 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035]">
                                <option value="active" class="text-emerald-700 font-semibold">Aktif (Siap Menerima Pasangan Klien / Sesi)</option>
                                <option value="inactive" class="text-red-600 font-semibold">Nonaktif (Sedang Tidak Bertugas)</option>
                            </select>
                            @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- CARD 3: CATATAN & KETERANGAN TAMBAHAN --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-4">
                        <div class="pb-3 border-b border-[#EDE1FA]">
                            <h3 class="text-base font-bold text-purple-deep">Catatan & Keterangan Tambahan</h3>
                        </div>

                        <div>
                            <textarea id="create_notes" name="notes" x-model="newCounselor.notes" rows="3"
                                :disabled="!isNotesEnabled"
                                :class="isNotesEnabled ? 'bg-white border-[#D9C2F0]' : 'bg-[#F7F4FB] border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'"
                                :placeholder="isNotesEnabled ? 'Contoh: Bersedia praktek hari Selasa & Kamis pukul 09:00 - 15:00 WIB...' : ''"
                                class="w-full px-4 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035] leading-relaxed"></textarea>
                            @error('notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Modal Footer Actions --}}
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="closeCreateModal()" 
                            class="px-5 py-2.5 rounded-xl text-sm font-semibold text-[#6B5B85] border border-[#D9C2F0] hover:bg-white transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" 
                            class="px-6 py-2.5 rounded-xl text-sm font-bold bg-purple-deep text-white hover:opacity-90 transition shadow-sm flex items-center gap-2 cursor-pointer">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                            <span>Simpan Konselor</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- SUB-MODAL: KONFIRMASI BUANG PERUBAHAN FORM CREATE                          --}}
    {{-- ========================================================================= --}}
    <div x-cloak x-show="createDiscardModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div x-show="createDiscardModalOpen" 
            x-transition:enter="ease-out duration-200" 
            x-transition:enter-start="opacity-0" 
            x-transition:enter-end="opacity-100" 
            class="fixed inset-0 bg-black/60 backdrop-blur-xs" 
            @click="createDiscardModalOpen = false"></div>
        
        <div x-show="createDiscardModalOpen" 
            x-transition:enter="ease-out duration-200" 
            x-transition:enter-start="opacity-0 scale-95" 
            x-transition:enter-end="opacity-100 scale-100" 
            class="relative bg-white rounded-3xl shadow-2xl border border-[#EDE1FA] max-w-md w-full p-7 z-10 space-y-5"
            @click.stop>
            
            <div class="w-14 h-14 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center mx-auto shadow-xs">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            
            <div class="text-center space-y-2">
                <h3 class="text-lg font-bold text-purple-deep">Batalkan Tambah Konselor?</h3>
                <p class="text-sm text-[#6B5B85] leading-relaxed">
                    Data isian yang telah Anda masukkan belum disimpan dan akan hilang jika Anda keluar.
                </p>
            </div>

            <div class="flex items-center justify-center gap-3 pt-2">
                <button type="button" @click="createDiscardModalOpen = false" class="flex-1 px-5 py-3 rounded-xl text-sm font-semibold text-[#6B5B85] border border-[#D9C2F0] hover:bg-[#FAF8FD] transition cursor-pointer">
                    Lanjut Mengisi
                </button>
                <button type="button" @click="closeCreateModal(true)" class="flex-1 px-5 py-3 rounded-xl text-sm font-bold bg-red-600 text-white hover:bg-red-700 transition shadow-xs cursor-pointer">
                    Buang Isian
                </button>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL POP-UP DETAIL & EDIT DATA KONSELOR                                   --}}
    {{-- ========================================================================= --}}
    <div x-cloak x-show="editModalOpen" @keydown.escape.window="closeEditModal()" class="fixed inset-0 z-50 overflow-y-auto">
        {{-- Backdrop blur --}}
        <div x-show="editModalOpen" 
            x-transition:enter="ease-out duration-200" 
            x-transition:enter-start="opacity-0" 
            x-transition:enter-end="opacity-100" 
            class="fixed inset-0 bg-black/50 backdrop-blur-xs" 
            @click="closeEditModal()"></div>

        <div class="min-h-full flex items-center justify-center p-4">
            <div x-show="editModalOpen" 
                x-transition:enter="ease-out duration-200" 
                x-transition:enter-start="opacity-0 scale-95" 
                x-transition:enter-end="opacity-100 scale-100" 
                class="relative bg-white rounded-2xl shadow-2xl border border-[#EDE1FA] max-w-3xl w-full max-h-[90vh] flex flex-col z-10 my-6"
                @click.stop>
                
                {{-- Modal Header (View Mode) --}}
                <div x-show="!isEditing" class="flex items-center justify-between px-6 py-4.5 border-b border-[#EDE1FA] bg-white rounded-t-2xl">
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-purple-deep">Detail Data Konselor</h3>
                        <p class="text-xs sm:text-[13px] text-[#6B5B85] mt-0.5">Informasi profil, kualifikasi, dan status konselor</p>
                    </div>
                    <button type="button" @click="closeEditModal()" class="w-8 h-8 rounded-lg text-[#827299] hover:text-purple-deep hover:bg-purple-deep/10 flex items-center justify-center transition cursor-pointer" title="Tutup">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                {{-- Modal Header (Edit Mode) --}}
                <div x-show="isEditing" class="flex items-center justify-between px-6 py-4.5 border-b border-[#EDE1FA] bg-white rounded-t-2xl">
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-purple-deep">Edit Data Konselor</h3>
                        <p class="text-xs sm:text-[13px] text-[#6B5B85] mt-0.5">Perbarui data identitas, kualifikasi, atau status konselor</p>
                    </div>
                    <button type="button" @click="closeEditModal()" class="w-8 h-8 rounded-lg text-[#827299] hover:text-purple-deep hover:bg-purple-deep/10 flex items-center justify-center transition cursor-pointer" title="Tutup">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                {{-- ================================================================= --}}
                {{-- 1. VIEW MODE (Hanya Melihat Data Konselor)                        --}}
                {{-- ================================================================= --}}
                <div x-show="!isEditing" class="flex-1 overflow-y-auto bg-[#FAF8FD] p-6 space-y-5">
                    {{-- CARD 1: DATA IDENTITAS & KONTAK (VIEW) --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-5">
                        <div class="pb-3">
                            <h3 class="text-base font-bold text-purple-deep">Data Identitas &amp; Kontak</h3>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center gap-5 p-4 rounded-xl bg-white border border-[#EDE1FA] shadow-2xs">
                            <div class="flex-shrink-0">
                                <template x-if="editingCounselor.photo_url">
                                    <img :src="editingCounselor.photo_url" alt="Foto Profil" class="w-20 h-20 rounded-full object-cover shadow-sm border-2 border-white ring-2 ring-purple-100">
                                </template>
                                <template x-if="!editingCounselor.photo_url">
                                    <div class="w-20 h-20 rounded-full grad-purple text-white flex items-center justify-center font-bold text-2xl shadow-sm border-2 border-white ring-2 ring-purple-100">
                                        <span x-text="editingCounselor.name ? editingCounselor.name.trim().charAt(0).toUpperCase() : '?'"></span>
                                    </div>
                                </template>
                            </div>

                            <div class="flex-1 text-center sm:text-left space-y-1.5 min-w-0">
                                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5">
                                    <h4 class="text-lg font-bold text-purple-deep truncate" x-text="editingCounselor.name"></h4>
                                    <template x-if="editingCounselor.status === 'active'">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span>Aktif</span>
                                        </span>
                                    </template>
                                    <template x-if="editingCounselor.status !== 'active'">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                            <span>Nonaktif</span>
                                        </span>
                                    </template>
                                </div>
                                <p class="text-xs sm:text-sm text-[#6B5B85] font-medium" x-text="editingCounselor.specialization || 'Psikolog / Konselor Pendamping'"></p>
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-4 pt-1">
                            <div class="p-3.5 rounded-xl bg-white border border-[#EDE1FA] shadow-2xs">
                                <span class="block text-xs font-semibold text-[#827299] mb-1">No. Telepon / WhatsApp</span>
                                <p class="text-sm font-semibold text-[#2A2035] flex items-center gap-2">
                                    <svg class="text-purple-deep shrink-0" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                    <span x-text="editingCounselor.phone || '-'"></span>
                                </p>
                            </div>

                            <div class="p-3.5 rounded-xl bg-white border border-[#EDE1FA] shadow-2xs">
                                <span class="block text-xs font-semibold text-[#827299] mb-1">Email</span>
                                <p class="text-sm font-semibold text-[#2A2035] flex items-center gap-2">
                                    <svg class="text-purple-deep shrink-0" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                    <span x-text="editingCounselor.email || '-'" class="truncate"></span>
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- CARD 2: ALAMAT DOMISILI (VIEW) --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-4">
                        <div class="pb-3">
                            <h3 class="text-base font-bold text-purple-deep">Alamat Domisili</h3>
                        </div>

                        <div class="grid sm:grid-cols-3 gap-4">
                            <div class="p-3.5 rounded-xl bg-white border border-[#EDE1FA] shadow-2xs">
                                <span class="block text-xs font-semibold text-[#827299] mb-1">Negara</span>
                                <p class="text-sm font-semibold text-[#2A2035]" x-text="editingCounselor.country || 'Indonesia'"></p>
                            </div>

                            <div class="p-3.5 rounded-xl bg-white border border-[#EDE1FA] shadow-2xs">
                                <span class="block text-xs font-semibold text-[#827299] mb-1">Provinsi</span>
                                <p class="text-sm font-semibold text-[#2A2035]" x-text="editingCounselor.province || '-'"></p>
                            </div>

                            <div class="p-3.5 rounded-xl bg-white border border-[#EDE1FA] shadow-2xs">
                                <span class="block text-xs font-semibold text-[#827299] mb-1">Kota / Kabupaten</span>
                                <p class="text-sm font-semibold text-[#2A2035]" x-text="editingCounselor.city || '-'"></p>
                            </div>

                            <div class="sm:col-span-3 p-3.5 rounded-xl bg-white border border-[#EDE1FA] shadow-2xs">
                                <span class="block text-xs font-semibold text-[#827299] mb-1">Alamat Lengkap</span>
                                <p class="text-sm font-semibold text-[#2A2035] flex items-start gap-2">
                                    <svg class="text-purple-deep shrink-0 mt-0.5" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    <span x-text="editingCounselor.full_address || editingCounselor.address || '-'" class="leading-relaxed whitespace-pre-line"></span>
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- CARD 3: KUALIFIKASI & LEGALITAS (VIEW) --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-4">
                        <div class="pb-3">
                            <h3 class="text-base font-bold text-purple-deep">Kualifikasi, Izin Praktik &amp; Status</h3>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2 p-3.5 rounded-xl bg-white border border-[#EDE1FA] shadow-2xs">
                                <span class="block text-xs font-semibold text-[#827299] mb-1">Bidang Spesialisasi / Keahlian</span>
                                <p class="text-sm font-bold text-purple-deep" x-text="editingCounselor.specialization || '-'"></p>
                            </div>

                            <div class="p-3.5 rounded-xl bg-white border border-[#EDE1FA] shadow-2xs">
                                <span class="block text-xs font-semibold text-[#827299] mb-1">Nomor SIPP (Surat Izin Praktik)</span>
                                <p class="text-sm font-semibold text-[#2A2035]" x-text="editingCounselor.sipp_number || '-'"></p>
                            </div>

                            <div class="p-3.5 rounded-xl bg-white border border-[#EDE1FA] shadow-2xs">
                                <span class="block text-xs font-semibold text-[#827299] mb-1">Nomor STR (Surat Tanda Registrasi)</span>
                                <p class="text-sm font-semibold text-[#2A2035]" x-text="editingCounselor.str_number || '-'"></p>
                            </div>

                            <div class="sm:col-span-2 p-3.5 rounded-xl bg-white border border-[#EDE1FA] shadow-2xs">
                                <span class="block text-xs font-semibold text-[#827299] mb-1">Status Keaktifan</span>
                                <p class="text-sm font-semibold" :class="editingCounselor.status === 'active' ? 'text-emerald-700' : 'text-red-600'" x-text="editingCounselor.status === 'active' ? 'Aktif (Siap Menerima Pasangan Klien / Sesi)' : 'Nonaktif (Sedang Tidak Bertugas)'"></p>
                            </div>
                        </div>
                    </div>

                    {{-- CARD 4: CATATAN (VIEW) --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-3">
                        <div class="pb-3">
                            <h3 class="text-base font-bold text-purple-deep">Catatan &amp; Keterangan Tambahan</h3>
                        </div>
                        <div class="p-4 rounded-xl bg-white border border-[#EDE1FA] shadow-2xs">
                            <template x-if="editingCounselor.notes">
                                <p class="text-sm text-[#4A3E5C] leading-relaxed whitespace-pre-line" x-text="editingCounselor.notes"></p>
                            </template>
                            <template x-if="!editingCounselor.notes">
                                <p class="text-xs text-gray-400 italic">Tidak ada catatan tambahan untuk konselor ini.</p>
                            </template>
                        </div>
                    </div>

                    {{-- VIEW MODE FOOTER ACTIONS --}}
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="startEditing()" 
                            class="px-5 py-2.5 rounded-xl text-sm font-semibold text-purple-deep border border-[#D9C2F0] hover:bg-[#FAF8FD] hover:border-purple-deep transition cursor-pointer flex items-center gap-2 shadow-2xs">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            <span>Edit Data</span>
                        </button>

                        <button type="button" @click="deleteCounselorModalOpen = true" 
                            class="px-5 py-2.5 rounded-xl text-sm font-semibold text-red-600 border border-red-200 hover:bg-red-50 transition cursor-pointer flex items-center gap-2 shadow-2xs">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                            <span>Hapus Data</span>
                        </button>
                    </div>
                </div>

                {{-- ================================================================= --}}
                {{-- 2. EDIT MODE (Formulir Pengubahan Data Konselor)                   --}}
                {{-- ================================================================= --}}
                <form x-show="isEditing" :action="editingCounselor.update_url" method="POST" enctype="multipart/form-data" class="flex-1 overflow-y-auto bg-[#FAF8FD] p-6 space-y-5" data-no-guard="true">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="remove_photo" :value="editRemovePhoto ? '1' : '0'">

                    {{-- CARD 1: DATA IDENTITAS & KONTAK (EDIT) --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-5">
                        <div class="pb-3">
                            <h3 class="text-base font-bold text-purple-deep">Data Identitas &amp; Kontak</h3>
                        </div>

                        {{-- Foto Profil Konselor --}}
                        <div class="flex flex-col sm:flex-row items-center gap-5 p-4 rounded-xl bg-gray-50 border border-gray-200">
                            <div class="relative group flex-shrink-0">
                                <template x-if="editPhotoPreview">
                                    <img :src="editPhotoPreview" alt="Preview Foto" class="w-20 h-20 rounded-full object-cover shadow-sm border-2 border-white ring-2 ring-gray-200">
                                </template>
                                <template x-if="!editPhotoPreview">
                                    <div class="w-20 h-20 rounded-full grad-purple text-white flex items-center justify-center font-bold text-2xl shadow-sm border-2 border-white ring-2 ring-gray-200">
                                        <span x-text="editingCounselor.name ? editingCounselor.name.trim().charAt(0).toUpperCase() : '?'"></span>
                                    </div>
                                </template>

                                <button type="button" 
                                    x-show="editPhotoPreview" 
                                    @click="removeEditPhoto()" 
                                    class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full p-1 shadow-sm hover:bg-red-600 transition cursor-pointer" 
                                    title="Hapus Foto">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </div>

                            <div class="flex-1 text-center sm:text-left space-y-1.5">
                                <label class="block text-xs sm:text-[13px] font-bold text-[#2A2035]">Foto Profil Konselor</label>
                                <p class="text-xs text-gray-500">Format JPG, PNG, atau WebP. Ukuran maksimal 2 MB.</p>
                                
                                <div class="pt-1">
                                    <input type="file" id="edit_photo_input" name="photo" accept="image/*" class="hidden" @change="handleEditPhotoSelect($event)">
                                    <label for="edit_photo_input" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border border-gray-300 bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50 transition cursor-pointer shadow-2xs">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                                        <span x-text="editPhotoPreview ? 'Ganti Foto Profil' : 'Unggah Foto Profil'"></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Field: Nama Konselor --}}
                        <div>
                            <label for="edit_name" class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">Nama Konselor / Psikolog <span class="text-red-500">*</span></label>
                            <input type="text" id="edit_name" name="name" x-model="editingCounselor.name" required
                                placeholder="Contoh: Dr. Amanda Wijaya, M.Psi., Psikolog"
                                class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035]">
                        </div>

                        {{-- Kontak: No Telepon & Email --}}
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label for="edit_phone" class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">No. Telepon / WhatsApp</label>
                                <input type="text" id="edit_phone" name="phone" x-model="editingCounselor.phone"
                                    inputmode="numeric"
                                    maxlength="13"
                                    @input="editingCounselor.phone = $el.value.replace(/[^0-9]/g, '').slice(0, 13); $el.value = editingCounselor.phone"
                                    @keydown="if (!/[0-9]/.test($event.key) && !['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab', 'Home', 'End'].includes($event.key) && !$event.ctrlKey && !$event.metaKey) $event.preventDefault()"
                                    placeholder="Contoh: 081234567890 (maks. 13 digit)"
                                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035]">
                            </div>

                            <div>
                                <label for="edit_email" class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">Email</label>
                                <input type="email" id="edit_email" name="email" x-model="editingCounselor.email"
                                    placeholder="Contoh: amanda.wijaya@uc.ac.id"
                                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035]">
                            </div>
                        </div>
                    </div>

                    {{-- CARD 2: ALAMAT DOMISILI (EDIT) --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-5">
                        <div class="pb-3">
                            <h3 class="text-base font-bold text-purple-deep">Alamat Domisili</h3>
                        </div>

                        {{-- Alamat Domisili / Tempat Tinggal (Struktur Lengkap seperti Klien) --}}
                        <div class="grid sm:grid-cols-3 gap-4 pt-1">
                            {{-- 1. NEGARA --}}
                            <div class="relative" @click.outside="editCountryOpen = false">
                                <label class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">Negara</label>
                                <input type="hidden" name="country" :value="editingCounselor.country">
                                <button type="button" @click="editCountryOpen ? (editCountryOpen = false) : openEditCountry()"
                                    class="w-full px-4 py-2.5 rounded-xl text-sm flex items-center justify-between text-left transition bg-white border border-[#D9C2F0] hover:border-[#B59BD6] cursor-pointer text-[#2A2035] focus:outline-none focus:ring-2 focus:ring-purple-deep">
                                    <span x-text="editingCounselor.country || 'Indonesia'"></span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[#827299] transition-transform duration-200" :class="{ 'rotate-180': editCountryOpen }"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div x-cloak x-show="editCountryOpen" class="absolute z-50 mt-1.5 w-full bg-white rounded-xl shadow-xl border border-[#EDE1FA] overflow-hidden">
                                    <div class="p-2.5 border-b border-[#EDE1FA] bg-[#FAF8FD]">
                                        <input type="text" x-ref="editCountrySearchInput" x-model="editCountrySearch" placeholder="Cari negara..." class="w-full px-3 py-1.5 bg-white border border-[#D9C2F0] rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-purple-deep text-[#2A2035]">
                                    </div>
                                    <div class="max-h-48 overflow-y-auto divide-y divide-[#FAF8FD] py-1 custom-scrollbar">
                                        <template x-for="c in editCountryList" :key="c">
                                            <div @click="selectEditCountry(c)" class="px-4 py-2 text-xs sm:text-sm cursor-pointer flex items-center justify-between transition hover:bg-[#FAF8FD]" :class="{ 'bg-[#F3EBFC] text-purple-deep font-bold': editingCounselor.country === c, 'text-[#2A2035]': editingCounselor.country !== c }">
                                                <span x-text="c"></span>
                                                <svg x-show="editingCounselor.country === c" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-purple-deep"><polyline points="20 6 9 17 4 12"/></svg>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- 2. PROVINSI --}}
                            <div class="relative" @click.outside="editProvinceOpen = false">
                                <label class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">
                                    Provinsi
                                    <span x-show="editingCounselor.country !== 'Indonesia'" class="text-[11px] font-normal text-[#9D8EB0]">(Khusus Indonesia)</span>
                                </label>
                                <input type="hidden" name="province" :value="editingCounselor.country === 'Indonesia' ? editingCounselor.province : ''">
                                <button type="button" :disabled="editingCounselor.country !== 'Indonesia'" @click="editingCounselor.country === 'Indonesia' && (editProvinceOpen ? (editProvinceOpen = false) : openEditProvince())"
                                    class="w-full px-4 py-2.5 rounded-xl text-sm flex items-center justify-between text-left transition"
                                    :class="editingCounselor.country === 'Indonesia' ? 'bg-white border border-[#D9C2F0] hover:border-[#B59BD6] cursor-pointer text-[#2A2035] focus:outline-none focus:ring-2 focus:ring-purple-deep' : 'bg-[#F7F4FB] border border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'">
                                    <span x-text="editingCounselor.country === 'Indonesia' ? (editingCounselor.province || '-- Pilih Provinsi --') : '-'"></span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[#827299] transition-transform duration-200" :class="{ 'rotate-180': editProvinceOpen }"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div x-cloak x-show="editingCounselor.country === 'Indonesia' && editProvinceOpen" class="absolute z-50 mt-1.5 w-full bg-white rounded-xl shadow-xl border border-[#EDE1FA] overflow-hidden">
                                    <div class="p-2.5 border-b border-[#EDE1FA] bg-[#FAF8FD]">
                                        <input type="text" x-ref="editProvinceSearchInput" x-model="editProvinceSearch" placeholder="Cari provinsi..." class="w-full px-3 py-1.5 bg-white border border-[#D9C2F0] rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-purple-deep text-[#2A2035]">
                                    </div>
                                    <div class="max-h-48 overflow-y-auto divide-y divide-[#FAF8FD] py-1 custom-scrollbar">
                                        <template x-for="p in editProvinceList" :key="p">
                                            <div @click="selectEditProvince(p)" class="px-4 py-2 text-xs sm:text-sm cursor-pointer flex items-center justify-between transition hover:bg-[#FAF8FD]" :class="{ 'bg-[#F3EBFC] text-purple-deep font-bold': editingCounselor.province === p, 'text-[#2A2035]': editingCounselor.province !== p }">
                                                <span x-text="p"></span>
                                                <svg x-show="editingCounselor.province === p" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-purple-deep"><polyline points="20 6 9 17 4 12"/></svg>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- 3. KOTA / KABUPATEN --}}
                            <div class="relative" @click.outside="editCityOpen = false">
                                <label class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">
                                    Kota / Kabupaten
                                    <span x-show="editingCounselor.country !== 'Indonesia'" class="text-[11px] font-normal text-[#9D8EB0]">(Khusus Indonesia)</span>
                                </label>
                                <input type="hidden" name="city" :value="editingCounselor.country === 'Indonesia' ? editingCounselor.city : ''">
                                <button type="button" :disabled="editingCounselor.country !== 'Indonesia' || !editingCounselor.province" @click="editingCounselor.country === 'Indonesia' && editingCounselor.province && (editCityOpen ? (editCityOpen = false) : openEditCity())"
                                    class="w-full px-4 py-2.5 rounded-xl text-sm flex items-center justify-between text-left transition"
                                    :class="(editingCounselor.country === 'Indonesia' && editingCounselor.province) ? 'bg-white border border-[#D9C2F0] hover:border-[#B59BD6] cursor-pointer text-[#2A2035] focus:outline-none focus:ring-2 focus:ring-purple-deep' : 'bg-[#F7F4FB] border border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'">
                                    <span x-text="editingCounselor.country === 'Indonesia' ? (editingCounselor.province ? (editingCounselor.city || '-- Pilih Kota / Kabupaten --') : '-') : '-'"></span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[#827299] transition-transform duration-200" :class="{ 'rotate-180': editCityOpen }"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div x-cloak x-show="editingCounselor.country === 'Indonesia' && editingCounselor.province && editCityOpen" class="absolute z-50 mt-1.5 w-full bg-white rounded-xl shadow-xl border border-[#EDE1FA] overflow-hidden">
                                    <div class="p-2.5 border-b border-[#EDE1FA] bg-[#FAF8FD]">
                                        <input type="text" x-ref="editCitySearchInput" x-model="editCitySearch" :placeholder="editingCounselor.province ? 'Cari kota di ' + editingCounselor.province + '...' : 'Cari kota / kabupaten...'" class="w-full px-3 py-1.5 bg-white border border-[#D9C2F0] rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-purple-deep text-[#2A2035]">
                                    </div>
                                    <div class="max-h-48 overflow-y-auto divide-y divide-[#FAF8FD] py-1 custom-scrollbar">
                                        <template x-for="c in editCityList" :key="c">
                                            <div @click="selectEditCity(c)" class="px-4 py-2 text-xs sm:text-sm cursor-pointer flex items-center justify-between transition hover:bg-[#FAF8FD]" :class="{ 'bg-[#F3EBFC] text-purple-deep font-bold': editingCounselor.city === c, 'text-[#2A2035]': editingCounselor.city !== c }">
                                                <span x-text="c"></span>
                                                <svg x-show="editingCounselor.city === c" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-purple-deep"><polyline points="20 6 9 17 4 12"/></svg>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- 4. ALAMAT LENGKAP --}}
                            <div class="sm:col-span-3">
                                <label for="edit_address" class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">Alamat Lengkap <span class="text-[#827299] font-normal">(Jalan, Nomor, RT/RW, Kelurahan, Kecamatan, Kode Pos)</span></label>
                                <textarea id="edit_address" name="address" rows="2" x-model="editingCounselor.address"
                                    placeholder="Contoh: Jl. CitraLand CBD Boulevard No. 8, Sambikerep, Surabaya, 60219"
                                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035] transition"></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- CARD 3: KUALIFIKASI, IZIN PRAKTIK & STATUS (EDIT) --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-5">
                        <div class="pb-3">
                            <h3 class="text-base font-bold text-purple-deep">Kualifikasi, Izin Praktik &amp; Status</h3>
                        </div>

                        {{-- Bidang Spesialisasi --}}
                        <div>
                            <label for="edit_specialization" class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">Bidang Spesialisasi / Keahlian</label>
                            <select id="edit_specialization" name="specialization" x-model="editingCounselor.specialization"
                                class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035]">
                                <option value="">-- Pilih Bidang Spesialisasi / Keahlian --</option>
                                @foreach(\App\Models\Counselor::SPECIALIZATIONS as $spec)
                                    <option value="{{ $spec }}">{{ $spec }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Nomor SIPP & STR --}}
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label for="edit_sipp_number" class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">Nomor SIPP (Surat Izin Praktik)</label>
                                <input type="text" id="edit_sipp_number" name="sipp_number" x-model="editingCounselor.sipp_number"
                                    placeholder="Contoh: SIPP-12345/2024"
                                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035]">
                            </div>

                            <div>
                                <label for="edit_str_number" class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">Nomor STR (Surat Tanda Registrasi)</label>
                                <input type="text" id="edit_str_number" name="str_number" x-model="editingCounselor.str_number"
                                    placeholder="Contoh: STR-98765/2024"
                                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035]">
                            </div>
                        </div>

                        {{-- Status Keaktifan --}}
                        <div>
                            <label for="edit_status" class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">Status Keaktifan Konselor <span class="text-red-500">*</span></label>
                            <select id="edit_status" name="status" x-model="editingCounselor.status" required
                                class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035]">
                                <option value="active" class="text-emerald-700 font-semibold">Aktif (Siap Menerima Pasangan Klien / Sesi)</option>
                                <option value="inactive" class="text-red-600 font-semibold">Nonaktif (Sedang Tidak Bertugas)</option>
                            </select>
                        </div>
                    </div>

                    {{-- CARD 4: CATATAN & KETERANGAN TAMBAHAN (EDIT) --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-4">
                        <div class="pb-3">
                            <h3 class="text-base font-bold text-purple-deep">Catatan &amp; Keterangan Tambahan</h3>
                        </div>

                        <div>
                            <textarea id="edit_notes" name="notes" x-model="editingCounselor.notes" rows="3"
                                placeholder="Contoh: Bersedia praktek hari Selasa & Kamis pukul 09:00 - 15:00 WIB..."
                                class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035] leading-relaxed"></textarea>
                        </div>
                    </div>

                    {{-- EDIT MODE FOOTER ACTIONS --}}
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="cancelEditing()" 
                            class="px-5 py-2.5 rounded-xl text-sm font-semibold text-[#6B5B85] border border-[#D9C2F0] hover:bg-white transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" 
                            class="px-6 py-2.5 rounded-xl text-sm font-bold bg-purple-deep text-white hover:opacity-90 transition shadow-sm flex items-center gap-2 cursor-pointer">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- SUB-MODAL: KONFIRMASI BUANG PERUBAHAN FORM EDIT                            --}}
    {{-- ========================================================================= --}}
    <div x-cloak x-show="editDiscardModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div x-show="editDiscardModalOpen" 
            x-transition:enter="ease-out duration-200" 
            x-transition:enter-start="opacity-0" 
            x-transition:enter-end="opacity-100" 
            class="fixed inset-0 bg-black/60 backdrop-blur-xs" 
            @click="editDiscardModalOpen = false"></div>
        
        <div x-show="editDiscardModalOpen" 
            x-transition:enter="ease-out duration-200" 
            x-transition:enter-start="opacity-0 scale-95" 
            x-transition:enter-end="opacity-100 scale-100" 
            class="relative bg-white rounded-3xl shadow-2xl border border-[#EDE1FA] max-w-md w-full p-7 z-10 space-y-5"
            @click.stop>
            
            <div class="w-14 h-14 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center mx-auto shadow-xs">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            
            <div class="text-center space-y-2">
                <h3 class="text-lg font-bold text-purple-deep">Batalkan Perubahan?</h3>
                <p class="text-sm text-[#6B5B85] leading-relaxed">
                    Perubahan data konselor yang telah Anda masukkan belum disimpan dan akan hilang jika Anda keluar.
                </p>
            </div>

            <div class="flex items-center justify-center gap-3 pt-2">
                <button type="button" @click="editDiscardModalOpen = false" class="flex-1 px-5 py-3 rounded-xl text-sm font-semibold text-[#6B5B85] border border-[#D9C2F0] hover:bg-[#FAF8FD] transition cursor-pointer">
                    Lanjut Mengedit
                </button>
                <button type="button" @click="cancelEditing(true)" class="flex-1 px-5 py-3 rounded-xl text-sm font-bold bg-red-600 text-white hover:bg-red-700 transition shadow-xs cursor-pointer">
                    Buang Perubahan
                </button>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- SUB-MODAL: KONFIRMASI HAPUS DATA KONSELOR                                  --}}
    {{-- ========================================================================= --}}
    <div x-cloak x-show="deleteCounselorModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div x-show="deleteCounselorModalOpen" 
            x-transition:enter="ease-out duration-200" 
            x-transition:enter-start="opacity-0" 
            x-transition:enter-end="opacity-100" 
            class="fixed inset-0 bg-black/60 backdrop-blur-xs" 
            @click="deleteCounselorModalOpen = false"></div>
        
        <div x-show="deleteCounselorModalOpen" 
            x-transition:enter="ease-out duration-200" 
            x-transition:enter-start="opacity-0 scale-95" 
            x-transition:enter-end="opacity-100 scale-100" 
            class="relative bg-white rounded-3xl shadow-2xl border border-[#EDE1FA] max-w-md w-full p-7 z-10 space-y-5"
            @click.stop>
            
            <div class="w-14 h-14 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center mx-auto shadow-xs">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    <line x1="10" y1="11" x2="10" y2="17"></line>
                    <line x1="14" y1="11" x2="14" y2="17"></line>
                </svg>
            </div>
            
            <div class="text-center space-y-2">
                <h3 class="text-lg font-bold text-red-600">Hapus Data Konselor?</h3>
                <p class="text-sm text-[#6B5B85] leading-relaxed">
                    Apakah Anda yakin ingin menghapus data konselor <strong class="text-purple-deep" x-text="editingCounselor.name"></strong>? Data yang dihapus bersifat permanen dan tidak dapat dipulihkan kembali.
                </p>
            </div>

            <form :action="'{{ url('counselors') }}/' + editingCounselor.id" method="POST" class="flex items-center justify-center gap-3 pt-2">
                @csrf
                @method('DELETE')
                <button type="button" @click="deleteCounselorModalOpen = false" class="flex-1 px-5 py-3 rounded-xl text-sm font-semibold text-[#6B5B85] border border-[#D9C2F0] hover:bg-[#FAF8FD] transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="flex-1 px-5 py-3 rounded-xl text-sm font-bold bg-red-600 text-white hover:bg-red-700 transition shadow-xs cursor-pointer">
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
