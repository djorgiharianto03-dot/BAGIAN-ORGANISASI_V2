<?php

/**
 * @return array<string, string>|null
 */
/**
 * Bootstrap DB otomatis (index.php) hanya di lingkungan dev: localhost, Laragon/XAMPP,
 * atau ORG_DEV_BOOTSTRAP=1 di environment server.
 */
function org_is_dev_environment(): bool
{
    $env = getenv('ORG_DEV_BOOTSTRAP');
    if ($env === '1' || strtolower((string) $env) === 'true') {
        return true;
    }
    if ($env === '0' || strtolower((string) $env) === 'false') {
        return false;
    }
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    if ($host === 'localhost' || str_starts_with($host, '127.0.0.1') || str_starts_with($host, 'localhost:')) {
        return true;
    }
    foreach (['.test', '.local', '.localhost', '.dev', '.invalid'] as $suffix) {
        $len = strlen($suffix);
        if ($len > 0 && strlen($host) > $len && substr($host, -$len) === $suffix) {
            return true;
        }
    }
    $root = strtolower(str_replace('\\', '/', dirname(__DIR__)));
    if (str_contains($root, '/laragon/') || str_contains($root, '/xampp/')) {
        return true;
    }

    return false;
}

function org_db_config(): ?array
{
    $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php';
    if (!is_file($path)) {
        return null;
    }
    $cfg = require $path;
    return is_array($cfg) ? $cfg : null;
}

function org_db(): ?mysqli
{
    static $conn = null;
    static $failed = false;
    if ($failed) {
        return null;
    }
    if ($conn instanceof mysqli) {
        return $conn;
    }
    $cfg = org_db_config();
    if ($cfg === null) {
        $failed = true;
        return null;
    }
    $host = (string) ($cfg['host'] ?? '127.0.0.1');
    $user = (string) ($cfg['user'] ?? 'root');
    $pass = (string) ($cfg['password'] ?? '');
    $db = (string) ($cfg['database'] ?? '');
    $charset = (string) ($cfg['charset'] ?? 'utf8mb4');
    if ($db === '') {
        $failed = true;
        return null;
    }
    mysqli_report(MYSQLI_REPORT_OFF);
    $mysqli = mysqli_init();
    if ($mysqli === false) {
        $failed = true;
        return null;
    }
    if (!$mysqli->real_connect($host, $user, $pass, $db)) {
        $failed = true;
        return null;
    }
    if (!$mysqli->set_charset($charset)) {
        $mysqli->close();
        $failed = true;
        return null;
    }
    $conn = $mysqli;
    return $conn;
}

function org_site_content_table_exists(mysqli $db): bool
{
    $r = $db->query("SHOW TABLES LIKE 'site_content'");
    return $r !== false && $r->num_rows > 0;
}

/**
 * Path URL ke akar situs (mis. '' atau '/subfolder'), untuk link, cookie sesi, dan fetch dari /admin.
 * Prioritas: config web_root → DOCUMENT_ROOT → SCRIPT_NAME (dinormalisasi, termasuk %20).
 */
function org_site_web_root(): string
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    $normalize = static function (string $path): string {
        $path = str_replace('\\', '/', trim(rawurldecode($path)));
        $path = rtrim($path, '/');
        if ($path === '' || $path === '/' || $path === '.') {
            return '';
        }
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }

        return $path;
    };

    $cfg = org_db_config();
    if (is_array($cfg) && array_key_exists('web_root', $cfg)) {
        $cached = $normalize((string) $cfg['web_root']);

        return $cached;
    }

    $fromScript = '';
    $sn = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', (string) $_SERVER['SCRIPT_NAME']) : '';
    $sn = rawurldecode($sn);
    if ($sn !== '' && preg_match('#^(.+)/admin/[^/]+\.php$#iu', $sn, $m)) {
        $fromScript = (string) $m[1];
    } elseif ($sn !== '' && preg_match('#^/admin/[^/]+\.php$#iu', $sn)) {
        $fromScript = '';
    } elseif ($sn !== '' && $sn !== '/') {
        $fromScript = dirname($sn);
    }
    $fromScript = $normalize($fromScript);

    $fromDocRoot = '';
    $docRaw = $_SERVER['DOCUMENT_ROOT'] ?? '';
    if ($docRaw !== '') {
        $orgRootPath = defined('ORG_ROOT')
            ? ORG_ROOT
            : dirname(__DIR__);
        $doc = realpath(str_replace('\\', '/', (string) $docRaw));
        $org = realpath(str_replace('\\', '/', (string) $orgRootPath));
        if (is_string($doc) && is_string($org)) {
            $docNorm = rtrim(str_replace('\\', '/', $doc), '/');
            $orgNorm = rtrim(str_replace('\\', '/', $org), '/');
            if ($orgNorm === $docNorm) {
                $fromDocRoot = '';
            } elseif (str_starts_with(strtolower($orgNorm), strtolower($docNorm . '/'))) {
                $fromDocRoot = $normalize(substr($orgNorm, strlen($docNorm)));
            }
        }
    }

    if ($fromDocRoot !== '') {
        $cached = $fromDocRoot;
    } else {
        $cached = $fromScript;
    }

    return $cached;
}

function org_proses_saran_url(): string
{
    if (function_exists('org_page_url')) {
        return org_page_url('proses_saran.php');
    }
    $b = org_site_web_root();

    return ($b === '' ? '' : $b) . '/proses_saran';
}
