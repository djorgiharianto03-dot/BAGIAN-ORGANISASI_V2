<?php

function org_staff_role_normalize(?string $role): string
{
    $r = strtolower(trim((string) $role));
    if ($r === '') {
        return '';
    }
    if ($r === 'super_admin' || $r === 'super admin') {
        return 'super_admin';
    }
    if ($r === 'admin' || $r === 'administrator') {
        return 'admin';
    }
    if ($r === 'sub_admin_eorganisasi' || $r === 'sub admin eorganisasi') {
        return 'sub_admin_eorganisasi';
    }
    if ($r === 'sub_admin_publikasi' || $r === 'sub admin publikasi') {
        return 'sub_admin_publikasi';
    }
    if (
        $r === 'staf_disposisi'
        || $r === 'staf disposisi'
        || $r === 'staff_disposisi'
        || $r === 'staff disposisi'
        || $r === 'stafdisposisi'
        || $r === 'staffdisposisi'
    ) {
        return 'staf_disposisi';
    }
    if (
        $r === 'kabag_organisasi'
        || $r === 'kabag organisasi'
        || preg_match('/^kabag[\s_]*organisasi$/', $r) === 1
    ) {
        return 'kabag_organisasi';
    }
    if ($r === 'sub_admin' || $r === 'sub admin' || $r === 'subadmin') {
        return 'sub_admin_eorganisasi';
    }
    return '';
}

function org_staff_role_label(string $normalized): string
{
    if ($normalized === 'super_admin') {
        return 'Super Admin';
    }
    if ($normalized === 'admin') {
        return 'Admin';
    }
    if ($normalized === 'sub_admin_publikasi') {
        return 'Sub Admin Publikasi';
    }
    if ($normalized === 'sub_admin_eorganisasi') {
        return 'Sub Admin E-Organisasi';
    }
    if ($normalized === 'staf_disposisi') {
        return 'Staf Disposisi';
    }
    if ($normalized === 'kabag_organisasi') {
        return 'Kabag Organisasi';
    }
    return 'Belum Aktif';
}

/** @return list<string> */
function org_eorg_hub_page_roles(): array
{
    return ['super_admin', 'admin', 'sub_admin_eorganisasi', 'staf_disposisi', 'kabag_organisasi'];
}

if (!function_exists('org_require_level_access')) {
    /**
     * @param list<string> $allowedLevels
     */
    function org_require_level_access(array $allowedLevels): void
    {
        $lvl = org_staff_role_normalize((string) ($_SESSION['level'] ?? $_SESSION['admin_role'] ?? ''));
        $ok = !empty($_SESSION['is_admin']) && in_array($lvl, $allowedLevels, true);
        if ($ok) {
            return;
        }
        if (empty($_SESSION['is_admin'])) {
            $_SESSION['flash_message'] = 'Silakan login terlebih dahulu.';
            $_SESSION['flash_type'] = 'warning';
            header('Location: ' . (function_exists('org_home_url') ? org_home_url() : 'index.php'));
            exit;
        }
        $_SESSION['flash_message'] = 'Akses Ditolak';
        $_SESSION['flash_type'] = 'danger';
        header('Location: dashboard.php');
        exit;
    }
}

function org_eorg_session_can_access_hub(): bool
{
    if (empty($_SESSION['is_admin'])) {
        return false;
    }
    $role = org_staff_role_normalize((string) ($_SESSION['level'] ?? $_SESSION['admin_role'] ?? ''));
    if (in_array($role, org_eorg_hub_page_roles(), true)) {
        return true;
    }

    return org_staff_session_is_kabag();
}

/**
 * Unggah & hapus dokumen perpustakaan digital (dashboard / bootstrap).
 * Hanya super admin dan admin penuh — bukan sub admin E-Organisasi atau Publikasi.
 */
function org_staff_can_manage_perpustakaan_dokumen(): bool
{
    if (empty($_SESSION['is_admin'])) {
        return false;
    }
    $role = org_staff_role_normalize((string) ($_SESSION['level'] ?? $_SESSION['admin_role'] ?? ''));

    return in_array($role, ['super_admin', 'admin'], true);
}

/** Dashboard grafik, buku tamu, arsip surat — hanya admin / sub admin E-Org / Kabag. */
function org_eorg_hub_can_see_core_admin_modules(): bool
{
    if (empty($_SESSION['is_admin'])) {
        return false;
    }
    $role = org_staff_role_normalize((string) ($_SESSION['level'] ?? $_SESSION['admin_role'] ?? ''));
    if (in_array($role, ['super_admin', 'admin', 'sub_admin_eorganisasi', 'kabag_organisasi'], true)) {
        return true;
    }

    return org_staff_session_is_kabag();
}

/** Normalisasi username login (selaras monitoring_disposisi: hapus spasi). */
function org_staff_norm_login_username(string $username): string
{
    return strtolower((string) preg_replace('/\s+/u', '', trim($username)));
}

/** Apakah string username/nama mengidentifikasi akun Kabag Organisasi. */
function org_staff_username_is_kabag_organisasi(string $username): bool
{
    if (trim($username) === '') {
        return false;
    }
    if (org_staff_norm_login_username($username) === 'kabag_organisasi') {
        return true;
    }
    $alnum = (string) preg_replace('/[^a-z0-9]+/u', '', strtolower(trim($username)));
    if (in_array($alnum, ['kabagorganisasi', 'kabag', 'kepalabagian'], true)) {
        return true;
    }

    return str_starts_with($alnum, 'kabag') && str_contains($alnum, 'organisasi');
}

/**
 * @param array<string, mixed> $userRow
 */
function org_staff_user_is_kabag(array $userRow): bool
{
    $role = org_staff_role_normalize((string) ($userRow['level'] ?? $userRow['role'] ?? ''));
    if ($role === 'kabag_organisasi') {
        return true;
    }
    $username = trim((string) ($userRow['username'] ?? ''));
    $nama = trim((string) ($userRow['nama'] ?? ''));
    if (org_staff_username_is_kabag_organisasi($username) || org_staff_username_is_kabag_organisasi($nama)) {
        return true;
    }
    $namaNorm = (string) preg_replace('/[^a-z0-9]+/u', '', strtolower($nama));

    return str_contains($namaNorm, 'kepala') && str_contains($namaNorm, 'bagian');
}

/** Kabag aktif di sesi saat ini (disimpan saat login + sinkron dari DB). */
function org_staff_session_is_kabag(?mysqli $db = null): bool
{
    if (!empty($_SESSION['is_admin']) && !empty($_SESSION['org_is_kabag'])) {
        return true;
    }
    if (empty($_SESSION['is_admin'])) {
        return false;
    }

    $role = org_staff_role_normalize((string) ($_SESSION['level'] ?? $_SESSION['admin_role'] ?? ''));
    $username = trim((string) ($_SESSION['admin_username'] ?? ''));
    $display = trim((string) ($_SESSION['admin_display'] ?? ''));
    if (
        $role === 'kabag_organisasi'
        || org_staff_username_is_kabag_organisasi($username)
        || org_staff_username_is_kabag_organisasi($display)
    ) {
        $_SESSION['org_is_kabag'] = true;

        return true;
    }

    if (!($db instanceof mysqli)) {
        $db = org_db();
    }
    if ($db instanceof mysqli) {
        $uid = (int) ($_SESSION['admin_user_id'] ?? 0);
        if ($uid > 0) {
            $row = org_staff_users_fetch_by_id($db, $uid);
            if (is_array($row) && org_staff_user_is_kabag($row)) {
                $_SESSION['org_is_kabag'] = true;

                return true;
            }
        }
        if ($username !== '') {
            $row = org_staff_users_fetch_by_username($db, $username);
            if (is_array($row) && org_staff_user_is_kabag($row)) {
                $_SESSION['org_is_kabag'] = true;

                return true;
            }
        }
    }

    return false;
}

function org_staff_users_level_column(mysqli $db): string
{
    if (!org_staff_users_table_exists($db)) {
        return 'level';
    }
    $colLevel = $db->query("SHOW COLUMNS FROM `users` LIKE 'level'");
    if ($colLevel !== false && $colLevel->num_rows > 0) {
        $colLevel->free();
    } else {
        if ($colLevel) {
            $colLevel->free();
        }

        return 'role';
    }

    return 'level';
}

/**
 * Invalidasi cache skema kolom users (panggil setelah ALTER TABLE).
 */
function org_staff_users_schema_cache_invalidate(): void
{
    $GLOBALS['org_staff_users_schema_gen'] = (int) ($GLOBALS['org_staff_users_schema_gen'] ?? 0) + 1;
}

/**
 * Tambahkan kolom `role` VARCHAR jika belum ada (agar staf_disposisi tidak bergantung pada ENUM `level`).
 */
function org_staff_users_try_add_role_column(mysqli $db): bool
{
    if (!org_staff_users_table_exists($db)) {
        return false;
    }
    $r = $db->query("SHOW COLUMNS FROM `users` LIKE 'role'");
    if ($r !== false && $r->num_rows > 0) {
        $r->free();

        return true;
    }
    if ($r) {
        $r->free();
    }
    $sql = 'ALTER TABLE `users` ADD COLUMN `role` VARCHAR(64) NULL DEFAULT NULL COMMENT \'Peran disposisi / tambahan (staf_disposisi)\'';
    $chkL = $db->query("SHOW COLUMNS FROM `users` LIKE 'level'");
    if ($chkL !== false && $chkL->num_rows > 0) {
        $chkL->free();
        $sql .= ' AFTER `level`';
    } elseif ($chkL) {
        $chkL->free();
    }

    $ok = $db->query($sql);
    if ($ok === true) {
        return true;
    }
    if ((int) $db->errno === 1060) {
        return true;
    }

    return false;
}

/**
 * Ekspresi SQL untuk nilai role efektif (beberapa DB punya `level` kosong dan role di kolom `role`).
 */
function org_staff_users_level_sql_for_select(mysqli $db): string
{
    static $cachedGen = -1;
    static $cache = null;
    $gen = (int) ($GLOBALS['org_staff_users_schema_gen'] ?? 0);
    if ($cachedGen === $gen && is_string($cache)) {
        return $cache;
    }
    $cache = '`level`';
    if (!org_staff_users_table_exists($db)) {
        $cachedGen = $gen;

        return $cache;
    }
    $hasLevel = false;
    $hasRole = false;
    $res = $db->query('SHOW COLUMNS FROM `users`');
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $f = strtolower(trim((string) ($row['Field'] ?? '')));
            if ($f === 'level') {
                $hasLevel = true;
            }
            if ($f === 'role') {
                $hasRole = true;
            }
        }
        $res->free();
    }
    if ($hasLevel && $hasRole) {
        $cache = 'COALESCE(NULLIF(TRIM(`level`), \'\'), NULLIF(TRIM(`role`), \'\'))';
    } elseif ($hasRole) {
        $cache = '`role`';
    } else {
        $cache = '`level`';
    }
    $cachedGen = $gen;

    return $cache;
}

function org_staff_users_table_exists(mysqli $db): bool
{
    $r = $db->query("SHOW TABLES LIKE 'users'");
    return $r !== false && $r->num_rows > 0;
}

/**
 * Kolom penyimpanan role pada tabel users (beberapa instalasi punya level + role).
 *
 * @return array{level: bool, role: bool}
 */
function org_staff_users_role_storage_flags(mysqli $db): array
{
    static $cachedGen = -1;
    static $cache = null;
    $gen = (int) ($GLOBALS['org_staff_users_schema_gen'] ?? 0);
    if ($cachedGen === $gen && is_array($cache)) {
        return $cache;
    }
    $cache = ['level' => false, 'role' => false];
    if (!org_staff_users_table_exists($db)) {
        $cachedGen = $gen;

        return $cache;
    }
    $res = $db->query('SHOW COLUMNS FROM `users`');
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $f = strtolower(trim((string) ($row['Field'] ?? '')));
            if ($f === 'level') {
                $cache['level'] = true;
            }
            if ($f === 'role') {
                $cache['role'] = true;
            }
        }
        $res->free();
    }
    $cachedGen = $gen;

    return $cache;
}

/**
 * Username (perbandingan case-insensitive) yang disembunyikan dari tabel daftar staf.
 *
 * @return list<string>
 */
function org_staff_usernames_hidden_from_list(): array
{
    return ['sibos'];
}

/**
 * @return list<array<string, int|string>>
 */
function org_staff_users_fetch_all(mysqli $db): array
{
    if (!org_staff_users_table_exists($db)) {
        return [];
    }
    $hidden = org_staff_usernames_hidden_from_list();
    $literals = [];
    foreach ($hidden as $h) {
        $literals[] = "'" . $db->real_escape_string(strtolower(trim($h))) . "'";
    }
    $where = $literals === []
        ? '1=1'
        : 'LOWER(TRIM(`username`)) NOT IN (' . implode(',', $literals) . ')';
    $lvlExpr = org_staff_users_level_sql_for_select($db);
    $sql = 'SELECT id, username, nama, email_google, ' . $lvlExpr . ' AS `level` FROM users WHERE ' . $where . ' ORDER BY id ASC';
    $rows = [];
    $res = $db->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            if (is_array($row)) {
                $rows[] = $row;
            }
        }
        $res->free();
    }
    return $rows;
}

/**
 * @return array<string, int|string>|null
 */
function org_staff_users_fetch_by_id(mysqli $db, int $id): ?array
{
    if (!org_staff_users_table_exists($db)) {
        return null;
    }
    $lvlExpr = org_staff_users_level_sql_for_select($db);
    $st = $db->prepare('SELECT id, username, nama, email_google, ' . $lvlExpr . ' AS `level` FROM users WHERE id = ? LIMIT 1');
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

/**
 * @return array<string, int|string>|null
 */
function org_staff_users_fetch_by_username(mysqli $db, string $username): ?array
{
    if (!org_staff_users_table_exists($db)) {
        return null;
    }
    $lvlExpr = org_staff_users_level_sql_for_select($db);
    $st = $db->prepare(
        'SELECT id, username, nama, email_google, ' . $lvlExpr . ' AS `level`, password FROM users WHERE LOWER(username) = LOWER(?) LIMIT 1'
    );
    if ($st === false) {
        return null;
    }
    $st->bind_param('s', $username);
    if (!$st->execute()) {
        $st->close();
        return null;
    }
    $res = $st->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $st->close();
    return is_array($row) ? $row : null;
}

function org_staff_users_delete_by_id(mysqli $db, int $id): bool
{
    if (!org_staff_users_table_exists($db)) {
        return false;
    }
    $st = $db->prepare('DELETE FROM users WHERE id = ?');
    if ($st === false) {
        return false;
    }
    $st->bind_param('i', $id);
    $ok = $st->execute();
    $st->close();
    return $ok;
}

function org_staff_users_update_email_google(mysqli $db, int $id, string $emailGoogle): bool
{
    if (!org_staff_users_table_exists($db)) {
        return false;
    }
    $st = $db->prepare('UPDATE users SET email_google = ? WHERE id = ?');
    if ($st === false) {
        return false;
    }
    $st->bind_param('si', $emailGoogle, $id);
    $ok = $st->execute();
    $st->close();
    return $ok;
}

function org_staff_users_update_password_hash(mysqli $db, int $id, string $passwordHash): bool
{
    if (!org_staff_users_table_exists($db)) {
        return false;
    }
    $st = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
    if ($st === false) {
        return false;
    }
    $st->bind_param('si', $passwordHash, $id);
    $ok = $st->execute();
    $st->close();
    return $ok;
}

/**
 * Validasi format username. Mengembalikan pesan error atau null jika valid.
 */
function org_staff_users_validate_username(string $username): ?string
{
    $username = trim($username);
    if ($username === '') {
        return 'Username wajib diisi.';
    }
    $len = strlen($username);
    if ($len < 3 || $len > 64) {
        return 'Username harus 3–64 karakter.';
    }
    if (!preg_match('/^[A-Za-z0-9._-]+$/', $username)) {
        return 'Username hanya boleh huruf, angka, titik, garis bawah, dan tanda hubung.';
    }

    return null;
}

/**
 * Cek username sudah dipakai (case-insensitive). $excludeUserId = 0 untuk pendaftaran baru.
 */
function org_staff_users_username_taken(mysqli $db, string $username, int $excludeUserId = 0): bool
{
    if (!org_staff_users_table_exists($db)) {
        return false;
    }
    if ($excludeUserId > 0) {
        $st = $db->prepare('SELECT 1 FROM users WHERE LOWER(TRIM(`username`)) = LOWER(TRIM(?)) AND id <> ? LIMIT 1');
        if ($st === false) {
            return false;
        }
        $st->bind_param('si', $username, $excludeUserId);
    } else {
        $st = $db->prepare('SELECT 1 FROM users WHERE LOWER(TRIM(`username`)) = LOWER(TRIM(?)) LIMIT 1');
        if ($st === false) {
            return false;
        }
        $st->bind_param('s', $username);
    }
    $st->execute();
    $res = $st->get_result();
    $ok = $res && $res->num_rows > 0;
    $st->close();

    return $ok;
}

/** @deprecated Gunakan org_staff_users_username_taken() */
function org_staff_users_username_exists(mysqli $db, string $username): bool
{
    return org_staff_users_username_taken($db, $username, 0);
}

/**
 * Perbarui profil staf. Password hanya diubah jika $passwordHash tidak null.
 */
function org_staff_users_update(
    mysqli $db,
    int $id,
    string $username,
    string $nama,
    string $emailGoogle,
    string $level,
    ?string $passwordHash = null
): bool {
    if (!org_staff_users_table_exists($db) || $id < 1) {
        return false;
    }
    if ($level === 'staf_disposisi') {
        $preFlags = org_staff_users_role_storage_flags($db);
        if ($preFlags['level'] && !$preFlags['role'] && org_staff_users_try_add_role_column($db)) {
            org_staff_users_schema_cache_invalidate();
        }
    }
    $flags = org_staff_users_role_storage_flags($db);
    $hasL = $flags['level'];
    $hasR = $flags['role'];

    $setPwd = $passwordHash !== null && $passwordHash !== '';
    $pwdClause = $setPwd ? ', `password` = ?' : '';

    if ($hasL && $hasR) {
        $sql = 'UPDATE `users` SET `username` = ?, `nama` = ?, `email_google` = ?, `level` = ?, `role` = ?' . $pwdClause . ' WHERE `id` = ?';
        $st = $db->prepare($sql);
        if ($st !== false) {
            if ($setPwd) {
                $st->bind_param('ssssssi', $username, $nama, $emailGoogle, $level, $level, $passwordHash, $id);
            } else {
                $st->bind_param('sssssi', $username, $nama, $emailGoogle, $level, $level, $id);
            }
            if ($st->execute()) {
                $st->close();

                return true;
            }
            $st->close();
        }
        $sql2 = 'UPDATE `users` SET `username` = ?, `nama` = ?, `email_google` = ?, `level` = NULL, `role` = ?' . $pwdClause . ' WHERE `id` = ?';
        $st2 = $db->prepare($sql2);
        if ($st2 !== false) {
            if ($setPwd) {
                $st2->bind_param('sssssi', $username, $nama, $emailGoogle, $level, $passwordHash, $id);
            } else {
                $st2->bind_param('ssssi', $username, $nama, $emailGoogle, $level, $id);
            }
            if ($st2->execute()) {
                $st2->close();

                return true;
            }
            $st2->close();
        }
    }

    if ($hasL) {
        $sql = 'UPDATE `users` SET `username` = ?, `nama` = ?, `email_google` = ?, `level` = ?' . $pwdClause . ' WHERE `id` = ?';
        $st = $db->prepare($sql);
        if ($st === false) {
            return false;
        }
        if ($setPwd) {
            $st->bind_param('sssssi', $username, $nama, $emailGoogle, $level, $passwordHash, $id);
        } else {
            $st->bind_param('ssssi', $username, $nama, $emailGoogle, $level, $id);
        }
        $ok = $st->execute();
        $st->close();

        return $ok;
    }

    if ($hasR) {
        $sql = 'UPDATE `users` SET `username` = ?, `nama` = ?, `email_google` = ?, `role` = ?' . $pwdClause . ' WHERE `id` = ?';
        $st = $db->prepare($sql);
        if ($st === false) {
            return false;
        }
        if ($setPwd) {
            $st->bind_param('sssssi', $username, $nama, $emailGoogle, $level, $passwordHash, $id);
        } else {
            $st->bind_param('ssssi', $username, $nama, $emailGoogle, $level, $id);
        }
        $ok = $st->execute();
        $st->close();

        return $ok;
    }

    return false;
}

function org_staff_users_insert(
    mysqli $db,
    string $username,
    string $nama,
    string $emailGoogle,
    string $level,
    string $passwordHash
): bool {
    if (!org_staff_users_table_exists($db)) {
        return false;
    }
    if ($level === 'staf_disposisi') {
        $preFlags = org_staff_users_role_storage_flags($db);
        if ($preFlags['level'] && !$preFlags['role'] && org_staff_users_try_add_role_column($db)) {
            org_staff_users_schema_cache_invalidate();
        }
    }
    $flags = org_staff_users_role_storage_flags($db);
    $hasL = $flags['level'];
    $hasR = $flags['role'];

    if ($hasL && $hasR) {
        $st = $db->prepare(
            'INSERT INTO `users` (`username`, `nama`, `email_google`, `level`, `role`, `password`) VALUES (?, ?, ?, ?, ?, ?)'
        );
        if ($st !== false) {
            $st->bind_param('ssssss', $username, $nama, $emailGoogle, $level, $level, $passwordHash);
            if ($st->execute()) {
                $st->close();

                return true;
            }
            $st->close();
            $st2 = $db->prepare(
                'INSERT INTO `users` (`username`, `nama`, `email_google`, `level`, `role`, `password`) VALUES (?, ?, ?, NULL, ?, ?)'
            );
            if ($st2 !== false) {
                $st2->bind_param('sssss', $username, $nama, $emailGoogle, $level, $passwordHash);
                if ($st2->execute()) {
                    $st2->close();

                    return true;
                }
                $st2->close();
            }
            $st3 = $db->prepare(
                'INSERT INTO `users` (`username`, `nama`, `email_google`, `role`, `password`) VALUES (?, ?, ?, ?, ?)'
            );
            if ($st3 !== false) {
                $st3->bind_param('sssss', $username, $nama, $emailGoogle, $level, $passwordHash);
                if ($st3->execute()) {
                    $st3->close();

                    return true;
                }
                $st3->close();
            }

            return false;
        }

        return false;
    }

    if ($hasR && !$hasL) {
        $st = $db->prepare(
            'INSERT INTO `users` (`username`, `nama`, `email_google`, `role`, `password`) VALUES (?, ?, ?, ?, ?)'
        );
        if ($st === false) {
            return false;
        }
        $st->bind_param('sssss', $username, $nama, $emailGoogle, $level, $passwordHash);
        $ok = $st->execute();
        $st->close();

        return $ok;
    }

    if ($hasL) {
        $st = $db->prepare(
            'INSERT INTO `users` (`username`, `nama`, `email_google`, `level`, `password`) VALUES (?, ?, ?, ?, ?)'
        );
        if ($st === false) {
            return false;
        }
        $st->bind_param('sssss', $username, $nama, $emailGoogle, $level, $passwordHash);
        $ok = $st->execute();
        $st->close();

        return $ok;
    }

    return false;
}

/**
 * Username "Si Bos" / sibos (tanpa membedakan spasi & huruf besar) — disembunyikan dari riwayat audit.
 */
function org_staff_audit_username_is_si_bos(string $username): bool
{
    $n = strtolower(preg_replace('/\s+/u', '', trim($username)));
    return $n === 'sibos';
}

/**
 * Riwayat audit ditampilkan hanya untuk Admin (bukan Sub Admin); Super Admin dan akun Si Bos tidak melihat tabel.
 */
function org_staff_audit_viewer_can_see_riwayat(): bool
{
    $level = org_staff_role_normalize((string) ($_SESSION['level'] ?? $_SESSION['admin_role'] ?? ''));
    if ($level !== 'admin') {
        return false;
    }
    if (org_staff_audit_username_is_si_bos((string) ($_SESSION['admin_username'] ?? ''))) {
        return false;
    }
    return true;
}
