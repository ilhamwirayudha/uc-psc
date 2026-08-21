@extends('layouts.dashboard')

@section('title', 'Edit Konselor — UC PSC')
@section('page-title', 'Edit Konselor')
@section('page-subtitle', 'Perbarui data konselor')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6">
        <form action="{{ route('counselors.update', $counselor) }}" method="POST" class="space-y-5">
            @csrf @method('PUT')

            <div>
                <label for="name" class="block text-sm font-semibold text-[#5B4A73] mb-2">Nama Konselor <span class="text-red-400">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name', $counselor->name) }}" required
                    class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm">
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label for="phone" class="block text-sm font-semibold text-[#5B4A73] mb-2">No. Telepon / WA</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $counselor->phone) }}"
                        class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm">
                </div>
                <div>
                    <label for="email" class="block text-sm font-semibold text-[#5B4A73] mb-2">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $counselor->email) }}"
                        class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm">
                </div>
            </div>

            <div>
                <label for="specialization" class="block text-sm font-semibold text-[#5B4A73] mb-2">Spesialisasi</label>
                <input type="text" id="specialization" name="specialization" value="{{ old('specialization', $counselor->specialization) }}"
                    class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm">
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label for="sipp_number" class="block text-sm font-semibold text-[#5B4A73] mb-2">Nomor SIPP</label>
                    <input type="text" id="sipp_number" name="sipp_number" value="{{ old('sipp_number', $counselor->sipp_number) }}"
                        class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm">
                </div>
                <div>
                    <label for="str_number" class="block text-sm font-semibold text-[#5B4A73] mb-2">Nomor STR</label>
                    <input type="text" id="str_number" name="str_number" value="{{ old('str_number', $counselor->str_number) }}"
                        class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm">
                </div>
            </div>

            <div>
                <label for="status" class="block text-sm font-semibold text-[#5B4A73] mb-2">Status</label>
                <select id="status" name="status"
                    class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm text-[#5B4A73]">
                    <option value="active" class="text-emerald-600 font-semibold bg-white" style="color: #059669;" {{ old('status', $counselor->status) === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" class="text-red-500 font-semibold bg-white" style="color: #ef4444;" {{ old('status', $counselor->status) === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <div>
                <label for="notes" class="block text-sm font-semibold text-[#5B4A73] mb-2">Catatan</label>
                <textarea id="notes" name="notes" rows="3"
                    class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm resize-none">{{ old('notes', $counselor->notes) }}</textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="bg-purple-deep text-white px-6 py-3 rounded-xl text-sm font-semibold hover:opacity-90 transition">Perbarui</button>
                <a href="{{ route('counselors.index') }}" class="px-6 py-3 rounded-xl text-sm font-semibold text-[#6B5B85] border border-[#D9C2F0] hover:bg-[#F7F5FB] transition">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
