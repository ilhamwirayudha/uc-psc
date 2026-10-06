{{-- FORMULIR RIWAYAT HIDUP KONSELING - DEWASA --}}
<div class="space-y-6">

    {{-- KARTU: LEMBAR PERSETUJUAN (INFORMED CONSENT) --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-4">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center gap-2">
            <h3 class="text-base font-bold text-purple-deep">Lembar Persetujuan (Informed Consent) <span class="text-red-500">*</span></h3>
        </div>

        <div>
            <p class="text-sm text-[#6B5B85] leading-relaxed mb-3">
                Dengan menyetujui pernyataan ini, saya menyatakan bahwa seluruh data yang diisikan adalah benar dan saya menyetujui pelaksanaan layanan di UC Psychological Service Center.
            </p>

            <label class="inline-flex items-center gap-2.5 cursor-pointer text-sm text-[#2A2035] font-semibold">
                <input type="checkbox" name="consent_agree" value="Setuju" required checked class="w-4 h-4 rounded text-purple-deep focus:ring-purple-deep border-[#D9C2F0]">
                <span>Menyetujui lembar persetujuan layanan konseling UC PSC <span class="text-red-500">*</span></span>
            </label>
            @error('consent_agree') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- KARTU 1: DATA IDENTITAS & DIRI --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-5">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center gap-2">
            <h3 class="text-base font-bold text-purple-deep">Data Identitas & Diri</h3>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            {{-- Nama Lengkap --}}
            <div class="sm:col-span-2">
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                    Nama Lengkap (Beserta Gelar Jika Ada) <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required
                    placeholder="Contoh: Ahmad Fauzi Pratama, S.Kom"
                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('nama_lengkap') border-red-400 bg-red-50/20 @enderror">
                @error('nama_lengkap') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Jenis Kelamin --}}
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                    Jenis Kelamin <span class="text-red-500">*</span>
                </label>
                <select name="jenis_kelamin" required
                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('jenis_kelamin') border-red-400 bg-red-50/20 @enderror">
                    <option value="">-- Pilih Jenis Kelamin --</option>
                    <option value="Laki-Laki" {{ old('jenis_kelamin') === 'Laki-Laki' ? 'selected' : '' }}>Laki-Laki</option>
                    <option value="Perempuan" {{ old('jenis_kelamin') === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                </select>
                @error('jenis_kelamin') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Tempat Lahir --}}
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                    Tempat Lahir <span class="text-red-500">*</span>
                </label>
                <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}" required
                    placeholder="Contoh: Surabaya"
                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('tempat_lahir') border-red-400 bg-red-50/20 @enderror">
                @error('tempat_lahir') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Tanggal Lahir --}}
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                    Tanggal Lahir <span class="text-red-500">*</span>
                </label>
                <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" required
                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('tanggal_lahir') border-red-400 bg-red-50/20 @enderror">
                @error('tanggal_lahir') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Urutan Kelahiran --}}
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                    Urutan Kelahiran (Opsional)
                </label>
                <input type="text" name="urutan_kelahiran" value="{{ old('urutan_kelahiran') }}"
                    placeholder="Contoh: Anak ke 1 dari 3 bersaudara"
                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('urutan_kelahiran') border-red-400 bg-red-50/20 @enderror">
                @error('urutan_kelahiran') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Alamat --}}
            <div class="sm:col-span-2">
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                    Alamat Tempat Tinggal Saat Ini <span class="text-red-500">*</span>
                </label>
                <textarea name="alamat" rows="2" required
                    placeholder="Alamat lengkap domisili saat ini..."
                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('alamat') border-red-400 bg-red-50/20 @enderror">{{ old('alamat') }}</textarea>
                @error('alamat') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- No WhatsApp / Telepon --}}
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                    No. Telp / WhatsApp Aktif <span class="text-red-500">*</span>
                </label>
                <input type="tel" name="phone" value="{{ old('phone') }}" required
                    placeholder="Contoh: 081234567890"
                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('phone') border-red-400 bg-red-50/20 @enderror">
                @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Email --}}
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                    Alamat Email Aktif <span class="text-red-500">*</span>
                </label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    placeholder="nama@email.com"
                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('email') border-red-400 bg-red-50/20 @enderror">
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Agama --}}
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                    Agama / Kepercayaan <span class="text-red-500">*</span>
                </label>
                <select name="agama" required
                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('agama') border-red-400 bg-red-50/20 @enderror">
                    <option value="">-- Pilih Agama --</option>
                    @foreach(['Islam', 'Kristen Protestan', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Lainnya'] as $agm)
                        <option value="{{ $agm }}" {{ old('agama') === $agm ? 'selected' : '' }}>{{ $agm }}</option>
                    @endforeach
                </select>
                @error('agama') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Suku Bangsa --}}
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                    Suku Bangsa (Opsional)
                </label>
                <input type="text" name="suku_bangsa" value="{{ old('suku_bangsa') }}"
                    placeholder="Contoh: Jawa, Tionghoa, Batak, Sunda"
                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('suku_bangsa') border-red-400 bg-red-50/20 @enderror">
                @error('suku_bangsa') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Pendidikan Terakhir --}}
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                    Pendidikan Terakhir <span class="text-red-500">*</span>
                </label>
                <select name="pendidikan_terakhir" required
                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('pendidikan_terakhir') border-red-400 bg-red-50/20 @enderror">
                    <option value="">-- Pilih Pendidikan --</option>
                    @foreach(['SD', 'SMP', 'SMA / SMK', 'Diploma (D3/D4)', 'Sarjana (S1)', 'Magister (S2)', 'Doktor (S3)', 'Lainnya'] as $edu)
                        <option value="{{ $edu }}" {{ old('pendidikan_terakhir') === $edu ? 'selected' : '' }}>{{ $edu }}</option>
                    @endforeach
                </select>
                @error('pendidikan_terakhir') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Pekerjaan --}}
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                    Pekerjaan Saat Ini (Opsional)
                </label>
                <input type="text" name="pekerjaan" value="{{ old('pekerjaan') }}"
                    placeholder="Contoh: Karyawan Swasta, Wiraswasta, Mahasiswa"
                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            {{-- Hobi --}}
            <div class="sm:col-span-2">
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                    Hobi / Minat Kegemaran (Opsional)
                </label>
                <input type="text" name="hobi" value="{{ old('hobi') }}"
                    placeholder="Contoh: Membaca buku, bersepeda, mendengarkan musik"
                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('hobi') border-red-400 bg-red-50/20 @enderror">
                @error('hobi') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Alasan Konseling --}}
            <div class="sm:col-span-2">
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                    Alasan Membutuhkan Konseling (Opsional)
                </label>
                <textarea name="alasan_konseling" rows="3"
                    placeholder="Jelaskan alasan atau hal yang mendorong Anda mencari layanan konseling..."
                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('alasan_konseling') border-red-400 bg-red-50/20 @enderror">{{ old('alasan_konseling') }}</textarea>
                @error('alasan_konseling') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    {{-- KARTU 2: DATA KONDISI PSIKOLOGIS --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-5">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center gap-2">
            <h3 class="text-base font-bold text-purple-deep">Data Kondisi Psikologis</h3>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                    Gambaran Kondisi Saat Ini (Opsional)
                </label>
                <textarea name="kondisi_saat_ini" rows="2"
                    placeholder="Jelaskan apa yang sedang Anda rasakan atau alami secara umum saat ini..."
                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('kondisi_saat_ini') border-red-400 bg-red-50/20 @enderror">{{ old('kondisi_saat_ini') }}</textarea>
                @error('kondisi_saat_ini') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                    Pikiran Negatif yang Sering Muncul (Opsional)
                </label>
                <textarea name="pikiran_negatif" rows="2"
                    placeholder="Pikiran-pikiran yang mengganggu atau membebani pikiran Anda..."
                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('pikiran_negatif') border-red-400 bg-red-50/20 @enderror">{{ old('pikiran_negatif') }}</textarea>
                @error('pikiran_negatif') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                    Hal-Hal yang Ingin Ditingkatkan dalam Hidup (Opsional)
                </label>
                <textarea name="hal_ingin_ditingkatkan" rows="2"
                    placeholder="Tujuan atau aspek hidup yang ingin diperbaiki melalui proses konseling..."
                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('hal_ingin_ditingkatkan') border-red-400 bg-red-50/20 @enderror">{{ old('hal_ingin_ditingkatkan') }}</textarea>
                @error('hal_ingin_ditingkatkan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                        Karakter Kepribadian Anda (Opsional)
                    </label>
                    <input type="text" name="karakter_kepribadian" value="{{ old('karakter_kepribadian') }}"
                        placeholder="Contoh: Terbuka, pendiam, perfeksionis, perasa"
                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('karakter_kepribadian') border-red-400 bg-red-50/20 @enderror">
                    @error('karakter_kepribadian') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                        Rata-Rata Jam Tidur Per Malam (Opsional)
                    </label>
                    <input type="text" name="jam_tidur" value="{{ old('jam_tidur') }}"
                        placeholder="Contoh: 6 - 7 jam per malam"
                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('jam_tidur') border-red-400 bg-red-50/20 @enderror">
                    @error('jam_tidur') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                    Ketakutan / Phobia Tertentu (Opsional)
                </label>
                <input type="text" name="ketakutan_phobia" value="{{ old('ketakutan_phobia') }}"
                    placeholder="Contoh: Ketinggian, ruang sempit, keramaian"
                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                        Riwayat Trauma Masa Lalu (Opsional)
                    </label>
                    <textarea name="riwayat_trauma" rows="2"
                        placeholder="Jika ada kejadian masa lalu yang membekas..."
                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('riwayat_trauma') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                        Riwayat Kesehatan Medis (Opsional)
                    </label>
                    <textarea name="riwayat_kesehatan" rows="2"
                        placeholder="Penyakit kronis, operasi, atau obat yang rutin dikonsumsi..."
                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('riwayat_kesehatan') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- KARTU 3: GAMBARAN RELASI INTERPERSONAL --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-5">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center gap-2">
            <h3 class="text-base font-bold text-purple-deep">Gambaran Relasi Interpersonal</h3>
        </div>

        <div class="space-y-4">
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                        Relasi dengan Ayah (Opsional)
                    </label>
                    <textarea name="relasi_ayah" rows="2"
                        placeholder="Contoh: Dekat dan komunikatif / Berjarak..."
                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('relasi_ayah') border-red-400 bg-red-50/20 @enderror">{{ old('relasi_ayah') }}</textarea>
                    @error('relasi_ayah') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                        Relasi dengan Ibu (Opsional)
                    </label>
                    <textarea name="relasi_ibu" rows="2"
                        placeholder="Contoh: Sangat dekat, sering curhat / Cukup kaku..."
                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('relasi_ibu') border-red-400 bg-red-50/20 @enderror">{{ old('relasi_ibu') }}</textarea>
                    @error('relasi_ibu') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Relasi Saudara (Opsional)</label>
                    <input type="text" name="relasi_saudara" value="{{ old('relasi_saudara') }}" placeholder="Harmonis / Kurang dekat" class="w-full px-4 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Relasi Pasangan (Opsional)</label>
                    <input type="text" name="relasi_pasangan" value="{{ old('relasi_pasangan') }}" placeholder="Baik / Mengalami konflik" class="w-full px-4 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Relasi Anak (Opsional)</label>
                    <input type="text" name="relasi_anak" value="{{ old('relasi_anak') }}" placeholder="Jika sudah memiliki anak" class="w-full px-4 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
            </div>
        </div>
    </div>

    {{-- KARTU 4: INFORMASI TAMBAHAN & PREFERENSI --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-5">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center gap-2">
            <h3 class="text-base font-bold text-purple-deep">Informasi Tambahan & Preferensi</h3>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                    Pernah Mengikuti Konseling Sebelumnya? (Opsional)
                </label>
                <select name="pernah_konseling"
                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('pernah_konseling') border-red-400 bg-red-50/20 @enderror">
                    <option value="">-- Pilih --</option>
                    <option value="Tidak" {{ old('pernah_konseling') === 'Tidak' ? 'selected' : '' }}>Tidak, baru pertama kali</option>
                    <option value="Ya" {{ old('pernah_konseling') === 'Ya' ? 'selected' : '' }}>Ya, pernah sebelumnya</option>
                </select>
                @error('pernah_konseling') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                    Nama Konselor Lama (Jika pernah)
                </label>
                <input type="text" name="nama_konselor" value="{{ old('nama_konselor') }}"
                    placeholder="Nama konselor / lembaga sebelumnya"
                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                    Kontak Darurat (Nama, Relasi, No. Telp) (Opsional)
                </label>
                <input type="text" name="kontak_darurat" value="{{ old('kontak_darurat') }}"
                    placeholder="Contoh: Ibu Rina (Ibu Kandung) - 08123456789"
                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('kontak_darurat') border-red-400 bg-red-50/20 @enderror">
                @error('kontak_darurat') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                    Sumber Informasi Mengenai UC PSC (Opsional)
                </label>
                <select name="sumber_info"
                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('sumber_info') border-red-400 bg-red-50/20 @enderror">
                    <option value="">-- Pilih Sumber Info --</option>
                    @foreach(['Instagram / Media Sosial UC PSC', 'Website Universitas Ciputra', 'Rujukan / Rekomendasi Teman atau Kerabat', 'Dosen / Civitas Akademika UC', 'Lainnya'] as $src)
                        <option value="{{ $src }}" {{ old('sumber_info') === $src ? 'selected' : '' }}>{{ $src }}</option>
                    @endforeach
                </select>
                @error('sumber_info') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                    Preferensi Proses Konseling (Opsional)
                </label>
                <select name="preferensi_konseling"
                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep @error('preferensi_konseling') border-red-400 bg-red-50/20 @enderror">
                    <option value="">-- Pilih Preferensi --</option>
                    <option value="Offline di UCPSC" {{ old('preferensi_konseling') === 'Offline di UCPSC' ? 'selected' : '' }}>Offline (Tatap Muka di UC PSC CitraLand)</option>
                    <option value="Online melalui Zoom" {{ old('preferensi_konseling') === 'Online melalui Zoom' ? 'selected' : '' }}>Online (Melalui Zoom Meeting)</option>
                </select>
                @error('preferensi_konseling') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

</div>
