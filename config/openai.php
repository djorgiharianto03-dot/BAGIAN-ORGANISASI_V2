<?php

/**
 * Konfigurasi OpenAI — API key dari env OPENAI_API_KEY atau config/openai.local.php (gitignored).
 *
 * @return array{api_key: string, enabled: bool, model: string, endpoint: string, timeout: int}
 */
function org_openai_config(): array
{
    static $cached = null;
    if (is_array($cached)) {
        return $cached;
    }

    org_openai_load_dotenv();

    $apiKey = getenv('OPENAI_API_KEY');
    if ($apiKey === false || trim((string) $apiKey) === '') {
        $localPath = __DIR__ . DIRECTORY_SEPARATOR . 'openai.local.php';
        if (is_file($localPath)) {
            $local = require $localPath;
            if (is_array($local)) {
                $apiKey = (string) ($local['api_key'] ?? $local['OPENAI_API_KEY'] ?? '');
            }
        }
    }

    $apiKey = trim((string) ($apiKey ?: ''));

    $cached = [
        'api_key' => $apiKey,
        'enabled' => $apiKey !== '',
        'model' => 'gpt-4.1-mini',
        'endpoint' => 'https://api.openai.com/v1/responses',
        'timeout' => 10,
    ];

    return $cached;
}

function org_openai_load_dotenv(): void
{
    static $loaded = false;
    if ($loaded) {
        return;
    }
    $loaded = true;

    $envPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
    if (!is_readable($envPath)) {
        return;
    }

    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim((string) $line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if ($key === '' || getenv($key) !== false) {
            continue;
        }
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
    }
}
