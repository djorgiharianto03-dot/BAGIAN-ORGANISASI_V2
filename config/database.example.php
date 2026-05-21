<?php
/**
 * Salin berkas ini menjadi database.php dan sesuaikan kredensial MySQL.
 *
 * Laragon: user root, password kosong, nama DB bebas (mis. db_organisasi).
 * CloudPanel: host biasanya 127.0.0.1, user/password dari panel Database.
 * Nama `database` harus sama dengan DB yang diimpor (install/schema.sql memakai db_organisasi).
 *
 * Bootstrap otomatis di index.php (dev saja): ORG_DEV_BOOTSTRAP=1 memaksa aktif, =0 mematikan.
 */
return [
    'host' => '127.0.0.1',
    'user' => 'root',
    'password' => '',
    'database' => 'db_organisasi',
    'charset' => 'utf8mb4',
    /** Opsional: path URL aplikasi jika cookie sesi/CSRF gagal (mis. '/BAGIAN ORGANISASI_V2'). Kosongkan untuk deteksi otomatis. */
    // 'web_root' => '/BAGIAN ORGANISASI_V2',
];
