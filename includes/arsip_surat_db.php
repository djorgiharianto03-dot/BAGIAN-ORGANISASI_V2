<?php

require_once __DIR__ . '/arsip_kategori_bagian.php';

/**
 * Kolom tabel arsip_surat (cache per request).
 *
 * @return array<string, true>
 */
function org_arsip_surat_column_set(mysqli $db): array
{
    static $cache = null;
    if (is_array($cache)) {
        return $cache;
    }
    $cache = [];
    $res = $db->query('SHOW COLUMNS FROM `arsip_surat`');
    if ($res) {
        while ($c = $res->fetch_assoc()) {
            $f = strtolower(trim((string) ($c['Field'] ?? '')));
            if ($f !== '') {
                $cache[$f] = true;
            }
        }
        $res->free();
    }

    return $cache;
}

function org_arsip_surat_table_exists(mysqli $db): bool
{
    $r = $db->query("SHOW TABLES LIKE 'arsip_surat'");
    if ($r === false) {
        return false;
    }
    $ok = $r->num_rows > 0;
    $r->free();

    return $ok;
}

/**
 * Apakah baris meta disinkronkan ke tabel `arsip_surat` (Monitoring Disposisi).
 * Surat keluar selalu ikut. Surat masuk mengikuti kunci `ikut_monitoring_disposisi`;
 * bila kunci tidak ada di meta lama, dianggap ikut (kompatibel mundur).
 *
 * @param array<string, mixed> $metaRow
 */
function org_arsip_meta_row_syncs_to_monitoring_table(string $jenis, array $metaRow): bool
{
    $jenis = strtolower(trim($jenis)) === 'keluar' ? 'keluar' : 'masuk';
    if ($jenis === 'keluar') {
        return true;
    }
    if (!array_key_exists('ikut_monitoring_disposisi', $metaRow)) {
        return true;
    }
    $v = $metaRow['ikut_monitoring_disposisi'];
    if (is_bool($v)) {
        return $v;
    }
    if (is_int($v) || is_float($v)) {
        return ((int) $v) !== 0;
    }
    $s = strtolower(trim((string) $v));

    return in_array($s, ['1', 'true', 'yes', 'on'], true);
}

/**
 * Nama kolom berkas pada tabel arsip_surat (bervariasi antar instalasi).
 *
 * @param array<string, true> $cols
 */
function org_arsip_surat_file_column_name(array $cols): string
{
    if (isset($cols['nama_file'])) {
        return 'nama_file';
    }
    if (isset($cols['file_surat'])) {
        return 'file_surat';
    }
    if (isset($cols['file_pdf'])) {
        return 'file_pdf';
    }

    return '';
}

/**
 * Nama berkas untuk tampilan / path relatif dari satu baris arsip_surat.
 *
 * @param array<string, mixed> $row
 */
function org_arsip_surat_row_display_filename(array $row): string
{
    $raw = trim((string) ($row['nama_file'] ?? $row['file_surat'] ?? $row['file_pdf'] ?? ''));
    if ($raw === '') {
        return '';
    }

    return basename(str_replace('\\', '/', $raw));
}

/**
 * Path relatif web ke PDF arsip (folder uploads/surat_masuk atau uploads/surat_keluar).
 * Mengembalikan null jika nama berkas tidak ada.
 *
 * @param array<string, mixed> $row
 */
function org_arsip_surat_row_pdf_web_path(array $row): ?string
{
    $fn = org_arsip_surat_row_display_filename($row);
    if ($fn === '') {
        return null;
    }
    $jenis = strtolower(trim((string) ($row['jenis_surat'] ?? $row['jenis'] ?? $row['tipe'] ?? 'masuk')));
    $sub = $jenis === 'keluar' ? 'surat_keluar' : 'surat_masuk';

    return 'uploads/' . $sub . '/' . rawurlencode($fn);
}

/**
 * Cari ID baris arsip_surat berdasarkan jenis + nama berkas.
 */
function org_arsip_surat_find_id_by_file(mysqli $db, string $jenis, string $namaFile): int
{
    $cols = org_arsip_surat_column_set($db);
    $jenis = strtolower($jenis) === 'keluar' ? 'keluar' : 'masuk';
    $namaFile = basename($namaFile);
    if ($namaFile === '') {
        return 0;
    }
    $fileCol = org_arsip_surat_file_column_name($cols);
    if ($fileCol === '') {
        return 0;
    }
    $jenisCol = isset($cols['jenis_surat']) ? 'jenis_surat' : (isset($cols['jenis']) ? 'jenis' : (isset($cols['tipe']) ? 'tipe' : ''));

    if ($jenisCol !== '') {
        $sql = 'SELECT `id` FROM `arsip_surat` WHERE LOWER(TRIM(`' . $fileCol . '`)) = LOWER(?) AND LOWER(TRIM(COALESCE(`' . $jenisCol . "`, ''))) = LOWER(?) LIMIT 1";
        $st = $db->prepare($sql);
        if ($st === false) {
            return 0;
        }
        $st->bind_param('ss', $namaFile, $jenis);
    } else {
        $sql = 'SELECT `id` FROM `arsip_surat` WHERE LOWER(TRIM(`' . $fileCol . '`)) = LOWER(?) LIMIT 1';
        $st = $db->prepare($sql);
        if ($st === false) {
            return 0;
        }
        $st->bind_param('s', $namaFile);
    }
    $st->execute();
    $rs = $st->get_result();
    $row = $rs ? $rs->fetch_assoc() : null;
    $st->close();

    return is_array($row) ? (int) ($row['id'] ?? 0) : 0;
}

/**
 * Sisipkan satu baris arsip dari metadata unggahan (modul Arsip JSON).
 *
 * @param array<string, mixed> $metaRow
 */
function org_arsip_surat_insert_from_meta(mysqli $db, string $jenis, string $namaFile, array $metaRow): ?int
{
    $cols = org_arsip_surat_column_set($db);
    if ($cols === []) {
        return null;
    }
    $jenis = strtolower($jenis) === 'keluar' ? 'keluar' : 'masuk';
    $namaFile = basename($namaFile);
    $nomor = trim((string) ($metaRow['nomor_surat'] ?? ''));
    $perihal = trim((string) ($metaRow['perihal_ringkasan'] ?? ''));
    $asal = trim((string) ($metaRow['instansi_asal'] ?? ''));
    $tujuan = trim((string) ($metaRow['instansi_tujuan'] ?? ''));
    $kb = trim((string) ($metaRow['kategori_bagian'] ?? ''));

    $fields = [];
    $ph = [];
    $types = '';
    $params = [];

    if (isset($cols['jenis_surat'])) {
        $fields[] = '`jenis_surat`';
        $ph[] = '?';
        $types .= 's';
        $params[] = $jenis;
    } elseif (isset($cols['jenis'])) {
        $fields[] = '`jenis`';
        $ph[] = '?';
        $types .= 's';
        $params[] = $jenis;
    } elseif (isset($cols['tipe'])) {
        $fields[] = '`tipe`';
        $ph[] = '?';
        $types .= 's';
        $params[] = $jenis;
    }

    if (isset($cols['nama_file'])) {
        $fields[] = '`nama_file`';
        $ph[] = '?';
        $types .= 's';
        $params[] = $namaFile;
    } elseif (isset($cols['file_surat'])) {
        $fields[] = '`file_surat`';
        $ph[] = '?';
        $types .= 's';
        $params[] = $namaFile;
    } elseif (isset($cols['file_pdf'])) {
        $fields[] = '`file_pdf`';
        $ph[] = '?';
        $types .= 's';
        $params[] = $namaFile;
    } else {
        return null;
    }

    if (isset($cols['nomor_surat'])) {
        $fields[] = '`nomor_surat`';
        $ph[] = '?';
        $types .= 's';
        $params[] = $nomor;
    } elseif (isset($cols['nomor'])) {
        $fields[] = '`nomor`';
        $ph[] = '?';
        $types .= 's';
        $params[] = $nomor;
    } else {
        return null;
    }

    if (isset($cols['perihal_ringkasan'])) {
        $fields[] = '`perihal_ringkasan`';
        $ph[] = '?';
        $types .= 's';
        $params[] = $perihal;
    } elseif (isset($cols['perihal'])) {
        $fields[] = '`perihal`';
        $ph[] = '?';
        $types .= 's';
        $params[] = $perihal;
    }

    if (isset($cols['instansi_asal'])) {
        $fields[] = '`instansi_asal`';
        $ph[] = '?';
        $types .= 's';
        $params[] = $asal;
    } elseif (isset($cols['asal_surat'])) {
        $fields[] = '`asal_surat`';
        $ph[] = '?';
        $types .= 's';
        $params[] = $asal;
    }
    if (isset($cols['instansi_tujuan'])) {
        $fields[] = '`instansi_tujuan`';
        $ph[] = '?';
        $types .= 's';
        $params[] = $tujuan;
    } elseif (isset($cols['tujuan_surat'])) {
        $fields[] = '`tujuan_surat`';
        $ph[] = '?';
        $types .= 's';
        $params[] = $tujuan;
    }

    if ($kb !== '' && isset($cols['kategori_bagian'])) {
        $allowed = org_arsip_kategori_bagian_map();
        if (isset($allowed[$kb])) {
            $fields[] = '`kategori_bagian`';
            $ph[] = '?';
            $types .= 's';
            $params[] = $kb;
        }
    }

    if ($fields === []) {
        return null;
    }

    $sql = 'INSERT INTO `arsip_surat` (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $ph) . ')';
    $st = $db->prepare($sql);
    if ($st === false) {
        return null;
    }
    $st->bind_param($types, ...$params);
    if (!$st->execute()) {
        $st->close();

        return null;
    }
    $newId = (int) $st->insert_id;
    $st->close();

    return $newId > 0 ? $newId : null;
}

/**
 * Hapus baris arsip_surat yang cocok dengan unggahan JSON (file + jenis).
 */
function org_arsip_surat_delete_by_file(mysqli $db, string $jenis, string $namaFile, ?int $knownId = null): void
{
    if ($knownId !== null && $knownId > 0) {
        $st = $db->prepare('DELETE FROM `arsip_surat` WHERE `id` = ? LIMIT 1');
        if ($st !== false) {
            $st->bind_param('i', $knownId);
            $st->execute();
            $st->close();
        }

        return;
    }
    $id = org_arsip_surat_find_id_by_file($db, $jenis, $namaFile);
    if ($id > 0) {
        org_arsip_surat_delete_by_file($db, $jenis, $namaFile, $id);
    }
}

/**
 * Sinkronkan isi arsip_surat_meta.json + berkas PDF ke tabel arsip_surat (baris yang belum ada).
 *
 * @param array<string, string> $dirMap jenis => path folder uploads
 * @return int jumlah baris baru yang disisipkan
 */
function org_arsip_sync_meta_to_arsip_surat_table(mysqli $db, string $metaFile, array $dirMap, int $maxInserts = 200): int
{
    if (!org_arsip_surat_table_exists($db)) {
        return 0;
    }
    if (!is_file($metaFile)) {
        return 0;
    }
    $raw = @file_get_contents($metaFile);
    if ($raw === false || $raw === '') {
        return 0;
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return 0;
    }
    $inserted = 0;
    foreach ($decoded as $metaKey => $metaRow) {
        if ($inserted >= $maxInserts) {
            break;
        }
        if (!is_string($metaKey) || !is_array($metaRow)) {
            continue;
        }
        $parts = explode('|', $metaKey, 2);
        $jenis = strtolower(trim((string) ($parts[0] ?? '')));
        $fn = isset($parts[1]) ? basename((string) $parts[1]) : '';
        if (!in_array($jenis, ['masuk', 'keluar'], true) || $fn === '' || !str_ends_with(strtolower($fn), '.pdf')) {
            continue;
        }
        $dir = $dirMap[$jenis] ?? '';
        if ($dir === '' || !is_file($dir . DIRECTORY_SEPARATOR . $fn)) {
            continue;
        }
        if ($jenis === 'masuk' && !org_arsip_meta_row_syncs_to_monitoring_table('masuk', $metaRow)) {
            continue;
        }
        if (org_arsip_surat_find_id_by_file($db, $jenis, $fn) > 0) {
            continue;
        }
        $id = org_arsip_surat_insert_from_meta($db, $jenis, $fn, $metaRow);
        if ($id !== null) {
            $inserted++;
        }
    }

    return $inserted;
}
