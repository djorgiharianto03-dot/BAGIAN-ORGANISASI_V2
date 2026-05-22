<?php

/**
 * Stylesheet mobile-first situs (semua halaman publik & admin).
 */
function org_mobile_stylesheet_link(): string
{
    if (!defined('ORG_WEB_ROOT')) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_database.php';
        define('ORG_WEB_ROOT', org_site_web_root());
    }
    $base = ORG_WEB_ROOT === '' ? '' : rtrim(ORG_WEB_ROOT, '/');
    $href = ($base !== '' ? $base . '/' : '') . 'assets/css/org-mobile-first.css?v=2';

    return '<link rel="stylesheet" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">' . "\n";
}
