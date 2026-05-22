<?php
/**
 * cek_db.php — persiapan database otomatis untuk aplikasi Bagian Organisasi
 *
 * Yang dilakukan:
 *  1) Membaca config/database.php
 *  2) Membuat basis data jika belum ada
 *  3) Membuat tabel: site_content, audit_logs, saran_pengunjung, saran_kritik, galeri
 *  4) Mengisi baris awal site_content (id=1) jika tabel masih kosong
 *
 * Keamanan: setelah database berjalan, hapus berkas ini dari server produksi
 *           atau batasi akses (htaccess / firewall), karena skrip ini
 *           memakai kredensial dari config.
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_database.php';

if (!org_is_dev_environment()) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "cek_db.php hanya untuk lingkungan development (localhost / Laragon).\n";
    echo "Di server production: impor install/*.sql lewat phpMyAdmin atau CLI.\n";
    exit;
}

header('Content-Type: text/html; charset=UTF-8');

$root = __DIR__;
$configPath = $root . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php';
$siteSettingsPath = $root . DIRECTORY_SEPARATOR . 'site_settings.json';

$log = [];

function log_line(array &$log, string $type, string $message): void
{
    $log[] = ['type' => $type, 'message' => $message];
}

// -----------------------------------------------------------------------------
// 1) Muat konfigurasi
// -----------------------------------------------------------------------------
if (!is_file($configPath)) {
    log_line($log, 'error', 'Berkas config/database.php tidak ditemukan. Salin dari config/database.example.php.');
    goto render;
}

$cfg = require $configPath;
if (!is_array($cfg)) {
    log_line($log, 'error', 'config/database.php harus mengembalikan array.');
    goto render;
}

$host = (string) ($cfg['host'] ?? '127.0.0.1');
$user = (string) ($cfg['user'] ?? 'root');
$pass = (string) ($cfg['password'] ?? '');
$dbName = (string) ($cfg['database'] ?? '');
$charset = (string) ($cfg['charset'] ?? 'utf8mb4');

if ($dbName === '') {
    log_line($log, 'error', 'Nama database (key "database") kosong di config/database.php.');
    goto render;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // -----------------------------------------------------------------------------
    // 2) Koneksi tanpa memilih database (agar bisa CREATE DATABASE)
    // -----------------------------------------------------------------------------
    $mysqli = new mysqli($host, $user, $pass);
    $mysqli->set_charset($charset);
    log_line($log, 'ok', 'Terhubung ke server MySQL.');

    // -----------------------------------------------------------------------------
    // 3) Buat basis data bila belum ada
    // -----------------------------------------------------------------------------
    $dbNameEsc = '`' . str_replace('`', '``', $dbName) . '`';
    $mysqli->query(
        "CREATE DATABASE IF NOT EXISTS {$dbNameEsc} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
    );
    log_line($log, 'ok', 'Basis data "' . htmlspecialchars($dbName, ENT_QUOTES, 'UTF-8') . '" siap (dibuat jika belum ada).');

    if (!$mysqli->select_db($dbName)) {
        throw new RuntimeException('Gagal memilih basis data: ' . $mysqli->error);
    }

    // -----------------------------------------------------------------------------
    // 4) Buat tabel site_content
    // -----------------------------------------------------------------------------
    $mysqli->query(
        "CREATE TABLE IF NOT EXISTS `site_content` (
          `id` TINYINT UNSIGNED NOT NULL PRIMARY KEY,
          `profile_visi` MEDIUMTEXT NOT NULL,
          `profile_misi` MEDIUMTEXT NOT NULL,
          `profile_struktur` TEXT NOT NULL,
          `struktur_blurb` TEXT NOT NULL,
          `organisasi_intro` TEXT NOT NULL,
          `pengumuman` TEXT NOT NULL,
          `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
    log_line($log, 'ok', 'Tabel site_content siap.');

    // -----------------------------------------------------------------------------
    // 5) Buat tabel audit_logs
    // -----------------------------------------------------------------------------
    $mysqli->query(
        "CREATE TABLE IF NOT EXISTS `audit_logs` (
          `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `id_admin` VARCHAR(64) NOT NULL,
          `nama_admin` VARCHAR(191) NOT NULL,
          `aksi` VARCHAR(512) NOT NULL,
          `waktu` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          KEY `idx_waktu` (`waktu`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
    log_line($log, 'ok', 'Tabel audit_logs siap.');

    // -----------------------------------------------------------------------------
    // 6) Buat tabel saran_pengunjung
    // -----------------------------------------------------------------------------
    $mysqli->query(
        "CREATE TABLE IF NOT EXISTS `saran_pengunjung` (
          `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `nama` VARCHAR(191) NOT NULL,
          `email` VARCHAR(191) NOT NULL,
          `pesan` TEXT NOT NULL,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
    log_line($log, 'ok', 'Tabel saran_pengunjung siap.');

    // -----------------------------------------------------------------------------
    // 6a) Buat tabel saran_kritik (footer / AJAX)
    // -----------------------------------------------------------------------------
    $mysqli->query(
        "CREATE TABLE IF NOT EXISTS `saran_kritik` (
          `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
          `nama` VARCHAR(191) NOT NULL,
          `email` VARCHAR(191) NOT NULL,
          `pesan` TEXT NOT NULL,
          `tgl_kirim` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_saran_kritik_tgl` (`tgl_kirim`),
          KEY `idx_saran_kritik_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
    log_line($log, 'ok', 'Tabel saran_kritik siap.');

    // -----------------------------------------------------------------------------
    // 6a-2) Metadata dokumen (Digital Library) + penghitung unduhan
    // -----------------------------------------------------------------------------
    $mysqli->query(
        "CREATE TABLE IF NOT EXISTS `dokumen` (
          `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
          `nama_file` VARCHAR(255) NOT NULL COMMENT 'Nama file di folder uploads',
          `kategori` VARCHAR(100) NOT NULL DEFAULT 'Umum',
          `jumlah_unduh` INT UNSIGNED NOT NULL DEFAULT 0,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uniq_dokumen_nama_file` (`nama_file`),
          KEY `idx_dokumen_unduh` (`jumlah_unduh`),
          KEY `idx_dokumen_kategori` (`kategori`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
    log_line($log, 'ok', 'Tabel dokumen (metadata unduhan) siap.');

    // -----------------------------------------------------------------------------
    // 6a-3) Pengumuman & brosur (gambar di uploads/pengumuman/)
    // -----------------------------------------------------------------------------
    $mysqli->query(
        "CREATE TABLE IF NOT EXISTS `pengumuman` (
          `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `judul` VARCHAR(255) NOT NULL DEFAULT '',
          `teks` TEXT NOT NULL,
          `nama_gambar` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Berkas di uploads/pengumuman/',
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_pengumuman_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
    log_line($log, 'ok', 'Tabel pengumuman siap.');

    // -----------------------------------------------------------------------------
    // 6a-4) Pusat Informasi & Pengumuman (beranda; gambar di uploads/pusat_informasi/)
    // -----------------------------------------------------------------------------
    $mysqli->query(
        "CREATE TABLE IF NOT EXISTS `pusat_informasi` (
          `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `judul` VARCHAR(255) NOT NULL DEFAULT '',
          `kategori` VARCHAR(32) NOT NULL DEFAULT 'berita' COMMENT 'berita | pengumuman',
          `isi_teks` TEXT NOT NULL,
          `nama_gambar` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Berkas di uploads/pusat_informasi/',
          `is_featured` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Berita utama / pin beranda',
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_pusat_informasi_created` (`created_at`),
          KEY `idx_pusat_informasi_kategori` (`kategori`),
          KEY `idx_pusat_informasi_featured_created` (`is_featured`, `created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
    log_line($log, 'ok', 'Tabel pusat_informasi siap.');
    $chkFeat = $mysqli->query("SHOW COLUMNS FROM `pusat_informasi` LIKE 'is_featured'");
    if ($chkFeat && $chkFeat->num_rows === 0) {
        $mysqli->query(
            'ALTER TABLE `pusat_informasi` ADD COLUMN `is_featured` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT \'Berita utama / pin beranda\' AFTER `nama_gambar`'
        );
        $mysqli->query('ALTER TABLE `pusat_informasi` ADD KEY `idx_pusat_informasi_featured_created` (`is_featured`, `created_at`)');
        log_line($log, 'ok', 'Kolom pusat_informasi.is_featured ditambahkan.');
    }

    // -----------------------------------------------------------------------------
    // 6b) Buat tabel galeri (foto kegiatan)
    // -----------------------------------------------------------------------------
    $mysqli->query(
        "CREATE TABLE IF NOT EXISTS `galeri` (
          `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `judul` VARCHAR(255) NOT NULL DEFAULT '',
          `nama_file` VARCHAR(255) NOT NULL DEFAULT '',
          `tgl_upload` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_galeri_tgl` (`tgl_upload`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
    log_line($log, 'ok', 'Tabel galeri siap.');

    // -----------------------------------------------------------------------------
    // 7) Isi baris default site_content (id=1) jika belum ada
    // -----------------------------------------------------------------------------
    $check = $mysqli->query('SELECT COUNT(*) AS c FROM site_content WHERE id = 1 LIMIT 1');
    $row = $check ? $check->fetch_assoc() : null;
    $count = isset($row['c']) ? (int) $row['c'] : 0;

    if ($count === 0) {
        $defaults = [
            'profile_visi' => 'Menjadi bagian organisasi yang profesional, transparan, dan adaptif dalam pelayanan informasi.',
            'profile_misi' => 'Mengelola data dan dokumen organisasi secara efektif, akurat, dan mudah diakses oleh pihak terkait.',
            'profile_struktur' => 'Kepala Bagian, Subbag Umum, Subbag Dokumentasi, dan Tim Dukungan Administrasi.',
            'struktur_blurb' => 'Daftar personel Bagian Organisasi ditampilkan secara dinamis. Foto akan otomatis diambil dari folder uploads, dan memakai placeholder jika file belum tersedia.',
            'organisasi_intro' => 'Terima kasih telah mengunjungi website organisasi kami. Halaman ini dibuat untuk memudahkan anggota dalam menerima informasi dan mengelola dokumen.',
            'pengumuman' => '',
        ];
        if (is_file($siteSettingsPath)) {
            $json = file_get_contents($siteSettingsPath);
            if ($json !== false && $json !== '') {
                $decoded = json_decode($json, true);
                if (is_array($decoded)) {
                    $defaults = array_merge($defaults, $decoded);
                }
            }
        }

        $st = $mysqli->prepare(
            'INSERT INTO site_content (id, profile_visi, profile_misi, profile_struktur, struktur_blurb, organisasi_intro, pengumuman)
             VALUES (1, ?, ?, ?, ?, ?, ?)'
        );
        if ($st === false) {
            throw new RuntimeException('Prepare insert site_content gagal: ' . $mysqli->error);
        }
        $st->bind_param(
            'ssssss',
            $defaults['profile_visi'],
            $defaults['profile_misi'],
            $defaults['profile_struktur'],
            $defaults['struktur_blurb'],
            $defaults['organisasi_intro'],
            $defaults['pengumuman']
        );
        $st->execute();
        $st->close();
        log_line($log, 'ok', 'Baris awal site_content (id=1) berhasil diisi dari default / site_settings.json.');
    } else {
        log_line($log, 'info', 'Baris site_content id=1 sudah ada — tidak diubah.');
    }

    log_line($log, 'ok', 'Selesai. Muat ulang halaman utama atau dashboard; pesan "Database belum siap" seharusnya hilang.');
} catch (Throwable $e) {
    log_line($log, 'error', $e->getMessage());
}

render:
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cek &amp; buat database</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 52rem; margin: 2rem auto; padding: 0 1rem; line-height: 1.5; }
        h1 { font-size: 1.25rem; }
        ul { list-style: none; padding: 0; }
        li { margin: 0.35rem 0; padding: 0.35rem 0.5rem; border-radius: 6px; }
        .ok { background: #ecfdf5; color: #065f46; }
        .info { background: #f1f5f9; color: #334155; }
        .error { background: #fef2f2; color: #991b1b; }
        .hint { color: #64748b; font-size: 0.9rem; margin-top: 2rem; }
        a { color: #0369a1; }
    </style>
</head>
<body>
    <h1>cek_db.php — hasil</h1>
    <ul>
        <?php foreach ($log as $item): ?>
            <?php
            $cls = htmlspecialchars($item['type'], ENT_QUOTES, 'UTF-8');
            $msg = htmlspecialchars($item['message'], ENT_QUOTES, 'UTF-8');
            ?>
            <li class="<?php echo $cls; ?>"><?php echo $msg; ?></li>
        <?php endforeach; ?>
    </ul>
    <p class="hint">
        Jika ada error koneksi, pastikan layanan MySQL di Laragon menyala dan isian
        <code>config/database.php</code> benar (host, user, password, database).<br>
        Setelah sukses, pertimbangkan untuk <strong>menghapus cek_db.php</strong> di lingkungan produksi.
    </p>
    <p><a href="index.php">Kembali ke beranda</a> · <a href="admin/dashboard.php">Dashboard admin</a></p>
</body>
</html>
