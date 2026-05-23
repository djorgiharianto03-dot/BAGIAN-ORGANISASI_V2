<?php
/**
 * Bootstrap aplikasi: sesi, data, dan penanganan POST.
 * Wajib dipanggil dari setiap halaman publik sebelum header.
 */
if (!defined('ORG_ROOT')) {
    define('ORG_ROOT', dirname(__DIR__));
}

require_once ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_database.php';
require_once ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_app.php';
require_once ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_layanan_integrasi_url.php';
require_once ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_runtime_cache.php';
require_once ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_upload_dirs.php';
org_ensure_upload_directories(ORG_ROOT);
org_runtime_cache_ensure_dir();
require_once ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_beranda_perf.php';

org_force_https_redirect();

if (!defined('ORG_WEB_ROOT')) {
    define('ORG_WEB_ROOT', org_site_web_root());
}
if (!defined('ORG_PROSES_SARAN_URL')) {
    define('ORG_PROSES_SARAN_URL', org_proses_saran_url());
}
if (!defined('ORG_DOWNLOAD_DOKUMEN_URL')) {
    define('ORG_DOWNLOAD_DOKUMEN_URL', org_page_url('download_dokumen.php'));
}
if (!defined('ORG_VIEW_DOKUMEN_URL')) {
    define('ORG_VIEW_DOKUMEN_URL', org_page_url('view_dokumen.php'));
}
if (!defined('ORG_DOWNLOAD_ARSIP_URL')) {
    define('ORG_DOWNLOAD_ARSIP_URL', org_page_url('download_arsip.php'));
}
if (!defined('ORG_DOWNLOAD_TUGAS_URL')) {
    define('ORG_DOWNLOAD_TUGAS_URL', (ORG_WEB_ROOT === '' ? '' : ORG_WEB_ROOT) . '/download_tugas.php');
}
if (!defined('ORG_VIEW_TUGAS_FILE_URL')) {
    define('ORG_VIEW_TUGAS_FILE_URL', (ORG_WEB_ROOT === '' ? '' : ORG_WEB_ROOT) . '/view_tugas_file.php');
}
if (!defined('ORG_DOKUMEN_MAX_UPLOAD_BYTES')) {
    define('ORG_DOKUMEN_MAX_UPLOAD_BYTES', 20 * 1024 * 1024);
}
if (!defined('ORG_DOKUMEN_MAX_UPLOAD_IMAGE_BYTES')) {
    define('ORG_DOKUMEN_MAX_UPLOAD_IMAGE_BYTES', 5 * 1024 * 1024);
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_session.php';
org_session_start();

if (
    defined('ORG_BERANDA_PAGE') && ORG_BERANDA_PAGE === true
    && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET'
    && !(defined('ORG_BERANDA_CHUNK') && ORG_BERANDA_CHUNK === true)
) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap_beranda_fast.php';
    return;
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_theme_hari_besar.php';

/* Tema hari besar: org_theme_hari_besar.php */

$uploadDir = ORG_ROOT . DIRECTORY_SEPARATOR . 'uploads';
$libraryUploadDir = $uploadDir . DIRECTORY_SEPARATOR . 'perpustakaan_digital';
$fotoStrukturDir = $uploadDir . DIRECTORY_SEPARATOR . 'foto_struktur';
$siteSettingsFile = ORG_ROOT . DIRECTORY_SEPARATOR . 'site_settings.json';

require_once ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_upload_dirs.php';
org_ensure_upload_directories(ORG_ROOT);

require_once ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'staff_users_db.php';
require_once ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'site_content_db.php';
require_once ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'galeri_kegiatan_db.php';
require_once ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'saran_kritik_db.php';
require_once ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'dokumen_db.php';
require_once ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'pengumuman_db.php';
require_once ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'pusat_informasi_db.php';
require_once ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'dashboard_widgets_db.php';
require_once ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'team_targets_db.php';

$message = '';
$messageType = '';

$searchQuery = trim($_GET['q'] ?? '');
$slugify = static function (string $value): string {
    $slug = strtolower($value);
    $slug = preg_replace('/[^a-z0-9]+/', '_', $slug);
    return trim((string) $slug, '_');
};

/** Nama file di disk / URL tanpa prefiks timestamp unggah (jika ada). */
$storedDocumentBasename = static function (string $storedName): string {
    if (preg_match('/^\d{8}_\d{6}_(.+)$/i', $storedName, $m)) {
        return $m[1];
    }
    return $storedName;
};

/** Label tampilan: underscore â†’ spasi (nama asli di server tidak diubah). */
$displayUploadFilename = static function (string $storedName) use ($storedDocumentBasename): string {
    $namaFile = $storedDocumentBasename($storedName);
    return str_replace('_', ' ', $namaFile);
};

/** Deskripsi singkat untuk sorotan hasil pencarian (bukan kolom DB). */
$documentDisplayDescription = static function (string $storedName) use ($displayUploadFilename): string {
    $ext = strtolower((string) (pathinfo($storedName, PATHINFO_EXTENSION) ?: ''));
    $tipe = match ($ext) {
        'pdf' => 'Dokumen PDF',
        'doc', 'docx' => 'Dokumen Word',
        'xls', 'xlsx' => 'Dokumen Excel',
        'jpg', 'jpeg' => 'Gambar JPEG',
        'png' => 'Gambar PNG',
        'gif' => 'Gambar GIF',
        'webp' => 'Gambar WebP',
        default => $ext !== '' ? ('Berkas .' . $ext) : 'Berkas unduhan',
    };
    $stem = (string) pathinfo($displayUploadFilename($storedName), PATHINFO_FILENAME);
    return $tipe . ' â€” ringkasan dari nama berkas: "' . $stem . '".';
};

/**
 * Pencarian dokumen ala LIKE %kata%: tiap kata kunci (dipisah spasi) harus muncul sebagai substring
 * di salah satu variasi nama (asli, basename, label tampilan).
 */
$documentMatchesSearchQuery = static function (string $storedFileName, string $query) use ($storedDocumentBasename, $displayUploadFilename): bool {
    $query = trim($query);
    if ($query === '') {
        return true;
    }
    $fn = $storedFileName;
    $base = $storedDocumentBasename($fn);
    $display = $displayUploadFilename($fn);
    $haystack = strtolower($fn . "\n" . $base . "\n" . $display . "\n" . str_replace('_', ' ', $fn));
    $tokens = preg_split('/\s+/u', $query, -1, PREG_SPLIT_NO_EMPTY);
    if (!is_array($tokens) || $tokens === []) {
        return stripos($haystack, strtolower($query)) !== false;
    }
    foreach ($tokens as $tok) {
        if (stripos($haystack, (string) $tok) === false) {
            return false;
        }
    }
    return true;
};

/** Halaman aman untuk redirect setelah POST personel */
$orgAllowedReturnPages = ['index.php', 'profil.php', 'struktur.php', 'dokumen.php', 'berita.php'];

$orgSanitizeReturn = static function (string $v) use ($orgAllowedReturnPages): string {
    $b = basename($v);
    return in_array($b, $orgAllowedReturnPages, true) ? $b : 'struktur.php';
};

org_ensure_upload_directories(ORG_ROOT);
$galleryDir = $uploadDir . DIRECTORY_SEPARATOR . 'gallery';

$defaultSiteSettings = [
    'profile_visi' => 'Menjadi bagian organisasi yang profesional, transparan, dan adaptif dalam pelayanan informasi.',
    'profile_misi' => 'Mengelola data dan dokumen organisasi secara efektif, akurat, dan mudah diakses oleh pihak terkait.',
    'profile_struktur' => 'Kepala Bagian, Subbag Umum, Subbag Dokumentasi, dan Tim Dukungan Administrasi.',
    'struktur_blurb' => 'Daftar personel Bagian Organisasi ditampilkan secara dinamis. Foto akan otomatis diambil dari folder uploads, dan memakai placeholder jika file belum tersedia.',
    'organisasi_intro' => '',
    'pengumuman' => '',
];
$siteSettings = $defaultSiteSettings;
if (file_exists($siteSettingsFile)) {
    $siteRaw = file_get_contents($siteSettingsFile);
    if ($siteRaw !== false && $siteRaw !== '') {
        $decodedSite = json_decode($siteRaw, true);
        if (is_array($decodedSite)) {
            $siteSettings = array_merge($defaultSiteSettings, $decodedSite);
        }
    }
}

$dbApp = org_db();
if ($dbApp !== null) {
    if (org_beranda_is_light_page()) {
        org_beranda_ensure_table_once($dbApp, 'site_content', static function () use ($dbApp): void {
            org_site_content_ensure_installed($dbApp);
        });
        org_beranda_ensure_table_once($dbApp, 'galeri', static function () use ($dbApp): void {
            org_galeri_ensure_table($dbApp);
        });
    } else {
        org_site_content_ensure_installed($dbApp);
        org_galeri_ensure_table($dbApp);
        org_saran_kritik_ensure_table($dbApp);
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'surat_disposisi_db.php';
        org_arsip_surat_disposisi_ensure_tables($dbApp);
    }
}
if ($dbApp !== null) {
    if (org_beranda_is_light_page()) {
        $siteSettings = org_beranda_merge_site_settings($siteSettings, $dbApp);
    } elseif (org_site_content_table_exists($dbApp)) {
        $rowSite = org_site_content_fetch($dbApp);
        if ($rowSite !== null) {
            $siteSettings = array_merge($siteSettings, $rowSite);
        }
    }
}

$personnelFile = ORG_ROOT . DIRECTORY_SEPARATOR . 'personnel.json';
$defaultPersonnelSeed = [
    ['position' => 'KEPALA BAGIAN ORGANISASI', 'name' => 'Nelson Rusmana, S.IP'],
    ['position' => 'ANALIS KEBIJAKAN AHLI MUDA', 'name' => 'Herlin Harianto, S.Mn., M.M'],
    ['position' => 'ANALIS KEBIJAKAN AHLI MUDA', 'name' => 'Erfie Solissa, S.E'],
    ['position' => 'ANALIS KEBIJAKAN AHLI PERTAMA', 'name' => 'Martha Vina Kilay, S.S'],
    ['position' => 'ANALIS KEBIJAKAN AHLI PERTAMA', 'name' => 'Djorgi Harianto, S.E'],
    ['position' => 'ANALIS KEBIJAKAN AHLI PERTAMA', 'name' => 'Barnesi Sabono, SH'],
    ['position' => 'ANALIS KEBIJAKAN AHLI PERTAMA', 'name' => 'Sulce. N Beresaby, SE'],
    ['position' => 'ANALIS KEBIJAKAN AHLI PERTAMA', 'name' => 'Irene Soplantila, S.I.P'],
    ['position' => 'ANALIS KEBIJAKAN AHLI PERTAMA', 'name' => 'Jacob Josimus Bothmir, S.E'],
    ['position' => 'ANALIS KEBIJAKAN AHLI PERTAMA', 'name' => 'Friets Benyamin Meturan, S.E'],
    ['position' => 'PENELAAH TEKNIS KEBIJAKAN', 'name' => 'Sephliana J. Mantuges, S.Psi'],
    ['position' => 'PENELAAH TEKNIS KEBIJAKAN', 'name' => 'Agung Perkasa, S.Tr.IP'],
    ['position' => 'PENATA LAYANAN OPERASIONAL', 'name' => 'Josina Watratan, S.Ip'],
    ['position' => 'PENATA LAYANAN OPERASIONAL', 'name' => 'Gerth Jelinik Gainau, SH'],
    ['position' => 'PENATA LAYANAN OPERASIONAL', 'name' => 'Dominggus Steven Djilarpoin, SH'],
    ['position' => 'PENGELOLA LAYANAN OPERASIONAL', 'name' => 'Djefry J. Ohoiner, A.Md'],
    ['position' => 'PENGADMINISTRASI PERKANTORAN', 'name' => 'Darmawati Darakay'],
    ['position' => 'PENGADMINISTRASI PERKANTORAN', 'name' => 'Maria Theresia Rahayaan'],
    ['position' => 'PENGADMINISTRASI PERKANTORAN', 'name' => 'Muhamad Ali Hanafi Sedubun'],
    ['position' => 'PENGADMINISTRASI PERKANTORAN', 'name' => 'Christina Manaha'],
    ['position' => 'PENGADMINISTRASI PERKANTORAN', 'name' => 'Yobelina Mangar'],
    ['position' => 'PENGADMINISTRASI PERKANTORAN', 'name' => 'Daniel Watunglawar'],
    ['position' => 'PENGADMINISTRASI PERKANTORAN', 'name' => 'Marthen Karelau'],
    ['position' => 'PENGADMINISTRASI PERKANTORAN', 'name' => 'Fredi Dorus Letlora'],
    ['position' => 'PENGADMINISTRASI PERKANTORAN', 'name' => 'Maximus Walten Jr'],
    ['position' => 'PENGADMINISTRASI PERKANTORAN', 'name' => 'Inda Lenora Uniplaita'],
];

if (!file_exists($personnelFile)) {
    $seedWithId = [];
    foreach ($defaultPersonnelSeed as $seed) {
        $nipSeed = isset($seed['nip']) ? substr((string) $seed['nip'], 0, 20) : '';
        $seedWithId[] = [
            'id' => uniqid('staff_', true),
            'name' => $seed['name'],
            'nip' => $nipSeed,
            'position' => $seed['position'],
        ];
    }
    file_put_contents($personnelFile, json_encode($seedWithId, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$personnelData = [];
$personnelIds = [];
$personnelSlugs = [];
$berandaLibraryDocCount = null;

$savePersonnelData = static function (string $personnelFilePath, array $items): bool {
    return org_personnel_write_file($personnelFilePath, $items);
};

require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_personnel_sync.php';

$orgPersonnelRegistryApply = static function (array $registry) use (&$personnelData, &$personnelIds, &$personnelSlugs): void {
    $personnelData = $registry['data'];
    $personnelIds = $registry['ids'];
    $personnelSlugs = $registry['slugs'];
};

if (!org_beranda_is_light_page()) {
    $orgPersonnelRegistryApply(org_personnel_sync_from_disk($personnelFile, $fotoStrukturDir, $slugify, $savePersonnelData));
}

if (isset($_SESSION['flash_message'], $_SESSION['flash_type'])) {
    $message = (string) $_SESSION['flash_message'];
    $messageType = (string) $_SESSION['flash_type'];
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
}

$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
$currentLevel = org_staff_role_normalize((string) ($_SESSION['level'] ?? $_SESSION['admin_role'] ?? ''));
if ($currentLevel !== '' && (!isset($_SESSION['level']) || $_SESSION['level'] !== $currentLevel)) {
    $_SESSION['level'] = $currentLevel;
    $_SESSION['admin_role'] = $currentLevel;
}

if ($isAdmin && !isset($_SESSION['admin_role'])) {
    $dbSync = org_db();
    if ($dbSync !== null && org_staff_users_table_exists($dbSync)) {
        $unSync = trim((string) ($_SESSION['admin_username'] ?? ''));
        if ($unSync !== '') {
            $uSync = org_staff_users_fetch_by_username($dbSync, $unSync);
            if ($uSync !== null) {
                $_SESSION['admin_role'] = org_staff_role_normalize((string) ($uSync['level'] ?? ''));
                $_SESSION['level'] = $_SESSION['admin_role'];
                $_SESSION['admin_user_id'] = (int) ($uSync['id'] ?? 0);
                $_SESSION['org_is_kabag'] = org_staff_user_is_kabag($uSync);
            }
        }
    }
    if (!isset($_SESSION['admin_role'])) {
        $_SESSION['admin_role'] = 'super_admin';
        $_SESSION['level'] = 'super_admin';
    }
}

if ($isAdmin && empty($_SESSION['org_is_kabag'])) {
    org_staff_session_is_kabag(org_db());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $shouldRedirect = false;
    $redirectPage = 'struktur.php';

    if (in_array($action, ['add_personnel', 'edit_personnel', 'delete_personnel'], true) && $personnelData === []) {
        $orgPersonnelRegistryApply(org_personnel_sync_from_disk($personnelFile, $fotoStrukturDir, $slugify, $savePersonnelData));
    }

    if ($action === 'login') {
        if (!org_csrf_validate()) {
            org_csrf_invalidate();
            $_SESSION['flash_message'] = 'Sesi keamanan tidak valid. Muat ulang halaman lalu coba lagi.';
            $_SESSION['flash_type'] = 'danger';
            $loginRedirect = function_exists('org_login_post_url') ? org_login_post_url() : (function_exists('org_home_url') ? org_home_url() : 'index.php');
            header('Location: ' . $loginRedirect, true, 303);
            exit;
        } else {
            $nowTs = time();
            $attemptWindowSec = 10 * 60;
            $maxAttempts = 5;
            $attempts = $_SESSION['login_attempts'] ?? [];
            if (!is_array($attempts)) {
                $attempts = [];
            }
            $attempts = array_values(array_filter($attempts, static function ($ts) use ($nowTs, $attemptWindowSec): bool {
                return is_int($ts) && $ts > ($nowTs - $attemptWindowSec);
            }));
            $_SESSION['login_attempts'] = $attempts;
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $loginOk = false;
            $loginErrorMessage = '';
            $loginErrorType = 'danger';

            if (count($attempts) >= $maxAttempts) {
                $waitSec = max(1, $attemptWindowSec - ($nowTs - (int) $attempts[0]));
                $message = 'Terlalu banyak percobaan login. Coba lagi dalam ' . (int) ceil($waitSec / 60) . ' menit.';
                $messageType = 'warning';
                $loginErrorMessage = $message;
            } else {
                $dbLogin = org_db();
                if ($dbLogin !== null && org_staff_users_table_exists($dbLogin) && $username !== '') {
                    $uLogin = org_staff_users_fetch_by_username($dbLogin, $username);
                    if ($uLogin !== null && password_verify($password, (string) ($uLogin['password'] ?? ''))) {
                        $levelLogin = org_staff_role_normalize((string) ($uLogin['level'] ?? ''));
                        if ($levelLogin === '') {
                            $loginErrorMessage = 'Akun belum aktif';
                            $loginErrorType = 'warning';
                            $loginOk = false;
                        } else {
                        session_regenerate_id(true);
                        $_SESSION['is_admin'] = true;
                        $_SESSION['admin_username'] = (string) ($uLogin['username'] ?? $username);
                        $_SESSION['admin_display'] = trim((string) ($uLogin['nama'] ?? '')) !== ''
                            ? (string) $uLogin['nama']
                            : (string) ($uLogin['username'] ?? $username);
                        $_SESSION['admin_user_id'] = (int) ($uLogin['id'] ?? 0);
                        $_SESSION['admin_role'] = $levelLogin;
                        $_SESSION['level'] = $levelLogin;
                        $_SESSION['org_is_kabag'] = org_staff_user_is_kabag($uLogin);
                        $_SESSION['login_attempts'] = [];
                        $isAdmin = true;
                        $loginOk = true;
                        $message = 'Login berhasil. Selamat datang, ' . htmlspecialchars($_SESSION['admin_display'], ENT_QUOTES, 'UTF-8') . '.';
                        $messageType = 'success';
                        }
                    }
                }

                if (!$loginOk) {
                    $_SESSION['login_attempts'][] = $nowTs;
                    $_SESSION['login_attempts'] = array_values($_SESSION['login_attempts']);
                    if ($loginErrorMessage === '') {
                        $loginErrorMessage = 'Username atau password salah.';
                    }
                    $message = $loginErrorMessage;
                    $messageType = $loginErrorType;
                    if (count($_SESSION['login_attempts']) >= $maxAttempts) {
                        $message = 'Terlalu banyak percobaan login. Coba lagi dalam 10 menit.';
                        $messageType = 'warning';
                    }
                }
            }
        }
    }

    if ($action === 'logout') {
        if (!org_csrf_validate()) {
            org_csrf_invalidate();
            $message = 'Sesi keamanan tidak valid. Muat ulang halaman lalu coba lagi.';
            $messageType = 'danger';
        } else {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }
            session_destroy();
            $isAdmin = false;
            $message = 'Anda berhasil logout.';
            $messageType = 'info';
        }
    }

    if ($action === 'upload') {
        if (!org_csrf_validate()) {
            $message = 'Sesi keamanan tidak valid. Muat ulang halaman lalu coba lagi.';
            $messageType = 'danger';
        } elseif (!$isAdmin) {
            $message = 'Akses ditolak. Silakan login sebagai Admin terlebih dahulu.';
            $messageType = 'danger';
        } elseif (!org_staff_can_manage_perpustakaan_dokumen()) {
            $message = 'Akses ditolak. Unggah dokumen hanya untuk Admin.';
            $messageType = 'danger';
        } else {
            $uploadResult = org_dokumen_process_upload(
                isset($_FILES['dokumen']) && is_array($_FILES['dokumen']) ? $_FILES['dokumen'] : null,
                (string) ($_POST['dokumen_kategori'] ?? '')
            );
            $message = $uploadResult['message'];
            $messageType = $uploadResult['type'];
        }
    }

    if ($action === 'delete_file') {
        if (!org_csrf_validate()) {
            $message = 'Sesi keamanan tidak valid. Muat ulang halaman lalu coba lagi.';
            $messageType = 'danger';
        } elseif (!$isAdmin) {
            $message = 'Akses ditolak. Silakan login sebagai Admin terlebih dahulu.';
            $messageType = 'danger';
        } elseif (!org_staff_can_manage_perpustakaan_dokumen()) {
            $message = 'Akses ditolak. Hapus dokumen hanya untuk Admin.';
            $messageType = 'danger';
        } else {
            $deleteResult = org_dokumen_delete_library_file((string) ($_POST['file_name'] ?? ''));
            $message = $deleteResult['message'];
            $messageType = $deleteResult['type'];
        }
    }

    if ($action === 'add_personnel') {
        $shouldRedirect = true;
        $redirectPage = $orgSanitizeReturn((string) ($_POST['return_to'] ?? 'struktur.php'));
        if (!org_csrf_validate()) {
            $_SESSION['flash_message'] = 'Sesi keamanan tidak valid. Muat ulang halaman lalu coba lagi.';
            $_SESSION['flash_type'] = 'danger';
        } elseif (!org_personnel_can_manage()) {
            $_SESSION['flash_message'] = 'Akses ditolak. Hanya Admin yang dapat mengelola personel.';
            $_SESSION['flash_type'] = 'danger';
        } else {
            $name = trim((string) ($_POST['person_name'] ?? ''));
            $position = trim((string) ($_POST['person_position'] ?? ''));
            $nip = substr(preg_replace('/\s+/u', '', trim((string) ($_POST['person_nip'] ?? ''))), 0, 20);
            if ($name === '' || $position === '') {
                $_SESSION['flash_message'] = 'Nama dan jabatan wajib diisi.';
                $_SESSION['flash_type'] = 'warning';
            } else {
                $newPersonId = uniqid('staff_', true);
                $newPersonSlug = $slugify($name);
                $personnelData[] = [
                    'id' => $newPersonId,
                    'name' => $name,
                    'nip' => $nip,
                    'position' => $position,
                ];

                if ($savePersonnelData($personnelFile, $personnelData)) {
                    $orgPersonnelRegistryApply(org_personnel_sync_from_disk($personnelFile, $fotoStrukturDir, $slugify, $savePersonnelData));
                    if (isset($_FILES['person_photo']) && $_FILES['person_photo']['error'] === UPLOAD_ERR_OK) {
                        $photoFile = $_FILES['person_photo'];
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $photoMime = finfo_file($finfo, $photoFile['tmp_name']);
                        finfo_close($finfo);
                        $allowedPhotoTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png'];

                        if (isset($allowedPhotoTypes[$photoMime])) {
                            $targetExt = $allowedPhotoTypes[$photoMime];
                            $targetFileName = $newPersonSlug . '.' . $targetExt;
                            $target_dir = $fotoStrukturDir;
                            if (!is_dir($target_dir)) {
                                mkdir($target_dir, 0777, true);
                            }
                            $targetPath = $target_dir . DIRECTORY_SEPARATOR . $targetFileName;
                            foreach (['png', 'jpg', 'jpeg'] as $oldExt) {
                                $oldPath = $target_dir . DIRECTORY_SEPARATOR . $newPersonSlug . '.' . $oldExt;
                                if (is_file($oldPath)) {
                                    @unlink($oldPath);
                                }
                            }
                            move_uploaded_file($photoFile['tmp_name'], $targetPath);
                        }
                    }
                    $_SESSION['flash_message'] = 'Personel baru berhasil ditambahkan.';
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'Gagal menyimpan data personel.';
                    $_SESSION['flash_type'] = 'danger';
                }
            }
        }
    }

    if ($action === 'edit_personnel') {
        $shouldRedirect = true;
        $redirectPage = $orgSanitizeReturn((string) ($_POST['return_to'] ?? 'struktur.php'));
        if (!org_csrf_validate()) {
            $_SESSION['flash_message'] = 'Sesi keamanan tidak valid. Muat ulang halaman lalu coba lagi.';
            $_SESSION['flash_type'] = 'danger';
        } elseif (!org_personnel_can_manage()) {
            $_SESSION['flash_message'] = 'Akses ditolak. Hanya Admin yang dapat mengelola personel.';
            $_SESSION['flash_type'] = 'danger';
        } else {
            $personId = (string) ($_POST['person_id'] ?? '');
            $personSlug = trim((string) ($_POST['person_slug'] ?? ''));
            $name = trim((string) ($_POST['person_name'] ?? ''));
            $position = trim((string) ($_POST['person_position'] ?? ''));
            $nip = substr(preg_replace('/\s+/u', '', trim((string) ($_POST['person_nip'] ?? ''))), 0, 20);
            $rowIndex = org_personnel_find_index($personnelData, trim($personId), $personSlug);

            if ($rowIndex === false || $name === '' || $position === '') {
                $_SESSION['flash_message'] = 'Data personel tidak valid.';
                $_SESSION['flash_type'] = 'warning';
            } else {
                $oldSlug = $personnelData[$rowIndex]['slug'];
                $newSlug = $slugify($name);
                $personnelData[$rowIndex]['name'] = $name;
                $personnelData[$rowIndex]['nip'] = $nip;
                $personnelData[$rowIndex]['position'] = $position;
                $personnelData[$rowIndex]['slug'] = $newSlug;

                if ($oldSlug !== $newSlug) {
                    foreach (['png', 'jpg', 'jpeg'] as $ext) {
                        $oldPhotoPath = $fotoStrukturDir . DIRECTORY_SEPARATOR . $oldSlug . '.' . $ext;
                        $newPhotoPath = $fotoStrukturDir . DIRECTORY_SEPARATOR . $newSlug . '.' . $ext;
                        if (is_file($oldPhotoPath)) {
                            @rename($oldPhotoPath, $newPhotoPath);
                        }
                    }
                }

                if (isset($_FILES['person_photo']) && $_FILES['person_photo']['error'] === UPLOAD_ERR_OK) {
                    $photoFile = $_FILES['person_photo'];
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $photoMime = finfo_file($finfo, $photoFile['tmp_name']);
                    finfo_close($finfo);
                    $allowedPhotoTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png'];

                    if (!isset($allowedPhotoTypes[$photoMime])) {
                        $_SESSION['flash_message'] = 'Format foto tidak didukung. Gunakan JPG atau PNG.';
                        $_SESSION['flash_type'] = 'warning';
                        header('Location: ' . $redirectPage);
                        exit;
                    }

                    foreach (['png', 'jpg', 'jpeg'] as $ext) {
                        $oldPath = $fotoStrukturDir . DIRECTORY_SEPARATOR . $newSlug . '.' . $ext;
                        if (is_file($oldPath)) {
                            @unlink($oldPath);
                        }
                    }
                    $target_dir = $fotoStrukturDir;
                    if (!is_dir($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    $targetPath = $target_dir . DIRECTORY_SEPARATOR . $newSlug . '.' . $allowedPhotoTypes[$photoMime];
                    move_uploaded_file($photoFile['tmp_name'], $targetPath);
                }

                if ($savePersonnelData($personnelFile, $personnelData)) {
                    $orgPersonnelRegistryApply(org_personnel_sync_from_disk($personnelFile, $fotoStrukturDir, $slugify, $savePersonnelData));
                    $_SESSION['flash_message'] = 'Data personel berhasil diperbarui.';
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'Gagal memperbarui data personel. Periksa izin tulis berkas personnel.json.';
                    $_SESSION['flash_type'] = 'danger';
                }
            }
        }
    }

    if ($action === 'delete_personnel') {
        $shouldRedirect = true;
        $redirectPage = $orgSanitizeReturn((string) ($_POST['return_to'] ?? 'struktur.php'));
        if (!org_csrf_validate()) {
            $_SESSION['flash_message'] = 'Sesi keamanan tidak valid. Muat ulang halaman lalu coba lagi.';
            $_SESSION['flash_type'] = 'danger';
        } elseif (!org_personnel_can_manage()) {
            $_SESSION['flash_message'] = 'Akses ditolak. Hanya Admin yang dapat mengelola personel.';
            $_SESSION['flash_type'] = 'danger';
        } else {
            $personId = trim((string) ($_POST['person_id'] ?? ''));
            $personSlug = trim((string) ($_POST['person_slug'] ?? ''));
            $rowIndex = org_personnel_find_index($personnelData, $personId, $personSlug);
            if ($rowIndex === false) {
                $_SESSION['flash_message'] = 'Data personel tidak ditemukan.';
                $_SESSION['flash_type'] = 'warning';
            } else {
                $slug = (string) ($personnelData[$rowIndex]['slug'] ?? '');
                if ($slug === '' && $personSlug !== '') {
                    $slug = $personSlug;
                }
                if ($slug === '') {
                    $slug = $slugify((string) ($personnelData[$rowIndex]['name'] ?? ''));
                }
                org_personnel_delete_photo_files($fotoStrukturDir, $slug);
                array_splice($personnelData, $rowIndex, 1);
                if ($savePersonnelData($personnelFile, $personnelData)) {
                    $orgPersonnelRegistryApply(org_personnel_sync_from_disk($personnelFile, $fotoStrukturDir, $slugify, $savePersonnelData));
                    $_SESSION['flash_message'] = 'Personel berhasil dihapus.';
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'Gagal menghapus personel. Periksa izin tulis berkas personnel.json.';
                    $_SESSION['flash_type'] = 'danger';
                }
            }
        }
    }

    if ($shouldRedirect) {
        $hash = '';
        if (in_array($action, ['add_personnel', 'edit_personnel', 'delete_personnel'], true) && $redirectPage === 'profil.php') {
            $hash = 'profil-struktur-organisasi';
        }
        $redirectUrl = org_personnel_post_redirect_url($redirectPage, $hash, $searchQuery);
        header('Location: ' . $redirectUrl);
        exit;
    }
}

$uploadedFiles = [];
$libraryDocumentFiles = [];
$libraryDocumentStatsMap = [];
$pengumumanCards = [];
$pusatInformasiPostsAll = [];
$pusatInformasiPosts = [];

if (org_beranda_is_light_page()) {
    $dbBeranda = $dbApp instanceof mysqli ? $dbApp : org_db();
    if ($dbBeranda instanceof mysqli) {
        org_beranda_ensure_table_once($dbBeranda, 'dokumen', static function () use ($dbBeranda): void {
            org_dokumen_ensure_table($dbBeranda);
        });
        $berandaLibraryDocCount = org_beranda_dokumen_count_cached($dbBeranda);
        org_beranda_ensure_table_once($dbBeranda, 'pusat_informasi', static function () use ($dbBeranda): void {
            org_pusat_informasi_ensure_table($dbBeranda);
        });
        $pusatInformasiPosts = org_pusat_informasi_fetch_for_beranda($dbBeranda, 4, 12);
    } else {
        $berandaLibraryDocCount = 0;
    }
} else {
if (is_dir($libraryUploadDir)) {
    $uploadedFiles = array_values(array_filter(scandir($libraryUploadDir), function ($item) use ($libraryUploadDir) {
        return $item !== '.' && $item !== '..' && is_file($libraryUploadDir . DIRECTORY_SEPARATOR . $item);
    }));
    rsort($uploadedFiles);
}

/** Dokumen library (termasuk PDF/Word/Excel) dan visual yang dikelola via kategori. */
$libraryDocumentFiles = array_values(array_filter($uploadedFiles, static function ($f) {
    return org_dokumen_is_library_file((string) $f);
}));
rsort($libraryDocumentFiles);

$dbDokumenSync = org_db();
if ($dbDokumenSync instanceof mysqli) {
    org_dokumen_ensure_table($dbDokumenSync);
    org_dokumen_sync_with_disk($dbDokumenSync, $libraryDocumentFiles);
    $libraryDocumentStatsMap = org_dokumen_fetch_stats_map($dbDokumenSync);
}

$dbPeng = org_db();
if ($dbPeng instanceof mysqli) {
    org_pengumuman_ensure_table($dbPeng);
    $pengumumanCards = org_pengumuman_fetch_all($dbPeng, 50);
}

$dbPi = org_db();
if ($dbPi instanceof mysqli) {
    org_pusat_informasi_ensure_table($dbPi);
    $pusatInformasiPostsAll = org_pusat_informasi_fetch_all($dbPi, 100);
    /** Beranda: hingga 4 berita utama di urutan atas, lalu mengisi sampai 12 kartu */
    $pusatInformasiPosts = org_pusat_informasi_fetch_for_beranda($dbPi, 4, 12);
}
}

$berandaGaleriKegiatan = [];
$dbBerandaData = ($dbApp instanceof mysqli) ? $dbApp : org_db();
if (org_beranda_is_light_page()) {
    $berandaGaleriKegiatan = org_beranda_fetch_galeri_cached($dbBerandaData, 6);
} else {
    $berandaGaleriKegiatan = array_slice(
        org_galeri_kegiatan_load_public($dbBerandaData),
        0,
        6
    );
}

$berandaDashboardWidgets = [];
$berandaWidgetDetailsMap = [];
$berandaTeamTargetsTahun = org_team_targets_normalize_tahun($_GET['tahun'] ?? (int) date('Y'));
$berandaTeamTargetsYears = [];
$berandaTeamTargetsGrouped = org_team_targets_empty_grouped();
$berandaTeamTargetsVisible = false;

if (org_beranda_is_light_page() && $dbBerandaData instanceof mysqli) {
    $berandaHeavy = org_beranda_load_dashboard_and_team($dbBerandaData, $berandaTeamTargetsTahun);
    $berandaDashboardWidgets = $berandaHeavy['widgets'];
    $berandaWidgetDetailsMap = $berandaHeavy['details'];
    $berandaTeamTargetsTahun = (int) $berandaHeavy['teamTahun'];
    $berandaTeamTargetsYears = $berandaHeavy['teamYears'];
    $berandaTeamTargetsGrouped = $berandaHeavy['teamGrouped'];
    $berandaTeamTargetsVisible = (bool) $berandaHeavy['teamVisible'];
} else {
    $dbWidgets = org_db();
    if ($dbWidgets instanceof mysqli && !org_beranda_is_light_page()) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'dashboard_widgets_db.php';
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'widget_details_db.php';
        org_dashboard_widgets_ensure_table($dbWidgets);
        $berandaDashboardWidgets = org_dashboard_widgets_fetch_all($dbWidgets, true);
        $widgetIds = [];
        foreach ($berandaDashboardWidgets as $bw) {
            $wid = (int) ($bw['id'] ?? 0);
            if ($wid > 0) {
                $widgetIds[] = $wid;
            }
        }
        if ($widgetIds !== []) {
            $berandaWidgetDetailsMap = org_widget_details_fetch_grouped_map($dbWidgets, $widgetIds);
        }
    }

    $dbTeamTargets = org_db();
    if ($dbTeamTargets instanceof mysqli && !org_beranda_is_light_page()) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'team_targets_db.php';
        org_team_targets_ensure_table($dbTeamTargets);
        $showYear = org_team_targets_resolve_beranda_year($dbTeamTargets, $berandaTeamTargetsTahun);
        if ($showYear > 0) {
            $berandaTeamTargetsTahun = $showYear;
            $berandaTeamTargetsGrouped = org_team_targets_fetch_grouped_by_year($dbTeamTargets, $showYear);
            $berandaTeamTargetsYears = org_team_targets_fetch_beranda_years($dbTeamTargets);
            if ($berandaTeamTargetsYears === []) {
                $berandaTeamTargetsYears = [$showYear];
            }
            if (!in_array($showYear, $berandaTeamTargetsYears, true)) {
                array_unshift($berandaTeamTargetsYears, $showYear);
            }
            $berandaTeamTargetsVisible = true;
        }
    }
}

$filteredUploadedFiles = $uploadedFiles;
$filteredLibraryDocuments = $libraryDocumentFiles;
if ($searchQuery !== '') {
    $filteredUploadedFiles = array_values(array_filter($uploadedFiles, function ($fileName) use ($searchQuery, $documentMatchesSearchQuery) {
        return $documentMatchesSearchQuery((string) $fileName, $searchQuery);
    }));
    $filteredLibraryDocuments = array_values(array_filter($libraryDocumentFiles, function ($fileName) use ($searchQuery, $storedDocumentBasename, $displayUploadFilename, $libraryDocumentStatsMap) {
        $fn = (string) $fileName;
        $stat = $libraryDocumentStatsMap[$fn] ?? [];
        if ($stat === []) {
            $stat = [
                'nama_file' => $fn,
                'kategori' => org_dokumen_kategori_from_filename($fn),
                'judul' => '',
                'deskripsi' => '',
            ];
        }

        return org_dokumen_match_library_query($fn, $searchQuery, $stat, $storedDocumentBasename, $displayUploadFilename);
    }));
}

$logoWebPath = '';
foreach (['png', 'jpg', 'jpeg', 'webp', 'svg'] as $logoExt) {
    $logoFs = ORG_ROOT . DIRECTORY_SEPARATOR . 'logo.' . $logoExt;
    if (is_file($logoFs)) {
        $logoWebPath = 'logo.' . $logoExt . '?v=' . rawurlencode((string) filemtime($logoFs));
        break;
    }
}
