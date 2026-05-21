<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'staff_users_db.php';

/**
 * HTML dari editor: izinkan tag aman umum, buang script/style/event.
 */
function org_sanitize_rich_html(string $html): string
{
    $allowed = '<p><br><br/><strong><b><em><i><u><ul><ol><li><a><h1><h2><h3><h4><blockquote><span><div>';
    return strip_tags($html, $allowed);
}

/**
 * Sanitasi teks biasa (bukan HTML) untuk disimpan.
 */
function org_sanitize_plain(string $s): string
{
    return trim(strip_tags($s));
}

/**
 * @return array<string, string>|null
 */
function org_site_content_fetch(mysqli $db): ?array
{
    if (!org_site_content_table_exists($db)) {
        return null;
    }
    $res = $db->query('SELECT profile_visi, profile_misi, profile_struktur, struktur_blurb, organisasi_intro, pengumuman FROM site_content WHERE id = 1 LIMIT 1');
    if (!$res || $res->num_rows === 0) {
        return null;
    }
    $row = $res->fetch_assoc();
    return is_array($row) ? $row : null;
}

/**
 * Buat tabel site_content & audit_logs bila belum ada, lalu isi baris default id=1 jika kosong.
 */
function org_site_content_ensure_installed(mysqli $db): void
{
    $db->query(
        'CREATE TABLE IF NOT EXISTS `site_content` (
          `id` TINYINT UNSIGNED NOT NULL,
          `profile_visi` MEDIUMTEXT NOT NULL,
          `profile_misi` MEDIUMTEXT NOT NULL,
          `profile_struktur` TEXT NOT NULL,
          `struktur_blurb` TEXT NOT NULL,
          `organisasi_intro` TEXT NOT NULL,
          `pengumuman` TEXT NOT NULL,
          `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    $db->query(
        'CREATE TABLE IF NOT EXISTS `audit_logs` (
          `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
          `id_admin` VARCHAR(64) NOT NULL,
          `nama_admin` VARCHAR(191) NOT NULL,
          `aksi` VARCHAR(512) NOT NULL,
          `waktu` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_waktu` (`waktu`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    $check = $db->query('SELECT COUNT(*) AS `c` FROM `site_content` WHERE `id` = 1 LIMIT 1');
    if (!$check) {
        return;
    }
    $row = $check->fetch_assoc();
    $check->free();
    if (isset($row['c']) && (int) $row['c'] > 0) {
        return;
    }
    $visi = 'Menjadi bagian organisasi yang profesional, transparan, dan adaptif dalam pelayanan informasi.';
    $misi = 'Mengelola data dan dokumen organisasi secara efektif, akurat, dan mudah diakses oleh pihak terkait.';
    $struktur = 'Kepala Bagian, Subbag Umum, Subbag Dokumentasi, dan Tim Dukungan Administrasi.';
    $blurb = 'Daftar personel Bagian Organisasi ditampilkan secara dinamis. Foto akan otomatis diambil dari folder uploads, dan memakai placeholder jika file belum tersedia.';
    $intro = 'Terima kasih telah mengunjungi website organisasi kami. Halaman ini dibuat untuk memudahkan anggota dalam menerima informasi dan mengelola dokumen.';
    $pengumuman = '';
    $st = $db->prepare(
        'INSERT INTO `site_content` (`id`, `profile_visi`, `profile_misi`, `profile_struktur`, `struktur_blurb`, `organisasi_intro`, `pengumuman`)
         VALUES (1, ?, ?, ?, ?, ?, ?)'
    );
    if ($st === false) {
        return;
    }
    $st->bind_param('ssssss', $visi, $misi, $struktur, $blurb, $intro, $pengumuman);
    $st->execute();
    $st->close();
}

/**
 * Ambil baris audit_logs untuk ditampilkan: sembunyikan entri akun Si Bos/sibos dan entri dari user ber-level super_admin.
 *
 * @return list<array<string, string>>
 */
function org_audit_logs_fetch_visible_rows(mysqli $db, int $limit = 40): array
{
    $limit = max(1, min(500, $limit));
    $rows = [];
    $chk = $db->query("SHOW TABLES LIKE 'audit_logs'");
    if (!$chk || $chk->num_rows === 0) {
        return $rows;
    }
    if (org_staff_users_table_exists($db)) {
        $levelCol = org_staff_users_level_column($db);
        if ($levelCol !== 'level' && $levelCol !== 'role') {
            $levelCol = 'level';
        }
        $sql = 'SELECT a.`id_admin`, a.`nama_admin`, a.`aksi`, a.`waktu`
                FROM `audit_logs` a
                LEFT JOIN `users` u ON LOWER(TRIM(u.`username`)) = LOWER(TRIM(a.`id_admin`))
                WHERE LOWER(REPLACE(TRIM(a.`id_admin`), \' \', \'\')) <> \'sibos\'
                  AND (u.`id` IS NULL OR LOWER(TRIM(COALESCE(u.`' . $levelCol . '`, \'\'))) NOT IN (\'super_admin\', \'super admin\'))
                ORDER BY a.`waktu` DESC
                LIMIT ?';
    } else {
        $sql = 'SELECT `id_admin`, `nama_admin`, `aksi`, `waktu`
                FROM `audit_logs`
                WHERE LOWER(REPLACE(TRIM(`id_admin`), \' \', \'\')) <> \'sibos\'
                ORDER BY `waktu` DESC
                LIMIT ?';
    }
    $st = $db->prepare($sql);
    if ($st === false) {
        return $rows;
    }
    $st->bind_param('i', $limit);
    if (!$st->execute()) {
        $st->close();
        return $rows;
    }
    $res = $st->get_result();
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            if (is_array($row)) {
                $rows[] = $row;
            }
        }
        $res->free();
    }
    $st->close();
    return $rows;
}

function org_audit_log_insert(mysqli $db, string $idAdmin, string $namaAdmin, string $aksi): bool
{
    $idE = $db->real_escape_string($idAdmin);
    $namaE = $db->real_escape_string($namaAdmin);
    $aksiE = $db->real_escape_string($aksi);
    $sql = "INSERT INTO audit_logs (id_admin, nama_admin, aksi, waktu) VALUES ('{$idE}', '{$namaE}', '{$aksiE}', NOW())";
    return (bool) $db->query($sql);
}

/**
 * Simpan konten situs + salin JSON + audit. Visi/Misi boleh HTML ringan.
 *
 * @param array<string, string> $data
 */
function org_site_content_save_full(mysqli $db, array $data, string $idAdmin, string $namaAdmin, string $siteSettingsFile): bool
{
    $visi = org_sanitize_rich_html((string) ($data['profile_visi'] ?? ''));
    $misi = org_sanitize_rich_html((string) ($data['profile_misi'] ?? ''));
    $struktur = org_sanitize_plain((string) ($data['profile_struktur'] ?? ''));
    $blurb = org_sanitize_plain((string) ($data['struktur_blurb'] ?? ''));
    $intro = org_sanitize_plain((string) ($data['organisasi_intro'] ?? ''));
    $pengumuman = org_sanitize_plain((string) ($data['pengumuman'] ?? ''));

    $st = $db->prepare(
        'INSERT INTO site_content (id, profile_visi, profile_misi, profile_struktur, struktur_blurb, organisasi_intro, pengumuman)
         VALUES (1, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE profile_visi = VALUES(profile_visi), profile_misi = VALUES(profile_misi),
         profile_struktur = VALUES(profile_struktur), struktur_blurb = VALUES(struktur_blurb),
         organisasi_intro = VALUES(organisasi_intro), pengumuman = VALUES(pengumuman), updated_at = NOW()'
    );
    if ($st === false) {
        return false;
    }
    $st->bind_param('ssssss', $visi, $misi, $struktur, $blurb, $intro, $pengumuman);
    if (!$st->execute()) {
        $st->close();
        return false;
    }
    $st->close();

    $merged = [
        'profile_visi' => $visi,
        'profile_misi' => $misi,
        'profile_struktur' => $struktur,
        'struktur_blurb' => $blurb,
        'organisasi_intro' => $intro,
        'pengumuman' => $pengumuman,
    ];
    file_put_contents(
        $siteSettingsFile,
        json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );

    org_audit_log_insert($db, $idAdmin, $namaAdmin, 'Memperbarui konten halaman (Visi, Misi, Struktur, pengumuman).');
    return true;
}

function org_is_nonempty_html(string $html): bool
{
    $plain = trim(html_entity_decode(strip_tags($html, '<p><br><br/><strong><b><em><i><u><ul><ol><li><a><h1><h2><h3><h4><blockquote><span><div>'), ENT_QUOTES, 'UTF-8'));
    $plain = str_replace(["\xc2\xa0", '&nbsp;'], ' ', $plain);
    $plain = trim(preg_replace('/\s+/u', ' ', $plain));
    return $plain !== '';
}

/**
 * Validasi: kolom wajib tidak boleh kosong (pengumuman boleh kosong).
 *
 * @param array<string, string> $post
 * @return array{0: bool, 1: string}
 */
function org_validate_site_content_post(array $post): array
{
    $rich = ['profile_visi', 'profile_misi'];
    foreach ($rich as $k) {
        if (!org_is_nonempty_html((string) ($post[$k] ?? ''))) {
            return [false, 'Field ' . $k . ' (Visi/Misi) tidak boleh kosong.'];
        }
    }
    $plainKeys = ['profile_struktur', 'struktur_blurb'];
    foreach ($plainKeys as $k) {
        if (trim((string) ($post[$k] ?? '')) === '') {
            return [false, 'Field ' . $k . ' tidak boleh kosong.'];
        }
    }
    return [true, ''];
}
