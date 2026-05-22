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
    $ttl = 120;
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
