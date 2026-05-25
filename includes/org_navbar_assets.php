<?php

function org_navbar_stylesheet_link(): string
{
    if (!function_exists('org_asset_url')) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_app.php';
    }

    return '<link rel="stylesheet" href="' . htmlspecialchars(org_asset_url('assets/css/org-navbar.css?v=20'), ENT_QUOTES, 'UTF-8') . '">' . "\n";
}

function org_navbar_script_tag(): string
{
    if (!function_exists('org_asset_url')) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_app.php';
    }

    return '<script src="' . htmlspecialchars(org_asset_url('assets/js/org-navbar.js?v=9'), ENT_QUOTES, 'UTF-8') . '" defer></script>' . "\n";
}
