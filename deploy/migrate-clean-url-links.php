<?php

/**
 * Satu kali: ganti href/action statis *.php → org_href() di template PHP.
 * Lewati file yang sudah memakai org_href/org_page_url.
 */
$root = dirname(__DIR__);

/** @var array<string, string> */
$hrefMap = [
    'profil.php' => "org_href('profil.php')",
    'layanan.php' => "org_href('layanan.php')",
    'dokumen.php' => "org_href('dokumen.php')",
    'berita.php' => "org_href('berita.php')",
    'galeri.php' => "org_href('galeri.php')",
    'informasi.php' => "org_href('informasi.php')",
    'e_organisasi.php' => "org_href('e_organisasi.php')",
    'manajemen_tugas.php' => "org_href('manajemen_tugas.php')",
    'monitoring_disposisi.php' => "org_href('monitoring_disposisi.php')",
    'monitoring-disposisi.php' => "org_href('monitoring-disposisi.php')",
    'disposisi_awal_kabag.php' => "org_href('disposisi_awal_kabag.php')",
    'disposisi_terbaru.php' => "org_href('disposisi_terbaru.php')",
    'arsip.php' => "org_href('arsip.php')",
    'beranda.php' => "org_href('index.php')",
    '../index.php' => "htmlspecialchars(org_home_url(), ENT_QUOTES, 'UTF-8')",
    '../dokumen.php' => "org_href('dokumen.php')",
    'admin/dashboard.php' => "org_href('admin/dashboard.php')",
    '../admin/dashboard.php' => "org_href('admin/dashboard.php')",
    'dashboard.php' => "org_href('admin/dashboard.php')",
    'kelola_dashboard_widgets.php' => "org_href('admin/kelola_dashboard_widgets.php')",
    'kelola_team_targets.php' => "org_href('admin/kelola_team_targets.php')",
    'daftar_saran_kritik.php' => "org_href('admin/daftar_saran_kritik.php')",
    'laporan_audit.php' => "org_href('admin/laporan_audit.php')",
];

/** @var array<string, string> */
$actionMap = [
    'dokumen.php' => "org_href('dokumen.php')",
    'manajemen_tugas.php' => "org_href('manajemen_tugas.php')",
    'dashboard.php' => "org_href('admin/dashboard.php')",
    '../index.php' => "htmlspecialchars(org_home_url(), ENT_QUOTES, 'UTF-8')",
];

$changed = [];

$iter = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);

foreach ($iter as $fileInfo) {
    if (!$fileInfo->isFile() || $fileInfo->getExtension() !== 'php') {
        continue;
    }
    $path = str_replace('\\', '/', $fileInfo->getPathname());
    if (str_contains($path, '/vendor/') || str_contains($path, '/deploy/migrate-clean-url-links.php')) {
        continue;
    }

    $content = file_get_contents($path);
    if ($content === false) {
        continue;
    }
    $orig = $content;

    foreach ($hrefMap as $target => $expr) {
        $pattern = '/href="' . preg_quote($target, '/') . '([^"]*)"/';
        $content = preg_replace_callback($pattern, static function (array $m) use ($expr, $target): string {
            $extra = $m[1];
            if (str_contains($extra, '<?php')) {
                return $m[0];
            }
            if ($target === 'informasi.php' && str_starts_with($extra, '?id=')) {
                return 'href="<?php echo org_href(\'informasi.php\', ' . substr($extra, 1) . '); ?>"';
            }
            if (str_contains($extra, '#')) {
                [$q, $frag] = array_pad(explode('#', ltrim($extra, '?'), 2), 2, '');
                $fragPart = $frag !== '' ? ", '" . addslashes($frag) . "'" : '';
                if ($q !== '') {
                    return 'href="<?php echo org_href(\'' . addslashes(str_replace('admin/', '', $target)) . '\', \'' . addslashes($q) . '\'' . $fragPart . '); ?>"';
                }

                return 'href="<?php echo org_href(\'' . addslashes($target) . '\', \'\'' . $fragPart . '); ?>"';
            }
            if (str_starts_with($extra, '?')) {
                return 'href="<?php echo org_href(\'' . addslashes($target) . '\', \'' . addslashes(substr($extra, 1)) . '\'); ?>"';
            }

            return 'href="<?php echo ' . $expr . '; ?>"';
        }, $content) ?? $content;
    }

    foreach ($actionMap as $target => $expr) {
        $pattern = '/action="' . preg_quote($target, '/') . '([^"]*)"/';
        $content = preg_replace_callback($pattern, static function (array $m) use ($expr, $target): string {
            $extra = $m[1];
            if (str_contains($extra, '<?php')) {
                return $m[0];
            }
            if (str_contains($extra, '#')) {
                return 'action="<?php echo org_href(\'' . addslashes($target === 'dashboard.php' ? 'admin/dashboard.php' : $target) . '\', \'\', \'' . addslashes(ltrim($extra, '#')) . '\'); ?>"';
            }

            return 'action="<?php echo ' . $expr . '; ?>"';
        }, $content) ?? $content;
    }

  // index.php#fragment
    $content = preg_replace(
        '/href="index\.php(#[^"]+)"/',
        'href="<?php echo org_href(\'index.php\', \'\', \'$1\'); ?>"',
        $content
    ) ?? $content;

    if ($content !== $orig) {
        file_put_contents($path, $content);
        $changed[] = str_replace($root . '/', '', $path);
    }
}

echo 'Updated ' . count($changed) . " files:\n";
foreach ($changed as $rel) {
    echo " - $rel\n";
}
