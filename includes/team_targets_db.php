<?php
declare(strict_types=1);

if (!defined('ORG_ROOT')) {
    define('ORG_ROOT', dirname(__DIR__));
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_database.php';

/** Pesan error operasi terakhir (untuk ditampilkan di admin). */
function org_team_targets_last_error(): string
{
    return (string) ($GLOBALS['org_team_targets_last_error'] ?? '');
}

function org_team_targets_last_error_clear(): void
{
    $GLOBALS['org_team_targets_last_error'] = '';
}

function org_team_targets_last_error_set(string $message): void
{
    $GLOBALS['org_team_targets_last_error'] = trim($message);
}

function org_team_targets_column_exists(mysqli $db, string $column): bool
{
    $column = preg_replace('/[^a-z0-9_]/i', '', $column);
    if ($column === '' || !org_team_targets_table_exists($db)) {
        return false;
    }
    $r = $db->query("SHOW COLUMNS FROM `team_targets` LIKE '{$column}'");

    return $r !== false && $r->num_rows > 0;
}

/** Skema lama: tim_kerja_id, nama_kegiatan, status_target (tabel sudah ada sebelum modul ini). */
function org_team_targets_uses_legacy_schema(mysqli $db): bool
{
    return org_team_targets_column_exists($db, 'nama_kegiatan')
        && !org_team_targets_column_exists($db, 'kegiatan');
}

function org_team_targets_tim_to_legacy_id(string $tim): int
{
    return match (org_team_targets_normalize_tim($tim)) {
        'rb' => 2,
        'yanlik' => 3,
        default => 1,
    };
}

function org_team_targets_legacy_id_to_tim(int $id): string
{
    return match ($id) {
        2 => 'rb',
        3 => 'yanlik',
        default => 'kelembagaan',
    };
}

/** Selaraskan tabel lama dengan skema terbaru. */
function org_team_targets_migrate_schema(mysqli $db): void
{
    if (!org_team_targets_table_exists($db)) {
        return;
    }
    if (!org_team_targets_column_exists($db, 'urutan')) {
        $db->query(
            'ALTER TABLE `team_targets`
             ADD COLUMN `urutan` INT UNSIGNED NOT NULL DEFAULT 0'
        );
    }
    if (!org_team_targets_column_exists($db, 'created_at')) {
        $db->query(
            'ALTER TABLE `team_targets`
             ADD COLUMN `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP'
        );
    }
    if (!org_team_targets_column_exists($db, 'updated_at')) {
        $db->query(
            'ALTER TABLE `team_targets`
             ADD COLUMN `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP'
        );
    }
    if (org_team_targets_uses_legacy_schema($db)) {
        return;
    }
    if (org_team_targets_column_exists($db, 'tim_kerja')) {
        $db->query(
            'ALTER TABLE `team_targets`
             MODIFY COLUMN `tim_kerja` ENUM(\'kelembagaan\', \'rb\', \'yanlik\') NOT NULL,
             MODIFY COLUMN `kegiatan` VARCHAR(255) NOT NULL DEFAULT \'\',
             MODIFY COLUMN `status` ENUM(\'direncanakan\', \'berjalan\', \'selesai\') NOT NULL DEFAULT \'direncanakan\',
             MODIFY COLUMN `tahun` SMALLINT UNSIGNED NOT NULL'
        );
    }
}

/** @return list<string> */
function org_team_targets_tim_list(): array
{
    return ['kelembagaan', 'rb', 'yanlik'];
}

/** @return list<string> */
function org_team_targets_status_list(): array
{
    return ['direncanakan', 'berjalan', 'selesai'];
}

function org_team_targets_normalize_tim(string $tim): string
{
    $tim = strtolower(trim(str_replace([' ', '-'], '_', $tim)));
    if ($tim === 'kelembagaan_anjab' || $tim === 'anjab') {
        return 'kelembagaan';
    }
    if ($tim === 'sakip' || $tim === 'sakip_rb' || $tim === 'kinerja') {
        return 'rb';
    }
    if ($tim === 'pelayanan_publik' || $tim === 'tata_laksana') {
        return 'yanlik';
    }

    return in_array($tim, org_team_targets_tim_list(), true) ? $tim : 'kelembagaan';
}

function org_team_targets_normalize_status(string $status): string
{
    $status = strtolower(trim(str_replace([' ', '-'], '_', $status)));
    if ($status === 'planned' || $status === 'rencana') {
        return 'direncanakan';
    }
    if ($status === 'ongoing' || $status === 'proses' || $status === 'berjalan') {
        return 'berjalan';
    }
    if ($status === 'done' || $status === 'complete' || $status === 'completed' || $status === 'selesai') {
        return 'selesai';
    }

    return in_array($status, org_team_targets_status_list(), true) ? $status : 'direncanakan';
}

/**
 * Gabungkan baris POST yang terpisah indeks ([][] tanpa nomor sering memecah kegiatan & status).
 *
 * @param list<mixed> $rows
 * @return list<array{kegiatan: string, status: string}>
 */
function org_team_targets_normalize_post_rows(array $rows): array
{
    $normalized = [];
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $hasKegiatan = array_key_exists('kegiatan', $row);
        $hasStatus = array_key_exists('status', $row);
        if ($hasKegiatan && $hasStatus) {
            $normalized[] = [
                'kegiatan' => trim((string) $row['kegiatan']),
                'status' => (string) $row['status'],
            ];
            continue;
        }
        if ($hasKegiatan) {
            $normalized[] = [
                'kegiatan' => trim((string) $row['kegiatan']),
                'status' => '',
            ];
            continue;
        }
        if ($hasStatus && $normalized !== []) {
            $last = count($normalized) - 1;
            if (($normalized[$last]['status'] ?? '') === '') {
                $normalized[$last]['status'] = (string) $row['status'];
            }
        }
    }

    return $normalized;
}

function org_team_targets_tim_label(string $tim): string
{
    return match (org_team_targets_normalize_tim($tim)) {
        'rb' => 'Kinerja dan RB',
        'yanlik' => 'Pelayanan Publik dan Tata Laksana',
        default => 'Kelembagaan dan Anjab',
    };
}

function org_team_targets_tim_short(string $tim): string
{
    return org_team_targets_tim_label($tim);
}

function org_team_targets_status_label(string $status): string
{
    return match (org_team_targets_normalize_status($status)) {
        'berjalan' => 'Berjalan',
        'selesai' => 'Selesai',
        default => 'Direncanakan',
    };
}

function org_team_targets_status_badge_class(string $status): string
{
    return match (org_team_targets_normalize_status($status)) {
        'berjalan' => 'gov-team-target__badge--berjalan',
        'selesai' => 'gov-team-target__badge--selesai',
        default => 'gov-team-target__badge--rencana',
    };
}

/**
 * Persen capaian per status (grafik radial, progress bar, accordion).
 * Direncanakan = 0%, Berjalan = 50%, Selesai = 100%.
 */
function org_team_targets_status_progress_pct(string $status): int
{
    return match (org_team_targets_normalize_status($status)) {
        'selesai' => 100,
        'berjalan' => 50,
        default => 0,
    };
}

function org_team_targets_status_dot_class(string $status): string
{
    return match (org_team_targets_normalize_status($status)) {
        'berjalan' => 'gov-team-target-dot--berjalan',
        'selesai' => 'gov-team-target-dot--selesai',
        default => 'gov-team-target-dot--rencana',
    };
}

/** Warna pekat grafik & aksen sesuai status (selaras legenda target kerja). */
function org_team_targets_status_accent_color(string $status): string
{
    return match (org_team_targets_normalize_status($status)) {
        'berjalan' => '#1A3F6E',
        'selesai' => '#0B5E48',
        default => '#8F6524',
    };
}

/** Warna terang gradien grafik sesuai status. */
function org_team_targets_status_accent_color_light(string $status): string
{
    return match (org_team_targets_normalize_status($status)) {
        'berjalan' => '#5A8FD4',
        'selesai' => '#5EC4A0',
        default => '#E8D4A0',
    };
}

/**
 * Status dominan dari daftar kegiatan (untuk warna gauge kartu tim).
 *
 * @param list<array{status?: string}> $items
 */
function org_team_targets_items_dominant_status(array $items): string
{
    $bestStatus = 'direncanakan';
    $bestPct = -1;
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $st = org_team_targets_normalize_status((string) ($item['status'] ?? ''));
        $pct = org_team_targets_status_progress_pct($st);
        if ($pct > $bestPct) {
            $bestPct = $pct;
            $bestStatus = $st;
        }
    }

    return $bestStatus;
}

/** Petakan rata-rata capaian (0 / 50 / 100) ke status untuk warna batang overview. */
function org_team_targets_status_from_avg_pct(int $avgPct): string
{
    if ($avgPct >= 100) {
        return 'selesai';
    }
    if ($avgPct >= 50) {
        return 'berjalan';
    }
    if ($avgPct > 0) {
        return 'berjalan';
    }

    return 'direncanakan';
}

/**
 * Rata-rata progres tim = jumlah persen tiap kegiatan ÷ jumlah kegiatan.
 *
 * @param list<array{kegiatan?: string, status?: string}> $items
 */
function org_team_targets_tim_average_progress(array $items): int
{
    if ($items === []) {
        return 0;
    }
    $sum = 0;
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $sum += org_team_targets_status_progress_pct((string) ($item['status'] ?? ''));
    }

    return (int) round($sum / count($items));
}

/** Warna utama grafik per tim — palet premium (sapphire, zamrud, emas). */
function org_team_targets_tim_accent_color(string $tim): string
{
    return match (org_team_targets_normalize_tim($tim)) {
        'rb' => '#0B5E48',
        'yanlik' => '#8F6524',
        default => '#1A3F6E',
    };
}

function org_team_targets_tim_accent_color_light(string $tim): string
{
    return match (org_team_targets_normalize_tim($tim)) {
        'rb' => '#5EC4A0',
        'yanlik' => '#E2C27A',
        default => '#7EAAE8',
    };
}

/** Highlight gradien batang grafik akumulasi (sisi terang). */
function org_team_targets_tim_overview_bar_color(string $tim): string
{
    return match (org_team_targets_normalize_tim($tim)) {
        'rb' => '#6EC9A8',
        'yanlik' => '#E8CF8E',
        default => '#8CB8EB',
    };
}

/** Warna gradien ujung batang (sisi pekat / jewel tone). */
function org_team_targets_tim_overview_bar_color_deep(string $tim): string
{
    return match (org_team_targets_normalize_tim($tim)) {
        'rb' => '#0B5E48',
        'yanlik' => '#8F6524',
        default => '#1A3F6E',
    };
}

/** Label ringkas untuk sumbu grafik perbandingan tim. */
function org_team_targets_tim_chart_label(string $tim): string
{
    return match (org_team_targets_normalize_tim($tim)) {
        'rb' => 'Tim Kinerja dan RB',
        'yanlik' => 'Tim Pelayanan Publik',
        default => 'Tim Kelembagaan',
    };
}

/** Kelas badge status kegiatan (selaras legenda & progress bar). */
function org_team_targets_status_bs_badge(string $status): string
{
    return match (org_team_targets_normalize_status($status)) {
        'berjalan' => 'gov-team-target-status-badge gov-team-target-status-badge--berjalan',
        'selesai' => 'gov-team-target-status-badge gov-team-target-status-badge--selesai',
        default => 'gov-team-target-status-badge gov-team-target-status-badge--direncanakan',
    };
}

function org_team_targets_activity_target_caption(string $kegiatan, string $status, int $tahun): string
{
    $kegiatan = trim($kegiatan);
    $tahun = org_team_targets_normalize_tahun($tahun);
    $st = org_team_targets_normalize_status($status);
    if ($st === 'selesai') {
        return 'Target: ' . $kegiatan . ' — selesai pada tahun ' . $tahun . '.';
    }
    if ($st === 'berjalan') {
        return 'Target: ' . $kegiatan . ' — sedang berjalan (capaian tahun ' . $tahun . ').';
    }

    return 'Target: ' . $kegiatan . ' — direncanakan tahun ' . $tahun . '.';
}

function org_team_targets_normalize_tahun(int|string $tahun): int
{
    $y = (int) $tahun;
    if ($y < 2000) {
        $y = (int) date('Y');
    }
    if ($y > 2100) {
        $y = 2100;
    }

    return $y;
}

function org_team_targets_table_exists(mysqli $db): bool
{
    $r = $db->query("SHOW TABLES LIKE 'team_targets'");

    return $r !== false && $r->num_rows > 0;
}

function org_team_targets_year_table_exists(mysqli $db): bool
{
    $r = $db->query("SHOW TABLES LIKE 'team_targets_year'");

    return $r !== false && $r->num_rows > 0;
}

function org_team_targets_ensure_table(mysqli $db): void
{
    $db->query(
        'CREATE TABLE IF NOT EXISTS `team_targets` (
          `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `tim_kerja` ENUM(\'kelembagaan\', \'rb\', \'yanlik\') NOT NULL,
          `tahun` SMALLINT UNSIGNED NOT NULL,
          `kegiatan` VARCHAR(255) NOT NULL DEFAULT \'\',
          `status` ENUM(\'direncanakan\', \'berjalan\', \'selesai\') NOT NULL DEFAULT \'direncanakan\',
          `urutan` INT UNSIGNED NOT NULL DEFAULT 0,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_team_targets_tahun_tim` (`tahun`, `tim_kerja`, `urutan`, `id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    $db->query(
        'CREATE TABLE IF NOT EXISTS `team_targets_year` (
          `tahun` SMALLINT UNSIGNED NOT NULL,
          `tampil_beranda` TINYINT(1) NOT NULL DEFAULT 1,
          `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`tahun`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    org_team_targets_migrate_schema($db);
    org_team_targets_migrate_year_defaults($db);
}

/** Tahun yang punya data target tetapi belum punya baris pengaturan → default tampil di beranda. */
function org_team_targets_migrate_year_defaults(mysqli $db): void
{
    if (!org_team_targets_table_exists($db) || !org_team_targets_year_table_exists($db)) {
        return;
    }
    $db->query(
        'INSERT INTO `team_targets_year` (`tahun`, `tampil_beranda`)
         SELECT DISTINCT `t`.`tahun`, 1
         FROM `team_targets` `t`
         LEFT JOIN `team_targets_year` `y` ON `y`.`tahun` = `t`.`tahun`
         WHERE `y`.`tahun` IS NULL'
    );
}

/**
 * @return list<array<string, mixed>>
 */
function org_team_targets_stmt_fetch_assoc_all(mysqli_stmt $st): array
{
    $rows = [];
    if (method_exists($st, 'get_result')) {
        $res = $st->get_result();
        if ($res !== false) {
            while ($row = $res->fetch_assoc()) {
                if (is_array($row)) {
                    $rows[] = $row;
                }
            }

            return $rows;
        }
    }
    $meta = $st->result_metadata();
    if ($meta === false) {
        return $rows;
    }
    $bind = [];
    $data = [];
    while ($field = $meta->fetch_field()) {
        $name = (string) ($field->name ?? '');
        if ($name === '') {
            continue;
        }
        $data[$name] = null;
        $bind[] = &$data[$name];
    }
    $meta->free();
    if ($bind === []) {
        return $rows;
    }
    call_user_func_array([$st, 'bind_result'], $bind);
    while ($st->fetch()) {
        $copy = [];
        foreach ($data as $key => $value) {
            $copy[$key] = $value;
        }
        $rows[] = $copy;
    }

    return $rows;
}

/** Apakah target tahun ini ditampilkan di halaman beranda (default: tampil jika belum pernah disimpan). */
function org_team_targets_tampil_beranda(mysqli $db, int $tahun): bool
{
    org_team_targets_ensure_table($db);
    $tahun = org_team_targets_normalize_tahun($tahun);
    if (!org_team_targets_year_table_exists($db)) {
        return true;
    }
    $st = $db->prepare('SELECT `tampil_beranda` FROM `team_targets_year` WHERE `tahun` = ? LIMIT 1');
    if ($st === false) {
        return true;
    }
    $st->bind_param('i', $tahun);
    $st->execute();
    $res = $st->get_result();
    $row = $res !== false ? $res->fetch_assoc() : false;
    $st->close();
    if (!is_array($row)) {
        return true;
    }

    return (int) ($row['tampil_beranda'] ?? 0) === 1;
}

function org_team_targets_set_tampil_beranda(mysqli $db, int $tahun, bool $tampil): bool
{
    org_team_targets_ensure_table($db);
    $tahun = org_team_targets_normalize_tahun($tahun);
    $flag = $tampil ? 1 : 0;
    $st = $db->prepare(
        'REPLACE INTO `team_targets_year` (`tahun`, `tampil_beranda`) VALUES (?, ?)'
    );
    if ($st === false) {
        org_team_targets_last_error_set((string) $db->error);

        return false;
    }
    $st->bind_param('ii', $tahun, $flag);
    $ok = $st->execute();
    if (!$ok) {
        org_team_targets_last_error_set((string) $st->error);
    }
    $st->close();

    return $ok;
}

function org_team_targets_should_show_on_beranda(mysqli $db, int $tahun): bool
{
    if (org_team_targets_count_all($db, $tahun) < 1) {
        return false;
    }

    return org_team_targets_tampil_beranda($db, $tahun);
}

/** Tahun yang punya data dan diizinkan tampil di beranda. */
function org_team_targets_fetch_beranda_years(mysqli $db): array
{
    $years = org_team_targets_fetch_available_years($db);

    return array_values(array_filter(
        $years,
        static fn (int $y): bool => org_team_targets_should_show_on_beranda($db, $y)
    ));
}

/**
 * @param array<string, mixed> $row
 * @return array{id: string, tim_kerja: string, tahun: int, kegiatan: string, status: string, urutan: int}
 */
function org_team_targets_row_from_db(array $row): array
{
    if (isset($row['nama_kegiatan']) || isset($row['tim_kerja_id'])) {
        return [
            'id' => (string) ($row['id'] ?? ''),
            'tim_kerja' => org_team_targets_legacy_id_to_tim((int) ($row['tim_kerja_id'] ?? 1)),
            'tahun' => org_team_targets_normalize_tahun($row['tahun'] ?? 0),
            'kegiatan' => (string) ($row['nama_kegiatan'] ?? ''),
            'status' => org_team_targets_normalize_status((string) ($row['status_target'] ?? '')),
            'urutan' => (int) ($row['urutan'] ?? 0),
        ];
    }

    return [
        'id' => (string) ($row['id'] ?? ''),
        'tim_kerja' => org_team_targets_normalize_tim((string) ($row['tim_kerja'] ?? '')),
        'tahun' => org_team_targets_normalize_tahun($row['tahun'] ?? 0),
        'kegiatan' => (string) ($row['kegiatan'] ?? ''),
        'status' => org_team_targets_normalize_status((string) ($row['status'] ?? '')),
        'urutan' => (int) ($row['urutan'] ?? 0),
    ];
}

/** @return array{kelembagaan: list<array{id: string, kegiatan: string, status: string}>, rb: list<array{id: string, kegiatan: string, status: string}>, yanlik: list<array{id: string, kegiatan: string, status: string}>} */
function org_team_targets_empty_grouped(): array
{
    return ['kelembagaan' => [], 'rb' => [], 'yanlik' => []];
}

/**
 * @return array{kelembagaan: list<array{id: string, kegiatan: string, status: string}>, rb: list<array{id: string, kegiatan: string, status: string}>, yanlik: list<array{id: string, kegiatan: string, status: string}>}
 */
function org_team_targets_fetch_grouped_by_year(mysqli $db, int $tahun): array
{
    $grouped = org_team_targets_empty_grouped();
    $tahun = org_team_targets_normalize_tahun($tahun);
    if (!org_team_targets_table_exists($db)) {
        return $grouped;
    }
    $legacy = org_team_targets_uses_legacy_schema($db);
    if ($legacy) {
        $sql = 'SELECT `id`, `tim_kerja_id`, `tahun`, `nama_kegiatan`, `status_target`, `urutan`
                FROM `team_targets`
                WHERE `tahun` = ?
                ORDER BY `tim_kerja_id` ASC, `urutan` ASC, `id` ASC';
    } else {
        $sql = 'SELECT `id`, `tim_kerja`, `tahun`, `kegiatan`, `status`, `urutan`
                FROM `team_targets`
                WHERE `tahun` = ?
                ORDER BY `tim_kerja` ASC, `urutan` ASC, `id` ASC';
    }
    $st = $db->prepare($sql);
    if ($st === false) {
        return $grouped;
    }
    $st->bind_param('i', $tahun);
    $st->execute();
    foreach (org_team_targets_stmt_fetch_assoc_all($st) as $row) {
        $parsed = org_team_targets_row_from_db($row);
        $tim = $parsed['tim_kerja'];
        if (!isset($grouped[$tim])) {
            continue;
        }
        $grouped[$tim][] = [
            'id' => $parsed['id'],
            'kegiatan' => $parsed['kegiatan'],
            'status' => $parsed['status'],
        ];
    }
    $st->close();

    return $grouped;
}

/** @return list<int> */
function org_team_targets_fetch_available_years(mysqli $db): array
{
    if (!org_team_targets_table_exists($db)) {
        return [(int) date('Y')];
    }
    $years = [];
    $res = $db->query('SELECT DISTINCT `tahun` FROM `team_targets` ORDER BY `tahun` DESC');
    if ($res !== false) {
        while ($row = $res->fetch_assoc()) {
            if (!is_array($row)) {
                continue;
            }
            $years[] = org_team_targets_normalize_tahun($row['tahun'] ?? 0);
        }
    }
    $current = (int) date('Y');
    if (!in_array($current, $years, true)) {
        $years[] = $current;
    }
    rsort($years);

    return array_values(array_unique($years));
}

/**
 * @param array<string, list<array{kegiatan: string, status: string}>> $byTim
 */
function org_team_targets_replace_year(mysqli $db, int $tahun, array $byTim, bool $tampilBeranda = true): bool
{
    org_team_targets_last_error_clear();
    org_team_targets_ensure_table($db);
    $tahun = org_team_targets_normalize_tahun($tahun);
    $hasUrutan = org_team_targets_column_exists($db, 'urutan');

    $stDel = $db->prepare('DELETE FROM `team_targets` WHERE `tahun` = ?');
    if ($stDel === false) {
        org_team_targets_last_error_set((string) $db->error);

        return false;
    }
    $stDel->bind_param('i', $tahun);
    if (!$stDel->execute()) {
        org_team_targets_last_error_set((string) $stDel->error);
        $stDel->close();

        return false;
    }
    $stDel->close();

    $inserted = 0;
    $legacy = org_team_targets_uses_legacy_schema($db);

    foreach (org_team_targets_tim_list() as $timKey) {
        $rows = $byTim[$timKey] ?? [];
        if (!is_array($rows)) {
            continue;
        }
        $urutan = 0;
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $kegiatan = trim((string) ($row['kegiatan'] ?? ''));
            if ($kegiatan === '') {
                continue;
            }
            if (function_exists('org_sanitize_plain')) {
                $kegiatan = org_sanitize_plain($kegiatan);
            }
            if ($kegiatan === '') {
                continue;
            }
            $status = org_team_targets_normalize_status((string) ($row['status'] ?? ''));
            if ($legacy) {
                if ($hasUrutan) {
                    $sqlInsert = 'INSERT INTO `team_targets` (`tim_kerja_id`, `tahun`, `nama_kegiatan`, `status_target`, `urutan`)
                         VALUES (?, ?, ?, ?, ?)';
                } else {
                    $sqlInsert = 'INSERT INTO `team_targets` (`tim_kerja_id`, `tahun`, `nama_kegiatan`, `status_target`)
                         VALUES (?, ?, ?, ?)';
                }
            } elseif ($hasUrutan) {
                $sqlInsert = 'INSERT INTO `team_targets` (`tim_kerja`, `tahun`, `kegiatan`, `status`, `urutan`)
                     VALUES (?, ?, ?, ?, ?)';
            } else {
                $sqlInsert = 'INSERT INTO `team_targets` (`tim_kerja`, `tahun`, `kegiatan`, `status`)
                     VALUES (?, ?, ?, ?)';
            }
            $st = $db->prepare($sqlInsert);
            if ($st === false) {
                org_team_targets_last_error_set((string) $db->error);

                return false;
            }
            $tahunBind = $tahun;
            $kegiatanBind = $kegiatan;
            $statusBind = $status;
            if ($legacy && $hasUrutan) {
                $timIdBind = org_team_targets_tim_to_legacy_id($timKey);
                $urutanBind = $urutan;
                $st->bind_param('iissi', $timIdBind, $tahunBind, $kegiatanBind, $statusBind, $urutanBind);
            } elseif ($legacy) {
                $timIdBind = org_team_targets_tim_to_legacy_id($timKey);
                $st->bind_param('iiss', $timIdBind, $tahunBind, $kegiatanBind, $statusBind);
            } elseif ($hasUrutan) {
                $timBind = $timKey;
                $urutanBind = $urutan;
                $st->bind_param('sissi', $timBind, $tahunBind, $kegiatanBind, $statusBind, $urutanBind);
            } else {
                $timBind = $timKey;
                $st->bind_param('siss', $timBind, $tahunBind, $kegiatanBind, $statusBind);
            }
            if (!$st->execute()) {
                org_team_targets_last_error_set((string) $st->error);
                $st->close();

                return false;
            }
            $st->close();
            $inserted++;
            $urutan++;
        }
    }

    if (!org_team_targets_set_tampil_beranda($db, $tahun, $tampilBeranda)) {
        return false;
    }

    if ($inserted > 0) {
        return true;
    }

    if (!$tampilBeranda) {
        return true;
    }

    org_team_targets_last_error_set('Tidak ada kegiatan yang berhasil disimpan. Isi nama kegiatan lalu simpan lagi.');

    return false;
}

function org_team_targets_parse_tampil_beranda_from_post(): bool
{
    if (!isset($_POST['tampil_beranda'])) {
        return true;
    }
    $raw = $_POST['tampil_beranda'];
    if (is_array($raw)) {
        foreach ($raw as $v) {
            if ((string) $v === '1') {
                return true;
            }
        }

        return false;
    }

    return (string) $raw === '1';
}

/**
 * @return array<string, list<array{kegiatan: string, status: string}>>
 */
function org_team_targets_parse_post_by_tim(): array
{
    $raw = $_POST['team_targets'] ?? [];
    if (!is_array($raw)) {
        return org_team_targets_empty_grouped();
    }
    $out = org_team_targets_empty_grouped();
    foreach (org_team_targets_tim_list() as $tim) {
        $rows = $raw[$tim] ?? [];
        if (!is_array($rows)) {
            continue;
        }
        foreach (org_team_targets_normalize_post_rows($rows) as $row) {
            $kegiatan = trim((string) ($row['kegiatan'] ?? ''));
            if ($kegiatan === '') {
                continue;
            }
            $out[$tim][] = [
                'kegiatan' => $kegiatan,
                'status' => org_team_targets_normalize_status((string) ($row['status'] ?? '')),
            ];
        }
    }

    return $out;
}

function org_team_targets_count_all(mysqli $db, int $tahun): int
{
    $tahun = org_team_targets_normalize_tahun($tahun);
    if (!org_team_targets_table_exists($db)) {
        return 0;
    }
    $st = $db->prepare('SELECT COUNT(*) AS `c` FROM `team_targets` WHERE `tahun` = ?');
    if ($st === false) {
        return 0;
    }
    $st->bind_param('i', $tahun);
    $st->execute();
    $count = 0;
    if (method_exists($st, 'get_result')) {
        $res = $st->get_result();
        if ($res !== false) {
            $row = $res->fetch_assoc();
            if (is_array($row)) {
                $count = (int) ($row['c'] ?? 0);
            }
        } else {
            foreach (org_team_targets_stmt_fetch_assoc_all($st) as $row) {
                $count = (int) ($row['c'] ?? 0);
                break;
            }
        }
    } else {
        foreach (org_team_targets_stmt_fetch_assoc_all($st) as $row) {
            $count = (int) ($row['c'] ?? 0);
            break;
        }
    }
    $st->close();

    return $count;
}

/** Tahun pertama yang boleh ditampilkan di beranda (prioritas: tahun diminta → tahun berjalan → terbaru). */
function org_team_targets_resolve_beranda_year(mysqli $db, int $preferredTahun): int
{
    $preferredTahun = org_team_targets_normalize_tahun($preferredTahun);
    if (org_team_targets_should_show_on_beranda($db, $preferredTahun)) {
        return $preferredTahun;
    }
    $current = (int) date('Y');
    if ($current !== $preferredTahun && org_team_targets_should_show_on_beranda($db, $current)) {
        return $current;
    }
    foreach (org_team_targets_fetch_available_years($db) as $y) {
        if (org_team_targets_should_show_on_beranda($db, $y)) {
            return $y;
        }
    }

    return 0;
}
