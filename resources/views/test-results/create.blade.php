@extends('layouts.dashboard')

@section('title', 'Upload Hasil Tes — UC PSC')
@section('page-title', 'Upload Hasil Tes')
@section('page-subtitle', 'Upload file hasil tes psikologi klien')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6">
        <form action="{{ route('test-results.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div>
                <label for="client_id" class="block text-sm font-semibold text-[#5B4A73] mb-2">Klien <span class="text-red-400">*</span></label>
                <select id="client_id" name="client_id" required
                    class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm text-[#5B4A73]">
                    <option value="">Pilih klien...</option>
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                    @endforeach
                </select>
                @error('client_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="test_name" class="block text-sm font-semibold text-[#5B4A73] mb-2">Nama Tes <span class="text-red-400">*</span></label>
                <input type="text" id="test_name" name="test_name" value="{{ old('test_name') }}" required placeholder="contoh: MMPI-2, WISC-IV, BDI-II"
                    class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm @error('test_name') border-red-300 @enderror">
                @error('test_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="tested_at" class="block text-sm font-semibold text-[#5B4A73] mb-2">Tanggal Tes</label>
                <input type="date" id="tested_at" name="tested_at" value="{{ old('tested_at') }}"
                    class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm">
            </div>

            <div>
                <label for="file" class="block text-sm font-semibold text-[#5B4A73] mb-2">File Hasil Tes</label>
                <div class="relative">
                    <input type="file" id="file" name="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls"
                        class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-purple-deep/10 file:text-purple-deep hover:file:bg-purple-deep/20">
                </div>
                <p class="text-xs text-[#6B5B85] mt-1">Maks. 10MB. Format: PDF, DOC, DOCX, JPG, PNG, XLSX</p>
                @error('file') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="result_summary" class="block text-sm font-semibold text-[#5B4A73] mb-2">Ringkasan Hasil</label>
                <textarea id="result_summary" name="result_summary" rows="3" placeholder="Ringkasan singkat hasil tes..."
                    class="w-full px-4 py-3 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep focus:border-transparent transition text-sm resize-none">{{ old('result_summary') }}</textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="bg-purple-deep text-white px-6 py-3 rounded-xl text-sm font-semibold hover:opacity-90 transition">Upload Hasil Tes</button>
                <a href="{{ route('test-results.index') }}" class="px-6 py-3 rounded-xl text-sm font-semibold text-[#6B5B85] border border-[#D9C2F0] hover:bg-[#F7F5FB] transition">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
