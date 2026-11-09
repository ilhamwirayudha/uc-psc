<?php

namespace App\Http\Controllers;

use App\Models\ClientForm;
use Illuminate\Http\Request;

class ClientFormManagementController extends Controller
{
    /**
     * Display a listing of client form links (List view matching Data Klien & Data Konselor style).
     */
    public function index(Request $request)
    {
        // Count submissions for all 7 forms
        $formTypes = ['anak', 'dewasa', 'pra_nikah', 'pernikahan', 'biography_en', 'non_industri', 'industri'];
        $counts = ClientForm::whereHas('client')
            ->whereIn('form_type', $formTypes)
            ->groupBy('form_type')
            ->selectRaw('form_type, count(*) as total')
            ->pluck('total', 'form_type')
            ->toArray();

        $latestSubmissions = [];
        foreach ($formTypes as $ft) {
            $latestSubmissions[$ft] = ClientForm::whereHas('client')->where('form_type', $ft)->latest()->first();
        }

        // Forms list
        $forms = [
            [
                'id' => 1,
                'key' => 'anak',
                'title' => 'Formulir Riwayat Hidup Konseling - Anak',
                'short_title' => 'Konseling - Anak',
                'description' => 'Khusus anak-anak yang didaftarkan oleh orang tua/wali dengan data riwayat keluarga dan tumbuh kembang.',
                'target_client' => 'Anak & Remaja (Orang Tua/Wali)',
                'url' => route('public.client-form.anak'),
                'icon' => '🧒',
                'badge' => 'Aktif & Siap Digunakan',
                'status' => 'active',
                'status_label' => 'Aktif',
                'fields_count' => '58 Pertanyaan',
                'responses_count' => $counts['anak'] ?? 0,
                'last_submission' => !empty($latestSubmissions['anak']) ? $latestSubmissions['anak']->created_at->diffForHumans() : 'Belum ada respon',
                'created_at' => '25 Sep 2026',
            ],
            [
                'id' => 2,
                'key' => 'dewasa',
                'title' => 'Formulir Riwayat Hidup Konseling - Dewasa',
                'short_title' => 'Konseling - Dewasa',
                'description' => 'Untuk klien dewasa perorangan dengan evaluasi kondisi psikologis, relasi interpersonal, trauma, dan latar belakang.',
                'target_client' => 'Klien Dewasa Umum (Perorangan)',
                'url' => route('public.client-form.dewasa'),
                'icon' => '👤',
                'badge' => 'Aktif & Siap Digunakan',
                'status' => 'active',
                'status_label' => 'Aktif',
                'fields_count' => '33 Pertanyaan',
                'responses_count' => $counts['dewasa'] ?? 0,
                'last_submission' => !empty($latestSubmissions['dewasa']) ? $latestSubmissions['dewasa']->created_at->diffForHumans() : 'Belum ada respon',
                'created_at' => '26 Sep 2026',
            ],
            [
                'id' => 3,
                'key' => 'pra_nikah',
                'title' => 'Formulir Riwayat Hidup Konseling - Pra Nikah',
                'short_title' => 'Konseling - Pra Nikah',
                'description' => 'Evaluasi kesiapan mental, komunikasi, ekspektasi bersama, dan dinamika hubungan sebelum menikah.',
                'target_client' => 'Calon Pasangan Pengantin',
                'url' => route('public.client-form.pra-nikah'),
                'icon' => '💍',
                'badge' => 'Aktif & Siap Digunakan',
                'status' => 'active',
                'status_label' => 'Aktif',
                'fields_count' => '35 Pertanyaan',
                'responses_count' => $counts['pra_nikah'] ?? 0,
                'last_submission' => !empty($latestSubmissions['pra_nikah']) ? $latestSubmissions['pra_nikah']->created_at->diffForHumans() : 'Belum ada respon',
                'created_at' => '26 Sep 2026',
            ],
            [
                'id' => 4,
                'key' => 'pernikahan',
                'title' => 'Formulir Riwayat Hidup Konseling - Pernikahan',
                'short_title' => 'Konseling - Pernikahan',
                'description' => 'Konseling evaluasi dinamika hubungan suami istri, resolusi konflik, dan keharmonisan perkawinan.',
                'target_client' => 'Pasangan Suami Istri',
                'url' => route('public.client-form.pernikahan'),
                'icon' => '❤️',
                'badge' => 'Aktif & Siap Digunakan',
                'status' => 'active',
                'status_label' => 'Aktif',
                'fields_count' => '38 Pertanyaan',
                'responses_count' => $counts['pernikahan'] ?? 0,
                'last_submission' => !empty($latestSubmissions['pernikahan']) ? $latestSubmissions['pernikahan']->created_at->diffForHumans() : 'Belum ada respon',
                'created_at' => '26 Sep 2026',
            ],
            [
                'id' => 5,
                'key' => 'biography_en',
                'title' => 'Biography Form (English)',
                'short_title' => 'Biography Form (English)',
                'description' => 'English biography and intake questionnaire for international clients and non-Indonesian speakers.',
                'target_client' => 'International / Expatriate Clients',
                'url' => route('public.client-form.biography-en'),
                'icon' => '🌐',
                'badge' => 'Aktif & Siap Digunakan',
                'status' => 'active',
                'status_label' => 'Aktif',
                'fields_count' => '32 Questions',
                'responses_count' => $counts['biography_en'] ?? 0,
                'last_submission' => !empty($latestSubmissions['biography_en']) ? $latestSubmissions['biography_en']->created_at->diffForHumans() : 'Belum ada respon',
                'created_at' => '26 Sep 2026',
            ],
            [
                'id' => 6,
                'key' => 'non_industri',
                'title' => 'Formulir Riwayat Hidup - Non-Industri',
                'short_title' => 'Riwayat Hidup - Non-Industri',
                'description' => 'Layanan asesmen umum untuk komunitas, sekolah, penelusuran bakat minat, dan institusi pendidikan.',
                'target_client' => 'Siswa / Pendidikan / Komunitas',
                'url' => route('public.client-form.non-industri'),
                'icon' => '🎓',
                'badge' => 'Aktif & Siap Digunakan',
                'status' => 'active',
                'status_label' => 'Aktif',
                'fields_count' => '35 Pertanyaan',
                'responses_count' => $counts['non_industri'] ?? 0,
                'last_submission' => !empty($latestSubmissions['non_industri']) ? $latestSubmissions['non_industri']->created_at->diffForHumans() : 'Belum ada respon',
                'created_at' => '26 Sep 2026',
            ],
            [
                'id' => 7,
                'key' => 'industri',
                'title' => 'Formulir Riwayat Hidup - Industri',
                'short_title' => 'Riwayat Hidup - Industri',
                'description' => 'Formulir asesmen rekam jejak untuk karyawan perusahaan, korporat, rekrutmen institusi, dan tes stres PSS.',
                'target_client' => 'Karyawan / Rekrutmen Korporat',
                'url' => route('public.client-form.industri'),
                'icon' => '🏢',
                'badge' => 'Aktif & Siap Digunakan',
                'status' => 'active',
                'status_label' => 'Aktif',
                'fields_count' => '42 Pertanyaan',
                'responses_count' => $counts['industri'] ?? 0,
                'last_submission' => !empty($latestSubmissions['industri']) ? $latestSubmissions['industri']->created_at->diffForHumans() : 'Belum ada respon',
                'created_at' => '26 Sep 2026',
            ],
        ];

        // Search filtering
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $forms = array_filter($forms, function ($f) use ($search) {
                return str_contains(strtolower($f['title']), $search) || 
                       str_contains(strtolower($f['description']), $search) ||
                       str_contains(strtolower($f['target_client']), $search);
            });
        }

        // Status filtering
        if ($request->filled('status')) {
            $status = $request->status;
            $forms = array_filter($forms, function ($f) use ($status) {
                return $f['status'] === $status;
            });
        }

        return view('client-forms.client-forms-incoming-table', compact('forms'));
    }

    /**
     * Display the Google Sheets responses spreadsheet for a form category.
     */
    public function results(Request $request)
    {
        // 7 Categories from Real UC PSC Google Forms
        $categories = [
            'anak' => [
                'key' => 'anak',
                'title' => 'Formulir Riwayat Hidup Konseling - Anak',
                'short_title' => 'Konseling - Anak',
                'description' => 'Khusus anak-anak yang didaftarkan oleh orang tua/wali dengan data riwayat keluarga dan tumbuh kembang.',
                'badge' => 'Aktif & Siap Digunakan',
                'badge_color' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'url' => route('public.client-form.anak'),
                'icon' => '🧒',
                'fields_count' => '58 Pertanyaan',
                'is_ready' => true,
            ],
            'dewasa' => [
                'key' => 'dewasa',
                'title' => 'Formulir Riwayat Hidup Konseling - Dewasa',
                'short_title' => 'Konseling - Dewasa',
                'description' => 'Untuk klien dewasa umum dengan evaluasi kondisi psikologis, relasi, trauma, dan pekerjaan.',
                'badge' => 'Aktif & Siap Digunakan',
                'badge_color' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'url' => route('public.client-form.dewasa'),
                'icon' => '👤',
                'fields_count' => '33 Pertanyaan',
                'is_ready' => true,
            ],
            'pra_nikah' => [
                'key' => 'pra_nikah',
                'title' => 'Formulir Riwayat Hidup Konseling - Pra Nikah',
                'short_title' => 'Konseling - Pra Nikah',
                'description' => 'Evaluasi kesiapan mental, komunikasi, dan ekspektasi pasangan sebelum melangkah ke pernikahan.',
                'badge' => 'Aktif & Siap Digunakan',
                'badge_color' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'url' => route('public.client-form.pra-nikah'),
                'icon' => '💍',
                'fields_count' => 'Calon Pengantin',
                'is_ready' => true,
            ],
            'pernikahan' => [
                'key' => 'pernikahan',
                'title' => 'Formulir Riwayat Hidup Konseling - Pernikahan',
                'short_title' => 'Konseling - Pernikahan',
                'description' => 'Konseling evaluasi dinamika hubungan, resolusi konflik, dan keharmonisan suami istri.',
                'badge' => 'Aktif & Siap Digunakan',
                'badge_color' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'url' => route('public.client-form.pernikahan'),
                'icon' => '❤️',
                'fields_count' => 'Pasangan Suami Istri',
                'is_ready' => true,
            ],
            'biography_en' => [
                'key' => 'biography_en',
                'title' => 'Biography Form',
                'short_title' => 'Biography Form (English)',
                'description' => 'English biography and intake questionnaire for international clients and non-Indonesian speakers.',
                'badge' => 'Aktif & Siap Digunakan',
                'badge_color' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'url' => route('public.client-form.biography-en'),
                'icon' => '🌐',
                'fields_count' => 'English Version',
                'is_ready' => true,
            ],
            'non_industri' => [
                'key' => 'non_industri',
                'title' => 'Formulir Riwayat Hidup - Non-Industri',
                'short_title' => 'Riwayat Hidup - Non-Industri',
                'description' => 'Layanan asesmen umum untuk komunitas, sekolah, yayasan, atau institusi non-profit.',
                'badge' => 'Aktif & Siap Digunakan',
                'badge_color' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'url' => route('public.client-form.non-industri'),
                'icon' => '🎓',
                'fields_count' => 'Sosial & Komunitas',
                'is_ready' => true,
            ],
            'industri' => [
                'key' => 'industri',
                'title' => 'Formulir Riwayat Hidup - Industri',
                'short_title' => 'Riwayat Hidup - Industri',
                'description' => 'Formulir asesmen rekam jejak untuk karyawan perusahaan, korporat, atau rekrutmen institusi.',
                'badge' => 'Aktif & Siap Digunakan',
                'badge_color' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'url' => route('public.client-form.industri'),
                'icon' => '🏢',
                'fields_count' => 'Asesmen Industri',
                'is_ready' => true,
            ],
        ];

        // Count responses per category for badge display
        $counts = ClientForm::whereHas('client')
            ->groupBy('form_type')
            ->selectRaw('form_type, count(*) as total')
            ->pluck('total', 'form_type')
            ->toArray();

        foreach ($categories as $k => &$cat) {
            $cat['responses_count'] = $counts[$k] ?? 0;
        }
        unset($cat);

        // Determine active category from query parameter, default to 'anak'
        $activeKey = $request->get('category', 'anak');
        if (!array_key_exists($activeKey, $categories)) {
            $activeKey = 'anak';
        }
        $activeCategory = $categories[$activeKey];

        // Query Submissions for the active category
        $query = ClientForm::with(['client', 'booking'])
            ->whereHas('client')
            ->where('form_type', $activeKey)
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('client_name', 'like', "%{$search}%")
                  ->orWhere('client_phone', 'like', "%{$search}%")
                  ->orWhere('ticket_number', 'like', "%{$search}%");
            });
        }

        $submissions = $query->paginate(25)->withQueryString();

        return view('client-forms.client-forms-summary-results', compact('categories', 'activeKey', 'activeCategory', 'submissions'));
    }

    /**
     * Show detailed dossier submission.
     */
    public function show(ClientForm $clientForm)
    {
        $clientForm->load(['client', 'booking']);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $clientForm,
            ]);
        }

        return view('client-forms.show', compact('clientForm'));
    }

    /**
     * Tampilkan dan cetak / download laporan PDF resmi untuk berkas jawaban formulir klien.
     */
    public function downloadPdf(ClientForm $clientForm)
    {
        $clientForm->load(['client', 'booking']);

        return view('client-forms.client-form-print-dossier', compact('clientForm'));
    }

    /**
     * Perbarui data jawaban formulir klien dan sinkronkan data biodata ke master client.
     */
    public function update(Request $request, ClientForm $clientForm)
    {
        $updatedAnswers = $request->input('answers', $request->except(['_token', '_method']));
        if (!is_array($updatedAnswers)) {
            $updatedAnswers = [];
        }

        $currentAnswers = $clientForm->answers ?? [];
        if (!is_array($currentAnswers)) {
            $currentAnswers = [];
        }

        foreach ($updatedAnswers as $key => $val) {
            $currentAnswers[$key] = $val;
        }

        $clientForm->answers = $currentAnswers;

        // Perbarui client_name & client_phone jika ada field terkait di form
        $newName = $currentAnswers['nama_lengkap'] ?? $currentAnswers['nama'] ?? $currentAnswers['full_name'] ?? null;
        if (!empty($newName)) {
            if (in_array($clientForm->form_type, ['pra_nikah', 'pernikahan']) && !empty($currentAnswers['nama_pasangan'])) {
                $clientForm->client_name = trim($newName) . ' & ' . trim($currentAnswers['nama_pasangan']);
            } else {
                $clientForm->client_name = trim($newName);
            }
        }

        $newPhone = $currentAnswers['no_telepon'] ?? $currentAnswers['phone'] ?? $currentAnswers['telepon'] ?? null;
        if (!empty($newPhone)) {
            $clientForm->client_phone = trim($newPhone);
        }

        $clientForm->save();

        // Sinkronisasi ke master client jika formulir ini terhubung ke Client
        $client = $clientForm->client;
        if ($client) {
            $clientChanged = false;

            // 1. Nama
            if (!empty($newName) && $client->name !== trim($newName)) {
                $client->name = trim($newName);
                $clientChanged = true;
            }

            // 2. Jenis Kelamin
            $genderRaw = $currentAnswers['jenis_kelamin'] ?? $currentAnswers['gender'] ?? null;
            if (!empty($genderRaw)) {
                $gCode = match(strtolower(trim($genderRaw))) {
                    'laki-laki', 'l', 'pria', 'male' => 'l',
                    'perempuan', 'p', 'wanita', 'female' => 'p',
                    default => $client->gender,
                };
                if ($client->gender !== $gCode) {
                    $client->gender = $gCode;
                    $clientChanged = true;
                }
            }

            // 3. Tempat Lahir
            $birthPlace = $currentAnswers['tempat_lahir'] ?? $currentAnswers['birth_place'] ?? $currentAnswers['place_of_birth'] ?? null;
            if (!empty($birthPlace) && $client->birth_place !== trim($birthPlace)) {
                $client->birth_place = trim($birthPlace);
                $clientChanged = true;
            }

            // 4. Tanggal Lahir
            $dob = $currentAnswers['tanggal_lahir'] ?? $currentAnswers['dob'] ?? $currentAnswers['date_of_birth'] ?? null;
            if (!empty($dob)) {
                $formattedDob = date('Y-m-d', strtotime($dob));
                if (!$client->dob || $client->dob->format('Y-m-d') !== $formattedDob) {
                    $client->dob = $formattedDob;
                    $clientChanged = true;
                }
            }

            // 5. No. Telepon
            if (!empty($newPhone) && $client->phone !== trim($newPhone)) {
                $client->phone = trim($newPhone);
                $clientChanged = true;
            }

            // 6. Alamat Email
            $email = $currentAnswers['email'] ?? $currentAnswers['alamat_email'] ?? null;
            if (!empty($email) && $client->email !== trim($email)) {
                $client->email = trim($email);
                $clientChanged = true;
            }

            // 7. Agama
            $religion = $currentAnswers['agama'] ?? $currentAnswers['religion'] ?? null;
            if (!empty($religion) && $client->religion !== trim($religion)) {
                $client->religion = trim($religion);
                $clientChanged = true;
            }

            // 8. Pendidikan Terakhir
            $education = $currentAnswers['pendidikan_terakhir'] ?? $currentAnswers['pendidikan'] ?? $currentAnswers['last_education'] ?? null;
            if (!empty($education) && $client->education !== trim($education)) {
                $client->education = trim($education);
                $clientChanged = true;
            }

            // 9. Pekerjaan
            $occupation = $currentAnswers['pekerjaan'] ?? $currentAnswers['pekerjaan_saat_ini'] ?? $currentAnswers['occupation'] ?? null;
            if (!empty($occupation) && $client->occupation !== trim($occupation)) {
                $client->occupation = trim($occupation);
                $clientChanged = true;
            }

            // 10. Alamat
            $address = $currentAnswers['alamat'] ?? $currentAnswers['alamat_lengkap'] ?? $currentAnswers['alamat_sekarang'] ?? $currentAnswers['address'] ?? null;
            if (!empty($address) && $client->address !== trim($address)) {
                $client->address = trim($address);
                $clientChanged = true;
            }

            if ($clientChanged) {
                // Menyimpan client akan otomatis memicu static::updated yang memanggil $client->syncToClientForms()
                // sehingga seluruh formulir lain milik klien ini juga ikut tersinkronisasi
                $client->save();
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data formulir berhasil diperbarui dan disinkronkan.',
                'data' => $clientForm->fresh(['client', 'booking']),
                'client' => $client ? $client->fresh(['clientForms']) : null,
            ]);
        }

        return back()->with('success', 'Data formulir berhasil diperbarui dan disinkronkan.');
    }
}
