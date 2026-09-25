@extends('layouts.dashboard')

@section('title', 'Kalender Operasional & Jadwal Praktik — UC PSC')
@section('page-title', 'Kalender Operasional')
@section('page-subtitle', 'Monitoring jadwal praktik konselor dan penugasan staff harian')

@section('content')
<div 
    x-data="googleCalendar(@js($calendarEvents), @js(auth()->user()))"
    x-cloak
    class="space-y-5"
>
    {{-- ========================================================================= --}}
    {{-- GOOGLE CALENDAR MAIN CARD                                                 --}}
    {{-- ========================================================================= --}}
    <div class="bg-white rounded-3xl border border-[#EDE1FA] shadow-sm overflow-hidden flex flex-col">
        
        {{-- ===== TOOLBAR ATAS (GOOGLE CALENDAR STYLE) ===== --}}
        <div class="p-4 sm:p-5 border-b border-[#EDE1FA] flex flex-col xl:flex-row xl:items-center justify-between gap-4">
            
            {{-- Kelompok Kiri: Navigasi Periode & Hari Ini --}}
            <div class="flex items-center gap-2.5 flex-wrap">
                <button 
                    type="button" 
                    @click="goToday()" 
                    class="px-4 py-2 rounded-xl text-xs font-bold border border-[#D9C2F0] hover:bg-[#F7F5FB] text-[#2A2035] transition cursor-pointer shadow-2xs"
                >
                    Hari Ini
                </button>

                <div class="flex items-center gap-1 bg-[#F7F5FB] rounded-xl p-1 border border-[#EDE1FA]">
                    <button 
                        type="button" 
                        @click="prev()" 
                        class="p-1.5 rounded-lg hover:bg-white hover:text-purple-deep transition text-[#6B5B85] cursor-pointer"
                        title="Periode Sebelumnya"
                    >
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                    </button>
                    <button 
                        type="button" 
                        @click="next()" 
                        class="p-1.5 rounded-lg hover:bg-white hover:text-purple-deep transition text-[#6B5B85] cursor-pointer"
                        title="Periode Berikutnya"
                    >
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>
                </div>

                {{-- Judul Periode Aktif --}}
                <h2 class="text-lg sm:text-xl font-extrabold text-[#2A2035] min-w-[180px]" x-text="headerPeriodTitle"></h2>
            </div>

            {{-- Kelompok Tengah & Kanan: Filter & Switcher --}}
            <div class="flex items-center gap-2.5 flex-wrap xl:justify-end">
                
                {{-- Filter Konselor Dropdown --}}
                <div class="w-40 sm:w-48">
                    <select 
                        x-model="filterCounselorId" 
                        class="w-full px-3 py-2 rounded-xl border border-[#D9C2F0] bg-white text-xs font-medium focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035]"
                    >
                        <option value="">-- Semua Konselor --</option>
                        @foreach($counselors as $counselor)
                        <option value="{{ $counselor->id }}">{{ $counselor->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Staff Dropdown --}}
                <div class="w-36 sm:w-44">
                    <select 
                        x-model="filterStaffId" 
                        class="w-full px-3 py-2 rounded-xl border border-[#D9C2F0] bg-white text-xs font-medium focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035]"
                    >
                        <option value="">-- Semua Staff Tim --</option>
                        @foreach($staffList as $staff)
                        <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Switcher Tampilan (Bulan, Minggu, Agenda) --}}
                <div class="flex items-center bg-[#F7F5FB] p-1 rounded-xl border border-[#EDE1FA] text-xs font-bold text-[#6B5B85]">
                    <button 
                        type="button" 
                        @click="viewMode = 'month'" 
                        :class="viewMode === 'month' ? 'bg-white text-purple-deep shadow-xs' : 'hover:text-purple-deep'"
                        class="px-3 py-1.5 rounded-lg transition cursor-pointer"
                    >
                        Bulan
                    </button>
                    <button 
                        type="button" 
                        @click="viewMode = 'week'" 
                        :class="viewMode === 'week' ? 'bg-white text-purple-deep shadow-xs' : 'hover:text-purple-deep'"
                        class="px-3 py-1.5 rounded-lg transition cursor-pointer"
                    >
                        Minggu
                    </button>
                    <button 
                        type="button" 
                        @click="viewMode = 'agenda'" 
                        :class="viewMode === 'agenda' ? 'bg-white text-purple-deep shadow-xs' : 'hover:text-purple-deep'"
                        class="px-3 py-1.5 rounded-lg transition cursor-pointer"
                    >
                        Agenda
                    </button>
                </div>


            </div>
        </div>

        {{-- ===== TAMPILAN 1: BULAN (MONTH VIEW GRID) ===== --}}
        <div x-show="viewMode === 'month'" class="flex-1 flex flex-col">
            {{-- Header 7 Hari --}}
            <div class="grid grid-cols-7 border-b border-[#EDE1FA] bg-[#FAF8FD] text-center text-xs font-bold text-[#5B4A73]">
                <div class="py-2.5 border-r border-[#EDE1FA]">Senin</div>
                <div class="py-2.5 border-r border-[#EDE1FA]">Selasa</div>
                <div class="py-2.5 border-r border-[#EDE1FA]">Rabu</div>
                <div class="py-2.5 border-r border-[#EDE1FA]">Kamis</div>
                <div class="py-2.5 border-r border-[#EDE1FA]">Jumat</div>
                <div class="py-2.5 border-r border-[#EDE1FA] text-purple-deep">Sabtu</div>
                <div class="py-2.5 text-rose-600">Minggu</div>
            </div>

            {{-- Grid Tanggal --}}
            <div class="grid grid-cols-7 divide-x divide-y divide-[#EDE1FA] bg-[#F7F5FB] flex-1">
                <template x-for="(day, index) in monthDays" :key="index">
                    <div 
                        :class="day.isCurrentMonth ? 'bg-white' : 'bg-[#FAF8FD] text-[#A699BA]'"
                        class="min-h-[115px] sm:min-h-[135px] p-1.5 sm:p-2 flex flex-col justify-between transition hover:bg-[#FDFBFF] group"
                    >
                        {{-- Tanggal Header --}}
                        <div class="flex items-center justify-between mb-1">
                            <span 
                                :class="{
                                    'bg-purple-deep text-white shadow-xs font-extrabold': day.isToday,
                                    'text-[#2A2035] font-bold': day.isCurrentMonth && !day.isToday,
                                    'text-[#A699BA]': !day.isCurrentMonth
                                }"
                                class="w-6 h-6 rounded-full flex items-center justify-center text-xs"
                                x-text="day.dayNumber"
                            ></span>

                            {{-- Tombol Quick Add saat hover --}}
                            <button 
                                type="button" 
                                @click="$dispatch('open-booking-modal', { date: day.dateStr })"
                                class="opacity-0 group-hover:opacity-100 p-0.5 rounded text-[#827299] hover:text-purple-deep transition text-[10px] cursor-pointer"
                                title="Tambah jadwal pada hari ini"
                            >
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            </button>
                        </div>

                        {{-- Event Chips List --}}
                        <div class="space-y-1 flex-1 overflow-hidden">
                            <template x-for="(event, eIndex) in day.events.slice(0, 3)" :key="event.id">
                                <div 
                                    @click="openEventModal(event)"
                                    :class="event.bg_chip"
                                    class="px-2 py-1 rounded-lg text-[10px] font-bold truncate cursor-pointer transition shadow-2xs flex items-center gap-1.5"
                                    :title="event.title"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full bg-white flex-shrink-0"></span>
                                    <span class="truncate" x-text="event.short_title"></span>
                                </div>
                            </template>

                            {{-- Lebih dari 3 Event --}}
                            <template x-if="day.events.length > 3">
                                <button 
                                    type="button" 
                                    @click="openDayModal(day)"
                                    class="w-full text-left text-[10px] font-extrabold text-purple-deep hover:underline px-1 py-0.5 cursor-pointer"
                                    x-text="'+ ' + (day.events.length - 3) + ' lainnya'"
                                ></button>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- ===== TAMPILAN 2: MINGGU (WEEK VIEW) ===== --}}
        <div x-show="viewMode === 'week'" class="flex-1 flex flex-col overflow-x-auto">
            <div class="grid grid-cols-7 min-w-[700px] border-b border-[#EDE1FA] bg-[#FAF8FD] text-center text-xs font-bold text-[#5B4A73]">
                <template x-for="(wDay, index) in weekDays" :key="index">
                    <div class="py-3 border-r border-[#EDE1FA] flex flex-col items-center gap-1">
                        <span class="text-[11px] text-[#827299]" x-text="['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'][index]"></span>
                        <span 
                            :class="wDay.isToday ? 'bg-purple-deep text-white shadow-xs' : 'text-[#2A2035]'"
                            class="w-7 h-7 rounded-full flex items-center justify-center font-extrabold text-sm"
                            x-text="wDay.dayNumber"
                        ></span>
                    </div>
                </template>
            </div>

            <div class="grid grid-cols-7 min-w-[700px] divide-x divide-[#EDE1FA] bg-white flex-1 min-h-[450px]">
                <template x-for="(wDay, index) in weekDays" :key="index">
                    <div class="p-2 space-y-2 bg-white hover:bg-[#FAF8FD] transition">
                        <template x-for="event in wDay.events" :key="event.id">
                            <div 
                                @click="openEventModal(event)"
                                class="p-2.5 rounded-xl border border-[#EDE1FA] hover:border-purple-300 bg-white shadow-2xs hover:shadow-xs transition cursor-pointer space-y-1.5 group"
                            >
                                <div class="flex items-center justify-between gap-1">
                                    <span 
                                        :class="event.bg_chip"
                                        class="px-1.5 py-0.2 rounded text-[9px] font-bold"
                                        x-text="event.category_label"
                                    ></span>
                                    <span class="text-[9px] text-[#827299]" x-text="event.status_label"></span>
                                </div>
                                <h4 class="text-xs font-bold text-[#2A2035] group-hover:text-purple-deep line-clamp-2" x-text="event.title"></h4>
                                <div class="text-[10px] text-[#827299]">
                                    <template x-if="event.category === 'counseling'">
                                        <p>Konselor: <strong class="text-[#2A2035]" x-text="event.counselor_name"></strong></p>
                                    </template>
                                    <template x-if="event.category !== 'counseling'">
                                        <p>Staff: <strong class="text-[#2A2035]" x-text="event.staff_name"></strong> (<span x-text="event.role_label"></span>)</p>
                                    </template>
                                    <p>Klien: <span class="font-medium text-[#2A2035]" x-text="event.client_name"></span></p>
                                </div>
                            </div>
                        </template>

                        <template x-if="wDay.events.length === 0">
                            <div class="h-32 flex flex-col items-center justify-center text-center p-2 text-[11px] text-[#A699BA]">
                                <span>Tidak ada jadwal</span>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        {{-- ===== TAMPILAN 3: AGENDA (LIST VIEW) ===== --}}
        <div x-show="viewMode === 'agenda'" class="p-5 flex-1 divide-y divide-[#F3EAFB]">
            <template x-for="(eventsOnDate, dateKey) in agendaGrouped" :key="dateKey">
                <div class="py-4 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-start gap-4">
                    {{-- Badge Tanggal Kolom Kiri --}}
                    <div class="sm:w-48 flex-shrink-0">
                        <span class="text-xs font-extrabold text-purple-deep block" x-text="eventsOnDate[0]?.formatted_date"></span>
                        <span class="text-[10px] text-[#827299]" x-text="eventsOnDate.length + ' Kegiatan Terjadwal'"></span>
                    </div>

                    {{-- List Event Kolom Kanan --}}
                    <div class="flex-1 space-y-2.5">
                        <template x-for="event in eventsOnDate" :key="event.id">
                            <div 
                                @click="openEventModal(event)"
                                class="p-3.5 rounded-2xl bg-white border border-[#EDE1FA] hover:border-purple-deep hover:shadow-xs transition cursor-pointer flex flex-col sm:flex-row sm:items-center justify-between gap-3 group"
                            >
                                <div class="flex items-start gap-3">
                                    <div 
                                        :class="event.dot_color" 
                                        class="w-3 h-3 rounded-full mt-1 flex-shrink-0"
                                    ></div>
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap mb-1">
                                            <span 
                                                :class="event.bg_chip" 
                                                class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                                x-text="event.category_label"
                                            ></span>
                                            <span class="text-[10px] font-bold text-[#827299]" x-text="event.status_label"></span>
                                        </div>
                                        <h4 class="text-sm font-bold text-[#2A2035] group-hover:text-purple-deep" x-text="event.title"></h4>
                                        <p class="text-xs text-[#6B5B85] mt-0.5">
                                            <template x-if="event.category === 'counseling'">
                                                <span>Praktik: <strong class="text-[#2A2035]" x-text="event.counselor_name"></strong> · Klien: <strong class="text-[#2A2035]" x-text="event.client_name"></strong></span>
                                            </template>
                                            <template x-if="event.category !== 'counseling'">
                                                <span>Penugasan: <strong class="text-[#2A2035]" x-text="event.staff_name"></strong> (<span x-text="event.role_label"></span>) · Klien: <strong class="text-[#2A2035]" x-text="event.client_name"></strong></span>
                                            </template>
                                        </p>
                                    </div>
                                </div>

                                <div class="self-end sm:self-center flex-shrink-0">
                                    <span class="text-xs font-bold text-purple-deep group-hover:underline">Buka Detail →</span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <template x-if="Object.keys(agendaGrouped).length === 0">
                <div class="py-16 text-center text-xs text-[#827299]">
                    <div class="w-12 h-12 rounded-2xl bg-[#F7F5FB] text-purple-deep flex items-center justify-center mx-auto mb-3 text-xl font-bold">
                        📅
                    </div>
                    <p class="font-bold text-sm text-[#2A2035] mb-1">Belum ada agenda jadwal yang tercatat</p>
                    <p class="mb-4">Tidak ada sesi konseling atau penugasan tes psikotes pada filter yang dipilih.</p>
                    <button 
                        type="button" 
                        @click="$dispatch('open-booking-modal')" 
                        class="px-4 py-2 bg-purple-deep text-white font-bold text-xs rounded-xl shadow-xs hover:opacity-90 transition cursor-pointer"
                    >
                        + Buat Booking / Jadwal Baru
                    </button>
                </div>
            </template>
        </div>

    </div>

    {{-- ========================================================================= --}}
    {{-- 3. MODAL DETAIL JADWAL (GOOGLE CALENDAR STYLE POPOVER/MODAL)               --}}
    {{-- ========================================================================= --}}
    <div 
        x-show="eventModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-2xs"
        @keydown.escape.window="eventModalOpen = false"
    >
        <div 
            @click.away="eventModalOpen = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="bg-white w-full max-w-lg rounded-3xl shadow-xl border border-[#EDE1FA] overflow-hidden"
        >
            {{-- Header Modal --}}
            <div 
                :class="selectedEvent?.bg_chip || 'bg-purple-deep text-white'"
                class="p-5 flex items-center justify-between transition"
            >
                <div class="flex items-center gap-2">
                    <span class="text-xs uppercase tracking-wider font-extrabold opacity-90" x-text="selectedEvent?.category_label"></span>
                    <span>•</span>
                    <span class="text-xs font-semibold opacity-90" x-text="selectedEvent?.status_label"></span>
                </div>
                <button 
                    type="button" 
                    @click="eventModalOpen = false"
                    class="p-1 rounded-lg hover:bg-white/20 text-white transition cursor-pointer"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            {{-- Isi Modal --}}
            <div class="p-6 space-y-4 text-xs">
                {{-- Judul Acara --}}
                <div>
                    <h3 class="text-base font-extrabold text-[#2A2035]" x-text="selectedEvent?.title"></h3>
                    <p class="text-[#827299] mt-0.5" x-text="selectedEvent?.formatted_date"></p>
                </div>

                {{-- Detail Penanggung Jawab --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3.5 rounded-2xl bg-[#F7F5FB] border border-[#EDE1FA]">
                    <div>
                        <span class="text-[10px] font-bold text-[#827299] block">
                            <span x-text="selectedEvent?.category === 'counseling' ? 'Konselor yang Praktik:' : 'Staff yang Bertugas:'"></span>
                        </span>
                        <strong class="text-sm font-extrabold text-purple-deep" x-text="selectedEvent?.category === 'counseling' ? selectedEvent?.counselor_name : selectedEvent?.staff_name"></strong>
                        <span class="text-[10px] text-[#827299] block" x-text="selectedEvent?.role_label"></span>
                    </div>

                    <div>
                        <span class="text-[10px] font-bold text-[#827299] block">Klien yang Dilayani:</span>
                        <strong class="text-sm font-extrabold text-[#2A2035]" x-text="selectedEvent?.client_name"></strong>
                        <span class="text-[10px] text-[#827299] block">Klien Terdaftar</span>
                    </div>
                </div>

                {{-- Catatan Jika Ada --}}
                <template x-if="selectedEvent?.notes">
                    <div class="p-3 rounded-xl bg-amber-50/60 border border-amber-200 text-amber-900">
                        <span class="font-bold block text-[10px] text-amber-800 mb-0.5">Catatan:</span>
                        <p x-text="selectedEvent?.notes" class="leading-relaxed"></p>
                    </div>
                </template>

                {{-- Tombol Aksi --}}
                <div class="pt-3 border-t border-[#EDE1FA] flex items-center justify-end gap-2">
                    <button 
                        type="button" 
                        @click="eventModalOpen = false" 
                        class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-700 font-bold hover:bg-gray-50 transition cursor-pointer"
                    >
                        Tutup
                    </button>
                    <a 
                        :href="selectedEvent?.url" 
                        class="px-5 py-2.5 rounded-xl bg-purple-deep text-white font-bold hover:opacity-90 transition shadow-xs flex items-center gap-1.5"
                    >
                        <span>Buka Detail Lengkap</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 4. MODAL POPUP HARI (+X LAINNYA)                                           --}}
    {{-- ========================================================================= --}}
    <div 
        x-show="selectedDateModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-2xs"
        @keydown.escape.window="selectedDateModalOpen = false"
    >
        <div 
            @click.away="selectedDateModalOpen = false"
            class="bg-white w-full max-w-md rounded-3xl shadow-xl border border-[#EDE1FA] overflow-hidden"
        >
            <div class="p-4 border-b border-[#EDE1FA] flex items-center justify-between bg-[#F7F5FB]">
                <div>
                    <h3 class="font-bold text-sm text-[#2A2035]">Jadwal Kegiatan</h3>
                    <p class="text-[10px] text-[#827299]" x-text="selectedDateEvents?.dateStr"></p>
                </div>
                <button 
                    type="button" 
                    @click="selectedDateModalOpen = false"
                    class="p-1 rounded-lg hover:bg-gray-200 text-gray-600 transition cursor-pointer"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <div class="p-4 max-h-80 overflow-y-auto space-y-2">
                <template x-for="event in selectedDateEvents?.events || []" :key="event.id">
                    <div 
                        @click="selectedDateModalOpen = false; openEventModal(event)"
                        :class="event.bg_chip"
                        class="p-2.5 rounded-xl text-xs font-bold cursor-pointer transition shadow-2xs flex items-center justify-between"
                    >
                        <div class="truncate mr-2">
                            <span class="text-[9px] uppercase tracking-wider block opacity-80" x-text="event.category_label"></span>
                            <span class="truncate block" x-text="event.title"></span>
                        </div>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- 5. ALPINE.JS GOOGLE CALENDAR CONTROLLER SCRIPT                            --}}
{{-- ========================================================================= --}}
<script>
function googleCalendar(initialEvents, currentUser) {
    return {
        currentDate: new Date(),
        viewMode: 'month', // 'month', 'week', 'agenda'
        filterCategory: 'all', // 'all', 'counseling', 'testing', 'correction', 'reporting'
        filterCounselorId: '',
        filterStaffId: '',
        searchQuery: '',
        events: initialEvents || [],
        selectedEvent: null,
        selectedDateEvents: null,
        eventModalOpen: false,
        selectedDateModalOpen: false,

        get currentYear() {
            return this.currentDate.getFullYear();
        },
        get currentMonth() {
            return this.currentDate.getMonth();
        },
        get currentMonthName() {
            const months = [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            return months[this.currentMonth];
        },
        get headerPeriodTitle() {
            if (this.viewMode === 'month') {
                return `${this.currentMonthName} ${this.currentYear}`;
            } else if (this.viewMode === 'week') {
                const start = this.getStartOfWeek(this.currentDate);
                const end = new Date(start);
                end.setDate(start.getDate() + 6);
                const months = [
                    'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                    'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
                ];
                if (start.getMonth() === end.getMonth()) {
                    return `${start.getDate()} – ${end.getDate()} ${months[start.getMonth()]} ${start.getFullYear()}`;
                } else {
                    return `${start.getDate()} ${months[start.getMonth()]} – ${end.getDate()} ${months[end.getMonth()]} ${end.getFullYear()}`;
                }
            } else {
                return `Agenda ${this.currentMonthName} ${this.currentYear}`;
            }
        },

        goToday() {
            this.currentDate = new Date();
        },
        prev() {
            if (this.viewMode === 'month') {
                this.currentDate = new Date(this.currentYear, this.currentMonth - 1, 1);
            } else if (this.viewMode === 'week') {
                const d = new Date(this.currentDate);
                d.setDate(d.getDate() - 7);
                this.currentDate = d;
            } else {
                this.currentDate = new Date(this.currentYear, this.currentMonth - 1, 1);
            }
        },
        next() {
            if (this.viewMode === 'month') {
                this.currentDate = new Date(this.currentYear, this.currentMonth + 1, 1);
            } else if (this.viewMode === 'week') {
                const d = new Date(this.currentDate);
                d.setDate(d.getDate() + 7);
                this.currentDate = d;
            } else {
                this.currentDate = new Date(this.currentYear, this.currentMonth + 1, 1);
            }
        },

        get filteredEvents() {
            return this.events.filter(e => {
                if (this.filterCategory !== 'all' && e.category !== this.filterCategory) {
                    return false;
                }
                if (this.filterCounselorId && String(e.counselor_id) !== String(this.filterCounselorId)) {
                    return false;
                }
                if (this.filterStaffId && String(e.staff_id) !== String(this.filterStaffId)) {
                    return false;
                }
                if (this.searchQuery) {
                    const q = this.searchQuery.toLowerCase();
                    const title = (e.title || '').toLowerCase();
                    const client = (e.client_name || '').toLowerCase();
                    const person = ((e.counselor_name || '') + ' ' + (e.staff_name || '')).toLowerCase();
                    if (!title.includes(q) && !client.includes(q) && !person.includes(q)) {
                        return false;
                    }
                }
                return true;
            });
        },

        get monthDays() {
            const year = this.currentYear;
            const month = this.currentMonth;
            const firstDayOfMonth = new Date(year, month, 1);
            const lastDayOfMonth = new Date(year, month + 1, 0);

            // Senin = 0, Minggu = 6
            let startDay = firstDayOfMonth.getDay();
            let dayOffset = (startDay === 0 ? 6 : startDay - 1);

            const days = [];
            const prevMonthLastDay = new Date(year, month, 0).getDate();

            for (let i = dayOffset - 1; i >= 0; i--) {
                const d = new Date(year, month - 1, prevMonthLastDay - i);
                days.push(this.formatDayObj(d, false));
            }

            for (let i = 1; i <= lastDayOfMonth.getDate(); i++) {
                const d = new Date(year, month, i);
                days.push(this.formatDayObj(d, true));
            }

            const remaining = (7 - (days.length % 7)) % 7;
            for (let i = 1; i <= remaining; i++) {
                const d = new Date(year, month + 1, i);
                days.push(this.formatDayObj(d, false));
            }

            return days;
        },

        get weekDays() {
            const start = this.getStartOfWeek(this.currentDate);
            const days = [];
            for (let i = 0; i < 7; i++) {
                const d = new Date(start);
                d.setDate(start.getDate() + i);
                days.push(this.formatDayObj(d, true));
            }
            return days;
        },

        get agendaGrouped() {
            const grouped = {};
            const sorted = [...this.filteredEvents].sort((a, b) => a.date.localeCompare(b.date));
            sorted.forEach(e => {
                if (!grouped[e.date]) {
                    grouped[e.date] = [];
                }
                grouped[e.date].push(e);
            });
            return grouped;
        },

        formatDayObj(date, isCurrentMonth) {
            const dateStr = this.toDateString(date);
            const todayStr = this.toDateString(new Date());
            const dayEvents = this.filteredEvents.filter(e => e.date === dateStr);

            return {
                date: date,
                dateStr: dateStr,
                dayNumber: date.getDate(),
                isCurrentMonth: isCurrentMonth,
                isToday: dateStr === todayStr,
                events: dayEvents,
            };
        },

        toDateString(d) {
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },

        getStartOfWeek(d) {
            const date = new Date(d);
            const day = date.getDay();
            const diff = date.getDate() - (day === 0 ? 6 : day - 1);
            return new Date(date.setDate(diff));
        },

        openEventModal(event) {
            this.selectedEvent = event;
            this.eventModalOpen = true;
        },

        openDayModal(dayObj) {
            this.selectedDateEvents = dayObj;
            this.selectedDateModalOpen = true;
        }
    };
}
</script>
@endsection
