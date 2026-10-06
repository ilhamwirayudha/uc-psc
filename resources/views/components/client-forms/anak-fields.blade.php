{{-- FORMULIR RIWAYAT HIDUP KONSELING - ANAK --}}
<div class="space-y-6">

    {{-- KARTU: LEMBAR PERSETUJUAN (INFORMED CONSENT) --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-4">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center gap-2">
            <h3 class="text-base font-bold text-purple-deep">Lembar Persetujuan Orang Tua / Wali <span class="text-red-500">*</span></h3>
        </div>

        <div>
            <p class="text-sm text-[#6B5B85] leading-relaxed mb-3">
                Dengan menyetujui pernyataan ini, orang tua / wali menyatakan bahwa seluruh data yang diisikan adalah benar dan menyetujui pelaksanaan layanan konseling anak di UC Psychological Service Center.
            </p>

            <label class="inline-flex items-center gap-2.5 cursor-pointer text-sm text-[#2A2035] font-semibold">
                <input type="checkbox" name="consent_agree" value="Setuju" required checked class="w-4 h-4 rounded text-purple-deep focus:ring-purple-deep border-[#D9C2F0]">
                <span>Menyetujui lembar persetujuan layanan konseling anak UC PSC <span class="text-red-500">*</span></span>
            </label>
            @error('consent_agree') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- KARTU 1: DATA DIRI ANAK --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-5">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center gap-2">
            <h3 class="text-base font-bold text-purple-deep">Data Diri Anak</h3>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Nama Lengkap Anak <span class="text-red-500">*</span></label>
                <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required placeholder="Nama lengkap ananda..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Jenis Kelamin Anak <span class="text-red-500">*</span></label>
                <select name="jenis_kelamin" required class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                    <option value="">-- Pilih --</option>
                    <option value="Laki-Laki" {{ old('jenis_kelamin') === 'Laki-Laki' ? 'selected' : '' }}>Laki-Laki</option>
                    <option value="Perempuan" {{ old('jenis_kelamin') === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Tempat Lahir Anak <span class="text-red-500">*</span></label>
                <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}" required placeholder="Contoh: Surabaya" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Tanggal Lahir Anak <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" required class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Urutan Kelahiran (Opsional)</label>
                <input type="text" name="urutan_kelahiran" value="{{ old('urutan_kelahiran') }}" placeholder="Contoh: Anak ke 2 dari 3 bersaudara" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Alamat Tempat Tinggal Anak <span class="text-red-500">*</span></label>
                <textarea name="alamat" rows="2" required placeholder="Alamat lengkap domisili anak..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('alamat') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">No. Telp / HP / WhatsApp <span class="text-red-500">*</span></label>
                <input type="tel" name="phone" value="{{ old('phone') }}" required placeholder="Kontak utama orang tua / anak" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Alamat Email Aktif <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="email@contoh.com" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Agama / Kepercayaan Anak <span class="text-red-500">*</span></label>
                <select name="agama" required class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                    <option value="">-- Pilih Agama --</option>
                    @foreach(['Islam', 'Kristen Protestan', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Lainnya'] as $agm)
                        <option value="{{ $agm }}" {{ old('agama') === $agm ? 'selected' : '' }}>{{ $agm }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Suku Bangsa Anak (Opsional)</label>
                <input type="text" name="suku_bangsa" value="{{ old('suku_bangsa') }}" placeholder="Contoh: Jawa, Tionghoa, Batak" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Pendidikan Terakhir / Kelas <span class="text-red-500">*</span></label>
                <input type="text" name="pendidikan_terakhir" value="{{ old('pendidikan_terakhir') }}" required placeholder="Contoh: Kelas 5 SD, Kelas 2 SMP" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Status Pernikahan Orang Tua (Opsional)</label>
                <select name="status_pernikahan_ortu" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                    <option value="">-- Pilih Status (Opsional) --</option>
                    <option value="Menikah" {{ old('status_pernikahan_ortu') === 'Menikah' ? 'selected' : '' }}>Menikah</option>
                    <option value="Bercerai" {{ old('status_pernikahan_ortu') === 'Bercerai' ? 'selected' : '' }}>Bercerai</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Alasan Anak Membutuhkan Konseling (Opsional)</label>
                <textarea name="alasan_konseling" rows="3" placeholder="Jelaskan alasan atau permasalahan yang dialami anak..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('alasan_konseling') }}</textarea>
            </div>
        </div>
    </div>

    {{-- KARTU 2: KONDISI PSIKOLOGIS ANAK --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-4">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center gap-2">
            <h3 class="text-base font-bold text-purple-deep">Data Kondisi Psikologis Anak</h3>
        </div>

        <div>
            <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Gambaran Kondisi Anak Saat Ini (Opsional)</label>
            <textarea name="kondisi_anak_saat_ini" rows="2" placeholder="Deskripsikan kondisi emosi, perilaku, atau keluhan anak..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('kondisi_anak_saat_ini') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Hal yang Ingin Ditingkatkan pada Diri Anak (Opsional)</label>
            <textarea name="hal_ingin_ditingkatkan" rows="2" placeholder="Tujuan perubahan perilaku/prestasi yang diharapkan..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('hal_ingin_ditingkatkan') }}</textarea>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Karakter Kepribadian Anak (Opsional)</label>
                <input type="text" name="karakter_anak" value="{{ old('karakter_anak') }}" placeholder="Contoh: Pemalu, aktif, sensitif, ceria" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Prestasi / Minat Bakat Anak (Opsional)</label>
                <input type="text" name="prestasi_anak" value="{{ old('prestasi_anak') }}" placeholder="Contoh: Juara gambar, gemar berhitung" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
        </div>

        <div class="grid sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Ketakutan / Phobia (Opsional)</label>
                <input type="text" name="ketakutan_phobia" value="{{ old('ketakutan_phobia') }}" placeholder="Contoh: Takut gelap, hewan tertentu" class="w-full px-4 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Riwayat Trauma (Opsional)</label>
                <input type="text" name="riwayat_trauma" value="{{ old('riwayat_trauma') }}" placeholder="Peristiwa yang membekas" class="w-full px-4 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Riwayat Kesehatan (Opsional)</label>
                <input type="text" name="riwayat_kesehatan" value="{{ old('riwayat_kesehatan') }}" placeholder="Alergi, riwayat sakit" class="w-full px-4 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
            </div>
        </div>
    </div>

    {{-- KARTU 3: GAMBARAN RELASI ANAK --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-4">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center gap-2">
            <h3 class="text-base font-bold text-purple-deep">Gambaran Relasi Anak</h3>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Relasi Anak dengan Ayah (Opsional)</label>
                <textarea name="relasi_ayah" rows="2" placeholder="Bagaimana kedekatan dan komunikasi dengan ayah..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('relasi_ayah') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Relasi Anak dengan Ibu (Opsional)</label>
                <textarea name="relasi_ibu" rows="2" placeholder="Bagaimana kedekatan dan komunikasi dengan ibu..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('relasi_ibu') }}</textarea>
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Relasi dengan Saudara (Opsional)</label>
                <input type="text" name="relasi_saudara" value="{{ old('relasi_saudara') }}" placeholder="Rukun / Sering bertengkar" class="w-full px-4 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Relasi dengan Teman Sebaya (Opsional)</label>
                <input type="text" name="relasi_teman" value="{{ old('relasi_teman') }}" placeholder="Mudah berteman / Menarik diri" class="w-full px-4 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
            </div>
        </div>
    </div>

    {{-- KARTU 4: DATA ORANG TUA (AYAH) --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-4">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center gap-2">
            <h3 class="text-base font-bold text-purple-deep">Data Orang Tua (Ayah)</h3>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Nama Ayah (Opsional)</label>
                <input type="text" name="nama_ayah" value="{{ old('nama_ayah') }}" placeholder="Nama lengkap ayah" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                <input type="hidden" name="jenis_kelamin_ayah" value="Laki-Laki">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Usia Ayah (Opsional)</label>
                <input type="text" name="usia_ayah" value="{{ old('usia_ayah') }}" placeholder="Contoh: 42 Tahun" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Pendidikan Terakhir Ayah (Opsional)</label>
                <input type="text" name="pendidikan_ayah" value="{{ old('pendidikan_ayah') }}" placeholder="Contoh: S1 Ekonomi" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Pekerjaan Ayah (Opsional)</label>
                <input type="text" name="pekerjaan_ayah" value="{{ old('pekerjaan_ayah') }}" placeholder="Contoh: Manajer Operasional" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Agama Ayah (Opsional)</label>
                <input type="text" name="agama_ayah" value="{{ old('agama_ayah') }}" placeholder="Agama ayah" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Urutan Kelahiran Ayah (Opsional)</label>
                <input type="text" name="urutan_kelahiran_ayah" value="{{ old('urutan_kelahiran_ayah') }}" placeholder="Anak ke 1 dari 2 bersaudara" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Alamat Ayah (Opsional)</label>
                <input type="text" name="alamat_ayah" value="{{ old('alamat_ayah') }}" placeholder="Alamat tinggal ayah" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">No. Telp / HP Ayah (Opsional)</label>
                <input type="tel" name="phone_ayah" value="{{ old('phone_ayah') }}" placeholder="08xxxxxxxx" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Pernikahan Ayah Ke- (Opsional)</label>
                <input type="text" name="pernikahan_ayah_ke" value="{{ old('pernikahan_ayah_ke', '1') }}" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Jumlah Anak Ayah (Opsional)</label>
                <input type="text" name="jumlah_anak_ayah" value="{{ old('jumlah_anak_ayah', '1') }}" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Status Pernikahan Ayah (Opsional)</label>
                <select name="status_nikah_ayah" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    <option value="">-- Pilih Status --</option>
                    <option value="Menikah" {{ old('status_nikah_ayah', 'Menikah') === 'Menikah' ? 'selected' : '' }}>Menikah</option>
                    <option value="Bercerai" {{ old('status_nikah_ayah') === 'Bercerai' ? 'selected' : '' }}>Bercerai</option>
                </select>
            </div>
        </div>
    </div>

    {{-- KARTU 5: DATA ORANG TUA (IBU) --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-4">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center gap-2">
            <h3 class="text-base font-bold text-purple-deep">Data Orang Tua (Ibu)</h3>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Nama Ibu (Opsional)</label>
                <input type="text" name="nama_ibu" value="{{ old('nama_ibu') }}" placeholder="Nama lengkap ibu" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                <input type="hidden" name="jenis_kelamin_ibu" value="Perempuan">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Usia Ibu (Opsional)</label>
                <input type="text" name="usia_ibu" value="{{ old('usia_ibu') }}" placeholder="Contoh: 39 Tahun" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Pendidikan Terakhir Ibu (Opsional)</label>
                <input type="text" name="pendidikan_ibu" value="{{ old('pendidikan_ibu') }}" placeholder="Contoh: S1 Psikologi" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Pekerjaan Ibu (Opsional)</label>
                <input type="text" name="pekerjaan_ibu" value="{{ old('pekerjaan_ibu') }}" placeholder="Contoh: Ibu Rumah Tangga / Guru" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Agama Ibu (Opsional)</label>
                <input type="text" name="agama_ibu" value="{{ old('agama_ibu') }}" placeholder="Agama ibu" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Urutan Kelahiran Ibu (Opsional)</label>
                <input type="text" name="urutan_kelahiran_ibu" value="{{ old('urutan_kelahiran_ibu') }}" placeholder="Anak ke 2 dari 3 bersaudara" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Alamat Ibu (Opsional)</label>
                <input type="text" name="alamat_ibu" value="{{ old('alamat_ibu') }}" placeholder="Alamat tinggal ibu" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">No. Telp / HP Ibu (Opsional)</label>
                <input type="tel" name="phone_ibu" value="{{ old('phone_ibu') }}" placeholder="08xxxxxxxx" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Pernikahan Ibu Ke- (Opsional)</label>
                <input type="text" name="pernikahan_ibu_ke" value="{{ old('pernikahan_ibu_ke', '1') }}" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Jumlah Anak Ibu (Opsional)</label>
                <input type="text" name="jumlah_anak_ibu" value="{{ old('jumlah_anak_ibu', '1') }}" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Status Pernikahan Ibu (Opsional)</label>
                <select name="status_nikah_ibu" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    <option value="">-- Pilih Status --</option>
                    <option value="Menikah" {{ old('status_nikah_ibu', 'Menikah') === 'Menikah' ? 'selected' : '' }}>Menikah</option>
                    <option value="Bercerai" {{ old('status_nikah_ibu') === 'Bercerai' ? 'selected' : '' }}>Bercerai</option>
                </select>
            </div>
        </div>
    </div>

    {{-- KARTU 6: LAIN-LAIN & PREFERENSI --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-4">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center gap-2">
            <h3 class="text-base font-bold text-purple-deep">Lain-Lain & Preferensi</h3>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Anak Pernah Konseling Sebelumnya? (Opsional)</label>
                <select name="pernah_konseling" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    <option value="Tidak" {{ old('pernah_konseling', 'Tidak') === 'Tidak' ? 'selected' : '' }}>Tidak, pertama kali</option>
                    <option value="Ya" {{ old('pernah_konseling') === 'Ya' ? 'selected' : '' }}>Ya, pernah sebelumnya</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Nama Konselor Lama (Opsional)</label>
                <input type="text" name="nama_konselor_lama" value="{{ old('nama_konselor_lama') }}" placeholder="Konselor / psikolog sebelumnya" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Kontak Darurat (Nama, Relasi, No. Telp) (Opsional)</label>
                <input type="text" name="kontak_darurat" value="{{ old('kontak_darurat') }}" placeholder="Contoh: Kakek Budi (Kakek) - 0812345678" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Sumber Informasi Mengenai UC PSC (Opsional)</label>
                <select name="sumber_info" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    <option value="">-- Pilih Sumber Info --</option>
                    <option value="Media Sosial / Instagram" {{ old('sumber_info') === 'Media Sosial / Instagram' ? 'selected' : '' }}>Media Sosial / Instagram</option>
                    <option value="Sekolah / Guru" {{ old('sumber_info') === 'Sekolah / Guru' ? 'selected' : '' }}>Rujukan Sekolah / Guru</option>
                    <option value="Rekomendasi Kerabat" {{ old('sumber_info') === 'Rekomendasi Kerabat' ? 'selected' : '' }}>Rekomendasi Teman / Kerabat</option>
                    <option value="Website UC" {{ old('sumber_info') === 'Website UC' ? 'selected' : '' }}>Website UC</option>
                    <option value="Lainnya" {{ old('sumber_info') === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Preferensi Proses Konseling (Opsional)</label>
                <select name="preferensi_konseling" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    <option value="">-- Pilih Preferensi --</option>
                    <option value="Offline di UCPSC" {{ old('preferensi_konseling', 'Offline di UCPSC') === 'Offline di UCPSC' ? 'selected' : '' }}>Offline (Tatap Muka di UC PSC)</option>
                    <option value="Online melalui Zoom" {{ old('preferensi_konseling') === 'Online melalui Zoom' ? 'selected' : '' }}>Online (Melalui Zoom Meeting)</option>
                </select>
            </div>
        </div>
    </div>

</div>
