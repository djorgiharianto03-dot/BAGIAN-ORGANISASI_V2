<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_database.php';

if (!defined('ORG_DOKUMEN_MAX_UPLOAD_BYTES')) {
    define('ORG_DOKUMEN_MAX_UPLOAD_BYTES', 20 * 1024 * 1024);
}
if (!defined('ORG_DOKUMEN_MAX_UPLOAD_IMAGE_BYTES')) {
    define('ORG_DOKUMEN_MAX_UPLOAD_IMAGE_BYTES', 5 * 1024 * 1024);
}

function org_dokumen_library_upload_dir_fs(): string
{
    $root = defined('ORG_ROOT') ? (string) ORG_ROOT : dirname(__DIR__);

    return $root . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'perpustakaan_digital';
}

/**
 * @param array<string, mixed>|null $file
 * @return array{message: string, type: string}
 */
function org_dokumen_process_upload(?array $file, string $kategoriRaw): array
{
    $fail = static function (string $message, string $type): array {
        return ['message' => $message, 'type' => $type];
    };

    if ($file === null || !is_array($file)) {
        return $fail('File belum dipilih.', 'warning');
    }

    $kategoriUpload = org_dokumen_normalize_tim_kategori($kategoriRaw);
    $rawKategoriUpload = trim($kategoriRaw);
    if (!in_array($kategoriUpload, org_dokumen_tim_kategori_list(), true) || $rawKategoriUpload === '') {
        return $fail('Pilih kategori dokumen: Kelembagaan, Pelayanan Publik, SAKIP & RB, Regulasi, Lainnya, atau Visual/Foto Struktur.', 'warning');
    }

    if ((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $uploadErr = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($uploadErr === UPLOAD_ERR_INI_SIZE || $uploadErr === UPLOAD_ERR_FORM_SIZE) {
            return $fail(
                'Ukuran file melebihi batas upload server. Maksimal ' . org_format_file_size((int) ORG_DOKUMEN_MAX_UPLOAD_BYTES) . '.',
                'warning'
            );
        }
        if ($uploadErr === UPLOAD_ERR_NO_FILE) {
            return $fail('File belum dipilih.', 'warning');
        }

        return $fail('Terjadi kesalahan saat upload file.', 'danger');
    }

    $fileSize = (int) ($file['size'] ?? 0);
    if ($fileSize <= 0) {
        return $fail('Ukuran file tidak valid.', 'warning');
    }
    if ($fileSize > ORG_DOKUMEN_MAX_UPLOAD_BYTES) {
        return $fail(
            'Ukuran file melebihi batas maksimal ' . org_format_file_size((int) ORG_DOKUMEN_MAX_UPLOAD_BYTES) . '.',
            'warning'
        );
    }

    $allowedMimeTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'image/jpeg',
        'image/png',
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = $finfo !== false ? (string) finfo_file($finfo, (string) ($file['tmp_name'] ?? '')) : '';
    if ($finfo !== false) {
        finfo_close($finfo);
    }

    if (!in_array($mimeType, $allowedMimeTypes, true)) {
        return $fail('Format file tidak didukung. Gunakan PDF, DOCX, XLSX, JPG, atau PNG.', 'warning');
    }

    $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', basename((string) ($file['name'] ?? '')));
    if ($safeName === '' || $safeName === '_') {
        $safeName = 'dokumen.pdf';
    }
    $pathInfo = pathinfo($safeName);
    $stem = isset($pathInfo['filename']) ? (string) $pathInfo['filename'] : 'dokumen';
    $extRaw = isset($pathInfo['extension']) && $pathInfo['extension'] !== ''
        ? strtolower((string) $pathInfo['extension'])
        : '';
    if (!in_array($extRaw, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'], true)) {
        return $fail('Format tidak didukung. Gunakan PDF, DOCX, XLSX, JPG, atau PNG.', 'warning');
    }

    $isImageUpload = in_array($extRaw, ['jpg', 'jpeg', 'png'], true);
    $maxBytesByType = $isImageUpload ? (int) ORG_DOKUMEN_MAX_UPLOAD_IMAGE_BYTES : (int) ORG_DOKUMEN_MAX_UPLOAD_BYTES;
    if ($fileSize > $maxBytesByType) {
        $jenis = $isImageUpload ? 'gambar' : 'dokumen';

        return $fail(
            'Ukuran ' . $jenis . ' melebihi batas maksimal ' . org_format_file_size($maxBytesByType) . '.',
            'warning'
        );
    }

    $ext = '.' . $extRaw;
    $targetName = $stem . $ext;
    $target_dir = org_dokumen_library_upload_dir_fs();
    if (!is_dir($target_dir)) {
        @mkdir($target_dir, 0775, true);
    }
    if (!is_dir($target_dir) || !is_writable($target_dir)) {
        return $fail('Folder unggahan tidak dapat ditulis. Periksa permission folder uploads/perpustakaan_digital/ di server.', 'danger');
    }
    $n = 1;
    while (is_file($target_dir . DIRECTORY_SEPARATOR . $targetName)) {
        $n++;
        $targetName = $stem . '_' . $n . $ext;
    }
    $targetPath = $target_dir . DIRECTORY_SEPARATOR . $targetName;
    $tmp = (string) ($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        return $fail('File upload tidak valid.', 'danger');
    }

    if (!move_uploaded_file($tmp, $targetPath)) {
        return $fail('Gagal menyimpan file ke server.', 'danger');
    }

    $dbUpDoc = org_db();
    if ($dbUpDoc instanceof mysqli) {
        org_dokumen_ensure_table($dbUpDoc);
        org_dokumen_register_file($dbUpDoc, $targetName);
        org_dokumen_update_kategori_by_filename($dbUpDoc, $targetName, $kategoriUpload);
    }

    return $fail('Dokumen berhasil diunggah ke folder uploads/perpustakaan_digital/.', 'success');
}

/**
 * @return array{message: string, type: string}
 */
function org_dokumen_delete_library_file(string $fileName): array
{
    $fail = static function (string $message, string $type): array {
        return ['message' => $message, 'type' => $type];
    };

    $fileToDelete = basename($fileName);
    $libraryDir = org_dokumen_library_upload_dir_fs();
    $targetPath = $libraryDir . DIRECTORY_SEPARATOR . $fileToDelete;
    $uploadDirRealPath = realpath($libraryDir);
    $targetRealPath = realpath($targetPath);

    if (
        $fileToDelete === ''
        || $uploadDirRealPath === false
        || $targetRealPath === false
        || !is_file($targetRealPath)
        || dirname($targetRealPath) !== $uploadDirRealPath
    ) {
        return $fail('File tidak valid atau tidak ditemukan.', 'warning');
    }
    if (!unlink($targetRealPath)) {
        return $fail('Gagal menghapus file.', 'danger');
    }

    $dbDelDoc = org_db();
    if ($dbDelDoc instanceof mysqli) {
        org_dokumen_delete_by_filename($dbDelDoc, $fileToDelete);
    }

    return $fail('File berhasil dihapus.', 'success');
}

function org_dokumen_stored_basename(string $storedName): string
{
    if (preg_match('/^\d{8}_\d{6}_(.+)$/i', $storedName, $m)) {
        return (string) $m[1];
    }

    return $storedName;
}

/**
 * @return array<string, array{kategori?: string, judul?: string, deskripsi?: string, nama_file?: string, jumlah_unduh?: int}>
 */
function org_dokumen_list_library_files_on_disk(): array
{
    $libraryDir = org_dokumen_library_upload_dir_fs();
    $files = [];
    if (!is_dir($libraryDir)) {
        return $files;
    }
    $files = array_values(array_filter(scandir($libraryDir), static function ($item) use ($libraryDir) {
        return $item !== '.' && $item !== '..' && is_file($libraryDir . DIRECTORY_SEPARATOR . (string) $item);
    }));
    rsort($files);

    return $files;
}

/**
 * @return list<string>
 */
function org_dokumen_tim_kategori_list(): array
{
    return ['Kelembagaan', 'Pelayanan Publik', 'SAKIP & RB', 'Regulasi', 'Lainnya', 'Visual/Foto Struktur'];
}

function org_dokumen_normalize_tim_kategori(string $raw): string
{
    $val = trim(mb_strtolower($raw));
    if ($val === 'kelembagaan') {
        return 'Kelembagaan';
    }
    if ($val === 'pelayanan publik') {
        return 'Pelayanan Publik';
    }
    if ($val === 'sakip & rb' || $val === 'sakip dan rb' || $val === 'sakip rb') {
        return 'SAKIP & RB';
    }
    if ($val === 'regulasi') {
        return 'Regulasi';
    }
    if ($val === 'lainnya' || $val === 'lain lain' || $val === 'lain-lain') {
        return 'Lainnya';
    }
    if ($val === 'visual/foto struktur' || $val === 'visual foto struktur' || $val === 'foto struktur') {
        return 'Visual/Foto Struktur';
    }

    return 'Kelembagaan';
}

function org_dokumen_tim_kategori_slug(string $kategori): string
{
    $k = org_dokumen_normalize_tim_kategori($kategori);
    if ($k === 'Pelayanan Publik') {
        return 'pelayanan-publik';
    }
    if ($k === 'SAKIP & RB') {
        return 'sakip-rb';
    }
    if ($k === 'Lainnya') {
        return 'lainnya';
    }
    if ($k === 'Regulasi') {
        return 'regulasi';
    }
    if ($k === 'Visual/Foto Struktur') {
        return 'visual-foto';
    }

    return 'kelembagaan';
}

function org_dokumen_ensure_table(mysqli $db): void
{
    $db->query(
        'CREATE TABLE IF NOT EXISTS `dokumen` (
          `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
          `nama_file` VARCHAR(255) NOT NULL COMMENT \'Nama file di folder uploads\',
          `kategori` VARCHAR(100) NOT NULL DEFAULT \'Umum\',
          `jumlah_unduh` INT UNSIGNED NOT NULL DEFAULT 0,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uniq_dokumen_nama_file` (`nama_file`),
          KEY `idx_dokumen_unduh` (`jumlah_unduh`),
          KEY `idx_dokumen_kategori` (`kategori`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    org_dokumen_migrate_metadata_columns($db);
    $db->query("UPDATE `dokumen` SET `kategori` = 'Kelembagaan' WHERE `kategori` NOT IN ('Kelembagaan', 'Pelayanan Publik', 'SAKIP & RB', 'Regulasi', 'Lainnya', 'Visual/Foto Struktur')");
}

/**
 * Tambah kolom judul / deskripsi untuk pencarian LIKE (tanpa menghapus data).
 */
function org_dokumen_migrate_metadata_columns(mysqli $db): void
{
    if (!org_dokumen_table_exists($db)) {
        return;
    }
    $have = [];
    $res = $db->query('SHOW COLUMNS FROM `dokumen`');
    if ($res !== false) {
        while ($row = $res->fetch_assoc()) {
            if (is_array($row) && isset($row['Field'])) {
                $have[(string) $row['Field']] = true;
            }
        }
    }
    if (!isset($have['judul'])) {
        $db->query(
            'ALTER TABLE `dokumen` ADD COLUMN `judul` VARCHAR(512) NULL DEFAULT NULL COMMENT \'Judul tampilan\' AFTER `kategori`'
        );
    }
    if (!isset($have['deskripsi'])) {
        $db->query(
            'ALTER TABLE `dokumen` ADD COLUMN `deskripsi` TEXT NULL DEFAULT NULL COMMENT \'Deskripsi / catatan\' AFTER `judul`'
        );
    }
}

function org_dokumen_table_exists(mysqli $db): bool
{
    $r = $db->query("SHOW TABLES LIKE 'dokumen'");
    return $r !== false && $r->num_rows > 0;
}

/**
 * File yang ditampilkan di Perpustakaan Dokumen (bukan foto personel / gambar).
 */
function org_dokumen_is_library_file(string $namaFile): bool
{
    $ext = strtolower(pathinfo(basename($namaFile), PATHINFO_EXTENSION));

    return in_array($ext, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif', 'webp'], true);
}

/**
 * Path absolut berkas perpustakaan yang valid (anti path traversal).
 */
function org_dokumen_resolve_realpath(string $namaFile): ?string
{
    $namaFile = basename($namaFile);
    if ($namaFile === '' || $namaFile === '.' || $namaFile === '..' || !org_dokumen_is_library_file($namaFile)) {
        return null;
    }
    $libraryDir = org_dokumen_library_upload_dir_fs();
    $targetPath = $libraryDir . DIRECTORY_SEPARATOR . $namaFile;
    $uploadDirReal = realpath($libraryDir);
    $targetReal = realpath($targetPath);
    if (
        $uploadDirReal === false
        || $targetReal === false
        || !is_file($targetReal)
        || dirname($targetReal) !== $uploadDirReal
    ) {
        return null;
    }

    return $targetReal;
}

function org_dokumen_can_preview_inline(string $namaFile): bool
{
    $ext = strtolower(pathinfo(basename($namaFile), PATHINFO_EXTENSION));

    return in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'], true);
}

function org_dokumen_download_url(string $namaFile): string
{
    if (!function_exists('org_page_url')) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_app.php';
    }

    return org_page_url('download_dokumen.php') . '?file=' . rawurlencode(basename($namaFile));
}

function org_dokumen_view_url(string $namaFile): string
{
    if (!function_exists('org_page_url')) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_app.php';
    }

    return org_page_url('view_dokumen.php') . '?file=' . rawurlencode(basename($namaFile));
}

/**
 * Kirim berkas ke browser; mengakhiri skrip.
 *
 * @param 'inline'|'attachment' $disposition
 */
function org_dokumen_send_http(string $namaFile, string $disposition = 'attachment'): void
{
    $targetReal = org_dokumen_resolve_realpath($namaFile);
    if ($targetReal === null) {
        http_response_code(404);
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Berkas tidak ditemukan</title></head><body>';
        echo '<p style="font-family:system-ui,sans-serif;padding:1.5rem">Berkas dokumen tidak ditemukan di server. ';
        echo 'Mungkin belum diunggah atau folder <code>uploads/perpustakaan_digital/</code> belum disalin saat deploy.</p>';
        echo '<p><a href="javascript:history.back()">Kembali</a></p></body></html>';
        exit;
    }

    if ($disposition === 'download' || $disposition === 'attachment') {
        $db = org_db();
        if ($db instanceof mysqli) {
            org_dokumen_increment_download($db, basename($namaFile));
        }
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo !== false ? (string) finfo_file($finfo, $targetReal) : 'application/octet-stream';
    if ($finfo !== false) {
        finfo_close($finfo);
    }

    $stored = basename($namaFile);
    $downloadName = str_replace('_', ' ', pathinfo($stored, PATHINFO_FILENAME));
    $ext = pathinfo($stored, PATHINFO_EXTENSION);
    if ($ext !== '') {
        $downloadName .= '.' . $ext;
    }

    $disp = $disposition === 'inline' ? 'inline' : 'attachment';
    if ($disp === 'inline' && !org_dokumen_can_preview_inline($stored)) {
        $disp = 'attachment';
    }

    header('Content-Type: ' . $mime);
    header('Content-Length: ' . (string) filesize($targetReal));
    header('Content-Disposition: ' . $disp . '; filename="' . str_replace('"', '', $downloadName) . '"');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: private, max-age=120');
    if ($disp === 'inline') {
        header('X-Frame-Options: SAMEORIGIN');
    }

    readfile($targetReal);
    exit;
}

function org_dokumen_kategori_from_filename(string $namaFile): string
{
    $ext = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
        return 'Visual/Foto Struktur';
    }

    return 'Kelembagaan';
}

function org_dokumen_is_visual_kategori(string $kategori): bool
{
    return org_dokumen_normalize_tim_kategori($kategori) === 'Visual/Foto Struktur';
}

/**
 * Ukuran file ramah pengguna (mis. 1,2 MB).
 */
function org_format_file_size(int $bytes): string
{
    if ($bytes < 0) {
        $bytes = 0;
    }
    if ($bytes < 1024) {
        return (string) $bytes . ' B';
    }
    $kb = $bytes / 1024;
    if ($kb < 1024) {
        return (string) (round($kb, $kb < 10 ? 1 : 0)) . ' KB';
    }
    $mb = $kb / 1024;
    if ($mb < 1024) {
        return (string) (round($mb, 1)) . ' MB';
    }
    return (string) (round($mb / 1024, 2)) . ' GB';
}

/**
 * Ikon Font Awesome + kelas warna untuk ekstensi file.
 *
 * @return array{0: string, 1: string} [kelas ikon, kelas warna Bootstrap/FA]
 */
function org_dokumen_icon_for_extension(string $namaFile): array
{
    $ext = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));
    return match ($ext) {
        'pdf' => ['fa-file-pdf', 'text-danger'],
        'doc', 'docx' => ['fa-file-word', 'text-primary'],
        'xls', 'xlsx' => ['fa-file-excel', 'text-success'],
        'jpg', 'jpeg' => ['fa-file-image', 'text-primary'],
        'png', 'gif', 'webp' => ['fa-file-image', 'text-success'],
        default => ['fa-file-lines', 'text-secondary'],
    };
}

/** Daftarkan file ke tabel dokumen agar kolom nama_file ikut dalam filter/pencarian. */
function org_dokumen_register_file(mysqli $db, string $namaFile): void
{
    if (!org_dokumen_table_exists($db)) {
        return;
    }
    $namaFile = basename($namaFile);
    if ($namaFile === '' || $namaFile === '.' || $namaFile === '..') {
        return;
    }
    $kat = org_dokumen_kategori_from_filename($namaFile);
    $st = $db->prepare('INSERT IGNORE INTO `dokumen` (`nama_file`, `kategori`) VALUES (?, ?)');
    if ($st === false) {
        return;
    }
    $st->bind_param('ss', $namaFile, $kat);
    $st->execute();
    $st->close();
}

function org_dokumen_update_kategori_by_filename(mysqli $db, string $namaFile, string $kategori): bool
{
    if (!org_dokumen_table_exists($db)) {
        return false;
    }
    $namaFile = basename($namaFile);
    if ($namaFile === '') {
        return false;
    }
    $kat = org_dokumen_normalize_tim_kategori($kategori);
    $st = $db->prepare('UPDATE `dokumen` SET `kategori` = ? WHERE `nama_file` = ? LIMIT 1');
    if ($st === false) {
        return false;
    }
    $st->bind_param('ss', $kat, $namaFile);
    $ok = $st->execute();
    $st->close();

    return (bool) $ok;
}

function org_dokumen_delete_by_filename(mysqli $db, string $namaFile): void
{
    if (!org_dokumen_table_exists($db)) {
        return;
    }
    $namaFile = basename($namaFile);
    if ($namaFile === '') {
        return;
    }
    $st = $db->prepare('DELETE FROM `dokumen` WHERE `nama_file` = ? LIMIT 1');
    if ($st === false) {
        return;
    }
    $st->bind_param('s', $namaFile);
    $st->execute();
    $st->close();
}

/**
 * Sinkronkan baris DB dengan file yang ada di disk (tambah yang hilang, hapus orphan).
 * Memastikan setiap nama_file terdaftar untuk pencarian perpustakaan (indeks ↔ unggahan baru).
 *
 * @param list<string> $filesOnDisk
 */
function org_dokumen_sync_with_disk(mysqli $db, array $filesOnDisk): void
{
    org_dokumen_ensure_table($db);
    if (!org_dokumen_table_exists($db)) {
        return;
    }
    foreach ($filesOnDisk as $f) {
        if (is_string($f) && $f !== '') {
            org_dokumen_register_file($db, $f);
        }
    }
    if ($filesOnDisk === []) {
        $db->query('DELETE FROM `dokumen`');

        return;
    }
    $placeholders = implode(',', array_fill(0, count($filesOnDisk), '?'));
    $types = str_repeat('s', count($filesOnDisk));
    $sql = 'DELETE FROM `dokumen` WHERE `nama_file` NOT IN (' . $placeholders . ')';
    $st = $db->prepare($sql);
    if ($st === false) {
        return;
    }
    $st->bind_param($types, ...$filesOnDisk);
    $st->execute();
    $st->close();
}

/**
 * Tambah jumlah_unduh (+1). Membuat baris jika belum ada.
 */
function org_dokumen_increment_download(mysqli $db, string $namaFile): bool
{
    org_dokumen_ensure_table($db);
    if (!org_dokumen_table_exists($db)) {
        return false;
    }
    $namaFile = basename($namaFile);
    if ($namaFile === '') {
        return false;
    }
    $kat = org_dokumen_kategori_from_filename($namaFile);
    $sql = 'INSERT INTO `dokumen` (`nama_file`, `kategori`, `jumlah_unduh`) VALUES (?, ?, 1)
            ON DUPLICATE KEY UPDATE `jumlah_unduh` = `jumlah_unduh` + 1';
    $st = $db->prepare($sql);
    if ($st === false) {
        return false;
    }
    $st->bind_param('ss', $namaFile, $kat);
    $ok = $st->execute();
    $st->close();

    return (bool) $ok;
}

/**
 * @return array<string, array{nama_file: string, kategori: string, jumlah_unduh: int, judul: string, deskripsi: string}> keyed by nama_file
 */
function org_dokumen_fetch_stats_map(mysqli $db): array
{
    if (!org_dokumen_table_exists($db)) {
        return [];
    }
    org_dokumen_migrate_metadata_columns($db);
    $map = [];
    $res = $db->query('SELECT `nama_file`, `kategori`, `jumlah_unduh`, `judul`, `deskripsi` FROM `dokumen`');
    if ($res === false) {
        return [];
    }
    while ($row = $res->fetch_assoc()) {
        if (!is_array($row)) {
            continue;
        }
        $nf = (string) ($row['nama_file'] ?? '');
        if ($nf === '') {
            continue;
        }
        $map[$nf] = [
            'nama_file' => $nf,
            'kategori' => org_dokumen_normalize_tim_kategori((string) ($row['kategori'] ?? 'Kelembagaan')),
            'jumlah_unduh' => (int) ($row['jumlah_unduh'] ?? 0),
            'judul' => (string) ($row['judul'] ?? ''),
            'deskripsi' => (string) ($row['deskripsi'] ?? ''),
        ];
    }

    return $map;
}

/**
 * Sinonim singkatan ↔ frasa panjang (perluasan carian dua arah).
 *
 * @return list<list<string>>
 */
function org_dokumen_search_synonym_groups(): array
{
    return [
        ['perbub', 'perbup', 'peraturan bupati', 'bupati'],
        ['perda', 'peraturan daerah'],
        ['perwali', 'peraturan walikota'],
        ['pergub', 'peraturan gubernur'],
        ['anjab', 'analisis jabatan'],
        ['evab', 'evaluasi abk'],
        ['abk', 'analisis beban kerja'],
        ['sakip', 'sistem akuntabilitas kinerja'],
    ];
}

/**
 * Hilangkan ekstensi dokumen dari token / nama agar "PERBUB" cocok dengan "PERBUB_1.pdf".
 */
function org_dokumen_search_normalize_token_strip_ext(string $token): string
{
    $t = trim($token);

    return (string) preg_replace('/\.(pdf|docx?)$/iu', '', $t);
}

/**
 * Stem nama file tanpa ekstensi .pdf / .doc(x) untuk indeks pencarian.
 */
function org_dokumen_search_strip_known_extensions(string $stemOrFilename): string
{
    return (string) preg_replace('/\.(pdf|docx?)$/iu', '', $stemOrFilename);
}

/**
 * Gabungan teks indeks untuk satu dokumen perpustakaan (nama_file, basename tanpa ekstensi,
 * judul/kategori/deskripsi dari DB, sinonim). Setara dengan gabungan LIKE pada:
 * nama_file, judul, kategori, deskripsi — dengan token dinormalisasi tanpa ekstensi.
 */
function org_dokumen_library_search_haystack(
    string $storedFileName,
    array $statRow,
    callable $storedDocumentBasename,
    callable $displayUploadFilename
): string {
    $fn = $storedFileName;
    $base = $storedDocumentBasename($fn);
    $display = $displayUploadFilename($fn);
    $namaDb = (string) ($statRow['nama_file'] ?? $fn);

    $stemBase = (string) pathinfo($base, PATHINFO_FILENAME);
    $stemFn = (string) pathinfo($fn, PATHINFO_FILENAME);

    $stemBaseClean = org_dokumen_search_strip_known_extensions($stemBase);
    $stemFnClean = org_dokumen_search_strip_known_extensions($stemFn);

    $fnBare = org_dokumen_search_strip_known_extensions($fn);
    $baseBare = org_dokumen_search_strip_known_extensions($base);

    $parts = [
        $fn,
        $namaDb,
        $base,
        $fnBare,
        $baseBare,
        $stemBase,
        $stemFn,
        $stemBaseClean,
        $stemFnClean,
        str_replace('_', ' ', $stemBaseClean),
        str_replace('_', ' ', $stemFnClean),
        str_replace('_', ' ', $stemBase),
        str_replace('_', ' ', $stemFn),
        str_replace('_', ' ', $fn),
        str_replace('_', ' ', $namaDb),
        preg_replace('/_+/u', ' ', $stemFnClean),
        $display,
        str_replace('_', ' ', $display),
        (string) ($statRow['kategori'] ?? ''),
        (string) ($statRow['judul'] ?? ''),
        (string) ($statRow['deskripsi'] ?? ''),
    ];

    $raw = implode("\n", $parts);
    $lower = mb_strtolower($raw);

    return org_dokumen_search_append_synonyms_to_haystack($lower);
}

function org_dokumen_search_append_synonyms_to_haystack(string $hayLower): string
{
    $extra = '';
    foreach (org_dokumen_search_synonym_groups() as $terms) {
        $hit = false;
        foreach ($terms as $term) {
            $tl = mb_strtolower((string) $term);
            if ($tl !== '' && mb_strlen($tl) >= 3 && str_contains($hayLower, $tl)) {
                $hit = true;
                break;
            }
        }
        if ($hit) {
            foreach ($terms as $x) {
                $xl = mb_strtolower((string) $x);
                if ($xl !== '') {
                    $extra .= "\n" . $xl;
                }
            }
        }
    }

    return $hayLower . $extra;
}

/**
 * Variasi kata untuk satu token kueri (sinonim + tanpa ekstensi).
 *
 * @return list<string>
 */
function org_dokumen_search_synonym_variants_for_token(string $token): array
{
    $t = mb_strtolower(org_dokumen_search_normalize_token_strip_ext($token));
    if ($t === '') {
        return [];
    }
    $out = [$t];
    foreach (org_dokumen_search_synonym_groups() as $terms) {
        foreach ($terms as $term) {
            if ($t === mb_strtolower((string) $term)) {
                foreach ($terms as $other) {
                    $ot = mb_strtolower((string) $other);
                    if ($ot !== '') {
                        $out[] = $ot;
                    }
                }

                return array_values(array_unique($out));
            }
        }
    }

    return array_values(array_unique($out));
}

function org_dokumen_search_token_matches_haystack(string $haystackLower, string $token): bool
{
    $variants = org_dokumen_search_synonym_variants_for_token($token);
    if ($variants === []) {
        return true;
    }
    foreach ($variants as $v) {
        if ($v !== '' && str_contains($haystackLower, $v)) {
            return true;
        }
    }

    return false;
}

/**
 * Pencarian perpustakaan (AND antar kata). Logika setara SQL per token:
 * (nama_file LIKE '%t%' OR judul LIKE '%t%' OR kategori LIKE '%t%' OR deskripsi LIKE '%t%')
 * dengan normalisasi ekstensi dan sinonim.
 *
 * @param array{kategori?: string, judul?: string, deskripsi?: string, nama_file?: string} $statRow
 */
function org_dokumen_match_library_query(
    string $storedFileName,
    string $query,
    array $statRow,
    callable $storedDocumentBasename,
    callable $displayUploadFilename
): bool {
    $query = trim($query);
    if ($query === '') {
        return true;
    }
    $haystack = org_dokumen_library_search_haystack($storedFileName, $statRow, $storedDocumentBasename, $displayUploadFilename);

    $tokens = preg_split('/\s+/u', $query, -1, PREG_SPLIT_NO_EMPTY);
    if (!is_array($tokens) || $tokens === []) {
        return org_dokumen_search_token_matches_haystack($haystack, $query);
    }
    foreach ($tokens as $tok) {
        if (!org_dokumen_search_token_matches_haystack($haystack, (string) $tok)) {
            return false;
        }
    }

    return true;
}
