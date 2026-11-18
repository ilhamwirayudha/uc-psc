@extends('layouts.dashboard')

@section('title', 'Tambah Akun Tim — UC PSC')
@section('page-title', 'Tambah Akun Tim')
@section('page-subtitle', 'Buat akun login baru untuk Staff atau Psikolog')

@section('content')
<div class="max-w-2xl" x-data="{ role: '{{ old('role', 'staff') }}' }">
    <div class="glass-card rounded-2xl p-6">
        <form action="{{ route('staff-management.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="role" class="block text-xs font-medium text-slate-500 mb-1">Peran Akun *</label>
                <select id="role" name="role" x-model="role" required
                    class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black min-h-[42px]">
                    <option value="staff">Staff (Tester / Skorer Manual / Skorer Software / Editor)</option>
                    <option value="psikolog">Psikolog (Konseling &amp; Pengampu Psikotes / Draft HPP)</option>
                </select>
                @error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div x-show="role === 'psikolog'" x-cloak>
                <label for="counselor_id" class="block text-xs font-medium text-slate-500 mb-1">Tautkan ke Data Psikolog yang Sudah Ada (Opsional)</label>
                <select id="counselor_id" name="counselor_id"
                    class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black min-h-[42px]">
                    <option value="">-- Otomatis buat profil Psikolog baru --</option>
                    @foreach($availableCounselors as $counselor)
                        <option value="{{ $counselor->id }}" {{ (string) old('counselor_id') === (string) $counselor->id ? 'selected' : '' }}>
                            {{ $counselor->name }} {{ $counselor->specialization ? '(' . $counselor->specialization . ')' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('counselor_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="name" class="block text-xs font-medium text-slate-500 mb-1">Nama Lengkap *</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                    class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black min-h-[42px]">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="block text-xs font-medium text-slate-500 mb-1">Email *</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                    class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black min-h-[42px]">
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-xs font-medium text-slate-500 mb-1">Password *</label>
                    <input type="password" id="password" name="password" required
                        class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black min-h-[42px]">
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-xs font-medium text-slate-500 mb-1">Konfirmasi Password *</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                        class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black min-h-[42px]">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <a href="{{ route('staff-management.index') }}" class="px-5 py-2.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-sm shadow-xs transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-sm shadow-xs transition cursor-pointer inline-flex items-center gap-2">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    <span>Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
