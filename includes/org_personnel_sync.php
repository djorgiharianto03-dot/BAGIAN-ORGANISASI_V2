<?php

/**
 * Muat / sinkronkan registry personel dari personnel.json + foto_struktur.
 */

function org_personnel_photo_web_url(string $storageFileName): string
{
    if ($storageFileName === '') {
        return '';
    }
    $path = 'uploads/foto_struktur/' . rawurlencode($storageFileName);
    $root = defined('ORG_WEB_ROOT') ? rtrim((string) ORG_WEB_ROOT, '/') : '';

    return ($root !== '' ? $root . '/' : '') . $path;
}

/**
 * @param callable(string): string $slugify
 * @param callable(string, array): bool $savePersonnelData
 */
function org_personnel_sync_from_disk(
    string $personnelFile,
    string $fotoStrukturDir,
    callable $slugify,
    callable $savePersonnelData
): array {
    clearstatcache(true, $personnelFile);
    $personnelData = [];
    $raw = is_file($personnelFile) ? @file_get_contents($personnelFile) : false;
    if ($raw !== false && $raw !== '') {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $personnelData = $decoded;
        }
    }

    $defaultProfileImage = "data:image/svg+xml;utf8," . rawurlencode(
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 320">'
        . '<rect width="400" height="320" fill="#e5edf8"/>'
        . '<circle cx="200" cy="125" r="55" fill="#9db4d5"/>'
        . '<rect x="95" y="195" width="210" height="85" rx="42" fill="#9db4d5"/>'
        . '</svg>'
    );

    $needsSave = false;
    foreach ($personnelData as $idx => $person) {
        if (!is_array($person)) {
            unset($personnelData[$idx]);
            $needsSave = true;
            continue;
        }
        $slug = $slugify((string) ($person['name'] ?? ''));
        if (!isset($person['id']) || (string) $person['id'] === '') {
            $personnelData[$idx]['id'] = uniqid('staff_', true);
            $needsSave = true;
        } else {
            $personnelData[$idx]['id'] = (string) $person['id'];
        }
        $personnelData[$idx]['slug'] = $slug;
        if (!array_key_exists('nip', $personnelData[$idx])) {
            $personnelData[$idx]['nip'] = '';
            $needsSave = true;
        } else {
            $personnelData[$idx]['nip'] = substr(
                preg_replace('/\s+/u', '', trim((string) $personnelData[$idx]['nip'])),
                0,
                20
            );
        }
        $availablePhoto = '';
        foreach (['png', 'jpg', 'jpeg'] as $ext) {
            $candidateFile = $slug . '.' . $ext;
            if (is_file($fotoStrukturDir . DIRECTORY_SEPARATOR . $candidateFile)) {
                $availablePhoto = $candidateFile;
                break;
            }
        }
        $personnelData[$idx]['photo'] = $availablePhoto !== ''
            ? org_personnel_photo_web_url($availablePhoto)
            : $defaultProfileImage;
    }

    $personnelData = array_values($personnelData);
    if ($needsSave) {
        $savePersonnelData($personnelFile, $personnelData);
    }

    return [
        'data' => $personnelData,
        'ids' => array_column($personnelData, 'id'),
        'slugs' => array_column($personnelData, 'slug'),
    ];
}

/**
 * Admin / super admin / Kabag boleh kelola personel di halaman profil.
 */
function org_personnel_can_manage(): bool
{
    if (empty($_SESSION['is_admin'])) {
        return false;
    }
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'staff_users_db.php';
    $role = org_staff_role_normalize((string) ($_SESSION['level'] ?? $_SESSION['admin_role'] ?? ''));
    if ($role === '' && trim((string) ($_SESSION['admin_username'] ?? '')) !== '') {
        return true;
    }
    if (in_array($role, ['super_admin', 'admin', 'kabag_organisasi'], true)) {
        return true;
    }

    return org_staff_session_is_kabag();
}

/**
 * @param array<string, mixed> $person
 * @return array{id: string, name: string, nip: string, position: string}
 */
function org_personnel_row_for_storage(array $person): array
{
    return [
        'id' => (string) ($person['id'] ?? ''),
        'name' => trim((string) ($person['name'] ?? '')),
        'nip' => substr(preg_replace('/\s+/u', '', trim((string) ($person['nip'] ?? ''))), 0, 20),
        'position' => trim((string) ($person['position'] ?? '')),
    ];
}

/**
 * Tulis personnel.json (hanya field inti; foto/slug dihitung ulang saat muat).
 *
 * Strategi tulis (tahan banting di Windows/Laragon):
 *   1. Tulis ke berkas .tmp di folder yang sama (LOCK_EX) untuk hindari tulis
 *      sebagian.
 *   2. Rename atomik dari .tmp → personnel.json. Pada Windows kadang gagal
 *      jika file tujuan dipegang proses lain (antivirus / file watcher).
 *   3. Fallback langsung: file_put_contents() ke target dengan retry singkat.
 *   4. Verifikasi: baca-ulang file, decode JSON, pastikan jumlah baris sama.
 *      Jika verifikasi gagal, return false agar pemanggil tidak salah klaim
 *      sukses.
 *   5. clearstatcache() supaya pembacaan berikutnya tidak melihat stat lama.
 *
 * @param list<array<string, mixed>> $items
 */
function org_personnel_write_file(string $personnelFilePath, array $items): bool
{
    $rows = [];
    foreach ($items as $person) {
        if (!is_array($person)) {
            continue;
        }
        $row = org_personnel_row_for_storage($person);
        if ($row['id'] === '' || $row['name'] === '' || $row['position'] === '') {
            continue;
        }
        $rows[] = $row;
    }
    $rows = array_values($rows);

    $json = json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        return false;
    }

    $dir = dirname($personnelFilePath);
    if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
        return false;
    }

    $expectedCount = count($rows);
    $expectedIds = array_column($rows, 'id');
    sort($expectedIds);

    $writeOk = false;
    $tmp = $personnelFilePath . '.tmp.' . bin2hex(random_bytes(4));
    if (@file_put_contents($tmp, $json, LOCK_EX) !== false) {
        clearstatcache(true, $tmp);
        if (@rename($tmp, $personnelFilePath)) {
            $writeOk = true;
        } else {
            @unlink($tmp);
            for ($attempt = 0; $attempt < 3 && !$writeOk; $attempt++) {
                if (@file_put_contents($personnelFilePath, $json, LOCK_EX) !== false) {
                    $writeOk = true;
                    break;
                }
                usleep(50000);
            }
        }
    } else {
        @unlink($tmp);
        for ($attempt = 0; $attempt < 3 && !$writeOk; $attempt++) {
            if (@file_put_contents($personnelFilePath, $json, LOCK_EX) !== false) {
                $writeOk = true;
                break;
            }
            usleep(50000);
        }
    }

    if (!$writeOk) {
        return false;
    }

    clearstatcache(true, $personnelFilePath);

    /* Verifikasi: pastikan file di disk benar-benar mencerminkan data yang
       diminta. Tanpa verifikasi, kegagalan tulis di Windows kadang lolos
       (mis. file ditulis sebagian) sehingga delete terlihat sukses tetapi
       baris yang dihapus muncul kembali setelah refresh. */
    $verifyRaw = @file_get_contents($personnelFilePath);
    if ($verifyRaw === false || $verifyRaw === '') {
        return false;
    }
    $verifyData = json_decode($verifyRaw, true);
    if (!is_array($verifyData) || count($verifyData) !== $expectedCount) {
        return false;
    }
    $verifyIds = [];
    foreach ($verifyData as $verifyRow) {
        if (is_array($verifyRow)) {
            $verifyIds[] = (string) ($verifyRow['id'] ?? '');
        }
    }
    sort($verifyIds);
    if ($verifyIds !== $expectedIds) {
        return false;
    }

    return true;
}

/**
 * @param list<array<string, mixed>> $personnelData
 */
function org_personnel_find_index_by_slug(array $personnelData, string $slug): int|false
{
    if ($slug === '') {
        return false;
    }
    foreach ($personnelData as $idx => $person) {
        if (!is_array($person)) {
            continue;
        }
        if ((string) ($person['slug'] ?? '') === $slug) {
            return $idx;
        }
    }

    return false;
}

/**
 * @param list<array<string, mixed>> $personnelData
 */
function org_personnel_find_index_by_id(array $personnelData, string $personId): int|false
{
    if ($personId === '') {
        return false;
    }
    foreach ($personnelData as $idx => $person) {
        if (!is_array($person)) {
            continue;
        }
        if ((string) ($person['id'] ?? '') === $personId) {
            return $idx;
        }
    }

    return false;
}

/**
 * Cari baris personel berdasarkan id, lalu slug (cadangan).
 *
 * @param list<array<string, mixed>> $personnelData
 */
function org_personnel_find_index(array $personnelData, string $personId, string $personSlug = ''): int|false
{
    $rowIndex = org_personnel_find_index_by_id($personnelData, trim($personId));
    if ($rowIndex !== false) {
        return $rowIndex;
    }

    return org_personnel_find_index_by_slug($personnelData, trim($personSlug));
}

function org_personnel_delete_photo_files(string $fotoStrukturDir, string $slug): void
{
    if ($slug === '') {
        return;
    }
    foreach (['png', 'jpg', 'jpeg', 'webp'] as $ext) {
        $photoPath = $fotoStrukturDir . DIRECTORY_SEPARATOR . $slug . '.' . $ext;
        if (is_file($photoPath)) {
            @unlink($photoPath);
        }
    }
}

/** URL redirect aman setelah POST personel (query sebelum fragment). */
function org_personnel_post_redirect_url(string $page, string $hash = '', string $searchQuery = ''): string
{
    $url = $page;
    if ($searchQuery !== '') {
        $url .= '?q=' . rawurlencode($searchQuery);
    }
    if ($hash !== '') {
        $url .= '#' . ltrim($hash, '#');
    }

    return $url;
}
