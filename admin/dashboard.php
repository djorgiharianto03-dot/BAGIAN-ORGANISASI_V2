<?php

$root = dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_upload_dirs.php';
org_ensure_upload_directories($root);
require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_session.php';
org_session_start();

$csrfToken = org_csrf_token();

if (empty($_SESSION['is_admin'])) {
    header('Location: ../index.php');
    exit;
}

$galeriImgDir = $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'galeri';
if (!is_dir($galeriImgDir)) {
    @mkdir($galeriImgDir, 0777, true);
}
require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_database.php';
require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_app.php';
require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_layanan_integrasi_url.php';
require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'staff_users_db.php';
require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'site_content_db.php';
require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'galeri_kegiatan_db.php';
require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'saran_kritik_db.php';
require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'dokumen_db.php';
require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'pengumuman_db.php';
require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'pusat_informasi_db.php';

$prosesSaranUrlDash = htmlspecialchars(org_proses_saran_url(), ENT_QUOTES, 'UTF-8');

$sessionRoleNorm = org_staff_role_normalize((string) ($_SESSION['level'] ?? $_SESSION['admin_role'] ?? ''));
$auditRiwayatVisible = org_staff_audit_viewer_can_see_riwayat();
$isSuperAdminActor = $sessionRoleNorm === 'super_admin';
$isAdminActor = $sessionRoleNorm === 'admin';
$canDeleteStaffAccount = $isSuperAdminActor || $isAdminActor;
$isSubAdminActor = $sessionRoleNorm === 'sub_admin_eorganisasi' || $sessionRoleNorm === 'sub_admin_publikasi';
$isSubAdminEorgActor = $sessionRoleNorm === 'sub_admin_eorganisasi';
$isSubAdminPublikasiActor = $sessionRoleNorm === 'sub_admin_publikasi';

$siteSettingsFile = $root . DIRECTORY_SEPARATOR . 'site_settings.json';
$defaultSiteSettings = [
    'profile_visi' => 'Menjadi bagian organisasi yang profesional, transparan, dan adaptif dalam pelayanan informasi.',
    'profile_misi' => 'Mengelola data dan dokumen organisasi secara efektif, akurat, dan mudah diakses oleh pihak terkait.',
    'profile_struktur' => 'Kepala Bagian, Subbag Umum, Subbag Dokumentasi, dan Tim Dukungan Administrasi.',
    'struktur_blurb' => 'Daftar personel Bagian Organisasi ditampilkan secara dinamis.',
    'pengumuman' => '',
];

$siteSettings = $defaultSiteSettings;
if (is_file($siteSettingsFile)) {
    $raw = file_get_contents($siteSettingsFile);
    if ($raw !== false && $raw !== '') {
        $j = json_decode($raw, true);
        if (is_array($j)) {
            $siteSettings = array_merge($defaultSiteSettings, $j);
        }
    }
}

$layananDataFile = $root . DIRECTORY_SEPARATOR . 'layanan_data.json';
$layananKategoriList = ['Kelembagaan', 'Pelayanan Publik', 'SAKIP & RB'];
$layananKategoriLabelMap = [
    'Kelembagaan' => 'Layanan Kelembagaan dan Anjab',
    'Pelayanan Publik' => 'Layanan Pelayanan Publik dan Tata Laksana',
    'SAKIP & RB' => 'Layanan Kinerja dan RB',
];
$layananRows = [];
if (is_file($layananDataFile)) {
    $rawLayanan = file_get_contents($layananDataFile);
    if ($rawLayanan !== false && $rawLayanan !== '') {
        $layananParsed = json_decode($rawLayanan, true);
        if (is_array($layananParsed)) {
            foreach ($layananParsed as $lr) {
                if (!is_array($lr)) {
                    continue;
                }
                $kat = org_dokumen_normalize_tim_kategori((string) ($lr['kategori'] ?? 'Kelembagaan'));
                if (!in_array($kat, $layananKategoriList, true)) {
                    continue;
                }
                $namaL = trim(org_sanitize_plain((string) ($lr['nama'] ?? '')));
                $deskL = trim(org_sanitize_plain((string) ($lr['deskripsi'] ?? '')));
                $imgL = trim((string) ($lr['media_image'] ?? ''));
                $docL = trim((string) ($lr['media_document'] ?? ''));
                $docsL = [];
                if (isset($lr['media_documents']) && is_array($lr['media_documents'])) {
                    foreach ($lr['media_documents'] as $d) {
                        if (is_string($d) && trim($d) !== '') {
                            $docsL[] = trim($d);
                        }
                    }
                } elseif ($docL !== '') {
                    $docsL[] = $docL;
                }
                $linkL = trim((string) ($lr['link'] ?? ''));
                $pinLblL = trim(org_sanitize_plain((string) ($lr['pin_label'] ?? '')));
                $pinPosL = (string) ($lr['pin_position'] ?? '');
                if ($pinPosL !== 'before' && $pinPosL !== 'after') {
                    $pinPosL = '';
                }
                if ($pinLblL === '') {
                    $pinPosL = '';
                }
                $urutanL = (int) ($lr['urutan'] ?? 0);
                if ($urutanL < 0) {
                    $urutanL = 0;
                }
                if ($urutanL > 9999) {
                    $urutanL = 9999;
                }
                if ($namaL === '' && $deskL === '' && $imgL === '' && $docsL === [] && $linkL === '') {
                    continue;
                }
                $layananRows[] = [
                    'kategori' => $kat,
                    'nama' => $namaL,
                    'deskripsi' => $deskL,
                    'media_image' => $imgL,
                    'media_document' => $docsL[0] ?? '',
                    'media_documents' => $docsL,
                    'link' => $linkL,
                    'pin_label' => $pinLblL !== '' ? (function_exists('mb_substr') ? mb_substr($pinLblL, 0, 40, 'UTF-8') : substr($pinLblL, 0, 40)) : '',
                    'pin_position' => $pinPosL,
                    'urutan' => $urutanL,
                ];
            }
        }
    }
}

$db = org_db();
if ($db !== null) {
    org_site_content_ensure_installed($db);
    org_galeri_ensure_table($db);
    org_saran_kritik_ensure_table($db);
    org_dokumen_ensure_table($db);
    org_pengumuman_ensure_table($db);
    org_pusat_informasi_ensure_table($db);
}

$dashUploadDir = $root . DIRECTORY_SEPARATOR . 'uploads';
$dashLibraryDir = org_dokumen_library_upload_dir_fs();
$dashLibraryFiles = org_dokumen_list_library_files_on_disk();
$dashLibraryStatsMap = [];
$dashDocFiles = [];
if (is_dir($dashUploadDir)) {
    $dashDocFiles = array_values(array_filter(scandir($dashUploadDir), function ($item) use ($dashUploadDir) {
        if ($item === '.' || $item === '..' || !is_file($dashUploadDir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }

        return org_dokumen_is_library_file((string) $item);
    }));
}
if ($db !== null && org_dokumen_table_exists($db)) {
    org_dokumen_sync_with_disk($db, $dashLibraryFiles);
    $dashLibraryStatsMap = org_dokumen_fetch_stats_map($db);
}
$dashDocRanked = [];
$dashDocDbReady = $db !== null && org_dokumen_table_exists($db);
if ($db !== null && $dashDocDbReady) {
    org_dokumen_sync_with_disk($db, $dashDocFiles);
    $resRank = $db->query('SELECT `nama_file`, `kategori`, `jumlah_unduh` FROM `dokumen` ORDER BY `jumlah_unduh` DESC, `nama_file` ASC LIMIT 80');
    if ($resRank !== false) {
        while ($r = $resRank->fetch_assoc()) {
            if (!is_array($r)) {
                continue;
            }
            $nf = (string) ($r['nama_file'] ?? '');
            if ($nf === '') {
                continue;
            }
            $p = $dashUploadDir . DIRECTORY_SEPARATOR . $nf;
            if (!is_file($p)) {
                continue;
            }
            $dashDocRanked[] = [
                'nama_file' => $nf,
                'kategori' => (string) ($r['kategori'] ?? ''),
                'jumlah_unduh' => (int) ($r['jumlah_unduh'] ?? 0),
                'bytes' => (int) filesize($p),
            ];
        }
    }
}
$dbReady = $db !== null && org_site_content_table_exists($db);
if ($dbReady) {
    $row = org_site_content_fetch($db);
    if ($row !== null) {
        $siteSettings = array_merge($siteSettings, $row);
    }
}

$flashOk = '';
$flashErr = '';
$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$postedAction = (string) ($_POST['action'] ?? '');
$csrfValid = !$isPost || org_csrf_validate();
if ($isPost && !$csrfValid) {
    $csrfToken = org_csrf_invalidate();
    $flashErr = 'Sesi keamanan tidak valid. Muat ulang halaman lalu coba lagi.';
}
if ($isPost && $csrfValid && $postedAction === 'save_layanan_dashboard') {
    $rowsIn = $_POST['layanan'] ?? [];
    $nextRows = [];
    $layananAssetDir = $root . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'layanan_assets';
    $layananAssetWebPrefix = 'uploads/layanan_assets/';
    $normalizeAssetPath = static function (string $rawPath) use ($layananAssetWebPrefix): string {
        $p = str_replace('\\', '/', trim($rawPath));
        if ($p === '' || strpos($p, $layananAssetWebPrefix) !== 0) {
            return '';
        }
        $base = basename($p);
        if ($base === '' || $base === '.' || $base === '..') {
            return '';
        }

        return $layananAssetWebPrefix . $base;
    };
    $normalizeAssetList = static function ($raw, callable $normalizeAssetPath): array {
        $out = [];
        if (is_array($raw)) {
            foreach ($raw as $x) {
                $p = $normalizeAssetPath((string) $x);
                if ($p !== '') {
                    $out[] = $p;
                }
            }
        } elseif (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                foreach ($decoded as $x) {
                    $p = $normalizeAssetPath((string) $x);
                    if ($p !== '') {
                        $out[] = $p;
                    }
                }
            } else {
                $p = $normalizeAssetPath($raw);
                if ($p !== '') {
                    $out[] = $p;
                }
            }
        }

        return array_values(array_unique($out));
    };
    $saveUpload = static function (
        array $fileSpec,
        array $allowedExt,
        array $allowedMime,
        string $prefix
    ) use ($layananAssetDir, $layananAssetWebPrefix): array {
        $err = (int) ($fileSpec['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($err === UPLOAD_ERR_NO_FILE) {
            return ['ok' => true, 'path' => '', 'msg' => ''];
        }
        if ($err !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'path' => '', 'msg' => 'Gagal upload berkas layanan.'];
        }
        if (!is_dir($layananAssetDir) && !mkdir($layananAssetDir, 0775, true) && !is_dir($layananAssetDir)) {
            return ['ok' => false, 'path' => '', 'msg' => 'Folder uploads/layanan_assets tidak dapat dibuat.'];
        }
        $tmp = (string) ($fileSpec['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            return ['ok' => false, 'path' => '', 'msg' => 'File upload layanan tidak valid.'];
        }
        $orig = (string) ($fileSpec['name'] ?? '');
        $ext = strtolower((string) pathinfo($orig, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt, true)) {
            return ['ok' => false, 'path' => '', 'msg' => 'Ekstensi file tidak didukung untuk lampiran layanan.'];
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? (string) finfo_file($finfo, $tmp) : '';
        if ($finfo) {
            finfo_close($finfo);
        }
        if ($mime === '' || !in_array($mime, $allowedMime, true)) {
            return ['ok' => false, 'path' => '', 'msg' => 'Tipe file tidak didukung untuk lampiran layanan.'];
        }
        $targetBase = $prefix . '_' . date('YmdHis') . '_' . (string) mt_rand(1000, 999999) . '.' . $ext;
        $targetFs = $layananAssetDir . DIRECTORY_SEPARATOR . $targetBase;
        if (!move_uploaded_file($tmp, $targetFs)) {
            return ['ok' => false, 'path' => '', 'msg' => 'Gagal menyimpan lampiran layanan ke server.'];
        }

        return ['ok' => true, 'path' => $layananAssetWebPrefix . $targetBase, 'msg' => ''];
    };
    if (is_array($rowsIn)) {
        foreach ($rowsIn as $i => $r) {
            if (!is_array($r)) {
                continue;
            }
            $kat = org_dokumen_normalize_tim_kategori((string) ($r['kategori'] ?? 'Kelembagaan'));
            if (!in_array($kat, $layananKategoriList, true)) {
                continue;
            }
            $nama = trim(org_sanitize_plain((string) ($r['nama'] ?? '')));
            $des = trim(org_sanitize_plain((string) ($r['deskripsi'] ?? '')));
            $link = org_layanan_integrasi_url_normalize((string) ($r['link'] ?? ''));
            $pinLabel = trim(org_sanitize_plain((string) ($r['pin_label'] ?? '')));
            $pinLabel = $pinLabel !== '' ? (function_exists('mb_substr') ? mb_substr($pinLabel, 0, 40, 'UTF-8') : substr($pinLabel, 0, 40)) : '';
            $pinPosition = (string) ($r['pin_position'] ?? '');
            if ($pinPosition !== 'before' && $pinPosition !== 'after') {
                $pinPosition = '';
            }
            if ($pinLabel === '') {
                $pinPosition = '';
            }
            $urutan = (int) ($r['urutan'] ?? 0);
            if ($urutan < 0) {
                $urutan = 0;
            }
            if ($urutan > 9999) {
                $urutan = 9999;
            }
            $imgExisting = $normalizeAssetPath((string) ($r['media_image_existing'] ?? ''));
            $docExistingLegacy = $normalizeAssetPath((string) ($r['media_document_existing'] ?? ''));
            $docExistingList = $normalizeAssetList($r['media_documents_existing'] ?? '', $normalizeAssetPath);
            if ($docExistingLegacy !== '' && !in_array($docExistingLegacy, $docExistingList, true)) {
                $docExistingList[] = $docExistingLegacy;
            }
            $imgNew = ['ok' => true, 'path' => '', 'msg' => ''];
            if (isset($_FILES['layanan_media_image']) && is_array($_FILES['layanan_media_image'])) {
                $imgNew = $saveUpload(
                    [
                        'name' => $_FILES['layanan_media_image']['name'][$i] ?? '',
                        'type' => $_FILES['layanan_media_image']['type'][$i] ?? '',
                        'tmp_name' => $_FILES['layanan_media_image']['tmp_name'][$i] ?? '',
                        'error' => $_FILES['layanan_media_image']['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                        'size' => $_FILES['layanan_media_image']['size'][$i] ?? 0,
                    ],
                    ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                    ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
                    'layanan_img'
                );
                if (!$imgNew['ok']) {
                    $flashErr = (string) $imgNew['msg'];
                    break;
                }
            }
            $docNew = ['ok' => true, 'paths' => [], 'msg' => ''];
            if (isset($_FILES['layanan_media_document']) && is_array($_FILES['layanan_media_document'])) {
                $docNames = $_FILES['layanan_media_document']['name'][$i] ?? [];
                $docTypes = $_FILES['layanan_media_document']['type'][$i] ?? [];
                $docTmps = $_FILES['layanan_media_document']['tmp_name'][$i] ?? [];
                $docErrs = $_FILES['layanan_media_document']['error'][$i] ?? [];
                $docSizes = $_FILES['layanan_media_document']['size'][$i] ?? [];
                if (is_array($docNames)) {
                    foreach ($docNames as $j => $docNameEntry) {
                        $one = $saveUpload(
                            [
                                'name' => (string) $docNameEntry,
                                'type' => $docTypes[$j] ?? '',
                                'tmp_name' => $docTmps[$j] ?? '',
                                'error' => $docErrs[$j] ?? UPLOAD_ERR_NO_FILE,
                                'size' => $docSizes[$j] ?? 0,
                            ],
                            ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
                            [
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            ],
                            'layanan_doc'
                        );
                        if (!$one['ok']) {
                            $flashErr = (string) $one['msg'];
                            break 2;
                        }
                        if ($one['path'] !== '') {
                            $docNew['paths'][] = (string) $one['path'];
                        }
                    }
                }
            }
            $imgPath = (string) ($imgNew['path'] !== '' ? $imgNew['path'] : $imgExisting);
            $docPaths = array_values(array_unique(array_merge($docExistingList, (array) ($docNew['paths'] ?? []))));
            if ($nama === '' && $des === '' && $imgPath === '' && $docPaths === [] && $link === '') {
                continue;
            }
            $nextRows[] = [
                'kategori' => $kat,
                'nama' => $nama,
                'deskripsi' => $des,
                'media_image' => $imgPath,
                'media_document' => $docPaths[0] ?? '',
                'media_documents' => $docPaths,
                'link' => $link,
                'pin_label' => $pinLabel,
                'pin_position' => $pinPosition,
                'urutan' => $urutan,
            ];
        }
    }
    if ($flashErr === '') {
        $jsonOut = json_encode($nextRows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($jsonOut === false) {
            $flashErr = 'Gagal menyusun data layanan.';
        } elseif (file_put_contents($layananDataFile, $jsonOut) === false) {
            $flashErr = 'Gagal menyimpan Manajemen Layanan.';
        } else {
            $layananRows = $nextRows;
            if ($db !== null) {
                org_audit_log_insert($db, $idAdminSess ?? (string) ($_SESSION['admin_username'] ?? 'admin'), $namaAdminSess ?? (string) ($_SESSION['admin_display'] ?? 'Admin'), 'Memperbarui Manajemen Layanan (nama layanan + deskripsi/SOP + lampiran integrasi).');
            }
            $flashOk = 'Manajemen Layanan berhasil diperbarui.';
        }
    }
}
if ($isPost && $csrfValid && $postedAction === 'save_konten_dashboard') {
    if ($isSubAdminActor) {
        $flashErr = 'Akses ditolak. Sub-Admin tidak dapat mengubah Pengaturan Visi Misi.';
    } elseif ($db === null) {
        $flashErr = 'Tidak dapat terhubung ke database. Periksa config/database.php.';
    } else {
        org_site_content_ensure_installed($db);
        [$okVal, $msgVal] = org_validate_site_content_post($_POST);
        if (!$okVal) {
            $flashErr = $msgVal;
        } else {
            $payload = [
                'profile_visi' => (string) ($_POST['profile_visi'] ?? ''),
                'profile_misi' => (string) ($_POST['profile_misi'] ?? ''),
                'profile_struktur' => (string) ($_POST['profile_struktur'] ?? ''),
                'struktur_blurb' => (string) ($_POST['struktur_blurb'] ?? ''),
                'organisasi_intro' => (string) ($_POST['organisasi_intro'] ?? ''),
                'pengumuman' => (string) ($_POST['pengumuman'] ?? ''),
            ];
            $idAdmin = (string) ($_SESSION['admin_username'] ?? 'admin');
            $namaAdmin = (string) ($_SESSION['admin_display'] ?? $idAdmin);
            if ($db !== null && org_site_content_save_full($db, $payload, $idAdmin, $namaAdmin, $siteSettingsFile)) {
                $siteSettings = array_merge($siteSettings, [
                    'profile_visi' => org_sanitize_rich_html($payload['profile_visi']),
                    'profile_misi' => org_sanitize_rich_html($payload['profile_misi']),
                    'profile_struktur' => org_sanitize_plain($payload['profile_struktur']),
                    'struktur_blurb' => org_sanitize_plain($payload['struktur_blurb']),
                    'organisasi_intro' => org_sanitize_plain($payload['organisasi_intro']),
                    'pengumuman' => org_sanitize_plain($payload['pengumuman']),
                ]);
                $flashOk = 'Konten berhasil disimpan. Riwayat audit telah dicatat.';
            } else {
                $flashErr = 'Gagal menyimpan ke database.';
            }
        }
    }
}

$idAdminSess = (string) ($_SESSION['admin_username'] ?? 'admin');
$namaAdminSess = (string) ($_SESSION['admin_display'] ?? $idAdminSess);

if ($isPost && $csrfValid && $postedAction === 'upload') {
    if ($isSubAdminPublikasiActor) {
        $flashErr = 'Akses ditolak.';
    } else {
        $uploadResult = org_dokumen_process_upload(
            isset($_FILES['dokumen']) && is_array($_FILES['dokumen']) ? $_FILES['dokumen'] : null,
            (string) ($_POST['dokumen_kategori'] ?? '')
        );
        if ($uploadResult['type'] === 'success') {
            $flashOk = $uploadResult['message'];
            if ($db !== null) {
                org_audit_log_insert($db, $idAdminSess, $namaAdminSess, 'Mengunggah dokumen ke perpustakaan digital.');
            }
        } else {
            $flashErr = $uploadResult['message'];
        }
    }
}

if ($isPost && $csrfValid && $postedAction === 'delete_file') {
    if ($isSubAdminPublikasiActor) {
        $flashErr = 'Akses ditolak.';
    } else {
        $deleteResult = org_dokumen_delete_library_file((string) ($_POST['file_name'] ?? ''));
        if ($deleteResult['type'] === 'success') {
            $flashOk = $deleteResult['message'];
            if ($db !== null) {
                org_audit_log_insert($db, $idAdminSess, $namaAdminSess, 'Menghapus dokumen perpustakaan digital: ' . basename((string) ($_POST['file_name'] ?? '')));
            }
        } else {
            $flashErr = $deleteResult['message'];
        }
    }
}

if ($isPost && $csrfValid && $postedAction === 'gallery_upload') {
    if ($db === null || !org_galeri_kegiatan_table_exists($db)) {
        $flashErr = 'Tabel galeri tidak tersedia. Periksa koneksi database atau muat ulang halaman.';
    } elseif (!isset($_FILES['foto_kegiatan']) || !is_array($_FILES['foto_kegiatan'])) {
        $flashErr = 'Pilih file gambar terlebih dahulu.';
    } else {
        $judul = org_sanitize_plain((string) ($_POST['judul_kegiatan'] ?? ''));
        if (strlen($judul) > 255) {
            $judul = substr($judul, 0, 255);
        }
        if ($judul === '') {
            $flashErr = 'Judul kegiatan wajib diisi.';
        } else {
            $file = $_FILES['foto_kegiatan'];
            $maxBytes = 2 * 1024 * 1024;
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $flashErr = 'Gagal mengunggah file. Pastikan format gambar didukung dan ukuran maksimal 2MB.';
            } elseif (($file['size'] ?? 0) > $maxBytes) {
                $flashErr = 'Ukuran gambar melebihi 2MB.';
            } else {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = $finfo ? (string) finfo_file($finfo, $file['tmp_name']) : '';
                if ($finfo) {
                    finfo_close($finfo);
                }
                $ext = '';
                if ($mime === 'image/jpeg') {
                    $ext = 'jpg';
                } elseif ($mime === 'image/png') {
                    $ext = 'png';
                } elseif ($mime === 'image/webp') {
                    $ext = 'webp';
                } elseif ($mime === 'image/gif') {
                    $ext = 'gif';
                }
                if ($ext === '') {
                    $flashErr = 'Hanya file gambar JPG, JPEG, PNG, WebP, atau GIF yang diperbolehkan.';
                } else {
                    $baseName = 'gal_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $targetPath = $galeriImgDir . DIRECTORY_SEPARATOR . $baseName;
                    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                        $flashErr = 'Gagal menyimpan file ke folder assets/img/galeri/.';
                    } elseif (!org_galeri_kegiatan_insert($db, $judul, $baseName)) {
                        @unlink($targetPath);
                        $flashErr = 'Gagal menyimpan data ke database.';
                    } else {
                        org_audit_log_insert($db, $idAdminSess, $namaAdminSess, 'Menambah foto galeri kegiatan: "' . $judul . '" (berkas: ' . $baseName . ').');
                        $flashOk = 'Foto galeri berhasil diunggah. Aktivitas dicatat di audit trail.';
                    }
                }
            }
        }
    }
}

if ($isPost && $csrfValid && $postedAction === 'gallery_delete') {
    if ($db === null || !org_galeri_kegiatan_table_exists($db)) {
        $flashErr = 'Tabel galeri tidak tersedia.';
    } else {
        $delId = (int) ($_POST['gallery_id'] ?? 0);
        if ($delId < 1) {
            $flashErr = 'Permintaan hapus tidak valid.';
        } else {
            $row = org_galeri_kegiatan_fetch_by_id($db, $delId);
            if ($row === null) {
                $flashErr = 'Item galeri tidak ditemukan.';
            } else {
                $fn = basename((string) ($row['nama_file'] ?? ''));
                if ($fn !== '') {
                    $full = $galeriImgDir . DIRECTORY_SEPARATOR . $fn;
                    $galeriReal = realpath($galeriImgDir);
                    $fileReal = is_file($full) ? realpath($full) : false;
                    if (
                        $galeriReal !== false
                        && $fileReal !== false
                        && strpos((string) $fileReal, (string) $galeriReal) === 0
                    ) {
                        @unlink($fileReal);
                    }
                }
                if (!org_galeri_kegiatan_delete_by_id($db, $delId)) {
                    $flashErr = 'Gagal menghapus entri dari database.';
                } else {
                    $jt = org_sanitize_plain((string) ($row['judul'] ?? ''));
                    org_audit_log_insert($db, $idAdminSess, $namaAdminSess, 'Menghapus foto galeri kegiatan: "' . $jt . '" (id: ' . $delId . ').');
                    $flashOk = 'Foto galeri dihapus. Aktivitas dicatat di audit trail.';
                }
            }
        }
    }
}

if ($isPost && $csrfValid && $postedAction === 'gallery_update_text') {
    if ($db === null || !org_galeri_kegiatan_table_exists($db)) {
        $flashErr = 'Tabel galeri tidak tersedia.';
    } else {
        $editId = (int) ($_POST['gallery_id'] ?? 0);
        $judulBaru = org_sanitize_plain(trim((string) ($_POST['gallery_judul'] ?? '')));
        if (strlen($judulBaru) > 255) {
            $judulBaru = substr($judulBaru, 0, 255);
        }
        if ($editId < 1) {
            $flashErr = 'Data galeri tidak valid.';
        } elseif ($judulBaru === '') {
            $flashErr = 'Judul/caption galeri wajib diisi.';
        } else {
            $rowOld = org_galeri_kegiatan_fetch_by_id($db, $editId);
            if ($rowOld === null) {
                $flashErr = 'Item galeri tidak ditemukan.';
            } elseif (org_galeri_kegiatan_update_judul_by_id($db, $editId, $judulBaru)) {
                org_audit_log_insert($db, $idAdminSess, $namaAdminSess, 'Memperbarui caption foto galeri id ' . $editId . ' menjadi: "' . $judulBaru . '".');
                $flashOk = 'Teks galeri berhasil diperbarui.';
            } else {
                $flashErr = 'Gagal memperbarui caption galeri.';
            }
        }
    }
}

if ($isPost && $csrfValid && $postedAction === 'staff_update_email') {
    if ($isSubAdminActor) {
        $flashErr = 'Akses ditolak. Sub-Admin tidak dapat mengelola akun pengguna.';
    } elseif ($db === null || !org_staff_users_table_exists($db)) {
        $flashErr = 'Tabel users belum tersedia. Impor berkas install/users_staff.sql di phpMyAdmin.';
    } else {
        $sid = (int) ($_POST['staff_user_id'] ?? 0);
        $emailRaw = trim((string) ($_POST['email_google'] ?? ''));
        if ($sid < 1) {
            $flashErr = 'Data staf tidak valid.';
        } elseif ($emailRaw !== '' && filter_var($emailRaw, FILTER_VALIDATE_EMAIL) === false) {
            $flashErr = 'Format alamat email tidak valid.';
        } else {
            $urow = org_staff_users_fetch_by_id($db, $sid);
            if ($urow === null) {
                $flashErr = 'Staf tidak ditemukan.';
            } elseif (org_staff_users_update_email_google($db, $sid, $emailRaw)) {
                $namaAudit = org_sanitize_plain((string) ($urow['nama'] ?? $urow['username'] ?? 'staf'));
                org_audit_log_insert($db, $idAdminSess, $namaAdminSess, 'Admin memperbarui email Google untuk ' . $namaAudit . '.');
                $flashOk = 'Email Google staf telah diperbarui dan dicatat di audit trail.';
            } else {
                $flashErr = 'Gagal memperbarui email.';
            }
        }
    }
}

if ($isPost && $csrfValid && $postedAction === 'staff_reset_password') {
    if ($isSubAdminActor) {
        $flashErr = 'Akses ditolak. Sub-Admin tidak dapat mengelola akun pengguna.';
    } elseif ($db === null || !org_staff_users_table_exists($db)) {
        $flashErr = 'Tabel users belum tersedia. Impor berkas install/users_staff.sql di phpMyAdmin.';
    } else {
        $sid = (int) ($_POST['staff_user_id'] ?? 0);
        $pwd = (string) ($_POST['new_password'] ?? '');
        if ($sid < 1) {
            $flashErr = 'Data staf tidak valid.';
        } elseif (strlen($pwd) < 8) {
            $flashErr = 'Password baru minimal 8 karakter.';
        } else {
            $urow = org_staff_users_fetch_by_id($db, $sid);
            if ($urow === null) {
                $flashErr = 'Staf tidak ditemukan.';
            } else {
                $hash = password_hash($pwd, PASSWORD_DEFAULT);
                if ($hash === false) {
                    $flashErr = 'Gagal membuat hash password.';
                } elseif (org_staff_users_update_password_hash($db, $sid, $hash)) {
                    $namaAudit = org_sanitize_plain((string) ($urow['nama'] ?? $urow['username'] ?? 'staf'));
                    org_audit_log_insert($db, $idAdminSess, $namaAdminSess, 'Admin mereset password untuk ' . $namaAudit . '.');
                    $flashOk = 'Password staf telah direset dan dicatat di audit trail.';
                } else {
                    $flashErr = 'Gagal menyimpan password baru.';
                }
            }
        }
    }
}

if ($isPost && $csrfValid && $postedAction === 'staff_delete_user') {
    if ($isSubAdminActor) {
        $flashErr = 'Akses ditolak. Sub-Admin tidak dapat mengelola akun pengguna.';
    } elseif ($db === null || !org_staff_users_table_exists($db)) {
        $flashErr = 'Tabel users belum tersedia.';
    } elseif (!$canDeleteStaffAccount) {
        $flashErr = 'Hanya Admin atau Super Admin yang dapat menghapus akun staf.';
    } else {
        $delSid = (int) ($_POST['staff_user_id'] ?? 0);
        if ($delSid < 1) {
            $flashErr = 'Permintaan hapus tidak valid.';
        } else {
            $delRow = org_staff_users_fetch_by_id($db, $delSid);
            if ($delRow === null) {
                $flashErr = 'Akun staf tidak ditemukan.';
            } elseif (org_staff_role_normalize((string) ($delRow['level'] ?? '')) === 'super_admin') {
                $flashErr = 'Akun Super Admin tidak dapat dihapus.';
            } elseif (org_staff_users_delete_by_id($db, $delSid)) {
                $namaDel = org_sanitize_plain((string) ($delRow['nama'] ?? $delRow['username'] ?? 'staf'));
                $deleteActorLabel = $isSuperAdminActor ? 'Super Admin' : 'Admin';
                org_audit_log_insert(
                    $db,
                    $idAdminSess,
                    $namaAdminSess,
                    $deleteActorLabel . ' menghapus akun staf: «' . $namaDel . '» (username: ' . (string) ($delRow['username'] ?? '') . ').'
                );
                $flashOk = 'Akun staf telah dihapus dan dicatat di audit trail.';
            } else {
                $flashErr = 'Gagal menghapus akun dari database.';
            }
        }
    }
}

if ($isPost && $csrfValid && $postedAction === 'staff_edit_user') {
    if ($isSubAdminActor) {
        $flashErr = 'Akses ditolak. Sub-Admin tidak dapat mengelola akun pengguna.';
    } elseif ($db === null || !org_staff_users_table_exists($db)) {
        $flashErr = 'Tabel users belum tersedia. Impor berkas install/users_staff.sql di phpMyAdmin.';
    } else {
        $sid = (int) ($_POST['staff_user_id'] ?? 0);
        $namaEdit = org_sanitize_plain((string) ($_POST['nama_lengkap'] ?? ''));
        $userEdit = trim((string) ($_POST['username'] ?? ''));
        $emailEdit = trim((string) ($_POST['email_google'] ?? ''));
        $pwdEdit = (string) ($_POST['password'] ?? '');
        $levelEditRaw = trim((string) ($_POST['level'] ?? $_POST['role'] ?? ''));
        $levelEditNorm = org_staff_role_normalize($levelEditRaw);
        if (!in_array($levelEditNorm, ['super_admin', 'admin', 'sub_admin_eorganisasi', 'sub_admin_publikasi', 'staf_disposisi'], true)) {
            $flashErr = 'Level/role akses tidak valid.';
        } elseif ($sid < 1) {
            $flashErr = 'Data staf tidak valid.';
        } elseif ($namaEdit === '') {
            $flashErr = 'Nama lengkap wajib diisi.';
        } else {
            $usernameErr = org_staff_users_validate_username($userEdit);
            if ($usernameErr !== null) {
                $flashErr = $usernameErr;
            } elseif ($emailEdit !== '' && filter_var($emailEdit, FILTER_VALIDATE_EMAIL) === false) {
                $flashErr = 'Format email Google tidak valid.';
            } elseif ($pwdEdit !== '' && strlen($pwdEdit) < 8) {
                $flashErr = 'Password baru minimal 8 karakter (kosongkan jika tidak ingin mengubah password).';
            } elseif (!$isSuperAdminActor && $levelEditNorm === 'super_admin') {
                $flashErr = 'Hanya Super Admin yang dapat menetapkan peran Super Admin.';
            } elseif (str_starts_with($sessionRoleNorm, 'sub_admin_') && $levelEditNorm !== $sessionRoleNorm) {
                $flashErr = 'Anda hanya dapat mengubah akun dengan level yang sama.';
            } elseif (org_staff_users_username_taken($db, $userEdit, $sid)) {
                $flashErr = 'Username sudah dipakai oleh akun lain. Pilih username lain.';
            } else {
                $urowEdit = org_staff_users_fetch_by_id($db, $sid);
                if ($urowEdit === null) {
                    $flashErr = 'Staf tidak ditemukan.';
                } elseif (org_staff_role_normalize((string) ($urowEdit['level'] ?? '')) === 'super_admin' && !$isSuperAdminActor) {
                    $flashErr = 'Hanya Super Admin yang dapat mengubah akun Super Admin.';
                } else {
                    $pwdHashEdit = null;
                    if ($pwdEdit !== '') {
                        $pwdHashEdit = password_hash($pwdEdit, PASSWORD_DEFAULT);
                        if ($pwdHashEdit === false) {
                            $flashErr = 'Gagal membuat hash password.';
                            $pwdHashEdit = null;
                        }
                    }
                    if ($flashErr === '' && org_staff_users_update($db, $sid, $userEdit, $namaEdit, $emailEdit, $levelEditNorm, $pwdHashEdit)) {
                        $namaAudit = org_sanitize_plain((string) ($urowEdit['nama'] ?? $urowEdit['username'] ?? 'staf'));
                        $auditDetail = 'Admin memperbarui akun staf «' . $namaAudit . '» (username: ' . $userEdit . ', level: ' . $levelEditNorm . ')';
                        if ($pwdHashEdit !== null) {
                            $auditDetail .= ', termasuk password baru';
                        }
                        $auditDetail .= '.';
                        org_audit_log_insert($db, $idAdminSess, $namaAdminSess, $auditDetail);
                        $flashOk = 'Data staf berhasil diperbarui.';
                    } elseif ($flashErr === '') {
                        $flashErr = 'Gagal menyimpan perubahan akun staf. Periksa struktur tabel users (kolom level/role).';
                    }
                }
            }
        }
    }
}

if ($isPost && $csrfValid && $postedAction === 'staff_add_pegawai') {
    if ($isSubAdminActor) {
        $flashErr = 'Akses ditolak. Sub-Admin tidak dapat menambah akun pengguna.';
    } elseif ($db === null || !org_staff_users_table_exists($db)) {
        $flashErr = 'Tabel users belum tersedia. Impor berkas install/users_staff.sql (dan install/users_staff_add_role.sql jika kolom level belum ada).';
    } else {
        $namaBaru = org_sanitize_plain((string) ($_POST['nama_lengkap'] ?? ''));
        $userBaru = trim((string) ($_POST['username'] ?? ''));
        $pwdBaru = (string) ($_POST['password'] ?? '');
        $emailBaru = trim((string) ($_POST['email_google'] ?? ''));
        $levelBaruRaw = trim((string) ($_POST['level'] ?? $_POST['role'] ?? 'sub_admin_eorganisasi'));
        $levelNormAdd = org_staff_role_normalize($levelBaruRaw);
        if (!in_array($levelNormAdd, ['super_admin', 'admin', 'sub_admin_eorganisasi', 'sub_admin_publikasi', 'staf_disposisi'], true)) {
            $levelNormAdd = 'sub_admin_eorganisasi';
        }
        if ($namaBaru === '' || $userBaru === '') {
            $flashErr = 'Nama lengkap dan username wajib diisi.';
        } elseif ($pwdBaru === '' || strlen($pwdBaru) < 8) {
            $flashErr = 'Password wajib diisi (minimal 8 karakter).';
        } elseif ($emailBaru !== '' && filter_var($emailBaru, FILTER_VALIDATE_EMAIL) === false) {
            $flashErr = 'Format email Google tidak valid.';
        } elseif (!$isSuperAdminActor && $levelNormAdd === 'super_admin') {
            $flashErr = 'Hanya Super Admin yang dapat menetapkan peran Super Admin.';
        } elseif (str_starts_with($sessionRoleNorm, 'sub_admin_') && $levelNormAdd !== $sessionRoleNorm) {
            $flashErr = 'Anda hanya dapat menambahkan akun Sub Admin.';
        } else {
            $usernameErrAdd = org_staff_users_validate_username($userBaru);
            if ($usernameErrAdd !== null) {
                $flashErr = $usernameErrAdd;
            } elseif (org_staff_users_username_taken($db, $userBaru, 0)) {
                $flashErr = 'Username sudah dipakai. Pilih username lain.';
            } else {
                $hashBaru = password_hash($pwdBaru, PASSWORD_DEFAULT);
                if ($hashBaru === false) {
                    $flashErr = 'Gagal membuat hash password.';
                } elseif (org_staff_users_insert($db, $userBaru, $namaBaru, $emailBaru, $levelNormAdd, $hashBaru)) {
                    org_audit_log_insert(
                        $db,
                        $idAdminSess,
                        $namaAdminSess,
                        'Admin menambah pegawai baru: «' . $namaBaru . '» (username: ' . $userBaru . ', level: ' . $levelNormAdd . ').'
                    );
                    $flashOk = 'Pegawai baru berhasil ditambahkan dan tampil di tabel.';
                } else {
                    $flashErr = 'Gagal menyimpan pegawai. Akun User (disposisi) membutuhkan kolom `role` VARCHAR di tabel users jika `level` bertipe ENUM tanpa nilai staf_disposisi. Coba lagi (sistem akan mencoba menambah kolom otomatis); jika tetap gagal, jalankan skrip install/users_add_role_column.sql di phpMyAdmin atau beri hak ALTER pada user database.';
                }
            }
        }
    }
}

if ($isPost && $csrfValid && $postedAction === 'pengumuman_tambah') {
    if ($db === null) {
        $flashErr = 'Tidak dapat terhubung ke database.';
    } else {
        org_pengumuman_ensure_table($db);
        $judulP = org_sanitize_plain(trim((string) ($_POST['pengumuman_judul'] ?? '')));
        $teksP = org_sanitize_plain(trim((string) ($_POST['pengumuman_teks'] ?? '')));
        if ($judulP === '' || strlen($judulP) > 255) {
            $flashErr = 'Judul wajib diisi (maksimal 255 karakter).';
        } elseif ($teksP === '') {
            $flashErr = 'Teks pengumuman wajib diisi.';
        } elseif (!isset($_FILES['pengumuman_gambar']) || !is_array($_FILES['pengumuman_gambar'])) {
            $flashErr = 'Pilih file gambar brosur (JPG, PNG, WebP, atau GIF).';
        } else {
            $gf = $_FILES['pengumuman_gambar'];
            if ($gf['error'] !== UPLOAD_ERR_OK) {
                $flashErr = 'Gagal mengunggah gambar. Coba lagi.';
            } else {
                $maxB = 3 * 1024 * 1024;
                if (($gf['size'] ?? 0) > $maxB) {
                    $flashErr = 'Ukuran gambar maksimal 3 MB.';
                } else {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = $finfo !== false ? (string) finfo_file($finfo, $gf['tmp_name']) : '';
                    if ($finfo !== false) {
                        finfo_close($finfo);
                    }
                    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                    if (!in_array($mime, $allowed, true)) {
                        $flashErr = 'Format gambar tidak didukung. Gunakan JPG, PNG, WebP, atau GIF.';
                    } else {
                        $ext = match ($mime) {
                            'image/jpeg' => 'jpg',
                            'image/png' => 'png',
                            'image/webp' => 'webp',
                            default => 'gif',
                        };
                        $pengDir = org_pengumuman_upload_dir_fs();
                        if (!is_dir($pengDir)) {
                            @mkdir($pengDir, 0777, true);
                        }
                        $safeBase = 'brs_' . bin2hex(random_bytes(6)) . '.' . $ext;
                        $dest = $pengDir . DIRECTORY_SEPARATOR . $safeBase;
                        if (!move_uploaded_file($gf['tmp_name'], $dest)) {
                            $flashErr = 'Gagal menyimpan berkas gambar ke server.';
                        } elseif (org_pengumuman_insert($db, $judulP, $teksP, $safeBase)) {
                            org_audit_log_insert($db, $idAdminSess, $namaAdminSess, 'Admin menambah pengumuman/brosur: «' . $judulP . '».');
                            $flashOk = 'Pengumuman berhasil ditambahkan.';
                        } else {
                            @unlink($dest);
                            $flashErr = 'Gagal menyimpan data pengumuman ke database.';
                        }
                    }
                }
            }
        }
    }
}

if ($isPost && $csrfValid && $postedAction === 'pengumuman_hapus') {
    if ($db === null) {
        $flashErr = 'Tidak dapat terhubung ke database.';
    } else {
        $pid = (int) ($_POST['pengumuman_id'] ?? 0);
        if ($pid < 1) {
            $flashErr = 'Data hapus tidak valid.';
        } else {
            $rowH = org_pengumuman_fetch_by_id($db, $pid);
            if ($rowH === null) {
                $flashErr = 'Pengumuman tidak ditemukan.';
            } elseif (org_pengumuman_delete_by_id($db, $pid)) {
                org_audit_log_insert($db, $idAdminSess, $namaAdminSess, 'Admin menghapus pengumuman id ' . (string) $pid . ' («' . org_sanitize_plain((string) ($rowH['judul'] ?? '')) . '»).');
                $flashOk = 'Pengumuman dan berkas gambarnya telah dihapus.';
            } else {
                $flashErr = 'Gagal menghapus pengumuman.';
            }
        }
    }
}

if ($isPost && $csrfValid && $postedAction === 'pusat_informasi_tambah') {
    if ($db === null) {
        $flashErr = 'Tidak dapat terhubung ke database.';
    } else {
        org_pusat_informasi_ensure_table($db);
        $judulPi = org_sanitize_plain(trim((string) ($_POST['pusat_judul'] ?? '')));
        $katPi = strtolower(trim((string) ($_POST['pusat_kategori'] ?? 'berita')));
        if ($katPi !== 'pengumuman') {
            $katPi = 'berita';
        }
        $teksPi = org_sanitize_plain(trim((string) ($_POST['pusat_isi'] ?? '')));
        if ($judulPi === '' || strlen($judulPi) > 255) {
            $flashErr = 'Judul wajib diisi (maksimal 255 karakter).';
        } elseif ($teksPi === '') {
            $flashErr = 'Isi teks wajib diisi.';
        } elseif (!isset($_FILES['pusat_gambar']) || !is_array($_FILES['pusat_gambar'])) {
            $flashErr = 'Unggah gambar brosur atau poster (JPG, PNG, WebP, atau GIF).';
        } else {
            $gf = $_FILES['pusat_gambar'];
            if ($gf['error'] !== UPLOAD_ERR_OK) {
                $flashErr = 'Gagal mengunggah gambar.';
            } else {
                $maxB = 4 * 1024 * 1024;
                if (($gf['size'] ?? 0) > $maxB) {
                    $flashErr = 'Ukuran gambar maksimal 4 MB.';
                } else {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = $finfo !== false ? (string) finfo_file($finfo, $gf['tmp_name']) : '';
                    if ($finfo !== false) {
                        finfo_close($finfo);
                    }
                    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                    if (!in_array($mime, $allowed, true)) {
                        $flashErr = 'Format gambar tidak didukung.';
                    } else {
                        $ext = match ($mime) {
                            'image/jpeg' => 'jpg',
                            'image/png' => 'png',
                            'image/webp' => 'webp',
                            default => 'gif',
                        };
                        $piDir = org_pusat_informasi_upload_dir_fs();
                        if (!is_dir($piDir)) {
                            @mkdir($piDir, 0777, true);
                        }
                        $safeBase = 'pi_' . bin2hex(random_bytes(6)) . '.' . $ext;
                        $dest = $piDir . DIRECTORY_SEPARATOR . $safeBase;
                        if (!move_uploaded_file($gf['tmp_name'], $dest)) {
                            $flashErr = 'Gagal menyimpan gambar ke server.';
                        } elseif (org_pusat_informasi_insert($db, $judulPi, $katPi, $teksPi, $safeBase)) {
                            org_audit_log_insert($db, $idAdminSess, $namaAdminSess, 'Admin menambah entri Pusat Informasi: «' . $judulPi . '» (' . $katPi . ').');
                            $flashOk = 'Entri Pusat Informasi berhasil ditambahkan.';
                        } else {
                            @unlink($dest);
                            $flashErr = 'Gagal menyimpan ke database.';
                        }
                    }
                }
            }
        }
    }
}

if ($isPost && $csrfValid && $postedAction === 'pusat_informasi_hapus') {
    if ($db === null) {
        $flashErr = 'Tidak dapat terhubung ke database.';
    } else {
        $xid = (int) ($_POST['pusat_id'] ?? 0);
        if ($xid < 1) {
            $flashErr = 'Permintaan hapus tidak valid.';
        } else {
            $rowX = org_pusat_informasi_fetch_by_id($db, $xid);
            if ($rowX === null) {
                $flashErr = 'Data tidak ditemukan.';
            } elseif (org_pusat_informasi_delete_by_id($db, $xid)) {
                org_audit_log_insert($db, $idAdminSess, $namaAdminSess, 'Admin menghapus Pusat Informasi id ' . (string) $xid . ' («' . org_sanitize_plain((string) ($rowX['judul'] ?? '')) . '»).');
                $flashOk = 'Postingan dan berkas gambarnya telah dihapus.';
            } else {
                $flashErr = 'Gagal menghapus data.';
            }
        }
    }
}

if ($isPost && $csrfValid && $postedAction === 'pusat_informasi_update_text') {
    if ($db === null) {
        $flashErr = 'Tidak dapat terhubung ke database.';
    } else {
        org_pusat_informasi_ensure_table($db);
        $xid = (int) ($_POST['pusat_id'] ?? 0);
        $judulBaru = org_sanitize_plain(trim((string) ($_POST['pusat_judul'] ?? '')));
        $katBaru = strtolower(trim((string) ($_POST['pusat_kategori'] ?? 'berita')));
        if ($katBaru !== 'pengumuman') {
            $katBaru = 'berita';
        }
        $isiBaru = org_sanitize_plain(trim((string) ($_POST['pusat_isi'] ?? '')));
        if ($xid < 1) {
            $flashErr = 'Data postingan tidak valid.';
        } elseif ($judulBaru === '' || strlen($judulBaru) > 255) {
            $flashErr = 'Judul wajib diisi (maksimal 255 karakter).';
        } elseif ($isiBaru === '') {
            $flashErr = 'Isi teks wajib diisi.';
        } else {
            $rowOld = org_pusat_informasi_fetch_by_id($db, $xid);
            if ($rowOld === null) {
                $flashErr = 'Postingan tidak ditemukan.';
            } elseif (org_pusat_informasi_update_text_by_id($db, $xid, $judulBaru, $katBaru, $isiBaru)) {
                org_audit_log_insert($db, $idAdminSess, $namaAdminSess, 'Memperbarui teks postingan Pusat Informasi id ' . $xid . ' («' . $judulBaru . '», kategori: ' . $katBaru . ').');
                $flashOk = 'Teks berhasil diperbarui.';
            } else {
                $flashErr = 'Gagal memperbarui teks postingan.';
            }
        }
    }
}

if ($isPost && $csrfValid && $postedAction === 'pusat_informasi_featured') {
    if ($db === null) {
        $flashErr = 'Tidak dapat terhubung ke database.';
    } else {
        org_pusat_informasi_ensure_table($db);
        $xid = (int) ($_POST['pusat_id'] ?? 0);
        $featuredOn = isset($_POST['featured']) && (string) ($_POST['featured'] ?? '') === '1';
        if ($xid < 1) {
            $flashErr = 'Permintaan tidak valid.';
        } else {
            $rowF = org_pusat_informasi_fetch_by_id($db, $xid);
            if ($rowF === null) {
                $flashErr = 'Data tidak ditemukan.';
            } elseif (org_pusat_informasi_set_featured($db, $xid, $featuredOn)) {
                $judulPlain = org_sanitize_plain((string) ($rowF['judul'] ?? ''));
                $aksiTxt = $featuredOn
                    ? 'Admin menandai «' . $judulPlain . '» sebagai berita utama (tampil di depan beranda).'
                    : 'Admin membatalkan tanda berita utama untuk «' . $judulPlain . '».';
                org_audit_log_insert($db, $idAdminSess, $namaAdminSess, $aksiTxt);
                $flashOk = $featuredOn ? 'Entri diutamakan di bagian atas beranda.' : 'Entri tidak lagi ditandai sebagai berita utama.';
            } else {
                $flashErr = 'Gagal memperbarui status berita utama.';
            }
        }
    }
}

$galeriRows = [];
$galleryUrls = [];
if ($db !== null && org_galeri_kegiatan_table_exists($db)) {
    $galeriRows = org_galeri_kegiatan_fetch_all($db);
    foreach ($galeriRows as $gr) {
        $f = basename((string) ($gr['nama_file'] ?? ''));
        if ($f !== '') {
            $galleryUrls[] = '../' . ORG_GALERI_WEB_DIR . '/' . rawurlencode($f);
        }
    }
} elseif (is_dir($galeriImgDir)) {
    foreach (scandir($galeriImgDir) ?: [] as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        if (preg_match('/\.(jpe?g|png|gif|webp)$/i', $file)) {
            $galleryUrls[] = '../' . ORG_GALERI_WEB_DIR . '/' . rawurlencode($file);
        }
    }
    natsort($galleryUrls);
    $galleryUrls = array_values($galleryUrls);
}

$staffUsersTableOk = $db !== null && org_staff_users_table_exists($db);
$staffUserRows = [];
if ($staffUsersTableOk) {
    $staffUserRows = org_staff_users_fetch_all($db);
}

$auditRows = [];
if ($db !== null && $auditRiwayatVisible) {
    $auditRows = org_audit_logs_fetch_visible_rows($db, 40);
}

$pusatInformasiList = [];
if ($db !== null) {
    org_pusat_informasi_ensure_table($db);
    $pusatInformasiList = org_pusat_informasi_fetch_all($db, 100);
}

$adminName = htmlspecialchars((string) ($_SESSION['admin_display'] ?? 'Admin'), ENT_QUOTES, 'UTF-8');
$adminRoleLabel = htmlspecialchars(org_staff_role_label($sessionRoleNorm), ENT_QUOTES, 'UTF-8');

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'dashboard_metrics.php';
$dashDocFileCount = count($dashLibraryFiles) > 0 ? count($dashLibraryFiles) : count($dashDocFiles);
$dashMetrics = admin_dashboard_collect_metrics(
    $db,
    $dashDocRanked,
    $pusatInformasiList,
    $galleryUrls,
    $staffUserRows,
    $auditRows,
    $dashDocFileCount
);
$dashMetrics['layanan_total'] = count($layananRows);
$dashMetrics['kinerja_pegawai_pct'] = (int) ($dashMetrics['team_progress_pct'] > 0
    ? $dashMetrics['team_progress_pct']
    : min(100, (int) $dashMetrics['staf_total'] * 15));
$saranN = (int) ($dashMetrics['saran_total'] ?? 0);
$dashMetrics['kepuasan_publik'] = (int) min(100, max(0, round(
    ((int) ($dashMetrics['kpi_pelayanan'] ?? 0)) * 0.55
    + max(0, 100 - min(40, $saranN * 4)) * 0.45
)));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <script>try{var o=localStorage.getItem('org-color-theme'),s=localStorage.getItem('sg-dashboard-theme');if(o==='dark'||s==='dark')document.documentElement.setAttribute('data-theme','dark');else if(o==='light')document.documentElement.removeAttribute('data-theme');}catch(e){}</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
    <title>Smart Governance Monitoring — Bagian Organisasi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <?php
    require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_mobile_assets.php';
    require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_motion_assets.php';
    echo org_mobile_stylesheet_link();
    echo org_motion_stylesheet_link();
    ?>
    <?php require __DIR__ . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'dashboard_premium_styles.php'; ?>
    <?php require __DIR__ . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'dashboard_smart_governance_styles.php'; ?>
    <style>
        .gallery-wrap { border-radius: 20px; overflow: hidden; box-shadow: var(--adm-shadow); margin-bottom: 2rem; }
        .gallery-wrap .swiper-slide img { width: 100%; height: 320px; object-fit: cover; display: block; }
        @media (max-width: 576px) { .gallery-wrap .swiper-slide img { height: 200px; } }
        .admin-doc-search-wrap { position: relative; }
        .admin-doc-search-wrap .admin-doc-search-icon {
            position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
            width: 17px; height: 17px; color: #94a3b8; pointer-events: none;
        }
        .admin-doc-search-wrap .form-control { padding-left: 2.35rem; border-radius: 12px; }
        .audit-table { font-size: 0.82rem; }
    </style>
</head>
<body class="dash-premium sg-dashboard <?php echo $isSubAdminEorgActor ? 'mode-eorganisasi' : ($isSubAdminPublikasiActor ? 'mode-publikasi' : ''); ?>">
<?php if ($isSubAdminEorgActor): ?>
<style>
    #panel-konten-tabs { display: none !important; }
</style>
<?php endif; ?>
<?php require __DIR__ . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'dashboard_smart_layout_start.php'; ?>
        <?php if ($isSubAdminEorgActor): ?>
            <div class="alert alert-info border-0 shadow-sm sg-fade-in">
                Mode E-Organisasi aktif. Fitur publikasi disembunyikan — gunakan sidebar untuk modul internal.
            </div>
        <?php elseif ($isSubAdminPublikasiActor): ?>
            <div class="alert alert-primary border-0 shadow-sm sg-fade-in">
                Mode Publikasi aktif. Gunakan sidebar menu Layanan Publik.
            </div>
        <?php endif; ?>
        <?php if ($db === null): ?>
            <div class="alert alert-warning sg-fade-in">Tidak dapat terhubung ke database.</div>
        <?php endif; ?>
        <?php if ($flashOk !== ''): ?>
            <div class="alert alert-success alert-dismissible fade show sg-fade-in"><?php echo htmlspecialchars($flashOk, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($flashErr !== ''): ?>
            <div class="alert alert-danger alert-dismissible fade show sg-fade-in"><?php echo htmlspecialchars($flashErr, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div id="sgMonitoring" class="sg-view sg-view--active">
        <?php require __DIR__ . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'dashboard_monitoring.php'; ?>
        </div>

        <?php require __DIR__ . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'dashboard_operations.php'; ?>

        </main>
    </div>
</div>

    <footer class="footer-saran">
        <div class="container">
            <h2>Saran &amp; kritik</h2>
            <p class="small text-secondary mb-3">Kirim masukan untuk perbaikan layanan (tanpa memuat ulang halaman).</p>
            <div class="row">
                <div class="col-lg-8">
                    <form id="formSaranDashboard" class="row g-2" method="post" action="<?php echo $prosesSaranUrlDash; ?>" data-saran-endpoint="<?php echo $prosesSaranUrlDash; ?>" novalidate>
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="nama" id="saran_nama_d" placeholder="Nama" required maxlength="190">
                        </div>
                        <div class="col-md-4">
                            <input type="email" class="form-control" name="email" id="saran_email_d" placeholder="Email" required maxlength="190">
                        </div>
                        <div class="col-12">
                            <textarea class="form-control" name="pesan" id="saran_pesan_d" rows="3" placeholder="Pesan" required maxlength="20000"></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary" id="btnSaranD">Kirim</button>
                            <div class="small mt-2" id="saranFeedbackD" role="status" aria-live="polite"></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </footer>

    <div class="modal fade" id="modalEditGalleryText" tabindex="-1" aria-labelledby="modalEditGalleryTextLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="dashboard.php#panel-galeri">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditGalleryTextLabel">Edit caption galeri</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="gallery_update_text">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="gallery_id" id="modal_gallery_edit_id" value="">
                        <label for="modal_gallery_edit_judul" class="form-label">Caption / Judul Foto</label>
                        <input type="text" class="form-control" name="gallery_judul" id="modal_gallery_edit_judul" maxlength="255" required autocomplete="off">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditPusatInformasi" tabindex="-1" aria-labelledby="modalEditPusatInformasiLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="post" action="dashboard.php#panel-pusat-informasi" autocomplete="off">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditPusatInformasiLabel">Edit teks Berita/Pengumuman</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="pusat_informasi_update_text">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="pusat_id" id="modal_pusat_edit_id" value="">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="modal_pusat_edit_judul" class="form-label">Judul</label>
                                <input type="text" class="form-control" name="pusat_judul" id="modal_pusat_edit_judul" maxlength="255" required>
                            </div>
                            <div class="col-md-4">
                                <label for="modal_pusat_edit_kategori" class="form-label">Kategori</label>
                                <select class="form-select" name="pusat_kategori" id="modal_pusat_edit_kategori" required>
                                    <option value="berita">Berita</option>
                                    <option value="pengumuman">Pengumuman</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="modal_pusat_edit_isi" class="form-label">Isi Teks</label>
                                <textarea class="form-control" name="pusat_isi" id="modal_pusat_edit_isi" rows="6" required></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label mb-2">Preview gambar saat ini</label>
                                <div class="border rounded p-2 bg-light d-inline-block">
                                    <img
                                        id="modal_pusat_edit_preview"
                                        src=""
                                        alt="Preview gambar postingan"
                                        class="rounded d-none"
                                        style="width: 96px; height: 96px; object-fit: cover;"
                                    >
                                    <p class="small text-muted mb-0" id="modal_pusat_edit_no_preview">Tidak ada gambar untuk postingan ini.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditEmailStaff" tabindex="-1" aria-labelledby="modalEditEmailStaffLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="dashboard.php#panel-manajemen-staf">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditEmailStaffLabel">Edit email Google</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="staff_update_email">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="staff_user_id" id="modal_edit_email_staff_id" value="">
                        <p class="small text-muted mb-3">Staf: <strong id="modal_edit_email_staff_nama">—</strong></p>
                        <label for="modal_edit_email_input" class="form-label">Alamat Gmail (untuk integrasi Google Drive)</label>
                        <input type="email" class="form-control" name="email_google" id="modal_edit_email_input" maxlength="255" placeholder="nama@gmail.com" autocomplete="off">
                        <p class="form-text small mb-0">Kosongkan untuk menghapus email (status akan menjadi Belum Terdaftar).</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditStaff" tabindex="-1" aria-labelledby="modalEditStaffLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="post" action="dashboard.php#panel-manajemen-staf" autocomplete="off">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditStaffLabel">Edit pegawai / staf</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="staff_edit_user">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="staff_user_id" id="modal_edit_staff_id" value="">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit_pegawai_nama" class="form-label">Nama lengkap</label>
                                <input type="text" class="form-control" name="nama_lengkap" id="edit_pegawai_nama" required maxlength="191">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_pegawai_username" class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" id="edit_pegawai_username" required minlength="3" maxlength="64" pattern="[A-Za-z0-9._-]+" title="3–64 karakter: huruf, angka, titik, garis bawah, tanda hubung">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_pegawai_email_google" class="form-label">Email Google (untuk Drive)</label>
                                <input type="email" class="form-control" name="email_google" id="edit_pegawai_email_google" maxlength="255" placeholder="nama@gmail.com (opsional)">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_pegawai_level" class="form-label">Level / role akses</label>
                                <select class="form-select" name="level" id="edit_pegawai_level" required>
                                    <?php if ($isSuperAdminActor): ?>
                                        <option value="super_admin">Super Admin</option>
                                    <?php endif; ?>
                                    <?php if ($isSuperAdminActor || $sessionRoleNorm === 'admin'): ?>
                                        <option value="admin">Admin</option>
                                    <?php endif; ?>
                                    <option value="sub_admin_eorganisasi">Sub Admin E-Organisasi</option>
                                    <option value="sub_admin_publikasi">Sub Admin Publikasi</option>
                                    <option value="staf_disposisi">User</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="edit_pegawai_password" class="form-label">Password baru <span class="text-muted fw-normal">(opsional)</span></label>
                                <input type="password" class="form-control" name="password" id="edit_pegawai_password" minlength="8" maxlength="128" autocomplete="new-password" placeholder="Kosongkan jika tidak mengubah password">
                                <div class="form-text small">Jika diisi, disimpan dengan <code>password_hash()</code>. Minimal 8 karakter.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTambahPegawai" tabindex="-1" aria-labelledby="modalTambahPegawaiLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="post" action="dashboard.php#panel-manajemen-staf" autocomplete="off">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTambahPegawaiLabel">Tambah pegawai baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="staff_add_pegawai">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="add_pegawai_nama" class="form-label">Nama lengkap</label>
                                <input type="text" class="form-control" name="nama_lengkap" id="add_pegawai_nama" required maxlength="191" placeholder="Nama sesuai identitas">
                            </div>
                            <div class="col-md-6">
                                <label for="add_pegawai_username" class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" id="add_pegawai_username" required minlength="3" maxlength="64" pattern="[A-Za-z0-9._-]+" title="Huruf, angka, titik, garis bawah, atau tanda hubung" placeholder="contoh: budi.setda">
                            </div>
                            <div class="col-md-6">
                                <label for="add_pegawai_password" class="form-label">Password</label>
                                <input type="password" class="form-control" name="password" id="add_pegawai_password" required minlength="8" maxlength="128" autocomplete="new-password" placeholder="Minimal 8 karakter">
                                <div class="form-text small">Disimpan dengan <code>password_hash()</code>.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="add_pegawai_email_google" class="form-label">Email Google (untuk Drive)</label>
                                <input type="email" class="form-control" name="email_google" id="add_pegawai_email_google" maxlength="255" placeholder="nama@gmail.com (opsional)">
                            </div>
                            <div class="col-12">
                                <label for="add_pegawai_level" class="form-label">Level Akses</label>
                                <select class="form-select" name="level" id="add_pegawai_level" required>
                                    <?php if ($isSuperAdminActor): ?>
                                        <option value="super_admin">Super Admin</option>
                                    <?php endif; ?>
                                    <?php if ($isSuperAdminActor || $sessionRoleNorm === 'admin'): ?>
                                        <option value="admin">Admin</option>
                                    <?php endif; ?>
                                    <option value="sub_admin_eorganisasi" selected>Sub Admin E-Organisasi</option>
                                    <option value="sub_admin_publikasi">Sub Admin Publikasi</option>
                                    <option value="staf_disposisi">User</option>
                                </select>
                                <div class="form-text small">Pilihan <strong>User</strong> dipakai untuk pegawai yang menerima tugas lewat menu Monitoring Disposisi.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Simpan pegawai</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalResetPasswordStaff" tabindex="-1" aria-labelledby="modalResetPasswordStaffLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="dashboard.php#panel-manajemen-staf">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalResetPasswordStaffLabel">Reset password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="staff_reset_password">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="staff_user_id" id="modal_reset_pwd_staff_id" value="">
                        <p class="small text-muted mb-3">Staf: <strong id="modal_reset_pwd_staff_nama">—</strong></p>
                        <label for="modal_reset_pwd_input" class="form-label">Password baru</label>
                        <input type="password" class="form-control" name="new_password" id="modal_reset_pwd_input" required minlength="8" maxlength="128" autocomplete="new-password" placeholder="Minimal 8 karakter">
                        <p class="form-text small mb-0">Password disimpan dengan <code>password_hash()</code>. Beritahu staf secara aman setelah reset.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning text-dark">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php
    require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_motion_assets.php';
    echo org_motion_script_tag();
    ?>
    <?php require __DIR__ . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'dashboard_monitoring_charts.php'; ?>
    <?php require __DIR__ . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'dashboard_module_router.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js"></script>
    <script>
        var DASH_IS_SUB_ADMIN = <?php echo $isSubAdminActor ? 'true' : 'false'; ?>;
        var DASH_IS_SUB_ADMIN_PUB = <?php echo $isSubAdminPublikasiActor ? 'true' : 'false'; ?>;
        if (DASH_IS_SUB_ADMIN) {
            try {
                var badHashes = ['#panel-manajemen-staf', '#panel-digital-library-stats', '#panel-audit', '#tab-konten'];
                if (DASH_IS_SUB_ADMIN_PUB) {
                    badHashes.push('#panel-akses-cepat', '#panel-layanan');
                }
                if (badHashes.indexOf(window.location.hash) !== -1) {
                    window.history.replaceState({}, document.title, window.location.pathname + '#panel-konten-tabs');
                }
            } catch (eHash) { /* ignore */ }
        }
        (function () {
            var galleryEl = document.querySelector('.dash-gallery-swiper');
            if (!galleryEl || typeof Swiper === 'undefined') {
                return;
            }
            window.dashGallerySwiper = new Swiper(galleryEl, {
                loop: <?php echo count($galleryUrls) > 1 ? 'true' : 'false'; ?>,
                speed: 600,
                autoplay: { delay: 4000, disableOnInteraction: false },
                pagination: { el: galleryEl.querySelector('.swiper-pagination'), clickable: true },
                navigation: {
                    nextEl: galleryEl.querySelector('.swiper-button-next'),
                    prevEl: galleryEl.querySelector('.swiper-button-prev'),
                },
            });
        }());

        (function () {
            function scrollActivityFeedsToEnd() {
                document.querySelectorAll('[data-adm-activity-feed]').forEach(function (list) {
                    list.scrollTop = list.scrollHeight;
                });
            }
            function scheduleActivityScroll() {
                scrollActivityFeedsToEnd();
                window.requestAnimationFrame(scrollActivityFeedsToEnd);
                window.setTimeout(scrollActivityFeedsToEnd, 80);
                window.setTimeout(scrollActivityFeedsToEnd, 350);
            }
            scheduleActivityScroll();
            window.addEventListener('load', scheduleActivityScroll);
        }());

        tinymce.init({
            selector: '#profile_visi, #profile_misi',
            height: 280,
            menubar: false,
            license_key: 'gpl',
            plugins: 'lists link autoresize',
            toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link removeformat',
            content_style: 'body { font-family: Roboto, sans-serif; font-size: 14px; }',
        });

        function dashHtmlNonEmpty(html) {
            var d = document.createElement('div');
            d.innerHTML = html || '';
            var t = (d.textContent || '').replace(/\u00a0/g, ' ').trim();
            return t.length > 0;
        }

        var formKontenDash = document.getElementById('formKontenDashboard');
        if (formKontenDash) formKontenDash.addEventListener('submit', function (e) {
            if (window.tinymce) {
                tinymce.triggerSave();
            }
            var v = document.getElementById('profile_visi').value;
            var m = document.getElementById('profile_misi').value;
            if (!dashHtmlNonEmpty(v) || !dashHtmlNonEmpty(m)) {
                e.preventDefault();
                alert('Visi dan Misi tidak boleh kosong.');
                return false;
            }
            var ids = ['profile_struktur', 'struktur_blurb'];
            for (var i = 0; i < ids.length; i++) {
                var el = document.getElementById(ids[i]);
                if (!el || !el.value.trim()) {
                    e.preventDefault();
                    alert('Semua kolom wajib (kecuali pengumuman) harus diisi.');
                    return false;
                }
            }
            return true;
        });

        (function () {
            var tabsRoot = document.getElementById('dashboardContentTabs');
            if (tabsRoot) {
                tabsRoot.querySelectorAll('[data-bs-toggle="tab"]').forEach(function (tabBtn) {
                    tabBtn.addEventListener('shown.bs.tab', function (ev) {
                        var targetSel = ev.target.getAttribute('data-bs-target') || '';
                        var tabId = targetSel.charAt(0) === '#' ? targetSel.slice(1) : targetSel;
                        if (tabId === 'tab-galeri' && window.dashGallerySwiper) {
                            window.setTimeout(function () { window.dashGallerySwiper.update(); }, 120);
                        }
                    });
                });
            }
        }());

        (function () {
            var modalGallery = document.getElementById('modalEditGalleryText');
            if (modalGallery) {
                modalGallery.addEventListener('show.bs.modal', function (ev) {
                    var btn = ev.relatedTarget;
                    if (!btn || !btn.classList.contains('js-gallery-edit')) return;
                    document.getElementById('modal_gallery_edit_id').value = btn.getAttribute('data-gallery-id') || '';
                    document.getElementById('modal_gallery_edit_judul').value = btn.getAttribute('data-gallery-judul') || '';
                });
            }

            var modalPusat = document.getElementById('modalEditPusatInformasi');
            if (modalPusat) {
                modalPusat.addEventListener('show.bs.modal', function (ev) {
                    var btn = ev.relatedTarget;
                    if (!btn || !btn.classList.contains('js-pusat-edit')) return;
                    document.getElementById('modal_pusat_edit_id').value = btn.getAttribute('data-pusat-id') || '';
                    document.getElementById('modal_pusat_edit_judul').value = btn.getAttribute('data-pusat-judul') || '';
                    document.getElementById('modal_pusat_edit_kategori').value = btn.getAttribute('data-pusat-kategori') || 'berita';
                    document.getElementById('modal_pusat_edit_isi').value = btn.getAttribute('data-pusat-isi') || '';
                    var previewUrl = btn.getAttribute('data-pusat-gambar-url') || '';
                    var imgPrev = document.getElementById('modal_pusat_edit_preview');
                    var noPrev = document.getElementById('modal_pusat_edit_no_preview');
                    if (imgPrev && noPrev) {
                        if (previewUrl) {
                            imgPrev.src = previewUrl;
                            imgPrev.classList.remove('d-none');
                            noPrev.classList.add('d-none');
                        } else {
                            imgPrev.src = '';
                            imgPrev.classList.add('d-none');
                            noPrev.classList.remove('d-none');
                        }
                    }
                });
            }

            var modalEdit = document.getElementById('modalEditEmailStaff');
            if (modalEdit) {
                modalEdit.addEventListener('show.bs.modal', function (ev) {
                    var btn = ev.relatedTarget;
                    if (!btn || !btn.classList.contains('js-staff-edit-email')) return;
                    document.getElementById('modal_edit_email_staff_id').value = btn.getAttribute('data-staff-id') || '';
                    document.getElementById('modal_edit_email_input').value = btn.getAttribute('data-staff-email') || '';
                    var nm = document.getElementById('modal_edit_email_staff_nama');
                    if (nm) nm.textContent = btn.getAttribute('data-staff-nama') || '—';
                });
            }
            var modalPwd = document.getElementById('modalResetPasswordStaff');
            if (modalPwd) {
                modalPwd.addEventListener('show.bs.modal', function (ev) {
                    var btn = ev.relatedTarget;
                    if (!btn || !btn.classList.contains('js-staff-reset-pwd')) return;
                    document.getElementById('modal_reset_pwd_staff_id').value = btn.getAttribute('data-staff-id') || '';
                    var inp = document.getElementById('modal_reset_pwd_input');
                    if (inp) inp.value = '';
                    var nm = document.getElementById('modal_reset_pwd_staff_nama');
                    if (nm) nm.textContent = btn.getAttribute('data-staff-nama') || '—';
                });
            }
            var modalAdd = document.getElementById('modalTambahPegawai');
            if (modalAdd) {
                modalAdd.addEventListener('hidden.bs.modal', function () {
                    var f = modalAdd.querySelector('form');
                    if (f) f.reset();
                });
            }
            var modalEditStaff = document.getElementById('modalEditStaff');
            if (modalEditStaff) {
                modalEditStaff.addEventListener('show.bs.modal', function (ev) {
                    var btn = ev.relatedTarget;
                    if (!btn || !btn.classList.contains('js-staff-edit-user')) return;
                    document.getElementById('modal_edit_staff_id').value = btn.getAttribute('data-staff-id') || '';
                    document.getElementById('edit_pegawai_nama').value = btn.getAttribute('data-staff-nama') || '';
                    document.getElementById('edit_pegawai_username').value = btn.getAttribute('data-staff-username') || '';
                    document.getElementById('edit_pegawai_email_google').value = btn.getAttribute('data-staff-email') || '';
                    var pwdInp = document.getElementById('edit_pegawai_password');
                    if (pwdInp) pwdInp.value = '';
                    var lvlSel = document.getElementById('edit_pegawai_level');
                    var lvl = btn.getAttribute('data-staff-level') || '';
                    if (lvlSel && lvl) {
                        lvlSel.value = lvl;
                        if (lvlSel.value !== lvl) {
                            var opt = document.createElement('option');
                            opt.value = lvl;
                            opt.textContent = lvl;
                            opt.selected = true;
                            lvlSel.appendChild(opt);
                        }
                    }
                });
            }
        }());

        (function () {
            var form = document.getElementById('formSaranDashboard');
            if (!form) return;
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var fd = new FormData(form);
                var nama = (fd.get('nama') || '').toString().trim();
                var email = (fd.get('email') || '').toString().trim();
                var pesan = (fd.get('pesan') || '').toString().trim();
                var statusEl = document.getElementById('saranFeedbackD');
                var btn = document.getElementById('btnSaranD');
                if (!nama || !email || !pesan) {
                    statusEl.textContent = 'Lengkapi semua field.';
                    statusEl.className = 'small text-warning mt-2';
                    return;
                }
                btn.disabled = true;
                statusEl.textContent = 'Mengirim...';
                statusEl.className = 'small text-secondary mt-2';
                var endpointD = form.getAttribute('data-saran-endpoint') || form.getAttribute('action') || '../proses_saran.php';
                fetch(endpointD, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ nama: nama, email: email, pesan: pesan }),
                })
                    .then(function (r) {
                        return r.text().then(function (t) {
                            var j = null;
                            try { j = t ? JSON.parse(t) : null; } catch (e) { j = null; }
                            return { ok: r.ok, j: j };
                        });
                    })
                    .then(function (x) {
                        if (x.j && x.j.ok) {
                            statusEl.textContent = x.j.message || 'Terima kasih, saran Anda telah terkirim!';
                            statusEl.className = 'small text-success fw-semibold mt-2';
                            form.reset();
                        } else {
                            statusEl.textContent = (x.j && x.j.message) ? x.j.message : 'Gagal mengirim.';
                            statusEl.className = 'small text-danger mt-2';
                        }
                    })
                    .catch(function () {
                        statusEl.textContent = 'Kesalahan jaringan.';
                        statusEl.className = 'small text-danger mt-2';
                    })
                    .finally(function () {
                        btn.disabled = false;
                    });
            });
        }());

        (function () {
            var adminSearchInput = document.getElementById('adminDocumentSearch');
            var tableBody = document.getElementById('adminDocumentTableBody');
            var quickFilterWrap = document.getElementById('adminDocumentQuickFilter');
            var quickFilterBtns = quickFilterWrap ? quickFilterWrap.querySelectorAll('[data-admin-doc-filter]') : [];
            var activeType = 'all';
            if (!adminSearchInput || !tableBody) {
                return;
            }
            function applyAdminDocFilter() {
                var keyword = adminSearchInput.value.toLowerCase().trim();
                var rows = tableBody.querySelectorAll('tr');
                rows.forEach(function (row) {
                    var fileName = row.getAttribute('data-file-name') || '';
                    var fileType = (row.getAttribute('data-file-type') || 'pdf').toLowerCase();
                    var typeOk = activeType === 'all' || fileType === activeType;
                    row.style.display = (fileName.includes(keyword) && typeOk) ? '' : 'none';
                });
            }
            adminSearchInput.addEventListener('input', applyAdminDocFilter);
            if (quickFilterBtns.length) {
                quickFilterBtns.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        activeType = String(btn.getAttribute('data-admin-doc-filter') || 'all').toLowerCase();
                        quickFilterBtns.forEach(function (x) { x.classList.remove('active'); });
                        btn.classList.add('active');
                        applyAdminDocFilter();
                    });
                });
            }
            applyAdminDocFilter();
        }());

        (function () {
            function syncCsrfInputs() {
                var meta = document.querySelector('meta[name="csrf-token"]');
                if (!meta) {
                    return;
                }
                var token = meta.getAttribute('content') || '';
                if (token === '') {
                    return;
                }
                document.querySelectorAll('input[name="csrf_token"]').forEach(function (input) {
                    input.value = token;
                });
            }
            document.querySelectorAll('form[method="post"], form[method="POST"]').forEach(function (form) {
                form.addEventListener('submit', function () {
                    syncCsrfInputs();
                }, true);
            });
            syncCsrfInputs();
        }());
    </script>
</body>
</html>
