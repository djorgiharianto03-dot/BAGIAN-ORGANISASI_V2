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
    $personnelData = [];
    $raw = is_file($personnelFile) ? file_get_contents($personnelFile) : false;
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
