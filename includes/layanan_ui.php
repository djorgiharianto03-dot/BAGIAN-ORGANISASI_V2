<?php

/**
 * @return array{slug: string, card_mod: string, glow: string, accent: string}
 */
function layanan_category_theme(string $kategori): array
{
    $map = [
        'Kelembagaan' => [
            'slug' => 'kelembagaan',
            'card_mod' => 'layanan-premium-card--kelembagaan',
            'glow' => 'rgba(37, 99, 235, 0.38)',
            'accent' => '#2563eb',
        ],
        'Pelayanan Publik' => [
            'slug' => 'pelayanan',
            'card_mod' => 'layanan-premium-card--pelayanan',
            'glow' => 'rgba(13, 148, 136, 0.38)',
            'accent' => '#0d9488',
        ],
        'SAKIP & RB' => [
            'slug' => 'sakip',
            'card_mod' => 'layanan-premium-card--sakip',
            'glow' => 'rgba(124, 58, 237, 0.38)',
            'accent' => '#7c3aed',
        ],
    ];

    return $map[$kategori] ?? $map['Kelembagaan'];
}

/**
 * @param array<string, mixed> $item
 * @return array{class: string, text: string}
 */
function layanan_status_badge(array $item): array
{
    $link = trim((string) ($item['link'] ?? ''));
    if ($link !== '' && org_layanan_integrasi_url_normalize($link) !== '') {
        return [
            'class' => 'layanan-premium-card__status layanan-premium-card__status--digital',
            'text' => 'Tersedia Digital',
        ];
    }

    return [
        'class' => 'layanan-premium-card__status layanan-premium-card__status--manual',
        'text' => 'Proses Manual',
    ];
}

/**
 * @param array<string, mixed> $item
 * @return list<string>
 */
function layanan_collect_documents(array $item): array
{
    $docs = [];
    if (isset($item['media_documents']) && is_array($item['media_documents'])) {
        foreach ($item['media_documents'] as $docItem) {
            if (is_string($docItem) && trim($docItem) !== '') {
                $docs[] = trim($docItem);
            }
        }
    }
    $doc = trim((string) ($item['media_document'] ?? ''));
    if ($doc !== '' && !in_array($doc, $docs, true)) {
        $docs[] = $doc;
    }

    return $docs;
}
