@extends('layouts.dashboard')

@section('title', 'Tambah Staff — UC PSC')
@section('page-title', 'Tambah Staff Baru')
@section('page-subtitle', 'Buat akun staff baru untuk sistem UC PSC')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6">
        <form action="{{ route('staff-management.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="name" class="block text-sm font-semibold text-[#5B4A73] mb-2">Nama <span class="text-red-400">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                    class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm @error('name') border-red-300 @enderror">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold text-[#5B4A73] mb-2">Email <span class="text-red-400">*</span></label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                    class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm @error('email') border-red-300 @enderror">
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-[#5B4A73] mb-2">Password <span class="text-red-400">*</span></label>
                <input type="password" id="password" name="password" required
                    class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm @error('password') border-red-300 @enderror">
                @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-[#5B4A73] mb-2">Konfirmasi Password <span class="text-red-400">*</span></label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                    class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm">
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="bg-purple-deep text-white px-6 py-3 rounded-xl text-sm font-semibold hover:opacity-90 transition">Buat Staff</button>
                <a href="{{ route('staff-management.index') }}" class="px-6 py-3 rounded-xl text-sm font-semibold text-[#6B5B85] border border-[#D9C2F0] hover:bg-[#F7F5FB] transition">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
