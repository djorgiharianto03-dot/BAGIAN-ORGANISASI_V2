<?php

/**
 * Build semua aset production (CSS bundle, JS minify, site-global).
 * Jalankan: php deploy/build-production-assets.php
 */

$root = dirname(__DIR__);
define('ORG_ROOT', $root);

require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_build_assets.php';

$steps = [
    'site-global.min.css' => static function (): bool {
        return org_build_assets_generate_site_global();
    },
    'beranda.bundle.min.css' => static function (): bool {
        return org_build_assets_write_bundle('assets/css/beranda.bundle.min.css', [
            'assets/css/beranda-page.css',
            'assets/css/smart-governance-homepage.css',
            'assets/css/beranda-layout-fix.css',
            'assets/css/beranda-lightweight.css',
            'assets/css/beranda-mobile.css',
            'assets/css/beranda-design-system.css',
            'assets/css/beranda-nav-hero.css',
            'assets/css/beranda-dashboard-cards.css',
        ]);
    },
    'beranda-shell.bundle.min.css' => static function (): bool {
        return org_build_assets_write_bundle('assets/css/beranda-shell.bundle.min.css', [
            'assets/css/org-container-global.css', /* v39 — hero minimal compact */
            'assets/css/sg-portal-panel-layout.css',
            'assets/css/sg-portal-shell-align.css',
            'assets/css/org-overflow-guard.css',
            'assets/css/smart-governance-portal-layout-fix.css',
        ]);
    },
];

echo "== Build production assets ==\n";
$ok = true;
foreach ($steps as $label => $fn) {
    $result = $fn();
    echo ($result ? '[ok]' : '[FAIL]') . " {$label}\n";
    $ok = $ok && $result;
}

org_build_assets_generate_beranda_js_min();
$jsFiles = glob($root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . '*.min.js') ?: [];
echo '[ok] JS minify: ' . count($jsFiles) . " file(s)\n";

exit($ok ? 0 : 1);
