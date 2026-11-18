<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Isi 10 data dummy perusahaan (PT AAA s.d. PT JJJ) secara idempoten.
     */
    public function run(): void
    {
        $companies = [
            [
                'name' => 'PT AAA',
                'category' => 'kerja_sama',
                'pic_name' => 'Bpk. Andi Wijaya',
                'pic_phone' => '081230000001',
                'pic_email' => 'hrd@ptaaa.co.id',
                'address' => 'Jl. Mayjend Sungkono No. 10, Surabaya',
                'notes' => 'Data dummy perusahaan mitra kerja sama 1.',
                'is_active' => true,
            ],
            [
                'name' => 'PT BBB',
                'category' => 'umum',
                'pic_name' => 'Ibu Bella Santoso',
                'pic_phone' => '081230000002',
                'pic_email' => 'hrd@ptbbb.co.id',
                'address' => 'Jl. HR Muhammad No. 20, Surabaya',
                'notes' => 'Data dummy perusahaan kategori umum 2.',
                'is_active' => true,
            ],
            [
                'name' => 'PT CCC',
                'category' => 'kerja_sama',
                'pic_name' => 'Bpk. Candra Pratama',
                'pic_phone' => '081230000003',
                'pic_email' => 'hrd@ptccc.co.id',
                'address' => 'Jl. Basuki Rahmat No. 30, Surabaya',
                'notes' => 'Data dummy perusahaan mitra kerja sama 3.',
                'is_active' => true,
            ],
            [
                'name' => 'PT DDD',
                'category' => 'umum',
                'pic_name' => 'Ibu Dina Kusuma',
                'pic_phone' => '081230000004',
                'pic_email' => 'hrd@ptddd.co.id',
                'address' => 'Jl. Ahmad Yani No. 40, Surabaya',
                'notes' => 'Data dummy perusahaan kategori umum 4.',
                'is_active' => true,
            ],
            [
                'name' => 'PT EEE',
                'category' => 'kerja_sama',
                'pic_name' => 'Bpk. Eko Nugroho',
                'pic_phone' => '081230000005',
                'pic_email' => 'hrd@pteee.co.id',
                'address' => 'Jl. Raya Darmo No. 50, Surabaya',
                'notes' => 'Data dummy perusahaan mitra kerja sama 5.',
                'is_active' => true,
            ],
            [
                'name' => 'PT FFF',
                'category' => 'umum',
                'pic_name' => 'Ibu Fitri Rahayu',
                'pic_phone' => '081230000006',
                'pic_email' => 'hrd@ptfff.co.id',
                'address' => 'Jl. Diponegoro No. 60, Surabaya',
                'notes' => 'Data dummy perusahaan kategori umum 6.',
                'is_active' => true,
            ],
            [
                'name' => 'PT GGG',
                'category' => 'kerja_sama',
                'pic_name' => 'Bpk. Gilang Permana',
                'pic_phone' => '081230000007',
                'pic_email' => 'hrd@ptggg.co.id',
                'address' => 'Jl. Manyar Kertoarjo No. 70, Surabaya',
                'notes' => 'Data dummy perusahaan mitra kerja sama 7.',
                'is_active' => true,
            ],
            [
                'name' => 'PT HHH',
                'category' => 'umum',
                'pic_name' => 'Ibu Hana Lestari',
                'pic_phone' => '081230000008',
                'pic_email' => 'hrd@pthhh.co.id',
                'address' => 'Jl. Kertajaya Indah No. 80, Surabaya',
                'notes' => 'Data dummy perusahaan kategori umum 8.',
                'is_active' => true,
            ],
            [
                'name' => 'PT III',
                'category' => 'kerja_sama',
                'pic_name' => 'Bpk. Indra Gunawan',
                'pic_phone' => '081230000009',
                'pic_email' => 'hrd@ptiii.co.id',
                'address' => 'Jl. Rungkut Industri No. 90, Surabaya',
                'notes' => 'Data dummy perusahaan mitra kerja sama 9.',
                'is_active' => true,
            ],
            [
                'name' => 'PT JJJ',
                'category' => 'umum',
                'pic_name' => 'Ibu Julia Hartono',
                'pic_phone' => '081230000010',
                'pic_email' => 'hrd@ptjjj.co.id',
                'address' => 'Jl. CitraLand CBD Boulevard No. 100, Surabaya',
                'notes' => 'Data dummy perusahaan kategori umum 10.',
                'is_active' => true,
            ],
        ];

        foreach ($companies as $company) {
            Company::firstOrCreate(
                ['name' => $company['name']],
                $company
            );
        }
    }
}
