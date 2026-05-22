<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'site_content_db.php';

/** Path relatif web (dari akar situs) ke folder unggahan galeri. */
const ORG_GALERI_WEB_DIR = 'assets/img/galeri';

/**
 * URL publik ke berkas galeri (hormati subfolder situs / ORG_WEB_ROOT).
 */
function org_galeri_kegiatan_image_url(string $namaFile): string
{
    $file = basename($namaFile);
    if ($file === '' || $file === '.' || $file === '..') {
        return '';
    }
    if (!defined('ORG_WEB_ROOT')) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_database.php';
        define('ORG_WEB_ROOT', org_site_web_root());
    }
    $base = ORG_WEB_ROOT === '' ? '' : rtrim(ORG_WEB_ROOT, '/');
    $prefix = $base === '' ? '' : $base . '/';

    return $prefix . ORG_GALERI_WEB_DIR . '/' . rawurlencode($file);
}

function org_galeri_kegiatan_upload_dir_fs(): string
{
    if (!defined('ORG_ROOT')) {
        define('ORG_ROOT', dirname(__DIR__));
    }

    return ORG_ROOT . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'galeri';
}

/**
 * Gabungkan baris DB dengan berkas di folder yang belum tercatat (instalasi lama / unggahan gagal ke DB).
 *
 * @param list<array<string, mixed>> $rows
 * @return list<array<string, mixed>>
 */
function org_galeri_kegiatan_merge_disk_orphans(array $rows): array
{
    $dir = org_galeri_kegiatan_upload_dir_fs();
    if (!is_dir($dir)) {
        return $rows;
    }
    $known = [];
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $fn = basename((string) ($row['nama_file'] ?? ''));
        if ($fn !== '') {
            $known[strtolower($fn)] = true;
        }
    }
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    foreach (scandir($dir) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..' || $entry === '.gitkeep') {
            continue;
        }
        $full = $dir . DIRECTORY_SEPARATOR . $entry;
        if (!is_file($full)) {
            continue;
        }
        $ext = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) {
            continue;
        }
        if (isset($known[strtolower($entry)])) {
            continue;
        }
        $mtime = filemtime($full);
        $judulOrphan = trim((string) preg_replace('/^gal_\d{8}_\d{6}_[a-f0-9]+$/i', '', pathinfo($entry, PATHINFO_FILENAME)));
        if ($judulOrphan === '') {
            $judulOrphan = 'Foto kegiatan';
        }
        $rows[] = [
            'id' => 0,
            'judul' => $judulOrphan,
            'nama_file' => $entry,
            'tgl_upload' => $mtime !== false ? date('Y-m-d H:i:s', $mtime) : date('Y-m-d H:i:s'),
        ];
    }
    usort($rows, static function (array $a, array $b): int {
        $ta = strtotime((string) ($a['tgl_upload'] ?? '')) ?: 0;
        $tb = strtotime((string) ($b['tgl_upload'] ?? '')) ?: 0;

        return $tb <=> $ta;
    });

    return $rows;
}

/**
 * Hanya entri yang berkas gambarnya benar-benar ada di server.
 *
 * @param list<array<string, mixed>> $rows
 * @return list<array<string, mixed>>
 */
function org_galeri_kegiatan_filter_displayable(array $rows): array
{
    $dir = org_galeri_kegiatan_upload_dir_fs();
    $out = [];
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $fn = basename((string) ($row['nama_file'] ?? ''));
        if ($fn === '' || $fn === '.' || $fn === '..') {
            continue;
        }
        $full = $dir . DIRECTORY_SEPARATOR . $fn;
        if (!is_file($full)) {
            continue;
        }
        if (org_galeri_kegiatan_image_url($fn) === '') {
            continue;
        }
        $judul = trim((string) ($row['judul'] ?? ''));
        if ($judul === '') {
            $row['judul'] = 'Foto kegiatan';
        }
        $out[] = $row;
    }

    return $out;
}

/**
 * Data galeri untuk halaman publik: DB + berkas di folder uploads.
 */
function org_galeri_kegiatan_load_public(?mysqli $db = null): array
{
    $rows = [];
    if ($db instanceof mysqli && org_galeri_kegiatan_table_exists($db)) {
        $rows = org_galeri_kegiatan_fetch_all($db);
    }

    return org_galeri_kegiatan_filter_displayable(org_galeri_kegiatan_merge_disk_orphans($rows));
}

function org_galeri_ensure_table(mysqli $db): void
{
    $db->query(
        'CREATE TABLE IF NOT EXISTS `galeri` (
          `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `judul` VARCHAR(255) NOT NULL DEFAULT \'\',
          `nama_file` VARCHAR(255) NOT NULL DEFAULT \'\',
          `tgl_upload` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_galeri_tgl` (`tgl_upload`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
}

function org_galeri_kegiatan_table_exists(mysqli $db): bool
{
    $r = $db->query("SHOW TABLES LIKE 'galeri'");
    return $r !== false && $r->num_rows > 0;
}

/**
 * @return list<array<string, string>>
 */
function org_galeri_kegiatan_fetch_all(mysqli $db): array
{
    if (!org_galeri_kegiatan_table_exists($db)) {
        return [];
    }
    $rows = [];
    $res = $db->query('SELECT `id`, `judul`, `nama_file`, `tgl_upload` FROM `galeri` ORDER BY `tgl_upload` DESC, `id` DESC');
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            if (is_array($row)) {
                $rows[] = $row;
            }
        }
    }
    return $rows;
}

/**
 * @return array<string, string>|null
 */
function org_galeri_kegiatan_fetch_by_id(mysqli $db, int $id): ?array
{
    if (!org_galeri_kegiatan_table_exists($db)) {
        return null;
    }
    $st = $db->prepare('SELECT `id`, `judul`, `nama_file`, `tgl_upload` FROM `galeri` WHERE `id` = ? LIMIT 1');
    if ($st === false) {
        return null;
    }
    $st->bind_param('i', $id);
    if (!$st->execute()) {
        $st->close();
        return null;
    }
    $res = $st->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $st->close();
    return is_array($row) ? $row : null;
}

function org_galeri_kegiatan_insert(mysqli $db, string $judul, string $namaFile): bool
{
    if (!org_galeri_kegiatan_table_exists($db)) {
        return false;
    }
    $st = $db->prepare('INSERT INTO `galeri` (`judul`, `nama_file`) VALUES (?, ?)');
    if ($st === false) {
        return false;
    }
    $st->bind_param('ss', $judul, $namaFile);
    $ok = $st->execute();
    $st->close();
    return $ok;
}

function org_galeri_kegiatan_delete_by_id(mysqli $db, int $id): bool
{
    if (!org_galeri_kegiatan_table_exists($db)) {
        return false;
    }
    $st = $db->prepare('DELETE FROM `galeri` WHERE `id` = ?');
    if ($st === false) {
        return false;
    }
    $st->bind_param('i', $id);
    $ok = $st->execute();
    $st->close();
    return $ok;
}

function org_galeri_kegiatan_update_judul_by_id(mysqli $db, int $id, string $judul): bool
{
    if ($id < 1 || !org_galeri_kegiatan_table_exists($db)) {
        return false;
    }
    $st = $db->prepare('UPDATE `galeri` SET `judul` = ? WHERE `id` = ? LIMIT 1');
    if ($st === false) {
        return false;
    }
    $st->bind_param('si', $judul, $id);
    $ok = $st->execute();
    $st->close();

    return (bool) $ok;
}
