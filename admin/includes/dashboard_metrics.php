<?php

/**
 * Metrik ringkas untuk overview dashboard admin (data nyata dari DB).
 *
 * @return array<string, mixed>
 */
function admin_dashboard_collect_metrics(
    ?mysqli $db,
    array $dashDocRanked,
    array $pusatInformasiList,
    array $galleryUrls,
    array $staffUserRows,
    array $auditRows,
    int $dashDocFileCount
): array {
    $out = [
        'dokumen_total' => $dashDocFileCount,
        'unduhan_total' => 0,
        'berita_total' => 0,
        'pengumuman_total' => 0,
        'galeri_total' => count($galleryUrls),
        'staf_total' => count($staffUserRows),
        'saran_total' => 0,
        'tamu_hari_ini' => 0,
        'tamu_minggu' => 0,
        'tamu_labels' => [],
        'tamu_values' => [],
        'download_labels' => [],
        'download_values' => [],
        'recent_activity' => [],
        'org_score' => 0,
        'kpi_pelayanan' => 0,
        'team_total' => 0,
        'team_selesai' => 0,
        'team_progress_pct' => 0,
        'team_tim_labels' => ['Kelembagaan', 'RB', 'Yanlik'],
        'team_tim_pct' => [0, 0, 0],
        'target_labels' => [],
        'target_values' => [],
        'realisasi_values' => [],
        'heatmap_series' => [],
    ];

    foreach ($dashDocRanked as $dr) {
        $out['unduhan_total'] += (int) ($dr['jumlah_unduh'] ?? 0);
    }

    foreach ($pusatInformasiList as $pi) {
        $kat = strtolower(trim((string) ($pi['kategori'] ?? 'berita')));
        if ($kat === 'pengumuman') {
            $out['pengumuman_total']++;
        } else {
            $out['berita_total']++;
        }
    }

    $top = array_slice($dashDocRanked, 0, 6);
    foreach ($top as $dr) {
        $base = (string) ($dr['nama_file'] ?? '');
        $baseDisp = preg_replace('/^\d{8}_\d{6}_/i', '', $base);
        $label = str_replace('_', ' ', pathinfo($baseDisp, PATHINFO_FILENAME));
        if ($label === '') {
            $label = $base;
        }
        if (function_exists('mb_strlen') && mb_strlen($label, 'UTF-8') > 28) {
            $label = mb_substr($label, 0, 26, 'UTF-8') . '…';
        } elseif (strlen($label) > 28) {
            $label = substr($label, 0, 26) . '…';
        }
        $out['download_labels'][] = $label;
        $out['download_values'][] = (int) ($dr['jumlah_unduh'] ?? 0);
    }

    $recentAudit = array_slice($auditRows, 0, 8);
    usort($recentAudit, static function (array $a, array $b): int {
        $ta = strtotime((string) ($a['waktu'] ?? '')) ?: 0;
        $tb = strtotime((string) ($b['waktu'] ?? '')) ?: 0;
        return $ta <=> $tb;
    });
    foreach ($recentAudit as $ar) {
        $waktu = (string) ($ar['waktu'] ?? '');
        $ts = $waktu !== '' ? strtotime($waktu) : false;
        $out['recent_activity'][] = [
            'admin' => (string) ($ar['nama_admin'] ?? $ar['id_admin'] ?? 'Admin'),
            'aksi' => (string) ($ar['aksi'] ?? ''),
            'waktu' => $waktu,
            'waktu_rel' => $ts !== false ? admin_dashboard_time_ago($ts) : '—',
        ];
    }

    if (!($db instanceof mysqli)) {
        for ($i = 13; $i >= 0; $i--) {
            $out['tamu_labels'][] = date('d M', strtotime("-{$i} days"));
            $out['tamu_values'][] = 0;
        }
        return $out;
    }

    if (org_saran_kritik_table_exists($db)) {
        $rS = $db->query('SELECT COUNT(*) AS c FROM `saran_kritik`');
        if ($rS !== false) {
            $rowS = $rS->fetch_assoc();
            $out['saran_total'] = (int) ($rowS['c'] ?? 0);
            $rS->free();
        }
    }

    $tableTamu = $db->query("SHOW TABLES LIKE 'tamu'");
    if ($tableTamu !== false && $tableTamu->num_rows > 0) {
        $tableTamu->free();
        $tamuCols = [];
        $colRes = $db->query('SHOW COLUMNS FROM `tamu`');
        if ($colRes !== false) {
            while ($col = $colRes->fetch_assoc()) {
                $f = (string) ($col['Field'] ?? '');
                if ($f !== '') {
                    $tamuCols[$f] = true;
                }
            }
            $colRes->free();
        }
        $dateField = isset($tamuCols['created_at']) ? 'created_at' : (isset($tamuCols['tanggal']) ? 'tanggal' : (isset($tamuCols['tanggal_kunjungan']) ? 'tanggal_kunjungan' : ''));
        if ($dateField !== '') {
            $df = preg_replace('/[^a-z0-9_]/i', '', $dateField);
            $startDate = date('Y-m-d', strtotime('-13 days'));
            $endDate = date('Y-m-d');
            $countsByDate = [];
            $stmtTrend = $db->prepare(
                "SELECT DATE(`{$df}`) AS d, COUNT(*) AS c FROM `tamu` WHERE DATE(`{$df}`) BETWEEN ? AND ? GROUP BY DATE(`{$df}`)"
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
                    }
                }
                $stmtTrend->close();
            }
            $todayDate = date('Y-m-d');
            $weekStart = date('Y-m-d', strtotime('-6 days'));
            for ($i = 13; $i >= 0; $i--) {
                $dateKey = date('Y-m-d', strtotime("-{$i} days"));
                $out['tamu_labels'][] = date('d M', strtotime($dateKey));
                $out['tamu_values'][] = (int) ($countsByDate[$dateKey] ?? 0);
            }
            $out['tamu_hari_ini'] = (int) ($countsByDate[$todayDate] ?? 0);
            foreach ($countsByDate as $dateKey => $cnt) {
                if ($dateKey >= $weekStart && $dateKey <= $todayDate) {
                    $out['tamu_minggu'] += (int) $cnt;
                }
            }
        }
    }

    if (count($out['tamu_labels']) === 0) {
        for ($i = 13; $i >= 0; $i--) {
            $out['tamu_labels'][] = date('d M', strtotime("-{$i} days"));
            $out['tamu_values'][] = 0;
        }
    }

    $docTotal = max(1, (int) $out['dokumen_total']);
    $unduh = (int) $out['unduhan_total'];
    $tamuWeek = max(1, (int) $out['tamu_minggu']);
    $infoTotal = (int) $out['berita_total'] + (int) $out['pengumuman_total'];
    $out['kpi_pelayanan'] = (int) min(100, round(($unduh / $docTotal) * 35 + min(100, $tamuWeek * 8) * 0.35 + min(100, $infoTotal * 5) * 0.3));
    $out['org_score'] = (int) min(100, round(
        min(100, (int) $out['staf_total'] * 12) * 0.2
        + min(100, $infoTotal * 8) * 0.25
        + min(100, (int) $out['galeri_total'] * 15) * 0.15
        + $out['kpi_pelayanan'] * 0.4
    ));

    if ($db instanceof mysqli) {
        $ttPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'team_targets_db.php';
        if (is_file($ttPath)) {
            require_once $ttPath;
            if (function_exists('org_team_targets_table_exists') && org_team_targets_table_exists($db)) {
                $tahun = (int) date('Y');
                $grouped = org_team_targets_fetch_grouped_by_year($db, $tahun);
                $timMap = [
                    'kelembagaan' => 'Kelembagaan',
                    'rb' => 'RB',
                    'yanlik' => 'Yanlik',
                ];
                $out['team_tim_labels'] = [];
                $out['team_tim_pct'] = [];
                foreach ($timMap as $timKey => $timLabel) {
                    $rows = $grouped[$timKey] ?? [];
                    $totalTim = count($rows);
                    $doneTim = 0;
                    foreach ($rows as $tr) {
                        $st = strtolower(trim((string) ($tr['status'] ?? '')));
                        if ($st === 'selesai' || $st === 'completed' || $st === 'done') {
                            $doneTim++;
                        }
                    }
                    $out['team_total'] += $totalTim;
                    $out['team_selesai'] += $doneTim;
                    $out['team_tim_labels'][] = $timLabel;
                    $out['team_tim_pct'][] = $totalTim > 0 ? (int) round(($doneTim / $totalTim) * 100) : 0;
                }
                if ($out['team_total'] > 0) {
                    $out['team_progress_pct'] = (int) round(($out['team_selesai'] / $out['team_total']) * 100);
                }
                $out['target_labels'] = $out['team_tim_labels'];
                $out['target_values'] = array_map(static fn (int $p): int => 100, $out['team_tim_pct']);
                $out['realisasi_values'] = $out['team_tim_pct'];
            }
        }

        $chkAudit = $db->query("SHOW TABLES LIKE 'audit_logs'");
        if ($chkAudit !== false && $chkAudit->num_rows > 0) {
            $chkAudit->free();
            $dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
            $matrix = [];
            foreach ($dayNames as $dn) {
                $matrix[$dn] = array_fill(0, 8, 0);
            }
            $resH = $db->query(
                "SELECT DAYOFWEEK(`waktu`) AS dow, HOUR(`waktu`) AS hr, COUNT(*) AS c
                 FROM `audit_logs`
                 WHERE `waktu` >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                 GROUP BY DAYOFWEEK(`waktu`), HOUR(`waktu`)"
            );
            if ($resH !== false) {
                while ($hr = $resH->fetch_assoc()) {
                    $dow = (int) ($hr['dow'] ?? 1);
                    $hour = (int) ($hr['hr'] ?? 0);
                    $cnt = (int) ($hr['c'] ?? 0);
                    $dayLabel = $dayNames[$dow - 1] ?? 'Sen';
                    $slot = (int) min(7, max(0, floor($hour / 3)));
                    $matrix[$dayLabel][$slot] += $cnt;
                }
                $resH->free();
            }
            foreach ($matrix as $dayLabel => $slots) {
                $out['heatmap_series'][] = [
                    'name' => $dayLabel,
                    'data' => array_map(static fn (int $v): int => $v, $slots),
                ];
            }
        }
    }

    return $out;
}

function admin_dashboard_time_ago(int $timestamp): string
{
    $diff = time() - $timestamp;
    if ($diff < 60) {
        return 'Baru saja';
    }
    if ($diff < 3600) {
        return (int) floor($diff / 60) . ' menit lalu';
    }
    if ($diff < 86400) {
        return (int) floor($diff / 3600) . ' jam lalu';
    }
    if ($diff < 604800) {
        return (int) floor($diff / 86400) . ' hari lalu';
    }

    return date('d M Y, H:i', $timestamp);
}
