<?php

if (!function_exists('org_layanan_integrasi_url_normalize')) {
    /**
     * URL integrasi Manajemen Layanan: pastikan valid untuk href.
     * filter_var(..., FILTER_VALIDATE_URL) membutuhkan skema; URL tanpa https://
     * (mis. subdomain.go.id/jalur) ditolak sehingga tautan tidak tersimpan / tidak tampil.
     *
     * Hanya skema http dan https yang diizinkan (cegah javascript:, data:, dll.).
     */
    function org_layanan_integrasi_url_normalize(string $raw): string
    {
        $t = trim($raw);
        if ($t === '') {
            return '';
        }
        if (!preg_match('#^[a-z][a-z0-9+.-]*:#i', $t)) {
            $t = 'https://' . ltrim($t, '/');
        }
        if (filter_var($t, FILTER_VALIDATE_URL) === false) {
            return '';
        }
        $scheme = strtolower((string) (parse_url($t, PHP_URL_SCHEME) ?? ''));
        if ($scheme !== 'http' && $scheme !== 'https') {
            return '';
        }

        return $t;
    }
}
