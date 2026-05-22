<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'bootstrap.php';
org_require_level_access(['super_admin', 'admin', 'sub_admin_eorganisasi']);
require __DIR__ . DIRECTORY_SEPARATOR . 'export_laporan.php';
