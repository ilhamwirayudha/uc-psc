<?php

namespace Database\Seeders;

use App\Models\Counselor;
use Illuminate\Database\Seeder;

class CounselorSeeder extends Seeder
{
    public function run(): void
    {
        $counselors = [
            ['name' => 'Ainun Fahma, M.Psi., Psikolog', 'phone' => '081234567001', 'email' => 'ainun@ucpsc.test', 'specialization' => 'Psikolog Klinis Dewasa', 'sipp_number' => 'SIPP-001', 'str_number' => 'STR-001', 'status' => 'active'],
            ['name' => 'Budi Santoso, M.Psi., Psikolog', 'phone' => '081234567002', 'email' => 'budi@ucpsc.test', 'specialization' => 'Psikolog Anak & Remaja', 'sipp_number' => 'SIPP-002', 'str_number' => 'STR-002', 'status' => 'active'],
            ['name' => 'Citra Dewi, M.Psi., Psikolog', 'phone' => '081234567003', 'email' => 'citra@ucpsc.test', 'specialization' => 'Psikolog Klinis Umum', 'sipp_number' => 'SIPP-003', 'str_number' => 'STR-003', 'status' => 'active'],
            ['name' => 'Dian Kusuma, M.Psi., Psikolog', 'phone' => '081234567004', 'email' => 'dian@ucpsc.test', 'specialization' => 'Psikolog Pendidikan', 'sipp_number' => 'SIPP-004', 'str_number' => 'STR-004', 'status' => 'active'],
            ['name' => 'Eka Putra, M.Psi., Psikolog', 'phone' => '081234567005', 'email' => 'eka@ucpsc.test', 'specialization' => 'Psikolog Klinis Umum', 'sipp_number' => 'SIPP-005', 'str_number' => 'STR-005', 'status' => 'inactive'],
        ];

        foreach ($counselors as $counselor) {
            Counselor::create($counselor);
        }
    }
}
