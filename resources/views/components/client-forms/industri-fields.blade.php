{{-- FORMULIR RIWAYAT HIDUP - INDUSTRI --}}
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
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Nama Lengkap (Beserta Gelar Jika Ada) <span class="text-red-500">*</span></label>
                <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required placeholder="Contoh: Budi Santoso, S.T." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Nama Perusahaan <span class="text-red-500">*</span></label>
                <input type="text" name="nama_perusahaan" value="{{ old('nama_perusahaan') }}" required placeholder="Contoh: PT Ciputra Development Tbk" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Pekerjaan Saat Ini <span class="text-red-500">*</span></label>
                <input type="text" name="pekerjaan_saat_ini" value="{{ old('pekerjaan_saat_ini') }}" required placeholder="Contoh: Staff Keuangan / HR Officer" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
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
                <input type="text" name="tempat_tanggal_lahir" value="{{ old('tempat_tanggal_lahir') }}" required placeholder="Contoh: Surabaya, 15 Januari 1998" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Alamat Sekarang (Opsional)</label>
                <textarea name="alamat_sekarang" rows="2" placeholder="Alamat domisili saat ini..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('alamat_sekarang') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">No. Telp/HP/WhatsApp <span class="text-red-500">*</span></label>
                <input type="tel" name="phone" value="{{ old('phone') }}" required placeholder="08xxxxxxxx" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Alamat Email Aktif <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="email@perusahaan.com" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Agama/Kepercayaan (Opsional)</label>
                <select name="agama" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                    <option value="">-- Pilih Agama (Opsional) --</option>
                    @foreach(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Lainnya'] as $agm)
                        <option value="{{ $agm }}" {{ old('agama') === $agm ? 'selected' : '' }}>{{ $agm }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Pendidikan Terakhir <span class="text-red-500">*</span></label>
                <select name="pendidikan_terakhir" required class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                    <option value="">-- Pilih Pendidikan --</option>
                    @foreach(['SMA', 'SMK', 'D3', 'D4', 'S1', 'S2', 'S3'] as $pnd)
                        <option value="{{ $pnd }}" {{ old('pendidikan_terakhir') === $pnd ? 'selected' : '' }}>{{ $pnd }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Urutan Kelahiran (Opsional)</label>
                <input type="text" name="urutan_kelahiran" value="{{ old('urutan_kelahiran') }}" placeholder="Contoh: Anak ke 2 dari 3 bersaudara" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Asal Kota (Opsional)</label>
                <input type="text" name="asal_kota" value="{{ old('asal_kota') }}" placeholder="Contoh: Surabaya" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Suku Bangsa (Opsional)</label>
                <input type="text" name="suku_bangsa" value="{{ old('suku_bangsa') }}" placeholder="Contoh: Jawa, Tionghoa, Batak" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Status Perkawinan (Opsional)</label>
                <select name="status_perkawinan" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                    <option value="">-- Pilih Status (Opsional) --</option>
                    @foreach(['Belum Menikah', 'Menikah', 'Bercerai', 'Pasangan Meninggal'] as $st)
                        <option value="{{ $st }}" {{ old('status_perkawinan') === $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Posisi yang Dituju (Opsional)</label>
                <input type="text" name="posisi_dituju" value="{{ old('posisi_dituju') }}" placeholder="Contoh: Management Trainee / Finance Staff" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
        </div>
    </div>

    {{-- KARTU 2: KUESIONER STRES PSS-10 --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-4">
        <div class="pb-3 border-b border-[#EDE1FA]">
            <div class="flex items-center gap-2">
                <h3 class="text-base font-semibold text-black">Kuesioner Stres (PSS-10)</h3>
            </div>
            <p class="text-xs font-medium text-gray-500 mt-1">Skala: <strong>0</strong> (Tidak Pernah), <strong>1</strong> (Hampir Tidak Pernah), <strong>2</strong> (Kadang-kadang), <strong>3</strong> (Cukup Sering), <strong>4</strong> (Sangat Sering) - (Opsional).</p>
        </div>

        @php
        $pssQuestions = [
            1 => 'Dalam sebulan terakhir, seberapa sering Anda merasa kecewa karena sesuatu yang terjadi secara tidak terduga?',
            2 => 'Dalam sebulan terakhir, seberapa sering Anda merasa tidak mampu mengendalikan hal-hal penting dalam hidup Anda?',
            3 => 'Dalam sebulan terakhir, seberapa sering Anda merasa gelisah dan stres?',
            4 => 'Dalam sebulan terakhir, seberapa sering Anda merasa percaya diri dengan kemampuan Anda untuk menyelesaikan masalah pribadi Anda?',
            5 => 'Dalam sebulan terakhir, seberapa sering Anda merasa segala sesuatu berjalan sesuai keinginan Anda?',
            6 => 'Dalam sebulan terakhir, seberapa sering Anda mengetahui bahwa Anda tidak bisa mengatasi hal-hal yang harus Anda lakukan?',
            7 => 'Dalam sebulan terakhir, seberapa sering Anda mampu mengendalikan hal-hal yang menjengkelkan dalam hidup Anda?',
            8 => 'Dalam sebulan terakhir, seberapa sering Anda merasa mampu mengendalikan permasalahan Anda?',
            9 => 'Dalam sebulan terakhir, seberapa sering Anda marah karena hal-hal yang terjadi di luar kendali Anda?',
            10 => 'Dalam sebulan terakhir, seberapa sering Anda merasa tidak mampu menyelesaikan permasalahan permasalahan yang menumpuk dalam hidup Anda?',
        ];
        @endphp

        <div class="space-y-3.5">
            @foreach($pssQuestions as $num => $question)
            <div class="p-3.5 bg-slate-50/60 rounded-xl border border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                <span class="text-xs font-medium text-black flex-1 leading-snug">{{ $num }}. {{ $question }}</span>
                <div class="flex items-center gap-2 flex-shrink-0">
                    @for($val = 0; $val <= 4; $val++)
                    <label class="flex items-center gap-1.5 text-xs cursor-pointer font-medium px-2.5 py-1 rounded-lg border border-[#EDE1FA] bg-white hover:border-purple-deep">
                        <input type="radio" name="pss_{{ $num }}" value="{{ $val }}" {{ old('pss_' . $num) !== null && (string)old('pss_' . $num) === (string)$val ? 'checked' : '' }} class="text-purple-deep focus:ring-purple-deep w-4 h-4">
                        <span>{{ $val }}</span>
                    </label>
                    @endfor
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- KARTU 3: DATA KELUARGA --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-6">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div>
                    <h3 class="text-base font-semibold text-black">Data Keluarga</h3>
                    <p class="text-xs text-slate-500">Informasi latar belakang keluarga (orang tua, pasangan, saudara, dan anak) - opsional.</p>
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
                    <input type="text" name="ayah_usia" value="{{ old('ayah_usia') }}" placeholder="Contoh: 58 Tahun" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Pendidikan</label>
                    <input type="text" name="ayah_pendidikan" value="{{ old('ayah_pendidikan') }}" placeholder="Contoh: S1" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
                <div class="md:col-span-5">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Pekerjaan Ayah</label>
                    <input type="text" name="ayah_pekerjaan" value="{{ old('ayah_pekerjaan') }}" placeholder="Contoh: Pensiunan PNS / Wiraswasta" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
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
                    <input type="text" name="ibu_usia" value="{{ old('ibu_usia') }}" placeholder="Contoh: 54 Tahun" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Pendidikan</label>
                    <input type="text" name="ibu_pendidikan" value="{{ old('ibu_pendidikan') }}" placeholder="Contoh: SMA" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
                <div class="md:col-span-5">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Pekerjaan Ibu</label>
                    <input type="text" name="ibu_pekerjaan" value="{{ old('ibu_pekerjaan') }}" placeholder="Contoh: Ibu Rumah Tangga / Guru" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
            </div>
        </div>

        {{-- Data Suami / Istri --}}
        <div class="p-4 bg-[#FAF9FD] rounded-xl border border-[#EDE1FA]/80 space-y-3">
            <h4 class="text-sm font-semibold text-black border-b border-[#EDE1FA] pb-1.5">Data Suami / Istri <span class="text-xs font-normal text-slate-500">(Jika sudah menikah)</span></h4>
            <div class="grid sm:grid-cols-2 md:grid-cols-5 gap-3">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Nama Suami/Istri</label>
                    <input type="text" name="pasangan_nama" value="{{ old('pasangan_nama') }}" placeholder="Nama pasangan" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Jenis Kelamin</label>
                    <select name="pasangan_jenis_kelamin" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                        <option value="">-- Pilih --</option>
                        <option value="Laki-Laki" {{ old('pasangan_jenis_kelamin') === 'Laki-Laki' ? 'selected' : '' }}>Laki-Laki</option>
                        <option value="Perempuan" {{ old('pasangan_jenis_kelamin') === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Usia</label>
                    <input type="text" name="pasangan_usia" value="{{ old('pasangan_usia') }}" placeholder="Usia pasangan" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Pendidikan</label>
                    <input type="text" name="pasangan_pendidikan" value="{{ old('pasangan_pendidikan') }}" placeholder="Pendidikan" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
                <div class="md:col-span-5">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Pekerjaan</label>
                    <input type="text" name="pasangan_pekerjaan" value="{{ old('pasangan_pekerjaan') }}" placeholder="Pekerjaan pasangan" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
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
                        <input type="text" name="saudara_{{ $s }}_nama" value="{{ old('saudara_' . $s . '_nama') }}" placeholder="Nama" class="w-full px-2.5 py-1.5 bg-white border border-[#D9C2F0] rounded-lg text-xs">
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

        {{-- Data Anak (1 s.d. 5) --}}
        <div class="space-y-3">
            <h4 class="text-sm font-semibold text-black">Data Anak (1 s.d. 5)</h4>
            @for($a = 1; $a <= 5; $a++)
            <div class="p-3.5 bg-[#FAF9FD] rounded-xl border border-[#EDE1FA]/80 space-y-2">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-black">Anak {{ $a }} <span class="text-slate-400 font-normal">(Opsional)</span></span>
                </div>
                <div class="grid sm:grid-cols-2 md:grid-cols-5 gap-2.5">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Nama Anak {{ $a }}</label>
                        <input type="text" name="anak_{{ $a }}_nama" value="{{ old('anak_' . $a . '_nama') }}" placeholder="Nama" class="w-full px-2.5 py-1.5 bg-white border border-[#D9C2F0] rounded-lg text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Jenis Kelamin</label>
                        <select name="anak_{{ $a }}_jenis_kelamin" class="w-full px-2 py-1.5 bg-white border border-[#D9C2F0] rounded-lg text-xs">
                            <option value="">--</option>
                            <option value="Laki-Laki" {{ old('anak_' . $a . '_jenis_kelamin') === 'Laki-Laki' ? 'selected' : '' }}>Laki-Laki</option>
                            <option value="Perempuan" {{ old('anak_' . $a . '_jenis_kelamin') === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Usia</label>
                        <input type="text" name="anak_{{ $a }}_usia" value="{{ old('anak_' . $a . '_usia') }}" placeholder="Usia" class="w-full px-2.5 py-1.5 bg-white border border-[#D9C2F0] rounded-lg text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Pendidikan</label>
                        <input type="text" name="anak_{{ $a }}_pendidikan" value="{{ old('anak_' . $a . '_pendidikan') }}" placeholder="Pendidikan" class="w-full px-2.5 py-1.5 bg-white border border-[#D9C2F0] rounded-lg text-xs">
                    </div>
                    <div class="md:col-span-5">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Pekerjaan</label>
                        <input type="text" name="anak_{{ $a }}_pekerjaan" value="{{ old('anak_' . $a . '_pekerjaan') }}" placeholder="Pekerjaan" class="w-full px-2.5 py-1.5 bg-white border border-[#D9C2F0] rounded-lg text-xs">
                    </div>
                </div>
            </div>
            @endfor
        </div>
    </div>

    {{-- KARTU 4: RIWAYAT PENDIDIKAN FORMAL --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-5">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div>
                    <h3 class="text-base font-semibold text-black">Riwayat Pendidikan Formal</h3>
                    <p class="text-xs text-slate-500">Tuliskan dari yang paling akhir (opsional).</p>
                </div>
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">IPK Terakhir (Jika Ada)</label>
            <input type="text" name="ipk_terakhir" value="{{ old('ipk_terakhir') }}" placeholder="Contoh: 3.75" class="w-full sm:w-1/2 px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
        </div>

        {{-- Sekolah 1 --}}
        <div class="p-4 bg-purple-50/20 rounded-xl border border-purple-200/80 space-y-3">
            <h4 class="text-sm font-semibold text-black border-b border-[#EDE1FA] pb-1.5">Sekolah / Universitas 1 (Pendidikan Terakhir)</h4>
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
                    <label class="block text-xs font-medium text-gray-500 mb-1">Keterangan Sekolah 1 (Jurusan)</label>
                    <input type="text" name="keterangan_sekolah_1" value="{{ old('keterangan_sekolah_1') }}" placeholder="Contoh: S1 Teknik Informatika" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
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
            <h4 class="text-sm font-semibold text-black border-b border-[#EDE1FA] pb-1.5">Sekolah / Universitas {{ $sk }} <span class="text-slate-400 font-normal">(Opsional)</span></h4>
            <div class="grid sm:grid-cols-2 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Nama Sekolah {{ $sk }}</label>
                    <input type="text" name="nama_sekolah_{{ $sk }}" value="{{ old('nama_sekolah_' . $sk) }}" placeholder="Nama sekolah / universitas" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Kota Sekolah {{ $sk }}</label>
                    <input type="text" name="kota_sekolah_{{ $sk }}" value="{{ old('kota_sekolah_' . $sk) }}" placeholder="Kota" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Keterangan Sekolah {{ $sk }} (Jurusan)</label>
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
    </div>

    {{-- KARTU 5: PENDIDIKAN NON FORMAL --}}
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
                <h4 class="text-sm font-semibold text-black">Kursus / Pelatihan {{ $c }}</h4>
                <div class="grid sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Jenis Kursus {{ $c }}</label>
                        <input type="text" name="jenis_kursus_{{ $c }}" value="{{ old('jenis_kursus_' . $c) }}" placeholder="Contoh: Digital Marketing" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Tempat Kursus {{ $c }}</label>
                        <input type="text" name="tempat_kursus_{{ $c }}" value="{{ old('tempat_kursus_' . $c) }}" placeholder="Lembaga / Kota" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Lama Kursus {{ $c }}</label>
                        <input type="text" name="lama_kursus_{{ $c }}" value="{{ old('lama_kursus_' . $c) }}" placeholder="Contoh: 3 Bulan" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                </div>
            </div>
            @endfor
        </div>
    </div>

    {{-- KARTU 6: RIWAYAT PEKERJAAN --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-5">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div>
                    <h3 class="text-base font-semibold text-black">Riwayat Pekerjaan</h3>
                    <p class="text-xs text-slate-500">Tuliskan dari yang paling akhir. Bagi fresh graduate, bisa menuliskan pengalaman magang (opsional).</p>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            @for($w = 1; $w <= 5; $w++)
            <div class="p-4 bg-[#FAF9FD] rounded-xl border border-[#EDE1FA]/80 space-y-3">
                <h4 class="text-sm font-semibold text-black">Instansi / Perusahaan {{ $w }} <span class="text-slate-400 font-normal">(Opsional)</span></h4>
                <div class="grid sm:grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Nama Instansi {{ $w }}</label>
                        <input type="text" name="instansi_{{ $w }}_nama" value="{{ old('instansi_' . $w . '_nama') }}" placeholder="Nama perusahaan / instansi" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Jabatan Instansi {{ $w }}</label>
                        <input type="text" name="instansi_{{ $w }}_jabatan" value="{{ old('instansi_' . $w . '_jabatan') }}" placeholder="Jabatan" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Tahun Masuk</label>
                        <input type="text" name="instansi_{{ $w }}_tahun_masuk" value="{{ old('instansi_' . $w . '_tahun_masuk') }}" placeholder="Tahun masuk" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Tahun Keluar</label>
                        <input type="text" name="instansi_{{ $w }}_tahun_keluar" value="{{ old('instansi_' . $w . '_tahun_keluar') }}" placeholder="Tahun keluar" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Gaji dan Tunjangan</label>
                        <input type="text" name="instansi_{{ $w }}_gaji" value="{{ old('instansi_' . $w . '_gaji') }}" placeholder="Contoh: Rp 5.000.000" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Alasan Berhenti</label>
                        <input type="text" name="instansi_{{ $w }}_alasan_berhenti" value="{{ old('instansi_' . $w . '_alasan_berhenti') }}" placeholder="Alasan keluar / selesai" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                    <div class="sm:col-span-2 md:col-span-4">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Ringkasan Tugas Instansi {{ $w }}</label>
                        <textarea name="instansi_{{ $w }}_tugas" rows="2" placeholder="Deskripsikan tanggung jawab utama..." class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">{{ old('instansi_' . $w . '_tugas') }}</textarea>
                    </div>
                </div>
            </div>
            @endfor
        </div>

        <div class="pt-2">
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Ekspektasi Gaji dan Tunjangan pada Perusahaan Ini (Opsional)</label>
            <input type="text" name="ekspektasi_gaji_tunjangan" value="{{ old('ekspektasi_gaji_tunjangan') }}" placeholder="Contoh: Rp 7.000.000 - Rp 9.000.000 beserta BPJS dan tunjangan transport" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
        </div>
    </div>

    {{-- KARTU 7: PENGALAMAN ORGANISASI --}}
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
                        <input type="text" name="organisasi_{{ $o }}_lama" value="{{ old('organisasi_' . $o . '_lama') }}" placeholder="Contoh: 1 Tahun (2022 - 2023)" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                </div>
            </div>
            @endfor
        </div>
    </div>

    {{-- KARTU 8: PRESTASI --}}
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

    {{-- KARTU 9: DESKRIPSI DIRI --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-4">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center gap-2">
            <h3 class="text-base font-semibold text-black">Deskripsi Diri</h3>
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

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Hobi (Opsional)</label>
            <input type="text" name="hobi" value="{{ old('hobi') }}" placeholder="Hobi yang sering dilakukan..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Deskripsikan diri Anda secara bebas dalam 1-2 paragraf. (Opsional)</label>
            <textarea name="deskripsi_diri_bebas" rows="3" placeholder="Gambarkan karakter, etos kerja, dan kepribadian diri Anda secara umum..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('deskripsi_diri_bebas') }}</textarea>
        </div>
    </div>

</div>
