<?php

return [

    'store' => [
        'name' => 'FrozeMate',
        'city' => 'Bandung',
        'whatsapp' => '0812-3456-7890',
        'hours' => 'Senin–Sabtu, 08.00–20.00',
        'bank' => 'BCA 123 456 7890 a.n. FrozeMate',
    ],

    // Stok di bawah atau sama dengan angka ini dianggap menipis.
    'low_stock' => 5,

    /*
    | Parameter Smart Bundle. Kunci (kos, keluarga, ...) juga dipakai sebagai
    | tag pada produk, jadi menambah kebutuhan baru cukup di sini + tag produk.
    */
    'needs' => [
        'kos' => [
            'label' => 'Anak Kos',
            'bundle' => 'Kos Saver',
            'hint' => 'Irit, tahan lama, masak cepat',
            'icon' => 'ph-backpack',
        ],
        'keluarga' => [
            'label' => 'Keluarga',
            'bundle' => 'Family Feast',
            'hint' => 'Lauk harian serumah',
            'icon' => 'ph-house-line',
        ],
        'bekal' => [
            'label' => 'Bekal Sekolah',
            'bundle' => 'Lunchbox Hero',
            'hint' => 'Kecil-kecil, disukai anak',
            'icon' => 'ph-bag-simple',
        ],
        'mealprep' => [
            'label' => 'Meal Prep',
            'bundle' => 'Prep Master',
            'hint' => 'Protein untuk stok seminggu',
            'icon' => 'ph-calendar-check',
        ],
        'acara' => [
            'label' => 'Acara',
            'bundle' => 'Party Platter',
            'hint' => 'Camilan ramai-ramai',
            'icon' => 'ph-confetti',
        ],
    ],

    'people' => [1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5+'],

    'budgets' => [
        'b1' => ['label' => 'Rp25–50 rb', 'long' => 'Rp25.000 – Rp50.000', 'min' => 25000, 'max' => 50000],
        'b2' => ['label' => 'Rp50–75 rb', 'long' => 'Rp50.000 – Rp75.000', 'min' => 50000, 'max' => 75000],
        'b3' => ['label' => 'Rp75–100 rb', 'long' => 'Rp75.000 – Rp100.000', 'min' => 75000, 'max' => 100000],
        'b4' => ['label' => '> Rp100 rb', 'long' => 'Di atas Rp100.000', 'min' => 100000, 'max' => null],
    ],

    'meals' => [3, 5, 7, 10],

    'payments' => [
        'cod' => ['label' => 'Bayar di tempat (COD)', 'hint' => 'Bayar tunai saat pesanan diterima.'],
        'transfer' => ['label' => 'Transfer bank', 'hint' => 'Transfer manual, lalu kirim bukti via WhatsApp.'],
        'qris' => ['label' => 'QRIS di toko', 'hint' => 'Scan QRIS saat ambil pesanan di toko.'],
    ],

    'tones' => [
        'amber' => 'Kuning keemasan',
        'red' => 'Merah',
        'jade' => 'Hijau',
        'blue' => 'Biru',
        'yellow' => 'Kuning',
        'orange' => 'Oranye',
        'plum' => 'Ungu',
    ],

    'arts' => [
        'nugget' => 'Nugget',
        'sosis' => 'Sosis',
        'dimsum' => 'Dimsum',
        'bakso' => 'Bakso tusuk',
        'kentang' => 'Kentang goreng',
        'ayam' => 'Paha ayam',
        'kudapan' => 'Kudapan / pastel',
        'box' => 'Kotak (umum)',
    ],
];
