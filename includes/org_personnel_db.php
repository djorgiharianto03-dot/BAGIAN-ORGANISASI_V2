<?php

declare(strict_types=1);

/**
 * Layer DB untuk personel.
 * -----------------------------------------------------------------------------
 * Sumber data utama (canonical) saat ini TETAP `personnel.json` di root proyek.
 * Tabel `personel` di MySQL berfungsi sebagai **mirror** yang otomatis
 * disinkronkan setiap kali file JSON ditulis (lihat hook di
 * `org_personnel_write_file()` pada `org_personnel_sync.php`).
 *
 * Mapping kolom:
 *   personnel.json    →  tabel personel
 *   id                →  id        (VARCHAR(64))
 *   name              →  nama      (VARCHAR(255))
 *   nip               →  nip       (VARCHAR(20))
 *   position          →  jabatan   (VARCHAR(255))
 *
 * Aman dipanggil walau MySQL mati: semua fungsi mengembalikan nilai netral
 * (false / [] / void) tanpa melempar exception ke pemanggil.
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_database.php';

function org_personnel_db_table_exists(mysqli $db): bool
{
    $res = @$db->query("SHOW TABLES LIKE 'personel'");
    if ($res === false) {
        return false;
    }
    $has = $res->num_rows > 0;
    $res->close();

    return $has;
}

function org_personnel_db_ensure_table(mysqli $db): void
{
    @$db->query(
        'CREATE TABLE IF NOT EXISTS `personel` ('
        . ' `id` VARCHAR(64) NOT NULL,'
        . ' `nama` VARCHAR(255) NOT NULL,'
        . ' `nip` VARCHAR(20) NOT NULL DEFAULT \'\','
        . ' `jabatan` VARCHAR(255) NOT NULL,'
        . ' `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
        . ' PRIMARY KEY (`id`),'
        . ' KEY `idx_nip` (`nip`),'
        . ' KEY `idx_nama` (`nama`(64))'
        . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        . ' COMMENT=\'Personel Bagian Organisasi (mirror auto-sync dari personnel.json)\''
    );
}

/**
 * Baca seluruh baris personel dari DB.
 *
 * @return list<array{id:string,name:string,nip:string,position:string}>
 */
function org_personnel_db_fetch_all(mysqli $db): array
{
    if (!org_personnel_db_table_exists($db)) {
        return [];
    }
    $rows = [];
    $res = @$db->query('SELECT `id`,`nama`,`nip`,`jabatan` FROM `personel` ORDER BY `nama` ASC');
    if (!$res) {
        return [];
    }
    while ($row = $res->fetch_assoc()) {
        $rows[] = [
            'id'       => (string) ($row['id'] ?? ''),
            'name'     => (string) ($row['nama'] ?? ''),
            'nip'      => (string) ($row['nip'] ?? ''),
            'position' => (string) ($row['jabatan'] ?? ''),
        ];
    }
    $res->close();

    return $rows;
}

/**
 * Sinkronkan seluruh isi tabel `personel` agar identik dengan $items
 * (diambil dari personnel.json). Strategi: transaksi → DELETE → batch INSERT.
 *
 * @param list<array<string,mixed>> $items
 */
function org_personnel_db_sync_all(mysqli $db, array $items): bool
{
    org_personnel_db_ensure_table($db);

    /* TOMBSTONE — buang entry yang sudah pernah dihapus admin sebelum
       di-mirror ke DB. Tanpa ini, kalau JSON sempat ter-restore (seed
       race / file watcher), tabel personel akan diisi ulang dengan baris
       yang seharusnya tidak ada. */
    if (is_file(__DIR__ . DIRECTORY_SEPARATOR . 'org_personnel_tombstone.php')) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_personnel_tombstone.php';
        if (function_exists('org_personnel_tombstone_filter_db_rows')) {
            $slugifyForDb = static function (string $s): string {
                $s = strtolower(trim($s));
                $s = preg_replace('/[^a-z0-9]+/u', '-', $s);
                return trim((string) $s, '-');
            };
            $items = org_personnel_tombstone_filter_db_rows($items, $slugifyForDb);
        }
    }

    $clean = [];
    foreach ($items as $row) {
        if (!is_array($row)) {
            continue;
        }
        $id       = trim((string) ($row['id'] ?? ''));
        $name     = trim((string) ($row['name'] ?? ''));
        $position = trim((string) ($row['position'] ?? ''));
        if ($id === '' || $name === '' || $position === '') {
            continue;
        }
        $nip = preg_replace('/\s+/u', '', trim((string) ($row['nip'] ?? '')));
        $clean[$id] = [
            'id'       => mb_substr($id, 0, 64, 'UTF-8'),
            'nama'     => mb_substr($name, 0, 255, 'UTF-8'),
            'nip'      => mb_substr((string) $nip, 0, 20, 'UTF-8'),
            'jabatan'  => mb_substr($position, 0, 255, 'UTF-8'),
        ];
    }

    if (!$db->begin_transaction()) {
        return false;
    }
    try {
        if (!@$db->query('DELETE FROM `personel`')) {
            throw new RuntimeException('DELETE personel: ' . $db->error);
        }
        if ($clean !== []) {
            $stmt = @$db->prepare('INSERT INTO `personel` (`id`,`nama`,`nip`,`jabatan`) VALUES (?,?,?,?)');
            if ($stmt === false) {
                throw new RuntimeException('prepare INSERT personel: ' . $db->error);
            }
            foreach ($clean as $r) {
                $stmt->bind_param('ssss', $r['id'], $r['nama'], $r['nip'], $r['jabatan']);
                if (!$stmt->execute()) {
                    $err = $stmt->error;
                    $stmt->close();
                    throw new RuntimeException('execute INSERT personel: ' . $err);
                }
            }
            $stmt->close();
        }
        if (!$db->commit()) {
            throw new RuntimeException('commit personel: ' . $db->error);
        }

        return true;
    } catch (\Throwable $e) {
        @$db->rollback();
        @error_log('[personel-sync] ' . $e->getMessage());

        return false;
    }
}

/**
 * Inisialisasi awal: kalau tabel kosong tapi personnel.json ada isi,
 * salin ke DB. Idempoten — aman dipanggil setiap request.
 */
function org_personnel_db_init_from_json(mysqli $db, string $jsonPath): void
{
    org_personnel_db_ensure_table($db);

    $countRes = @$db->query('SELECT COUNT(*) FROM `personel`');
    if (!$countRes) {
        return;
    }
    $row   = $countRes->fetch_row();
    $count = (int) ($row[0] ?? 0);
    $countRes->close();
    if ($count > 0) {
        return;
    }

    if (!is_file($jsonPath)) {
        return;
    }
    $raw = @file_get_contents($jsonPath);
    if ($raw === false || $raw === '') {
        return;
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded) || $decoded === []) {
        return;
    }
    org_personnel_db_sync_all($db, $decoded);
}

/**
 * Helper pemanggilan dari kode aplikasi tanpa harus mengelola koneksi DB
 * sendiri. Mengembalikan true kalau sinkron ke DB berhasil; false kalau DB
 * mati / sync gagal. Penulisan JSON adalah sumber kebenaran — kegagalan
 * sync DB tidak boleh membatalkan operasi.
 *
 * @param list<array<string,mixed>> $items
 */
function org_personnel_db_sync_safe(array $items): bool
{
    if (!function_exists('org_db')) {
        return false;
    }
    $db = org_db();
    if (!($db instanceof mysqli)) {
        return false;
    }

    return org_personnel_db_sync_all($db, $items);
}
