<?php
$_SERVER['DOCUMENT_ROOT'] = 'C:/laragon/www';
$_SERVER['SCRIPT_NAME'] = '/BAGIAN ORGANISASI_V2/index.php';
define('ORG_ROOT', dirname(__DIR__));
require ORG_ROOT . '/includes/org_database.php';
require ORG_ROOT . '/includes/org_app.php';
echo 'web_root=' . org_site_web_root() . PHP_EOL;
echo 'profil=' . org_page_url('profil.php') . PHP_EOL;
echo 'home=' . org_home_url() . PHP_EOL;
