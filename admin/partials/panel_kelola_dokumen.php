<?php
/** @var list<string> $dashLibraryFiles */
/** @var array<string, array<string, mixed>> $dashLibraryStatsMap */
/** @var string $dashLibraryDir */
/** @var string $csrfToken */
/** @var bool $canManagePerpustakaanDokumen */
$canManagePerpustakaanDokumen = $canManagePerpustakaanDokumen ?? org_staff_can_manage_perpustakaan_dokumen();
?>
        <div id="panel-kelola-dokumen" class="card border-0 shadow-sm dash-section">
            <div class="card-body p-4">
                <h2 class="h5 mb-2">Kelola dokumen</h2>
                <p class="text-muted small mb-3">Filter dan buka file perpustakaan digital<?php echo $canManagePerpustakaanDokumen ? ', atau hapus file' : ''; ?>. Pratinjau publik: <a href="<?php echo org_href('dokumen.php'); ?>" target="_blank" rel="noopener">halaman dokumen</a>.</p>
                <label for="adminDocumentSearch" class="form-label">Filter tabel</label>
                <div class="admin-doc-search-wrap mb-3">
                    <svg class="admin-doc-search-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                    <input type="text" class="form-control" id="adminDocumentSearch" placeholder="Cari nama file..." autocomplete="off">
                </div>
                <div class="d-flex flex-wrap gap-2 mb-3" id="adminDocumentQuickFilter">
                    <button type="button" class="btn btn-sm btn-outline-secondary active" data-admin-doc-filter="all">Semua File</button>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-admin-doc-filter="pdf">Dokumen PDF/Word/Excel</button>
                    <button type="button" class="btn btn-sm btn-outline-success" data-admin-doc-filter="visual">Foto Struktur</button>
                </div>
                <?php if (count($dashLibraryFiles) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama file</th>
                                    <th class="text-nowrap">Kategori</th>
                                    <th class="text-nowrap">Ukuran</th>
                                    <th class="text-nowrap text-center">Unduhan</th>
                                    <th class="text-center" style="width: 220px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="adminDocumentTableBody">
                                <?php foreach ($dashLibraryFiles as $dashLibFile): ?>
                                    <?php
                                    $dashLibOnDisk = function_exists('org_dokumen_resolve_realpath')
                                        ? org_dokumen_resolve_realpath($dashLibFile)
                                        : null;
                                    if ($dashLibOnDisk === null) {
                                        $legacyPath = $dashLibraryDir . DIRECTORY_SEPARATOR . $dashLibFile;
                                        $dashLibOnDisk = is_file($legacyPath) ? $legacyPath : null;
                                    }
                                    $viewUrl = function_exists('org_dokumen_view_url')
                                        ? org_dokumen_view_url($dashLibFile)
                                        : '../view_dokumen.php?file=' . rawurlencode($dashLibFile);
                                    $dlUrl = function_exists('org_dokumen_download_url')
                                        ? org_dokumen_download_url($dashLibFile)
                                        : '../download_dokumen.php?file=' . rawurlencode($dashLibFile);
                                    $namaFileDash = org_dokumen_stored_basename($dashLibFile);
                                    $namaTampilanDash = str_replace('_', ' ', $namaFileDash);
                                    $filterHaystackDash = strtolower($dashLibFile . ' ' . $namaTampilanDash);
                                    $statRowDash = $dashLibraryStatsMap[$dashLibFile] ?? null;
                                    $katDash = $statRowDash !== null ? (string) ($statRowDash['kategori'] ?? 'Kelembagaan') : org_dokumen_kategori_from_filename($dashLibFile);
                                    $katDash = org_dokumen_normalize_tim_kategori($katDash);
                                    $isVisualDash = org_dokumen_is_visual_kategori($katDash);
                                    $typeFilterDash = $isVisualDash ? 'visual' : 'pdf';
                                    $badgeClassDash = $isVisualDash ? 'text-bg-success' : 'text-bg-primary';
                                    $badgeIconDash = $isVisualDash ? 'fa-image' : 'fa-file-lines';
                                    $dlCntDash = $statRowDash !== null ? (int) ($statRowDash['jumlah_unduh'] ?? 0) : 0;
                                    $bytesDash = $dashLibOnDisk !== null ? (int) filesize($dashLibOnDisk) : 0;
                                    ?>
                                    <tr data-file-name="<?php echo htmlspecialchars($filterHaystackDash, ENT_QUOTES, 'UTF-8'); ?>" data-file-type="<?php echo htmlspecialchars($typeFilterDash, ENT_QUOTES, 'UTF-8'); ?>">
                                        <td><?php echo htmlspecialchars($namaTampilanDash, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><span class="badge <?php echo htmlspecialchars($badgeClassDash, ENT_QUOTES, 'UTF-8'); ?>"><i class="fa-solid <?php echo htmlspecialchars($badgeIconDash, ENT_QUOTES, 'UTF-8'); ?> me-1" aria-hidden="true"></i><?php echo htmlspecialchars($katDash, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td class="text-muted small"><?php echo htmlspecialchars(org_format_file_size($bytesDash), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-center small"><?php echo (int) $dlCntDash; ?></td>
                                        <td class="text-center">
                                            <?php if ($dashLibOnDisk !== null): ?>
                                                <a class="btn btn-sm btn-outline-secondary me-1" href="<?php echo htmlspecialchars($viewUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" title="Pratinjau">Lihat</a>
                                                <a class="btn btn-sm btn-outline-primary me-1" href="<?php echo htmlspecialchars($dlUrl, ENT_QUOTES, 'UTF-8'); ?>" title="Unduh">Unduh</a>
                                            <?php else: ?>
                                                <span class="badge text-bg-warning me-1">Berkas hilang</span>
                                            <?php endif; ?>
                                            <?php if ($canManagePerpustakaanDokumen): ?>
                                            <form method="post" class="d-inline" action="<?php echo org_href('admin/dashboard.php', '', 'panel-kelola-dokumen'); ?>">
                                                <input type="hidden" name="action" value="delete_file">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                                <input type="hidden" name="file_name" value="<?php echo htmlspecialchars($dashLibFile, ENT_QUOTES, 'UTF-8'); ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus file ini?');">Hapus</button>
                                            </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted small mb-0">Belum ada file. Unggah dokumen pertama melalui panel di atas.</p>
                <?php endif; ?>
            </div>
        </div>
