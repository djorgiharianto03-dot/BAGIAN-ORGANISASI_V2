<?php
declare(strict_types=1);

/**
 * Ekspor CSS dari includes/partials/site_styles.php → assets/css/site-global.min.css
 * Jalankan: php deploy/build-site-global-css.php
 */

$root = dirname(__DIR__);
$src = $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'site_styles.php';
$out = $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'site-global.min.css';

$raw = file_get_contents($src);
if ($raw === false) {
    fwrite(STDERR, "Cannot read site_styles.php\n");
    exit(1);
}
if (!preg_match('/<style>\s*(.*)\s*<\/style>/s', $raw, $m)) {
    fwrite(STDERR, "No <style> block found\n");
    exit(1);
}
$css = trim($m[1]);
$css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css) ?? $css;
$css = preg_replace('/\s+/', ' ', $css) ?? $css;
$css = preg_replace('/\s*([{}:;,>+~])\s*/', '$1', $css) ?? $css;
$css = preg_replace('/;}/', '}', $css) ?? $css;

$header = "/* Generated from site_styles.php — " . date('c') . " */\n";
file_put_contents($out, $header . $css);
echo "Written: {$out}\n";
echo 'Size: ' . strlen($css) . " bytes (minified)\n";
