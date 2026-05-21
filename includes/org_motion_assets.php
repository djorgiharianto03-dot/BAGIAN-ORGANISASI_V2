<?php
declare(strict_types=1);

/**
 * Micro-interactions & motion (CSS + JS) — situs publik.
 */
function org_motion_asset_base(): string
{
    if (!defined('ORG_WEB_ROOT')) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_database.php';
        define('ORG_WEB_ROOT', org_site_web_root());
    }
    $base = ORG_WEB_ROOT === '' ? '' : rtrim(ORG_WEB_ROOT, '/');

    return $base === '' ? '' : $base . '/';
}

function org_motion_stylesheet_link(): string
{
    $href = org_motion_asset_base() . 'assets/css/org-micro-interactions.css?v=1';

    return '<link rel="stylesheet" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">' . "\n";
}

function org_motion_script_tag(): string
{
    $src = org_motion_asset_base() . 'assets/js/org-micro-interactions.js?v=1';

    return '<script src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" defer></script>' . "\n";
}
