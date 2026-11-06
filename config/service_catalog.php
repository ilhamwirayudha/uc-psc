<?php

return [
    /*
    |--------------------------------------------------------------------------
    | UC PSC Service & Pricing Catalog
    |--------------------------------------------------------------------------
    | Sesuai dengan tabel resmi tarif layanan UC Psychological Service Center.
    */

    'konseling' => [
        'tiers' => [
            'pro' => [
                'label' => 'Konseling Pro (Psikolog Senior)',
                'wni' => [
                    'umum' => [
                        'price' => 525000,
                        'overtime' => 525000,
                        'label' => 'Pro - WNI - Umum (Rp 525.000 / sesi)',
                    ],
                    'kerja_sama' => [
                        'price' => 450000,
                        'overtime' => 450000,
                        'label' => 'Pro - WNI - Kerja Sama (Rp 450.000 / sesi)',
                    ],
                ],
                'wna' => [
                    'umum' => [
                        'price' => 775000,
                        'overtime' => 775000,
                        'label' => 'Pro - WNA - Umum (Rp 775.000 / sesi)',
                    ],
                    'kerja_sama' => [
                        'price' => 675000,
                        'overtime' => 675000,
                        'label' => 'Pro - WNA - Kerja Sama (Rp 675.000 / sesi)',
                    ],
                ],
            ],
            'reguler' => [
                'label' => 'Konseling Reguler',
                'wni' => [
                    'umum' => [
                        'price' => 275000,
                        'overtime' => 275000,
                        'label' => 'Reguler - WNI - Umum (Rp 275.000 / sesi)',
                    ],
                    'kerja_sama' => [
                        'price' => 225000,
                        'overtime' => 225000,
                        'label' => 'Reguler - WNI - Kerja Sama (Rp 225.000 / sesi)',
                    ],
                ],
                'wna' => [
                    'umum' => [
                        'price' => 525000,
                        'overtime' => 525000,
                        'label' => 'Reguler - WNA - Umum (Rp 525.000 / sesi)',
                    ],
                    'kerja_sama' => [
                        'price' => 450000,
                        'overtime' => 450000,
                        'label' => 'Reguler - WNA - Kerja Sama (Rp 450.000 / sesi)',
                    ],
                ],
            ],
        ],
        'methods' => [
            'offline' => 'Offline (Tatap Muka di UC PSC)',
            'online' => 'Online (Video Call)',
        ],
    ],

    'psikotes' => [
        'industri' => [
            'label' => 'Psikotes Industri',
            'methods' => ['offline' => 'Offline', 'online' => 'Online'],
            'packages' => [
                'short_staf_umum' => [
                    'format' => 'Short',
                    'level' => 'Staf',
                    'kategori' => 'Umum',
                    'price' => 575000,
                    'label' => 'Short - Staf - Umum (Rp 575.000)',
                ],
                'short_staf_kerja_sama' => [
                    'format' => 'Short',
                    'level' => 'Staf',
                    'kategori' => 'Kerja Sama',
                    'price' => 500000,
                    'label' => 'Short - Staf - Kerja Sama (Rp 500.000)',
                ],
                'long_staf_umum' => [
                    'format' => 'Long',
                    'level' => 'Staf',
                    'kategori' => 'Umum',
                    'price' => 700000,
                    'label' => 'Long - Staf - Umum (Rp 700.000)',
                ],
                'long_staf_kerja_sama' => [
                    'format' => 'Long',
                    'level' => 'Staf',
                    'kategori' => 'Kerja Sama',
                    'price' => 600000,
                    'label' => 'Long - Staf - Kerja Sama (Rp 600.000)',
                ],
                'long_supervisor_umum' => [
                    'format' => 'Long',
                    'level' => 'Supervisor',
                    'kategori' => 'Umum',
                    'price' => 775000,
                    'label' => 'Long - Supervisor - Umum (Rp 775.000)',
                ],
                'long_supervisor_kerja_sama' => [
                    'format' => 'Long',
                    'level' => 'Supervisor',
                    'kategori' => 'Kerja Sama',
                    'price' => 675000,
                    'label' => 'Long - Supervisor - Kerja Sama (Rp 675.000)',
                ],
                'long_manager_umum' => [
                    'format' => 'Long',
                    'level' => 'Manager',
                    'kategori' => 'Umum',
                    'price' => 850000,
                    'label' => 'Long - Manager - Umum (Rp 850.000)',
                ],
                'long_manager_kerja_sama' => [
                    'format' => 'Long',
                    'level' => 'Manager',
                    'kategori' => 'Kerja Sama',
                    'price' => 750000,
                    'label' => 'Long - Manager - Kerja Sama (Rp 750.000)',
                ],
            ],
        ],

        'non_industri_bakat_minat' => [
            'label' => 'Psikotes Non-Industri Bakat Minat Biasa',
            'methods' => ['offline' => 'Offline', 'online' => 'Online'],
            'packages' => [
                'short_umum' => [
                    'format' => 'Short',
                    'kategori' => 'Umum',
                    'price' => 625000,
                    'label' => 'Short - Umum (Rp 625.000)',
                ],
                'short_kerja_sama' => [
                    'format' => 'Short',
                    'kategori' => 'Kerja Sama',
                    'price' => 550000,
                    'label' => 'Short - Kerja Sama (Rp 550.000)',
                ],
                'long_umum' => [
                    'format' => 'Long',
                    'kategori' => 'Umum',
                    'price' => 750000,
                    'label' => 'Long - Umum (Rp 750.000)',
                ],
                'long_kerja_sama' => [
                    'format' => 'Long',
                    'kategori' => 'Kerja Sama',
                    'price' => 650000,
                    'label' => 'Long - Kerja Sama (Rp 650.000)',
                ],
            ],
        ],

        'non_industri_khusus' => [
            'label' => 'Psikotes Non-Industri Khusus',
            'packages' => [
                'ucs_med_mmpi' => [
                    'format' => 'UCS-MED',
                    'metode' => 'Online (UCS MNA)',
                    'kategori' => 'MMPI',
                    'price' => 50000,
                    'label' => 'UCS-MED - MMPI (Online UCS MNA - Rp 50.000)',
                ],
                'ucs_dem_mmpi' => [
                    'format' => 'UCS-DEM',
                    'metode' => 'Online (UCS MNA)',
                    'kategori' => 'MMPI',
                    'price' => 50000,
                    'label' => 'UCS-DEM - MMPI (Online UCS MNA - Rp 50.000)',
                ],
                'ucs_dem_fcat_feast' => [
                    'format' => 'UCS-DEM',
                    'metode' => 'Online (UCS MNA)',
                    'kategori' => 'FCAT-FEAST',
                    'price' => 200000,
                    'label' => 'UCS-DEM - FCAT-FEAST (Online UCS MNA - Rp 200.000)',
                ],
                'ucs_ppsy_fcat_ftpi_feast' => [
                    'format' => 'UCS-PPSY',
                    'metode' => 'Online (UCS MNA)',
                    'kategori' => 'FCAT - FTPI - FEAST',
                    'price' => 250000,
                    'label' => 'UCS-PPSY - FCAT - FTPI - FEAST (Online UCS MNA - Rp 250.000)',
                ],
                'konseling_ssct_epps' => [
                    'format' => 'Konseling Psikotes',
                    'metode' => 'Online & Offline',
                    'kategori' => 'SSCT - EPPS',
                    'price' => 75000,
                    'label' => 'Konseling Psikotes - SSCT - EPPS (Rp 75.000)',
                ],
            ],
        ],
    ],
];

