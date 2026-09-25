<script>
window.addressData = (function() {
    // Daftar Lengkap Seluruh Negara di Dunia (A-Z)
    const countries = [
        'Afganistan', 'Afrika Selatan', 'Afrika Tengah', 'Albania', 'Aljazair', 'Amerika Serikat', 'Andorra', 'Angola',
        'Antigua dan Barbuda', 'Arab Saudi', 'Argentina', 'Armenia', 'Australia', 'Austria', 'Azerbaijan',
        'Bahama', 'Bahrain', 'Bangladesh', 'Barbados', 'Belarus', 'Belgia', 'Belize', 'Benin', 'Bhutan', 'Bolivia',
        'Bosnia dan Herzegovina', 'Botswana', 'Brasil', 'Britania Raya (Inggris)', 'Brunei Darussalam', 'Bulgaria',
        'Burkina Faso', 'Burundi', 'Ceko', 'Chad', 'Chili', 'China (Tiongkok)', 'Denmark', 'Djibouti', 'Dominika',
        'Ekuador', 'El Salvador', 'Eritrea', 'Estonia', 'Eswatini', 'Ethiopia', 'Fiji', 'Filipina', 'Finlandia',
        'Gabon', 'Gambia', 'Georgia', 'Ghana', 'Grenada', 'Guatemala', 'Guinea', 'Guinea-Bissau', 'Guinea Khatulistiwa',
        'Guyana', 'Haiti', 'Honduras', 'Hongaria', 'Hong Kong', 'India', 'Indonesia', 'Irak', 'Iran', 'Irlandia',
        'Islandia', 'Israel', 'Italia', 'Jamaika', 'Jepang', 'Jerman', 'Jordan (Yordania)', 'Kamboja', 'Kamerun',
        'Kanada', 'Kazakhstan', 'Kenya', 'Kepulauan Marshall', 'Kepulauan Solomon', 'Kirgizstan', 'Kiribati', 'Kolombia',
        'Komoro', 'Kongo', 'Korea Selatan', 'Korea Utara', 'Kosta Rika', 'Kroasia', 'Kuba', 'Kuwait', 'Laos', 'Latvia',
        'Lebanon', 'Lesotho', 'Liberia', 'Libya', 'Liechtenstein', 'Lituania', 'Luksemburg', 'Madagaskar', 'Maladewa',
        'Malawi', 'Malaysia', 'Mali', 'Malta', 'Maroko', 'Mauritania', 'Mauritius', 'Meksiko', 'Mesir', 'Mikronesia',
        'Moldova', 'Monako', 'Mongolia', 'Montenegro', 'Mozambik', 'Myanmar', 'Namibia', 'Nauru', 'Nepal', 'Niger',
        'Nigeria', 'Nikaragua', 'Norwegia', 'Oman', 'Pakistan', 'Palau', 'Palestina', 'Panama', 'Pantai Gading',
        'Papua Nugini', 'Paraguay', 'Peru', 'Polandia', 'Portugal', 'Prancis', 'Qatar', 'Republik Dominika',
        'Rumania', 'Rusia', 'Rwanda', 'Saint Kitts dan Nevis', 'Saint Lucia', 'Saint Vincent dan Grenadines', 'Samoa',
        'San Marino', 'Sao Tome dan Principe', 'Selandia Baru', 'Senegal', 'Serbia', 'Seychelles', 'Sierra Leone',
        'Singapura', 'Siprus', 'Slovakia', 'Slovenia', 'Somalia', 'Spanyol', 'Sri Lanka', 'Sudan', 'Sudan Selatan',
        'Suriah', 'Suriname', 'Swedia', 'Swiss', 'Taiwan', 'Tajikistan', 'Tanjung Verde', 'Tanzania', 'Thailand',
        'Timor Leste', 'Togo', 'Tonga', 'Trinidad dan Tobago', 'Tunisia', 'Turki', 'Turkmenistan', 'Tuvalu', 'Uganda',
        'Ukraina', 'Uni Emirat Arab', 'Uruguay', 'Uzbekistan', 'Vanuatu', 'Vatikan', 'Venezuela', 'Vietnam', 'Yaman',
        'Yunani', 'Zambia', 'Zimbabwe', 'Lainnya'
    ];

    // Daftar Lengkap 38 Provinsi Indonesia (A-Z)
    const provinces = [
        'Aceh', 'Bali', 'Banten', 'Bengkulu', 'DI Yogyakarta', 'DKI Jakarta', 'Gorontalo', 'Jambi',
        'Jawa Barat', 'Jawa Tengah', 'Jawa Timur', 'Kalimantan Barat', 'Kalimantan Selatan',
        'Kalimantan Tengah', 'Kalimantan Timur', 'Kalimantan Utara', 'Kepulauan Bangka Belitung',
        'Kepulauan Riau', 'Lampung', 'Maluku', 'Maluku Utara', 'Nusa Tenggara Barat',
        'Nusa Tenggara Timur', 'Papua', 'Papua Barat', 'Papua Barat Daya', 'Papua Pegunungan',
        'Papua Selatan', 'Papua Tengah', 'Riau', 'Sulawesi Barat', 'Sulawesi Selatan',
        'Sulawesi Tengah', 'Sulawesi Tenggara', 'Sulawesi Utara', 'Sumatera Barat',
        'Sumatera Selatan', 'Sumatera Utara'
    ];

    // Daftar Lengkap Seluruh Kota & Kabupaten di 38 Provinsi Indonesia (514 Kota/Kabupaten)
    const citiesByProvince = {
        'Aceh': [
            'Banda Aceh', 'Langsa', 'Lhokseumawe', 'Sabang', 'Subulussalam',
            'Kab. Aceh Barat', 'Kab. Aceh Barat Daya', 'Kab. Aceh Besar', 'Kab. Aceh Jaya',
            'Kab. Aceh Selatan', 'Kab. Aceh Singkil', 'Kab. Aceh Tamiang', 'Kab. Aceh Tengah',
            'Kab. Aceh Tenggara', 'Kab. Aceh Timur', 'Kab. Aceh Utara', 'Kab. Bener Meriah',
            'Kab. Bireuen', 'Kab. Gayo Lues', 'Kab. Nagan Raya', 'Kab. Pidie', 'Kab. Pidie Jaya',
            'Kab. Simeulue'
        ],
        'Sumatera Utara': [
            'Binjai', 'Gunungsitoli', 'Medan', 'Padangsidimpuan', 'Pematangsiantar', 'Sibolga', 'Tanjungbalai', 'Tebing Tinggi',
            'Kab. Asahan', 'Kab. Batubara', 'Kab. Dairi', 'Kab. Deli Serdang', 'Kab. Humbang Hasundutan',
            'Kab. Karo', 'Kab. Labuhanbatu', 'Kab. Labuhanbatu Selatan', 'Kab. Labuhanbatu Utara',
            'Kab. Langkat', 'Kab. Mandailing Natal', 'Kab. Nias', 'Kab. Nias Barat', 'Kab. Nias Selatan',
            'Kab. Nias Utara', 'Kab. Padang Lawas', 'Kab. Padang Lawas Utara', 'Kab. Pakpak Bharat',
            'Kab. Samosir', 'Kab. Serdang Bedagai', 'Kab. Simalungun', 'Kab. Tapanuli Selatan',
            'Kab. Tapanuli Tengah', 'Kab. Tapanuli Utara', 'Kab. Toba'
        ],
        'Sumatera Barat': [
            'Bukittinggi', 'Padang', 'Padang Panjang', 'Pariaman', 'Payakumbuh', 'Sawahlunto', 'Solok',
            'Kab. Agam', 'Kab. Dharmasraya', 'Kab. Kepulauan Mentawai', 'Kab. Lima Puluh Kota',
            'Kab. Padang Pariaman', 'Kab. Pasaman', 'Kab. Pasaman Barat', 'Kab. Pesisir Selatan',
            'Kab. Sijunjung', 'Kab. Solok', 'Kab. Solok Selatan', 'Kab. Tanah Datar'
        ],
        'Riau': [
            'Dumai', 'Pekanbaru',
            'Kab. Bengkalis', 'Kab. Indragiri Hilir', 'Kab. Indragiri Hulu', 'Kab. Kampar',
            'Kab. Kepulauan Meranti', 'Kab. Kuantan Singingi', 'Kab. Pelalawan', 'Kab. Rokan Hilir',
            'Kab. Rokan Hulu', 'Kab. Siak'
        ],
        'Kepulauan Riau': [
            'Batam', 'Tanjungpinang',
            'Kab. Bintan', 'Kab. Karimun', 'Kab. Kepulauan Anambas', 'Kab. Lingga', 'Kab. Natuna'
        ],
        'Jambi': [
            'Jambi', 'Sungai Penuh',
            'Kab. Batanghari', 'Kab. Bungo', 'Kab. Kerinci', 'Kab. Merangin', 'Kab. Muaro Jambi',
            'Kab. Sarolangun', 'Kab. Tanjung Jabung Barat', 'Kab. Tanjung Jabung Timur', 'Kab. Tebo'
        ],
        'Sumatera Selatan': [
            'Lubuklinggau', 'Pagar Alam', 'Palembang', 'Prabumulih',
            'Kab. Banyuasin', 'Kab. Empat Lawang', 'Kab. Lahat', 'Kab. Muara Enim',
            'Kab. Musi Banyuasin', 'Kab. Musi Rawas', 'Kab. Musi Rawas Utara', 'Kab. Ogan Ilir',
            'Kab. Ogan Komering Ilir', 'Kab. Ogan Komering Ulu', 'Kab. Ogan Komering Ulu Selatan',
            'Kab. Ogan Komering Ulu Timur', 'Kab. Penukal Abab Lematang Ilir (PALI)'
        ],
        'Kepulauan Bangka Belitung': [
            'Pangkalpinang',
            'Kab. Bangka', 'Kab. Bangka Barat', 'Kab. Bangka Selatan', 'Kab. Bangka Tengah',
            'Kab. Belitung', 'Kab. Belitung Timur'
        ],
        'Bengkulu': [
            'Bengkulu',
            'Kab. Bengkulu Selatan', 'Kab. Bengkulu Tengah', 'Kab. Bengkulu Utara', 'Kab. Kaur',
            'Kab. Kepahiang', 'Kab. Lebong', 'Kab. Mukomuko', 'Kab. Rejang Lebong', 'Kab. Seluma'
        ],
        'Lampung': [
            'Bandar Lampung', 'Metro',
            'Kab. Lampung Barat', 'Kab. Lampung Selatan', 'Kab. Lampung Tengah', 'Kab. Lampung Timur',
            'Kab. Lampung Utara', 'Kab. Mesuji', 'Kab. Pesawaran', 'Kab. Pesisir Barat',
            'Kab. Pringsewu', 'Kab. Tanggamus', 'Kab. Tulang Bawang', 'Kab. Tulang Bawang Barat',
            'Kab. Way Kanan'
        ],
        'DKI Jakarta': [
            'Jakarta Barat', 'Jakarta Pusat', 'Jakarta Selatan', 'Jakarta Timur', 'Jakarta Utara',
            'Kab. Kepulauan Seribu'
        ],
        'Jawa Barat': [
            'Bandung', 'Banjar', 'Bekasi', 'Bogor', 'Cimahi', 'Cirebon', 'Depok', 'Sukabumi', 'Tasikmalaya',
            'Kab. Bandung', 'Kab. Bandung Barat', 'Kab. Bekasi', 'Kab. Bogor', 'Kab. Ciamis',
            'Kab. Cianjur', 'Kab. Cirebon', 'Kab. Garut', 'Kab. Indramayu', 'Kab. Karawang',
            'Kab. Kuningan', 'Kab. Majalengka', 'Kab. Pangandaran', 'Kab. Purwakarta',
            'Kab. Subang', 'Kab. Sukabumi', 'Kab. Sumedang', 'Kab. Tasikmalaya'
        ],
        'Banten': [
            'Cilegon', 'Serang', 'Tangerang', 'Tangerang Selatan',
            'Kab. Lebak', 'Kab. Pandeglang', 'Kab. Serang', 'Kab. Tangerang'
        ],
        'Jawa Tengah': [
            'Magelang', 'Pekalongan', 'Salatiga', 'Semarang', 'Surakarta (Solo)', 'Tegal',
            'Kab. Banjarnegara', 'Kab. Banyumas', 'Kab. Batang', 'Kab. Blora', 'Kab. Boyolali',
            'Kab. Brebes', 'Kab. Cilacap', 'Kab. Demak', 'Kab. Grobogan', 'Kab. Jepara',
            'Kab. Karanganyar', 'Kab. Kebumen', 'Kab. Kendal', 'Kab. Klaten', 'Kab. Kudus',
            'Kab. Magelang', 'Kab. Pati', 'Kab. Pekalongan', 'Kab. Pemalang', 'Kab. Purbalingga',
            'Kab. Purworejo', 'Kab. Rembang', 'Kab. Semarang', 'Kab. Sragen', 'Kab. Sukoharjo',
            'Kab. Tegal', 'Kab. Temanggung', 'Kab. Wonogiri', 'Kab. Wonosobo'
        ],
        'DI Yogyakarta': [
            'Yogyakarta', 'Kab. Bantul', 'Kab. Gunungkidul', 'Kab. Kulon Progo', 'Kab. Sleman'
        ],
        'Jawa Timur': [
            'Batu', 'Blitar', 'Kediri', 'Madiun', 'Malang', 'Mojokerto', 'Pasuruan', 'Probolinggo', 'Surabaya',
            'Kab. Bangkalan', 'Kab. Banyuwangi', 'Kab. Blitar', 'Kab. Bojonegoro', 'Kab. Bondowoso',
            'Kab. Gresik', 'Kab. Jember', 'Kab. Jombang', 'Kab. Kediri', 'Kab. Lamongan',
            'Kab. Lumajang', 'Kab. Madiun', 'Kab. Magetan', 'Kab. Malang', 'Kab. Mojokerto',
            'Kab. Nganjuk', 'Kab. Ngawi', 'Kab. Pacitan', 'Kab. Pamekasan', 'Kab. Pasuruan',
            'Kab. Ponorogo', 'Kab. Probolinggo', 'Kab. Sampang', 'Kab. Sidoarjo', 'Kab. Situbondo',
            'Kab. Sumenep', 'Kab. Trenggalek', 'Kab. Tuban', 'Kab. Tulungagung'
        ],
        'Bali': [
            'Denpasar',
            'Kab. Badung', 'Kab. Bangli', 'Kab. Buleleng', 'Kab. Gianyar', 'Kab. Jembrana',
            'Kab. Karangasem', 'Kab. Klungkung', 'Kab. Tabanan'
        ],
        'Nusa Tenggara Barat': [
            'Bima', 'Mataram',
            'Kab. Bima', 'Kab. Dompu', 'Kab. Lombok Barat', 'Kab. Lombok Tengah', 'Kab. Lombok Timur',
            'Kab. Lombok Utara', 'Kab. Sumbawa', 'Kab. Sumbawa Barat'
        ],
        'Nusa Tenggara Timur': [
            'Kupang',
            'Kab. Alor', 'Kab. Belu', 'Kab. Ende', 'Kab. Flores Timur', 'Kab. Kupang',
            'Kab. Lembata', 'Kab. Malaka', 'Kab. Manggarai', 'Kab. Manggarai Barat (Labuan Bajo)',
            'Kab. Manggarai Timur', 'Kab. Nagekeo', 'Kab. Ngada', 'Kab. Rote Ndao', 'Kab. Sabu Raijua',
            'Kab. Sikka', 'Kab. Sumba Barat', 'Kab. Sumba Barat Daya', 'Kab. Sumba Tengah',
            'Kab. Sumba Timur', 'Kab. Timor Tengah Selatan', 'Kab. Timor Tengah Utara'
        ],
        'Kalimantan Barat': [
            'Pontianak', 'Singkawang',
            'Kab. Bengkayang', 'Kab. Kapuas Hulu', 'Kab. Kayong Utara', 'Kab. Ketapang',
            'Kab. Kubu Raya', 'Kab. Landak', 'Kab. Melawi', 'Kab. Mempawah', 'Kab. Sambas',
            'Kab. Sanggau', 'Kab. Sekadau', 'Kab. Sintang'
        ],
        'Kalimantan Tengah': [
            'Palangka Raya',
            'Kab. Barito Selatan', 'Kab. Barito Timur', 'Kab. Barito Utara', 'Kab. Gunung Mas',
            'Kab. Kapuas', 'Kab. Katingan', 'Kab. Kotawaringin Barat', 'Kab. Kotawaringin Timur',
            'Kab. Lamandau', 'Kab. Murung Raya', 'Kab. Pulang Pisau', 'Kab. Seruyan', 'Kab. Sukamara'
        ],
        'Kalimantan Selatan': [
            'Banjarbaru', 'Banjarmasin',
            'Kab. Balangan', 'Kab. Banjar', 'Kab. Barito Kuala', 'Kab. Hulu Sungai Selatan',
            'Kab. Hulu Sungai Tengah', 'Kab. Hulu Sungai Utara', 'Kab. Kotabaru', 'Kab. Tabalong',
            'Kab. Tanah Bumbu', 'Kab. Tanah Laut', 'Kab. Tapin'
        ],
        'Kalimantan Timur': [
            'Balikpapan', 'Bontang', 'Samarinda',
            'Kab. Berau', 'Kab. Kutai Barat', 'Kab. Kutai Kartanegara', 'Kab. Kutai Timur',
            'Kab. Mahakam Ulu', 'Kab. Paser', 'Kab. Penajam Paser Utara (IKN)'
        ],
        'Kalimantan Utara': [
            'Tarakan',
            'Kab. Bulungan', 'Kab. Malinau', 'Kab. Nunukan', 'Kab. Tana Tidung'
        ],
        'Sulawesi Utara': [
            'Bitung', 'Kotamobagu', 'Manado', 'Tomohon',
            'Kab. Bolaang Mongondow', 'Kab. Bolaang Mongondow Selatan', 'Kab. Bolaang Mongondow Timur',
            'Kab. Bolaang Mongondow Utara', 'Kab. Kepulauan Sangihe', 'Kab. Kepulauan Siau Tagulandang Biaro (Sitaro)',
            'Kab. Kepulauan Talaud', 'Kab. Minahasa', 'Kab. Minahasa Selatan', 'Kab. Minahasa Tenggara',
            'Kab. Minahasa Utara'
        ],
        'Gorontalo': [
            'Gorontalo',
            'Kab. Boalemo', 'Kab. Bone Bolango', 'Kab. Gorontalo', 'Kab. Gorontalo Utara', 'Kab. Pohuwato'
        ],
        'Sulawesi Tengah': [
            'Palu',
            'Kab. Banggai', 'Kab. Banggai Kepulauan', 'Kab. Banggai Laut', 'Kab. Buol',
            'Kab. Donggala', 'Kab. Morowali', 'Kab. Morowali Utara', 'Kab. Parigi Moutong',
            'Kab. Poso', 'Kab. Sigi', 'Kab. Tojo Una-Una', 'Kab. Tolitoli'
        ],
        'Sulawesi Barat': [
            'Mamuju',
            'Kab. Majene', 'Kab. Mamasa', 'Kab. Mamuju', 'Kab. Mamuju Tengah', 'Kab. Pasangkayu', 'Kab. Polewali Mandar'
        ],
        'Sulawesi Selatan': [
            'Makassar', 'Palopo', 'Parepare',
            'Kab. Bantaeng', 'Kab. Barru', 'Kab. Bone', 'Kab. Bulukumba', 'Kab. Enrekang',
            'Kab. Gowa', 'Kab. Jeneponto', 'Kab. Kepulauan Selayar', 'Kab. Luwu', 'Kab. Luwu Timur',
            'Kab. Luwu Utara', 'Kab. Maros', 'Kab. Pangkajene dan Kepulauan (Pangkep)',
            'Kab. Pinrang', 'Kab. Sidenreng Rappang (Sidrap)', 'Kab. Sinjai', 'Kab. Soppeng',
            'Kab. Takalar', 'Kab. Tana Toraja', 'Kab. Toraja Utara', 'Kab. Wajo'
        ],
        'Sulawesi Tenggara': [
            'Baubau', 'Kendari',
            'Kab. Bombana', 'Kab. Buton', 'Kab. Buton Selatan', 'Kab. Buton Tengah', 'Kab. Buton Utara',
            'Kab. Kolaka', 'Kab. Kolaka Timur', 'Kab. Kolaka Utara', 'Kab. Konawe', 'Kab. Konawe Kepulauan',
            'Kab. Konawe Selatan', 'Kab. Konawe Utara', 'Kab. Muna', 'Kab. Muna Barat', 'Kab. Wakatobi'
        ],
        'Maluku': [
            'Ambon', 'Tual',
            'Kab. Buru', 'Kab. Buru Selatan', 'Kab. Kepulauan Aru', 'Kab. Kepulauan Tanimbar',
            'Kab. Maluku Barat Daya', 'Kab. Maluku Tengah', 'Kab. Maluku Tenggara',
            'Kab. Seram Bagian Barat', 'Kab. Seram Bagian Timur'
        ],
        'Maluku Utara': [
            'Ternate', 'Tidore Kepulauan',
            'Kab. Halmahera Barat', 'Kab. Halmahera Tengah', 'Kab. Halmahera Timur',
            'Kab. Halmahera Selatan', 'Kab. Halmahera Utara', 'Kab. Kepulauan Sula',
            'Kab. Pulau Morotai', 'Kab. Pulau Taliabu'
        ],
        'Papua': [
            'Jayapura',
            'Kab. Biak Numfor', 'Kab. Jayapura', 'Kab. Keerom', 'Kab. Kepulauan Yapen',
            'Kab. Mamberamo Raya', 'Kab. Sarmi', 'Kab. Supiori', 'Kab. Waropen'
        ],
        'Papua Barat': [
            'Manokwari',
            'Kab. Fakfak', 'Kab. Kaimana', 'Kab. Manokwari', 'Kab. Manokwari Selatan',
            'Kab. Pegunungan Arfak', 'Kab. Teluk Bintuni', 'Kab. Teluk Wondama'
        ],
        'Papua Selatan': [
            'Kab. Asmat', 'Kab. Boven Digoel', 'Kab. Mappi', 'Kab. Merauke'
        ],
        'Papua Tengah': [
            'Kab. Deiyai', 'Kab. Dogiyai', 'Kab. Intan Jaya', 'Kab. Mimika (Timika)',
            'Kab. Nabire', 'Kab. Paniai', 'Kab. Puncak', 'Kab. Puncak Jaya'
        ],
        'Papua Pegunungan': [
            'Kab. Jayawijaya (Wamena)', 'Kab. Lanny Jaya', 'Kab. Mamberamo Tengah',
            'Kab. Nduga', 'Kab. Pegunungan Bintang', 'Kab. Tolikara', 'Kab. Yahukimo', 'Kab. Yalimo'
        ],
        'Papua Barat Daya': [
            'Sorong',
            'Kab. Maybrat', 'Kab. Raja Ampat', 'Kab. Sorong', 'Kab. Sorong Selatan', 'Kab. Tambrauw'
        ]
    };

    // Sort Alphabetically A-Z (dengan 'Lainnya' di akhir)
    countries.sort((a, b) => (a === 'Lainnya' ? 1 : b === 'Lainnya' ? -1 : a.localeCompare('id')));
    provinces.sort((a, b) => a.localeCompare('id'));

    // Sort every province's city list A-Z
    Object.keys(citiesByProvince).forEach(prov => {
        citiesByProvince[prov].sort((a, b) => a.localeCompare('id'));
    });

    return { countries, provinces, citiesByProvince };
})();

window.addressSelector = function(initialCountry = '', initialProvince = '', initialCity = '', initialAddress = '') {
    const data = window.addressData || { countries: ['Indonesia', 'Lainnya'], provinces: ['Jawa Timur', 'DKI Jakarta'], citiesByProvince: {} };
    
    return {
        country: initialCountry || '',
        province: initialProvince || '',
        city: initialCity || '',
        address: initialAddress || '',
        notes: '',
        
        countryOpen: false,
        countrySearch: '',
        
        provinceOpen: false,
        provinceSearch: '',
        
        cityOpen: false,
        citySearch: '',
        
        get countryList() {
            let list = [...(data.countries || [])].sort((a, b) => a.localeCompare('id', { sensitivity: 'base' }));
            if (!this.countrySearch) return list;
            return list.filter(c => c.toLowerCase().includes(this.countrySearch.toLowerCase()));
        },
        
        get isAddressAllowed() {
            if (typeof this.isContactCompleted !== 'undefined') {
                return this.isContactCompleted;
            }
            return true;
        },
        
        get isAddressEnabled() {
            if (!this.isAddressAllowed) return false;
            if (!this.country) return false;
            if (this.country === 'Indonesia') {
                return !!this.province && !!this.city;
            }
            return true;
        },

        get isAddressCompleted() {
            if (!this.isAddressEnabled) return false;
            return !!this.address && this.address.trim().length > 0;
        },

        get isNotesEnabled() {
            return this.isAddressCompleted;
        },
        
        get provinceList() {
            let list = [...(data.provinces || [])].sort((a, b) => a.localeCompare('id', { sensitivity: 'base' }));
            if (!this.provinceSearch) return list;
            return list.filter(p => p.toLowerCase().includes(this.provinceSearch.toLowerCase()));
        },
        
        get cityList() {
            if (!this.province || !data.citiesByProvince || !data.citiesByProvince[this.province]) {
                return [];
            }
            let list = [...data.citiesByProvince[this.province]];
            list.sort((a, b) => a.localeCompare('id', { sensitivity: 'base' }));
            if (!this.citySearch) return list;
            return list.filter(c => c.toLowerCase().includes(this.citySearch.toLowerCase()));
        },
        
        openCountry() {
            if (!this.isAddressAllowed) return;
            this.countryOpen = true;
            this.countrySearch = '';
            this.provinceOpen = false;
            this.cityOpen = false;
            setTimeout(() => {
                this.$refs.countrySearchInput && this.$refs.countrySearchInput.focus();
            }, 50);
        },
        
        selectCountry(val) {
            this.country = val;
            this.countryOpen = false;
            this.countrySearch = '';
            if (val !== 'Indonesia') {
                this.province = '';
                this.provinceSearch = '';
                this.provinceOpen = false;
                this.city = '';
                this.citySearch = '';
                this.cityOpen = false;
            }
        },
        
        openProvince() {
            if (!this.isAddressAllowed || this.country !== 'Indonesia') return;
            this.provinceOpen = true;
            this.provinceSearch = '';
            this.countryOpen = false;
            this.cityOpen = false;
            setTimeout(() => {
                this.$refs.provinceSearchInput && this.$refs.provinceSearchInput.focus();
            }, 50);
        },
        
        selectProvince(val) {
            this.province = val;
            this.provinceOpen = false;
            this.provinceSearch = '';
            this.city = '';
            this.citySearch = '';
        },
        
        openCity() {
            if (!this.isAddressAllowed || this.country !== 'Indonesia' || !this.province) return;
            this.cityOpen = true;
            this.citySearch = '';
            this.countryOpen = false;
            this.provinceOpen = false;
            setTimeout(() => {
                this.$refs.citySearchInput && this.$refs.citySearchInput.focus();
            }, 50);
        },
        
        selectCity(val) {
            this.city = val;
            this.cityOpen = false;
            this.citySearch = '';
        }
    };
};
</script>
