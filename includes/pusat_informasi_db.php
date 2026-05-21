<?php
declare(strict_types=1);

if (!defined('ORG_ROOT')) {
    define('ORG_ROOT', dirname(__DIR__));
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_database.php';

function org_pusat_informasi_upload_dir_fs(): string
{
    return ORG_ROOT . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'pusat_informasi';
}

function org_pusat_informasi_upload_web_prefix(): string
{
    return 'uploads/pusat_informasi/';
}

/**
 * Tambahkan kolom is_featured pada instalasi lama (idempotent).
 */
function org_pusat_informasi_ensure_featured_column(mysqli $db): void
{
    if (!org_pusat_informasi_table_exists($db)) {
        return;
    }
    $r = $db->query("SHOW COLUMNS FROM `pusat_informasi` LIKE 'is_featured'");
    if ($r !== false && $r->num_rows > 0) {
        return;
    }
    $db->query(
        'ALTER TABLE `pusat_informasi` ADD COLUMN `is_featured` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT \'Berita utama / pin beranda\' AFTER `nama_gambar`'
    );
    $db->query('ALTER TABLE `pusat_informasi` ADD KEY `idx_pusat_informasi_featured_created` (`is_featured`, `created_at`)');
}

/**
 * @param array<string, mixed> $row
 * @return array{id: string, judul: string, kategori: string, isi_teks: string, nama_gambar: string, created_at: string, is_featured: int}
 */
function org_pusat_informasi_normalize_row(array $row): array
{
    return [
        'id' => (string) ($row['id'] ?? ''),
        'judul' => (string) ($row['judul'] ?? ''),
        'kategori' => strtolower((string) ($row['kategori'] ?? 'berita')),
        'isi_teks' => (string) ($row['isi_teks'] ?? ''),
        'nama_gambar' => (string) ($row['nama_gambar'] ?? ''),
        'created_at' => (string) ($row['created_at'] ?? ''),
        'is_featured' => (int) ($row['is_featured'] ?? 0),
    ];
}

function org_pusat_informasi_ensure_table(mysqli $db): void
{
    $dir = org_pusat_informasi_upload_dir_fs();
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
    $db->query(
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
    org_pusat_informasi_ensure_featured_column($db);
}

function org_pusat_informasi_table_exists(mysqli $db): bool
{
    $r = $db->query("SHOW TABLES LIKE 'pusat_informasi'");

    return $r !== false && $r->num_rows > 0;
}

/**
 * Urutan: berita utama dulu, lalu tanggal terbaru.
 *
 * @return list<array{id: string, judul: string, kategori: string, isi_teks: string, nama_gambar: string, created_at: string, is_featured: int}>
 */
function org_pusat_informasi_fetch_all(mysqli $db, int $limit = 24): array
{
    if (!org_pusat_informasi_table_exists($db)) {
        return [];
    }
    org_pusat_informasi_ensure_table($db);
    $limit = max(1, min(100, $limit));
    $rows = [];
    $sql = 'SELECT `id`, `judul`, `kategori`, `isi_teks`, `nama_gambar`, `created_at`, `is_featured` FROM `pusat_informasi` ORDER BY `is_featured` DESC, `created_at` DESC LIMIT ' . (int) $limit;
    $res = $db->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            if (is_array($row)) {
                $rows[] = org_pusat_informasi_normalize_row($row);
            }
        }
    }

    return $rows;
}

/**
 * Beranda: hingga $maxFeatured entri ber-status utama (terbaru), lalu sisanya mengisi sampai $maxTotal.
 *
 * @return list<array{id: string, judul: string, kategori: string, isi_teks: string, nama_gambar: string, created_at: string, is_featured: int}>
 */
function org_pusat_informasi_fetch_for_beranda(mysqli $db, int $maxFeatured = 4, int $maxTotal = 12): array
{
    if (!org_pusat_informasi_table_exists($db)) {
        return [];
    }
    org_pusat_informasi_ensure_table($db);
    $maxFeatured = max(1, min(12, $maxFeatured));
    $maxTotal = max(1, min(48, $maxTotal));
    if ($maxTotal < $maxFeatured) {
        $maxTotal = $maxFeatured;
    }

    $limF = (int) $maxFeatured;
    $sqlF = 'SELECT `id`, `judul`, `kategori`, `isi_teks`, `nama_gambar`, `created_at`, `is_featured` FROM `pusat_informasi` WHERE `is_featured` = 1 ORDER BY `created_at` DESC LIMIT ' . $limF;
    $featured = [];
    $resF = $db->query($sqlF);
    if ($resF) {
        while ($row = $resF->fetch_assoc()) {
            if (is_array($row)) {
                $featured[] = org_pusat_informasi_normalize_row($row);
            }
        }
    }

    $ids = [];
    foreach ($featured as $fr) {
        $ids[] = (int) $fr['id'];
    }

    $restLimit = $maxTotal - count($featured);
    if ($restLimit <= 0) {
        return array_slice($featured, 0, $maxTotal);
    }

    $rest = [];
    if (count($ids) === 0) {
        $sqlR = 'SELECT `id`, `judul`, `kategori`, `isi_teks`, `nama_gambar`, `created_at`, `is_featured` FROM `pusat_informasi` ORDER BY `created_at` DESC LIMIT ' . (int) $restLimit;
        $resR = $db->query($sqlR);
        if ($resR) {
            while ($row = $resR->fetch_assoc()) {
                if (is_array($row)) {
                    $rest[] = org_pusat_informasi_normalize_row($row);
                }
            }
        }
    } else {
        $idsSql = implode(',', array_map(static fn (int $x): string => (string) $x, $ids));
        $sqlR = 'SELECT `id`, `judul`, `kategori`, `isi_teks`, `nama_gambar`, `created_at`, `is_featured` FROM `pusat_informasi` WHERE `id` NOT IN (' . $idsSql . ') ORDER BY `created_at` DESC LIMIT ' . (int) $restLimit;
        $resR = $db->query($sqlR);
        if ($resR) {
            while ($row = $resR->fetch_assoc()) {
                if (is_array($row)) {
                    $rest[] = org_pusat_informasi_normalize_row($row);
                }
            }
        }
    }

    return array_merge($featured, $rest);
}

/**
 * @return array{id: string, judul: string, kategori: string, isi_teks: string, nama_gambar: string, created_at: string, is_featured: int}|null
 */
function org_pusat_informasi_fetch_by_id(mysqli $db, int $id): ?array
{
    if ($id < 1 || !org_pusat_informasi_table_exists($db)) {
        return null;
    }
    org_pusat_informasi_ensure_table($db);
    $st = $db->prepare('SELECT `id`, `judul`, `kategori`, `isi_teks`, `nama_gambar`, `created_at`, `is_featured` FROM `pusat_informasi` WHERE `id` = ? LIMIT 1');
    if ($st === false) {
        return null;
    }
    $st->bind_param('i', $id);
    $st->execute();
    $res = $st->get_result();
    $row = $res !== false ? $res->fetch_assoc() : null;
    $st->close();
    if (!is_array($row)) {
        return null;
    }

    return org_pusat_informasi_normalize_row($row);
}

function org_pusat_informasi_insert(mysqli $db, string $judul, string $kategori, string $isiTeks, string $namaGambar): bool
{
    org_pusat_informasi_ensure_table($db);
    $kat = $kategori === 'pengumuman' ? 'pengumuman' : 'berita';
    $st = $db->prepare('INSERT INTO `pusat_informasi` (`judul`, `kategori`, `isi_teks`, `nama_gambar`) VALUES (?, ?, ?, ?)');
    if ($st === false) {
        return false;
    }
    $st->bind_param('ssss', $judul, $kat, $isiTeks, $namaGambar);
    $ok = $st->execute();
    $st->close();

    return (bool) $ok;
}

function org_pusat_informasi_set_featured(mysqli $db, int $id, bool $featured): bool
{
    if ($id < 1) {
        return false;
    }
    org_pusat_informasi_ensure_table($db);
    if (!org_pusat_informasi_table_exists($db)) {
        return false;
    }
    $v = $featured ? 1 : 0;
    $st = $db->prepare('UPDATE `pusat_informasi` SET `is_featured` = ? WHERE `id` = ? LIMIT 1');
    if ($st === false) {
        return false;
    }
    $st->bind_param('ii', $v, $id);
    $ok = $st->execute();
    $st->close();

    return (bool) $ok;
}

function org_pusat_informasi_update_text_by_id(mysqli $db, int $id, string $judul, string $kategori, string $isiTeks): bool
{
    if ($id < 1 || !org_pusat_informasi_table_exists($db)) {
        return false;
    }
    org_pusat_informasi_ensure_table($db);
    $kat = $kategori === 'pengumuman' ? 'pengumuman' : 'berita';
    $st = $db->prepare('UPDATE `pusat_informasi` SET `judul` = ?, `kategori` = ?, `isi_teks` = ? WHERE `id` = ? LIMIT 1');
    if ($st === false) {
        return false;
    }
    $st->bind_param('sssi', $judul, $kat, $isiTeks, $id);
    $ok = $st->execute();
    $st->close();

    return (bool) $ok;
}

function org_pusat_informasi_delete_file_if_exists(string $namaGambar): void
{
    $namaGambar = basename($namaGambar);
    if ($namaGambar === '' || $namaGambar === '.' || $namaGambar === '..') {
        return;
    }
    $dir = realpath(org_pusat_informasi_upload_dir_fs());
    if ($dir === false) {
        return;
    }
    $path = $dir . DIRECTORY_SEPARATOR . $namaGambar;
    $real = realpath($path);
    if ($real !== false && is_file($real) && dirname($real) === $dir) {
        @unlink($real);
    }
}

function org_pusat_informasi_delete_by_id(mysqli $db, int $id): bool
{
    if ($id < 1 || !org_pusat_informasi_table_exists($db)) {
        return false;
    }
    $row = org_pusat_informasi_fetch_by_id($db, $id);
    if ($row === null) {
        return false;
    }
    $st = $db->prepare('DELETE FROM `pusat_informasi` WHERE `id` = ? LIMIT 1');
    if ($st === false) {
        return false;
    }
    $st->bind_param('i', $id);
    $ok = $st->execute();
    $st->close();
    if ($ok) {
        org_pusat_informasi_delete_file_if_exists($row['nama_gambar']);
    }

    return (bool) $ok;
}
