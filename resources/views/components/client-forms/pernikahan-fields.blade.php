{{-- FORMULIR RIWAYAT HIDUP KONSELING - PERNIKAHAN --}}
<div class="space-y-6">

    {{-- KARTU: LEMBAR PERSETUJUAN (INFORMED CONSENT) --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-4">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center gap-2">
            <h3 class="text-base font-semibold text-black">Lembar Persetujuan (Informed Consent) <span class="text-red-500">*</span></h3>
        </div>

        <div>
            <p class="text-sm text-[#6B5B85] leading-relaxed mb-3">
                Dengan menyetujui pernyataan ini, saya menyatakan bahwa seluruh data yang diisikan adalah benar dan saya menyetujui pelaksanaan layanan konseling pernikahan di UC Psychological Service Center.
            </p>

            <label class="inline-flex items-center gap-2.5 cursor-pointer text-sm font-medium text-black">
                <input type="checkbox" name="consent_agree" value="Setuju" required checked class="w-4 h-4 rounded text-purple-deep focus:ring-purple-deep border-[#D9C2F0]">
                <span>Menyetujui lembar persetujuan layanan konseling pernikahan UC PSC <span class="text-red-500">*</span></span>
            </label>
            @error('consent_agree') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- KARTU 1: DATA DIRI ANDA --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-4">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center gap-2">
            <h3 class="text-base font-semibold text-black">Data Diri Anda (Pendaftar)</h3>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Nama Lengkap Anda <span class="text-red-500">*</span></label>
                <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required placeholder="Nama lengkap Anda..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Jenis Kelamin <span class="text-red-500">*</span></label>
                <select name="jenis_kelamin" required class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                    <option value="">-- Pilih --</option>
                    <option value="Laki-Laki" {{ old('jenis_kelamin') === 'Laki-Laki' ? 'selected' : '' }}>Laki-Laki (Suami)</option>
                    <option value="Perempuan" {{ old('jenis_kelamin') === 'Perempuan' ? 'selected' : '' }}>Perempuan (Istri)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Tempat Lahir <span class="text-red-500">*</span></label>
                <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}" required placeholder="Kota kelahiran..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Tanggal Lahir <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" required class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Urutan Kelahiran (Opsional)</label>
                <input type="text" name="urutan_kelahiran" value="{{ old('urutan_kelahiran') }}" placeholder="Contoh: Anak ke 1 dari 3 bersaudara" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Alamat Tempat Tinggal Saat Ini <span class="text-red-500">*</span></label>
                <textarea name="alamat" rows="2" required placeholder="Alamat lengkap domisili saat ini..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('alamat') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">No. Telp / WhatsApp Aktif <span class="text-red-500">*</span></label>
                <input type="tel" name="phone" value="{{ old('phone') }}" required placeholder="08xxxxxxxx" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Alamat Email Aktif <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="email@contoh.com" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Agama / Kepercayaan <span class="text-red-500">*</span></label>
                <select name="agama" required class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                    <option value="">-- Pilih Agama --</option>
                    @foreach(['Islam', 'Kristen Protestan', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Lainnya'] as $agm)
                        <option value="{{ $agm }}" {{ old('agama') === $agm ? 'selected' : '' }}>{{ $agm }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Suku Bangsa (Opsional)</label>
                <input type="text" name="suku_bangsa" value="{{ old('suku_bangsa') }}" placeholder="Suku bangsa..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Pendidikan Terakhir <span class="text-red-500">*</span></label>
                <input type="text" name="pendidikan_terakhir" value="{{ old('pendidikan_terakhir') }}" required placeholder="Contoh: S1 Hukum" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Pekerjaan Saat Ini (Opsional)</label>
                <input type="text" name="pekerjaan" value="{{ old('pekerjaan') }}" placeholder="Contoh: Pegawai BUMN" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Pernikahan Saat Ini Adalah Pernikahan Ke- (Opsional)</label>
                <input type="text" name="pernikahan_ke" value="{{ old('pernikahan_ke', '1') }}" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Jumlah Anak yang Dimiliki Saat Ini (Jika ada)</label>
                <input type="text" name="jumlah_anak" value="{{ old('jumlah_anak', '0') }}" placeholder="0" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Alasan Mengikuti Konseling Pernikahan (Opsional)</label>
                <textarea name="alasan_konseling" rows="2" placeholder="Jelaskan alasan mencari konseling pernikahan..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('alasan_konseling') }}</textarea>
            </div>
        </div>
    </div>

    {{-- KARTU 2: DATA DIRI PASANGAN --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-4">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center gap-2">
            <h3 class="text-base font-semibold text-black">Data Diri Pasangan (Suami/Istri) (Opsional)</h3>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Nama Lengkap Pasangan</label>
                <input type="text" name="nama_pasangan" value="{{ old('nama_pasangan') }}" placeholder="Nama lengkap pasangan..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Jenis Kelamin Pasangan</label>
                <select name="jenis_kelamin_pasangan" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                    <option value="">-- Pilih --</option>
                    <option value="Laki-Laki" {{ old('jenis_kelamin_pasangan') === 'Laki-Laki' ? 'selected' : '' }}>Laki-Laki (Suami)</option>
                    <option value="Perempuan" {{ old('jenis_kelamin_pasangan') === 'Perempuan' ? 'selected' : '' }}>Perempuan (Istri)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Tempat Lahir Pasangan</label>
                <input type="text" name="tempat_lahir_pasangan" value="{{ old('tempat_lahir_pasangan') }}" placeholder="Kota kelahiran pasangan..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Tanggal Lahir Pasangan</label>
                <input type="date" name="tanggal_lahir_pasangan" value="{{ old('tanggal_lahir_pasangan') }}" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Urutan Kelahiran Pasangan</label>
                <input type="text" name="urutan_kelahiran_pasangan" value="{{ old('urutan_kelahiran_pasangan') }}" placeholder="Contoh: Anak ke 2 dari 3 bersaudara" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Alamat Tempat Tinggal Pasangan</label>
                <textarea name="alamat_pasangan" rows="2" placeholder="Alamat domisili pasangan..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('alamat_pasangan') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">No. Telp / WhatsApp Pasangan</label>
                <input type="tel" name="phone_pasangan" value="{{ old('phone_pasangan') }}" placeholder="08xxxxxxxx" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Alamat Email Pasangan</label>
                <input type="email" name="email_pasangan" value="{{ old('email_pasangan') }}" placeholder="email.pasangan@contoh.com" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Agama Pasangan</label>
                <select name="agama_pasangan" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                    <option value="">-- Pilih Agama --</option>
                    @foreach(['Islam', 'Kristen Protestan', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Lainnya'] as $agm)
                        <option value="{{ $agm }}" {{ old('agama_pasangan') === $agm ? 'selected' : '' }}>{{ $agm }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Suku Bangsa Pasangan</label>
                <input type="text" name="suku_bangsa_pasangan" value="{{ old('suku_bangsa_pasangan') }}" placeholder="Suku bangsa..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Pendidikan Terakhir Pasangan</label>
                <input type="text" name="pendidikan_terakhir_pasangan" value="{{ old('pendidikan_terakhir_pasangan') }}" placeholder="Contoh: S1 Psikologi" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Pekerjaan Pasangan</label>
                <input type="text" name="pekerjaan_pasangan" value="{{ old('pekerjaan_pasangan') }}" placeholder="Contoh: Wiraswasta" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Bagi Pasangan, Pernikahan Ini Adalah Ke-</label>
                <input type="text" name="pernikahan_pasangan_ke" value="{{ old('pernikahan_pasangan_ke', '1') }}" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Jumlah Anak yang Dimiliki Pasangan</label>
                <input type="text" name="jumlah_anak_pasangan" value="{{ old('jumlah_anak_pasangan', '0') }}" placeholder="0" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
        </div>
    </div>

    {{-- KARTU 3: DATA PERNIKAHAN & DINAMIKA HUBUNGAN --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-4">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center gap-2">
            <h3 class="text-base font-semibold text-black">Data Pernikahan & Dinamika Hubungan (Opsional)</h3>
        </div>

        <div class="grid sm:grid-cols-2 md:grid-cols-4 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Tempat & Tanggal Pernikahan</label>
                <input type="text" name="tempat_tanggal_pernikahan" value="{{ old('tempat_tanggal_pernikahan') }}" placeholder="Surabaya, 20 Oktober 2020" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Usia Pernikahan Saat Ini</label>
                <input type="text" name="lama_pernikahan" value="{{ old('lama_pernikahan') }}" placeholder="Contoh: 4 Tahun" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Lama Berkenalan Sebelum Nikah</label>
                <input type="text" name="lama_berkenalan" value="{{ old('lama_berkenalan') }}" placeholder="Contoh: 2 Tahun" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Lama Berpacaran</label>
                <input type="text" name="lama_berpacaran" value="{{ old('lama_berpacaran') }}" placeholder="Contoh: 1.5 Tahun" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Keluhan Utama / Masalah Pernikahan yang Dihadapi</label>
                <textarea name="keluhan_utama" rows="3" placeholder="Ceritakan permasalahan utama yang terjadi antara Anda dan pasangan..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('keluhan_utama') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Apakah Masih Ada Kemungkinan untuk Diperbaiki Menurut Anda?</label>
                <textarea name="kemungkinan_perbaikan" rows="2" placeholder="Pandangan Anda mengenai harapan dan komitmen perbaikan hubungan..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('kemungkinan_perbaikan') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Hal yang Ingin Ditingkatkan dalam Pernikahan</label>
                <textarea name="hal_ingin_ditingkatkan" rows="2" placeholder="Aspek hubungan yang ingin diperbaiki melalui proses konseling..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('hal_ingin_ditingkatkan') }}</textarea>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Kelebihan Peran Anda bagi Pasangan</label>
                    <textarea name="kelebihan_peran" rows="2" placeholder="Kontribusi positif atau hal baik yang Anda berikan..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('kelebihan_peran') }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Kekurangan Peran Anda bagi Pasangan</label>
                    <textarea name="kekurangan_peran" rows="2" placeholder="Sikap atau kebiasaan diri yang perlu diperbaiki..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('kekurangan_peran') }}</textarea>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Riwayat Trauma Masa Lalu</label>
                    <textarea name="riwayat_trauma" rows="2" placeholder="Kejadian masa lalu yang membekas..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('riwayat_trauma') }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Riwayat Kesehatan Medis</label>
                    <textarea name="riwayat_kesehatan" rows="2" placeholder="Penyakit kronis atau riwayat operasi..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('riwayat_kesehatan') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- KARTU 4: DATA ANAK --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-4">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center justify-between">
            <h3 class="text-base font-semibold text-black">Data Anak (1 s.d. 3)</h3>
            <span class="text-xs text-slate-500 font-normal">Jika sudah memiliki anak</span>
        </div>

        <div class="space-y-4">
            @for($i = 1; $i <= 3; $i++)
            <div class="p-3.5 bg-[#FAF9FD] rounded-xl border border-[#EDE1FA]/80 space-y-3">
                <span class="text-xs font-bold text-[#5B4A73]">Anak {{ $i }}</span>
                <div class="grid sm:grid-cols-2 md:grid-cols-5 gap-3">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-[#5B4A73] mb-1">Nama Lengkap</label>
                        <input type="text" name="anak_{{ $i }}_nama" value="{{ old('anak_' . $i . '_nama') }}" placeholder="Nama anak" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#5B4A73] mb-1">Jenis Kelamin</label>
                        <select name="anak_{{ $i }}_jenis_kelamin" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                            <option value="">--</option>
                            <option value="Laki-Laki" {{ old('anak_' . $i . '_jenis_kelamin') === 'Laki-Laki' ? 'selected' : '' }}>Laki-Laki</option>
                            <option value="Perempuan" {{ old('anak_' . $i . '_jenis_kelamin') === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#5B4A73] mb-1">Usia</label>
                        <input type="text" name="anak_{{ $i }}_usia" value="{{ old('anak_' . $i . '_usia') }}" placeholder="Contoh: 3 Tahun" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#5B4A73] mb-1">Pendidikan</label>
                        <input type="text" name="anak_{{ $i }}_pendidikan" value="{{ old('anak_' . $i . '_pendidikan') }}" placeholder="TK / SD / dll" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                </div>
            </div>
            @endfor
        </div>
    </div>

    {{-- KARTU 5: LAIN-LAIN & PREFERENSI --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-4">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center gap-2">
            <h3 class="text-base font-semibold text-black">Lain-Lain & Preferensi</h3>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Pernah Konseling Pernikahan Sebelumnya? (Opsional)</label>
                <select name="pernah_konseling" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                    <option value="Tidak" {{ old('pernah_konseling', 'Tidak') === 'Tidak' ? 'selected' : '' }}>Tidak, pertama kali</option>
                    <option value="Ya" {{ old('pernah_konseling') === 'Ya' ? 'selected' : '' }}>Ya, pernah sebelumnya</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Nama Konselor Lama (Opsional)</label>
                <input type="text" name="nama_konselor" value="{{ old('nama_konselor') }}" placeholder="Konselor / psikolog sebelumnya" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Kontak Darurat (Nama, Relasi, No. Telp) (Opsional)</label>
                <input type="text" name="kontak_darurat" value="{{ old('kontak_darurat') }}" placeholder="Contoh: Budi Santoso (Adik) - 0812345678" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Sumber Informasi Mengenai UC PSC (Opsional)</label>
                <select name="sumber_info" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                    <option value="">-- Pilih Sumber Info --</option>
                    <option value="Instagram / Media Sosial UC PSC" {{ old('sumber_info') === 'Instagram / Media Sosial UC PSC' ? 'selected' : '' }}>Instagram / Media Sosial UC PSC</option>
                    <option value="Website UC" {{ old('sumber_info') === 'Website UC' ? 'selected' : '' }}>Website UC</option>
                    <option value="Rekomendasi Kerabat" {{ old('sumber_info') === 'Rekomendasi Kerabat' ? 'selected' : '' }}>Rekomendasi Kerabat / Teman</option>
                    <option value="Lainnya" {{ old('sumber_info') === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Preferensi Proses Konseling (Opsional)</label>
                <select name="preferensi_konseling" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                    <option value="">-- Pilih Preferensi --</option>
                    <option value="Offline di UCPSC" {{ old('preferensi_konseling', 'Offline di UCPSC') === 'Offline di UCPSC' ? 'selected' : '' }}>Offline (Tatap Muka di UC PSC)</option>
                    <option value="Online melalui Zoom" {{ old('preferensi_konseling') === 'Online melalui Zoom' ? 'selected' : '' }}>Online (Melalui Zoom Meeting)</option>
                </select>
            </div>
        </div>
    </div>

</div>
