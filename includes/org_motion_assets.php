<?php

/**
 * Micro-interactions & motion (CSS + JS) — situs publik.
 */
function org_motion_stylesheet_link(): string
{
    if (!function_exists('org_asset_url')) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_app.php';
    }
    $href = org_asset_url('assets/css/org-micro-interactions.css?v=3');

    return '<link rel="stylesheet" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">' . "\n";
}

function org_motion_script_tag(): string
{
    if (!function_exists('org_asset_url')) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_app.php';
    }
    $src = org_asset_url('assets/js/org-micro-interactions.js?v=3');

    return '<script src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" defer></script>' . "\n";
}
