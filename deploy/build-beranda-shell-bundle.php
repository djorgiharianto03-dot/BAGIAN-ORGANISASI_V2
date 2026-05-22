<?php
declare(strict_types=1);

/**
 * Gabung CSS shell portal beranda → assets/css/beranda-shell.bundle.min.css
 * Jalankan: php deploy/build-beranda-shell-bundle.php
 */

$root = dirname(__DIR__);
$outFile = $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'beranda-shell.bundle.min.css';

$sources = [
    $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'org-container-global.css',
    $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'sg-portal-panel-layout.css',
    $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'sg-portal-shell-align.css',
    $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'org-overflow-guard.css',
    $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'smart-governance-portal-layout-fix.css',
];

function org_shell_minify_css(string $css): string
{
    $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css) ?? $css;
    $css = preg_replace('/\s+/', ' ', $css) ?? $css;
    $css = preg_replace('/\s*([{}:;,>+~])\s*/', '$1', $css) ?? $css;
    $css = preg_replace('/;}/', '}', $css) ?? $css;

    return trim($css);
}

$combined = '/* Beranda shell bundle — generated ' . date('c') . " */\n";
foreach ($sources as $path) {
    if (!is_file($path)) {
        fwrite(STDERR, "SKIP (missing): {$path}\n");
        continue;
    }
    $combined .= "\n/* === " . basename($path) . " === */\n";
    $combined .= file_get_contents($path);
    $combined .= "\n";
}

$min = org_shell_minify_css($combined);
if (file_put_contents($outFile, $min) === false) {
    fwrite(STDERR, "Failed to write {$outFile}\n");
    exit(1);
}

echo "Written: {$outFile}\n";
echo 'Size: ' . number_format(strlen($combined)) . ' → ' . number_format(strlen($min)) . " bytes\n";
