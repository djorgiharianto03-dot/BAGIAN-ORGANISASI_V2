<?php

/**
 * Cache sementara (JSON / flag) — hanya di uploads/.cache.
 * Bukan aset CSS/JS; aman jika folder dikosongkan (fallback DB + memori).
 */

function org_runtime_cache_dir(): string
{
    if (!defined('ORG_ROOT')) {
        define('ORG_ROOT', dirname(__DIR__));
    }
    $dir = ORG_ROOT . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . '.cache';

    return $dir;
}

function org_runtime_cache_ensure_dir(): bool
{
    $dir = org_runtime_cache_dir();
    if (is_dir($dir)) {
        return true;
    }
    $uploadRoot = ORG_ROOT . DIRECTORY_SEPARATOR . 'uploads';
    if (!is_dir($uploadRoot)) {
        @mkdir($uploadRoot, 0775, true);
    }
    if (!@mkdir($dir, 0775, true) && !is_dir($dir)) {
        return false;
    }

    return is_dir($dir);
}

function org_runtime_cache_is_writable(): bool
{
    if (!org_runtime_cache_ensure_dir()) {
        return false;
    }
    $dir = org_runtime_cache_dir();

    return is_writable($dir);
}

function org_runtime_cache_file_path(string $filename): string
{
    $safe = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename) ?? '';

    return org_runtime_cache_dir() . DIRECTORY_SEPARATOR . ($safe !== '' ? $safe : 'invalid.cache');
}

/**
 * @return array<string, mixed>|null
 */
function org_runtime_cache_read_json(string $filename, int $ttlSeconds): ?array
{
    if ($ttlSeconds < 1) {
        return null;
    }
    $path = org_runtime_cache_file_path($filename);
    if (!is_file($path)) {
        return null;
    }
    $age = time() - (int) filemtime($path);
    if ($age < 0 || $age >= $ttlSeconds) {
        return null;
    }
    $raw = @file_get_contents($path);
    if ($raw === false || $raw === '') {
        return null;
    }
    try {
        $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        return null;
    }

    return is_array($decoded) ? $decoded : null;
}

/**
 * @param array<string, mixed>|list<mixed> $payload
 */
function org_runtime_cache_write_json(string $filename, array $payload): bool
{
    if (!org_runtime_cache_is_writable()) {
        return false;
    }
    $path = org_runtime_cache_file_path($filename);
    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        return false;
    }

    return @file_put_contents($path, $json, LOCK_EX) !== false;
}

function org_runtime_cache_read_text(string $filename, int $ttlSeconds): ?string
{
    if ($ttlSeconds < 1) {
        return null;
    }
    $path = org_runtime_cache_file_path($filename);
    if (!is_file($path)) {
        return null;
    }
    $age = time() - (int) filemtime($path);
    if ($age < 0 || $age >= $ttlSeconds) {
        return null;
    }
    $raw = @file_get_contents($path);
    if ($raw === false) {
        return null;
    }
    $text = trim($raw);

    return $text !== '' ? $text : null;
}

function org_runtime_cache_write_text(string $filename, string $text): bool
{
    if (!org_runtime_cache_is_writable()) {
        return false;
    }

    return @file_put_contents(org_runtime_cache_file_path($filename), $text, LOCK_EX) !== false;
}

/**
 * Jalankan callback sekali per TTL (file flag + memori request).
 */
function org_runtime_cache_run_once(string $key, callable $fn, int $ttlSeconds = 86400): void
{
    static $doneRequest = [];
    $norm = preg_replace('/[^a-z0-9_]/', '', strtolower($key)) ?? '';
    if ($norm === '') {
        return;
    }
    if (!empty($doneRequest[$norm])) {
        return;
    }

    $flag = org_runtime_cache_file_path('once_' . $norm . '.ok');
    if (is_file($flag)) {
        $age = time() - (int) filemtime($flag);
        if ($age >= 0 && $age < $ttlSeconds) {
            $doneRequest[$norm] = true;

            return;
        }
    }

    try {
        $fn();
    } catch (Throwable) {
        return;
    }

    $doneRequest[$norm] = true;
    if (org_runtime_cache_is_writable()) {
        @touch($flag);
    }
}
