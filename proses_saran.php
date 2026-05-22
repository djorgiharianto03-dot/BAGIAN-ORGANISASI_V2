<?php

/**
 * Saran & kritik (footer) — simpan ke tabel saran_kritik.
 * - AJAX: JSON { ok, message }
 * - Form POST biasa: redirect index.php?saran=sukses|gagal
 *
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_database.php';

if (org_is_dev_environment()) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
}
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'saran_kritik_db.php';

function org_saran_wants_json_response(): bool
{
    $ct = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
    if (stripos($ct, 'application/json') !== false) {
        return true;
    }
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    if (stripos($accept, 'application/json') !== false) {
        return true;
    }
    if (strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '', 'XMLHttpRequest') === 0) {
        return true;
    }
    return false;
}

function org_saran_send_json(bool $ok, string $message, int $httpCode = 200): void
{
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=UTF-8');
    header('X-Content-Type-Options: nosniff');
    echo json_encode(['ok' => $ok, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

function org_saran_redirect(string $query): void
{
    $target = 'index.php' . ($query !== '' ? '?' . $query : '');
    header('Location: ' . $target, true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (org_saran_wants_json_response()) {
        org_saran_send_json(false, 'Metode tidak diizinkan.', 405);
    }
    org_saran_redirect('');
}

$wantsJson = org_saran_wants_json_response();

$data = [];
if ($wantsJson) {
    $raw = file_get_contents('php://input');
    if ($raw !== false && $raw !== '') {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $data = $decoded;
        }
    }
}
if ($data === []) {
    $data = $_POST;
}

// Form footer / POST biasa: name="nama"|"email"|"pesan". AJAX JSON: kunci sama.
if ($wantsJson) {
    $nama = trim((string) ($data['nama'] ?? ''));
    $email = trim((string) ($data['email'] ?? ''));
    $pesan = trim((string) ($data['pesan'] ?? ''));
} else {
    $nama = trim((string) ($_POST['nama'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $pesan = trim((string) ($_POST['pesan'] ?? ''));
}

if ($nama === '' || $email === '' || $pesan === '') {
    if ($wantsJson) {
        org_saran_send_json(false, 'Nama, email, dan pesan wajib diisi.', 400);
    }
    org_saran_redirect('saran=gagal&alasan=' . rawurlencode('input'));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    if ($wantsJson) {
        org_saran_send_json(false, 'Format email tidak valid.', 400);
    }
    org_saran_redirect('saran=gagal&alasan=' . rawurlencode('email'));
}

if (strlen($nama) > 190 || strlen($email) > 190 || strlen($pesan) > 20000) {
    if ($wantsJson) {
        org_saran_send_json(false, 'Panjang input melebihi batas.', 400);
    }
    org_saran_redirect('saran=gagal&alasan=' . rawurlencode('panjang'));
}

$db = org_db();
if ($db === null) {
    if ($wantsJson) {
        org_saran_send_json(false, 'Tidak dapat terhubung ke database. Periksa config/database.php.', 503);
    }
    org_saran_redirect('saran=gagal&alasan=' . rawurlencode('db'));
}

org_saran_kritik_ensure_table($db);

if (!org_saran_kritik_table_exists($db)) {
    if ($wantsJson) {
        org_saran_send_json(false, 'Tabel saran_kritik tidak tersedia.', 503);
    }
    org_saran_redirect('saran=gagal&alasan=' . rawurlencode('tabel'));
}

$pesanPlain = strip_tags($pesan);

org_saran_kritik_ensure_tgl_kirim_column($db);
$cols = org_saran_kritik_columns($db);
// Kolom huruf kecil: nama, email, pesan (+ tgl_kirim jika ada, supaya waktu konsisten)
$sql = isset($cols['tgl_kirim'])
    ? 'INSERT INTO saran_kritik (nama, email, pesan, tgl_kirim) VALUES (?, ?, ?, NOW())'
    : 'INSERT INTO saran_kritik (nama, email, pesan) VALUES (?, ?, ?)';
$st = $db->prepare($sql);
if ($st === false) {
    error_log('proses_saran.php prepare failed: ' . $db->error);
    if ($wantsJson) {
        org_saran_send_json(false, 'Gagal menyimpan saran (prepare). ' . $db->error, 500);
    }
    org_saran_redirect('saran=gagal&alasan=' . rawurlencode('prepare'));
}

$st->bind_param('sss', $nama, $email, $pesanPlain);
if (!$st->execute()) {
    $execErr = $st->error;
    error_log('proses_saran.php execute failed: ' . $execErr);
    $st->close();
    if ($wantsJson) {
        org_saran_send_json(false, 'Gagal menyimpan saran (execute). ' . $execErr, 500);
    }
    org_saran_redirect('saran=gagal&alasan=' . rawurlencode('simpan'));
}
$st->close();

if ($wantsJson) {
    org_saran_send_json(true, 'Terima kasih, saran Anda telah terkirim!');
}

org_saran_redirect('saran=sukses');
