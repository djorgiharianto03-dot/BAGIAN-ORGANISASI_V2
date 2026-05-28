<?php

declare(strict_types=1);

/**
 * Tombstone (nisan) personel — daftar id/slug yang sudah pernah dihapus dan
 * TIDAK boleh muncul lagi di sumber data manapun.
 *
 * FILE: `personnel_tombstone.json` di root proyek (sejajar personnel.json).
 * Legacy: `storage/personnel_tombstone.json` masih dibaca sekali untuk migrasi.
 */

if (!function_exists('org_personnel_tombstone_last_error')) {
    function org_personnel_tombstone_last_error(): ?string
    {
        return $GLOBALS['org_personnel_tombstone_last_error'] ?? null;
    }
}

if (!function_exists('org_personnel_tombstone_set_error')) {
    function org_personnel_tombstone_set_error(?string $message): void
    {
        $GLOBALS['org_personnel_tombstone_last_error'] = $message;
    }
}

if (!function_exists('org_personnel_tombstone_root')) {
    function org_personnel_tombstone_root(): string
    {
        return defined('ORG_ROOT') ? (string) ORG_ROOT : dirname(__DIR__);
    }
}

if (!function_exists('org_personnel_tombstone_file_path')) {
    function org_personnel_tombstone_file_path(): string
    {
        return org_personnel_tombstone_root()
            . DIRECTORY_SEPARATOR
            . 'personnel_tombstone.json';
    }
}

if (!function_exists('org_personnel_tombstone_legacy_file_path')) {
    function org_personnel_tombstone_legacy_file_path(): string
    {
        return org_personnel_tombstone_root()
            . DIRECTORY_SEPARATOR
            . 'storage'
            . DIRECTORY_SEPARATOR
            . 'personnel_tombstone.json';
    }
}

if (!function_exists('org_personnel_tombstone_migrate_legacy')) {
    function org_personnel_tombstone_migrate_legacy(): void
    {
        $path = org_personnel_tombstone_file_path();
        if (is_file($path)) {
            return;
        }
        $legacy = org_personnel_tombstone_legacy_file_path();
        if (!is_file($legacy)) {
            return;
        }
        $raw = @file_get_contents($legacy);
        if ($raw === false || $raw === '') {
            return;
        }
        @file_put_contents($path, $raw, LOCK_EX);
    }
}

if (!function_exists('org_personnel_tombstone_load')) {
    /**
     * @return list<array{id:string, slug:string, name:string, deleted_at:string}>
     */
    function org_personnel_tombstone_load(): array
    {
        org_personnel_tombstone_migrate_legacy();

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

if (!function_exists('org_personnel_tombstone_normalize_rows')) {
    /**
     * @param list<array<string,mixed>> $rows
     * @return list<array{id:string, slug:string, name:string, deleted_at:string}>
     */
    function org_personnel_tombstone_normalize_rows(array $rows): array
    {
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

        return $clean;
    }
}

if (!function_exists('org_personnel_tombstone_save')) {
    /**
     * @param list<array<string,mixed>> $rows
     */
    function org_personnel_tombstone_save(array $rows): bool
    {
        org_personnel_tombstone_set_error(null);

        $path = org_personnel_tombstone_file_path();
        $dir = dirname($path);
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            org_personnel_tombstone_set_error('cannot_create_dir:' . $dir);

            return false;
        }

        $clean = org_personnel_tombstone_normalize_rows($rows);
        $json = json_encode($clean, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            org_personnel_tombstone_set_error('json_encode_failed');

            return false;
        }

        $expectedKeys = [];
        foreach ($clean as $row) {
            $id = (string) ($row['id'] ?? '');
            $slug = strtolower((string) ($row['slug'] ?? ''));
            $expectedKeys[] = $id !== '' ? 'id:' . $id : 'slug:' . $slug;
        }
        sort($expectedKeys);

        $atomicWrite = static function (string $target, string $data): bool {
            $fp = @fopen($target, 'c+b');
            if ($fp === false) {
                $fp = @fopen($target, 'wb');
            }
            if ($fp === false) {
                return false;
            }
            $ok = false;
            if (@flock($fp, LOCK_EX)) {
                @ftruncate($fp, 0);
                @rewind($fp);
                $written = @fwrite($fp, $data);
                if ($written !== false && $written === strlen($data)) {
                    @fflush($fp);
                    $ok = true;
                }
                @flock($fp, LOCK_UN);
            }
            @fclose($fp);

            return $ok;
        };

        $writeOk = false;
        $tmp = $path . '.tmp.' . bin2hex(random_bytes(4));
        if ($atomicWrite($tmp, $json)) {
            clearstatcache(true, $tmp);
            if (@rename($tmp, $path)) {
                $writeOk = true;
            } else {
                @unlink($tmp);
                for ($attempt = 0; $attempt < 3 && !$writeOk; $attempt++) {
                    if ($atomicWrite($path, $json)) {
                        $writeOk = true;
                        break;
                    }
                    usleep(50000);
                }
            }
        } else {
            @unlink($tmp);
            for ($attempt = 0; $attempt < 3 && !$writeOk; $attempt++) {
                if ($atomicWrite($path, $json)) {
                    $writeOk = true;
                    break;
                }
                usleep(50000);
            }
        }

        if (!$writeOk) {
            $fallback = @file_put_contents($path, $json, LOCK_EX);
            $writeOk = ($fallback !== false && $fallback === strlen($json));
        }

        if (!$writeOk) {
            org_personnel_tombstone_set_error('write_failed path=' . $path
                . ' dir_writable=' . (is_writable($dir) ? '1' : '0')
                . ' file_writable=' . (is_file($path) && is_writable($path) ? '1' : (is_writable($dir) ? '1' : '0')));

            return false;
        }

        clearstatcache(true, $path);

        $verifyRaw = @file_get_contents($path);
        if ($verifyRaw === false || $verifyRaw === '') {
            org_personnel_tombstone_set_error('verify_read_empty');

            return false;
        }
        $verifyData = json_decode($verifyRaw, true);
        if (!is_array($verifyData)) {
            org_personnel_tombstone_set_error('verify_json_invalid');

            return false;
        }

        $verifyKeys = [];
        foreach ($verifyData as $verifyRow) {
            if (!is_array($verifyRow)) {
                continue;
            }
            $id = trim((string) ($verifyRow['id'] ?? ''));
            $slug = strtolower(trim((string) ($verifyRow['slug'] ?? '')));
            if ($id === '' && $slug === '') {
                continue;
            }
            $verifyKeys[] = $id !== '' ? 'id:' . $id : 'slug:' . $slug;
        }
        sort($verifyKeys);
        if ($verifyKeys !== $expectedKeys) {
            org_personnel_tombstone_set_error('verify_mismatch expected=' . count($expectedKeys) . ' got=' . count($verifyKeys));

            return false;
        }

        return true;
    }
}

if (!function_exists('org_personnel_tombstone_add')) {
    function org_personnel_tombstone_add(string $id, string $slug, string $name): bool
    {
        $id   = trim($id);
        $slug = strtolower(trim($slug));
        $name = trim($name);
        if ($id === '' && $slug === '') {
            org_personnel_tombstone_set_error('empty_id_and_slug');

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
            return true;
        }

        return org_personnel_tombstone_save($kept);
    }
}

if (!function_exists('org_personnel_tombstone_filter')) {
    /**
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
