<?php
declare(strict_types=1);

/**
 * CLI: php deploy/ensure-upload-dirs.php
 */
$root = dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_upload_dirs.php';

org_ensure_upload_directories($root);

echo "Folder uploads siap.\n";
foreach (org_upload_subdirectory_names() as $sub) {
    $path = $root . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $sub;
    echo '  - uploads/' . $sub . (is_dir($path) ? ' OK' : ' GAGAL') . "\n";
}
