<?php
declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'openai.php';

const ORG_AI_CHAT_OPENAI_SYSTEM_INSTRUCTION = <<<'TEXT'
Kamu adalah Asisten Smart Governance Portal Bagian Organisasi Setda Kabupaten Kepulauan Aru. Jawab hanya berdasarkan data portal yang diberikan. Jangan mengarang. Jika data tidak tersedia, katakan bahwa informasi belum ditemukan di portal. Gunakan bahasa Indonesia formal, singkat, ramah, dan jelas. Jika ada link, arahkan pengguna untuk membuka link yang tersedia.
TEXT;

/**
 * @param list<array{type: string, title: string, description: string, link: string}> $results
 */
function org_ai_chat_openai_format_results_context(array $results): string
{
    if ($results === []) {
        return '(tidak ada hasil)';
    }

    $lines = [];
    $n = 1;
    foreach ($results as $row) {
        if (!is_array($row)) {
            continue;
        }
        $type = org_sanitize_plain((string) ($row['type'] ?? ''));
        $title = org_sanitize_plain((string) ($row['title'] ?? ''));
        $desc = org_sanitize_plain((string) ($row['description'] ?? ''));
        $link = trim((string) ($row['link'] ?? ''));
        $lines[] = $n . '. [' . $type . '] ' . $title . ' - ' . $desc . ' - Link: ' . $link;
        $n++;
    }

    return $lines === [] ? '(tidak ada hasil)' : implode("\n", $lines);
}

/**
 * @param list<array{type: string, title: string, description: string, link: string}> $results
 */
function org_ai_chat_openai_build_user_prompt(string $userMessage, string $intent, array $results): string
{
    return "Buat satu jawaban singkat (maksimal 3 kalimat) untuk ditampilkan di chatbot portal.\n"
        . "Hanya gunakan informasi dari daftar hasil database di bawah. Jangan menambah fakta, URL, atau link baru.\n"
        . "Jangan menuliskan daftar bernomor; cukup paragraf pembuka yang merangkum temuan dan mengajak pengguna membuka kartu hasil di bawah jawaban.\n\n"
        . "Pertanyaan user: " . $userMessage . "\n"
        . "Intent: " . $intent . "\n"
        . "Hasil database:\n"
        . org_ai_chat_openai_format_results_context($results);
}

function org_ai_chat_openai_extract_answer_text(array $decoded): ?string
{
    if (isset($decoded['output_text']) && is_string($decoded['output_text'])) {
        $text = trim($decoded['output_text']);

        return $text !== '' ? $text : null;
    }

    if (!isset($decoded['output']) || !is_array($decoded['output'])) {
        return null;
    }

    foreach ($decoded['output'] as $item) {
        if (!is_array($item)) {
            continue;
        }
        $type = (string) ($item['type'] ?? '');
        if ($type === 'message' && isset($item['content']) && is_array($item['content'])) {
            foreach ($item['content'] as $part) {
                if (!is_array($part)) {
                    continue;
                }
                if (($part['type'] ?? '') === 'output_text' && isset($part['text'])) {
                    $text = trim((string) $part['text']);
                    if ($text !== '') {
                        return $text;
                    }
                }
            }
        }
    }

    return null;
}

/**
 * @param list<array{type: string, title: string, description: string, link: string}> $results
 */
function org_ai_chat_openai_polish_answer(
    string $userMessage,
    string $intent,
    array $results,
    string $fallbackAnswer
): ?string {
    $cfg = org_openai_config();
    if (!$cfg['enabled']) {
        return null;
    }

    $payload = [
        'model' => $cfg['model'],
        'instructions' => ORG_AI_CHAT_OPENAI_SYSTEM_INSTRUCTION,
        'input' => org_ai_chat_openai_build_user_prompt($userMessage, $intent, $results),
        'max_output_tokens' => 320,
        'temperature' => 0.35,
    ];

    $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
    if ($body === false) {
        error_log('[ai_chat] openai json encode failed');

        return null;
    }

    $ch = curl_init($cfg['endpoint']);
    if ($ch === false) {
        error_log('[ai_chat] openai curl init failed');

        return null;
    }

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => (int) $cfg['timeout'],
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $cfg['api_key'],
        ],
        CURLOPT_POSTFIELDS => $body,
    ]);

    $raw = curl_exec($ch);
    $curlErr = curl_error($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($raw === false || $curlErr !== '') {
        error_log('[ai_chat] openai curl error: ' . $curlErr);

        return null;
    }

    $decoded = json_decode((string) $raw, true);
    if (!is_array($decoded)) {
        error_log('[ai_chat] openai invalid json, http=' . $httpCode);

        return null;
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        $errMsg = (string) ($decoded['error']['message'] ?? $decoded['message'] ?? 'HTTP ' . $httpCode);
        error_log('[ai_chat] openai api error: ' . $errMsg);

        return null;
    }

    $text = org_ai_chat_openai_extract_answer_text($decoded);
    if ($text === null || $text === '') {
        error_log('[ai_chat] openai empty output text');

        return null;
    }

    if (mb_strlen($text) > 600) {
        $text = mb_substr($text, 0, 597) . '...';
    }

    return $text;
}

/**
 * @param array{success: bool, answer: string, intent?: string, results_count?: int, results: list<array{type: string, title: string, description: string, link: string}>, openai_polish?: bool} $response
 * @return array{success: bool, answer: string, intent: string, results_count: int, results: list<array{type: string, title: string, description: string, link: string}>}
 */
function org_ai_chat_polish_response_with_openai(array $response, string $userMessage): array
{
    $allowPolish = !isset($response['openai_polish']) || $response['openai_polish'] !== false;
    unset($response['openai_polish']);

    $results = $response['results'] ?? [];
    $count = (int) ($response['results_count'] ?? count($results));
    $intent = (string) ($response['intent'] ?? 'general');
    $fallback = trim((string) ($response['answer'] ?? ''));

    if (!$allowPolish || $count === 0 || $results === []) {
        return $response;
    }

    $polished = org_ai_chat_openai_polish_answer($userMessage, $intent, $results, $fallback);
    if ($polished !== null && $polished !== '') {
        $response['answer'] = $polished;
    }

    return $response;
}

/**
 * @param array{success: bool, answer: string, intent?: string, results_count?: int, results: list<mixed>, openai_polish?: bool} $response
 */
function org_ai_chat_send_search_response(array $response, string $userMessage): void
{
    $response = org_ai_chat_apply_db_error_response($response);
    if (!empty($GLOBALS['org_ai_chat_db_failed'])) {
        unset($response['openai_polish']);
        org_ai_chat_send_json($response, 503);

        return;
    }

    $response = org_ai_chat_polish_response_with_openai($response, $userMessage);
    org_ai_chat_send_json($response, 200);
}
