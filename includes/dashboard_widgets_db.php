<?php
declare(strict_types=1);

if (!defined('ORG_ROOT')) {
    define('ORG_ROOT', dirname(__DIR__));
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_database.php';

/** @return list<string> */
function org_dashboard_widgets_tipe_list(): array
{
    return ['progres_angka', 'perbandingan_nilai'];
}

/** @return list<string> */
function org_dashboard_widgets_warna_list(): array
{
    return ['primary', 'success', 'danger'];
}

function org_dashboard_widgets_normalize_tipe(string $tipe): string
{
    $tipe = strtolower(trim($tipe));

    return in_array($tipe, org_dashboard_widgets_tipe_list(), true) ? $tipe : 'progres_angka';
}

function org_dashboard_widgets_normalize_warna(string $warna): string
{
    $warna = strtolower(trim($warna));

    return in_array($warna, org_dashboard_widgets_warna_list(), true) ? $warna : 'primary';
}

function org_dashboard_widgets_column_exists(mysqli $db, string $column): bool
{
    $column = preg_replace('/[^a-z0-9_]/i', '', $column);
    if ($column === '') {
        return false;
    }
    $r = $db->query("SHOW COLUMNS FROM `dashboard_widgets` LIKE '{$column}'");

    return $r !== false && $r->num_rows > 0;
}

/** Selaraskan tabel lama (tanpa `aktif`) dengan skema widget dinamis terbaru. */
function org_dashboard_widgets_migrate_schema(mysqli $db): void
{
    if (!org_dashboard_widgets_table_exists($db)) {
        return;
    }
    if (!org_dashboard_widgets_column_exists($db, 'aktif')) {
        $db->query(
            'ALTER TABLE `dashboard_widgets`
             ADD COLUMN `aktif` TINYINT(1) NOT NULL DEFAULT 1 AFTER `urutan`'
        );
    }
    if (!org_dashboard_widgets_column_exists($db, 'updated_at')) {
        $db->query(
            'ALTER TABLE `dashboard_widgets`
             ADD COLUMN `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`'
        );
    }
    $db->query(
        'ALTER TABLE `dashboard_widgets`
         MODIFY COLUMN `nilai_kiri` VARCHAR(255) NOT NULL DEFAULT \'\',
         MODIFY COLUMN `nilai_kanan` VARCHAR(255) NOT NULL DEFAULT \'\''
    );
    $idxRes = $db->query("SHOW INDEX FROM `dashboard_widgets` WHERE Key_name = 'idx_dashboard_widgets_aktif_urutan'");
    if ($idxRes !== false && $idxRes->num_rows === 0) {
        $db->query(
            'ALTER TABLE `dashboard_widgets`
             ADD KEY `idx_dashboard_widgets_aktif_urutan` (`aktif`, `urutan`, `id`)'
        );
    }
    if ($idxRes !== false) {
        $idxRes->free();
    }
}

function org_dashboard_widgets_ensure_table(mysqli $db): void
{
    $db->query(
        'CREATE TABLE IF NOT EXISTS `dashboard_widgets` (
          `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `judul` VARCHAR(255) NOT NULL DEFAULT \'\',
          `tipe_data` ENUM(\'progres_angka\', \'perbandingan_nilai\') NOT NULL DEFAULT \'progres_angka\',
          `nilai_kiri` VARCHAR(255) NOT NULL DEFAULT \'\',
          `nilai_kanan` VARCHAR(255) NOT NULL DEFAULT \'\',
          `warna_tema` VARCHAR(32) NOT NULL DEFAULT \'primary\',
          `urutan` INT UNSIGNED NOT NULL DEFAULT 0,
          `aktif` TINYINT(1) NOT NULL DEFAULT 1,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_dashboard_widgets_aktif_urutan` (`aktif`, `urutan`, `id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    org_dashboard_widgets_migrate_schema($db);
    org_widget_details_ensure_table($db);
}

function org_dashboard_widgets_table_exists(mysqli $db): bool
{
    $r = $db->query("SHOW TABLES LIKE 'dashboard_widgets'");

    return $r !== false && $r->num_rows > 0;
}

/**
 * @param array<string, mixed> $row
 * @return array{id: string, judul: string, tipe_data: string, nilai_kiri: string, nilai_kanan: string, warna_tema: string, urutan: int, aktif: int, created_at: string, updated_at: string}
 */
function org_dashboard_widgets_row_from_db(array $row): array
{
    return [
        'id' => (string) ($row['id'] ?? ''),
        'judul' => (string) ($row['judul'] ?? ''),
        'tipe_data' => org_dashboard_widgets_normalize_tipe((string) ($row['tipe_data'] ?? '')),
        'nilai_kiri' => (string) ($row['nilai_kiri'] ?? ''),
        'nilai_kanan' => (string) ($row['nilai_kanan'] ?? ''),
        'warna_tema' => org_dashboard_widgets_normalize_warna((string) ($row['warna_tema'] ?? '')),
        'urutan' => (int) ($row['urutan'] ?? 0),
        'aktif' => (int) ($row['aktif'] ?? 1),
        'created_at' => (string) ($row['created_at'] ?? ''),
        'updated_at' => (string) ($row['updated_at'] ?? ''),
    ];
}

/**
 * @return list<array{id: string, judul: string, tipe_data: string, nilai_kiri: string, nilai_kanan: string, warna_tema: string, urutan: int, aktif: int, created_at: string, updated_at: string}>
 */
function org_dashboard_widgets_fetch_all(mysqli $db, bool $onlyActive = false): array
{
    if (!org_dashboard_widgets_table_exists($db)) {
        return [];
    }
    $hasAktif = org_dashboard_widgets_column_exists($db, 'aktif');
    $hasUpdated = org_dashboard_widgets_column_exists($db, 'updated_at');
    $cols = '`id`, `judul`, `tipe_data`, `nilai_kiri`, `nilai_kanan`, `warna_tema`, `urutan`';
    $cols .= $hasAktif ? ', `aktif`' : '';
    $cols .= ', `created_at`';
    $cols .= $hasUpdated ? ', `updated_at`' : '';
    $sql = 'SELECT ' . $cols . ' FROM `dashboard_widgets`';
    if ($onlyActive && $hasAktif) {
        $sql .= ' WHERE `aktif` = 1';
    }
    $sql .= ' ORDER BY `urutan` ASC, `id` ASC';
    $res = $db->query($sql);
    $rows = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            if (is_array($row)) {
                $rows[] = org_dashboard_widgets_row_from_db($row);
            }
        }
    }

    return $rows;
}

/**
 * @return array{id: string, judul: string, tipe_data: string, nilai_kiri: string, nilai_kanan: string, warna_tema: string, urutan: int, aktif: int, created_at: string, updated_at: string}|null
 */
function org_dashboard_widgets_fetch_by_id(mysqli $db, int $id): ?array
{
    if ($id < 1 || !org_dashboard_widgets_table_exists($db)) {
        return null;
    }
    $st = $db->prepare(
        'SELECT `id`, `judul`, `tipe_data`, `nilai_kiri`, `nilai_kanan`, `warna_tema`, `urutan`, `aktif`, `created_at`, `updated_at`
         FROM `dashboard_widgets` WHERE `id` = ? LIMIT 1'
    );
    if ($st === false) {
        return null;
    }
    $st->bind_param('i', $id);
    $st->execute();
    $res = $st->get_result();
    $row = $res !== false ? $res->fetch_assoc() : null;
    $st->close();
    if (!is_array($row)) {
        return null;
    }

    return org_dashboard_widgets_row_from_db($row);
}

function org_dashboard_widgets_insert(
    mysqli $db,
    string $judul,
    string $tipeData,
    string $nilaiKiri,
    string $nilaiKanan,
    string $warnaTema,
    int $urutan,
    int $aktif
): ?int {
    org_dashboard_widgets_ensure_table($db);
    $tipeData = org_dashboard_widgets_normalize_tipe($tipeData);
    $warnaTema = org_dashboard_widgets_normalize_warna($warnaTema);
    $urutan = max(0, min(9999, $urutan));
    $aktif = $aktif === 1 ? 1 : 0;
    $hasAktif = org_dashboard_widgets_column_exists($db, 'aktif');
    if ($hasAktif) {
        $st = $db->prepare(
            'INSERT INTO `dashboard_widgets` (`judul`, `tipe_data`, `nilai_kiri`, `nilai_kanan`, `warna_tema`, `urutan`, `aktif`)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        if ($st === false) {
            return null;
        }
        $st->bind_param('sssssii', $judul, $tipeData, $nilaiKiri, $nilaiKanan, $warnaTema, $urutan, $aktif);
    } else {
        $st = $db->prepare(
            'INSERT INTO `dashboard_widgets` (`judul`, `tipe_data`, `nilai_kiri`, `nilai_kanan`, `warna_tema`, `urutan`)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        if ($st === false) {
            return null;
        }
        $st->bind_param('sssssi', $judul, $tipeData, $nilaiKiri, $nilaiKanan, $warnaTema, $urutan);
    }
    $ok = $st->execute();
    $st->close();
    if (!$ok) {
        return null;
    }

    return (int) $db->insert_id;
}

function org_dashboard_widgets_update(
    mysqli $db,
    int $id,
    string $judul,
    string $tipeData,
    string $nilaiKiri,
    string $nilaiKanan,
    string $warnaTema,
    int $urutan,
    int $aktif
): bool {
    if ($id < 1 || !org_dashboard_widgets_table_exists($db)) {
        return false;
    }
    org_dashboard_widgets_ensure_table($db);
    $tipeData = org_dashboard_widgets_normalize_tipe($tipeData);
    $warnaTema = org_dashboard_widgets_normalize_warna($warnaTema);
    $urutan = max(0, min(9999, $urutan));
    $aktif = $aktif === 1 ? 1 : 0;
    $hasAktif = org_dashboard_widgets_column_exists($db, 'aktif');
    if ($hasAktif) {
        $st = $db->prepare(
            'UPDATE `dashboard_widgets`
             SET `judul` = ?, `tipe_data` = ?, `nilai_kiri` = ?, `nilai_kanan` = ?, `warna_tema` = ?, `urutan` = ?, `aktif` = ?
             WHERE `id` = ? LIMIT 1'
        );
        if ($st === false) {
            return false;
        }
        $st->bind_param('sssssiii', $judul, $tipeData, $nilaiKiri, $nilaiKanan, $warnaTema, $urutan, $aktif, $id);
    } else {
        $st = $db->prepare(
            'UPDATE `dashboard_widgets`
             SET `judul` = ?, `tipe_data` = ?, `nilai_kiri` = ?, `nilai_kanan` = ?, `warna_tema` = ?, `urutan` = ?
             WHERE `id` = ? LIMIT 1'
        );
        if ($st === false) {
            return false;
        }
        $st->bind_param('sssssii', $judul, $tipeData, $nilaiKiri, $nilaiKanan, $warnaTema, $urutan, $id);
    }
    $ok = $st->execute();
    $st->close();

    return (bool) $ok;
}

function org_dashboard_widgets_delete_by_id(mysqli $db, int $id): bool
{
    if ($id < 1 || !org_dashboard_widgets_table_exists($db)) {
        return false;
    }
    org_widget_details_delete_by_widget_id($db, $id);
    $st = $db->prepare('DELETE FROM `dashboard_widgets` WHERE `id` = ? LIMIT 1');
    if ($st === false) {
        return false;
    }
    $st->bind_param('i', $id);
    $ok = $st->execute();
    $st->close();

    return (bool) $ok;
}

/**
 * @return array{label: string, tone: string}
 */
function org_dashboard_widgets_progress_status(float $pct): array
{
    if ($pct >= 80.0) {
        return ['label' => 'Progress Baik', 'tone' => 'good'];
    }
    if ($pct >= 50.0) {
        return ['label' => 'Progress Sedang', 'tone' => 'mid'];
    }

    return ['label' => 'Perlu Perhatian', 'tone' => 'warn'];
}

function org_dashboard_widgets_icon_class(string $tipe, string $warna): string
{
    if ($tipe === 'perbandingan_nilai') {
        return 'fa-solid fa-arrows-left-right';
    }
    if ($warna === 'success') {
        return 'fa-solid fa-circle-check';
    }
    if ($warna === 'danger') {
        return 'fa-solid fa-triangle-exclamation';
    }

    return 'fa-solid fa-chart-line';
}

/**
 * Tinggi batang mini sparkline (5 titik) dari persentase capaian.
 *
 * @return list<int>
 */
function org_dashboard_widgets_spark_heights(float $pct): array
{
    $p = max(0.0, min(100.0, $pct));

    return [
        (int) max(18, min(100, round($p * 0.55))),
        (int) max(22, min(100, round($p * 0.68))),
        (int) max(28, min(100, round($p * 0.78))),
        (int) max(34, min(100, round($p * 0.88))),
        (int) max(40, min(100, round($p))),
    ];
}

/**
 * @return array{label: string, dir: string}
 */
function org_dashboard_widgets_trend_meta(float $pct): array
{
    if ($pct >= 70.0) {
        return ['label' => 'Trend naik', 'dir' => 'up'];
    }
    if ($pct >= 40.0) {
        return ['label' => 'Trend stabil', 'dir' => 'flat'];
    }

    return ['label' => 'Perlu dorongan', 'dir' => 'down'];
}

/** Teks informasi capaian, mis. «40 dari 57 OPD selesai». */
function org_dashboard_widgets_info_caption(string $nilaiKiri, string $nilaiKanan, string $tipeData): string
{
    if ($tipeData === 'perbandingan_nilai') {
        return '';
    }
    $kiri = trim($nilaiKiri);
    $kanan = trim($nilaiKanan);
    if ($kiri === '' || $kanan === '') {
        return '';
    }
    $kiriNum = str_replace(',', '.', $kiri);
    $kananNum = str_replace(',', '.', $kanan);
    if (is_numeric($kiriNum) && is_numeric($kananNum)) {
        return $kiri . ' dari ' . $kanan . ' selesai';
    }

    return $kiri . ' / ' . $kanan;
}

/** Persentase progres (0–100) dari nilai_kiri / nilai_kanan. */
function org_dashboard_widgets_hitung_persen(string $nilaiKiri, string $nilaiKanan): float
{
    $kiri = (float) str_replace(',', '.', trim($nilaiKiri));
    $kanan = (float) str_replace(',', '.', trim($nilaiKanan));
    if ($kanan <= 0.0) {
        return 0.0;
    }
    $pct = ($kiri / $kanan) * 100.0;

    return max(0.0, min(100.0, round($pct, 1)));
}

/* —— widget_details —— */

function org_widget_details_column_exists(mysqli $db, string $column): bool
{
    $column = preg_replace('/[^a-z0-9_]/i', '', $column);
    if ($column === '') {
        return false;
    }
    $r = $db->query("SHOW COLUMNS FROM `widget_details` LIKE '{$column}'");

    return $r !== false && $r->num_rows > 0;
}

/** Selaraskan tabel widget_details lama dengan skema terbaru. */
function org_widget_details_migrate_schema(mysqli $db): void
{
    if (!org_widget_details_table_exists($db)) {
        return;
    }
    if (!org_widget_details_column_exists($db, 'urutan')) {
        $db->query(
            'ALTER TABLE `widget_details`
             ADD COLUMN `urutan` INT UNSIGNED NOT NULL DEFAULT 0'
        );
    }
    if (!org_widget_details_column_exists($db, 'created_at')) {
        $db->query(
            'ALTER TABLE `widget_details`
             ADD COLUMN `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP'
        );
    }
    $db->query("UPDATE `widget_details` SET `status` = 'selesai' WHERE `status` = 'sudah'");
    $db->query(
        'ALTER TABLE `widget_details`
         MODIFY COLUMN `nama_opd` VARCHAR(255) NOT NULL DEFAULT \'\',
         MODIFY COLUMN `status` ENUM(\'selesai\', \'belum\', \'dalam_pengerjaan\') NOT NULL DEFAULT \'belum\',
         MODIFY COLUMN `alasan` TEXT NOT NULL'
    );
    $idxRes = $db->query("SHOW INDEX FROM `widget_details` WHERE Key_name = 'idx_widget_details_widget'");
    if ($idxRes !== false && $idxRes->num_rows === 0) {
        $db->query(
            'ALTER TABLE `widget_details`
             ADD KEY `idx_widget_details_widget` (`widget_id`, `status`, `urutan`)'
        );
    }
    if ($idxRes !== false) {
        $idxRes->free();
    }
}

function org_widget_details_ensure_table(mysqli $db): void
{
    $db->query(
        'CREATE TABLE IF NOT EXISTS `widget_details` (
          `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `widget_id` INT UNSIGNED NOT NULL,
          `nama_opd` VARCHAR(255) NOT NULL DEFAULT \'\',
          `status` ENUM(\'selesai\', \'belum\', \'dalam_pengerjaan\') NOT NULL DEFAULT \'belum\',
          `alasan` TEXT NOT NULL,
          `urutan` INT UNSIGNED NOT NULL DEFAULT 0,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_widget_details_widget` (`widget_id`, `status`, `urutan`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    org_widget_details_migrate_schema($db);
}

function org_widget_details_table_exists(mysqli $db): bool
{
    $r = $db->query("SHOW TABLES LIKE 'widget_details'");

    return $r !== false && $r->num_rows > 0;
}

/** @return list<string> */
function org_widget_details_status_list(): array
{
    return ['selesai', 'belum', 'dalam_pengerjaan'];
}

function org_widget_details_status_label(string $status): string
{
    $status = org_widget_details_normalize_status($status);

    return match ($status) {
        'selesai' => 'Selesai',
        'dalam_pengerjaan' => 'Dalam Pengerjaan',
        default => 'Belum Ditambahkan',
    };
}

function org_widget_details_normalize_status(string $status): string
{
    $status = strtolower(trim(str_replace([' ', '-'], '_', $status)));
    if ($status === 'sudah' || $status === 'selesai' || $status === 'done') {
        return 'selesai';
    }
    if ($status === 'dalam_pengerjaan' || $status === 'proses' || $status === 'progress' || $status === 'ongoing') {
        return 'dalam_pengerjaan';
    }

    return 'belum';
}

/** @return array{selesai: list<array{id: string, nama_opd: string, alasan: string}>, belum: list<array{id: string, nama_opd: string, alasan: string}>, dalam_pengerjaan: list<array{id: string, nama_opd: string, alasan: string}>} */
function org_widget_details_empty_grouped(): array
{
    return ['selesai' => [], 'belum' => [], 'dalam_pengerjaan' => []];
}

/**
 * @param array<string, mixed> $row
 * @return array{id: string, widget_id: string, nama_opd: string, status: string, alasan: string, urutan: int}
 */
function org_widget_details_row_from_db(array $row): array
{
    return [
        'id' => (string) ($row['id'] ?? ''),
        'widget_id' => (string) ($row['widget_id'] ?? ''),
        'nama_opd' => (string) ($row['nama_opd'] ?? ''),
        'status' => org_widget_details_normalize_status((string) ($row['status'] ?? '')),
        'alasan' => (string) ($row['alasan'] ?? ''),
        'urutan' => (int) ($row['urutan'] ?? 0),
    ];
}

/**
 * @return array{selesai: list<array{id: string, nama_opd: string, alasan: string}>, belum: list<array{id: string, nama_opd: string, alasan: string}>, dalam_pengerjaan: list<array{id: string, nama_opd: string, alasan: string}>}
 */
function org_widget_details_fetch_grouped(mysqli $db, int $widgetId): array
{
    $empty = org_widget_details_empty_grouped();
    if ($widgetId < 1 || !org_widget_details_table_exists($db)) {
        return $empty;
    }
    $orderBy = org_widget_details_column_exists($db, 'urutan') ? '`urutan` ASC, `id` ASC' : '`id` ASC';
    $cols = '`id`, `widget_id`, `nama_opd`, `status`, `alasan`';
    if (org_widget_details_column_exists($db, 'urutan')) {
        $cols .= ', `urutan`';
    }
    $st = $db->prepare(
        'SELECT ' . $cols . ' FROM `widget_details` WHERE `widget_id` = ? ORDER BY ' . $orderBy
    );
    if ($st === false) {
        return $empty;
    }
    $st->bind_param('i', $widgetId);
    $st->execute();
    $res = $st->get_result();
    $grouped = $empty;
    if ($res !== false) {
        while ($row = $res->fetch_assoc()) {
            if (!is_array($row)) {
                continue;
            }
            $parsed = org_widget_details_row_from_db($row);
            $bucket = org_widget_details_normalize_status($parsed['status']);
            if (!isset($grouped[$bucket])) {
                $bucket = 'belum';
            }
            $grouped[$bucket][] = [
                'id' => $parsed['id'],
                'nama_opd' => $parsed['nama_opd'],
                'alasan' => $parsed['alasan'],
            ];
        }
    }
    $st->close();

    return $grouped;
}

/**
 * @param list<int> $widgetIds
 * @return array<string, array{selesai: list<array{id: string, nama_opd: string, alasan: string}>, belum: list<array{id: string, nama_opd: string, alasan: string}>, dalam_pengerjaan: list<array{id: string, nama_opd: string, alasan: string}>}>
 */
function org_widget_details_fetch_grouped_map(mysqli $db, array $widgetIds): array
{
    $map = [];
    foreach ($widgetIds as $wid) {
        $id = (int) $wid;
        if ($id > 0) {
            $map[(string) $id] = org_widget_details_fetch_grouped($db, $id);
        }
    }

    return $map;
}

function org_widget_details_delete_by_widget_id(mysqli $db, int $widgetId): void
{
    if ($widgetId < 1 || !org_widget_details_table_exists($db)) {
        return;
    }
    $st = $db->prepare('DELETE FROM `widget_details` WHERE `widget_id` = ?');
    if ($st === false) {
        return;
    }
    $st->bind_param('i', $widgetId);
    $st->execute();
    $st->close();
}

/**
 * @param list<array{nama_opd: string, status: string, alasan: string}> $rows
 */
function org_widget_details_replace_all(mysqli $db, int $widgetId, array $rows): bool
{
    if ($widgetId < 1) {
        return false;
    }
    org_widget_details_ensure_table($db);
    org_widget_details_delete_by_widget_id($db, $widgetId);
    if ($rows === []) {
        return true;
    }
    $hasUrutan = org_widget_details_column_exists($db, 'urutan');
    if ($hasUrutan) {
        $sql = 'INSERT INTO `widget_details` (`widget_id`, `nama_opd`, `status`, `alasan`, `urutan`) VALUES (?, ?, ?, ?, ?)';
    } else {
        $sql = 'INSERT INTO `widget_details` (`widget_id`, `nama_opd`, `status`, `alasan`) VALUES (?, ?, ?, ?)';
    }
    $st = $db->prepare($sql);
    if ($st === false) {
        return false;
    }
    $urutan = 0;
    foreach ($rows as $row) {
        $nama = trim((string) ($row['nama_opd'] ?? ''));
        if ($nama === '') {
            continue;
        }
        $statusVal = org_widget_details_normalize_status((string) ($row['status'] ?? 'belum'));
        $alasanVal = trim((string) ($row['alasan'] ?? ''));
        $wid = $widgetId;
        if ($hasUrutan) {
            $urt = $urutan;
            $st->bind_param('isssi', $wid, $nama, $statusVal, $alasanVal, $urt);
        } else {
            $st->bind_param('isss', $wid, $nama, $statusVal, $alasanVal);
        }
        if (!$st->execute()) {
            $st->close();

            return false;
        }
        $urutan++;
    }
    $st->close();

    return true;
}

/**
 * @return list<array{nama_opd: string, status: string, alasan: string}>
 */
function org_widget_details_parse_post_rows(): array
{
    $names = $_POST['detail_nama_opd'] ?? [];
    $statuses = $_POST['detail_status'] ?? [];
    $alasans = $_POST['detail_alasan'] ?? [];
    if (!is_array($names)) {
        return [];
    }
    $rows = [];
    $count = count($names);
    for ($i = 0; $i < $count; $i++) {
        $nama = trim((string) ($names[$i] ?? ''));
        if ($nama === '') {
            continue;
        }
        $statusRaw = is_array($statuses) ? ($statuses[$i] ?? 'belum') : 'belum';
        $alasanRaw = is_array($alasans) ? ($alasans[$i] ?? '') : '';
        $statusNorm = org_widget_details_normalize_status((string) $statusRaw);
        $alasanVal = trim((string) $alasanRaw);
        if ($statusNorm === 'selesai') {
            $alasanVal = '';
        }
        $rows[] = [
            'nama_opd' => $nama,
            'status' => $statusNorm,
            'alasan' => $alasanVal,
        ];
    }

    return $rows;
}
