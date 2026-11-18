<?php

namespace Database\Seeders;

use App\Models\Counselor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            [
                'email' => 'admin@ucpsc.test',
                'name' => 'Admin UC PSC',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'position' => 'Admin Operasional UC PSC',
            ],
            [
                'email' => 'staff@ucpsc.test',
                'name' => 'Staff Operasional (Tester)',
                'password' => Hash::make('staff123'),
                'role' => 'staff',
                'position' => 'Staff Tester & Asisten Psikolog',
            ],
        ];

        foreach ($accounts as $account) {
            User::firstOrCreate(['email' => $account['email']], $account);
        }

        $this->call(StaffDummySeeder::class);

        // Hubungkan akun Psikolog dengan data master Counselor yang aktif
        $activeCounselors = Counselor::where('status', 'active')
            ->whereNotNull('email')
            ->get();

        foreach ($activeCounselors as $counselor) {
            User::firstOrCreate(
                ['email' => $counselor->email],
                [
                    'name' => $counselor->name,
                    'password' => Hash::make('psikolog123'),
                    'role' => 'psikolog',
                    'counselor_id' => $counselor->id,
                    'phone' => $counselor->phone,
                    'position' => $counselor->specialization,
                ]
            );
        }
    }
}
