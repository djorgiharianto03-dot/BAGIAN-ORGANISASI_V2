<?php

/**
 * Partial include — cegah akses langsung (plain text di browser).
 */
function org_partial_deny_direct(): void
{
    if (PHP_SAPI === 'cli') {
        return;
    }
    $script = (string) ($_SERVER['SCRIPT_FILENAME'] ?? '');
    if ($script === '') {
        return;
    }
    $run = realpath($script);
    if ($run === false) {
        return;
    }
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
    $caller = (string) ($trace[0]['file'] ?? '');
    if ($caller === '') {
        return;
    }
    $callerPath = realpath($caller);
    if ($callerPath !== false && $run === $callerPath) {
        http_response_code(404);
        exit;
    }
}

/**
 * @param non-empty-string $relativePath Path di bawah includes/partials/
 */
function org_require_partial(string $relativePath): void
{
    if (!defined('ORG_ROOT')) {
        define('ORG_ROOT', dirname(__DIR__));
    }
    $relativePath = str_replace(['\\', "\0"], ['/', ''], $relativePath);
    if ($relativePath === '' || str_contains($relativePath, '..')) {
        return;
    }
    $full = ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials'
        . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    if (!is_file($full)) {
        return;
    }
    require $full;
}
