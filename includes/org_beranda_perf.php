<?php
declare(strict_types=1);

/**
 * Kinerja halaman beranda: cache ringan statistik tamu & kurangi query berulang.
 */

function org_beranda_is_light_page(): bool
{
    return defined('ORG_BERANDA_PAGE') && ORG_BERANDA_PAGE === true;
}

function org_beranda_cache_dir(): string
{
    $dir = ORG_ROOT . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . '.cache';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    return $dir;
}

/**
 * @return array{labels: list<string>, values: list<int>, total_today: int, total_week: int}
 */
function org_beranda_fetch_visit_stats(?mysqli $db): array
{
    $empty = static function (): array {
        $labels = [];
        $values = [];
        for ($i = 13; $i >= 0; $i--) {
            $labels[] = date('d M', strtotime("-{$i} days"));
            $values[] = 0;
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'total_today' => 0,
            'total_week' => 0,
        ];
    };

    $cacheFile = org_beranda_cache_dir() . DIRECTORY_SEPARATOR . 'beranda_visit_stats.json';
    $ttl = 300;
    if (is_file($cacheFile)) {
        $age = time() - (int) filemtime($cacheFile);
        if ($age >= 0 && $age < $ttl) {
            $raw = file_get_contents($cacheFile);
            if ($raw !== false && $raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded) && isset($decoded['labels'], $decoded['values'])) {
                    return [
                        'labels' => array_values($decoded['labels']),
                        'values' => array_map('intval', (array) $decoded['values']),
                        'total_today' => (int) ($decoded['total_today'] ?? 0),
                        'total_week' => (int) ($decoded['total_week'] ?? 0),
                    ];
                }
            }
        }
    }

    $result = $empty();
    if (!$db instanceof mysqli) {
        return $result;
    }

    $schemaFile = org_beranda_cache_dir() . DIRECTORY_SEPARATOR . 'tamu_date_field.txt';
    $dateField = '';
    if (is_file($schemaFile) && (time() - (int) filemtime($schemaFile)) < 86400) {
        $dateField = trim((string) file_get_contents($schemaFile));
    } else {
        $tableTamuRes = $db->query("SHOW TABLES LIKE 'tamu'");
        if ($tableTamuRes === false || $tableTamuRes->num_rows === 0) {
            if ($tableTamuRes instanceof mysqli_result) {
                $tableTamuRes->free();
            }

            return $result;
        }
        $tableTamuRes->free();

        $tamuColRes = $db->query("SHOW COLUMNS FROM `tamu`");
        $tamuCols = [];
        if ($tamuColRes !== false) {
            while ($col = $tamuColRes->fetch_assoc()) {
                $field = (string) ($col['Field'] ?? '');
                if ($field !== '') {
                    $tamuCols[$field] = true;
                }
            }
            $tamuColRes->free();
        }
        if (isset($tamuCols['created_at'])) {
            $dateField = 'created_at';
        } elseif (isset($tamuCols['tanggal'])) {
            $dateField = 'tanggal';
        } elseif (isset($tamuCols['tanggal_kunjungan'])) {
            $dateField = 'tanggal_kunjungan';
        }
        @file_put_contents($schemaFile, $dateField);
    }

    if ($dateField === '') {
        return $result;
    }

    $startDate = date('Y-m-d', strtotime('-13 days'));
    $endDate = date('Y-m-d');
    $countsByDate = [];
    $dateColSql = '`' . str_replace('`', '``', $dateField) . '`';
    $stmtTrend = $db->prepare(
        "SELECT DATE({$dateColSql}) AS d, COUNT(*) AS c
         FROM `tamu`
         WHERE DATE({$dateColSql}) BETWEEN ? AND ?
         GROUP BY DATE({$dateColSql})"
    );
    if ($stmtTrend !== false) {
        $stmtTrend->bind_param('ss', $startDate, $endDate);
        if ($stmtTrend->execute()) {
            $resTrend = $stmtTrend->get_result();
            if ($resTrend !== false) {
                while ($trendRow = $resTrend->fetch_assoc()) {
                    $d = (string) ($trendRow['d'] ?? '');
                    if ($d !== '') {
                        $countsByDate[$d] = (int) ($trendRow['c'] ?? 0);
                    }
                }
                $resTrend->free();
            }
        }
        $stmtTrend->close();
    }

    $labels = [];
    $values = [];
    for ($i = 13; $i >= 0; $i--) {
        $dateKey = date('Y-m-d', strtotime("-{$i} days"));
        $labels[] = date('d M', strtotime($dateKey));
        $values[] = (int) ($countsByDate[$dateKey] ?? 0);
    }

    $todayDate = date('Y-m-d');
    $weekStartDate = date('Y-m-d', strtotime('-6 days'));
    $totalToday = (int) ($countsByDate[$todayDate] ?? 0);
    $totalWeek = 0;
    foreach ($countsByDate as $dateKey => $countDay) {
        if ($dateKey >= $weekStartDate && $dateKey <= $todayDate) {
            $totalWeek += (int) $countDay;
        }
    }

    $result = [
        'labels' => $labels,
        'values' => $values,
        'total_today' => $totalToday,
        'total_week' => $totalWeek,
    ];

    @file_put_contents(
        $cacheFile,
        json_encode($result, JSON_UNESCAPED_UNICODE),
        LOCK_EX
    );

    return $result;
}

function org_dokumen_count_library(?mysqli $db): int
{
    if (!$db instanceof mysqli || !org_dokumen_table_exists($db)) {
        return 0;
    }
    $res = $db->query('SELECT COUNT(*) AS c FROM `dokumen`');
    if ($res === false) {
        return 0;
    }
    $row = $res->fetch_assoc();
    $res->free();

    return (int) ($row['c'] ?? 0);
}

/**
 * @return mixed|null
 */
function org_beranda_cache_read_json(string $filename, int $ttlSeconds)
{
    $path = org_beranda_cache_dir() . DIRECTORY_SEPARATOR . $filename;
    if (!is_file($path)) {
        return null;
    }
    $age = time() - (int) filemtime($path);
    if ($age < 0 || $age >= $ttlSeconds) {
        return null;
    }
    $raw = file_get_contents($path);
    if ($raw === false || $raw === '') {
        return null;
    }
    $decoded = json_decode($raw, true);

    return is_array($decoded) ? $decoded : null;
}

function org_beranda_cache_write_json(string $filename, array $payload): void
{
    @file_put_contents(
        org_beranda_cache_dir() . DIRECTORY_SEPARATOR . $filename,
        json_encode($payload, JSON_UNESCAPED_UNICODE),
        LOCK_EX
    );
}

function org_beranda_ensure_table_once(mysqli $db, string $cacheKey, callable $ensureFn): void
{
    $flag = org_beranda_cache_dir() . DIRECTORY_SEPARATOR . 'schema_' . preg_replace('/[^a-z0-9_]/', '', strtolower($cacheKey)) . '.ok';
    if (is_file($flag) && (time() - (int) filemtime($flag)) < 86400) {
        return;
    }
    $ensureFn();
    @touch($flag);
}

function org_beranda_dokumen_count_cached(?mysqli $db): int
{
    $cached = org_beranda_cache_read_json('beranda_doc_count.json', 300);
    if (is_array($cached) && array_key_exists('count', $cached)) {
        return (int) $cached['count'];
    }
    $count = org_dokumen_count_library($db);
    org_beranda_cache_write_json('beranda_doc_count.json', ['count' => $count]);

    return $count;
}

/**
 * @return array{widgets: list<array<string, mixed>>, details: array<string, array<string, mixed>>}
 */
function org_beranda_fetch_dashboard_bundle(?mysqli $db): array
{
    $empty = ['widgets' => [], 'details' => []];
    $cached = org_beranda_cache_read_json('beranda_dashboard_bundle.json', 120);
    if (is_array($cached) && isset($cached['widgets'], $cached['details'])) {
        return [
            'widgets' => array_values($cached['widgets']),
            'details' => is_array($cached['details']) ? $cached['details'] : [],
        ];
    }
    if (!$db instanceof mysqli) {
        return $empty;
    }
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'dashboard_widgets_db.php';
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'widget_details_db.php';
    org_beranda_ensure_table_once($db, 'dashboard_widgets', static function () use ($db): void {
        org_dashboard_widgets_ensure_table($db);
    });
    $widgets = org_dashboard_widgets_fetch_all($db, true);
    $widgetIds = [];
    foreach ($widgets as $bw) {
        $wid = (int) ($bw['id'] ?? 0);
        if ($wid > 0) {
            $widgetIds[] = $wid;
        }
    }
    $details = $widgetIds !== []
        ? org_widget_details_fetch_grouped_map($db, $widgetIds)
        : [];
    $bundle = ['widgets' => $widgets, 'details' => $details];
    org_beranda_cache_write_json('beranda_dashboard_bundle.json', $bundle);

    return $bundle;
}

/**
 * Galeri beranda — DB saja (tanpa scandir folder uploads; itu lambat di first load).
 *
 * @return list<array<string, mixed>>
 */
function org_beranda_fetch_galeri_cached(?mysqli $db, int $limit = 6): array
{
    $limit = max(1, min(12, $limit));
    $cached = org_beranda_cache_read_json('beranda_galeri_public.json', 180);
    if (is_array($cached) && isset($cached['items']) && is_array($cached['items'])) {
        return array_slice(array_values($cached['items']), 0, $limit);
    }
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'galeri_kegiatan_db.php';
    $rows = [];
    if ($db instanceof mysqli && org_galeri_kegiatan_table_exists($db)) {
        $rows = org_galeri_kegiatan_fetch_all($db);
    }
    $items = org_galeri_kegiatan_filter_displayable($rows);
    org_beranda_cache_write_json('beranda_galeri_public.json', ['items' => $items]);

    return array_slice($items, 0, $limit);
}

/**
 * @param array<string, mixed> $defaults
 * @return array<string, mixed>
 */
/**
 * @return list<array<string, mixed>>
 */
function org_beranda_fetch_pusat_informasi_cached(?mysqli $db, int $maxFeatured = 4, int $maxTotal = 12): array
{
    $cached = org_beranda_cache_read_json('beranda_pusat_informasi.json', 300);
    if (is_array($cached) && isset($cached['items']) && is_array($cached['items'])) {
        return array_values($cached['items']);
    }
    if (!$db instanceof mysqli) {
        return [];
    }
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'pusat_informasi_db.php';
    $items = org_pusat_informasi_fetch_for_beranda($db, $maxFeatured, $maxTotal);
    org_beranda_cache_write_json('beranda_pusat_informasi.json', ['items' => $items]);

    return $items;
}

function org_beranda_merge_site_settings(array $defaults, ?mysqli $db): array
{
    $settings = $defaults;
    $cached = org_beranda_cache_read_json('beranda_site_content.json', 300);
    if (is_array($cached)) {
        return array_merge($settings, $cached);
    }
    if (!$db instanceof mysqli) {
        return $settings;
    }
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'site_content_db.php';
    if (!org_site_content_table_exists($db)) {
        return $settings;
    }
    $rowSite = org_site_content_fetch($db);
    if ($rowSite !== null) {
        org_beranda_cache_write_json('beranda_site_content.json', $rowSite);

        return array_merge($settings, $rowSite);
    }

    return $settings;
}

/**
 * @return array{
 *   tahun: int,
 *   years: list<int>,
 *   grouped: array<string, list<array<string, mixed>>>,
 *   visible: bool
 * }
 */
function org_beranda_fetch_team_targets_bundle(?mysqli $db, int $requestedYear): array
{
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'team_targets_db.php';
    $emptyGrouped = org_team_targets_empty_grouped();
    $result = [
        'tahun' => org_team_targets_normalize_tahun($requestedYear),
        'years' => [],
        'grouped' => $emptyGrouped,
        'visible' => false,
    ];
    if (!$db instanceof mysqli) {
        return $result;
    }
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'team_targets_db.php';
    $cacheName = 'beranda_team_targets_' . $result['tahun'] . '.json';
    $cached = org_beranda_cache_read_json($cacheName, 120);
    if (is_array($cached) && isset($cached['grouped'], $cached['years'], $cached['visible'])) {
        $result['tahun'] = (int) ($cached['tahun'] ?? $result['tahun']);
        $result['years'] = array_values(array_map('intval', (array) $cached['years']));
        $result['grouped'] = is_array($cached['grouped']) ? $cached['grouped'] : $emptyGrouped;
        $result['visible'] = !empty($cached['visible']);

        return $result;
    }
    org_beranda_ensure_table_once($db, 'team_targets', static function () use ($db): void {
        org_team_targets_ensure_table($db);
    });
    $showYear = org_team_targets_resolve_beranda_year($db, $result['tahun']);
    if ($showYear <= 0) {
        return $result;
    }
    $result['tahun'] = $showYear;
    $result['grouped'] = org_team_targets_fetch_grouped_by_year($db, $showYear);
    $result['years'] = org_team_targets_fetch_beranda_years($db);
    if ($result['years'] === []) {
        $result['years'] = [$showYear];
    }
    if (!in_array($showYear, $result['years'], true)) {
        array_unshift($result['years'], $showYear);
    }
    $result['visible'] = true;
    org_beranda_cache_write_json($cacheName, [
        'tahun' => $result['tahun'],
        'years' => $result['years'],
        'grouped' => $result['grouped'],
        'visible' => true,
    ]);

    return $result;
}
