<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_app.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_database.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'dokumen_db.php';

org_force_https_redirect();

$file = (string) ($_GET['file'] ?? '');
if ($file === '' || !org_dokumen_is_library_file($file)) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Permintaan unduhan tidak valid.';

    exit;
}

org_dokumen_send_http($file, 'attachment');
