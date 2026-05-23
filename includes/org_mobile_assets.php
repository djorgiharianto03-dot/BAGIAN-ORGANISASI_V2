<?php

/**
 * Stylesheet mobile-first situs (semua halaman publik & admin).
 */
function org_mobile_stylesheet_link(): string
{
    if (!function_exists('org_asset_url')) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_app.php';
    }

    return '<link rel="stylesheet" href="' . htmlspecialchars(org_asset_url('assets/css/org-mobile-first.css?v=3'), ENT_QUOTES, 'UTF-8') . '">' . "\n";
}
