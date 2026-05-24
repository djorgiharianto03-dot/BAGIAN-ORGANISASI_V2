<?php
declare(strict_types=1);

function org_container_global_stylesheet_link(): string
{
    if (!function_exists('org_asset_url')) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_app.php';
    }

    return '<link rel="stylesheet" href="' . htmlspecialchars(org_asset_url('assets/css/org-container-global.css?v=44'), ENT_QUOTES, 'UTF-8') . '">' . "\n"
        . '<link rel="stylesheet" href="' . htmlspecialchars(org_asset_url('assets/css/sg-portal-panel-layout.css?v=12'), ENT_QUOTES, 'UTF-8') . '">' . "\n"
        . '<link rel="stylesheet" href="' . htmlspecialchars(org_asset_url('assets/css/sg-portal-shell-align.css?v=9'), ENT_QUOTES, 'UTF-8') . '">' . "\n"
        . '<link rel="stylesheet" href="' . htmlspecialchars(org_asset_url('assets/css/org-overflow-guard.css?v=5'), ENT_QUOTES, 'UTF-8') . '">' . "\n"
        . '<link rel="stylesheet" href="' . htmlspecialchars(org_asset_url('assets/css/beranda-portal-rail.css?v=4'), ENT_QUOTES, 'UTF-8') . '">' . "\n";
}
