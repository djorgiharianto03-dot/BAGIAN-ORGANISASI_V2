<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_app.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_database.php';

org_force_https_redirect();

$jenis = strtolower(trim((string) ($_GET['jenis'] ?? '')));
$file = basename((string) ($_GET['file'] ?? ''));

if ($file === '' || $file === '.' || $file === '..') {
    http_response_code(400);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Permintaan arsip tidak valid.';

    exit;
}

$subDir = $jenis === 'masuk' ? 'surat_masuk' : ($jenis === 'keluar' ? 'surat_keluar' : '');
if ($subDir === '') {
    http_response_code(400);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Jenis arsip tidak valid (gunakan masuk atau keluar).';

    exit;
}

$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
if ($ext !== 'pdf') {
    http_response_code(400);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Hanya berkas PDF arsip yang dapat diunduh.';

    exit;
}

$root = defined('ORG_ROOT') ? ORG_ROOT : __DIR__;
$uploadDir = $root . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $subDir;
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
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>PDF arsip tidak ditemukan</title></head><body>';
    echo '<p style="font-family:system-ui,sans-serif;padding:1.5rem">Berkas PDF arsip tidak ditemukan di folder <code>uploads/' . htmlspecialchars($subDir, ENT_QUOTES, 'UTF-8') . '/</code>.</p>';
    echo '<p><a href="javascript:history.back()">Kembali</a></p></body></html>';

    exit;
}

header('Content-Type: application/pdf');
header('Content-Length: ' . (string) filesize($targetReal));
header('Content-Disposition: inline; filename="' . str_replace('"', '', $file) . '"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=120');
header('X-Frame-Options: SAMEORIGIN');

readfile($targetReal);
exit;
