{{-- FORMULIR RIWAYAT HIDUP - NON-INDUSTRI --}}
<div class="space-y-6">

    {{-- KARTU: LEMBAR PERNYATAAN KEJUJURAN --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-4">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center gap-2">
            <h3 class="text-base font-semibold text-black">Pernyataan Kejujuran <span class="text-red-500">*</span></h3>
        </div>

        <div>
            <p class="text-sm text-[#6B5B85] leading-relaxed mb-3">
                Semua keterangan yang saya berikan dalam Formulir Riwayat Hidup ini, saya buat dengan jujur dan sungguh-sungguh.
            </p>

            <label class="inline-flex items-center gap-2.5 cursor-pointer text-sm font-medium text-black">
                <input type="checkbox" name="consent_agree" value="Setuju" required checked class="w-4 h-4 rounded text-purple-deep focus:ring-purple-deep border-[#D9C2F0]">
                <span>Setuju <span class="text-red-500">*</span></span>
            </label>
            @error('consent_agree') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- KARTU 1: DATA DIRI --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-4">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center gap-2">
            <h3 class="text-base font-semibold text-black">Data Diri</h3>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required placeholder="Nama lengkap peserta..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Jenis Kelamin <span class="text-red-500">*</span></label>
                <select name="jenis_kelamin" required class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                    <option value="">-- Pilih --</option>
                    <option value="Laki-Laki" {{ old('jenis_kelamin') === 'Laki-Laki' ? 'selected' : '' }}>Laki-Laki</option>
                    <option value="Perempuan" {{ old('jenis_kelamin') === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Tempat, Tanggal Lahir <span class="text-red-500">*</span></label>
                <input type="text" name="tempat_tanggal_lahir" value="{{ old('tempat_tanggal_lahir') }}" required placeholder="Contoh: Surabaya, 12 Agustus 2005" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Urutan Kelahiran (Opsional)</label>
                <input type="text" name="urutan_kelahiran" value="{{ old('urutan_kelahiran') }}" placeholder="Contoh: Anak ke 1 dari 2 bersaudara" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Asal Kota (Opsional)</label>
                <input type="text" name="asal_kota" value="{{ old('asal_kota') }}" placeholder="Contoh: Surabaya" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Alamat Sekarang <span class="text-red-500">*</span></label>
                <textarea name="alamat_sekarang" rows="2" required placeholder="Alamat domisili saat ini..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('alamat_sekarang') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">No. Telp/HP <span class="text-red-500">*</span></label>
                <input type="tel" name="phone" value="{{ old('phone') }}" required placeholder="08xxxxxxxx" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Alamat Email Aktif <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="email@contoh.com" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Agama/Kepercayaan <span class="text-red-500">*</span></label>
                <select name="agama" required class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                    <option value="">-- Pilih Agama --</option>
                    @foreach(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Lainnya'] as $agm)
                        <option value="{{ $agm }}" {{ old('agama') === $agm ? 'selected' : '' }}>{{ $agm }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Suku Bangsa (Opsional)</label>
                <input type="text" name="suku_bangsa" value="{{ old('suku_bangsa') }}" placeholder="Contoh: Jawa, Tionghoa, dll" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Pendidikan Terakhir yang Berijazah <span class="text-red-500">*</span></label>
                <input type="text" name="pendidikan_terakhir" value="{{ old('pendidikan_terakhir') }}" required placeholder="Contoh: SMA / S1" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
        </div>
    </div>

    {{-- KARTU 2: DATA KELUARGA --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-6">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div>
                    <h3 class="text-base font-semibold text-black">Data Keluarga</h3>
                    <p class="text-xs text-slate-500">Informasi orang tua dan saudara kandung (opsional).</p>
                </div>
            </div>
        </div>

        {{-- Data Ayah --}}
        <div class="p-4 bg-[#FAF9FD] rounded-xl border border-[#EDE1FA]/80 space-y-3">
            <h4 class="text-sm font-semibold text-black border-b border-[#EDE1FA] pb-1.5">Data Ayah</h4>
            <div class="grid sm:grid-cols-2 md:grid-cols-5 gap-3">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Nama Ayah</label>
                    <input type="text" name="ayah_nama" value="{{ old('ayah_nama') }}" placeholder="Nama ayah" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Jenis Kelamin</label>
                    <select name="ayah_jenis_kelamin" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                        <option value="Laki-Laki" {{ old('ayah_jenis_kelamin') === 'Laki-Laki' ? 'selected' : '' }}>Laki-Laki</option>
                        <option value="Perempuan" {{ old('ayah_jenis_kelamin') === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Usia Ayah</label>
                    <input type="text" name="ayah_usia" value="{{ old('ayah_usia') }}" placeholder="Contoh: 55 Tahun" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Pendidikan</label>
                    <input type="text" name="ayah_pendidikan" value="{{ old('ayah_pendidikan') }}" placeholder="Contoh: S1" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
                <div class="md:col-span-5">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Pekerjaan Ayah</label>
                    <input type="text" name="ayah_pekerjaan" value="{{ old('ayah_pekerjaan') }}" placeholder="Pekerjaan ayah" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
            </div>
        </div>

        {{-- Data Ibu --}}
        <div class="p-4 bg-[#FAF9FD] rounded-xl border border-[#EDE1FA]/80 space-y-3">
            <h4 class="text-sm font-semibold text-black border-b border-[#EDE1FA] pb-1.5">Data Ibu</h4>
            <div class="grid sm:grid-cols-2 md:grid-cols-5 gap-3">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Nama Ibu</label>
                    <input type="text" name="ibu_nama" value="{{ old('ibu_nama') }}" placeholder="Nama ibu" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Jenis Kelamin</label>
                    <select name="ibu_jenis_kelamin" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                        <option value="Perempuan" {{ old('ibu_jenis_kelamin') === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        <option value="Laki-Laki" {{ old('ibu_jenis_kelamin') === 'Laki-Laki' ? 'selected' : '' }}>Laki-Laki</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Usia Ibu</label>
                    <input type="text" name="ibu_usia" value="{{ old('ibu_usia') }}" placeholder="Contoh: 50 Tahun" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Pendidikan</label>
                    <input type="text" name="ibu_pendidikan" value="{{ old('ibu_pendidikan') }}" placeholder="Contoh: SMA" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
                <div class="md:col-span-5">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Pekerjaan Ibu</label>
                    <input type="text" name="ibu_pekerjaan" value="{{ old('ibu_pekerjaan') }}" placeholder="Pekerjaan ibu" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
            </div>
        </div>

        {{-- Data Saudara (1 s.d. 5) --}}
        <div class="space-y-3">
            <h4 class="text-sm font-semibold text-black">Data Saudara Kandung (1 s.d. 5)</h4>
            @for($s = 1; $s <= 5; $s++)
            <div class="p-3.5 bg-[#FAF9FD] rounded-xl border border-[#EDE1FA]/80 space-y-2">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-black">Saudara {{ $s }} <span class="text-slate-400 font-normal">(Opsional)</span></span>
                </div>
                <div class="grid sm:grid-cols-2 md:grid-cols-5 gap-2.5">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Nama Saudara {{ $s }}</label>
                        <input type="text" name="saudara_{{ $s }}_nama" value="{{ old('saudara_' . $s . '_nama') }}" placeholder="Nama saudara" class="w-full px-2.5 py-1.5 bg-white border border-[#D9C2F0] rounded-lg text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Jenis Kelamin</label>
                        <select name="saudara_{{ $s }}_jenis_kelamin" class="w-full px-2 py-1.5 bg-white border border-[#D9C2F0] rounded-lg text-xs">
                            <option value="">--</option>
                            <option value="Laki-Laki" {{ old('saudara_' . $s . '_jenis_kelamin') === 'Laki-Laki' ? 'selected' : '' }}>Laki-Laki</option>
                            <option value="Perempuan" {{ old('saudara_' . $s . '_jenis_kelamin') === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Usia</label>
                        <input type="text" name="saudara_{{ $s }}_usia" value="{{ old('saudara_' . $s . '_usia') }}" placeholder="Usia" class="w-full px-2.5 py-1.5 bg-white border border-[#D9C2F0] rounded-lg text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Pendidikan</label>
                        <input type="text" name="saudara_{{ $s }}_pendidikan" value="{{ old('saudara_' . $s . '_pendidikan') }}" placeholder="Pendidikan" class="w-full px-2.5 py-1.5 bg-white border border-[#D9C2F0] rounded-lg text-xs">
                    </div>
                    <div class="md:col-span-5">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Pekerjaan</label>
                        <input type="text" name="saudara_{{ $s }}_pekerjaan" value="{{ old('saudara_' . $s . '_pekerjaan') }}" placeholder="Pekerjaan" class="w-full px-2.5 py-1.5 bg-white border border-[#D9C2F0] rounded-lg text-xs">
                    </div>
                </div>
            </div>
            @endfor
        </div>
    </div>

    {{-- KARTU 3: RIWAYAT PENDIDIKAN FORMAL --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-5">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div>
                    <h3 class="text-base font-semibold text-black">Riwayat Pendidikan Formal</h3>
                    <p class="text-xs text-slate-500">Tuliskan dari yang paling akhir (opsional).</p>
                </div>
            </div>
        </div>

        {{-- Sekolah 1 --}}
        <div class="p-4 bg-purple-50/20 rounded-xl border border-purple-200/80 space-y-3">
            <h4 class="text-sm font-semibold text-black border-b border-[#EDE1FA] pb-1.5">Sekolah 1 (Pendidikan Terakhir)</h4>
            <div class="grid sm:grid-cols-2 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Nama Sekolah 1</label>
                    <input type="text" name="nama_sekolah_1" value="{{ old('nama_sekolah_1') }}" placeholder="Nama sekolah / universitas" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Kota Sekolah 1</label>
                    <input type="text" name="kota_sekolah_1" value="{{ old('kota_sekolah_1') }}" placeholder="Kota" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Keterangan Sekolah 1 (Jurusan jika ada)</label>
                    <input type="text" name="keterangan_sekolah_1" value="{{ old('keterangan_sekolah_1') }}" placeholder="Contoh: IPA / Manajemen" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tahun Masuk Sekolah 1</label>
                    <input type="text" name="tahun_masuk_sekolah_1" value="{{ old('tahun_masuk_sekolah_1') }}" placeholder="Tahun masuk" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tahun Keluar Sekolah 1</label>
                    <input type="text" name="tahun_keluar_sekolah_1" value="{{ old('tahun_keluar_sekolah_1') }}" placeholder="Tahun keluar / lulus" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                </div>
            </div>
        </div>

        {{-- Sekolah 2 s.d. 5 --}}
        @for($sk = 2; $sk <= 5; $sk++)
        <div class="p-4 bg-[#FAF9FD] rounded-xl border border-[#EDE1FA]/80 space-y-3">
            <h4 class="text-sm font-semibold text-black border-b border-[#EDE1FA] pb-1.5">Sekolah {{ $sk }} <span class="text-slate-400 font-normal">(Opsional)</span></h4>
            <div class="grid sm:grid-cols-2 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Nama Sekolah {{ $sk }}</label>
                    <input type="text" name="nama_sekolah_{{ $sk }}" value="{{ old('nama_sekolah_' . $sk) }}" placeholder="Nama sekolah" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Kota Sekolah {{ $sk }}</label>
                    <input type="text" name="kota_sekolah_{{ $sk }}" value="{{ old('kota_sekolah_' . $sk) }}" placeholder="Kota" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Keterangan Sekolah {{ $sk }}</label>
                    <input type="text" name="keterangan_sekolah_{{ $sk }}" value="{{ old('keterangan_sekolah_' . $sk) }}" placeholder="Jurusan" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tahun Masuk Sekolah {{ $sk }}</label>
                    <input type="text" name="tahun_masuk_sekolah_{{ $sk }}" value="{{ old('tahun_masuk_sekolah_' . $sk) }}" placeholder="Tahun masuk" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tahun Keluar Sekolah {{ $sk }}</label>
                    <input type="text" name="tahun_keluar_sekolah_{{ $sk }}" value="{{ old('tahun_keluar_sekolah_' . $sk) }}" placeholder="Tahun keluar" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
            </div>
        </div>
        @endfor

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5 leading-snug">
                Apakah Anda pernah tinggal kelas? Jika pernah, kelas berapa dan apa sebabnya? (Opsional)
            </label>
            <textarea name="tidak_naik_kelas" rows="2" placeholder="Contoh: Tidak pernah / Pernah di kelas ... karena ..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('tidak_naik_kelas') }}</textarea>
        </div>
    </div>

    {{-- KARTU 4: PENDIDIKAN NON FORMAL --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-4">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center gap-2">
            <div>
                <h3 class="text-base font-semibold text-black">Pendidikan Non Formal</h3>
                <p class="text-xs text-slate-500">Tuliskan dari yang paling akhir (opsional).</p>
            </div>
        </div>

        <div class="space-y-4">
            @for($c = 1; $c <= 3; $c++)
            <div class="p-4 bg-[#FAF9FD] rounded-xl border border-[#EDE1FA]/80 space-y-3">
                <h4 class="text-sm font-semibold text-black">Kursus {{ $c }}</h4>
                <div class="grid sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Jenis Kursus {{ $c }}</label>
                        <input type="text" name="jenis_kursus_{{ $c }}" value="{{ old('jenis_kursus_' . $c) }}" placeholder="Contoh: Kursus Bahasa Inggris" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Tempat Kursus {{ $c }}</label>
                        <input type="text" name="tempat_kursus_{{ $c }}" value="{{ old('tempat_kursus_' . $c) }}" placeholder="Lembaga / Kota" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Lama Kursus {{ $c }}</label>
                        <input type="text" name="lama_kursus_{{ $c }}" value="{{ old('lama_kursus_' . $c) }}" placeholder="Contoh: 6 Bulan" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                </div>
            </div>
            @endfor
        </div>
    </div>

    {{-- KARTU 5: PENGALAMAN ORGANISASI --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-4">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center gap-2">
            <div>
                <h3 class="text-base font-semibold text-black">Pengalaman Organisasi</h3>
                <p class="text-xs text-slate-500">Tuliskan dari yang paling akhir (opsional).</p>
            </div>
        </div>

        <div class="space-y-4">
            @for($o = 1; $o <= 3; $o++)
            <div class="p-4 bg-[#FAF9FD] rounded-xl border border-[#EDE1FA]/80 space-y-3">
                <h4 class="text-sm font-semibold text-black">Organisasi {{ $o }}</h4>
                <div class="grid sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Nama Organisasi {{ $o }}</label>
                        <input type="text" name="organisasi_{{ $o }}_nama" value="{{ old('organisasi_' . $o . '_nama') }}" placeholder="Nama organisasi" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Posisi/Jabatan Organisasi {{ $o }}</label>
                        <input type="text" name="organisasi_{{ $o }}_jabatan" value="{{ old('organisasi_' . $o . '_jabatan') }}" placeholder="Jabatan" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Lama Berorganisasi di Organisasi {{ $o }}</label>
                        <input type="text" name="organisasi_{{ $o }}_lama" value="{{ old('organisasi_' . $o . '_lama') }}" placeholder="Contoh: 1 Tahun" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                </div>
            </div>
            @endfor
        </div>
    </div>

    {{-- KARTU 6: PRESTASI --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-4">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center gap-2">
            <h3 class="text-base font-semibold text-black">Prestasi</h3>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5 leading-snug">
                Jika Anda pernah meraih prestasi, tuliskan prestasi apa saja yang pernah Anda raih. Prestasi di sini, boleh yang sifatnya akademik maupun non-akademik.
            </label>
            <textarea name="prestasi" rows="3" placeholder="Tuliskan daftar prestasi Anda (opsional)..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('prestasi') }}</textarea>
        </div>
    </div>

    {{-- KARTU 7: DESKRIPSI DIRI & MINAT / CITA-CITA --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-5">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center gap-2">
            <h3 class="text-base font-semibold text-black">Deskripsi Diri & Minat</h3>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Tuliskan riwayat hidup yang sifatnya menimbulkan trauma (jika ada).</label>
            <textarea name="riwayat_trauma" rows="2" placeholder="Jawaban Anda (opsional)..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('riwayat_trauma') }}</textarea>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Tuliskan riwayat penyakit yang sifatnya sampai membutuhkan opname (jika ada).</label>
            <textarea name="riwayat_penyakit_opname" rows="2" placeholder="Jawaban Anda (opsional)..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('riwayat_penyakit_opname') }}</textarea>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Apakah Anda pernah konsultasi dengan psikolog sebelumnya? Jika ya, tuliskan jenis dan tujuan dari konsultasi tersebut.</label>
            <textarea name="konsultasi_psikolog" rows="2" placeholder="Jawaban Anda (opsional)..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('konsultasi_psikolog') }}</textarea>
        </div>

        {{-- Kelebihan Diri 1, 2, 3 --}}
        <div class="grid sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Kelebihan Diri 1 (Opsional)</label>
                <input type="text" name="kelebihan_1" value="{{ old('kelebihan_1') }}" placeholder="Kelebihan 1" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Kelebihan Diri 2 (Opsional)</label>
                <input type="text" name="kelebihan_2" value="{{ old('kelebihan_2') }}" placeholder="Kelebihan 2" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Kelebihan Diri 3 (Opsional)</label>
                <input type="text" name="kelebihan_3" value="{{ old('kelebihan_3') }}" placeholder="Kelebihan 3" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
        </div>

        {{-- Kelemahan Diri 1, 2, 3 --}}
        <div class="grid sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Kelemahan Diri 1 (Opsional)</label>
                <input type="text" name="kelemahan_1" value="{{ old('kelemahan_1') }}" placeholder="Kelemahan 1" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Kelemahan Diri 2 (Opsional)</label>
                <input type="text" name="kelemahan_2" value="{{ old('kelemahan_2') }}" placeholder="Kelemahan 2" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Kelemahan Diri 3 (Opsional)</label>
                <input type="text" name="kelemahan_3" value="{{ old('kelemahan_3') }}" placeholder="Kelemahan 3" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
        </div>

        {{-- Mata Pelajaran --}}
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Mata Pelajaran Nilai Tertinggi (Opsional)</label>
                <input type="text" name="mapel_nilai_tertinggi" value="{{ old('mapel_nilai_tertinggi') }}" placeholder="Contoh: Matematika, Bahasa Inggris" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Mata Pelajaran Nilai Terendah (Opsional)</label>
                <input type="text" name="mapel_nilai_terendah" value="{{ old('mapel_nilai_terendah') }}" placeholder="Contoh: Sejarah, Fisika" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Mata Pelajaran yang Disukai (Opsional)</label>
                <input type="text" name="mapel_disukai" value="{{ old('mapel_disukai') }}" placeholder="Pelajaran yang disukai..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Mata Pelajaran yang Tidak Disukai (Opsional)</label>
                <input type="text" name="mapel_tidak_disukai" value="{{ old('mapel_tidak_disukai') }}" placeholder="Pelajaran yang tidak disukai..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
        </div>

        {{-- Gaya Belajar, Kegiatan --}}
        <div class="grid sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Gaya Belajar (Opsional)</label>
                <input type="text" name="gaya_belajar" value="{{ old('gaya_belajar') }}" placeholder="Visual / Auditori / Kinestetik / dll" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Kegiatan yang Disenangi (Opsional)</label>
                <input type="text" name="hal_disenangi" value="{{ old('hal_disenangi') }}" placeholder="Kegiatan yang disenangi..." class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Kegiatan yang Tidak Disenangi (Opsional)</label>
                <input type="text" name="hal_tidak_disenangi" value="{{ old('hal_tidak_disenangi') }}" placeholder="Kegiatan yang tidak disenangi..." class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
        </div>

        {{-- Cita-Cita & Usaha --}}
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Cita-Cita (Opsional)</label>
            <input type="text" name="cita_cita" value="{{ old('cita_cita') }}" placeholder="Cita-cita Anda..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Usaha yang Telah Dilakukan untuk Cita-Cita (Opsional)</label>
                <textarea name="usaha_cita_cita" rows="2" placeholder="Usaha yang telah dijalankan..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('usaha_cita_cita') }}</textarea>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Rencana untuk Mencapai Cita-Cita (Opsional)</label>
                <textarea name="rencana_cita_cita" rows="2" placeholder="Rencana ke depan..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('rencana_cita_cita') }}</textarea>
            </div>
        </div>

        {{-- Bidang Pekerjaan & Aktivitas --}}
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Bidang Pekerjaan yang Disukai (Opsional)</label>
                <input type="text" name="pekerjaan_disukai" value="{{ old('pekerjaan_disukai') }}" placeholder="Bidang yang disukai..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Bidang Pekerjaan yang Tidak Disukai (Opsional)</label>
                <input type="text" name="pekerjaan_tidak_disukai" value="{{ old('pekerjaan_tidak_disukai') }}" placeholder="Bidang yang tidak disukai..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Aktivitas Disukai namun Kurang Mahir (Opsional)</label>
                <input type="text" name="aktivitas_disukai_kurang_mahir" value="{{ old('aktivitas_disukai_kurang_mahir') }}" placeholder="Aktivitas yang disukai tapi belum mahir..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Aktivitas Kurang Disukai namun Cukup Mahir (Opsional)</label>
                <input type="text" name="aktivitas_tidak_disukai_cukup_mahir" value="{{ old('aktivitas_tidak_disukai_cukup_mahir') }}" placeholder="Aktivitas yang kurang disukai tapi cukup mahir..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Deskripsikan diri Anda secara bebas dalam 1-2 paragraf. (Opsional)</label>
            <textarea name="deskripsi_diri_bebas" rows="3" placeholder="Gambarkan diri Anda secara bebas..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('deskripsi_diri_bebas') }}</textarea>
        </div>
    </div>

</div>
