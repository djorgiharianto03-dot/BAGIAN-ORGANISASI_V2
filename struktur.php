<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_database.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_app.php';
if (!defined('ORG_WEB_ROOT')) {
    define('ORG_WEB_ROOT', org_site_web_root());
}
org_redirect('profil.php', '', 'profil-struktur-organisasi', 301);
