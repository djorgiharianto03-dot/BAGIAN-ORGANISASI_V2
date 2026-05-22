# Panduan deploy cepat (CloudPanel)

Ringkasan langkah production. Detail lengkap ada di [README.md](README.md).

## Sebelum push ke GitHub

```bash
cd "C:\laragon\www\BAGIAN ORGANISASI_V2"
git init
git add .
git status   # pastikan database.php & site_settings.json TIDAK muncul
git commit -m "Initial commit: portal Bagian Organisasi"
git branch -M main
git remote add origin https://github.com/USERNAME/REPO.git
git push -u origin main
```

File sensitif sudah di `.gitignore`: `config/database.php`, `site_settings.json`, isi `uploads/`, `.env`.

## Pertama kali di VPS (SSH)

```bash
cd /home/cloudpanel/htdocs/nama-domain.com
git clone https://github.com/USERNAME/REPO.git .
bash deploy/post-deploy.sh
nano config/database.php
```

Impor database: lihat [install/README.md](install/README.md).

```bash
chown -R clp:clp uploads
chmod -R 775 uploads
```

CloudPanel → Site → Vhost: tambahkan isi [deploy/nginx-vhost-snippet.conf](deploy/nginx-vhost-snippet.conf) (HTTPS, tanpa `/index.php`, keamanan `uploads/`).

## Aset frontend (vendor lokal)

Setelah `git pull`, folder `assets/vendor/` dan `assets/css/beranda.bundle.min.css` ikut dari repo.

Jika perlu unduh ulang CDN ke lokal:

```powershell
.\deploy\download-vendor-assets.ps1
php deploy\build-beranda-bundle.php
```

## Update berikutnya

```bash
cd /home/cloudpanel/htdocs/nama-domain.com
git pull
bash deploy/post-deploy.sh
```

Jangan hapus folder `uploads/` saat update.

## Checklist go-live

- [ ] `config/database.php` — kredensial MySQL CloudPanel
- [ ] `site_settings.json` — visi, misi, teks situs
- [ ] Database diimpor (`install/README.md`)
- [ ] `uploads/` writable (775 + chown)
- [ ] PHP 8.2+, ekstensi mysqli, mbstring, fileinfo, gd, zip
- [ ] `upload_max_filesize` ≥ 25M
- [ ] SSL aktif
- [ ] Password admin diganti (bukan default dev)
- [ ] Repo GitHub private
- [ ] OpenAI (opsional): `.env` atau `config/openai.local.php` di server

## Bantuan

| Masalah | Solusi |
|---------|--------|
| Halaman kosong | Log PHP/Nginx di CloudPanel |
| DB gagal | `config/database.php`, nama DB sama dengan impor SQL |
| Upload gagal | Permission `uploads/`, ukuran upload PHP |
| `cek_db.php` 403 | Normal di production — impor SQL manual |
