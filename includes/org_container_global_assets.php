<?php
declare(strict_types=1);

function org_container_global_asset_base(): string
{
    if (!defined('ORG_WEB_ROOT')) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_database.php';
        define('ORG_WEB_ROOT', org_site_web_root());
    }
    $base = ORG_WEB_ROOT === '' ? '' : rtrim(ORG_WEB_ROOT, '/');

    return $base === '' ? '' : $base . '/';
}

function org_container_global_stylesheet_link(): string
{
    $base = org_container_global_asset_base();
    $global = $base . 'assets/css/org-container-global.css?v=38';
    $panel = $base . 'assets/css/sg-portal-panel-layout.css?v=9';
    $align = $base . 'assets/css/sg-portal-shell-align.css?v=4';
    $guard = $base . 'assets/css/org-overflow-guard.css?v=2';

    return '<link rel="stylesheet" href="' . htmlspecialchars($global, ENT_QUOTES, 'UTF-8') . '">' . "\n"
        . '<link rel="stylesheet" href="' . htmlspecialchars($panel, ENT_QUOTES, 'UTF-8') . '">' . "\n"
        . '<link rel="stylesheet" href="' . htmlspecialchars($align, ENT_QUOTES, 'UTF-8') . '">' . "\n"
        . '<link rel="stylesheet" href="' . htmlspecialchars($guard, ENT_QUOTES, 'UTF-8') . '">' . "\n";
}
