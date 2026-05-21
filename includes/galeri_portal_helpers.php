<?php
declare(strict_types=1);

if (!function_exists('org_galeri_portal_year_categories')) {
    /**
     * @param list<array<string, mixed>> $items
     * @return array<string, string> slug => label
     */
    function org_galeri_portal_year_categories(array $items): array
    {
        $years = [];
        foreach ($items as $row) {
            if (!is_array($row)) {
                continue;
            }
            $raw = (string) ($row['tgl_upload'] ?? '');
            if ($raw === '') {
                continue;
            }
            $ts = strtotime($raw);
            if ($ts !== false) {
                $years[date('Y', $ts)] = true;
            }
        }
        krsort($years, SORT_STRING);
        $out = ['all' => 'Semua'];
        foreach (array_keys($years) as $year) {
            $yearKey = (string) $year;
            $out[$yearKey] = $yearKey;
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $item
     */
    function org_galeri_portal_item_year_slug(array $item): string
    {
        $raw = (string) ($item['tgl_upload'] ?? '');
        if ($raw === '') {
            return 'lainnya';
        }
        $ts = strtotime($raw);
        if ($ts === false) {
            return 'lainnya';
        }

        return (string) date('Y', $ts);
    }

    /**
     * @param array<string, mixed> $item
     */
    function org_galeri_portal_item_size_class(int $index): string
    {
        $mods = ['', ' gl-item--tall', ' gl-item--wide', ''];

        return $mods[$index % 4] ?? '';
    }
}
