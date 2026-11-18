<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffDummySeeder extends Seeder
{
    /**
     * Isi 6 akun dummy dengan role staff secara idempoten.
     */
    public function run(): void
    {
        $staffAccounts = [
            [
                'email' => 'ester@ucpsc.test',
                'name' => 'Ester Wulandari',
                'password' => Hash::make('staff123'),
                'role' => 'staff',
                'employee_id' => 'PSC-STF-001',
                'position' => 'Staff Skorer Manual',
                'phone' => '081234500201',
                'gender' => 'p',
                'address' => 'Jl. CitraLand CBD Boulevard No. 11, Surabaya',
            ],
            [
                'email' => 'putri@ucpsc.test',
                'name' => 'Putri Rahmawati',
                'password' => Hash::make('staff123'),
                'role' => 'staff',
                'employee_id' => 'PSC-STF-002',
                'position' => 'Staff Skorer Software',
                'phone' => '081234500202',
                'gender' => 'p',
                'address' => 'Jl. Mayjend Sungkono No. 22, Surabaya',
            ],
            [
                'email' => 'dayu@ucpsc.test',
                'name' => 'Dayu Prakoso',
                'password' => Hash::make('staff123'),
                'role' => 'staff',
                'employee_id' => 'PSC-STF-003',
                'position' => 'Staff Editor HPP',
                'phone' => '081234500203',
                'gender' => 'l',
                'address' => 'Jl. HR Muhammad No. 33, Surabaya',
            ],
            [
                'email' => 'kevin@ucpsc.test',
                'name' => 'Kevin Pratama',
                'password' => Hash::make('staff123'),
                'role' => 'staff',
                'employee_id' => 'PSC-STF-004',
                'position' => 'Staff Tester Psikotes',
                'phone' => '081234500204',
                'gender' => 'l',
                'address' => 'Jl. Raya Darmo Permai No. 44, Surabaya',
            ],
            [
                'email' => 'nabila@ucpsc.test',
                'name' => 'Nabila Kusuma',
                'password' => Hash::make('staff123'),
                'role' => 'staff',
                'employee_id' => 'PSC-STF-005',
                'position' => 'Staff Skorer & Editor',
                'phone' => '081234500205',
                'gender' => 'p',
                'address' => 'Jl. Manyar Kertoarjo No. 55, Surabaya',
            ],
            [
                'email' => 'rafael@ucpsc.test',
                'name' => 'Rafael Santoso',
                'password' => Hash::make('staff123'),
                'role' => 'staff',
                'employee_id' => 'PSC-STF-006',
                'position' => 'Staff Administrasi Psikotes',
                'phone' => '081234500206',
                'gender' => 'l',
                'address' => 'Jl. Bukit Telaga Golf No. 66, Surabaya',
            ],
        ];

        foreach ($staffAccounts as $account) {
            User::firstOrCreate(
                ['email' => $account['email']],
                $account
            );
        }
    }
}
