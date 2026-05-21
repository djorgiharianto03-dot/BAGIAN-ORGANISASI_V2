# Urutan impor database (CloudPanel / phpMyAdmin)

Pilih database yang **sama** dengan `database` di `config/database.php`, lalu impor berkas SQL **berurutan**:

| No | Berkas | Keterangan |
|----|--------|------------|
| 1 | `schema.sql` | Basis: `site_content`, konten profil |
| 2 | `tables_saran_dan_audit.sql` | Audit & saran (jika dipakai) |
| 3 | `users_staff.sql` | Tabel pengguna |
| 4 | `users_add_role_column.sql` | Kolom role (migrasi) |
| 5 | `users_roles_migrate.sql` | Normalisasi role |
| 6 | `users_staff_add_role.sql` | Role staf |
| 7 | `personel_table.sql` | Data personel |
| 8 | `dokumen.sql` | Perpustakaan digital |
| 9 | `pengumuman.sql` | Pengumuman |
| 10 | `pusat_informasi.sql` | Pusat informasi beranda |
| 11 | `galeri_kegiatan.sql` | Galeri kegiatan |
| 12 | `saran_kritik.sql` | Saran & kritik |
| 13 | `team_targets.sql` | Target tim |
| 14 | `dashboard_widgets.sql` | Widget dashboard |
| 15 | `widget_details.sql` | Detail widget |
| 16 | `tugas_pegawai.sql` | Manajemen tugas |
| 17 | `migrate_dispositions.sql` | Disposisi |
| 18 | `arsip_surat_kategori_bagian.sql` | Arsip surat |

**Alternatif:** impor satu dump `.sql` lengkap dari HeidiSQL (Laragon) jika database lokal sudah final.

**Development:** `cek_db.php` di localhost dapat membuat beberapa tabel dasar; di production skrip itu **diblokir** — gunakan impor SQL di atas.
