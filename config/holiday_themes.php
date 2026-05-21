<?php

declare(strict_types=1);



/**

 * Konfigurasi kalender tema hari besar.

 *

 * Format item:

 * - key: 'm-d' (berulang tahunan) atau 'Y-m-d' (spesifik tahun)

 * - class: class CSS tema (contoh: theme-natal)

 * - label: label dekorasi (aksesibilitas)

 * - icon: icon Font Awesome solid (tanpa prefix fa-solid)

 * - ucapan: teks ucapan (gunakan " — " untuk memisah judul & subteks)

 * - badge: label kecil di atas ucapan
 * - duration_days: lama tema aktif (default 1). Hari keagamaan biasanya 7.

 *

 * @var list<array{key: string, class: string, label: string, icon: string, ucapan: string, badge: string, duration_days?: int}>

 */

return [

    // Hari nasional / peringatan

    [

        'key' => '01-01',

        'class' => 'theme-tahun-baru',

        'label' => 'Tema Tahun Baru',

        'icon' => 'fa-star',

        'badge' => 'Tahun Baru',

        'ucapan' => 'Selamat Tahun Baru — Semoga tahun ini membawa berkah, kesehatan, dan kemajuan bagi kita semua',

    ],

    [

        'key' => '02-09',

        'class' => 'theme-pers-nasional',

        'label' => 'Tema Hari Pers Nasional',

        'icon' => 'fa-newspaper',

        'badge' => 'Hari Pers Nasional',

        'ucapan' => 'Selamat Hari Pers Nasional — Mengapresiasi peran pers sebagai pilar demokrasi dan informasi',

    ],

    [

        'key' => '04-21',

        'class' => 'theme-kartini',

        'label' => 'Tema Hari Kartini',

        'icon' => 'fa-seedling',

        'badge' => 'Hari Kartini',

        'ucapan' => 'Selamat Hari Kartini — Harkat bangsa, harkat Indonesia, harkatnya api wanita!',

    ],

    [

        'key' => '05-02',

        'class' => 'theme-hardiknas',

        'label' => 'Tema Hari Pendidikan Nasional',

        'icon' => 'fa-graduation-cap',

        'badge' => 'Hardiknas',

        'ucapan' => 'Selamat Hari Pendidikan Nasional — Terus meningkatkan mutu pendidikan untuk Indonesia Emas',

    ],

    [

        'key' => '05-20',

        'class' => 'theme-kebangkitan-nasional',

        'label' => 'Tema Hari Kebangkitan Nasional',

        'icon' => 'fa-flag',

        'badge' => '20 Mei',

        'ucapan' => 'Selamat Hari Kebangkitan Nasional — Mengenang semangat pergerakan kebangkitan bangsa',

    ],

    [

        'key' => '06-01',

        'class' => 'theme-pancasila',

        'label' => 'Tema Hari Lahir Pancasila',

        'icon' => 'fa-landmark',

        'badge' => 'Pancasila',

        'ucapan' => 'Selamat Hari Lahir Pancasila — Pancasila sebagai dasar dan panduan kehidupan berbangsa',

    ],

    [

        'key' => '08-17',

        'class' => 'theme-kemerdekaan',

        'label' => 'Tema Hari Kemerdekaan RI',

        'icon' => 'fa-flag',

        'badge' => 'HUT RI',

        'ucapan' => 'Dirgahayu Republik Indonesia — Semoga bangsa kita semakin maju, bersatu, dan sejahtera',

    ],

    [

        'key' => '10-01',

        'class' => 'theme-kesaktian-pancasila',

        'label' => 'Tema Hari Kesaktian Pancasila',

        'icon' => 'fa-scale-balanced',

        'badge' => '1 Oktober',

        'ucapan' => 'Selamat Hari Kesaktian Pancasila — Mengukuhkan nilai-nilai luhur Pancasila dalam kehidupan berbangsa',

    ],

    [

        'key' => '10-28',

        'class' => 'theme-sumpah-pemuda',

        'label' => 'Tema Hari Sumpah Pemuda',

        'icon' => 'fa-users',

        'badge' => 'Sumpah Pemuda',

        'ucapan' => 'Selamat Hari Sumpah Pemuda — Satu nusa, satu bangsa, satu bahasa: Indonesia',

    ],

    [

        'key' => '11-10',

        'class' => 'theme-pahlawan',

        'label' => 'Tema Hari Pahlawan',

        'icon' => 'fa-shield-halved',

        'badge' => 'Hari Pahlawan',

        'ucapan' => 'Selamat Hari Pahlawan — Mengenang jasa para pahlawan dan pejuang kemerdekaan bangsa',

    ],

    [

        'key' => '11-25',

        'class' => 'theme-guru',

        'label' => 'Tema Hari Guru Nasional',

        'icon' => 'fa-chalkboard-user',

        'badge' => 'Hari Guru',

        'ucapan' => 'Selamat Hari Guru Nasional — Terima kasih atas dedikasi para pendidik bangsa',

    ],

    [

        'key' => '11-29',

        'class' => 'theme-korpri',

        'label' => 'Tema Hari Korpri',

        'icon' => 'fa-building-columns',

        'badge' => 'Hari Korpri',

        'ucapan' => 'Selamat Hari Korpri — Aparatur sipil negara yang profesional, berintegritas, dan berdedikasi',

    ],

    [

        'key' => '12-22',

        'class' => 'theme-ibu',

        'label' => 'Tema Hari Ibu',

        'icon' => 'fa-heart',

        'badge' => 'Hari Ibu',

        'ucapan' => 'Selamat Hari Ibu — Terima kasih atas kasih sayang dan pengorbanan ibu kita tercinta',

    ],



    // Hari agama (tanggal bergerak => perbarui setiap tahun; duration_days 7 = tema 1 minggu)


    [

        'key' => '2026-03-20',

        'class' => 'theme-idul-fitri',

        'label' => 'Tema Idul Fitri',

        'icon' => 'fa-mosque',

        'badge' => 'Idul Fitri 1447 H',

        'ucapan' => 'Selamat Hari Raya Idul Fitri — Mohon Maaf Lahir dan Batin',

        'duration_days' => 7,

    ],

    [

        'key' => '2026-05-27',

        'class' => 'theme-idul-adha',

        'label' => 'Tema Idul Adha',

        'icon' => 'fa-star-and-crescent',

        'badge' => 'Idul Adha 1447 H',

        'ucapan' => 'Selamat Hari Raya Idul Adha — Semoga ibadah kita diterima Allah SWT',

        'duration_days' => 7,

    ],

    [

        'key' => '12-25',

        'class' => 'theme-natal',

        'label' => 'Tema Natal',

        'icon' => 'fa-gift',

        'badge' => 'Natal',

        'ucapan' => 'Selamat Natal dan Tahun Baru — Semoga damai dan kasih menyertai kita semua',

        'duration_days' => 7,

    ],

];


