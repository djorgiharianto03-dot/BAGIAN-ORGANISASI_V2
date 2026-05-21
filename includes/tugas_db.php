<?php declare(strict_types=1);

if (!defined('ORG_ROOT')) {
    define('ORG_ROOT', dirname(__DIR__));
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_database.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'staff_users_db.php';

if (!defined('ORG_TUGAS_MAX_UPLOAD_BYTES')) {
    define('ORG_TUGAS_MAX_UPLOAD_BYTES', 5 * 1024 * 1024);
}

/** @return list<string> */
function org_tugas_upload_roles(): array
{
    return ['super_admin', 'admin', 'sub_admin_eorganisasi', 'staf_disposisi'];
}

/** @return list<string> */
function org_tugas_page_roles(): array
{
    return org_eorg_hub_page_roles();
}

function org_tugas_role_can_upload(string $roleNorm): bool
{
    return in_array($roleNorm, org_tugas_upload_roles(), true);
}

function org_tugas_role_can_edit(string $roleNorm): bool
{
    return org_tugas_role_can_upload($roleNorm);
}

function org_tugas_role_can_delete(string $roleNorm): bool
{
    return org_tugas_role_can_upload($roleNorm);
}

/** Admin / sub admin boleh mengedit tugas pegawai mana pun. */
function org_tugas_role_can_edit_any_task(string $roleNorm): bool
{
    return in_array($roleNorm, ['super_admin', 'admin', 'sub_admin_eorganisasi'], true);
}

function org_tugas_viewer_is_kabag(bool $isKabag = false): bool
{
    return $isKabag || org_staff_session_is_kabag();
}

/**
 * Edit/hapus hanya untuk selain Kabag Organisasi (admin, sub admin, staf).
 *
 * @param array<string, mixed> $row
 */
function org_tugas_user_can_edit_row(array $row, int $viewerUserId, string $roleNorm, bool $isKabag = false): bool
{
    if (org_tugas_viewer_is_kabag($isKabag)) {
        return false;
    }
    if (!org_tugas_role_can_edit($roleNorm)) {
        return false;
    }
    if (org_tugas_role_can_edit_any_task($roleNorm)) {
        return true;
    }
    if ($viewerUserId < 1 || (int) ($row['user_id'] ?? 0) !== $viewerUserId) {
        return false;
    }
    $status = org_tugas_status_normalize((string) ($row['status'] ?? ''));

    return in_array($status, ['pending', 'revisi'], true);
}

/**
 * @param array<string, mixed> $row
 */
function org_tugas_user_can_delete_row(array $row, int $viewerUserId, string $roleNorm, bool $isKabag = false): bool
{
    if (org_tugas_viewer_is_kabag($isKabag)) {
        return false;
    }

    return org_tugas_user_can_edit_row($row, $viewerUserId, $roleNorm, $isKabag);
}

function org_tugas_resolve_is_kabag(?mysqli $db = null): bool
{
    return org_staff_session_is_kabag($db);
}

function org_tugas_download_url(int $tugasId): string
{
    return 'download_tugas.php?id=' . max(0, $tugasId);
}

function org_tugas_lihat_url(int $tugasId): string
{
    return 'lihat_tugas.php?id=' . max(0, $tugasId);
}

function org_tugas_view_file_url(int $tugasId): string
{
    return 'view_tugas_file.php?id=' . max(0, $tugasId);
}

function org_tugas_session_can_access_page(): bool
{
    return org_eorg_session_can_access_hub();
}

function org_tugas_require_access(): void
{
    if (org_tugas_session_can_access_page()) {
        return;
    }
    if (empty($_SESSION['is_admin'])) {
        $_SESSION['flash_message'] = 'Silakan login terlebih dahulu.';
        $_SESSION['flash_type'] = 'warning';
        $qs = function_exists('org_theme_preview_query_suffix') ? org_theme_preview_query_suffix() : '';
        header('Location: index.php' . $qs);
        exit;
    }
    $_SESSION['flash_message'] = 'Akses Ditolak';
    $_SESSION['flash_type'] = 'danger';
    $qs = function_exists('org_theme_preview_query_suffix') ? org_theme_preview_query_suffix() : '';
    header('Location: e_organisasi.php' . $qs);
    exit;
}

function org_tugas_table_exists(mysqli $db): bool
{
    $r = $db->query("SHOW TABLES LIKE 'tugas_pegawai'");
    if ($r === false) {
        return false;
    }
    $ok = $r->num_rows > 0;
    $r->free();

    return $ok;
}

function org_tugas_ensure_table(mysqli $db): bool
{
    if (org_tugas_table_exists($db)) {
        return true;
    }

    $createSimple = 'CREATE TABLE IF NOT EXISTS `tugas_pegawai` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id` INT UNSIGNED NOT NULL,
        `judul_tugas` VARCHAR(255) NOT NULL,
        `deskripsi` TEXT NOT NULL,
        `file_tugas` VARCHAR(255) NOT NULL DEFAULT \'\',
        `status` ENUM(\'pending\', \'diterima\', \'revisi\', \'selesai\', \'ditolak\') NOT NULL DEFAULT \'pending\',
        `catatan_kabag` TEXT NULL DEFAULT NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_tugas_user_id` (`user_id`),
        KEY `idx_tugas_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
    if ($db->query($createSimple) === true && org_tugas_table_exists($db)) {
        return true;
    }

    $sqlFile = ORG_ROOT . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'tugas_pegawai.sql';
    if (!is_file($sqlFile)) {
        return org_tugas_table_exists($db);
    }
    $sql = (string) file_get_contents($sqlFile);
    if ($sql === '') {
        return org_tugas_table_exists($db);
    }
    $parts = preg_split('/;\s*\n/', $sql) ?: [];
    foreach ($parts as $chunk) {
        $stmt = trim($chunk);
        if ($stmt === '' || stripos($stmt, 'USE ') === 0) {
            continue;
        }
        $db->query($stmt);
    }

    return org_tugas_table_exists($db);
}

/** @return list<string> */
function org_tugas_status_list(): array
{
    return ['pending', 'diterima', 'revisi', 'selesai', 'ditolak'];
}

function org_tugas_status_normalize(string $status): string
{
    $s = strtolower(trim($status));
    if (in_array($s, org_tugas_status_list(), true)) {
        return $s;
    }

    return 'pending';
}

function org_tugas_status_label(string $status): string
{
    return match (org_tugas_status_normalize($status)) {
        'diterima' => 'Diterima',
        'revisi' => 'Revisi',
        'selesai' => 'Selesai',
        'ditolak' => 'Ditolak',
        default => 'Pending',
    };
}

function org_tugas_status_badge_class(string $status): string
{
    return match (org_tugas_status_normalize($status)) {
        'diterima' => 'bg-success',
        'revisi' => 'bg-warning text-dark',
        'selesai' => 'bg-primary',
        'ditolak' => 'bg-danger',
        default => 'bg-secondary',
    };
}

/** Status yang boleh dipilih Kabag saat validasi. */
/** @return list<string> */
function org_tugas_kabag_status_options(): array
{
    return ['diterima', 'revisi', 'selesai', 'ditolak'];
}

function org_tugas_upload_dir_fs(): string
{
    $dir = ORG_ROOT . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'tugas_pegawai';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }

    return $dir;
}

function org_tugas_upload_web_prefix(): string
{
    return 'uploads/tugas_pegawai/';
}

function org_tugas_upload_web_url(string $storedFilename): string
{
    $base = basename(trim($storedFilename));
    if ($base === '' || !org_tugas_is_safe_filename($base)) {
        return '';
    }

    return org_tugas_upload_web_prefix() . rawurlencode($base);
}

function org_tugas_file_extension(string $filename): string
{
    return strtolower(pathinfo(basename($filename), PATHINFO_EXTENSION));
}

function org_tugas_file_can_inline_preview(string $filename): bool
{
    return in_array(
        org_tugas_file_extension($filename),
        ['pdf', 'png', 'jpg', 'jpeg', 'gif', 'webp'],
        true
    );
}

function org_tugas_file_type_label(string $filename): string
{
    return match (org_tugas_file_extension($filename)) {
        'pdf' => 'PDF',
        'doc', 'docx' => 'Word',
        'xls', 'xlsx' => 'Excel',
        default => 'Dokumen',
    };
}

function org_tugas_is_safe_filename(string $filename): bool
{
    $base = basename($filename);
    if ($base === '' || $base === '.' || $base === '..' || str_contains($base, '/')) {
        return false;
    }
    $ext = strtolower(pathinfo($base, PATHINFO_EXTENSION));

    return in_array($ext, ['pdf', 'doc', 'docx', 'xls', 'xlsx'], true);
}

/**
 * @return array{ok: bool, filename: string, message: string}
 */
function org_tugas_process_upload(?array $file): array
{
    $fail = static function (string $message): array {
        return ['ok' => false, 'filename' => '', 'message' => $message];
    };

    if ($file === null || !is_array($file)) {
        return $fail('File tugas belum dipilih.');
    }
    $err = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($err === UPLOAD_ERR_NO_FILE) {
        return $fail('File tugas wajib diunggah.');
    }
    if ($err !== UPLOAD_ERR_OK) {
        if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
            return $fail('Ukuran file melebihi batas maksimal 5 MB.');
        }

        return $fail('Terjadi kesalahan saat mengunggah file.');
    }

    $size = (int) ($file['size'] ?? 0);
    if ($size <= 0) {
        return $fail('Ukuran file tidak valid.');
    }
    if ($size > ORG_TUGAS_MAX_UPLOAD_BYTES) {
        return $fail('Ukuran file melebihi batas maksimal 5 MB.');
    }

    $allowedMime = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];
    $tmp = (string) ($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        return $fail('File upload tidak valid.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo !== false ? (string) finfo_file($finfo, $tmp) : '';
    if ($finfo !== false) {
        finfo_close($finfo);
    }
    if (!in_array($mime, $allowedMime, true)) {
        return $fail('Format tidak didukung. Gunakan PDF, DOCX, atau XLSX.');
    }

    $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', basename((string) ($file['name'] ?? '')));
    if ($safeName === '' || $safeName === '_') {
        $safeName = 'tugas.pdf';
    }
    $ext = strtolower(pathinfo($safeName, PATHINFO_EXTENSION));
    if (!in_array($ext, ['pdf', 'doc', 'docx', 'xls', 'xlsx'], true)) {
        return $fail('Ekstensi file tidak didukung. Gunakan PDF, DOCX, atau XLSX.');
    }

    $stem = pathinfo($safeName, PATHINFO_FILENAME);
    if ($stem === '' || $stem === '.') {
        $stem = 'tugas';
    }
    $targetName = $stem . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dir = org_tugas_upload_dir_fs();
    $targetPath = $dir . DIRECTORY_SEPARATOR . $targetName;
    if (!move_uploaded_file($tmp, $targetPath)) {
        return $fail('Gagal menyimpan file ke server.');
    }

    return ['ok' => true, 'filename' => $targetName, 'message' => ''];
}

/**
 * Unggah opsional (kosong = tidak mengganti file).
 *
 * @return array{ok: bool, filename: string, message: string}
 */
function org_tugas_process_upload_optional(?array $file): array
{
    if ($file === null || !is_array($file)) {
        return ['ok' => true, 'filename' => '', 'message' => ''];
    }
    $err = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($err === UPLOAD_ERR_NO_FILE) {
        return ['ok' => true, 'filename' => '', 'message' => ''];
    }

    return org_tugas_process_upload($file);
}

/**
 * Cek apakah viewer boleh mengakses baris tugas (anti-IDOR).
 */
function org_tugas_viewer_can_access_row(array $row, int $viewerUserId, bool $isKabag): bool
{
    if ($isKabag || org_staff_session_is_kabag()) {
        return true;
    }
    $ownerId = (int) ($row['user_id'] ?? 0);

    return $viewerUserId > 0 && $ownerId === $viewerUserId;
}

/**
 * @return array<string, mixed>|null
 */
function org_tugas_fetch_by_id(mysqli $db, int $id): ?array
{
    if ($id < 1 || !org_tugas_table_exists($db)) {
        return null;
    }
    $stmt = $db->prepare(
        'SELECT t.*, u.`username`, u.`nama` AS `uploader_nama`
         FROM `tugas_pegawai` t
         LEFT JOIN `users` u ON u.`id` = t.`user_id`
         WHERE t.`id` = ?
         LIMIT 1'
    );
    if ($stmt === false) {
        return null;
    }
    $stmt->bind_param('i', $id);
    $row = null;
    if ($stmt->execute()) {
        $res = $stmt->get_result();
        if ($res !== false) {
            $fetched = $res->fetch_assoc();
            if (is_array($fetched)) {
                $row = $fetched;
            }
        }
    }
    $stmt->close();

    return $row;
}

/**
 * @return array<string, mixed>|null
 */
function org_tugas_fetch_by_id_for_viewer(mysqli $db, int $id, int $viewerUserId, bool $isKabag): ?array
{
    if (!$isKabag && org_staff_session_is_kabag($db)) {
        $isKabag = true;
    }
    $row = org_tugas_fetch_by_id($db, $id);
    if ($row === null) {
        return null;
    }
    if (!org_tugas_viewer_can_access_row($row, $viewerUserId, $isKabag)) {
        return null;
    }

    return $row;
}

/**
 * @return list<array<string, mixed>>
 */
function org_tugas_fetch_for_viewer(mysqli $db, int $viewerUserId, bool $isKabag, ?string $statusFilter = null): array
{
    if (!org_tugas_table_exists($db)) {
        return [];
    }

    if (!$isKabag && org_staff_session_is_kabag($db)) {
        $isKabag = true;
    }

    $sql = 'SELECT t.*, u.`username`, u.`nama` AS `uploader_nama`
            FROM `tugas_pegawai` t
            LEFT JOIN `users` u ON u.`id` = t.`user_id`';
    $types = '';
    $params = [];

    if (!$isKabag) {
        if ($viewerUserId < 1) {
            return [];
        }
        $sql .= ' WHERE t.`user_id` = ?';
        $types .= 'i';
        $params[] = $viewerUserId;
    } else {
        $sql .= ' WHERE 1=1';
    }

    if ($statusFilter !== null && $statusFilter !== '') {
        $norm = org_tugas_status_normalize($statusFilter);
        $sql .= ' AND t.`status` = ?';
        $types .= 's';
        $params[] = $norm;
    }

    $sql .= ' ORDER BY COALESCE(NULLIF(TRIM(u.`nama`), \'\'), NULLIF(TRIM(u.`username`), \'\'), \'Pegawai\') ASC,
              t.`created_at` DESC, t.`id` DESC';

    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        return [];
    }
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $rows = [];
    if ($stmt->execute()) {
        $res = $stmt->get_result();
        if ($res !== false) {
            while ($r = $res->fetch_assoc()) {
                if (is_array($r)) {
                    $rows[] = $r;
                }
            }
        }
    }
    $stmt->close();

    return $rows;
}

/**
 * @param list<array<string, mixed>> $rows
 * @return list<array{key: string, user_id: int, label: string, username: string, rows: list<array<string, mixed>>}>
 */
function org_tugas_group_rows_by_employee(array $rows): array
{
    $groups = [];
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $uid = (int) ($row['user_id'] ?? 0);
        $nama = trim((string) ($row['uploader_nama'] ?? ''));
        $username = trim((string) ($row['username'] ?? ''));
        $label = $nama !== '' ? $nama : ($username !== '' ? $username : ($uid > 0 ? 'Pegawai #' . $uid : 'Pegawai'));
        $key = $uid > 0 ? 'u' . $uid : 'x' . substr(md5($label), 0, 12);
        if (!isset($groups[$key])) {
            $groups[$key] = [
                'key' => $key,
                'user_id' => $uid,
                'label' => $label,
                'username' => $username,
                'rows' => [],
            ];
        }
        $groups[$key]['rows'][] = $row;
    }

    return array_values($groups);
}

function org_tugas_group_collapse_dom_id(string $groupKey): string
{
    $safe = preg_replace('/[^a-zA-Z0-9_-]+/', '-', $groupKey) ?? '';
    if ($safe === '') {
        $safe = 'grp';
    }

    return 'tugasAcc-' . $safe;
}

function org_tugas_row_display_name(array $row): string
{
    $nama = trim((string) ($row['uploader_nama'] ?? ''));
    if ($nama !== '') {
        return $nama;
    }
    $username = trim((string) ($row['username'] ?? ''));
    if ($username !== '') {
        return $username;
    }
    $uid = (int) ($row['user_id'] ?? 0);

    return $uid > 0 ? 'Pegawai #' . $uid : 'Pegawai';
}

function org_tugas_insert(
    mysqli $db,
    int $userId,
    string $judul,
    string $deskripsi,
    string $filename
): bool {
    if ($userId < 1 || !org_tugas_ensure_table($db)) {
        return false;
    }
    $judul = trim($judul);
    $deskripsi = trim($deskripsi);
    $filename = basename(trim($filename));
    if ($judul === '' || $deskripsi === '' || $filename === '') {
        return false;
    }

    $stmt = $db->prepare(
        'INSERT INTO `tugas_pegawai` (`user_id`, `judul_tugas`, `deskripsi`, `file_tugas`, `status`)
         VALUES (?, ?, ?, ?, \'pending\')'
    );
    if ($stmt === false) {
        return false;
    }
    $stmt->bind_param('isss', $userId, $judul, $deskripsi, $filename);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

function org_tugas_update_status_kabag(
    mysqli $db,
    int $tugasId,
    string $status,
    string $catatanKabag
): bool {
    if ($tugasId < 1) {
        return false;
    }
    $status = org_tugas_status_normalize($status);
    if (!in_array($status, org_tugas_kabag_status_options(), true)) {
        return false;
    }
    $catatanKabag = trim($catatanKabag);

    $stmt = $db->prepare(
        'UPDATE `tugas_pegawai`
         SET `status` = ?, `catatan_kabag` = ?, `updated_at` = NOW()
         WHERE `id` = ?'
    );
    if ($stmt === false) {
        return false;
    }
    $stmt->bind_param('ssi', $status, $catatanKabag, $tugasId);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

function org_tugas_update_content(
    mysqli $db,
    int $tugasId,
    string $judul,
    string $deskripsi,
    ?string $newFilename,
    bool $resetToPending
): bool {
    if ($tugasId < 1) {
        return false;
    }
    $judul = trim($judul);
    $deskripsi = trim($deskripsi);
    if ($judul === '' || $deskripsi === '') {
        return false;
    }

    $row = org_tugas_fetch_by_id($db, $tugasId);
    if ($row === null) {
        return false;
    }

    $oldFile = trim((string) ($row['file_tugas'] ?? ''));
    $fileToSave = $oldFile;
    if ($newFilename !== null && $newFilename !== '') {
        $fileToSave = basename($newFilename);
    }
    if ($fileToSave === '') {
        return false;
    }

    if ($resetToPending) {
        $stmt = $db->prepare(
            'UPDATE `tugas_pegawai`
             SET `judul_tugas` = ?, `deskripsi` = ?, `file_tugas` = ?, `status` = \'pending\',
                 `catatan_kabag` = NULL, `updated_at` = NOW()
             WHERE `id` = ?'
        );
        if ($stmt === false) {
            return false;
        }
        $stmt->bind_param('sssi', $judul, $deskripsi, $fileToSave, $tugasId);
    } else {
        $stmt = $db->prepare(
            'UPDATE `tugas_pegawai`
             SET `judul_tugas` = ?, `deskripsi` = ?, `file_tugas` = ?, `updated_at` = NOW()
             WHERE `id` = ?'
        );
        if ($stmt === false) {
            return false;
        }
        $stmt->bind_param('sssi', $judul, $deskripsi, $fileToSave, $tugasId);
    }
    $ok = $stmt->execute();
    $stmt->close();
    if (!$ok) {
        if ($newFilename !== null && $newFilename !== '' && $fileToSave !== $oldFile) {
            org_tugas_unlink_file($fileToSave);
        }

        return false;
    }
    if ($newFilename !== null && $newFilename !== '' && $fileToSave !== $oldFile && $oldFile !== '') {
        org_tugas_unlink_file($oldFile);
    }

    return true;
}

function org_tugas_delete_by_id(mysqli $db, int $tugasId): bool
{
    if ($tugasId < 1) {
        return false;
    }
    $row = org_tugas_fetch_by_id($db, $tugasId);
    if ($row === null) {
        return false;
    }
    $stmt = $db->prepare('DELETE FROM `tugas_pegawai` WHERE `id` = ? LIMIT 1');
    if ($stmt === false) {
        return false;
    }
    $stmt->bind_param('i', $tugasId);
    $ok = $stmt->execute();
    $stmt->close();
    if ($ok) {
        org_tugas_unlink_file((string) ($row['file_tugas'] ?? ''));
    }

    return $ok;
}

function org_tugas_count_pending_for_kabag(mysqli $db): int
{
    if (!org_tugas_table_exists($db)) {
        return 0;
    }
    $res = $db->query("SELECT COUNT(*) AS c FROM `tugas_pegawai` WHERE `status` = 'pending'");
    if ($res === false) {
        return 0;
    }
    $row = $res->fetch_assoc();
    $res->free();

    return (int) ($row['c'] ?? 0);
}

function org_tugas_resolve_file_path(string $storedFilename): ?string
{
    $base = basename($storedFilename);
    if (!org_tugas_is_safe_filename($base)) {
        return null;
    }
    $dir = realpath(org_tugas_upload_dir_fs());
    if ($dir === false) {
        return null;
    }
    $target = $dir . DIRECTORY_SEPARATOR . $base;
    $real = realpath($target);
    if ($real === false || !is_file($real) || dirname($real) !== $dir) {
        return null;
    }

    return $real;
}

function org_tugas_unlink_file(string $storedFilename): void
{
    $path = org_tugas_resolve_file_path($storedFilename);
    if ($path !== null) {
        @unlink($path);
    }
}
