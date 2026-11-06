{{-- Alpine.js Booking Manager Script --}}
<script>
function bookingManager() {
    return {
        detailModalOpen: false,
        editingCard: null,
        initialForm: null,
        get isEditing() {
            return Boolean(this.editingCard);
        },
        selectedBooking: null,
        editForm: {
            id: null,
            client_id: '',
            client_jenis: 'individu',
            kategori: 'konseling',
            service_type: '',
            service_metode: '',
            service_format: '',
            service_level: '',
            service_klien: '',
            service_kategori: '',
            counseling_type: '',
            tanggal_booking_dibuat: '',
            tanggal_dijadwalkan: '',
            start_time: '',
            end_time: '',
            session_type: 'tatap_muka',
            location: '',
            status: 'baru',
            counselor_id: '',
            staff_penguji_id: '',
            staff_koreksi_id: '',
            staff_pelapor_id: '',
            participants: [''],
            notes: '',
            action_url: '',
        },

        hasCardChanged(card) {
            if (!this.initialForm || !card) return false;
            if (card === 'layanan') {
                return this.editForm.kategori !== this.initialForm.kategori ||
                       this.editForm.service_type !== this.initialForm.service_type ||
                       this.editForm.service_metode !== this.initialForm.service_metode ||
                       this.editForm.service_format !== this.initialForm.service_format ||
                       this.editForm.service_level !== this.initialForm.service_level ||
                       this.editForm.service_klien !== this.initialForm.service_klien ||
                       this.editForm.service_kategori !== this.initialForm.service_kategori ||
                       this.editForm.counseling_type !== this.initialForm.counseling_type;
            }
            if (card === 'penugasan') {
                return String(this.editForm.counselor_id || '') !== String(this.initialForm.counselor_id || '') ||
                       String(this.editForm.staff_penguji_id || '') !== String(this.initialForm.staff_penguji_id || '') ||
                       String(this.editForm.staff_koreksi_id || '') !== String(this.initialForm.staff_koreksi_id || '') ||
                       String(this.editForm.staff_pelapor_id || '') !== String(this.initialForm.staff_pelapor_id || '');
            }
            if (card === 'jadwal') {
                return (this.editForm.tanggal_dijadwalkan || '') !== (this.initialForm.tanggal_dijadwalkan || '') ||
                       (this.editForm.start_time || '') !== (this.initialForm.start_time || '') ||
                       (this.editForm.end_time || '') !== (this.initialForm.end_time || '') ||
                       (this.editForm.session_type || '') !== (this.initialForm.session_type || '') ||
                       (this.editForm.location || '').trim() !== (this.initialForm.location || '').trim();
            }
            if (card === 'peserta') {
                return JSON.stringify(this.editForm.participants || []) !== JSON.stringify(this.initialForm.participants || []);
            }
            if (card === 'status') {
                return (this.editForm.status || '') !== (this.initialForm.status || '') ||
                       (this.editForm.notes || '').trim() !== (this.initialForm.notes || '').trim();
            }
            return false;
        },

        init() {
            // Optional URL param support (?detail=123)
            const urlParams = new URLSearchParams(window.location.search);
            const detailId = urlParams.get('detail');
            if (detailId) {
                const triggerBtn = document.querySelector(`button[data-booking-id="${detailId}"]`);
                if (triggerBtn) triggerBtn.click();
            }
        },

        openDetail(booking) {
            this.selectedBooking = booking;
            this.editingCard = null;
            this.populateEditForm(booking);
            this.detailModalOpen = true;
        },

        startEdit(cardName = 'layanan') {
            this.editingCard = cardName;
            this.$nextTick(() => {
                let elId = 'card-' + cardName;
                if (cardName === 'layanan') elId = 'card-pilihan-layanan';
                const el = document.getElementById(elId);
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            });
        },

        cancelEdit(cardName = null) {
            this.editingCard = null;
            if (this.selectedBooking) {
                this.populateEditForm(this.selectedBooking);
            }
        },

        showConfirmCloseModal: false,

        get editingCardTitle() {
            switch (this.editingCard) {
                case 'layanan': return 'Pilihan Layanan';
                case 'penugasan': return 'Penugasan Psikolog / Staff Pelaksana';
                case 'jadwal': return 'Jadwal, Waktu & Lokasi Sesi';
                case 'peserta': return 'Daftar Karyawan / Peserta Asesmen';
                case 'status': return 'Status & Catatan Sesi Booking';
                default: return 'Booking';
            }
        },

        handleCloseAttempt() {
            if (!this.detailModalOpen) return;
            if (this.showConfirmCloseModal) {
                this.showConfirmCloseModal = false;
                return;
            }
            if (this.editingCard) {
                this.showConfirmCloseModal = true;
            } else {
                this.closeDetailModal();
            }
        },

        confirmDiscardAndClose() {
            this.showConfirmCloseModal = false;
            this.cancelEdit();
            this.detailModalOpen = false;
        },

        cancelCloseAttempt() {
            this.showConfirmCloseModal = false;
        },

        closeDetailModal() {
            this.detailModalOpen = false;
            this.editingCard = null;
            this.showConfirmCloseModal = false;
        },

        openEdit(booking) {
            this.selectedBooking = booking;
            this.populateEditForm(booking);
            this.editingCard = 'layanan';
            this.detailModalOpen = true;
        },

        populateEditForm(booking) {
            this.editForm.id = booking.id;
            this.editForm.client_id = booking.client?.id || '';
            this.editForm.client_jenis = booking.client?.jenis || 'individu';
            this.editForm.kategori = booking.kategori || 'konseling';

            const rawCT = booking.client?.counseling_type || booking.counseling_type || '';
            const parsed = this.parseCounselingType(rawCT, this.editForm.kategori);
            this.editForm.service_type = parsed.serviceType;
            this.editForm.service_metode = parsed.serviceMetode;
            this.editForm.service_format = parsed.serviceFormat;
            this.editForm.service_level = parsed.serviceLevel;
            this.editForm.service_klien = parsed.serviceKlien;
            this.editForm.service_kategori = parsed.serviceKategori;
            this.editForm.counseling_type = parsed.counselingType;

            this.editForm.tanggal_booking_dibuat = booking.tanggal_booking_dibuat || '';
            this.editForm.tanggal_dijadwalkan = booking.tanggal_dijadwalkan || '';
            this.editForm.start_time = booking.start_time || '';
            this.editForm.end_time = booking.end_time || '';
            this.editForm.session_type = booking.session_type || 'tatap_muka';
            this.editForm.location = booking.location || '';
            this.editForm.status = booking.status || 'baru';
            this.editForm.counselor_id = booking.counselor ? booking.counselor.id : '';
            this.editForm.staff_penguji_id = booking.staff_penguji ? booking.staff_penguji.id : '';
            this.editForm.staff_koreksi_id = booking.staff_koreksi ? booking.staff_koreksi.id : '';
            this.editForm.staff_pelapor_id = booking.staff_pelapor ? booking.staff_pelapor.id : '';
            this.editForm.participants = (booking.participants && booking.participants.length > 0) ? [...booking.participants] : [''];
            this.editForm.notes = booking.notes || '';
            this.editForm.action_url = booking.update_url;
            this.initialForm = JSON.parse(JSON.stringify(this.editForm));
        },

        switchEditCategory(targetCategory) {
            if (this.editForm.kategori === targetCategory) return;
            this.editForm.kategori = targetCategory;
            if (targetCategory === 'konseling') {
                this.editForm.service_type = 'konseling_reguler';
                this.editForm.service_metode = 'Offline';
                this.editForm.service_klien = 'WNI';
                this.editForm.service_kategori = 'Umum';
                this.editForm.service_format = '';
                this.editForm.service_level = '';
                this.editForm.session_type = 'tatap_muka';
            } else {
                this.editForm.service_type = 'psikotes_industri';
                this.editForm.service_metode = 'Offline';
                this.editForm.service_format = 'Short';
                this.editForm.service_level = 'Staf';
                this.editForm.service_kategori = 'Umum';
                this.editForm.service_klien = '';
                this.editForm.session_type = 'tatap_muka';
            }
            this.updateCounselingType();
        },

        parseCounselingType(raw, kategori) {
            let res = {
                serviceType: '',
                serviceMetode: '',
                serviceFormat: '',
                serviceLevel: '',
                serviceKlien: '',
                serviceKategori: '',
                counselingType: ''
            };
            if (!raw || !raw.includes(' - ')) {
                return res;
            }

            const parts = raw.split(' - ').map(s => s.trim());
            if (kategori === 'konseling' || parts[0]?.toLowerCase().includes('konseling')) {
                res.serviceType = (parts[0] && parts[0].toLowerCase().includes('pro')) ? 'konseling_pro' : 'konseling_reguler';
                res.serviceMetode = (parts[1] && ['Online', 'Offline'].includes(parts[1])) ? parts[1] : 'Offline';
                res.serviceKlien = (parts[2] && ['WNI', 'WNA'].includes(parts[2])) ? parts[2] : 'WNI';
                res.serviceKategori = (parts[3] && ['Umum', 'Kerja Sama'].includes(parts[3])) ? parts[3] : 'Umum';
                const label = (res.serviceType === 'konseling_pro') ? 'Konseling Pro' : 'Konseling Reguler';
                res.counselingType = `${label} - ${res.serviceMetode} - ${res.serviceKlien} - ${res.serviceKategori}`;
            } else {
                const p0 = (parts[0] || '').toLowerCase();
                if (p0.includes('bakat minat')) {
                    res.serviceType = 'psikotes_bakat_minat';
                    res.serviceMetode = parts[1] || 'Offline';
                    res.serviceFormat = parts[2] || 'Short';
                    res.serviceKategori = parts[3] || 'Umum';
                    res.counselingType = `Psikotes Non-Industri Bakat Minat - ${res.serviceMetode} - ${res.serviceFormat} - ${res.serviceKategori}`;
                } else if (p0.includes('khusus')) {
                    res.serviceType = 'psikotes_khusus';
                    res.serviceMetode = parts[1] || 'Offline';
                    res.serviceFormat = parts[2] || 'Konseling Psikotes';
                    res.serviceKategori = parts[3] || 'SSCT - EPPS';
                    res.counselingType = `Psikotes Non-Industri Khusus - ${res.serviceMetode} - ${res.serviceFormat} - ${res.serviceKategori}`;
                } else {
                    res.serviceType = 'psikotes_industri';
                    res.serviceMetode = parts[1] || 'Offline';
                    res.serviceFormat = parts[2] || 'Short';
                    res.serviceLevel = (res.serviceFormat === 'Long') ? (parts[3] || 'Supervisor') : 'Staf';
                    res.serviceKategori = parts[4] || parts[3] || 'Umum';
                    res.counselingType = `Psikotes Industri - ${res.serviceMetode} - ${res.serviceFormat} - ${res.serviceLevel} - ${res.serviceKategori}`;
                }
            }
            return res;
        },

        updateCounselingType() {
            let parts = [];
            if (this.editForm.kategori === 'konseling') {
                if (!this.editForm.service_type || !this.editForm.service_metode || !this.editForm.service_klien || !this.editForm.service_kategori) {
                    this.editForm.counseling_type = '';
                    return;
                }
                const typeLabel = (this.editForm.service_type === 'konseling_pro') ? 'Konseling Pro' : 'Konseling Reguler';
                parts.push(typeLabel);
                parts.push(this.editForm.service_metode);
                parts.push(this.editForm.service_klien);
                parts.push(this.editForm.service_kategori);
            } else if (this.editForm.kategori === 'psikotes') {
                if (this.editForm.service_type === 'psikotes_industri') {
                    if (!this.editForm.service_type || !this.editForm.service_metode || !this.editForm.service_format || !this.editForm.service_level || !this.editForm.service_kategori) {
                        this.editForm.counseling_type = '';
                        return;
                    }
                    parts.push('Psikotes Industri');
                    parts.push(this.editForm.service_metode);
                    parts.push(this.editForm.service_format);
                    parts.push(this.editForm.service_level);
                    parts.push(this.editForm.service_kategori);
                } else if (this.editForm.service_type === 'psikotes_bakat_minat') {
                    if (!this.editForm.service_type || !this.editForm.service_metode || !this.editForm.service_format || !this.editForm.service_kategori) {
                        this.editForm.counseling_type = '';
                        return;
                    }
                    parts.push('Psikotes Non-Industri Bakat Minat');
                    parts.push(this.editForm.service_metode);
                    parts.push(this.editForm.service_format);
                    parts.push(this.editForm.service_kategori);
                } else if (this.editForm.service_type === 'psikotes_khusus') {
                    if (!this.editForm.service_type || !this.editForm.service_metode || !this.editForm.service_format || !this.editForm.service_kategori) {
                        this.editForm.counseling_type = '';
                        return;
                    }
                    parts.push('Psikotes Non-Industri Khusus');
                    parts.push(this.editForm.service_metode);
                    parts.push(this.editForm.service_format);
                    parts.push(this.editForm.service_kategori);
                } else {
                    this.editForm.counseling_type = '';
                    return;
                }
            } else {
                this.editForm.counseling_type = '';
                return;
            }
            this.editForm.counseling_type = parts.join(' - ');
        },

        onKonselingTypeChange(newType) {
            this.editForm.service_type = newType;
            this.editForm.service_metode = '';
            this.editForm.service_klien = '';
            this.editForm.service_kategori = '';
            this.updateCounselingType();
        },

        onKonselingMetodeChange(newMetode) {
            this.editForm.service_metode = newMetode;
            if (newMetode === 'Offline' || newMetode === 'Tatap Muka (Offline)') this.editForm.session_type = 'tatap_muka';
            else if (newMetode === 'Online') this.editForm.session_type = 'online';
            this.editForm.service_klien = '';
            this.editForm.service_kategori = '';
            this.updateCounselingType();
        },

        onKonselingKlienChange(newKlien) {
            this.editForm.service_klien = newKlien;
            this.editForm.service_kategori = '';
            this.updateCounselingType();
        },

        onKonselingKategoriChange(newKategori) {
            this.editForm.service_kategori = newKategori;
            this.updateCounselingType();
        },

        onPsikotesTypeChange(newType) {
            this.editForm.service_type = newType;
            this.editForm.service_metode = '';
            this.editForm.service_format = '';
            this.editForm.service_level = '';
            this.editForm.service_kategori = '';
            this.updateCounselingType();
        },

        onPsikotesMetodeChange(newMetode) {
            this.editForm.service_metode = newMetode;
            if (newMetode === 'Offline' || newMetode === 'Tatap Muka (Offline)') this.editForm.session_type = 'tatap_muka';
            else if (newMetode === 'Online' || newMetode === 'UCS MNA (Online)') this.editForm.session_type = 'online';
            this.editForm.service_format = '';
            this.editForm.service_level = '';
            this.editForm.service_kategori = '';
            this.updateCounselingType();
        },

        onPsikotesFormatChange(newFormat) {
            this.editForm.service_format = newFormat;
            if (this.editForm.service_type === 'psikotes_industri') {
                if (newFormat === 'Short' && this.editForm.service_level !== 'Staf') {
                    this.editForm.service_level = '';
                }
            } else {
                this.editForm.service_level = '';
            }
            this.editForm.service_kategori = '';
            this.updateCounselingType();
        },

        onPsikotesLevelChange(newLevel) {
            this.editForm.service_level = newLevel;
            this.editForm.service_kategori = '';
            this.updateCounselingType();
        },

        onPsikotesKategoriChange(newKategori) {
            this.editForm.service_kategori = newKategori;
            this.updateCounselingType();
        },

        confirmDeleteBooking() {
            if (confirm('Apakah Anda yakin ingin menghapus data booking ini?')) {
                const form = document.getElementById('delete-booking-form');
                if (form) form.submit();
            }
        },

        addParticipant() {
            this.editForm.participants.push('');
        },

        removeParticipant(index) {
            if (this.editForm.participants.length > 1) {
                this.editForm.participants.splice(index, 1);
            }
        }
    };
}
</script>
