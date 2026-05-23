<?php

/**
 * Gabung + minify CSS beranda → assets/css/beranda.bundle.min.css
 * Jalankan: php deploy/build-beranda-bundle.php
 */

$root = dirname(__DIR__);
$outFile = $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'beranda.bundle.min.css';

$sources = [
    $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'beranda-page.css',
    $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'smart-governance-homepage.css',
    $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'smart-governance-beranda-ultra.css',
    $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'smart-governance-beranda-premium.css',
    $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'smart-governance-beranda-govtech.css',
    $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'smart-governance-beranda-polish.css',
    $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'beranda-layout-fix.css',
    $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'beranda-lightweight.css',
    $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'beranda-mobile.css',
    $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'beranda-design-system.css',
    $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'beranda-nav-hero.css',
    $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'beranda-home-layout.css',
    $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'beranda-rail-unify.css',
    $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'beranda-dashboard-cards.css',
    $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'beranda-hero-fix-active.css',
    $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'beranda-sections.css',
];

/* beranda-dashboard-cards.css must stay last in bundle for cascade */

function org_minify_css_string(string $css): string
{
    $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css) ?? $css;
    $css = preg_replace('/\s+/', ' ', $css) ?? $css;
    $css = preg_replace('/\s*([{}:;,>+~])\s*/', '$1', $css) ?? $css;
    $css = preg_replace('/;}/', '}', $css) ?? $css;

    return trim($css);
}

$combined = "/* Beranda bundle — generated " . date('c') . " */\n";
foreach ($sources as $path) {
    if (!is_file($path)) {
        fwrite(STDERR, "SKIP (missing): {$path}\n");
        continue;
    }
    $combined .= "\n/* === " . basename($path) . " === */\n";
    $combined .= file_get_contents($path);
    $combined .= "\n";
}

$min = org_minify_css_string($combined);
$written = file_put_contents($outFile, $min);
if ($written === false) {
    fwrite(STDERR, "Failed to write {$outFile}\n");
    exit(1);
}

$rawSize = strlen($combined);
$minSize = strlen($min);
echo "Written: {$outFile}\n";
echo 'Size: ' . number_format($rawSize) . ' → ' . number_format($minSize) . ' bytes (' . round(100 - ($minSize / max(1, $rawSize) * 100), 1) . "% smaller)\n";
