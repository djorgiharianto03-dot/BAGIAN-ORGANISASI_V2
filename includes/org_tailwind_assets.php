<?php
declare(strict_types=1);

function org_tailwind_asset_base(): string
{
    if (!defined('ORG_WEB_ROOT')) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_database.php';
        define('ORG_WEB_ROOT', org_site_web_root());
    }
    $base = ORG_WEB_ROOT === '' ? '' : rtrim(ORG_WEB_ROOT, '/');

    return $base === '' ? '' : $base . '/';
}

function org_tailwind_stylesheet_link(): string
{
    $href = org_tailwind_asset_base() . 'assets/css/org-tailwind.css?v=2';

    return '<link rel="stylesheet" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">' . "\n";
}

function org_tailwind_bootstrap(): void
{
    static $loaded = false;
    if ($loaded) {
        return;
    }
    $loaded = true;
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'org_ui.php';
}
