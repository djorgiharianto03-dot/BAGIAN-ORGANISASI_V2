<?php

/**
 * Aset build permanen di assets/css/ — tidak di uploads/.cache.
 * Dibangun ulang otomatis jika file hilang (tanpa fatal error).
 */

function org_build_assets_css_minify(string $css): string
{
    $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css) ?? $css;
    $css = preg_replace('/\s+/', ' ', $css) ?? $css;
    $css = preg_replace('/\s*([{}:;,>+~])\s*/', '$1', $css) ?? $css;
    $css = preg_replace('/;}/', '}', $css) ?? $css;

    return trim($css);
}

function org_build_assets_js_minify(string $js): string
{
    $js = preg_replace('#/\*[\s\S]*?\*/#', '', $js) ?? $js;
    $lines = preg_split('/\R/', $js) ?: [];
    $out = [];
    foreach ($lines as $line) {
        if (preg_match('#^\s*//.#', $line)) {
            continue;
        }
        $trimmed = preg_replace('#\s*//.*$#', '', $line) ?? $line;
        $trimmed = rtrim($trimmed);
        if ($trimmed === '') {
            continue;
        }
        $out[] = $trimmed;
    }

    return implode("\n", $out);
}

/**
 * @param list<string> $sourceRelativePaths
 */
function org_build_assets_write_js_bundle(string $outRelativePath, array $sourceRelativePaths): bool
{
    $outFs = org_build_assets_fs_path($outRelativePath);
    $outDir = dirname($outFs);
    if (!is_dir($outDir) && !@mkdir($outDir, 0775, true) && !is_dir($outDir)) {
        return false;
    }

    $combined = '';
    foreach ($sourceRelativePaths as $rel) {
        $path = org_build_assets_fs_path($rel);
        if (!is_file($path)) {
            continue;
        }
        $chunk = @file_get_contents($path);
        if ($chunk === false || $chunk === '') {
            continue;
        }
        $combined .= "\n;/* === " . basename($path) . " === */\n" . $chunk;
    }

    if (strlen($combined) < 40) {
        return false;
    }

    $min = org_build_assets_js_minify($combined);

    return @file_put_contents($outFs, $min, LOCK_EX) !== false && is_file($outFs);
}

/**
 * Minify satu file JS → assets/js/{name}.min.js
 */
function org_build_assets_minify_js_file(string $sourceRelativePath): bool
{
    $path = org_build_assets_fs_path($sourceRelativePath);
    if (!is_file($path)) {
        return false;
    }
    $raw = @file_get_contents($path);
    if ($raw === false || $raw === '') {
        return false;
    }
    $base = basename($sourceRelativePath);
    $base = preg_replace('/\.js$/i', '', $base) ?? $base;
    $outRel = 'assets/js/' . $base . '.min.js';
    $outFs = org_build_assets_fs_path($outRel);
    $min = org_build_assets_js_minify($raw);

    return @file_put_contents($outFs, $min, LOCK_EX) !== false;
}

function org_build_assets_generate_beranda_js_min(): void
{
    foreach ([
        'assets/js/beranda-lite-render.js',
        'assets/js/beranda-deferred-load.js',
        'assets/js/beranda-gov-kpi-modal.js',
        'assets/js/beranda-team-target-charts.js',
        'assets/js/org-navbar.js',
    ] as $rel) {
        try {
            org_build_assets_minify_js_file($rel);
        } catch (Throwable) {
            /* skip */
        }
    }
}

function org_build_assets_fs_path(string $relativePath): string
{
    return ORG_ROOT . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($relativePath, '/'));
}

function org_build_assets_is_present(string $relativePath, int $minBytes = 64): bool
{
    $fs = org_build_assets_fs_path($relativePath);

    return is_file($fs) && (int) filesize($fs) >= $minBytes;
}

/**
 * @param list<string> $sourceRelativePaths
 */
function org_build_assets_write_bundle(string $outRelativePath, array $sourceRelativePaths): bool
{
    $outFs = org_build_assets_fs_path($outRelativePath);
    $outDir = dirname($outFs);
    if (!is_dir($outDir) && !@mkdir($outDir, 0775, true) && !is_dir($outDir)) {
        return false;
    }

    $combined = '/* Generated ' . date('c') . " */\n";
    foreach ($sourceRelativePaths as $rel) {
        $path = org_build_assets_fs_path($rel);
        if (!is_file($path)) {
            continue;
        }
        $chunk = @file_get_contents($path);
        if ($chunk === false || $chunk === '') {
            continue;
        }
        $combined .= "\n/* === " . basename($path) . " === */\n" . $chunk . "\n";
    }

    if (strlen($combined) < 80) {
        return false;
    }

    $min = org_build_assets_css_minify($combined);

    return @file_put_contents($outFs, $min, LOCK_EX) !== false && is_file($outFs);
}

function org_build_assets_generate_site_global(): bool
{
    if (org_build_assets_is_present('assets/css/site-global.min.css')) {
        return true;
    }

    $src = ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'site_styles.php';
    if (!is_file($src)) {
        return false;
    }
    $raw = @file_get_contents($src);
    if ($raw === false || $raw === '' || !preg_match('/<style>\s*(.*)\s*<\/style>/s', $raw, $m)) {
        return false;
    }
    $css = org_build_assets_css_minify(trim($m[1]));
    $header = '/* Generated from site_styles.php — ' . date('c') . " */\n";
    $outFs = org_build_assets_fs_path('assets/css/site-global.min.css');
    $outDir = dirname($outFs);
    if (!is_dir($outDir) && !@mkdir($outDir, 0775, true) && !is_dir($outDir)) {
        return false;
    }

    return @file_put_contents($outFs, $header . $css, LOCK_EX) !== false;
}

/**
 * @param list<string> $sourceRelativePaths
 */
function org_build_assets_bundle_stale(string $bundleRel, array $sourceRelativePaths): bool
{
    $bundleFs = org_build_assets_fs_path($bundleRel);
    if (!is_file($bundleFs)) {
        return true;
    }
    $bundleMtime = (int) filemtime($bundleFs);
    foreach ($sourceRelativePaths as $rel) {
        $path = org_build_assets_fs_path($rel);
        if (is_file($path) && (int) filemtime($path) > $bundleMtime) {
            return true;
        }
    }

    return false;
}

function org_build_assets_generate_beranda_bundle(): bool
{
    $sources = [
        'assets/css/beranda-page.css',
        'assets/css/smart-governance-homepage.css',
        'assets/css/smart-governance-beranda-ultra.css',
        'assets/css/smart-governance-beranda-premium.css',
        'assets/css/smart-governance-beranda-govtech.css',
        'assets/css/smart-governance-beranda-polish.css',
        'assets/css/beranda-layout-fix.css',
        'assets/css/beranda-lightweight.css',
        'assets/css/beranda-mobile.css',
        'assets/css/beranda-design-system.css',
        'assets/css/beranda-nav-hero.css',
        'assets/css/beranda-home-layout.css',
        'assets/css/beranda-rail-unify.css',
        'assets/css/beranda-dashboard-cards.css',
        'assets/css/beranda-hero-fix-active.css',
    ];

    if (
        org_build_assets_is_present('assets/css/beranda.bundle.min.css', 256)
        && !org_build_assets_bundle_stale('assets/css/beranda.bundle.min.css', $sources)
    ) {
        return true;
    }

    return org_build_assets_write_bundle('assets/css/beranda.bundle.min.css', $sources);
}

function org_build_assets_generate_beranda_shell_bundle(): bool
{
    if (org_build_assets_is_present('assets/css/beranda-shell.bundle.min.css', 128)) {
        return true;
    }

    return org_build_assets_write_bundle('assets/css/beranda-shell.bundle.min.css', [
        'assets/css/org-container-global.css',
        'assets/css/sg-portal-panel-layout.css',
        'assets/css/sg-portal-shell-align.css',
        'assets/css/org-overflow-guard.css',
        'assets/css/smart-governance-portal-layout-fix.css',
    ]);
}

function org_build_assets_ensure_beranda(): void
{
    static $ran = false;
    if ($ran) {
        return;
    }
    $ran = true;

    try {
        org_build_assets_generate_site_global();
        org_build_assets_generate_beranda_bundle();
        org_build_assets_generate_beranda_shell_bundle();
        org_build_assets_generate_beranda_js_min();
    } catch (Throwable) {
        /* fallback ke CSS sumber per-file di org_beranda_assets */
    }
}
