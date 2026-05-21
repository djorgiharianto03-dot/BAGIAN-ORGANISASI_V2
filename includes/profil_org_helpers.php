<?php
declare(strict_types=1);

/**
 * Ubah HTML Misi menjadi daftar poin untuk tampilan profil.
 *
 * @return list<string>
 */
function org_profil_misi_to_points(string $html): array
{
    $html = trim($html);
    if ($html === '') {
        return [];
    }

    if (preg_match_all('/<li[^>]*>(.*?)<\/li>/is', $html, $matches)) {
        $points = [];
        foreach ($matches[1] as $item) {
            $t = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $item)));
            if ($t !== '') {
                $points[] = $t;
            }
        }
        if ($points !== []) {
            return $points;
        }
    }

    if (preg_match_all('/<p[^>]*>(.*?)<\/p>/is', $html, $matches)) {
        $points = [];
        foreach ($matches[1] as $item) {
            $t = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $item)));
            if ($t !== '') {
                $points[] = $t;
            }
        }
        if (count($points) > 1) {
            return $points;
        }
    }

    $withBreaks = preg_replace('/<br\s*\/?>/i', "\n", $html) ?? $html;
    $lines = preg_split('/\n+/u', trim(strip_tags((string) $withBreaks)));
    if (is_array($lines)) {
        $points = [];
        foreach ($lines as $line) {
            $t = trim(preg_replace('/\s+/u', ' ', (string) $line));
            if ($t !== '') {
                $points[] = $t;
            }
        }
        if (count($points) > 1) {
            return $points;
        }
    }

    $plain = trim(preg_replace('/\s+/u', ' ', strip_tags($html)));

    return $plain !== '' ? [$plain] : [];
}

/**
 * Plain text ke sentence case (huruf pertama kalimat kapital, sisanya kecil).
 */
function org_profil_to_sentence_case_plain(string $text): string
{
    $text = trim(preg_replace('/\s+/u', ' ', $text));
    if ($text === '') {
        return '';
    }

    $lower = mb_strtolower($text, 'UTF-8');
    $first = mb_substr($lower, 0, 1, 'UTF-8');
    $rest = mb_substr($lower, 1, null, 'UTF-8');

    return mb_strtoupper($first, 'UTF-8') . $rest;
}

/**
 * Terapkan sentence case pada blok HTML Visi (p, li, heading).
 */
function org_profil_visi_sentence_case_html(string $html): string
{
    $html = trim($html);
    if ($html === '') {
        return '';
    }

    if (preg_match_all('/<(p|li|h[1-6])(\s[^>]*)?>(.*?)<\/\1>/is', $html, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $m) {
            $tag = $m[1];
            $attrs = $m[2] ?? '';
            $plain = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $m[3])));
            if ($plain === '') {
                continue;
            }
            $cased = org_profil_to_sentence_case_plain($plain);
            $replacement = '<' . $tag . $attrs . '>'
                . htmlspecialchars($cased, ENT_QUOTES, 'UTF-8')
                . '</' . $tag . '>';
            $html = str_replace($m[0], $replacement, $html);
        }

        return $html;
    }

    $plain = trim(preg_replace('/\s+/u', ' ', strip_tags($html)));
    if ($plain === '') {
        return '';
    }

    return '<p>' . htmlspecialchars(org_profil_to_sentence_case_plain($plain), ENT_QUOTES, 'UTF-8') . '</p>';
}

/**
 * Teks Visi untuk tampilan profil (HTML disanitasi + sentence case).
 */
function org_profil_visi_display_html(string $html): string
{
    $html = trim($html);
    if ($html === '') {
        return '';
    }

    return org_profil_visi_sentence_case_html(org_sanitize_rich_html($html));
}
