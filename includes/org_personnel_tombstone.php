<?php

declare(strict_types=1);

/**
 * Tombstone (nisan) personel — daftar id/slug yang sudah pernah dihapus dan
 * TIDAK boleh muncul lagi di sumber data manapun.
 * -----------------------------------------------------------------------------
 * MENGAPA ADA?
 * Seed default (`$defaultPersonnelSeed` di bootstrap.php) berisi seluruh nama
 * tim. Kalau seed re-fire (race-condition `file_exists()` di Windows + AV)
 * atau ada proses lain yang mengembalikan personnel.json ke kondisi awal,
 * baris yang sudah dihapus akan "hidup kembali" — keluhan user yang berulang.
 *
 * SOLUSI: catat secara permanen siapa saja yang pernah dihapus.
 *   - delete_personnel  → push entry ke tombstone, lalu hapus dari JSON+DB
 *   - load (sync/seed)  → buang setiap entry yang ada di tombstone
 *   - add_personnel     → kalau admin sengaja menambahkan ulang nama/slug yang
 *     ada di tombstone, otomatis lepas dari tombstone (un-tombstone)
 *
 * FILE: `storage/personnel_tombstone.json`
 * STRUKTUR:
 *   [
 *     {
 *       "id": "staff_xxx",
 *       "slug": "djorgi-harianto-s-e",
 *       "name": "Djorgi Harianto, S.E",
 *       "deleted_at": "2026-05-27 10:15:00"
 *     },
 *     ...
 *   ]
 */

if (!function_exists('org_personnel_tombstone_file_path')) {
    function org_personnel_tombstone_file_path(): string
    {
        $root = defined('ORG_ROOT') ? (string) ORG_ROOT : dirname(__DIR__);
        return $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'personnel_tombstone.json';
    }
}

if (!function_exists('org_personnel_tombstone_load')) {
    /**
     * Baca isi tombstone. Selalu kembalikan array (boleh kosong).
     *
     * @return list<array{id:string, slug:string, name:string, deleted_at:string}>
     */
    function org_personnel_tombstone_load(): array
    {
        $path = org_personnel_tombstone_file_path();
        if (!is_file($path)) {
            return [];
        }
        $raw = @file_get_contents($path);
        if ($raw === false || $raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }
        $out = [];
        foreach ($decoded as $row) {
            if (!is_array($row)) {
                continue;
            }
            $out[] = [
                'id'         => (string) ($row['id'] ?? ''),
                'slug'       => (string) ($row['slug'] ?? ''),
                'name'       => (string) ($row['name'] ?? ''),
                'deleted_at' => (string) ($row['deleted_at'] ?? ''),
            ];
        }
        return $out;
    }
}

if (!function_exists('org_personnel_tombstone_save')) {
    /**
     * Tulis tombstone secara atomik. True = sukses verifikasi pasca-tulis.
     *
     * @param list<array<string,mixed>> $rows
     */
    function org_personnel_tombstone_save(array $rows): bool
    {
        $path = org_personnel_tombstone_file_path();
        $dir = dirname($path);
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return false;
        }

        $clean = [];
        $seen = [];
        foreach ($rows as $r) {
            if (!is_array($r)) {
                continue;
            }
            $id = trim((string) ($r['id'] ?? ''));
            $slug = strtolower(trim((string) ($r['slug'] ?? '')));
            if ($id === '' && $slug === '') {
                continue;
            }
            $key = $id !== '' ? 'id:' . $id : 'slug:' . $slug;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $clean[] = [
                'id'         => $id,
                'slug'       => $slug,
                'name'       => trim((string) ($r['name'] ?? '')),
                'deleted_at' => trim((string) ($r['deleted_at'] ?? date('Y-m-d H:i:s'))),
            ];
        }

        $json = json_encode($clean, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return false;
        }

        /* Atomic write: tmp → rename, dengan retry untuk lock Windows. */
        $tmp = $path . '.tmp.' . bin2hex(random_bytes(4));
        $fp = @fopen($tmp, 'cb');
        if ($fp === false) {
            return false;
        }
        $writeOk = false;
        if (@flock($fp, LOCK_EX)) {
            @ftruncate($fp, 0);
            @rewind($fp);
            $written = @fwrite($fp, $json);
            if ($written !== false && $written === strlen($json)) {
                @fflush($fp);
                $writeOk = true;
            }
            @flock($fp, LOCK_UN);
        }
        @fclose($fp);

        if (!$writeOk) {
            @unlink($tmp);
            return false;
        }

        clearstatcache(true, $tmp);
        if (!@rename($tmp, $path)) {
            @unlink($tmp);
            /* Fallback direct write dengan retry */
            $retries = 0;
            $directOk = false;
            while ($retries < 3 && !$directOk) {
                $fp2 = @fopen($path, 'cb');
                if ($fp2 !== false) {
                    if (@flock($fp2, LOCK_EX)) {
                        @ftruncate($fp2, 0);
                        @rewind($fp2);
                        $w = @fwrite($fp2, $json);
                        if ($w !== false && $w === strlen($json)) {
                            @fflush($fp2);
                            $directOk = true;
                        }
                        @flock($fp2, LOCK_UN);
                    }
                    @fclose($fp2);
                }
                if (!$directOk) {
                    usleep(50000);
                }
                $retries++;
            }
            if (!$directOk) {
                return false;
            }
        }

        clearstatcache(true, $path);

        /* Verifikasi pasca-tulis */
        $verifyRaw = @file_get_contents($path);
        if ($verifyRaw === false) {
            return false;
        }
        $verifyData = json_decode($verifyRaw, true);
        return is_array($verifyData) && count($verifyData) === count($clean);
    }
}

if (!function_exists('org_personnel_tombstone_add')) {
    /**
     * Tambahkan entry ke tombstone. Idempoten — kalau id/slug sudah ada,
     * cuma update `deleted_at` agar audit-trail jelas.
     */
    function org_personnel_tombstone_add(string $id, string $slug, string $name): bool
    {
        $id   = trim($id);
        $slug = strtolower(trim($slug));
        $name = trim($name);
        if ($id === '' && $slug === '') {
            return false;
        }
        $existing = org_personnel_tombstone_load();
        $found = false;
        $now = date('Y-m-d H:i:s');
        foreach ($existing as $i => $row) {
            $rowId = (string) ($row['id'] ?? '');
            $rowSlug = strtolower((string) ($row['slug'] ?? ''));
            if (($id !== '' && $rowId === $id) || ($slug !== '' && $rowSlug === $slug)) {
                $existing[$i]['id'] = $id !== '' ? $id : $rowId;
                $existing[$i]['slug'] = $slug !== '' ? $slug : $rowSlug;
                if ($name !== '') {
                    $existing[$i]['name'] = $name;
                }
                $existing[$i]['deleted_at'] = $now;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $existing[] = [
                'id' => $id,
                'slug' => $slug,
                'name' => $name,
                'deleted_at' => $now,
            ];
        }
        return org_personnel_tombstone_save($existing);
    }
}

if (!function_exists('org_personnel_tombstone_remove')) {
    /**
     * Lepas entry dari tombstone (un-tombstone). Dipanggil saat admin
     * dengan sengaja menambahkan ulang nama/slug yang sudah dihapus.
     */
    function org_personnel_tombstone_remove(string $id, string $slug): bool
    {
        $id   = trim($id);
        $slug = strtolower(trim($slug));
        if ($id === '' && $slug === '') {
            return false;
        }
        $existing = org_personnel_tombstone_load();
        $kept = [];
        $changed = false;
        foreach ($existing as $row) {
            $rowId   = (string) ($row['id'] ?? '');
            $rowSlug = strtolower((string) ($row['slug'] ?? ''));
            $match = (($id !== '' && $rowId === $id) || ($slug !== '' && $rowSlug === $slug));
            if ($match) {
                $changed = true;
                continue;
            }
            $kept[] = $row;
        }
        if (!$changed) {
            return true; /* tidak ada yang perlu disimpan ulang */
        }
        return org_personnel_tombstone_save($kept);
    }
}

if (!function_exists('org_personnel_tombstone_filter')) {
    /**
     * Buang dari $rows setiap entry yang id ATAU slug-nya cocok dengan
     * tombstone. Dipakai SETIAP KALI data personel dimuat dari sumber
     * apa pun (sync_from_disk, DB init, dll.).
     *
     * @param list<array<string,mixed>> $rows
     * @return list<array<string,mixed>>
     */
    function org_personnel_tombstone_filter(array $rows): array
    {
        $tomb = org_personnel_tombstone_load();
        if ($tomb === []) {
            return array_values($rows);
        }
        $bannedIds = [];
        $bannedSlugs = [];
        foreach ($tomb as $t) {
            $id = (string) ($t['id'] ?? '');
            $slug = strtolower((string) ($t['slug'] ?? ''));
            if ($id !== '') {
                $bannedIds[$id] = true;
            }
            if ($slug !== '') {
                $bannedSlugs[$slug] = true;
            }
        }
        $out = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $rid = (string) ($row['id'] ?? '');
            $rslug = strtolower((string) ($row['slug'] ?? ''));
            if ($rid !== '' && isset($bannedIds[$rid])) {
                continue;
            }
            if ($rslug !== '' && isset($bannedSlugs[$rslug])) {
                continue;
            }
            $out[] = $row;
        }
        return array_values($out);
    }
}

if (!function_exists('org_personnel_tombstone_filter_db_rows')) {
    /**
     * Versi untuk row DB (key: nama, jabatan, id). Slug dihitung dari nama.
     *
     * @param list<array<string,mixed>> $rows
     * @return list<array<string,mixed>>
     */
    function org_personnel_tombstone_filter_db_rows(array $rows, callable $slugify): array
    {
        $tomb = org_personnel_tombstone_load();
        if ($tomb === []) {
            return array_values($rows);
        }
        $bannedIds = [];
        $bannedSlugs = [];
        foreach ($tomb as $t) {
            $id = (string) ($t['id'] ?? '');
            $slug = strtolower((string) ($t['slug'] ?? ''));
            if ($id !== '') {
                $bannedIds[$id] = true;
            }
            if ($slug !== '') {
                $bannedSlugs[$slug] = true;
            }
        }
        $out = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $rid = (string) ($row['id'] ?? '');
            $name = (string) ($row['name'] ?? $row['nama'] ?? '');
            $rslug = strtolower((string) $slugify($name));
            if ($rid !== '' && isset($bannedIds[$rid])) {
                continue;
            }
            if ($rslug !== '' && isset($bannedSlugs[$rslug])) {
                continue;
            }
            $out[] = $row;
        }
        return array_values($out);
    }
}
