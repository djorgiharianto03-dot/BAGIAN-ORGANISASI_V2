<?php

/**
 * Premium dark mode — boot script, stylesheet, switcher JS.
 */
function org_theme_asset_base(): string
{
    if (!defined('ORG_WEB_ROOT')) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_database.php';
        define('ORG_WEB_ROOT', org_site_web_root());
    }
    $base = ORG_WEB_ROOT === '' ? '' : rtrim(ORG_WEB_ROOT, '/');

    return $base === '' ? '' : $base . '/';
}

/** Inline di <head> sebelum CSS agar tidak kedip saat load. */
function org_theme_boot_script(): string
{
    return '<script>'
        . '(function(){try{'
        . 'var k="org-color-theme",s=localStorage.getItem(k);'
        . 'if(s==="dark"||(s===null&&window.matchMedia&&window.matchMedia("(prefers-color-scheme: dark)").matches)){'
        . 'document.documentElement.setAttribute("data-theme","dark");'
        . '}'
        . '}catch(e){}})();'
        . '</script>' . "\n";
}

function org_theme_stylesheet_link(): string
{
    $href = org_theme_asset_base() . 'assets/css/org-dark-mode.css?v=1';

    return '<link rel="stylesheet" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">' . "\n";
}

/**
 * Org typography (Plus Jakarta Sans + responsive sizes + line-heights).
 * Dimuat di semua halaman (Beranda, Portal, Admin) supaya konsisten.
 * Versi di-bump bila isi org-typography.css berubah agar cache browser bersih.
 */
function org_typography_stylesheet_link(): string
{
    $href = org_theme_asset_base() . 'assets/css/org-typography.css?v=1';

    return '<link rel="stylesheet" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">' . "\n";
}

function org_theme_script_tag(): string
{
    $src = org_theme_asset_base() . 'assets/js/org-theme-switcher.js?v=1';

    return '<script src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" defer></script>' . "\n";
}
