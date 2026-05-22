<?php

if (!function_exists('org_theme_hari_besar_class')) {
    /**
     * @return array{class: string, label: string, icon: string, ucapan: string, badge: string}
     */
    function org_theme_hari_besar_empty_meta(): array
    {
        return ['class' => '', 'label' => '', 'icon' => '', 'ucapan' => '', 'badge' => ''];
    }

    /** Aktif/nonaktif via config/holiday_themes_enable.php (default: aktif). */
    function org_theme_hari_besar_enabled(): bool
    {
        static $enabled = null;
        if ($enabled !== null) {
            return $enabled;
        }
        $flagFile = ORG_ROOT . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'holiday_themes_enable.php';
        if (is_file($flagFile)) {
            $flag = require $flagFile;
            $enabled = ($flag !== false);
        } else {
            $enabled = true;
        }

        return $enabled;
    }

    /**
     * Daftar kalender tema (MD = berulang tiap tahun, YMD = spesifik tanggal).
     *
     * @return list<array{key: string, class: string, label: string, icon: string}>
     */
    function org_theme_kalender_list(): array
    {
        $cfgFile = ORG_ROOT . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'holiday_themes.php';
        if (!is_file($cfgFile)) {
            return [];
        }
        $loaded = require $cfgFile;
        if (!is_array($loaded)) {
            return [];
        }
        $out = [];
        foreach ($loaded as $row) {
            if (!is_array($row)) {
                continue;
            }
            $key = trim((string) ($row['key'] ?? ''));
            $class = trim((string) ($row['class'] ?? ''));
            $label = trim((string) ($row['label'] ?? ''));
            $icon = trim((string) ($row['icon'] ?? ''));
            $ucapan = trim((string) ($row['ucapan'] ?? ''));
            $badge = trim((string) ($row['badge'] ?? ''));
            $durationDays = (int) ($row['duration_days'] ?? 1);
            if ($durationDays < 1) {
                $durationDays = 1;
            }
            if ($key === '' || $class === '') {
                continue;
            }
            $out[] = [
                'key' => $key,
                'class' => $class,
                'label' => $label,
                'icon' => $icon,
                'ucapan' => $ucapan,
                'badge' => $badge,
                'duration_days' => $durationDays,
            ];
        }

        return $out;
    }

    /**
     * @param array{key: string, duration_days?: int} $item
     */
    function org_theme_item_in_active_range(array $item, DateTimeInterface $date): bool
    {
        $key = trim((string) ($item['key'] ?? ''));
        if ($key === '') {
            return false;
        }
        $durationDays = (int) ($item['duration_days'] ?? 1);
        if ($durationDays < 1) {
            $durationDays = 1;
        }
        $check = DateTimeImmutable::createFromInterface($date)->setTime(0, 0, 0);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $key) === 1) {
            $start = DateTimeImmutable::createFromFormat('Y-m-d', $key);
            if (!$start instanceof DateTimeImmutable) {
                return false;
            }
            $start = $start->setTime(0, 0, 0);
            $end = $start->modify('+' . ($durationDays - 1) . ' days');

            return $check >= $start && $check <= $end;
        }

        if (preg_match('/^\d{2}-\d{2}$/', $key) === 1) {
            $year = (int) $check->format('Y');
            $start = DateTimeImmutable::createFromFormat('Y-m-d', $year . '-' . $key);
            if (!$start instanceof DateTimeImmutable) {
                return false;
            }
            $start = $start->setTime(0, 0, 0);
            if ($durationDays === 1) {
                return $check->format('m-d') === $key;
            }
            $end = $start->modify('+' . ($durationDays - 1) . ' days');

            return $check >= $start && $check <= $end;
        }

        return false;
    }

    /**
     * @param array{class: string, label: string, icon: string, ucapan: string, badge: string} $item
     * @return array{class: string, label: string, icon: string, ucapan: string, badge: string}
     */
    function org_theme_item_to_meta(array $item): array
    {
        return [
            'class' => (string) ($item['class'] ?? ''),
            'label' => (string) ($item['label'] ?? ''),
            'icon' => (string) ($item['icon'] ?? ''),
            'ucapan' => (string) ($item['ucapan'] ?? ''),
            'badge' => (string) ($item['badge'] ?? ''),
        ];
    }

    function org_theme_preview_allowed(): bool
    {
        if (!empty($_SESSION['is_admin'])) {
            return true;
        }
        $remote = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        if ($remote === '127.0.0.1' || $remote === '::1') {
            return true;
        }
        $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
        if ($host === 'localhost' || strpos($host, '127.0.0.1') === 0) {
            return true;
        }
        if (strpos($host, 'localhost:') === 0) {
            return true;
        }
        $localSuffixes = ['.test', '.local', '.localhost', '.dev', '.invalid'];
        foreach ($localSuffixes as $suffix) {
            $len = strlen($suffix);
            if ($len > 0 && strlen($host) > $len && substr($host, -$len) === $suffix) {
                return true;
            }
        }
        $root = strtolower(str_replace('\\', '/', (string) ORG_ROOT));
        if (strpos($root, '/laragon/') !== false || strpos($root, '/xampp/') !== false) {
            return true;
        }
        $flagFile = ORG_ROOT . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'theme_preview_enable.php';
        if (is_file($flagFile)) {
            $flag = require $flagFile;
            if ($flag === true) {
                return true;
            }
        }

        return false;
    }

    function org_theme_preview_query_suffix(): string
    {
        $raw = trim((string) ($_GET['theme_preview'] ?? ''));
        if ($raw === '') {
            return '';
        }
        $slug = preg_replace('/[^a-z0-9_-]/', '', strtolower($raw)) ?? '';
        if ($slug === '') {
            return '';
        }

        return '?theme_preview=' . rawurlencode($slug);
    }

    /**
     * Pratinjau tema via ?theme_preview=idul-fitri (localhost / admin saja).
     *
     * @return array{class: string, label: string, icon: string, ucapan: string, badge: string}
     */
    function org_theme_preview_meta(): array
    {
        $empty = org_theme_hari_besar_empty_meta();
        if (!org_theme_hari_besar_enabled()) {
            return $empty;
        }
        if (!org_theme_preview_allowed()) {
            return $empty;
        }
        $raw = strtolower(trim((string) ($_GET['theme_preview'] ?? '')));
        if ($raw === '') {
            return $empty;
        }
        $slug = preg_replace('/[^a-z0-9_-]/', '', $raw) ?? '';
        if ($slug === '') {
            return $empty;
        }
        foreach (org_theme_kalender_list() as $item) {
            $class = (string) ($item['class'] ?? '');
            if ($class === '') {
                continue;
            }
            $classSlug = preg_replace('/^theme-/', '', $class) ?? '';
            if ($slug === $class || $slug === $classSlug) {
                $label = trim((string) ($item['label'] ?? ''));
                if ($label === '') {
                    $label = 'Pratinjau tema';
                } else {
                    $label .= ' (pratinjau)';
                }

                return [
                    'class' => $class,
                    'label' => $label,
                    'icon' => (string) ($item['icon'] ?? ''),
                    'ucapan' => (string) ($item['ucapan'] ?? ''),
                    'badge' => (string) ($item['badge'] ?? ''),
                ];
            }
        }

        return $empty;
    }

    /**
     * @return array{class: string, label: string, icon: string, ucapan: string, badge: string}
     */
    function org_theme_hari_besar_meta(?DateTimeInterface $date = null): array
    {
        if (!org_theme_hari_besar_enabled()) {
            return org_theme_hari_besar_empty_meta();
        }

        $preview = org_theme_preview_meta();
        if (($preview['class'] ?? '') !== '') {
            return $preview;
        }

        $d = $date ?? new DateTimeImmutable('now');
        $picked = org_theme_hari_besar_empty_meta();

        // Hari keagamaan / periode panjang (mis. 7 hari) â€” prioritas lebih tinggi
        foreach (org_theme_kalender_list() as $item) {
            $durationDays = (int) ($item['duration_days'] ?? 1);
            if ($durationDays <= 1) {
                continue;
            }
            if (org_theme_item_in_active_range($item, $d)) {
                return org_theme_item_to_meta($item);
            }
        }

        // Hari nasional: satu hari (tanggal pasti Y-m-d atau berulang m-d)
        foreach (org_theme_kalender_list() as $item) {
            if ((int) ($item['duration_days'] ?? 1) > 1) {
                continue;
            }
            if (!org_theme_item_in_active_range($item, $d)) {
                continue;
            }
            $key = (string) ($item['key'] ?? '');
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $key) === 1) {
                return org_theme_item_to_meta($item);
            }
            if ($picked['class'] === '' && preg_match('/^\d{2}-\d{2}$/', $key) === 1) {
                $picked = org_theme_item_to_meta($item);
            }
        }

        return $picked;
    }

    /**
     * Kelas tema musiman berdasarkan tanggal hari ini.
     */
    function org_theme_hari_besar_class(?DateTimeInterface $date = null): string
    {
        $meta = org_theme_hari_besar_meta($date);
        return (string) ($meta['class'] ?? '');
    }
}
