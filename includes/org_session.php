<?php

/**

 * Sesi & CSRF terpusat (path cookie selaras subfolder Laragon / produksi).

 */

if (!defined('ORG_ROOT')) {

    define('ORG_ROOT', dirname(__DIR__));

}



if (!function_exists('org_session_cookie_path')) {

    function org_session_cookie_path(): string

    {

        if (!function_exists('org_site_web_root')) {

            require_once ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_database.php';

        }



        // Cookie Path harus tanpa spasi; subfolder Laragon (mis. "BAGIAN ORGANISASI_V2") pakai "/" agar sesi & CSRF konsisten.

        if (function_exists('org_is_dev_environment') && org_is_dev_environment()) {

            return '/';

        }



        $root = org_site_web_root();

        if ($root === '' || preg_match('/[\s%]/', $root) !== 0) {

            return '/';

        }



        return rtrim($root, '/') . '/';

    }

}



if (!function_exists('org_session_start')) {

    function org_session_start(): void

    {

        if (session_status() === PHP_SESSION_ACTIVE) {

            return;

        }



        session_name('ORG_BAGORG_SESSID');



        $sessionSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')

            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');

        $cookiePath = org_session_cookie_path();



        if (PHP_VERSION_ID >= 70300) {

            session_set_cookie_params([

                'lifetime' => 0,

                'path' => $cookiePath,

                'domain' => '',

                'secure' => $sessionSecure,

                'httponly' => true,

                'samesite' => 'Lax',

            ]);

        } else {

            session_set_cookie_params(0, $cookiePath . '; samesite=Lax', '', $sessionSecure, true);

        }



        session_start();

    }

}



if (!function_exists('org_csrf_token')) {

    function org_csrf_token(): string

    {

        org_session_start();

        $token = (string) ($_SESSION['csrf_token'] ?? '');

        if ($token === '') {

            $token = bin2hex(random_bytes(32));

            $_SESSION['csrf_token'] = $token;

        }



        return $token;

    }

}



if (!function_exists('org_csrf_submitted_token')) {

    /** Token dari form POST, header AJAX, atau argumen eksplisit. */

    function org_csrf_submitted_token(?string $fromArgument = null): string

    {

        if ($fromArgument !== null && trim($fromArgument) !== '') {

            return trim($fromArgument);

        }

        $posted = trim((string) ($_POST['csrf_token'] ?? ''));

        if ($posted !== '') {

            return $posted;

        }

        $header = trim((string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));

        if ($header !== '') {

            return $header;

        }



        return '';

    }

}



if (!function_exists('org_csrf_validate')) {

    function org_csrf_validate(?string $submittedToken = null): bool

    {

        org_session_start();

        $sessionToken = (string) ($_SESSION['csrf_token'] ?? '');

        $submitted = org_csrf_submitted_token($submittedToken);

        if ($sessionToken === '' || $submitted === '') {

            return false;

        }



        return hash_equals($sessionToken, $submitted);

    }

}



if (!function_exists('org_csrf_invalidate')) {

    /** Putar token setelah gagal validasi agar formulir lama tidak terus gagal. */

    function org_csrf_invalidate(): string

    {

        org_session_start();

        $token = bin2hex(random_bytes(32));

        $_SESSION['csrf_token'] = $token;



        return $token;

    }

}



if (!function_exists('org_login_post_url')) {

    /** URL tujuan POST login (selalu index.php di akar situs). */

    function org_login_post_url(): string

    {

        if (!function_exists('org_site_web_root')) {

            require_once ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_database.php';

        }

        $root = defined('ORG_WEB_ROOT') ? (string) ORG_WEB_ROOT : org_site_web_root();

        if (function_exists('org_home_url')) {
            return org_home_url();
        }
        $path = ($root === '' ? '' : rtrim($root, '/')) . '/index.php';

        $segments = explode('/', trim(str_replace('\\', '/', $path), '/'));

        $encoded = implode('/', array_map('rawurlencode', $segments));



        return '/' . $encoded;

    }

}

