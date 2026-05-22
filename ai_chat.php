<?php

/**
 * AI Smart Search — pencarian dokumen sederhana (tanpa OpenAI).
 * POST JSON: { "message": "pertanyaan user" }
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_database.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_session.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'site_content_db.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'dokumen_db.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'pusat_informasi_db.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'pengumuman_db.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'ai_chat_openai.php';

const ORG_AI_CHAT_MAX_MESSAGE_LEN = 300;
const ORG_AI_CHAT_RATE_LIMIT_MAX = 10;
const ORG_AI_CHAT_RATE_LIMIT_WINDOW_SEC = 60;

error_reporting(E_ALL);
if (!org_is_dev_environment()) {
    ini_set('display_errors', '0');
}

function org_ai_chat_send_json(array $payload, int $httpCode = 200): void
{
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=UTF-8');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function org_ai_chat_reject(string $answer, int $httpCode = 400): void
{
    org_ai_chat_send_json([
        'success' => false,
        'answer' => $answer,
        'results' => [],
    ], $httpCode);
}

function org_ai_chat_reset_db_error(): void
{
    $GLOBALS['org_ai_chat_db_failed'] = false;
}

function org_ai_chat_note_db_error(string $context, string $detail = ''): void
{
    $GLOBALS['org_ai_chat_db_failed'] = true;
    $message = '[ai_chat] ' . $context;
    if ($detail !== '') {
        $message .= ': ' . $detail;
    }
    error_log($message);
}

/**
 * @return array{success: bool, answer: string, results: list<mixed>}
 */
function org_ai_chat_service_unavailable_response(): array
{
    return [
        'success' => false,
        'answer' => 'Maaf, layanan chat sedang mengalami gangguan.',
        'results' => [],
    ];
}

/**
 * @param array{success: bool, answer: string, results: list<array{type: string, title: string, description: string, link: string}>} $response
 * @return array{success: bool, answer: string, results: list<array{type: string, title: string, description: string, link: string}>}
 */
function org_ai_chat_apply_db_error_response(array $response): array
{
    if (!empty($GLOBALS['org_ai_chat_db_failed'])) {
        return org_ai_chat_service_unavailable_response();
    }

    return $response;
}

function org_ai_chat_sanitize_message(string $message): string
{
    $s = trim(strip_tags($message));
    if ($s === '') {
        return '';
    }

    $s = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $s);
    $s = (string) preg_replace('/\p{Cc}/u', '', $s);

    return trim($s);
}

function org_ai_chat_client_ip(): string
{
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    if ($ip === '' || filter_var($ip, FILTER_VALIDATE_IP) === false) {
        return '0.0.0.0';
    }

    return $ip;
}

function org_ai_chat_rate_limit_session_key(): string
{
    return 'org_ai_chat_rl_' . hash('sha256', org_ai_chat_client_ip());
}

function org_ai_chat_rate_limit_allow(): bool
{
    $key = org_ai_chat_rate_limit_session_key();
    $now = time();
    $window = ORG_AI_CHAT_RATE_LIMIT_WINDOW_SEC;
    $hits = $_SESSION[$key] ?? [];
    if (!is_array($hits)) {
        $hits = [];
    }

    $hits = array_values(array_filter(
        $hits,
        static fn ($t): bool => is_int($t) && $t > $now - $window
    ));

    $_SESSION[$key] = $hits;

    return count($hits) < ORG_AI_CHAT_RATE_LIMIT_MAX;
}

function org_ai_chat_rate_limit_record(): void
{
    $key = org_ai_chat_rate_limit_session_key();
    $hits = $_SESSION[$key] ?? [];
    if (!is_array($hits)) {
        $hits = [];
    }
    $hits[] = time();
    $_SESSION[$key] = $hits;
}

/**
 * @return array{type: string, title: string, description: string, link: string}
 */
function org_ai_chat_result_item(string $type, string $title, string $description, string $link): array
{
    return [
        'type' => org_sanitize_plain($type),
        'title' => org_sanitize_plain($title),
        'description' => org_sanitize_plain($description),
        'link' => $link,
    ];
}

/**
 * @param list<array{type: string, title: string, description: string, link: string}> $results
 * @return list<string>
 */
function org_ai_chat_collect_result_types(array $results): array
{
    $types = [];
    foreach ($results as $row) {
        if (!is_array($row)) {
            continue;
        }
        $type = trim((string) ($row['type'] ?? ''));
        if ($type !== '' && !in_array($type, $types, true)) {
            $types[] = $type;
        }
    }

    return $types;
}

function org_ai_chat_format_keyword_display(string $keyword): string
{
    $keyword = trim(org_sanitize_plain($keyword));
    if ($keyword === '') {
        return '';
    }

    $upperHints = [
        'perbub' => 'Perbup',
        'perbup' => 'Perbup',
        'perda' => 'Perda',
        'perwali' => 'Perwali',
        'pergub' => 'Pergub',
        'sop' => 'SOP',
        'sakip' => 'SAKIP',
        'anjab' => 'Anjab',
        'evab' => 'Evab',
        'abk' => 'ABK',
        'sk' => 'SK',
    ];

    $lower = mb_strtolower($keyword);
    if (isset($upperHints[$lower])) {
        return $upperHints[$lower];
    }

    if (mb_strlen($keyword) <= 4 && preg_match('/^[a-z0-9]+$/iu', $keyword)) {
        return mb_strtoupper($keyword);
    }

    return mb_convert_case($keyword, MB_CASE_TITLE, 'UTF-8');
}

function org_ai_chat_trim_question_filler(string $message): string
{
    $s = mb_strtolower(org_sanitize_plain($message));
    $s = (string) preg_replace(
        '/^(saya\s+)?(cari|mencari|butuh|ingin\s+tahu|mau\s+tanya|tolong\s+cari|ada)\s+(informasi\s+)?(tentang\s+)?/u',
        '',
        $s
    );
    $s = (string) preg_replace('/^(dokumen|data|file|aturan)\s+(tentang\s+)?/u', '', $s);
    $s = trim((string) preg_replace('/[?.!]+$/u', '', $s));
    if (mb_strlen($s) > 48) {
        $s = mb_substr($s, 0, 48);
    }

    return trim($s);
}

/**
 * @param list<string> $terms
 */
function org_ai_chat_extract_display_keyword(string $message, array $terms = []): string
{
    foreach ($terms as $term) {
        $term = trim((string) $term);
        if ($term !== '' && mb_strlen($term) >= 2) {
            return org_ai_chat_format_keyword_display($term);
        }
    }

    $merged = array_merge(
        org_ai_chat_extract_search_terms($message),
        org_ai_chat_extract_layanan_search_terms($message),
        org_ai_chat_extract_publikasi_search_terms($message)
    );
    foreach ($merged as $term) {
        $term = trim((string) $term);
        if ($term !== '' && mb_strlen($term) >= 2) {
            return org_ai_chat_format_keyword_display($term);
        }
    }

    $trimmed = org_ai_chat_trim_question_filler($message);

    return $trimmed !== '' ? org_ai_chat_format_keyword_display($trimmed) : '';
}

/**
 * @param list<string> $types Label hasil (Dokumen, Layanan, Pengumuman, …)
 */
function org_ai_chat_smart_answer_multi_source(array $types): ?string
{
    $hasDoc = in_array('Dokumen', $types, true);
    $hasLay = in_array('Layanan', $types, true);
    $hasPub = in_array('Pengumuman', $types, true) || in_array('Berita', $types, true);

    $sourceCount = ($hasDoc ? 1 : 0) + ($hasLay ? 1 : 0) + ($hasPub ? 1 : 0);
    if ($sourceCount < 2) {
        return null;
    }

    if ($hasDoc && $hasPub && !$hasLay) {
        return 'Saya menemukan beberapa hasil dari Perpustakaan Digital dan Pusat Informasi yang mungkin sesuai.';
    }

    $labels = [];
    if ($hasDoc) {
        $labels[] = 'Perpustakaan Digital';
    }
    if ($hasLay) {
        $labels[] = 'Layanan Publik';
    }
    if ($hasPub) {
        $labels[] = 'Pusat Informasi';
    }

    if (count($labels) === 2) {
        return 'Saya menemukan beberapa hasil dari ' . $labels[0] . ' dan ' . $labels[1] . ' yang mungkin sesuai.';
    }

    $last = array_pop($labels);
    $joined = implode(', ', $labels) . ', dan ' . (string) $last;

    return 'Saya menemukan beberapa hasil dari ' . $joined . ' yang mungkin sesuai.';
}

/**
 * Susun jawaban natural berdasarkan intent, kata kunci, jumlah hasil, dan jenis hasil.
 *
 * @param list<string> $types
 */
function org_ai_chat_build_smart_answer(string $intent, string $keyword, int $resultsCount, array $types): string
{
    $intentNorm = mb_strtolower(trim($intent)) ?: 'general';

    if ($resultsCount === 0) {
        if ($intentNorm === 'dokumen') {
            return 'Maaf, saya belum menemukan dokumen terkait di Perpustakaan Digital.';
        }

        return 'Maaf, saya belum menemukan informasi tersebut di portal ini. Coba gunakan kata kunci lain seperti Perbup, SOTK, SOP, Layanan, atau Pengumuman.';
    }

    if ($resultsCount === 1) {
        return 'Saya menemukan 1 hasil yang paling sesuai dengan pertanyaan Anda.';
    }

    if ($intentNorm === 'cross' || $intentNorm === 'general') {
        $multi = org_ai_chat_smart_answer_multi_source($types);
        if ($multi !== null) {
            return $multi;
        }

        return 'Saya menemukan beberapa informasi yang mungkin sesuai dengan pertanyaan Anda.';
    }

    $kw = org_ai_chat_format_keyword_display($keyword);

    if ($intentNorm === 'dokumen') {
        if ($kw !== '') {
            return 'Saya menemukan dokumen terkait ' . $kw . ' di Perpustakaan Digital. Silakan pilih dokumen berikut:';
        }

        return 'Saya menemukan dokumen terkait pertanyaan Anda di Perpustakaan Digital. Silakan pilih dokumen berikut:';
    }

    if ($intentNorm === 'layanan') {
        return 'Saya menemukan layanan yang sesuai dengan pertanyaan Anda. Silakan buka layanan berikut:';
    }

    if ($intentNorm === 'pengumuman' || $intentNorm === 'publikasi') {
        return 'Saya menemukan informasi/pengumuman terbaru yang relevan. Silakan lihat daftar berikut:';
    }

    if ($intentNorm === 'personel') {
        return 'Saya menemukan data personel yang relevan dengan pertanyaan Anda. Silakan lihat detail berikut:';
    }

    return 'Saya menemukan beberapa hasil yang sesuai dengan pertanyaan Anda.';
}

/**
 * Saran pertanyaan lanjutan (logic lokal, bukan dari OpenAI).
 *
 * @return list<string>
 */
function org_ai_chat_build_suggestions(string $intent, bool $found): array
{
    if (!$found) {
        return [
            'Coba kata kunci Perbup',
            'Coba kata kunci SOP',
            'Lihat Perpustakaan Digital',
        ];
    }

    $intentNorm = mb_strtolower(trim($intent)) ?: 'cross';

    if ($intentNorm === 'dokumen') {
        return [
            'Cari dokumen lain',
            'Lihat Perpustakaan Digital',
            'Cari regulasi',
        ];
    }

    if ($intentNorm === 'layanan') {
        return [
            'Layanan apa saja?',
            'Cara mengajukan layanan',
            'Lihat semua layanan',
        ];
    }

    if ($intentNorm === 'pengumuman' || $intentNorm === 'publikasi') {
        return [
            'Pengumuman terbaru',
            'Berita terbaru',
            'Lihat pusat informasi',
        ];
    }

    if ($intentNorm === 'personel') {
        return [
            'Struktur Organisasi',
            'Lihat profil personel',
            'Tim kerja Bagian Organisasi',
        ];
    }

    return [
        'Lihat dokumen lainnya',
        'Cari layanan terkait',
        'Pengumuman terbaru',
    ];
}

/**
 * @param array<string, mixed> $response
 * @return array<string, mixed>
 */
function org_ai_chat_attach_suggestions(array $response): array
{
    $count = (int) ($response['results_count'] ?? count($response['results'] ?? []));
    $found = ($response['success'] ?? false) && $count > 0;
    $intent = (string) ($response['intent'] ?? 'cross');
    $response['suggestions'] = org_ai_chat_build_suggestions($intent, $found);

    return $response;
}

/**
 * @param list<array{type: string, title: string, description: string, link: string}> $results
 * @return array{success: bool, answer: string, intent: string, results_count: int, results: list<array{type: string, title: string, description: string, link: string}>, suggestions: list<string>}
 */
function org_ai_chat_build_response(
    array $results,
    bool $success = true,
    ?string $customAnswer = null,
    string $intent = 'general',
    ?string $keyword = null
): array {
    $count = count($results);
    $types = org_ai_chat_collect_result_types($results);
    $intentNorm = mb_strtolower(trim($intent)) ?: 'general';

    if ($customAnswer !== null && trim($customAnswer) !== '') {
        $answer = trim($customAnswer);
    } else {
        $answer = org_ai_chat_build_smart_answer($intentNorm, (string) ($keyword ?? ''), $count, $types);
    }

    if ($count === 0) {
        return org_ai_chat_attach_suggestions([
            'success' => false,
            'answer' => $answer,
            'intent' => $intentNorm,
            'results_count' => 0,
            'results' => [],
            'openai_polish' => false,
        ]);
    }

    return org_ai_chat_attach_suggestions([
        'success' => $success && $count > 0,
        'answer' => $answer,
        'intent' => $intentNorm,
        'results_count' => $count,
        'results' => $results,
        'openai_polish' => $customAnswer === null,
    ]);
}

/**
 * @param list<array{id: int, title: string, link: string, kategori?: string}> $rows
 * @return list<array{type: string, title: string, description: string, link: string}>
 */
function org_ai_chat_format_document_results(array $rows): array
{
    $results = [];
    foreach ($rows as $row) {
        $title = org_sanitize_plain((string) ($row['title'] ?? ''));
        $link = (string) ($row['link'] ?? '');
        if ($title === '' || $link === '') {
            continue;
        }
        $kat = org_sanitize_plain((string) ($row['kategori'] ?? ''));
        $description = $kat !== '' ? 'Kategori: ' . $kat : 'Dokumen perpustakaan digital';
        $results[] = org_ai_chat_result_item('Dokumen', $title, $description, $link);
        if (count($results) >= 5) {
            break;
        }
    }

    return $results;
}

/**
 * Ambil kata kunci dari pertanyaan (token + sinonim + kata kunci umum).
 *
 * @return list<string>
 */
function org_ai_chat_extract_search_terms(string $message): array
{
    $message = org_sanitize_plain($message);
    if ($message === '') {
        return [];
    }

    $lower = mb_strtolower($message);
    $terms = [];

    $hintWords = [
        'perbub',
        'perbup',
        'perda',
        'perwali',
        'pergub',
        'sop',
        'keputusan',
        'sk',
        'regulasi',
        'anjab',
        'evab',
        'abk',
        'sotk',
        'sakip',
    ];

    foreach ($hintWords as $hint) {
        if (str_contains($lower, $hint)) {
            foreach (org_dokumen_search_synonym_variants_for_token($hint) as $variant) {
                if ($variant !== '') {
                    $terms[] = $variant;
                }
            }
        }
    }

    $tokens = preg_split('/\s+/u', $lower, -1, PREG_SPLIT_NO_EMPTY);
    if (!is_array($tokens)) {
        $tokens = [];
    }

    foreach ($tokens as $rawTok) {
        $tok = mb_strtolower(org_dokumen_search_normalize_token_strip_ext((string) $rawTok));
        if ($tok === '') {
            continue;
        }
        $len = mb_strlen($tok);
        if ($len < 2) {
            continue;
        }
        if ($len === 2 && $tok !== 'sk') {
            continue;
        }
        foreach (org_dokumen_search_synonym_variants_for_token($tok) as $variant) {
            if ($variant !== '') {
                $terms[] = $variant;
            }
        }
    }

    $terms = array_values(array_unique($terms));

    if ($terms === [] && mb_strlen($lower) >= 3) {
        $terms[] = mb_substr($lower, 0, 120);
    }

    return $terms;
}

/**
 * @return list<string>
 */
function org_ai_chat_document_intent_keywords(): array
{
    return [
        'perbub',
        'perbup',
        'peraturan bupati',
        'perda',
        'perwali',
        'pergub',
        'keputusan',
        'sk',
        'sop',
        'regulasi',
        'aturan',
        'dokumen',
        'peraturan',
        'perpustakaan',
        'sakip',
        'sotk',
        'unduh',
        'download',
    ];
}

function org_ai_chat_message_has_perbup_hint(string $lower): bool
{
    return (bool) preg_match('/\bperbu[p b]\b/iu', $lower)
        || str_contains($lower, 'peraturan bupati');
}

/**
 * @return array<string, list<string>>
 */
function org_ai_chat_document_keyword_expansion_groups(): array
{
    return [
        'perbup' => ['perbup', 'perbub', 'peraturan bupati', 'bupati', 'sotk'],
        'regulasi' => ['regulasi', 'peraturan', 'aturan'],
        'aturan' => ['aturan', 'regulasi', 'peraturan'],
        'sotk' => ['sotk', 'perbup', 'perbub'],
    ];
}

/**
 * @param list<string> $baseTerms
 * @return list<string>
 */
function org_ai_chat_expand_document_search_terms(string $message, array $baseTerms): array
{
    $lower = mb_strtolower(org_sanitize_plain($message));
    $expanded = [];

    foreach ($baseTerms as $term) {
        $term = mb_strtolower(trim((string) $term));
        if ($term !== '') {
            $expanded[$term] = true;
        }
    }

    if (org_ai_chat_message_has_perbup_hint($lower)) {
        foreach (org_ai_chat_document_keyword_expansion_groups()['perbup'] as $kw) {
            $expanded[mb_strtolower($kw)] = true;
        }
    }

    foreach (org_ai_chat_document_keyword_expansion_groups() as $trigger => $keywords) {
        if ($trigger === 'perbup') {
            continue;
        }
        $hit = str_contains($lower, $trigger);
        if (!$hit) {
            foreach ($baseTerms as $term) {
                if (str_contains(mb_strtolower((string) $term), $trigger)) {
                    $hit = true;
                    break;
                }
            }
        }
        if ($hit) {
            foreach ($keywords as $kw) {
                $expanded[mb_strtolower($kw)] = true;
            }
        }
    }

    $out = [];
    foreach (array_keys($expanded) as $term) {
        foreach (org_dokumen_search_synonym_variants_for_token($term) as $variant) {
            if ($variant !== '') {
                $out[] = $variant;
            }
        }
    }

    return array_values(array_unique($out));
}

/**
 * @return list<string>
 */
function org_ai_chat_extract_document_search_terms(string $message): array
{
    return org_ai_chat_expand_document_search_terms(
        $message,
        org_ai_chat_extract_search_terms($message)
    );
}

/**
 * @return array{judul: bool, deskripsi: bool, ringkasan: bool}
 */
function org_ai_chat_dokumen_search_columns(mysqli $db): array
{
    $flags = ['judul' => false, 'deskripsi' => false, 'ringkasan' => false];
    $colRes = $db->query('SHOW COLUMNS FROM `dokumen`');
    if ($colRes === false) {
        return $flags;
    }
    while ($colRow = $colRes->fetch_assoc()) {
        if (!is_array($colRow)) {
            continue;
        }
        $field = (string) ($colRow['Field'] ?? '');
        if (isset($flags[$field])) {
            $flags[$field] = true;
        }
    }

    return $flags;
}

/**
 * @return list<string>
 */
function org_ai_chat_general_cross_keywords(): array
{
    return ['organisasi', 'informasi', 'data', 'arsip'];
}

function org_ai_chat_message_contains_keyword(string $lower, string $keyword): bool
{
    if ($keyword === 'sk') {
        return (bool) preg_match('/\bsk\b/u', $lower);
    }

    return str_contains($lower, $keyword);
}

function org_ai_chat_is_dokumen_intent(string $message): bool
{
    if (org_ai_chat_is_personel_intent($message)) {
        return false;
    }

    $lower = mb_strtolower(org_sanitize_plain($message));
    if (org_ai_chat_message_has_perbup_hint($lower)) {
        return true;
    }

    foreach (org_ai_chat_document_intent_keywords() as $keyword) {
        if (org_ai_chat_message_contains_keyword($lower, $keyword)) {
            return true;
        }
    }

    return (bool) preg_match(
        '/\b(apakah|ada)\b.{0,30}\b(perbub|perbup|peraturan|regulasi|dokumen|sotk)\b/iu',
        $lower
    );
}

function org_ai_chat_should_use_cross_search(string $message): bool
{
    if (org_ai_chat_is_personel_intent($message)
        || org_ai_chat_is_dokumen_intent($message)
        || org_ai_chat_is_layanan_intent($message)
        || org_ai_chat_is_publikasi_intent($message)) {
        return false;
    }

    $lower = mb_strtolower(org_sanitize_plain($message));
    foreach (org_ai_chat_general_cross_keywords() as $keyword) {
        if (str_contains($lower, $keyword)) {
            return true;
        }
    }

    return false;
}

function org_ai_chat_term_matches_haystack(string $haystack, string $term): bool
{
    $term = mb_strtolower(trim($term));
    if ($term === '') {
        return false;
    }

    $haystack = mb_strtolower($haystack);
    if (mb_strlen($term) <= 3) {
        $pattern = '/\b' . preg_quote($term, '/') . '\b/u';

        return (bool) preg_match($pattern, $haystack);
    }

    return str_contains($haystack, $term);
}

/**
 * @param list<array{type: string, title: string, description: string, link: string}> $results
 * @return list<array{type: string, title: string, description: string, link: string}>
 */
function org_ai_chat_filter_results_by_type(array $results, string $expectedType): array
{
    $out = [];
    foreach ($results as $row) {
        if (!is_array($row)) {
            continue;
        }
        if (trim((string) ($row['type'] ?? '')) === $expectedType) {
            $out[] = $row;
        }
    }

    return $out;
}

/**
 * @param array<string, mixed> $row
 * @param list<string> $terms
 * @param array{judul: bool, deskripsi: bool, ringkasan: bool} $cols
 */
function org_ai_chat_score_document_match(array $row, array $terms, array $cols): int
{
    $haveJudul = $cols['judul'] ?? false;
    $haveDeskripsi = $cols['deskripsi'] ?? false;
    $haveRingkasan = $cols['ringkasan'] ?? false;

    $judul = $haveJudul ? mb_strtolower(trim((string) ($row['judul'] ?? ''))) : '';
    $namaFile = mb_strtolower((string) ($row['nama_file'] ?? ''));
    $kategori = mb_strtolower(trim((string) ($row['kategori'] ?? '')));
    $deskripsi = $haveDeskripsi ? mb_strtolower(trim((string) ($row['deskripsi'] ?? ''))) : '';
    $ringkasan = $haveRingkasan ? mb_strtolower(trim((string) ($row['ringkasan'] ?? ''))) : '';
    $titleHay = $judul !== '' ? $judul : mb_strtolower(str_replace('_', ' ', basename($namaFile)));

    $score = 0;
    foreach ($terms as $term) {
        $term = mb_strtolower(mb_substr(trim((string) $term), 0, 80));
        if ($term === '') {
            continue;
        }

        if ($judul !== '' && org_dokumen_search_token_matches_haystack($judul, $term)) {
            $score += 100;
        }
        if (org_dokumen_search_token_matches_haystack($namaFile, $term)) {
            $score += 70;
        }
        if ($kategori !== '' && org_dokumen_search_token_matches_haystack($kategori, $term)) {
            $score += 40;
        }
        if ($deskripsi !== '' && org_dokumen_search_token_matches_haystack($deskripsi, $term)) {
            $score += 25;
        }
        if ($ringkasan !== '' && org_dokumen_search_token_matches_haystack($ringkasan, $term)) {
            $score += 25;
        }
    }

    return $score;
}

/**
 * @param list<string> $terms
 * @return list<array{id: int, title: string, link: string, kategori?: string}>
 */
function org_ai_chat_search_documents(mysqli $db, array $terms, int $limit = 6, ?string $message = null): array
{
    if ($terms === []) {
        return [];
    }

    org_dokumen_ensure_table($db);
    org_dokumen_migrate_metadata_columns($db);

    if (!org_dokumen_table_exists($db)) {
        return [];
    }

    if ($message !== null && trim($message) !== '') {
        $terms = org_ai_chat_expand_document_search_terms($message, $terms);
    }

    $cols = org_ai_chat_dokumen_search_columns($db);
    $haveJudul = $cols['judul'];
    $haveDeskripsi = $cols['deskripsi'];
    $haveRingkasan = $cols['ringkasan'];

    $clauses = [];
    $types = '';
    $params = [];

    $sqlTerms = [];
    foreach ($terms as $term) {
        foreach (org_dokumen_search_synonym_variants_for_token((string) $term) as $variant) {
            $variant = mb_substr(trim($variant), 0, 80);
            if ($variant !== '') {
                $sqlTerms[mb_strtolower($variant)] = true;
            }
        }
    }

    foreach (array_keys($sqlTerms) as $term) {
        $like = '%' . $term . '%';
        $part = [];
        if ($haveJudul) {
            $part[] = 'LOWER(`judul`) LIKE ?';
            $types .= 's';
            $params[] = $like;
        }
        $part[] = 'LOWER(`nama_file`) LIKE ?';
        $types .= 's';
        $params[] = $like;
        $part[] = 'LOWER(`kategori`) LIKE ?';
        $types .= 's';
        $params[] = $like;
        if ($haveDeskripsi) {
            $part[] = 'LOWER(`deskripsi`) LIKE ?';
            $types .= 's';
            $params[] = $like;
        }
        if ($haveRingkasan) {
            $part[] = 'LOWER(`ringkasan`) LIKE ?';
            $types .= 's';
            $params[] = $like;
        }
        if ($part !== []) {
            $clauses[] = '(' . implode(' OR ', $part) . ')';
        }
    }

    if ($clauses === []) {
        return [];
    }

    $selectCols = '`id`, `nama_file`, `kategori`';
    if ($haveJudul) {
        $selectCols .= ', `judul`';
    }
    if ($haveDeskripsi) {
        $selectCols .= ', `deskripsi`';
    }
    if ($haveRingkasan) {
        $selectCols .= ', `ringkasan`';
    }

    $sql = 'SELECT ' . $selectCols
        . ' FROM `dokumen` WHERE (' . implode(' OR ', $clauses) . ')'
        . ' ORDER BY `created_at` DESC'
        . ' LIMIT 40';

    $st = $db->prepare($sql);
    if ($st === false) {
        org_ai_chat_note_db_error('dokumen search prepare failed', (string) $db->error);
        return [];
    }

    org_ai_chat_stmt_bind($st, $types, $params);

    if (!$st->execute()) {
        org_ai_chat_note_db_error('dokumen search execute failed', (string) $st->error);
        $st->close();
        return [];
    }

    $result = $st->get_result();
    $st->close();

    if ($result === false) {
        return [];
    }

    $prefix = org_ai_chat_url_prefix();
    $scored = [];
    $seenIds = [];

    while ($row = $result->fetch_assoc()) {
        if (!is_array($row)) {
            continue;
        }
        $id = (int) ($row['id'] ?? 0);
        if ($id <= 0 || isset($seenIds[$id])) {
            continue;
        }
        $namaFile = (string) ($row['nama_file'] ?? '');
        if ($namaFile === '' || !org_dokumen_is_library_file($namaFile)) {
            continue;
        }

        $matchScore = org_ai_chat_score_document_match($row, $terms, $cols);
        if ($matchScore <= 0) {
            continue;
        }

        $judulDb = $haveJudul ? trim((string) ($row['judul'] ?? '')) : '';
        $title = $judulDb !== '' ? $judulDb : str_replace('_', ' ', basename($namaFile));
        $title = org_sanitize_plain($title);
        if ($title === '') {
            continue;
        }

        $kategori = org_sanitize_plain(trim((string) ($row['kategori'] ?? '')));
        $seenIds[$id] = true;
        $scored[] = [
            'score' => $matchScore,
            'id' => $id,
            'title' => $title,
            'kategori' => $kategori,
            'link' => $prefix . '/dokumen.php?id=' . $id,
        ];
    }

    usort($scored, static fn (array $a, array $b): int => ($b['score'] <=> $a['score']) ?: ($b['id'] <=> $a['id']));

    $out = [];
    foreach ($scored as $item) {
        $out[] = [
            'id' => (int) $item['id'],
            'title' => (string) $item['title'],
            'kategori' => (string) $item['kategori'],
            'link' => (string) $item['link'],
        ];
        if (count($out) >= $limit) {
            break;
        }
    }

    return $out;
}

/**
 * @return array{success: bool, answer: string, intent: string, results_count: int, results: list<array{type: string, title: string, description: string, link: string}>, openai_polish?: bool}
 */
function org_ai_chat_resolve_dokumen(mysqli $db, string $message): array
{
    $terms = org_ai_chat_extract_document_search_terms($message);
    $keyword = org_ai_chat_extract_display_keyword($message, $terms);
    $rows = org_ai_chat_search_documents($db, $terms, 6, $message);
    $results = org_ai_chat_filter_results_by_type(
        org_ai_chat_format_document_results($rows),
        'Dokumen'
    );

    if ($results === []) {
        return org_ai_chat_build_response(
            [],
            false,
            'Maaf, saya belum menemukan dokumen terkait di Perpustakaan Digital.',
            'dokumen',
            $keyword
        );
    }

    return org_ai_chat_build_response($results, true, null, 'dokumen', $keyword);
}

function org_ai_chat_url_prefix(): string
{
    $webRoot = org_site_web_root();

    return $webRoot === '' ? '' : rtrim($webRoot, '/');
}

function org_ai_chat_is_personel_intent(string $message): bool
{
    $lower = mb_strtolower(org_sanitize_plain($message));
    $keywords = [
        'pegawai',
        'personel',
        'personil',
        'staff',
        'sdm',
        'struktur organisasi',
        'bagan organisasi',
        'kepala bagian',
        'tim kerja',
        'tim mana',
        'di tim',
        'anggota tim',
        'nama pegawai',
        'siapa pegawai',
        'daftar pegawai',
        'daftar personel',
    ];
    foreach ($keywords as $keyword) {
        if (str_contains($lower, $keyword)) {
            return true;
        }
    }

    return (bool) preg_match(
        '/\b(jumlah|berapa|total|banyak|ada berapa)\b.{0,40}\b(pegawai|personel|staff)\b/u',
        $lower
    ) || (bool) preg_match(
        '/\b(pegawai|personel|staff)\b.{0,40}\b(jumlah|berapa|total|ada berapa|siapa)\b/u',
        $lower
    ) || (bool) preg_match(
        '/\b(siapa|nama)\b.{0,30}\b(pegawai|personel)\b/u',
        $lower
    );
}

/**
 * @return list<array{id: string, name: string, position: string, nip: string, tim: string, kelompok: string}>
 */
function org_ai_chat_load_personnel_catalog(): array
{
    if (!defined('ORG_ROOT')) {
        define('ORG_ROOT', __DIR__);
    }

    $path = ORG_ROOT . DIRECTORY_SEPARATOR . 'personnel.json';
    if (!is_file($path)) {
        return [];
    }

    $raw = file_get_contents($path);
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $parsed = json_decode($raw, true);
    if (!is_array($parsed)) {
        return [];
    }

    $rows = [];
    foreach ($parsed as $entry) {
        if (!is_array($entry)) {
            continue;
        }
        $name = org_sanitize_plain(trim((string) ($entry['name'] ?? '')));
        if ($name === '') {
            continue;
        }
        $position = org_sanitize_plain(trim((string) ($entry['position'] ?? '')));
        $nip = org_sanitize_plain(trim((string) ($entry['nip'] ?? '')));
        $tim = org_ai_chat_personnel_tim_label($position);
        $kelompok = org_ai_chat_personnel_kelompok_label($position);

        $rows[] = [
            'id' => trim((string) ($entry['id'] ?? '')),
            'name' => $name,
            'position' => $position,
            'nip' => $nip,
            'tim' => $tim,
            'kelompok' => $kelompok,
        ];
    }

    return $rows;
}

function org_ai_chat_personnel_tim_label(string $position): string
{
    $pos = mb_strtoupper(preg_replace('/\s+/u', ' ', trim($position)), 'UTF-8');
    if ($pos === '') {
        return 'Belum diketahui';
    }

    if (preg_match('/\(TIM\s+([^)]+)\)/u', $pos, $matches) === 1) {
        $tim = trim((string) ($matches[1] ?? ''));
        if ($tim !== '') {
            return mb_convert_case(mb_strtolower($tim, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
        }
    }

    if (str_contains($pos, 'KEPALA BAGIAN ORGANISASI')) {
        return 'Pimpinan — Kepala Bagian Organisasi';
    }
    if (str_contains($pos, 'SAKIP') || str_contains($pos, 'KINERJA') || preg_match('/\bRB\b/u', $pos) === 1) {
        return 'Tim SAKIP & RB (inferensi dari jabatan)';
    }
    if (str_contains($pos, 'KELEMBAGAAN') || str_contains($pos, 'ANJAB') || str_contains($pos, 'ABK')) {
        return 'Tim Kelembagaan & Anjab (inferensi dari jabatan)';
    }
    if (
        str_contains($pos, 'PENATA LAYANAN')
        || str_contains($pos, 'PENGELOLA LAYANAN')
        || str_contains($pos, 'PENGADMINISTRASI')
        || str_contains($pos, 'PELAKSANA')
    ) {
        return 'Tim Pelayanan Publik & Tata Laksana (kelompok pelaksana)';
    }
    if (str_contains($pos, 'PENELAAH TEKNIS') || str_contains($pos, 'ANALIS KEBIJAKAN')) {
        return 'Kelompok jabatan fungsional (belum ada penanda TIM di jabatan)';
    }

    return 'Belum ada penanda tim spesifik';
}

function org_ai_chat_personnel_kelompok_label(string $position): string
{
    $pos = mb_strtoupper(preg_replace('/\s+/u', ' ', trim($position)), 'UTF-8');
    if (str_contains($pos, 'KEPALA BAGIAN ORGANISASI')) {
        return 'Pimpinan';
    }
    if (
        str_contains($pos, 'PENATA')
        || str_contains($pos, 'PENGELOLA')
        || str_contains($pos, 'PENGADMINISTRASI')
    ) {
        return 'Pelaksana';
    }
    if (str_contains($pos, 'PENELAAH') || str_contains($pos, 'ANALIS')) {
        return 'Fungsional';
    }

    return 'Lainnya';
}

function org_ai_chat_is_personel_count_question(string $message): bool
{
    $lower = mb_strtolower(org_sanitize_plain($message));

    return (bool) preg_match(
        '/\b(jumlah|berapa|total|banyak|ada berapa|hitung)\b/u',
        $lower
    ) && (bool) preg_match('/\b(pegawai|personel|staff|sdm)\b/u', $lower);
}

function org_ai_chat_is_personel_list_question(string $message): bool
{
    $lower = mb_strtolower(org_sanitize_plain($message));

    return (bool) preg_match(
        '/\b(daftar|sebutkan|siapa saja|nama nama|nama-nama|semua pegawai|semua personel|pegawai apa|personel apa)\b/u',
        $lower
    );
}

/**
 * @return list<string>
 */
function org_ai_chat_extract_personel_search_terms(string $message): array
{
    $lower = mb_strtolower(org_sanitize_plain($message));
    $tokens = preg_split('/\s+/u', $lower, -1, PREG_SPLIT_NO_EMPTY);
    if (!is_array($tokens)) {
        return [];
    }

    $stop = [
        'pegawai', 'personel', 'personil', 'staff', 'sdm', 'nama', 'siapa', 'berapa', 'jumlah',
        'total', 'banyak', 'ada', 'berapa', 'orang', 'tim', 'kerja', 'mana', 'di', 'pada',
        'yang', 'apa', 'saja', 'daftar', 'sebutkan', 'semua', 'saya', 'mau', 'tahu', 'informasi',
        'tentang', 'struktur', 'organisasi', 'bagian', 'organisasi', 'kepala', 'bagian', 'dan',
        'atau', 'dari', 'untuk', 'ini', 'itu', 'adalah', 'berada', 'bekerja', 'anggota',
        'kelompok', 'jabatan', 'posisi', 'profil', 'portal',
    ];

    $terms = [];
    foreach ($tokens as $tok) {
        $tok = trim((string) $tok);
        if ($tok === '' || in_array($tok, $stop, true)) {
            continue;
        }
        if (mb_strlen($tok) < 2) {
            continue;
        }
        $terms[] = $tok;
    }

    return array_values(array_unique($terms));
}

/**
 * @param list<string> $terms
 */
function org_ai_chat_person_name_matches(string $name, array $terms): bool
{
    if ($terms === []) {
        return false;
    }
    $hay = mb_strtolower($name);
    $matched = 0;
    foreach ($terms as $term) {
        if ($term !== '' && str_contains($hay, $term)) {
            $matched++;
        }
    }

    return $matched > 0 && $matched >= min(count($terms), max(1, (int) ceil(count($terms) * 0.6)));
}

/**
 * @param list<array{id: string, name: string, position: string, nip: string, tim: string, kelompok: string}> $catalog
 * @return list<array{id: string, name: string, position: string, nip: string, tim: string, kelompok: string}>
 */
function org_ai_chat_filter_personnel_by_tim_hint(array $catalog, string $message): array
{
    $lower = mb_strtolower($message);
    $hints = [
        'kelembagaan' => ['kelembagaan', 'anjab', 'abk'],
        'sakip' => ['sakip', 'rb', 'kinerja'],
        'pelayanan' => ['pelayanan', 'yanlik', 'tata laksana', 'pelaksana', 'operasional'],
        'pimpinan' => ['kepala bagian', 'pimpinan'],
        'fungsional' => ['fungsional', 'analis', 'penelaah'],
    ];

    foreach ($hints as $label => $keys) {
        foreach ($keys as $key) {
            if (!str_contains($lower, $key)) {
                continue;
            }
            $out = [];
            foreach ($catalog as $row) {
                $timHay = mb_strtolower(((string) ($row['tim'] ?? '')) . ' ' . ((string) ($row['kelompok'] ?? '')));
                $posHay = mb_strtolower((string) ($row['position'] ?? ''));
                if (str_contains($timHay, $key) || str_contains($posHay, $key)) {
                    $out[] = $row;
                }
            }
            if ($out !== []) {
                return $out;
            }
        }
    }

    return [];
}

/**
 * @param list<array{id: string, name: string, position: string, nip: string, tim: string, kelompok: string}> $rows
 * @return list<array{title: string, description: string, link: string}>
 */
function org_ai_chat_format_personnel_results(array $rows, string $prefix): array
{
    $link = $prefix . '/profil.php#profil-struktur-organisasi';
    $results = [];
    foreach ($rows as $row) {
        $name = (string) ($row['name'] ?? '');
        if ($name === '') {
            continue;
        }
        $jabatan = (string) ($row['position'] ?? '');
        $tim = (string) ($row['tim'] ?? '');
        $nip = (string) ($row['nip'] ?? '');
        $descParts = [];
        if ($jabatan !== '') {
            $descParts[] = 'Jabatan: ' . $jabatan;
        }
        if ($tim !== '') {
            $descParts[] = 'Tim: ' . $tim;
        }
        if ($nip !== '') {
            $descParts[] = 'NIP: ' . $nip;
        }
        $results[] = org_ai_chat_result_item('Personel', $name, implode(' · ', $descParts), $link);
        if (count($results) >= 5) {
            break;
        }
    }

    return $results;
}

/**
 * @param list<array{id: string, name: string, position: string, nip: string, tim: string, kelompok: string}> $catalog
 * @return array{success: bool, answer: string, results: list<array{type: string, title: string, description: string, link: string}>}
 */
function org_ai_chat_resolve_personel(string $message): array
{
    $prefix = org_ai_chat_url_prefix();
    $profilLink = $prefix . '/profil.php#profil-struktur-organisasi';
    $catalog = org_ai_chat_load_personnel_catalog();

    if ($catalog === []) {
        return org_ai_chat_build_response(
            [
                org_ai_chat_result_item('Personel', 'Struktur & Personel', 'Halaman profil organisasi', $profilLink),
            ],
            false,
            null,
            'personel',
            org_ai_chat_extract_display_keyword($message)
        );
    }

    $total = count($catalog);
    $kelompokCounts = ['Pimpinan' => 0, 'Fungsional' => 0, 'Pelaksana' => 0, 'Lainnya' => 0];
    foreach ($catalog as $row) {
        $k = (string) ($row['kelompok'] ?? 'Lainnya');
        if (!isset($kelompokCounts[$k])) {
            $kelompokCounts[$k] = 0;
        }
        $kelompokCounts[$k]++;
    }

    if (org_ai_chat_is_personel_count_question($message)) {
        $answer = 'Saat ini tercatat ' . $total . ' pegawai di Bagian Organisasi'
            . ': ' . (int) $kelompokCounts['Pimpinan'] . ' pimpinan, '
            . (int) $kelompokCounts['Fungsional'] . ' jabatan fungsional, '
            . (int) $kelompokCounts['Pelaksana'] . ' pelaksana.';
        if ((int) $kelompokCounts['Lainnya'] > 0) {
            $answer .= ' Lainnya: ' . (int) $kelompokCounts['Lainnya'] . ' orang.';
        }
        $answer .= ' Sebutkan nama atau tim jika Anda ingin detail per orang.';

        return org_ai_chat_build_response(
            [
                org_ai_chat_result_item('Personel', 'Lihat struktur & personel', 'Halaman profil organisasi', $profilLink),
            ],
            true,
            $answer,
            'personel',
            org_ai_chat_extract_display_keyword($message)
        );
    }

    $terms = org_ai_chat_extract_personel_search_terms($message);
    $keyword = org_ai_chat_extract_display_keyword($message, $terms);
    $byTim = org_ai_chat_filter_personnel_by_tim_hint($catalog, $message);
    $picked = [];

    if ($terms !== []) {
        foreach ($catalog as $row) {
            if (org_ai_chat_person_name_matches((string) ($row['name'] ?? ''), $terms)) {
                $picked[] = $row;
            }
        }
    }

    if ($picked === [] && $byTim !== []) {
        $picked = array_slice($byTim, 0, 5);
    }

    if ($picked !== []) {
        if (count($picked) > 5) {
            $picked = array_slice($picked, 0, 5);
        }

        return org_ai_chat_build_response(
            org_ai_chat_format_personnel_results($picked, $prefix),
            true,
            null,
            'personel',
            $keyword
        );
    }

    if (org_ai_chat_is_personel_list_question($message) || $terms === []) {
        $picked = array_slice($catalog, 0, 5);

        return org_ai_chat_build_response(
            org_ai_chat_format_personnel_results($picked, $prefix),
            true,
            null,
            'personel',
            $keyword
        );
    }

    return org_ai_chat_build_response(
        [
            org_ai_chat_result_item('Personel', 'Struktur & Personel', 'Daftar lengkap di halaman profil', $profilLink),
        ],
        false,
        null,
        'personel',
        $keyword
    );
}

function org_ai_chat_publikasi_excerpt(string $text, int $max = 180): string
{
    $plain = org_sanitize_plain(trim(strip_tags($text)));
    if ($plain === '') {
        return '';
    }
    if (mb_strlen($plain) <= $max) {
        return $plain;
    }

    return mb_substr($plain, 0, $max - 3) . '...';
}

function org_ai_chat_is_publikasi_intent(string $message): bool
{
    if (org_ai_chat_is_personel_intent($message)) {
        return false;
    }

    if (org_ai_chat_is_dokumen_intent($message)) {
        return false;
    }

    $lower = mb_strtolower(org_sanitize_plain($message));

    $keywords = ['pengumuman', 'berita', 'kabar', 'publikasi'];
    foreach ($keywords as $keyword) {
        if (str_contains($lower, $keyword)) {
            return true;
        }
    }

    return (bool) preg_match(
        '/\b(terbaru|terkini)\b.{0,25}\b(pengumuman|berita|informasi|kabar|publikasi)\b/u',
        $lower
    ) || (bool) preg_match(
        '/\b(pengumuman|berita|informasi|kabar|publikasi)\b.{0,25}\b(terbaru|terkini|apa)\b/u',
        $lower
    );
}

function org_ai_chat_is_publikasi_general_question(string $message): bool
{
    $lower = mb_strtolower(org_sanitize_plain($message));
    $patterns = [
        'pengumuman terbaru',
        'berita terbaru',
        'informasi terbaru',
        'kabar terbaru',
        'publikasi terbaru',
        'ada pengumuman',
        'ada berita',
        'berita apa',
        'pengumuman apa',
        'informasi apa',
        'pengumuman terkini',
        'berita terkini',
    ];
    foreach ($patterns as $pattern) {
        if (str_contains($lower, $pattern)) {
            return true;
        }
    }

    return (bool) preg_match('/\b(apa|ada)\b.{0,20}\b(pengumuman|berita|kabar)\b/u', $lower);
}

/**
 * @return 'berita'|'pengumuman'|'all'
 */
function org_ai_chat_publikasi_kategori_filter(string $message): string
{
    $lower = mb_strtolower(org_sanitize_plain($message));
    $wantPeng = str_contains($lower, 'pengumuman');
    $wantBerita = str_contains($lower, 'berita') || str_contains($lower, 'kabar') || str_contains($lower, 'publikasi');
    if ($wantPeng && !$wantBerita) {
        return 'pengumuman';
    }
    if ($wantBerita && !$wantPeng) {
        return 'berita';
    }

    return 'all';
}

/**
 * @return list<string>
 */
function org_ai_chat_extract_publikasi_search_terms(string $message): array
{
    $lower = mb_strtolower(org_sanitize_plain($message));
    $tokens = preg_split('/\s+/u', $lower, -1, PREG_SPLIT_NO_EMPTY);
    if (!is_array($tokens)) {
        return [];
    }

    $stop = [
        'pengumuman', 'berita', 'informasi', 'kabar', 'publikasi', 'terbaru', 'terkini', 'apa', 'ada',
        'yang', 'saja', 'semua', 'cari', 'tolong', 'mau', 'ingin', 'tentang', 'mengenai', 'di', 'ke',
        'dari', 'dan', 'atau', 'ini', 'itu', 'bagian', 'organisasi', 'portal', 'halaman', 'baca',
        'lihat', 'daftar', 'kabar', 'artikel', 'posting',
    ];

    $terms = [];
    foreach ($tokens as $tok) {
        $tok = trim((string) $tok);
        if ($tok === '' || in_array($tok, $stop, true)) {
            continue;
        }
        if (mb_strlen($tok) < 2) {
            continue;
        }
        $terms[] = $tok;
    }

    return array_values(array_unique($terms));
}

/**
 * @return list<array{id: int, judul: string, ringkasan: string, tanggal: string, kategori: string, source: string}>
 */
function org_ai_chat_load_publikasi_from_pusat(mysqli $db, string $kategoriFilter = 'all'): array
{
    org_pusat_informasi_ensure_table($db);
    if (!org_pusat_informasi_table_exists($db)) {
        return [];
    }

    $sql = 'SELECT `id`, `judul`, `kategori`, `isi_teks`, `created_at` FROM `pusat_informasi`';
    $types = '';
    $params = [];
    if ($kategoriFilter === 'berita' || $kategoriFilter === 'pengumuman') {
        $sql .= ' WHERE `kategori` = ?';
        $types = 's';
        $params[] = $kategoriFilter;
    }
    $sql .= ' ORDER BY `created_at` DESC LIMIT 100';

    if ($types === '') {
        $res = $db->query($sql);
        if ($res === false) {
            org_ai_chat_note_db_error('pusat_informasi list query failed', (string) $db->error);
            return [];
        }
    } else {
        $st = $db->prepare($sql);
        if ($st === false) {
            org_ai_chat_note_db_error('pusat_informasi list prepare failed', (string) $db->error);
            return [];
        }
        org_ai_chat_stmt_bind($st, $types, $params);
        if (!$st->execute()) {
            org_ai_chat_note_db_error('pusat_informasi list execute failed', (string) $st->error);
            $st->close();
            return [];
        }
        $res = $st->get_result();
        $st->close();
        if ($res === false) {
            return [];
        }
    }

    $rows = [];
    while ($row = $res->fetch_assoc()) {
        if (!is_array($row)) {
            continue;
        }
        $judul = org_sanitize_plain(trim((string) ($row['judul'] ?? '')));
        if ($judul === '') {
            continue;
        }
        $kat = strtolower(trim((string) ($row['kategori'] ?? 'berita')));
        if ($kat !== 'pengumuman') {
            $kat = 'berita';
        }
        $rows[] = [
            'id' => (int) ($row['id'] ?? 0),
            'judul' => $judul,
            'ringkasan' => org_ai_chat_publikasi_excerpt((string) ($row['isi_teks'] ?? '')),
            'tanggal' => (string) ($row['created_at'] ?? ''),
            'kategori' => $kat,
            'source' => 'pusat_informasi',
        ];
    }

    return $rows;
}

/**
 * @return list<array{id: int, judul: string, ringkasan: string, tanggal: string, kategori: string, source: string}>
 */
function org_ai_chat_load_publikasi_from_pengumuman_table(mysqli $db): array
{
    org_pengumuman_ensure_table($db);
    if (!org_pengumuman_table_exists($db)) {
        return [];
    }

    $res = $db->query(
        'SELECT `id`, `judul`, `teks`, `created_at` FROM `pengumuman` ORDER BY `created_at` DESC LIMIT 50'
    );
    if ($res === false) {
        org_ai_chat_note_db_error('pengumuman list query failed', (string) $db->error);
        return [];
    }

    $rows = [];
    while ($row = $res->fetch_assoc()) {
        if (!is_array($row)) {
            continue;
        }
        $judul = org_sanitize_plain(trim((string) ($row['judul'] ?? '')));
        if ($judul === '') {
            continue;
        }
        $rows[] = [
            'id' => (int) ($row['id'] ?? 0),
            'judul' => $judul,
            'ringkasan' => org_ai_chat_publikasi_excerpt((string) ($row['teks'] ?? '')),
            'tanggal' => (string) ($row['created_at'] ?? ''),
            'kategori' => 'pengumuman',
            'source' => 'pengumuman',
        ];
    }

    return $rows;
}

/**
 * @return list<array{id: int, judul: string, ringkasan: string, tanggal: string, kategori: string, source: string}>
 */
function org_ai_chat_fetch_publikasi_catalog(mysqli $db, string $kategoriFilter = 'all'): array
{
    $rows = org_ai_chat_load_publikasi_from_pusat($db, $kategoriFilter);
    if ($kategoriFilter === 'all' || $kategoriFilter === 'pengumuman') {
        $rows = array_merge($rows, org_ai_chat_load_publikasi_from_pengumuman_table($db));
    }

    usort($rows, static function (array $a, array $b): int {
        $ta = strtotime((string) ($a['tanggal'] ?? '')) ?: 0;
        $tb = strtotime((string) ($b['tanggal'] ?? '')) ?: 0;

        return $tb <=> $ta;
    });

    return $rows;
}

/**
 * @param list<array{id: int, judul: string, ringkasan: string, tanggal: string, kategori: string, source: string}> $catalog
 * @param list<string> $terms
 * @return list<array{id: int, judul: string, ringkasan: string, tanggal: string, kategori: string, source: string}>
 */
function org_ai_chat_search_publikasi_catalog(array $catalog, array $terms, int $limit = 5): array
{
    if ($catalog === [] || $terms === []) {
        return [];
    }

    $scored = [];
    foreach ($catalog as $row) {
        $judulHay = mb_strtolower((string) ($row['judul'] ?? ''));
        $ringHay = mb_strtolower((string) ($row['ringkasan'] ?? ''));
        $katHay = mb_strtolower((string) ($row['kategori'] ?? ''));
        $score = 0;
        foreach ($terms as $term) {
            $term = mb_substr((string) $term, 0, 80);
            if ($term === '') {
                continue;
            }
            if (org_ai_chat_term_matches_haystack($judulHay, $term)) {
                $score += 55;
            } elseif ($ringHay !== '' && org_ai_chat_term_matches_haystack($ringHay, $term)) {
                $score += 15;
            } elseif ($katHay !== '' && org_ai_chat_term_matches_haystack($katHay, $term)) {
                $score += 10;
            }
        }
        if ($score > 0) {
            $scored[] = ['score' => $score, 'row' => $row];
        }
    }

    usort($scored, static fn (array $a, array $b): int => ($b['score'] <=> $a['score']));

    $out = [];
    foreach ($scored as $item) {
        $out[] = $item['row'];
        if (count($out) >= $limit) {
            break;
        }
    }

    return $out;
}

/**
 * @param list<array{id: int, judul: string, ringkasan: string, tanggal: string, kategori: string, source: string}> $catalog
 * @param list<string> $terms
 * @return list<array{id: int, judul: string, ringkasan: string, tanggal: string, kategori: string, source: string}>
 */
function org_ai_chat_search_publikasi_db(mysqli $db, array $terms, string $kategoriFilter, int $limit = 5): array
{
    if ($terms === [] || !org_pusat_informasi_table_exists($db)) {
        return [];
    }

    org_pusat_informasi_ensure_table($db);
    $clauses = [];
    $types = '';
    $params = [];

    foreach ($terms as $term) {
        $term = mb_substr($term, 0, 80);
        if ($term === '') {
            continue;
        }
        $like = '%' . $term . '%';
        $clauses[] = '(`judul` LIKE ? OR `isi_teks` LIKE ?)';
        $types .= 'ss';
        $params[] = $like;
        $params[] = $like;
    }

    if ($clauses === []) {
        return [];
    }

    $sql = 'SELECT `id`, `judul`, `kategori`, `isi_teks`, `created_at` FROM `pusat_informasi` WHERE ('
        . implode(' OR ', $clauses) . ')';
    if ($kategoriFilter === 'berita' || $kategoriFilter === 'pengumuman') {
        $sql .= ' AND `kategori` = ?';
        $types .= 's';
        $params[] = $kategoriFilter;
    }
    $sql .= ' ORDER BY `created_at` DESC LIMIT ' . (int) $limit;

    $st = $db->prepare($sql);
    if ($st === false) {
        org_ai_chat_note_db_error('publikasi search prepare failed', (string) $db->error);
        return [];
    }

    org_ai_chat_stmt_bind($st, $types, $params);
    if (!$st->execute()) {
        org_ai_chat_note_db_error('publikasi search execute failed', (string) $st->error);
        $st->close();
        return [];
    }

    $result = $st->get_result();
    $st->close();
    if ($result === false) {
        return [];
    }

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        if (!is_array($row)) {
            continue;
        }
        $judul = org_sanitize_plain(trim((string) ($row['judul'] ?? '')));
        if ($judul === '') {
            continue;
        }
        $kat = strtolower(trim((string) ($row['kategori'] ?? 'berita')));
        if ($kat !== 'pengumuman') {
            $kat = 'berita';
        }
        $rows[] = [
            'id' => (int) ($row['id'] ?? 0),
            'judul' => $judul,
            'ringkasan' => org_ai_chat_publikasi_excerpt((string) ($row['isi_teks'] ?? '')),
            'tanggal' => (string) ($row['created_at'] ?? ''),
            'kategori' => $kat,
            'source' => 'pusat_informasi',
        ];
    }

    return $rows;
}

/**
 * @param list<array{id: int, judul: string, ringkasan: string, tanggal: string, kategori: string, source: string}> $rows
 * @return list<array{title: string, description: string, link: string}>
 */
function org_ai_chat_format_publikasi_results(array $rows, string $prefix): array
{
    $results = [];
    foreach ($rows as $row) {
        $id = (int) ($row['id'] ?? 0);
        $title = org_sanitize_plain((string) ($row['judul'] ?? ''));
        if ($id <= 0 || $title === '') {
            continue;
        }

        $kat = (string) ($row['kategori'] ?? 'berita');
        $typeLabel = $kat === 'pengumuman' ? 'Pengumuman' : 'Berita';
        $descParts = [];
        $tanggal = trim((string) ($row['tanggal'] ?? ''));
        if ($tanggal !== '') {
            $ts = strtotime($tanggal);
            $descParts[] = $ts !== false ? date('d M Y', $ts) : $tanggal;
        }
        $ringkasan = (string) ($row['ringkasan'] ?? '');
        if ($ringkasan !== '') {
            $descParts[] = $ringkasan;
        }
        if ($descParts === []) {
            $descParts[] = $typeLabel . ' Bagian Organisasi';
        }

        $source = (string) ($row['source'] ?? 'pusat_informasi');
        $link = $source === 'pusat_informasi'
            ? $prefix . '/informasi.php?id=' . $id
            : $prefix . '/berita.php';

        $results[] = org_ai_chat_result_item($typeLabel, $title, implode(' · ', $descParts), $link);
        if (count($results) >= 5) {
            break;
        }
    }

    return $results;
}

/**
 * @return array{success: bool, answer: string, results: list<array{title: string, description?: string, link: string}>}
 */
function org_ai_chat_resolve_publikasi(mysqli $db, string $message): array
{
    $prefix = org_ai_chat_url_prefix();
    $katFilter = org_ai_chat_publikasi_kategori_filter($message);
    $catalog = org_ai_chat_fetch_publikasi_catalog($db, $katFilter);

    $beritaLink = $prefix . '/berita.php';

    if ($catalog === []) {
        return org_ai_chat_build_response(
            [
                org_ai_chat_result_item('Pengumuman', 'Pusat Informasi & Pengumuman', 'Arsip berita dan pengumuman', $beritaLink),
            ],
            false,
            null,
            'pengumuman',
            org_ai_chat_extract_display_keyword($message)
        );
    }

    $isGeneral = org_ai_chat_is_publikasi_general_question($message);
    $terms = org_ai_chat_extract_publikasi_search_terms($message);
    $keyword = org_ai_chat_extract_display_keyword($message, $terms);
    $picked = [];

    if ($isGeneral || $terms === []) {
        $picked = array_slice($catalog, 0, 5);
    } else {
        $picked = org_ai_chat_search_publikasi_db($db, $terms, $katFilter, 5);
        if ($picked === []) {
            $picked = org_ai_chat_search_publikasi_catalog($catalog, $terms, 5);
        }
    }

    if ($picked === []) {
        return org_ai_chat_build_response(
            [
                org_ai_chat_result_item('Pengumuman', 'Pusat Informasi & Pengumuman', 'Arsip berita dan pengumuman', $beritaLink),
            ],
            false,
            null,
            'pengumuman',
            $keyword
        );
    }

    $results = org_ai_chat_filter_results_by_type(
        org_ai_chat_format_publikasi_results($picked, $prefix),
        'Pengumuman'
    );

    if ($results === []) {
        return org_ai_chat_build_response(
            [
                org_ai_chat_result_item('Pengumuman', 'Pusat Informasi & Pengumuman', 'Arsip berita dan pengumuman', $beritaLink),
            ],
            false,
            null,
            'pengumuman',
            $keyword
        );
    }

    return org_ai_chat_build_response($results, true, null, 'pengumuman', $keyword);
}

function org_ai_chat_is_layanan_intent(string $message): bool
{
    $lower = mb_strtolower(org_sanitize_plain($message));
    if (org_ai_chat_is_personel_intent($message)) {
        return false;
    }
    if (org_ai_chat_is_dokumen_intent($message)) {
        return false;
    }
    if (org_ai_chat_is_publikasi_intent($message)) {
        return false;
    }
    $keywords = ['layanan', 'pelayanan', 'ajukan', 'permohonan', 'konsultasi', 'fasilitasi'];
    foreach ($keywords as $keyword) {
        if (str_contains($lower, $keyword)) {
            return true;
        }
    }

    return false;
}

function org_ai_chat_is_layanan_general_question(string $message): bool
{
    $lower = mb_strtolower(org_sanitize_plain($message));
    $patterns = [
        'layanan apa',
        'apa saja layanan',
        'daftar layanan',
        'layanan publik',
        'layanan yang ada',
        'layanan organisasi',
        'macam layanan',
        'jenis layanan',
        'info layanan',
        'informasi layanan',
        'layanan tersedia',
        'ada layanan',
    ];
    foreach ($patterns as $pattern) {
        if (str_contains($lower, $pattern)) {
            return true;
        }
    }

    return (bool) preg_match('/\b(apa|daftar|macam|jenis)\b.*\blayanan\b/u', $lower)
        || (bool) preg_match('/\blayanan\b.*\b(apa|ada|tersedia|publik)\b/u', $lower);
}

/**
 * @return list<string>
 */
function org_ai_chat_extract_layanan_search_terms(string $message): array
{
    $lower = mb_strtolower(org_sanitize_plain($message));
    $tokens = preg_split('/\s+/u', $lower, -1, PREG_SPLIT_NO_EMPTY);
    if (!is_array($tokens)) {
        return [];
    }

    $stop = [
        'layanan', 'pelayanan', 'publik', 'organisasi', 'apa', 'saja', 'yang', 'ada', 'untuk',
        'saya', 'mau', 'ajukan', 'permohonan', 'konsultasi', 'fasilitasi', 'informasi', 'tentang',
        'di', 'ke', 'dari', 'dan', 'atau', 'bagaimana', 'caranya', 'bisa', 'tolong', 'mohon',
        'ingin', 'akan', 'ini', 'itu', 'dengan', 'adalah', 'tersebut', 'semua', 'beberapa',
        'daftar', 'macam', 'jenis', 'tersedia', 'terkait', 'lebih', 'lanjut', 'halaman',
    ];

    $terms = [];
    foreach ($tokens as $tok) {
        $tok = trim((string) $tok);
        if ($tok === '' || in_array($tok, $stop, true)) {
            continue;
        }
        if (mb_strlen($tok) < 2) {
            continue;
        }
        $terms[] = $tok;
    }

    return array_values(array_unique($terms));
}

/**
 * @return array{name: string, desc: string}|null
 */
function org_ai_chat_layanan_column_map(mysqli $db): ?array
{
    $res = $db->query('SHOW COLUMNS FROM `layanan`');
    if ($res === false) {
        return null;
    }

    $fields = [];
    while ($row = $res->fetch_assoc()) {
        if (is_array($row) && isset($row['Field'])) {
            $fields[(string) $row['Field']] = true;
        }
    }

    if ($fields === []) {
        return null;
    }

    $nameCol = null;
    foreach (['nama_layanan', 'nama', 'judul', 'title'] as $candidate) {
        if (isset($fields[$candidate])) {
            $nameCol = $candidate;
            break;
        }
    }

    $descCol = null;
    foreach (['deskripsi', 'keterangan', 'description'] as $candidate) {
        if (isset($fields[$candidate])) {
            $descCol = $candidate;
            break;
        }
    }

    if ($nameCol === null || !isset($fields['id'])) {
        return null;
    }

    return [
        'name' => $nameCol,
        'desc' => $descCol ?? '',
    ];
}

function org_ai_chat_layanan_table_exists(mysqli $db): bool
{
    $res = $db->query("SHOW TABLES LIKE 'layanan'");
    return $res !== false && $res->num_rows > 0;
}

/**
 * @param list<string> $types
 * @param list<string|int> $params
 */
function org_ai_chat_stmt_bind(mysqli_stmt $st, string $types, array $params): void
{
    $bindRefs = array_merge([$types], $params);
    $bindArgs = [];
    foreach ($bindRefs as $key => $value) {
        $bindArgs[$key] = &$bindRefs[$key];
    }
    call_user_func_array([$st, 'bind_param'], $bindArgs);
}

/**
 * @return list<array{id: int, nama: string, deskripsi: string}>
 */
function org_ai_chat_load_layanan_from_db(mysqli $db): array
{
    if (!org_ai_chat_layanan_table_exists($db)) {
        return [];
    }

    $map = org_ai_chat_layanan_column_map($db);
    if ($map === null) {
        return [];
    }

    $nameCol = $map['name'];
    $descCol = $map['desc'];
    $descSelect = $descCol !== '' ? ', `' . $descCol . '`' : '';
    $sql = 'SELECT `id`, `' . $nameCol . '` AS `nama`' . $descSelect
        . ' FROM `layanan` ORDER BY `id` DESC LIMIT 200';
    $res = $db->query($sql);
    if ($res === false) {
        org_ai_chat_note_db_error('layanan list query failed', (string) $db->error);
        return [];
    }

    $rows = [];
    while ($row = $res->fetch_assoc()) {
        if (!is_array($row)) {
            continue;
        }
        $nama = org_sanitize_plain((string) ($row['nama'] ?? ''));
        if ($nama === '') {
            continue;
        }
        $rows[] = [
            'id' => (int) ($row['id'] ?? 0),
            'nama' => $nama,
            'deskripsi' => org_sanitize_plain((string) ($row[$descCol] ?? $row['deskripsi'] ?? '')),
        ];
    }

    return $rows;
}

/**
 * @return list<array{id: int, nama: string, deskripsi: string}>
 */
function org_ai_chat_load_layanan_from_json(): array
{
    if (!defined('ORG_ROOT')) {
        define('ORG_ROOT', __DIR__);
    }

    $path = ORG_ROOT . DIRECTORY_SEPARATOR . 'layanan_data.json';
    if (!is_file($path)) {
        return [];
    }

    $raw = file_get_contents($path);
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $parsed = json_decode($raw, true);
    if (!is_array($parsed)) {
        return [];
    }

    $rows = [];
    $id = 0;
    foreach ($parsed as $entry) {
        if (!is_array($entry)) {
            continue;
        }
        $nama = org_sanitize_plain(trim((string) ($entry['nama'] ?? '')));
        $desk = org_sanitize_plain(trim((string) ($entry['deskripsi'] ?? '')));
        $img = trim((string) ($entry['media_image'] ?? ''));
        $link = trim((string) ($entry['link'] ?? ''));
        $docs = [];
        if (isset($entry['media_documents']) && is_array($entry['media_documents'])) {
            foreach ($entry['media_documents'] as $docItem) {
                if (is_string($docItem) && trim($docItem) !== '') {
                    $docs[] = trim($docItem);
                }
            }
        }
        $doc = trim((string) ($entry['media_document'] ?? ''));
        if ($doc !== '' && !in_array($doc, $docs, true)) {
            $docs[] = $doc;
        }
        if ($nama === '' && $desk === '' && $img === '' && $docs === [] && $link === '') {
            continue;
        }
        $id++;
        $rows[] = [
            'id' => $id,
            'nama' => $nama !== '' ? $nama : 'Layanan',
            'deskripsi' => $desk,
        ];
    }

    return $rows;
}

/**
 * @return list<array{id: int, nama: string, deskripsi: string}>
 */
function org_ai_chat_fetch_layanan_catalog(?mysqli $db): array
{
    $fromJson = org_ai_chat_load_layanan_from_json();
    if ($db instanceof mysqli && org_ai_chat_layanan_table_exists($db)) {
        $fromDb = org_ai_chat_load_layanan_from_db($db);
        if ($fromDb !== []) {
            return $fromDb;
        }
    }

    return $fromJson;
}

/**
 * @param list<array{id: int, nama: string, deskripsi: string}> $catalog
 * @return list<array{id: int, nama: string, deskripsi: string}>
 */
function org_ai_chat_layanan_latest(array $catalog, int $limit = 5): array
{
    if ($catalog === []) {
        return [];
    }

    $sorted = $catalog;
    usort($sorted, static function (array $a, array $b): int {
        return ((int) ($b['id'] ?? 0)) <=> ((int) ($a['id'] ?? 0));
    });

    return array_slice($sorted, 0, $limit);
}

/**
 * @param list<array{id: int, nama: string, deskripsi: string}> $catalog
 * @param list<string> $terms
 * @return list<array{id: int, nama: string, deskripsi: string}>
 */
function org_ai_chat_filter_layanan_catalog(array $catalog, array $terms, int $limit = 5): array
{
    if ($catalog === [] || $terms === []) {
        return [];
    }

    $scored = [];
    foreach ($catalog as $row) {
        $namaHay = mb_strtolower((string) ($row['nama'] ?? ''));
        $descHay = mb_strtolower((string) ($row['deskripsi'] ?? ''));
        $score = 0;
        foreach ($terms as $term) {
            $term = mb_substr((string) $term, 0, 80);
            if ($term === '') {
                continue;
            }
            if (org_ai_chat_term_matches_haystack($namaHay, $term)) {
                $score += 60;
            } elseif ($descHay !== '' && org_ai_chat_term_matches_haystack($descHay, $term)) {
                $score += 15;
            }
        }
        if ($score > 0) {
            $scored[] = ['score' => $score, 'row' => $row];
        }
    }

    usort($scored, static fn (array $a, array $b): int => ($b['score'] <=> $a['score']));

    $out = [];
    foreach ($scored as $item) {
        $out[] = $item['row'];
        if (count($out) >= $limit) {
            break;
        }
    }

    return $out;
}

/**
 * @param list<array{id: int, nama: string, deskripsi: string}> $catalog
 * @param list<string> $terms
 * @return list<array{id: int, nama: string, deskripsi: string}>
 */
function org_ai_chat_search_layanan_db(mysqli $db, array $terms, int $limit = 5): array
{
    if ($terms === [] || !org_ai_chat_layanan_table_exists($db)) {
        return [];
    }

    $map = org_ai_chat_layanan_column_map($db);
    if ($map === null) {
        return [];
    }

    $nameCol = $map['name'];
    $descCol = $map['desc'];
    $clauses = [];
    $types = '';
    $params = [];

    foreach ($terms as $term) {
        $term = mb_substr($term, 0, 80);
        if ($term === '') {
            continue;
        }
        $like = '%' . $term . '%';
        if ($descCol !== '') {
            $clauses[] = '(`' . $nameCol . '` LIKE ? OR `' . $descCol . '` LIKE ?)';
            $types .= 'ss';
            $params[] = $like;
            $params[] = $like;
        } else {
            $clauses[] = '`' . $nameCol . '` LIKE ?';
            $types .= 's';
            $params[] = $like;
        }
    }

    if ($clauses === []) {
        return [];
    }

    $descSelect = $descCol !== '' ? ', `' . $descCol . '`' : '';
    $sql = 'SELECT `id`, `' . $nameCol . '` AS `nama`' . $descSelect
        . ' FROM `layanan` WHERE (' . implode(' OR ', $clauses) . ')'
        . ' ORDER BY `id` DESC LIMIT ' . (int) $limit;

    $st = $db->prepare($sql);
    if ($st === false) {
        org_ai_chat_note_db_error('layanan search prepare failed', (string) $db->error);
        return [];
    }

    org_ai_chat_stmt_bind($st, $types, $params);
    if (!$st->execute()) {
        org_ai_chat_note_db_error('layanan search execute failed', (string) $st->error);
        $st->close();
        return [];
    }

    $result = $st->get_result();
    $st->close();
    if ($result === false) {
        return [];
    }

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        if (!is_array($row)) {
            continue;
        }
        $nama = org_sanitize_plain((string) ($row['nama'] ?? ''));
        if ($nama === '') {
            continue;
        }
        $rows[] = [
            'id' => (int) ($row['id'] ?? 0),
            'nama' => $nama,
            'deskripsi' => org_sanitize_plain((string) ($row[$descCol] ?? $row['deskripsi'] ?? '')),
        ];
    }

    return $rows;
}

/**
 * @param list<array{id: int, nama: string, deskripsi: string}> $rows
 * @return list<array{title: string, description: string, link: string}>
 */
function org_ai_chat_format_layanan_results(array $rows, string $prefix): array
{
    $results = [];
    foreach ($rows as $row) {
        $id = (int) ($row['id'] ?? 0);
        $title = org_sanitize_plain((string) ($row['nama'] ?? ''));
        if ($id <= 0 || $title === '') {
            continue;
        }
        $desc = org_sanitize_plain((string) ($row['deskripsi'] ?? ''));
        if (mb_strlen($desc) > 180) {
            $desc = mb_substr($desc, 0, 177) . '...';
        }
        if ($desc === '') {
            $desc = 'Layanan publik Bagian Organisasi';
        }
        $results[] = org_ai_chat_result_item('Layanan', $title, $desc, $prefix . '/layanan.php?id=' . $id);
    }

    return $results;
}

/**
 * @return array{success: bool, answer: string, results: list<array{type: string, title: string, description: string, link: string}>}
 */
function org_ai_chat_layanan_unavailable_response(string $prefix, string $message): array
{
    return org_ai_chat_build_response(
        [
            org_ai_chat_result_item('Layanan', 'Halaman Layanan', 'Daftar layanan publik', $prefix . '/layanan.php'),
        ],
        false,
        null,
        'layanan',
        org_ai_chat_extract_display_keyword($message)
    );
}

/**
 * @return array{success: bool, answer: string, results: list<array{title: string, description?: string, link: string}>}
 */
function org_ai_chat_resolve_layanan(?mysqli $db, string $message): array
{
    $prefix = org_ai_chat_url_prefix();
    $catalog = org_ai_chat_fetch_layanan_catalog($db);

    if ($catalog === []) {
        return org_ai_chat_layanan_unavailable_response($prefix, $message);
    }

    $isGeneral = org_ai_chat_is_layanan_general_question($message);
    $terms = org_ai_chat_extract_layanan_search_terms($message);
    $keyword = org_ai_chat_extract_display_keyword($message, $terms);

    if ($isGeneral || $terms === []) {
        $picked = org_ai_chat_layanan_latest($catalog, 5);
        $results = org_ai_chat_format_layanan_results($picked, $prefix);
        if ($results === []) {
            return org_ai_chat_layanan_unavailable_response($prefix, $message);
        }

        $results = org_ai_chat_filter_results_by_type($results, 'Layanan');

        return org_ai_chat_build_response($results, true, null, 'layanan', $keyword);
    }

    $picked = [];
    if ($db instanceof mysqli && org_ai_chat_layanan_table_exists($db)) {
        $picked = org_ai_chat_search_layanan_db($db, $terms, 5);
    }
    if ($picked === []) {
        $picked = org_ai_chat_filter_layanan_catalog($catalog, $terms, 5);
    }

    if ($picked === []) {
        return org_ai_chat_build_response(
            [
                org_ai_chat_result_item('Layanan', 'Halaman Layanan', 'Daftar layanan publik', $prefix . '/layanan.php'),
            ],
            false,
            null,
            'layanan',
            $keyword
        );
    }

    $results = org_ai_chat_filter_results_by_type(
        org_ai_chat_format_layanan_results($picked, $prefix),
        'Layanan'
    );

    return org_ai_chat_build_response($results, true, null, 'layanan', $keyword);
}

/**
 * @return list<string>
 */
function org_ai_chat_extract_cross_search_terms(string $message): array
{
    $merged = array_merge(
        org_ai_chat_extract_search_terms($message),
        org_ai_chat_extract_layanan_search_terms($message),
        org_ai_chat_extract_publikasi_search_terms($message)
    );

    $unique = [];
    foreach ($merged as $term) {
        $term = trim((string) $term);
        if ($term !== '') {
            $unique[$term] = true;
        }
    }

    $out = array_keys($unique);
    if ($out === []) {
        $lower = mb_strtolower(org_sanitize_plain($message));
        if (mb_strlen($lower) >= 3) {
            $out[] = mb_substr($lower, 0, 80);
        }
    }

    return $out;
}

/**
 * Gabungkan hasil lintas kategori (prioritas: Dokumen → Layanan → Pengumuman/Berita), tanpa duplikat link.
 *
 * @param list<array{type: string, title: string, description: string, link: string}> $documents
 * @param list<array{type: string, title: string, description: string, link: string}> $layanan
 * @param list<array{type: string, title: string, description: string, link: string}> $publikasi
 * @return list<array{type: string, title: string, description: string, link: string}>
 */
/**
 * @param list<array{type: string, title: string, description: string, link: string}> $results
 */
function org_ai_chat_infer_intent_from_results(array $results): string
{
    $types = org_ai_chat_collect_result_types($results);
    if ($types === []) {
        return 'cross';
    }

    if (count($types) === 1) {
        return match ($types[0]) {
            'Dokumen' => 'dokumen',
            'Layanan' => 'layanan',
            'Pengumuman', 'Berita' => 'pengumuman',
            'Personel' => 'personel',
            default => 'cross',
        };
    }

    return 'cross';
}

function org_ai_chat_merge_cross_results(array $documents, array $layanan, array $publikasi, int $max = 6): array
{
    $seenLinks = [];
    $out = [];

    foreach ([$documents, $layanan, $publikasi] as $group) {
        foreach ($group as $item) {
            if (!is_array($item)) {
                continue;
            }
            $link = trim((string) ($item['link'] ?? ''));
            if ($link === '' || isset($seenLinks[$link])) {
                continue;
            }
            $seenLinks[$link] = true;
            $out[] = $item;
            if (count($out) >= $max) {
                return $out;
            }
        }
    }

    return $out;
}

/**
 * Pencarian umum lintas dokumen, layanan, dan berita/pengumuman.
 *
 * @return array{success: bool, answer: string, results: list<array{type: string, title: string, description: string, link: string}>}
 */
function org_ai_chat_resolve_cross_search(mysqli $db, string $message): array
{
    $prefix = org_ai_chat_url_prefix();
    $terms = org_ai_chat_extract_cross_search_terms($message);
    $keyword = org_ai_chat_extract_display_keyword($message, $terms);

    $docRows = org_ai_chat_search_documents($db, $terms, 4, $message);
    $docResults = org_ai_chat_filter_results_by_type(
        org_ai_chat_format_document_results($docRows),
        'Dokumen'
    );

    $layananPicked = [];
    if ($db instanceof mysqli && org_ai_chat_layanan_table_exists($db)) {
        $layananPicked = org_ai_chat_search_layanan_db($db, $terms, 4);
    }
    if ($layananPicked === []) {
        $layananCatalog = org_ai_chat_fetch_layanan_catalog($db);
        if ($layananCatalog !== []) {
            $layananPicked = org_ai_chat_filter_layanan_catalog($layananCatalog, $terms, 4);
        }
    }
    $layananResults = org_ai_chat_filter_results_by_type(
        org_ai_chat_format_layanan_results($layananPicked, $prefix),
        'Layanan'
    );

    $pubPicked = org_ai_chat_search_publikasi_db($db, $terms, 'all', 4);
    if ($pubPicked === []) {
        $pubCatalog = org_ai_chat_fetch_publikasi_catalog($db, 'all');
        if ($pubCatalog !== []) {
            $pubPicked = org_ai_chat_search_publikasi_catalog($pubCatalog, $terms, 4);
        }
    }
    $pubResults = org_ai_chat_filter_results_by_type(
        org_ai_chat_format_publikasi_results($pubPicked, $prefix),
        'Pengumuman'
    );

    $merged = org_ai_chat_merge_cross_results($docResults, $layananResults, $pubResults, 6);

    if ($merged === []) {
        return org_ai_chat_build_response([], false, null, 'cross', $keyword);
    }

    $intent = org_ai_chat_infer_intent_from_results($merged);

    return org_ai_chat_build_response($merged, true, null, $intent, $keyword);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    org_ai_chat_reject('Metode tidak diizinkan.', 405);
}

org_session_start();

if (!org_ai_chat_rate_limit_allow()) {
    org_ai_chat_send_json([
        'success' => false,
        'answer' => 'Permintaan terlalu sering. Silakan coba lagi sebentar.',
        'results' => [],
    ], 429);
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') === false) {
    org_ai_chat_reject('Content-Type harus application/json.', 415);
}

$raw = file_get_contents('php://input');
if ($raw === false || trim($raw) === '') {
    org_ai_chat_reject('Body JSON kosong.', 400);
}

$decoded = json_decode($raw, true);
if (!is_array($decoded)) {
    org_ai_chat_reject('Format JSON tidak valid.', 400);
}

$message = org_ai_chat_sanitize_message((string) ($decoded['message'] ?? ''));
if ($message === '') {
    org_ai_chat_reject('Pertanyaan tidak boleh kosong.', 400);
}

if (mb_strlen($message) > ORG_AI_CHAT_MAX_MESSAGE_LEN) {
    org_ai_chat_send_json([
        'success' => false,
        'answer' => 'Pertanyaan terlalu panjang. Silakan tulis pertanyaan yang lebih singkat.',
        'results' => [],
    ], 400);
}

org_ai_chat_rate_limit_record();

org_ai_chat_reset_db_error();

try {
    $db = org_db();
    if ($db === null) {
        org_ai_chat_note_db_error('database connection unavailable');
        org_ai_chat_send_json(org_ai_chat_service_unavailable_response(), 503);
    }

    if (org_ai_chat_is_personel_intent($message)) {
        org_ai_chat_send_search_response(org_ai_chat_resolve_personel($message), $message);
    }

    if (org_ai_chat_is_dokumen_intent($message)) {
        org_ai_chat_send_search_response(org_ai_chat_resolve_dokumen($db, $message), $message);
    }

    if (org_ai_chat_is_layanan_intent($message)) {
        org_ai_chat_send_search_response(org_ai_chat_resolve_layanan($db, $message), $message);
    }

    if (org_ai_chat_is_publikasi_intent($message)) {
        org_ai_chat_send_search_response(org_ai_chat_resolve_publikasi($db, $message), $message);
    }

    if (org_ai_chat_should_use_cross_search($message)) {
        org_ai_chat_send_search_response(org_ai_chat_resolve_cross_search($db, $message), $message);
    }

    org_ai_chat_send_search_response(
        org_ai_chat_build_response([], false, null, 'cross', org_ai_chat_extract_display_keyword($message)),
        $message
    );
} catch (Throwable $e) {
    org_ai_chat_note_db_error('uncaught exception', $e->getMessage());
    org_ai_chat_send_json(org_ai_chat_service_unavailable_response(), 503);
}
