# Website Bagian Organisasi (E-Organisasi)

Portal informasi dan modul operasional Bagian Organisasi: beranda publik, admin dashboard, manajemen tugas, disposisi, arsip surat, buku tamu, dan perpustakaan digital.

**Stack:** PHP 8.x, MySQL/MariaDB, Nginx (atau Apache), tanpa build Node/Composer.

---

## Persiapan lokal (Laragon)

1. Clone repositori ke `C:\laragon\www\` (atau folder www Laragon Anda).
2. Salin konfigurasi database:
   ```text
   config/database.example.php  →  config/database.php
   ```
3. Sesuaikan `host`, `user`, `password`, `database` di `config/database.php`.
4. Salin pengaturan situs (jika belum ada):
   ```text
   site_settings.example.json  →  site_settings.json
   ```
   Lalu sesuaikan isi visi, misi, dan teks pengantar.
5. Impor skema dari folder `install/` (mulai `schema.sql`, lalu berkas SQL modul lain) atau export dari HeidiSQL jika DB lokal sudah lengkap.
6. Pastikan folder `uploads/` dapat ditulis (Laragon biasanya sudah cukup).
7. Buka situs di browser (`http://nama-folder.test` atau `localhost/...`).

Bootstrap database otomatis di `index.php` **hanya berjalan di environment development** (localhost, Laragon, domain `.test`). Di server production blok itu dilewati.

| Variabel environment | Efek |
|---------------------|------|
| `ORG_DEV_BOOTSTRAP=1` | Paksa bootstrap dev aktif |
| `ORG_DEV_BOOTSTRAP=0` | Paksa bootstrap dev mati |

---

## GitHub (repositori private disarankan)

### Inisialisasi pertama (sekali)

```bash
cd "C:\laragon\www\BAGIAN ORGANISASI_V2"
git init
git add .
git commit -m "Initial commit: portal Bagian Organisasi"
git branch -M main
git remote add origin https://github.com/USERNAME/REPO.git
git push -u origin main
```

### Rutinitas setelah mengubah kode

```bash
git status   # pastikan tidak ada config/database.php atau site_settings.json
git add .
git commit -m "Deskripsi perubahan singkat"
git push
```

**Jangan di-commit:** `config/database.php`, isi `uploads/`, `site_settings.json` (sudah di `.gitignore`). Gunakan `site_settings.example.json` dan `config/database.example.php` sebagai template.

---

## Deploy ke VPS (CloudPanel)

Panduan singkat: **[DEPLOY.md](DEPLOY.md)**. Setelah `git clone` / `git pull`, jalankan:

```bash
bash deploy/post-deploy.sh
```

### 1. Server & panel

- VPS KVM (2 vCPU, 8 GB RAM sudah cukup).
- Install [CloudPanel](https://www.cloudpanel.io/), buat **Site** tipe PHP.
- Buat **Database** + user MySQL di panel; catat nama DB, user, password.

### 2. Kode di server

SSH ke VPS, lalu (sesuaikan path/domain):

```bash
cd /home/cloudpanel/htdocs/nama-domain.com
git clone https://github.com/USERNAME/REPO.git .
# atau: git pull   (update berikutnya)
```

Buat berkas konfigurasi di server (tidak ada di Git):

```bash
cp config/database.example.php config/database.php
cp site_settings.example.json site_settings.json
nano config/database.php
nano site_settings.json
```

Isi kredensial MySQL dari CloudPanel dan teks situs sesuai kebutuhan production.

### 3. Database

- Impor SQL lewat phpMyAdmin CloudPanel atau CLI.
- **Urutan lengkap:** lihat [install/README.md](install/README.md) (mulai `schema.sql`).
- Atau impor dump dari Laragon jika data lokal sudah dipakai.
- `cek_db.php` hanya berjalan di **localhost/Laragon**; di production mengembalikan 403.

### 4. Folder uploads

```bash
chown -R clp:clp uploads    # user bisa berbeda; sesuaikan dengan CloudPanel
chmod -R 775 uploads
```

Salin isi `uploads/` dari lokal ke server (FTP/rsync) **sekali** jika sudah ada file production. Deploy berikutnya **jangan hapus** folder `uploads/`.

### 5. PHP & Nginx (CloudPanel)

- PHP **8.2+**, ekstensi: `mysqli`, `mbstring`, `fileinfo`, `gd`, `zip`.
- `upload_max_filesize` ≥ **25M** (aplikasi mendukung upload hingga ~20 MB).
- Aktifkan **SSL** (Let's Encrypt) di CloudPanel.
- Pastikan `uploads/` tidak mengeksekusi PHP — gunakan [deploy/nginx-uploads-snippet.conf](deploy/nginx-uploads-snippet.conf) di Vhost jika perlu.
- Folder upload dibuat otomatis (`includes/org_upload_dirs.php` + `deploy/ensure-upload-dirs.php`).

### 6. Update situs live

Setelah uji di Laragon dan push ke GitHub:

```bash
cd /home/cloudpanel/htdocs/nama-domain.com
git pull
```

Tidak perlu `git pull` untuk perubahan file di `uploads/` atau data di MySQL — itu terpisah dari Git.

---

## Backup

| Yang di-backup | Cara |
|----------------|------|
| Database | Export harian (CloudPanel / cron `mysqldump`) |
| `uploads/` | Copy/rsync berkala |
| Kode | Sudah di GitHub |

---

## Struktur penting

| Path | Fungsi |
|------|--------|
| `index.php` | Beranda publik |
| `admin/dashboard.php` | Dashboard admin |
| `config/database.php` | Koneksi MySQL (lokal/server, tidak di Git) |
| `includes/bootstrap.php` | Sesi, upload, pengaturan situs |
| `install/*.sql` | Skema & migrasi database |
| `uploads/` | Dokumen, foto, arsip (tidak di Git) |

---

## Keamanan setelah go-live

- [ ] Ganti semua password default (termasuk user `admin` jika pernah di-seed lokal).
- [ ] Repo GitHub **private**.
- [ ] `display_errors = Off` di PHP production.
- [ ] HTTPS aktif.
- [ ] Backup DB + `uploads/` terjadwal.

---

## Bantuan

- Koneksi DB gagal → periksa `config/database.php` dan apakah MySQL berjalan.
- Upload gagal → permission `uploads/` dan `upload_max_filesize` di PHP.
- Halaman kosong setelah deploy → log Nginx/PHP di CloudPanel (**Logs**).
