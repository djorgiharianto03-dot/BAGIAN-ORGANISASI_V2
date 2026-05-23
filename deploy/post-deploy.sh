#!/usr/bin/env bash
# Jalankan sekali setelah git clone / git pull di CloudPanel (SSH).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "== Portal Bagian Organisasi — post-deploy =="

if [[ ! -f config/database.php ]]; then
  cp config/database.example.php config/database.php
  echo "[!] config/database.php dibuat dari contoh — EDIT kredensial MySQL sebelum go-live."
fi

if [[ ! -f site_settings.json ]]; then
  cp site_settings.example.json site_settings.json
  echo "[i] site_settings.json dibuat dari contoh."
fi

if command -v php >/dev/null 2>&1; then
  php "$ROOT/deploy/ensure-upload-dirs.php"
  if [[ -f "$ROOT/deploy/build-production-assets.php" ]]; then
    php "$ROOT/deploy/build-production-assets.php" || echo "[!] build-production-assets gagal — pastikan assets/css/*.min.css ada di repo."
  fi
else
  echo "[!] PHP CLI tidak ditemukan — buat folder uploads/* manual atau buka beranda sekali."
fi

if [[ -d uploads ]]; then
  chmod -R 775 uploads 2>/dev/null || true
  echo "[i] Permission uploads/ diset 775 (sesuaikan chown clp:clp di CloudPanel jika perlu)."
fi

echo ""
echo "Langkah berikutnya:"
echo "  1) Edit config/database.php (host, user, password, database)"
echo "  2) Impor install/*.sql (lihat install/README.md)"
echo "  3) Salin isi uploads/ dari lokal jika sudah ada file production"
echo "  4) Aktifkan SSL di CloudPanel"
echo "  5) Clean URL: Apache pakai .htaccess; Nginx salin deploy/nginx-vhost-snippet.conf ke Vhost Custom"
echo "  6) Jangan akses cek_db.php dari internet production (dev saja)"
echo "Selesai."
