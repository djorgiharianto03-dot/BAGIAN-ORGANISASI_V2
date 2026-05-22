<?php

if (!defined('ORG_ROOT')) {
    define('ORG_ROOT', dirname(__DIR__));
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_database.php';

function org_pengumuman_upload_dir_fs(): string
{
    return ORG_ROOT . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'pengumuman';
}

function org_pengumuman_upload_web_prefix(): string
{
    return 'uploads/pengumuman/';
}

function org_pengumuman_ensure_table(mysqli $db): void
{
    $dir = org_pengumuman_upload_dir_fs();
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
    $db->query(
        'CREATE TABLE IF NOT EXISTS `pengumuman` (
          `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `judul` VARCHAR(255) NOT NULL DEFAULT \'\',
          `teks` TEXT NOT NULL,
          `nama_gambar` VARCHAR(255) NOT NULL DEFAULT \'\' COMMENT \'Berkas di uploads/pengumuman/\',
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_pengumuman_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
}

function org_pengumuman_table_exists(mysqli $db): bool
{
    $r = $db->query("SHOW TABLES LIKE 'pengumuman'");
    return $r !== false && $r->num_rows > 0;
}

/**
 * @return list<array{id: string, judul: string, teks: string, nama_gambar: string, created_at: string}>
 */
function org_pengumuman_fetch_all(mysqli $db, int $limit = 50): array
{
    if (!org_pengumuman_table_exists($db)) {
        return [];
    }
    $limit = max(1, min(100, $limit));
    $rows = [];
    $sql = 'SELECT `id`, `judul`, `teks`, `nama_gambar`, `created_at` FROM `pengumuman` ORDER BY `id` DESC LIMIT ' . (int) $limit;
    $res = $db->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            if (is_array($row)) {
                $rows[] = [
                    'id' => (string) ($row['id'] ?? ''),
                    'judul' => (string) ($row['judul'] ?? ''),
                    'teks' => (string) ($row['teks'] ?? ''),
                    'nama_gambar' => (string) ($row['nama_gambar'] ?? ''),
                    'created_at' => (string) ($row['created_at'] ?? ''),
                ];
            }
        }
    }

    return $rows;
}

/**
 * @return array{id: string, judul: string, teks: string, nama_gambar: string}|null
 */
function org_pengumuman_fetch_by_id(mysqli $db, int $id): ?array
{
    if ($id < 1 || !org_pengumuman_table_exists($db)) {
        return null;
    }
    $st = $db->prepare('SELECT `id`, `judul`, `teks`, `nama_gambar` FROM `pengumuman` WHERE `id` = ? LIMIT 1');
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

    return [
        'id' => (string) ($row['id'] ?? ''),
        'judul' => (string) ($row['judul'] ?? ''),
        'teks' => (string) ($row['teks'] ?? ''),
        'nama_gambar' => (string) ($row['nama_gambar'] ?? ''),
    ];
}

function org_pengumuman_insert(mysqli $db, string $judul, string $teks, string $namaGambar): bool
{
    org_pengumuman_ensure_table($db);
    $st = $db->prepare('INSERT INTO `pengumuman` (`judul`, `teks`, `nama_gambar`) VALUES (?, ?, ?)');
    if ($st === false) {
        return false;
    }
    $st->bind_param('sss', $judul, $teks, $namaGambar);
    $ok = $st->execute();
    $st->close();

    return (bool) $ok;
}

function org_pengumuman_delete_file_if_exists(string $namaGambar): void
{
    $namaGambar = basename($namaGambar);
    if ($namaGambar === '' || $namaGambar === '.' || $namaGambar === '..') {
        return;
    }
    $dir = realpath(org_pengumuman_upload_dir_fs());
    if ($dir === false) {
        return;
    }
    $path = $dir . DIRECTORY_SEPARATOR . $namaGambar;
    $real = realpath($path);
    if ($real !== false && is_file($real) && dirname($real) === $dir) {
        @unlink($real);
    }
}

function org_pengumuman_delete_by_id(mysqli $db, int $id): bool
{
    if ($id < 1 || !org_pengumuman_table_exists($db)) {
        return false;
    }
    $row = org_pengumuman_fetch_by_id($db, $id);
    if ($row === null) {
        return false;
    }
    $st = $db->prepare('DELETE FROM `pengumuman` WHERE `id` = ? LIMIT 1');
    if ($st === false) {
        return false;
    }
    $st->bind_param('i', $id);
    $ok = $st->execute();
    $st->close();
    if ($ok) {
        org_pengumuman_delete_file_if_exists($row['nama_gambar']);
    }

    return (bool) $ok;
}
