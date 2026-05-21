<?php declare(strict_types=1);

/** @var list<array{key: string, user_id: int, label: string, username: string, rows: list<array<string, mixed>>}> $tugasGrouped */
/** @var bool $isKabag */
/** @var bool $canUpload */
/** @var int $viewerUserId */
/** @var string $roleNorm */

if (!function_exists('manajemen_tugas_render_table_row')) {
    return;
}
?>
<?php if ($tugasGrouped === []): ?>
    <div class="tugas-empty rounded-3 border bg-light">
        <i class="fa-regular fa-folder-open fa-2x mb-2 d-block opacity-50" aria-hidden="true"></i>
        Belum ada tugas<?php echo $canUpload ? '. Klik <strong>Unggah Tugas</strong> untuk memulai.' : '.'; ?>
    </div>
<?php else: ?>
    <p class="small text-muted mb-3 mb-md-0">
        Klik nama pegawai untuk membuka daftar tugas. Urutan default: <strong>terbaru di atas</strong>.
    </p>
    <div class="accordion tugas-pegawai-accordion" id="accordionTugasPegawai">
        <?php foreach ($tugasGrouped as $gi => $grp): ?>
            <?php
            $collapseId = org_tugas_group_collapse_dom_id((string) $grp['key']);
            $headingId = $collapseId . '-heading';
            $tbodyId = $collapseId . '-tbody';
            $label = (string) $grp['label'];
            $username = trim((string) $grp['username']);
            $nTasks = count($grp['rows']);
            $nPending = 0;
            foreach ($grp['rows'] as $gr) {
                if (org_tugas_status_normalize((string) ($gr['status'] ?? '')) === 'pending') {
                    $nPending++;
                }
            }
            $initials = '';
            $parts = preg_split('/\s+/u', $label, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            foreach (array_slice($parts, 0, 2) as $p) {
                $initials .= mb_strtoupper(mb_substr($p, 0, 1));
            }
            if ($initials === '') {
                $initials = '?';
            }
            $isFirst = $gi === 0;
            ?>
            <div class="accordion-item tugas-acc-item">
                <h2 class="accordion-header" id="<?php echo htmlspecialchars($headingId, ENT_QUOTES, 'UTF-8'); ?>">
                    <button
                        class="accordion-button<?php echo $isFirst ? '' : ' collapsed'; ?>"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#<?php echo htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>"
                        aria-expanded="<?php echo $isFirst ? 'true' : 'false'; ?>"
                        aria-controls="<?php echo htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>"
                    >
                        <span class="d-flex align-items-center gap-3 w-100 me-2">
                            <span class="tugas-acc-avatar" aria-hidden="true"><?php echo htmlspecialchars($initials, ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="tugas-acc-meta text-start flex-grow-1">
                                <span class="d-block"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php if ($username !== '' && $username !== $label): ?>
                                    <span class="small text-muted fw-normal">@<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endif; ?>
                            </span>
                            <span class="d-flex flex-wrap gap-1 justify-content-end">
                                <span class="badge rounded-pill text-bg-primary tugas-acc-count"><?php echo (int) $nTasks; ?> tugas</span>
                                <?php if ($nPending > 0): ?>
                                    <span class="badge rounded-pill text-bg-warning text-dark tugas-acc-count"><?php echo (int) $nPending; ?> pending</span>
                                <?php endif; ?>
                            </span>
                        </span>
                    </button>
                </h2>
                <div
                    id="<?php echo htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>"
                    class="accordion-collapse collapse<?php echo $isFirst ? ' show' : ''; ?>"
                    aria-labelledby="<?php echo htmlspecialchars($headingId, ENT_QUOTES, 'UTF-8'); ?>"
                    data-bs-parent="#accordionTugasPegawai"
                >
                    <div class="accordion-body tugas-acc-body">
                        <div class="tugas-acc-toolbar d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <span class="small text-muted"><i class="fa-solid fa-list-check me-1" aria-hidden="true"></i>Tugas milik <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></span>
                            <div class="d-flex align-items-center gap-2">
                                <label class="small text-muted mb-0" for="sort-<?php echo htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>">Urutan</label>
                                <select
                                    id="sort-<?php echo htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>"
                                    class="form-select form-select-sm js-tugas-group-sort"
                                    style="width:auto;min-width:11rem;"
                                    data-target="#<?php echo htmlspecialchars($tbodyId, ENT_QUOTES, 'UTF-8'); ?>"
                                >
                                    <option value="desc" selected>Terbaru di atas</option>
                                    <option value="asc">Terlama di atas</option>
                                </select>
                            </div>
                        </div>
                        <div class="tugas-acc-table-wrap table-responsive">
                            <table class="table table-sm table-hover align-middle tugas-table mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:3rem">#</th>
                                        <th>Judul</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="<?php echo htmlspecialchars($tbodyId, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php foreach ($grp['rows'] as $ri => $row): ?>
                                        <?php
                                        $canEditRow = org_tugas_user_can_edit_row($row, $viewerUserId, $roleNorm, $isKabag);
                                        $canDeleteRow = org_tugas_user_can_delete_row($row, $viewerUserId, $roleNorm, $isKabag);
                                        manajemen_tugas_render_table_row($row, $isKabag, $canEditRow, $canDeleteRow, $ri + 1);
                                        ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
