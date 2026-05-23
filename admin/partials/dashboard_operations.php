<?php
/** Panel operasional — ditampilkan via sidebar module, bukan di halaman monitoring. */
?>
<div id="sgWorkspace" class="sg-view" hidden>
    <div class="sg-workspace-head sg-fade-in">
        <button type="button" class="sg-back-btn" data-sg-back-monitor>
            <i data-lucide="arrow-left"></i><span>Kembali ke Monitoring</span>
        </button>
        <div>
            <h2 class="sg-workspace-head__title" id="sgWorkspaceTitle">Modul</h2>
            <p class="sg-workspace-head__sub" id="sgWorkspaceSubtitle">Kelola data operasional</p>
        </div>
    </div>
    <div class="sg-workspace-body" id="sgWorkspaceBody">
        <?php if (!$isSubAdminActor): ?>
        <div id="panel-manajemen-staf" class="card border-0 shadow-sm dash-section">
            <div class="card-body p-4">
                <h2 class="h5 mb-2">Manajemen akun staf</h2>
                <p class="text-muted small mb-3">Kelola akun staf: tambah pegawai, edit profil, email Google, dan password. Semua perubahan dicatat di audit trail.</p>
                <?php if ($db === null): ?>
                    <div class="alert alert-warning py-2 small mb-3">Koneksi database tidak tersedia. Periksa <code>config/database.php</code>.</div>
                <?php elseif (!$staffUsersTableOk): ?>
                    <div class="alert alert-warning mb-3 py-2 small">Tabel <code>users</code> belum ada. Impor <code>install/users_staff.sql</code> melalui phpMyAdmin, lalu muat ulang halaman ini.</div>
                <?php endif; ?>
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <span class="small text-muted">Daftar akun pegawai / staf</span>
                    <button
                        type="button"
                        class="btn btn-success btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#modalTambahPegawai"
                        <?php echo !$staffUsersTableOk ? ' disabled title="Tabel users belum tersedia"' : ''; ?>
                    ><i class="fa-solid fa-user-plus me-1" aria-hidden="true"></i>Tambah Pegawai Baru</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Username</th>
                                <th scope="col">Nama staf</th>
                                <th scope="col">Email Google</th>
                                <th scope="col">Level</th>
                                <th scope="col" class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($staffUsersTableOk && count($staffUserRows) === 0): ?>
                                <tr>
                                    <td colspan="6" class="text-muted small">Belum ada akun staf. Klik <strong>Tambah Pegawai Baru</strong> untuk menambahkan data pertama.</td>
                                </tr>
                            <?php elseif ($staffUsersTableOk): ?>
                                <?php foreach ($staffUserRows as $idx => $su): ?>
                                    <?php
                                    $suid = (int) ($su['id'] ?? 0);
                                    $suuserRaw = (string) ($su['username'] ?? '');
                                    $suuser = htmlspecialchars($suuserRaw, ENT_QUOTES, 'UTF-8');
                                    $sunama = htmlspecialchars((string) ($su['nama'] ?? ''), ENT_QUOTES, 'UTF-8');
                                    $suemail = trim((string) ($su['email_google'] ?? ''));
                                    $suemailAttr = htmlspecialchars($suemail, ENT_QUOTES, 'UTF-8');
                                    $sunamaJs = htmlspecialchars((string) ($su['nama'] ?? $su['username'] ?? ''), ENT_QUOTES, 'UTF-8');
                                    $suuserJs = htmlspecialchars($suuserRaw, ENT_QUOTES, 'UTF-8');
                                    $suroleRaw = (string) ($su['level'] ?? '');
                                    $suroleNorm = org_staff_role_normalize($suroleRaw);
                                    $suroleNormAttr = htmlspecialchars($suroleNorm, ENT_QUOTES, 'UTF-8');
                                    if ($suroleNorm === 'super_admin') {
                                        continue;
                                    }
                                    $suroleLabel = $suroleNorm === 'staf_disposisi'
                                        ? 'User'
                                        : htmlspecialchars(org_staff_role_label($suroleNorm), ENT_QUOTES, 'UTF-8');
                                    $suroleBadge = 'bg-secondary';
                                    if ($suroleNorm === 'admin') {
                                        $suroleBadge = 'bg-primary';
                                    } elseif ($suroleNorm === 'sub_admin_publikasi') {
                                        $suroleBadge = 'bg-info';
                                    } elseif ($suroleNorm === 'staf_disposisi') {
                                        $suroleBadge = 'bg-warning text-dark';
                                    }
                                    $showStaffDelete = $canDeleteStaffAccount && $suroleNorm !== 'super_admin';
                                    ?>
                                    <tr>
                                        <td><?php echo $idx + 1; ?></td>
                                        <td><code class="small"><?php echo $suuser; ?></code></td>
                                        <td><?php echo $sunama !== '' ? $sunama : '—'; ?></td>
                                        <td>
                                            <?php if ($suemail === ''): ?>
                                                <span class="text-danger small fw-semibold">Belum Terdaftar</span>
                                            <?php else: ?>
                                                <?php echo htmlspecialchars($suemail, ENT_QUOTES, 'UTF-8'); ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge <?php echo $suroleBadge; ?>"><?php echo $suroleLabel; ?></span></td>
                                        <td class="text-end text-nowrap">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-primary js-staff-edit-user"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalEditStaff"
                                                data-staff-id="<?php echo $suid; ?>"
                                                data-staff-username="<?php echo $suuserJs; ?>"
                                                data-staff-nama="<?php echo $sunamaJs; ?>"
                                                data-staff-email="<?php echo $suemailAttr; ?>"
                                                data-staff-level="<?php echo $suroleNormAttr; ?>"
                                                title="Edit data staf"
                                            ><i class="fa-solid fa-pen-to-square me-1" aria-hidden="true"></i>Edit</button>
                                            <?php if ($showStaffDelete): ?>
                                                <form method="post" action="dashboard.php#panel-manajemen-staf" class="d-inline mb-0" onsubmit="return confirm('Hapus akun staf ini secara permanen? Tindakan ini tidak dapat dibatalkan.');">
                                                    <input type="hidden" name="action" value="staff_delete_user">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <input type="hidden" name="staff_user_id" value="<?php echo $suid; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus akun"><i class="fa-solid fa-trash me-1" aria-hidden="true"></i>Hapus</button>
                                                </form>
                                            <?php endif; ?>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary js-staff-edit-email"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalEditEmailStaff"
                                                data-staff-id="<?php echo $suid; ?>"
                                                data-staff-nama="<?php echo $sunamaJs; ?>"
                                                data-staff-email="<?php echo $suemailAttr; ?>"
                                                title="Edit email Google"
                                            ><i class="fa-solid fa-pencil me-1" aria-hidden="true"></i>Edit Email</button>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-warning text-dark js-staff-reset-pwd"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalResetPasswordStaff"
                                                data-staff-id="<?php echo $suid; ?>"
                                                data-staff-nama="<?php echo $sunamaJs; ?>"
                                                title="Reset password"
                                            ><i class="fa-solid fa-key me-1" aria-hidden="true"></i>Reset Password</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-muted small">Setelah tabel <code>users</code> tersedia, daftar staf akan tampil di sini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>


        <?php if ($canManagePerpustakaanDokumen): ?>
        <div id="panel-unggah-dokumen" class="card border-0 shadow-sm dash-section">
            <div class="card-body p-4">
                <h2 class="h5 mb-2">Unggah dokumen</h2>
                <p class="text-muted small mb-4">File disimpan di <code>uploads/perpustakaan_digital/</code> dan tampil di halaman <a href="../dokumen.php" target="_blank" rel="noopener">dokumen publik</a>. Untuk mengelola atau menghapus file, gunakan panel <a href="#panel-kelola-dokumen">Kelola Dokumen</a> di bawah.</p>
                <form method="post" enctype="multipart/form-data" action="dashboard.php#panel-unggah-dokumen">
                    <input type="hidden" name="action" value="upload">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="mb-3">
                        <label for="dash_dokumen" class="form-label">Pilih file (PDF/Word/Excel/Gambar)</label>
                        <input class="form-control" type="file" id="dash_dokumen" name="dokumen" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.webp,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,image/jpeg,image/png,image/gif,image/webp" required>
                        <div class="form-text">
                            Batas upload: dokumen maksimal <?php echo htmlspecialchars(org_format_file_size((int) ORG_DOKUMEN_MAX_UPLOAD_BYTES), ENT_QUOTES, 'UTF-8'); ?>,
                            gambar maksimal <?php echo htmlspecialchars(org_format_file_size((int) ORG_DOKUMEN_MAX_UPLOAD_IMAGE_BYTES), ENT_QUOTES, 'UTF-8'); ?>.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="dash_dokumen_kategori" class="form-label">Pilih kategori</label>
                        <select class="form-select" id="dash_dokumen_kategori" name="dokumen_kategori" required>
                            <option value="">-- Pilih kategori tim kerja --</option>
                            <option value="Kelembagaan">Kelembagaan</option>
                            <option value="Pelayanan Publik">Pelayanan Publik</option>
                            <option value="SAKIP &amp; RB">SAKIP &amp; RB</option>
                            <option value="Regulasi">Regulasi</option>
                            <option value="Lainnya">Lainnya</option>
                            <option value="Visual/Foto Struktur">Visual/Foto Struktur</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-cloud-arrow-up me-1" aria-hidden="true"></i>Unggah</button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($canManagePerpustakaanDokumen): ?>
        <?php require __DIR__ . DIRECTORY_SEPARATOR . 'panel_kelola_dokumen.php'; ?>
        <?php endif; ?>

        <?php if (!$isSubAdminActor): ?>
        <div id="panel-digital-library-stats" class="card border-0 shadow-sm dash-section">
            <div class="card-body p-4">
                <h2 class="h5 mb-2">Digital Library — statistik unduhan</h2>
                <p class="text-muted small mb-4">Jumlah unduhan bertambah setiap kali pengunjung menekan <strong>Unduh</strong> di halaman <a href="../dokumen.php">dokumen publik</a> (bukan pratinjau &quot;Buka&quot;).</p>
                <?php if ($db === null): ?>
                    <p class="text-muted small mb-0">Database tidak tersedia.</p>
                <?php elseif (!$dashDocDbReady): ?>
                    <p class="text-muted small mb-0">Tabel <code>dokumen</code> belum ada. Jalankan <code>cek_db.php</code> atau buka halaman dokumen publik sekali.</p>
                <?php elseif (count($dashDocRanked) === 0): ?>
                    <p class="text-muted small mb-0">Belum ada dokumen di folder <code>uploads/</code> atau belum ada unduhan.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Judul</th>
                                    <th scope="col" class="text-nowrap">Kategori</th>
                                    <th scope="col" class="text-nowrap">Ukuran</th>
                                    <th scope="col" class="text-end text-nowrap">Jumlah unduh</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dashDocRanked as $dr): ?>
                                    <?php
                                    $base = $dr['nama_file'];
                                    $baseDisp = preg_replace('/^\d{8}_\d{6}_/i', '', $base);
                                    $judulDisp = str_replace('_', ' ', pathinfo($baseDisp, PATHINFO_FILENAME));
                                    if ($judulDisp === '') {
                                        $judulDisp = $base;
                                    }
                                    ?>
                                    <tr>
                                        <td class="text-break small"><?php echo htmlspecialchars($judulDisp, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><span class="badge text-bg-light border"><?php echo htmlspecialchars((string) $dr['kategori'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td class="text-muted small"><?php echo htmlspecialchars(org_format_file_size((int) $dr['bytes']), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-end fw-semibold"><?php echo (int) $dr['jumlah_unduh']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-muted small mt-3 mb-0">Diurutkan dari unduhan terbanyak. Maksimal 80 entri.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div id="panel-konten-tabs" class="card border-0 shadow-sm dash-section">
            <div class="card-body p-4 pb-0">
                <h2 class="h5 mb-3">Kelola konten publik</h2>
                <ul class="nav nav-tabs admin-tabs" id="dashboardContentTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-galeri-tab" data-bs-toggle="tab" data-bs-target="#tab-galeri" type="button" role="tab" aria-controls="tab-galeri" aria-selected="true">Galeri</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-pusat-tab" data-bs-toggle="tab" data-bs-target="#tab-pusat" type="button" role="tab" aria-controls="tab-pusat" aria-selected="false">Berita &amp; Pengumuman</button>
                    </li>
                    <?php if (!$isSubAdminActor): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-konten-tab" data-bs-toggle="tab" data-bs-target="#tab-konten" type="button" role="tab" aria-controls="tab-konten" aria-selected="false">Visi Misi</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-layanan-tab" data-bs-toggle="tab" data-bs-target="#tab-layanan" type="button" role="tab" aria-controls="tab-layanan" aria-selected="false">Manajemen Layanan</button>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="tab-content pt-3">
                <div class="tab-pane fade show active" id="tab-galeri" role="tabpanel" aria-labelledby="tab-galeri-tab">
        <?php require __DIR__ . DIRECTORY_SEPARATOR . 'dashboard_gallery_preview.php'; ?>
        <div id="panel-galeri" class="card border-0 shadow-sm mb-0">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Galeri kegiatan (admin)</h2>
                <?php
                $galleryDbReady = $db !== null && org_galeri_kegiatan_table_exists($db);
                ?>
                <?php if (!$galleryDbReady): ?>
                    <p class="text-muted small mb-0">Tabel <code>galeri</code> tidak dapat diakses. Pastikan MySQL berjalan dan <code>config/database.php</code> benar, lalu muat ulang halaman ini.</p>
                <?php else: ?>
                    <h3 class="h6 fw-semibold mb-3">Unggah foto kegiatan</h3>
                    <p class="text-muted small mb-3">File disimpan di <code>assets/img/galeri/</code> dan metadata di tabel <code>galeri</code>. Format: JPG, JPEG, PNG, WebP, atau GIF. Maksimal 2MB per file.</p>
                    <form method="post" enctype="multipart/form-data" class="border-bottom pb-4 mb-4">
                        <input type="hidden" name="action" value="gallery_upload">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="judul_kegiatan">Judul kegiatan</label>
                                <input type="text" class="form-control" id="judul_kegiatan" name="judul_kegiatan" required maxlength="255" autocomplete="off" placeholder="Contoh: Rapat koordinasi bulan April">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="foto_kegiatan">File gambar</label>
                                <input class="form-control" type="file" id="foto_kegiatan" name="foto_kegiatan" accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3"><i class="fa-solid fa-cloud-arrow-up me-1" aria-hidden="true"></i>Unggah ke galeri</button>
                    </form>
                    <h3 class="h6 mb-3">Foto yang dipublikasikan</h3>
                    <?php if (count($galeriRows) === 0): ?>
                        <p class="text-muted small mb-0">Belum ada entri. Unggah foto pertama menggunakan formulir di atas.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Diunggah</th>
                                        <th>Judul</th>
                                        <th>Berkas</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($galeriRows as $grow): ?>
                                        <?php
                                        $gid = (int) ($grow['id'] ?? 0);
                                        $gj = htmlspecialchars((string) ($grow['judul'] ?? ''), ENT_QUOTES, 'UTF-8');
                                        $gf = htmlspecialchars(basename((string) ($grow['nama_file'] ?? '')), ENT_QUOTES, 'UTF-8');
                                        $tglRaw = (string) ($grow['tgl_upload'] ?? '');
                                        $gt = $tglRaw !== '' ? htmlspecialchars(date('d/m/Y H:i', strtotime($tglRaw)), ENT_QUOTES, 'UTF-8') : '—';
                                        ?>
                                        <tr>
                                            <td><?php echo $gt; ?></td>
                                            <td><?php echo $gj; ?></td>
                                            <td><code class="small"><?php echo $gf; ?></code></td>
                                            <td class="text-end">
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-primary js-gallery-edit me-1"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalEditGalleryText"
                                                    data-gallery-id="<?php echo $gid; ?>"
                                                    data-gallery-judul="<?php echo $gj; ?>"
                                                    title="Edit caption/judul"
                                                ><i class="fa-solid fa-pen-to-square me-1" aria-hidden="true"></i>Edit</button>
                                                <form method="post" class="d-inline mb-0" onsubmit="return confirm('Hapus foto ini dari galeri? Berkas di server juga akan dihapus.');">
                                                    <input type="hidden" name="action" value="gallery_delete">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <input type="hidden" name="gallery_id" value="<?php echo $gid; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
                </div>
                <?php if (!$isSubAdminActor): ?>
                <div class="tab-pane fade" id="tab-konten" role="tabpanel" aria-labelledby="tab-konten-tab">
        <div id="panel-konten" class="card border-0 shadow-sm mb-0">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Simpan konten (Visi &amp; Misi dengan editor)</h2>
                <p class="text-muted small mb-3">Publikasi berita &amp; pengumuman dengan gambar dikelola di <a href="#panel-pusat-informasi">Pusat Informasi &amp; Pengumuman</a> (bukan di sini).</p>
                <form method="post" id="formKontenDashboard" novalidate>
                    <input type="hidden" name="action" value="save_konten_dashboard">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="profile_visi">Visi</label>
                            <textarea class="form-control" id="profile_visi" name="profile_visi" rows="8"><?php echo htmlspecialchars($siteSettings['profile_visi'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="profile_misi">Misi</label>
                            <textarea class="form-control" id="profile_misi" name="profile_misi" rows="8"><?php echo htmlspecialchars($siteSettings['profile_misi'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="profile_struktur">Struktur singkat</label>
                            <textarea class="form-control" name="profile_struktur" id="profile_struktur" rows="3" required><?php echo htmlspecialchars($siteSettings['profile_struktur'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold" for="struktur_blurb">Pengantar struktur</label>
                            <textarea class="form-control" name="struktur_blurb" id="struktur_blurb" rows="3" required><?php echo htmlspecialchars($siteSettings['struktur_blurb'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="organisasi_intro">Ringkasan organisasi</label>
                            <textarea class="form-control" name="organisasi_intro" id="organisasi_intro" rows="4" placeholder="Paragraf singkat untuk halaman Profil…"><?php echo htmlspecialchars($siteSettings['organisasi_intro'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            <div class="form-text">Tampil di halaman Profil. Disimpan di <code>site_content.organisasi_intro</code>.</div>
                        </div>
                        <input type="hidden" name="pengumuman" value="<?php echo htmlspecialchars($siteSettings['pengumuman'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Simpan ke database</button>
                </form>
            </div>
        </div>
                </div>
                <?php endif; ?>
                <?php if (!$isSubAdminActor): ?>
                <div class="tab-pane fade" id="tab-layanan" role="tabpanel" aria-labelledby="tab-layanan-tab">
        <div id="panel-layanan" class="card border-0 shadow-sm mb-0">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Manajemen Layanan</h2>
                <p class="text-muted small mb-3">Input daftar layanan publik, <strong>urutan</strong> (angka lebih kecil = tampil lebih dulu dalam satu kategori; 0 = ikuti urutan simpanan), <strong>pin nama</strong> (teks singkat di depan atau di belakang judul di halaman publik), lalu tambahkan media integrasi (gambar, dokumen, dan link) untuk dipakai di halaman <code>layanan.php</code> atau modul lain.</p>
                <form method="post" action="dashboard.php#panel-konten-tabs" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="save_layanan_dashboard">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-2">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 14rem;">Kategori Tim</th>
                                    <th style="width: 4.5rem;" title="Angka lebih kecil tampil lebih dulu. 0 = urutan bawaan file.">Urutan</th>
                                    <th style="width: 16rem;">Nama layanan &amp; pin</th>
                                    <th>Deskripsi / SOP</th>
                                    <th style="min-width: 24rem;">Integrasi (Gambar / Dokumen / Link)</th>
                                    <th style="width: 7rem;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $layananTableRows = $layananRows;
                                while (count($layananTableRows) < 9) {
                                    $idx = count($layananTableRows);
                                    $katDefault = $layananKategoriList[$idx % 3];
                                    $layananTableRows[] = ['kategori' => $katDefault, 'nama' => '', 'deskripsi' => '', 'media_image' => '', 'media_document' => '', 'media_documents' => [], 'link' => '', 'pin_label' => '', 'pin_position' => '', 'urutan' => 0];
                                }
                                foreach ($layananTableRows as $i => $lr):
                                ?>
                                    <tr class="js-layanan-row" data-editing="0">
                                        <td>
                                            <select class="form-select form-select-sm js-layanan-input" name="layanan[<?php echo (int) $i; ?>][kategori]" disabled>
                                                <?php foreach ($layananKategoriList as $katOpt): ?>
                                                    <option value="<?php echo htmlspecialchars($katOpt, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ((string) ($lr['kategori'] ?? '') === $katOpt) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars((string) ($layananKategoriLabelMap[$katOpt] ?? $katOpt), ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <?php
                                            $urutanCell = (int) ($lr['urutan'] ?? 0);
                                            if ($urutanCell < 0) {
                                                $urutanCell = 0;
                                            }
                                            if ($urutanCell > 9999) {
                                                $urutanCell = 9999;
                                            }
                                            ?>
                                            <label class="visually-hidden" for="layanan-urutan-<?php echo (int) $i; ?>">Urutan tampil</label>
                                            <input id="layanan-urutan-<?php echo (int) $i; ?>" type="number" inputmode="numeric" class="form-control form-control-sm js-layanan-input text-center" name="layanan[<?php echo (int) $i; ?>][urutan]" min="0" max="9999" step="1" value="<?php echo (int) $urutanCell; ?>" readonly>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm js-layanan-input mb-2" name="layanan[<?php echo (int) $i; ?>][nama]" maxlength="255" value="<?php echo htmlspecialchars((string) ($lr['nama'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="Contoh: Konsultasi kelembagaan OPD" readonly>
                                            <div class="small text-muted mb-1">Pin nama (opsional)</div>
                                            <input type="text" class="form-control form-control-sm js-layanan-input mb-1" name="layanan[<?php echo (int) $i; ?>][pin_label]" maxlength="40" value="<?php echo htmlspecialchars((string) ($lr['pin_label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="Contoh: Utama, Prioritas" readonly>
                                            <select class="form-select form-select-sm js-layanan-input" name="layanan[<?php echo (int) $i; ?>][pin_position]" disabled>
                                                <?php
                                                $pp = (string) ($lr['pin_position'] ?? '');
                                                if ($pp !== 'before' && $pp !== 'after') {
                                                    $pp = '';
                                                }
                                                ?>
                                                <option value="" <?php echo $pp === '' ? 'selected' : ''; ?>>Tanpa pin</option>
                                                <option value="before" <?php echo $pp === 'before' ? 'selected' : ''; ?>>Di depan nama</option>
                                                <option value="after" <?php echo $pp === 'after' ? 'selected' : ''; ?>>Di belakang nama</option>
                                            </select>
                                        </td>
                                        <td><input type="text" class="form-control form-control-sm js-layanan-input" name="layanan[<?php echo (int) $i; ?>][deskripsi]" maxlength="500" value="<?php echo htmlspecialchars((string) ($lr['deskripsi'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="Contoh: SOP 2 hari kerja, melalui alur internal Bagian Organisasi" readonly></td>
                                        <td>
                                            <?php
                                            $mediaImg = (string) ($lr['media_image'] ?? '');
                                            $mediaDocs = [];
                                            if (isset($lr['media_documents']) && is_array($lr['media_documents'])) {
                                                foreach ($lr['media_documents'] as $docItem) {
                                                    if (is_string($docItem) && trim($docItem) !== '') {
                                                        $mediaDocs[] = trim($docItem);
                                                    }
                                                }
                                            } elseif ((string) ($lr['media_document'] ?? '') !== '') {
                                                $mediaDocs[] = (string) ($lr['media_document'] ?? '');
                                            }
                                            $mediaDoc = $mediaDocs[0] ?? '';
                                            $mediaLink = (string) ($lr['link'] ?? '');
                                            ?>
                                            <input type="hidden" name="layanan[<?php echo (int) $i; ?>][media_image_existing]" value="<?php echo htmlspecialchars($mediaImg, ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="layanan[<?php echo (int) $i; ?>][media_document_existing]" value="<?php echo htmlspecialchars($mediaDoc, ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="layanan[<?php echo (int) $i; ?>][media_documents_existing]" value="<?php echo htmlspecialchars((string) json_encode($mediaDocs, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>">
                                            <div class="d-grid gap-2">
                                                <div>
                                                    <label class="form-label form-label-sm mb-1">Upload gambar (opsional)</label>
                                                    <input type="file" class="form-control form-control-sm js-layanan-input" name="layanan_media_image[<?php echo (int) $i; ?>]" accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp" disabled>
                                                    <?php if ($mediaImg !== ''): ?>
                                                        <a class="small text-decoration-none" href="<?php echo htmlspecialchars($mediaImg, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">Lihat gambar saat ini</a>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <label class="form-label form-label-sm mb-1">Upload dokumen (opsional, bisa lebih dari satu)</label>
                                                    <input type="file" class="form-control form-control-sm js-layanan-input" name="layanan_media_document[<?php echo (int) $i; ?>][]" accept=".pdf,.doc,.docx,.xls,.xlsx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" multiple disabled>
                                                    <?php if ($mediaDocs !== []): ?>
                                                        <div class="small mt-1">
                                                            <?php foreach ($mediaDocs as $dIdx => $docHref): ?>
                                                                <a class="text-decoration-none me-2" href="<?php echo htmlspecialchars((string) $docHref, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">Dokumen <?php echo (int) ($dIdx + 1); ?></a>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <label class="form-label form-label-sm mb-1">Link integrasi (opsional)</label>
                                                    <input type="url" class="form-control form-control-sm js-layanan-input" name="layanan[<?php echo (int) $i; ?>][link]" maxlength="500" value="<?php echo htmlspecialchars($mediaLink, ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://contoh.go.id/layanan" readonly>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-secondary js-edit-layanan-row mb-1">
                                                <i class="fa-solid fa-pen-to-square me-1" aria-hidden="true"></i>Edit
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger js-hapus-layanan-row">
                                                <i class="fa-solid fa-trash-can me-1" aria-hidden="true"></i>Hapus
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <template id="layananRowTemplate">
                        <tr class="js-layanan-row" data-editing="1">
                            <td>
                                <select class="form-select form-select-sm js-layanan-input" name="layanan[__INDEX__][kategori]">
                                    <?php foreach ($layananKategoriList as $katOpt): ?>
                                        <option value="<?php echo htmlspecialchars($katOpt, ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars((string) ($layananKategoriLabelMap[$katOpt] ?? $katOpt), ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <label class="visually-hidden" for="layanan-urutan-__INDEX__">Urutan tampil</label>
                                <input id="layanan-urutan-__INDEX__" type="number" inputmode="numeric" class="form-control form-control-sm js-layanan-input text-center" name="layanan[__INDEX__][urutan]" min="0" max="9999" step="1" value="0">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm js-layanan-input mb-2" name="layanan[__INDEX__][nama]" maxlength="255" value="" placeholder="Contoh: Konsultasi kelembagaan OPD">
                                <div class="small text-muted mb-1">Pin nama (opsional)</div>
                                <input type="text" class="form-control form-control-sm js-layanan-input mb-1" name="layanan[__INDEX__][pin_label]" maxlength="40" value="" placeholder="Contoh: Utama, Prioritas">
                                <select class="form-select form-select-sm js-layanan-input" name="layanan[__INDEX__][pin_position]">
                                    <option value="" selected>Tanpa pin</option>
                                    <option value="before">Di depan nama</option>
                                    <option value="after">Di belakang nama</option>
                                </select>
                            </td>
                            <td><input type="text" class="form-control form-control-sm js-layanan-input" name="layanan[__INDEX__][deskripsi]" maxlength="500" value="" placeholder="Contoh: SOP 2 hari kerja, melalui alur internal Bagian Organisasi"></td>
                            <td>
                                <input type="hidden" name="layanan[__INDEX__][media_image_existing]" value="">
                                <input type="hidden" name="layanan[__INDEX__][media_document_existing]" value="">
                                <input type="hidden" name="layanan[__INDEX__][media_documents_existing]" value="[]">
                                <div class="d-grid gap-2">
                                    <div>
                                        <label class="form-label form-label-sm mb-1">Upload gambar (opsional)</label>
                                        <input type="file" class="form-control form-control-sm js-layanan-input" name="layanan_media_image[__INDEX__]" accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp">
                                    </div>
                                    <div>
                                        <label class="form-label form-label-sm mb-1">Upload dokumen (opsional, bisa lebih dari satu)</label>
                                        <input type="file" class="form-control form-control-sm js-layanan-input" name="layanan_media_document[__INDEX__][]" accept=".pdf,.doc,.docx,.xls,.xlsx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" multiple>
                                    </div>
                                    <div>
                                        <label class="form-label form-label-sm mb-1">Link integrasi (opsional)</label>
                                        <input type="url" class="form-control form-control-sm js-layanan-input" name="layanan[__INDEX__][link]" maxlength="500" value="" placeholder="https://contoh.go.id/layanan">
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-secondary js-edit-layanan-row mb-1">
                                    <i class="fa-solid fa-circle-check me-1" aria-hidden="true"></i>Selesai
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger js-hapus-layanan-row">
                                    <i class="fa-solid fa-trash-can me-1" aria-hidden="true"></i>Hapus
                                </button>
                            </td>
                        </tr>
                    </template>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnTambahBarisLayanan">
                            <i class="fa-solid fa-plus me-1" aria-hidden="true"></i>Tambah Layanan
                        </button>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1" aria-hidden="true"></i>Simpan Manajemen Layanan</button>
                </form>
                <script>
                (function () {
                    var panel = document.getElementById('panel-layanan');
                    if (!panel) return;
                    var btnAdd = panel.querySelector('#btnTambahBarisLayanan');
                    var tpl = panel.querySelector('#layananRowTemplate');
                    var tbody = panel.querySelector('table tbody');
                    var form = panel.querySelector('form');
                    if (!btnAdd || !tpl || !tbody || !form) return;
                    var nextIndex = tbody.querySelectorAll('tr').length;
                    function setRowEditable(row, editable) {
                        if (!row) return;
                        row.setAttribute('data-editing', editable ? '1' : '0');
                        var controls = row.querySelectorAll('.js-layanan-input');
                        controls.forEach(function (el) {
                            var tag = (el.tagName || '').toUpperCase();
                            var type = (el.getAttribute('type') || '').toLowerCase();
                            if (tag === 'SELECT' || type === 'file') {
                                el.disabled = !editable;
                            } else if (tag === 'INPUT' || tag === 'TEXTAREA') {
                                if (editable) {
                                    el.removeAttribute('readonly');
                                } else {
                                    el.setAttribute('readonly', 'readonly');
                                }
                            }
                        });
                        var btnEdit = row.querySelector('.js-edit-layanan-row');
                        if (btnEdit) {
                            if (editable) {
                                btnEdit.innerHTML = '<i class="fa-solid fa-circle-check me-1" aria-hidden="true"></i>Selesai';
                                btnEdit.classList.remove('btn-outline-secondary');
                                btnEdit.classList.add('btn-secondary');
                            } else {
                                btnEdit.innerHTML = '<i class="fa-solid fa-pen-to-square me-1" aria-hidden="true"></i>Edit';
                                btnEdit.classList.remove('btn-secondary');
                                btnEdit.classList.add('btn-outline-secondary');
                            }
                        }
                    }
                    tbody.querySelectorAll('tr.js-layanan-row').forEach(function (row) {
                        setRowEditable(row, false);
                    });
                    btnAdd.addEventListener('click', function () {
                        var html = tpl.innerHTML.replace(/__INDEX__/g, String(nextIndex));
                        tbody.insertAdjacentHTML('beforeend', html);
                        var rows = tbody.querySelectorAll('tr.js-layanan-row');
                        var newRow = rows[rows.length - 1] || null;
                        setRowEditable(newRow, true);
                        nextIndex += 1;
                    });
                    tbody.addEventListener('click', function (ev) {
                        var target = ev.target;
                        if (!target) return;
                        var btnEdit = target.closest('.js-edit-layanan-row');
                        if (btnEdit) {
                            var rowEdit = btnEdit.closest('tr');
                            if (!rowEdit) return;
                            var isEditing = rowEdit.getAttribute('data-editing') === '1';
                            setRowEditable(rowEdit, !isEditing);
                            return;
                        }
                        var btn = target.closest('.js-hapus-layanan-row');
                        if (!btn) return;
                        var row = btn.closest('tr');
                        if (!row) return;
                        if (tbody.querySelectorAll('tr').length <= 1) {
                            return;
                        }
                        row.remove();
                    });
                    form.addEventListener('submit', function () {
                        tbody.querySelectorAll('.js-layanan-input').forEach(function (el) {
                            if (el.disabled) {
                                el.disabled = false;
                            }
                            if (el.hasAttribute('readonly')) {
                                el.removeAttribute('readonly');
                            }
                        });
                    });
                }());
                </script>
            </div>
        </div>
                </div>
                <?php endif; ?>
                <div class="tab-pane fade" id="tab-pusat" role="tabpanel" aria-labelledby="tab-pusat-tab">
        <div id="panel-pusat-informasi" class="card border-0 shadow-sm mb-0">
            <div class="card-body p-4">
                <h2 class="h5 mb-2">Pusat Informasi &amp; Pengumuman</h2>
                <p class="text-muted small mb-4">Konten tampil di beranda (grid kartu). Aktifkan <strong>Tampilkan di Depan</strong> untuk mengutamakan hingga 4 entri di urutan atas beranda. Gambar disimpan di <code>uploads/pusat_informasi/</code>. Hapus akan menghapus baris database dan berkas gambar di server.</p>
                <?php if ($db === null): ?>
                    <p class="text-muted small mb-0">Database tidak tersedia.</p>
                <?php elseif (!org_pusat_informasi_table_exists($db)): ?>
                    <p class="text-muted small mb-0">Tabel <code>pusat_informasi</code> belum ada. Jalankan <code>cek_db.php</code> atau buka halaman publik sekali.</p>
                <?php else: ?>
                    <h3 class="h6 fw-semibold mb-3">Tambah postingan</h3>
                    <form method="post" enctype="multipart/form-data" class="border-bottom pb-4 mb-4" autocomplete="off">
                        <input type="hidden" name="action" value="pusat_informasi_tambah">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="pusat_judul">Judul</label>
                                <input type="text" class="form-control" id="pusat_judul" name="pusat_judul" required maxlength="255" placeholder="Judul berita atau pengumuman">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="pusat_kategori">Kategori</label>
                                <select class="form-select" id="pusat_kategori" name="pusat_kategori" required>
                                    <option value="berita">Berita</option>
                                    <option value="pengumuman">Pengumuman</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="pusat_isi">Isi teks</label>
                                <textarea class="form-control" id="pusat_isi" name="pusat_isi" rows="5" required placeholder="Ringkasan atau isi lengkap…"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="pusat_gambar">Gambar brosur / poster</label>
                                <input type="file" class="form-control" id="pusat_gambar" name="pusat_gambar" accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp" required>
                                <div class="form-text">JPG, PNG, WebP, atau GIF. Maks. 4 MB.</div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3"><i class="fa-solid fa-plus me-1" aria-hidden="true"></i>Simpan postingan</button>
                    </form>
                    <h3 class="h6 fw-semibold mb-3">Daftar postingan</h3>
                    <?php if (count($pusatInformasiList) === 0): ?>
                        <p class="text-muted small mb-0">Belum ada postingan.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Judul</th>
                                        <th class="text-nowrap">Kategori</th>
                                        <th class="text-nowrap" style="width: 8.5rem;">Tampilkan di Depan</th>
                                        <th class="text-nowrap">Gambar</th>
                                        <th>Teks (ringkas)</th>
                                        <th class="text-end text-nowrap" style="width: 6rem;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pusatInformasiList as $piRow): ?>
                                        <?php
                                        $snPi = (string) ($piRow['isi_teks'] ?? '');
                                        if (strlen($snPi) > 120) {
                                            $snPi = substr($snPi, 0, 117) . '…';
                                        }
                                        $gPi = trim((string) ($piRow['nama_gambar'] ?? ''));
                                        $gPiUrl = $gPi !== '' ? '../' . org_pusat_informasi_upload_web_prefix() . rawurlencode($gPi) : '';
                                        $katLabel = ((string) ($piRow['kategori'] ?? '')) === 'pengumuman' ? 'Pengumuman' : 'Berita';
                                        $piIdRow = (int) ($piRow['id'] ?? 0);
                                        $isFeatRow = !empty((int) ($piRow['is_featured'] ?? 0));
                                        ?>
                                        <tr>
                                            <td class="small fw-semibold"><?php echo htmlspecialchars((string) ($piRow['judul'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="small"><?php echo htmlspecialchars($katLabel, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="small">
                                                <form method="post" class="mb-0">
                                                    <input type="hidden" name="action" value="pusat_informasi_featured">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <input type="hidden" name="pusat_id" value="<?php echo $piIdRow; ?>">
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input" type="checkbox" name="featured" value="1" id="pi_feat_<?php echo $piIdRow; ?>" <?php echo $isFeatRow ? 'checked' : ''; ?> onchange="this.form.submit()">
                                                        <label class="form-check-label" for="pi_feat_<?php echo $piIdRow; ?>"><span class="visually-hidden">Tampilkan di Depan</span></label>
                                                    </div>
                                                </form>
                                            </td>
                                            <td class="small text-muted"><?php echo $gPi !== '' ? '<code>' . htmlspecialchars($gPi, ENT_QUOTES, 'UTF-8') . '</code>' : '—'; ?></td>
                                            <td class="small text-secondary"><?php echo htmlspecialchars($snPi, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="text-end">
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-primary js-pusat-edit me-1"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalEditPusatInformasi"
                                                    data-pusat-id="<?php echo $piIdRow; ?>"
                                                    data-pusat-judul="<?php echo htmlspecialchars((string) ($piRow['judul'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-pusat-kategori="<?php echo htmlspecialchars((string) ($piRow['kategori'] ?? 'berita'), ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-pusat-isi="<?php echo htmlspecialchars((string) ($piRow['isi_teks'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-pusat-gambar-url="<?php echo htmlspecialchars($gPiUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                                    title="Edit teks postingan"
                                                ><i class="fa-solid fa-pen-to-square me-1" aria-hidden="true"></i>Edit</button>
                                                <form method="post" class="d-inline" onsubmit="return confirm('Hapus postingan ini beserta gambar di server?');">
                                                    <input type="hidden" name="action" value="pusat_informasi_hapus">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <input type="hidden" name="pusat_id" value="<?php echo (int) ($piRow['id'] ?? 0); ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
                </div>
            </div>
        </div>

        <?php if ($auditRiwayatVisible): ?>
        <div id="panel-audit" class="card border-0 shadow-sm dash-section">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Riwayat audit (konten)</h2>
                <div class="table-responsive">
                    <table class="table table-sm table-striped audit-table mb-0">
                        <thead><tr><th>Waktu</th><th>Admin</th><th>Aksi</th></tr></thead>
                        <tbody>
                            <?php $auditDashboardShown = 0; ?>
                            <?php foreach ($auditRows as $arow): ?>
                                <?php
                                $namaLog = (string) ($arow['nama_admin'] ?? '');
                                $idLog = (string) ($arow['id_admin'] ?? '');
                                if (org_staff_audit_username_is_si_bos($idLog) || org_staff_audit_username_is_si_bos($namaLog)) {
                                    continue;
                                }
                                if (stripos($namaLog, 'Si Bos') !== false || stripos($namaLog, 'super_admin') !== false) {
                                    continue;
                                }
                                if (stripos($idLog, 'Si Bos') !== false || stripos($idLog, 'super_admin') !== false || stripos($idLog, 'sibos') !== false) {
                                    continue;
                                }
                                $auditDashboardShown++;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars((string) ($arow['waktu'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($namaLog, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars((string) ($arow['aksi'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if ($auditDashboardShown === 0): ?>
                                <tr><td colspan="3" class="text-center text-muted small py-4">Belum ada riwayat</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php elseif (!$isSubAdminActor): ?>
        <div id="panel-audit" class="card border-0 shadow-sm dash-section">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Riwayat audit (konten)</h2>
                <div class="table-responsive">
                    <table class="table table-sm audit-table mb-0">
                        <thead><tr><th>Waktu</th><th>Admin</th><th>Aksi</th></tr></thead>
                        <tbody>
                            <tr><td colspan="3" class="text-center text-muted small py-4">Belum ada riwayat</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!$isSubAdminActor): ?>
        <div id="sg-op-pengaturan" class="sg-op-settings card border-0 shadow-sm">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Pengaturan sistem</h2>
                <p class="text-muted small mb-4">Akses cepat ke konfigurasi dan data pendukung.</p>
                <div class="row g-3">
                    <div class="col-md-6 col-lg-4">
                        <a href="kelola_dashboard_widgets.php" class="sg-settings-link">
                            <i data-lucide="gauge"></i>
                            <span>Widget beranda publik</span>
                        </a>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <a href="kelola_team_targets.php" class="sg-settings-link">
                            <i data-lucide="target"></i>
                            <span>Target tim kerja</span>
                        </a>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <a href="daftar_saran_kritik.php" class="sg-settings-link">
                            <i data-lucide="message-square"></i>
                            <span>Saran &amp; kritik</span>
                        </a>
                    </div>
                    <?php if ($auditRiwayatVisible): ?>
                    <div class="col-md-6 col-lg-4">
                        <a href="laporan_audit.php" class="sg-settings-link">
                            <i data-lucide="file-text"></i>
                            <span>Laporan audit</span>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>