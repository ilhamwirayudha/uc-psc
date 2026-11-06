@php
if (!function_exists('buildBookingDataPayload')) {
    function buildBookingDataPayload($booking) {
        $jamDibuat = $booking->created_at ? $booking->created_at->format('H:i:s') . ' WIB' : '00:00:00 WIB';
        $jamSesi = null;
        if ($booking->session_start && $booking->session_end) {
            $jamSesi = substr($booking->session_start, 0, 5) . ' - ' . substr($booking->session_end, 0, 5) . ' WIB';
        } elseif ($booking->session_start) {
            $jamSesi = substr($booking->session_start, 0, 5) . ' WIB';
        } elseif ($booking->notes && preg_match('/(?:Waktu|Jam Pelaksanaan)\s*:\s*([^\n\r\]]+)/i', $booking->notes, $matches)) {
            $jamSesi = trim($matches[1]);
        }

        $clientData = $booking->client ? [
            'id' => $booking->client->id,
            'name' => $booking->client->name,
            'jenis' => $booking->client->jenis ?? 'individu',
            'pic_name' => $booking->client->pic_name,
            'phone' => $booking->client->phone ?? '-',
            'email' => $booking->client->email ?? '-',
            'gender' => $booking->client->gender_label ?? '-',
            'dob' => $booking->client->dob ? $booking->client->dob->locale('id')->isoFormat('D MMMM Y') : '-',
            'birth_place' => $booking->client->birth_place ?? '-',
            'religion' => $booking->client->religion ?? '-',
            'occupation' => $booking->client->occupation ?? '-',
            'education' => $booking->client->education_label ?? '-',
            'marital_status' => $booking->client->marital_status ?? '-',
            'address' => $booking->client->address ?? '-',
            'status' => $booking->client->status ?? 'unassigned',
            'status_label' => ucfirst(str_replace('_', ' ', $booking->client->status ?? 'unassigned')),
            'service_type' => $booking->client->service_type ?? '-',
            'counseling_type' => $booking->client->counseling_type ?? '-',
            'notes' => $booking->client->notes ?? '',
            'assigned_staff' => $booking->client->assignedStaff ? [
                'name' => $booking->client->assignedStaff->name,
                'email' => $booking->client->assignedStaff->email,
            ] : null,
            'active_counselor' => $booking->client->pairings->where('status', 'active')->first()?->counselor ? [
                'name' => $booking->client->pairings->where('status', 'active')->first()->counselor->name,
                'specialization' => $booking->client->pairings->where('status', 'active')->first()->counselor->specialization,
            ] : null,
            'participants' => $booking->client->clientParticipants->pluck('nama_peserta')->values()->toArray(),
            'bookings' => $booking->client->bookings->map(fn($b) => [
                'id' => $b->id,
                'kategori' => $b->kategori,
                'status' => $b->status,
                'status_label' => ucfirst(str_replace('_', ' ', $b->status)),
                'tanggal_dibuat' => $b->tanggal_booking_dibuat->format('d M Y'),
                'tanggal_dijadwalkan' => $b->tanggal_dijadwalkan ? $b->tanggal_dijadwalkan->format('d M Y') : 'Belum Dijadwalkan',
                'counselor_name' => $b->counselor?->name,
                'staff_penguji_name' => $b->staffPenguji?->name,
                'notes' => $b->notes,
                'update_url' => route('bookings.update', $b),
            ])->values()->toArray(),
            'show_url' => route('clients.show', $booking->client),
            'edit_url' => route('clients.edit', $booking->client),
        ] : null;

        $startTime = $booking->session_start ? substr($booking->session_start, 0, 5) : '';
        $endTime = $booking->session_end ? substr($booking->session_end, 0, 5) : '';
        $sessionType = $booking->session_type ?? '';
        $location = $booking->location ?? '';

        if (empty($startTime) && $booking->notes && preg_match('/(?:Waktu|Jam Pelaksanaan)\s*:\s*(\d{2}:\d{2})(?:\s*-\s*(\d{2}:\d{2}))?/i', $booking->notes, $tm)) {
            $startTime = $tm[1] ?? '';
            $endTime = $tm[2] ?? '';
        }
        if (empty($sessionType) && $booking->notes && preg_match('/(?:Tipe Sesi|Metode Sesi)\s*:\s*([^\n\r\|\]]+)/i', $booking->notes, $tm)) {
            $st = strtolower(trim($tm[1]));
            $sessionType = ($st === 'online') ? 'online' : 'tatap_muka';
        }
        if (empty($location) && $booking->notes && preg_match('/(?:Lokasi|Ruangan)\s*:\s*([^\n\r\|\]]+)/i', $booking->notes, $tm)) {
            $location = trim($tm[1]);
        }

        return [
            'id' => $booking->id,
            'kategori' => $booking->kategori,
            'service_package_name' => $booking->service_package_name,
            'service_tier' => $booking->service_tier,
            'service_package_key' => $booking->service_package_key,
            'price' => $booking->price,
            'price_formatted' => $booking->price ? 'Rp ' . number_format($booking->price, 0, ',', '.') : '-',
            'client_form_id' => $booking->client_form_id,
            'status' => $booking->status,
            'status_label' => ucfirst(str_replace('_', ' ', $booking->status)),
            'tanggal_booking_dibuat' => $booking->tanggal_booking_dibuat->format('Y-m-d'),
            'tanggal_booking_dibuat_formatted' => $booking->tanggal_booking_dibuat->locale('id')->isoFormat('D MMMM Y'),
            'jam_dibuat' => $jamDibuat,
            'tanggal_dijadwalkan' => $booking->tanggal_dijadwalkan ? $booking->tanggal_dijadwalkan->format('Y-m-d') : null,
            'tanggal_dijadwalkan_formatted' => $booking->tanggal_dijadwalkan ? $booking->tanggal_dijadwalkan->locale('id')->isoFormat('dddd, D MMMM Y') : 'Belum Dijadwalkan',
            'jam_sesi' => $jamSesi,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'session_type' => $sessionType,
            'location' => $location,
            'follow_up_of_id' => $booking->follow_up_of_booking_id,
            'client' => $clientData,
            'counselor' => $booking->counselor ? [
                'id' => $booking->counselor->id,
                'name' => $booking->counselor->name,
                'specialization' => $booking->counselor->specialization ?? '-',
                'phone' => $booking->counselor->phone ?? '-',
                'email' => $booking->counselor->email ?? '-',
            ] : null,
            'staff_penguji' => $booking->staffPenguji ? [
                'id' => $booking->staffPenguji->id,
                'name' => $booking->staffPenguji->name,
                'email' => $booking->staffPenguji->email ?? '-',
            ] : null,
            'staff_koreksi' => $booking->staffKoreksi ? [
                'id' => $booking->staffKoreksi->id,
                'name' => $booking->staffKoreksi->name,
                'email' => $booking->staffKoreksi->email ?? '-',
            ] : null,
            'staff_pelapor' => $booking->staffPelapor ? [
                'id' => $booking->staffPelapor->id,
                'name' => $booking->staffPelapor->name,
                'email' => $booking->staffPelapor->email ?? '-',
            ] : null,
            'payment' => $booking->paymentTransaction ? [
                'id' => $booking->paymentTransaction->id,
                'amount_formatted' => 'Rp ' . number_format($booking->paymentTransaction->jumlah, 0, ',', '.'),
                'status' => $booking->paymentTransaction->status,
                'method' => $booking->paymentTransaction->metode_pembayaran ?? '-',
            ] : null,
            'participants' => $booking->participants->pluck('nama_peserta')->values()->toArray(),
            'follow_ups' => $booking->followUps->map(fn($fu) => [
                'id' => $fu->id,
                'date_formatted' => $fu->tanggal_booking_dibuat->locale('id')->isoFormat('D MMMM Y'),
                'status' => $fu->status,
                'status_label' => ucfirst(str_replace('_', ' ', $fu->status)),
            ])->values()->toArray(),
            'notes' => $booking->notes ?? '',
            'update_url' => route('bookings.update', $booking),
            'destroy_url' => route('bookings.destroy', $booking),
            'follow_up_url' => route('bookings.follow-up', $booking),
        ];
    }
}
@endphp
