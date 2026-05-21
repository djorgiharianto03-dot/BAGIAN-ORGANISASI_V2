<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'tugas_db.php';

/**
 * Metrik panel smart E-Organisasi dari database (bukan simulasi JS).
 *
 * @return array{
 *   db_ok: bool,
 *   service_online: bool,
 *   server_label: string,
 *   active_users_label: string,
 *   tamu_today: int,
 *   progress_pct: int,
 *   progress_hint: string,
 *   badge_arsip: int,
 *   badge_disposisi: int,
 *   badge_tugas: int,
 *   completed_units: int,
 *   pending_units: int
 * }
 */
function org_eorg_hub_collect_metrics(): array
{
    $defaults = [
        'db_ok' => false,
        'service_online' => false,
        'server_label' => 'Tidak tersedia',
        'active_users_label' => 'Belum ada aktivitas admin hari ini',
        'tamu_today' => 0,
        'progress_pct' => 0,
        'progress_hint' => 'Belum ada aktivitas layanan hari ini',
        'badge_arsip' => 0,
        'badge_disposisi' => 0,
        'badge_tugas' => 0,
        'completed_units' => 0,
        'pending_units' => 0,
    ];

    $db = org_db();
    if (!($db instanceof mysqli)) {
        return $defaults;
    }

    $defaults['db_ok'] = true;
    $defaults['service_online'] = true;
    $defaults['server_label'] = 'Stabil';

    $today = date('Y-m-d');
    $todayStart = $today . ' 00:00:00';

    $tamuToday = org_eorg_hub_count_tamu_today($db, $today, $todayStart);
    $defaults['tamu_today'] = $tamuToday;

    $adminActive = org_eorg_hub_count_admin_active_today($db, $today);
    if ($adminActive > 0) {
        $defaults['active_users_label'] = $adminActive === 1
            ? '1 admin aktif hari ini'
            : $adminActive . ' admin aktif hari ini';
    }

    $badgeArsip = org_eorg_hub_count_arsip_belum_disposisi($db);
    $badgeDispo = org_eorg_hub_count_disposisi_menunggu($db);
    $badgeTugas = org_tugas_count_pending_for_kabag($db);
    $defaults['badge_arsip'] = $badgeArsip;
    $defaults['badge_disposisi'] = $badgeDispo;
    $defaults['badge_tugas'] = $badgeTugas;

    $dispoSelesaiToday = org_eorg_hub_count_disposisi_selesai_today($db, $today);
    $completed = $tamuToday + $dispoSelesaiToday;
    $pending = $badgeArsip + $badgeDispo + $badgeTugas;
    $defaults['completed_units'] = $completed;
    $defaults['pending_units'] = $pending;

    $denom = $completed + $pending;
    if ($denom > 0) {
        $defaults['progress_pct'] = (int) round(($completed / $denom) * 100);
        $defaults['progress_hint'] = $completed . ' selesai · ' . $pending . ' menunggu tindak lanjut';
    } elseif ($tamuToday > 0 || $adminActive > 0) {
        $defaults['progress_pct'] = 100;
        $defaults['progress_hint'] = 'Aktivitas tercatat hari ini';
    }

    return $defaults;
}

function org_eorg_hub_table_exists(mysqli $db, string $table): bool
{
    $table = preg_replace('/[^a-z0-9_]/i', '', $table);
    if ($table === '') {
        return false;
    }
    $r = $db->query("SHOW TABLES LIKE '{$table}'");
    if ($r === false) {
        return false;
    }
    $ok = $r->num_rows > 0;
    $r->free();

    return $ok;
}

function org_eorg_hub_tamu_date_column(mysqli $db): string
{
    if (!org_eorg_hub_table_exists($db, 'tamu')) {
        return '';
    }
    $res = $db->query('SHOW COLUMNS FROM `tamu`');
    if ($res === false) {
        return '';
    }
    $cols = [];
    while ($row = $res->fetch_assoc()) {
        $field = (string) ($row['Field'] ?? '');
        if ($field !== '') {
            $cols[$field] = true;
        }
    }
    $res->free();
    if (isset($cols['created_at'])) {
        return 'created_at';
    }
    if (isset($cols['tanggal'])) {
        return 'tanggal';
    }
    if (isset($cols['tanggal_kunjungan'])) {
        return 'tanggal_kunjungan';
    }

    return '';
}

function org_eorg_hub_count_tamu_today(mysqli $db, string $today, string $todayStart): int
{
    $dateField = org_eorg_hub_tamu_date_column($db);
    if ($dateField === '') {
        return 0;
    }
    $df = preg_replace('/[^a-z0-9_]/i', '', $dateField);
    if ($df === '') {
        return 0;
    }
    $stmt = $db->prepare("SELECT COUNT(*) AS c FROM `tamu` WHERE DATE(`{$df}`) = ?");
    if ($stmt === false) {
        return 0;
    }
    $stmt->bind_param('s', $today);
    $count = 0;
    if ($stmt->execute()) {
        $res = $stmt->get_result();
        if ($res !== false) {
            $row = $res->fetch_assoc();
            $count = (int) ($row['c'] ?? 0);
        }
    }
    $stmt->close();

    return $count;
}

function org_eorg_hub_count_admin_active_today(mysqli $db, string $today): int
{
    if (!org_eorg_hub_table_exists($db, 'audit_logs')) {
        return 0;
    }
    $stmt = $db->prepare('SELECT COUNT(DISTINCT `id_admin`) AS c FROM `audit_logs` WHERE DATE(`waktu`) = ?');
    if ($stmt === false) {
        return 0;
    }
    $stmt->bind_param('s', $today);
    $count = 0;
    if ($stmt->execute()) {
        $res = $stmt->get_result();
        if ($res !== false) {
            $row = $res->fetch_assoc();
            $count = (int) ($row['c'] ?? 0);
        }
    }
    $stmt->close();

    return $count;
}

function org_eorg_hub_arsip_jenis_column(mysqli $db): string
{
    if (!org_eorg_hub_table_exists($db, 'arsip_surat')) {
        return '';
    }
    $res = $db->query('SHOW COLUMNS FROM `arsip_surat`');
    if ($res === false) {
        return '';
    }
    $cols = [];
    while ($c = $res->fetch_assoc()) {
        $f = strtolower(trim((string) ($c['Field'] ?? '')));
        if ($f !== '') {
            $cols[$f] = true;
        }
    }
    $res->free();
    foreach (['jenis_surat', 'jenis', 'tipe'] as $cand) {
        if (isset($cols[$cand])) {
            return $cand;
        }
    }

    return '';
}

function org_eorg_hub_count_arsip_belum_disposisi(mysqli $db): int
{
    if (!org_eorg_hub_table_exists($db, 'arsip_surat') || !org_eorg_hub_table_exists($db, 'surat_disposisi')) {
        return 0;
    }
    $jenisCol = org_eorg_hub_arsip_jenis_column($db);
    if ($jenisCol === '') {
        return 0;
    }
    $jSafe = preg_replace('/[^a-z0-9_]/i', '', $jenisCol);
    if ($jSafe === '') {
        return 0;
    }
    $sql = "SELECT COUNT(*) AS c FROM `arsip_surat` a
            WHERE LOWER(TRIM(COALESCE(a.`{$jSafe}`, ''))) = 'masuk'
              AND a.`id` NOT IN (
                  SELECT DISTINCT `id_arsip` FROM `surat_disposisi`
                  WHERE `id_arsip` IS NOT NULL AND `id_arsip` > 0
              )";
    $res = $db->query($sql);
    if ($res === false) {
        return 0;
    }
    $row = $res->fetch_assoc();
    $res->free();

    return (int) ($row['c'] ?? 0);
}

function org_eorg_hub_surat_disposisi_has_kabag_column(mysqli $db): bool
{
    $res = $db->query("SHOW COLUMNS FROM `surat_disposisi` LIKE 'kabag_tandai_selesai'");
    if ($res === false) {
        return false;
    }
    $ok = $res->num_rows > 0;
    $res->free();

    return $ok;
}

function org_eorg_hub_count_disposisi_menunggu(mysqli $db): int
{
    if (!org_eorg_hub_table_exists($db, 'surat_disposisi')) {
        return 0;
    }
    $hasKabag = org_eorg_hub_surat_disposisi_has_kabag_column($db);
    if ($hasKabag) {
        $sql = "SELECT COUNT(*) AS c FROM `surat_disposisi`
                WHERE LOWER(TRIM(COALESCE(`status`, ''))) IN ('pending', 'diterima', 'dikerjakan', 'revisi')
                   OR (LOWER(TRIM(COALESCE(`status`, ''))) IN ('selesai', 'fix')
                       AND COALESCE(`kabag_tandai_selesai`, 0) = 0)";
    } else {
        $sql = "SELECT COUNT(*) AS c FROM `surat_disposisi`
                WHERE LOWER(TRIM(COALESCE(`status`, ''))) IN ('pending', 'diterima', 'dikerjakan', 'revisi')";
    }
    $res = $db->query($sql);
    if ($res === false) {
        return 0;
    }
    $row = $res->fetch_assoc();
    $res->free();

    return (int) ($row['c'] ?? 0);
}

function org_eorg_hub_disposisi_date_column(mysqli $db): string
{
    $res = $db->query('SHOW COLUMNS FROM `surat_disposisi`');
    if ($res === false) {
        return '';
    }
    $cols = [];
    while ($c = $res->fetch_assoc()) {
        $f = strtolower(trim((string) ($c['Field'] ?? '')));
        if ($f !== '') {
            $cols[$f] = true;
        }
    }
    $res->free();
    foreach (['updated_at', 'created_at', 'tanggal'] as $cand) {
        if (isset($cols[$cand])) {
            return $cand;
        }
    }

    return '';
}

function org_eorg_hub_count_disposisi_selesai_today(mysqli $db, string $today): int
{
    if (!org_eorg_hub_table_exists($db, 'surat_disposisi')) {
        return 0;
    }
    $dateCol = org_eorg_hub_disposisi_date_column($db);
    if ($dateCol === '') {
        return 0;
    }
    $dc = preg_replace('/[^a-z0-9_]/i', '', $dateCol);
    if ($dc === '') {
        return 0;
    }
    $hasKabag = org_eorg_hub_surat_disposisi_has_kabag_column($db);
    if ($hasKabag) {
        $sql = "SELECT COUNT(*) AS c FROM `surat_disposisi`
                WHERE DATE(`{$dc}`) = ?
                  AND LOWER(TRIM(COALESCE(`status`, ''))) IN ('selesai', 'fix')
                  AND COALESCE(`kabag_tandai_selesai`, 0) = 1";
    } else {
        $sql = "SELECT COUNT(*) AS c FROM `surat_disposisi`
                WHERE DATE(`{$dc}`) = ?
                  AND LOWER(TRIM(COALESCE(`status`, ''))) IN ('selesai', 'fix')";
    }
    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        return 0;
    }
    $stmt->bind_param('s', $today);
    $count = 0;
    if ($stmt->execute()) {
        $res = $stmt->get_result();
        if ($res !== false) {
            $row = $res->fetch_assoc();
            $count = (int) ($row['c'] ?? 0);
        }
    }
    $stmt->close();

    return $count;
}

/**
 * Terapkan badge notifikasi pada kartu layanan dari metrik DB.
 *
 * @param list<array<string, mixed>> $services
 * @return list<array<string, mixed>>
 */
function org_eorg_hub_apply_service_badges(array $services, array $metrics): array
{
    foreach ($services as $i => $svc) {
        $theme = (string) ($svc['theme'] ?? '');
        if ($theme === 'arsip') {
            $n = (int) ($metrics['badge_arsip'] ?? 0);
            $services[$i]['badge'] = $n > 0 ? ['count' => $n, 'tone' => 'danger'] : null;
        } elseif ($theme === 'disposisi') {
            $n = (int) ($metrics['badge_disposisi'] ?? 0);
            $services[$i]['badge'] = $n > 0 ? ['count' => $n, 'tone' => 'info'] : null;
        } elseif ($theme === 'tugas') {
            $n = (int) ($metrics['badge_tugas'] ?? 0);
            $services[$i]['badge'] = $n > 0 ? ['count' => $n, 'tone' => 'warning'] : null;
        }
    }

    return $services;
}

/**
 * Data seri 7 hari untuk mini chart dashboard.
 *
 * @return array{labels: list<string>, tamu: list<int>, disposisi: list<int>}
 */
function org_eorg_hub_weekly_series(?mysqli $db = null): array
{
    $labels = [];
    $tamu = [];
    $dispo = [];
    for ($i = 6; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime('-' . $i . ' days'));
        $labels[] = date('d/m', strtotime($d));
        $tamu[] = 0;
        $dispo[] = 0;
    }
    if (!($db instanceof mysqli)) {
        return ['labels' => $labels, 'tamu' => $tamu, 'disposisi' => $dispo];
    }

    $dateField = org_eorg_hub_tamu_date_column($db);
    if ($dateField !== '') {
        $df = preg_replace('/[^a-z0-9_]/i', '', $dateField);
        $start = date('Y-m-d', strtotime('-6 days'));
        $end = date('Y-m-d');
        $stmt = $db->prepare(
            "SELECT DATE(`{$df}`) AS d, COUNT(*) AS c FROM `tamu`
             WHERE DATE(`{$df}`) BETWEEN ? AND ? GROUP BY DATE(`{$df}`)"
        );
        if ($stmt !== false) {
            $stmt->bind_param('ss', $start, $end);
            if ($stmt->execute()) {
                $res = $stmt->get_result();
                $map = [];
                if ($res !== false) {
                    while ($row = $res->fetch_assoc()) {
                        $map[(string) ($row['d'] ?? '')] = (int) ($row['c'] ?? 0);
                    }
                }
                for ($i = 6; $i >= 0; $i--) {
                    $d = date('Y-m-d', strtotime('-' . $i . ' days'));
                    $tamu[6 - $i] = $map[$d] ?? 0;
                }
            }
            $stmt->close();
        }
    }

    if (org_eorg_hub_table_exists($db, 'surat_disposisi')) {
        $dc = org_eorg_hub_disposisi_date_column($db);
        if ($dc !== '') {
            $dcSafe = preg_replace('/[^a-z0-9_]/i', '', $dc);
            $start = date('Y-m-d', strtotime('-6 days'));
            $end = date('Y-m-d');
            $stmt = $db->prepare(
                "SELECT DATE(`{$dcSafe}`) AS d, COUNT(*) AS c FROM `surat_disposisi`
                 WHERE DATE(`{$dcSafe}`) BETWEEN ? AND ? GROUP BY DATE(`{$dcSafe}`)"
            );
            if ($stmt !== false) {
                $stmt->bind_param('ss', $start, $end);
                if ($stmt->execute()) {
                    $res = $stmt->get_result();
                    $map = [];
                    if ($res !== false) {
                        while ($row = $res->fetch_assoc()) {
                            $map[(string) ($row['d'] ?? '')] = (int) ($row['c'] ?? 0);
                        }
                    }
                    for ($i = 6; $i >= 0; $i--) {
                        $d = date('Y-m-d', strtotime('-' . $i . ' days'));
                        $dispo[6 - $i] = $map[$d] ?? 0;
                    }
                }
                $stmt->close();
            }
        }
    }

    return ['labels' => $labels, 'tamu' => $tamu, 'disposisi' => $dispo];
}

/**
 * @return list<array<string, string>>
 */
function org_eorg_hub_activity_feed(int $limit = 8): array
{
    $db = org_db();
    if (!($db instanceof mysqli)) {
        return [];
    }
    if (!function_exists('org_audit_logs_fetch_visible_rows')) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'site_content_db.php';
    }

    return org_audit_logs_fetch_visible_rows($db, max(1, min(20, $limit)));
}
