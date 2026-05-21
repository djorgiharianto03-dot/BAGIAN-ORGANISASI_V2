<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_database.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'dokumen_db.php';

$file = basename((string) ($_GET['file'] ?? ''));
if ($file === '' || $file === '.' || $file === '..' || !org_dokumen_is_library_file($file)) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Permintaan tidak valid.';

    exit;
}

$uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'perpustakaan_digital';
$targetPath = $uploadDir . DIRECTORY_SEPARATOR . $file;
$uploadDirReal = realpath($uploadDir);
$targetReal = realpath($targetPath);

if (
    $uploadDirReal === false
    || $targetReal === false
    || !is_file($targetReal)
    || dirname($targetReal) !== $uploadDirReal
) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Berkas tidak ditemukan.';

    exit;
}

$db = org_db();
if ($db instanceof mysqli) {
    org_dokumen_increment_download($db, $file);
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = $finfo !== false ? (string) finfo_file($finfo, $targetReal) : 'application/octet-stream';
if ($finfo !== false) {
    finfo_close($finfo);
}

$downloadName = str_replace('_', ' ', pathinfo($file, PATHINFO_FILENAME));
$ext = pathinfo($file, PATHINFO_EXTENSION);
if ($ext !== '') {
    $downloadName .= '.' . $ext;
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . (string) filesize($targetReal));
header('Content-Disposition: attachment; filename="' . str_replace('"', '', $downloadName) . '"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=0, must-revalidate');

readfile($targetReal);

exit;
