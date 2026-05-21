<?php
declare(strict_types=1);

/** @var list<array{nama_opd: string, status: string, alasan: string}> $editDetailRows */
$editDetailRows = $editDetailRows ?? [];
?>
<hr class="my-3">
<div class="mb-2 d-flex justify-content-between align-items-center">
    <label class="form-label fw-semibold mb-0">Detail OPD</label>
    <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddDetailOpd">
        <i class="fa-solid fa-plus me-1" aria-hidden="true"></i>Tambah OPD
    </button>
</div>
<p class="text-muted small">Status: <strong>Selesai</strong>, <strong>Dalam Pengerjaan</strong>, atau <strong>Belum Ditambahkan</strong>. Isi <strong>Alasan</strong> wajib untuk status Belum Ditambahkan.</p>
<div id="detailOpdRows" class="mb-3">
    <?php if ($editDetailRows === []): ?>
        <p class="text-muted small mb-0" id="detailOpdEmptyHint">Belum ada baris OPD. Klik «Tambah OPD».</p>
    <?php endif; ?>
    <?php foreach ($editDetailRows as $detRow): ?>
        <div class="detail-opd-row border rounded-3 p-2 p-md-3 mb-2">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-5">
                    <label class="form-label">Nama OPD</label>
                    <input type="text" class="form-control form-control-sm" name="detail_nama_opd[]" maxlength="255" required
                           value="<?php echo htmlspecialchars((string) ($detRow['nama_opd'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label">Status</label>
                    <?php $detStatus = org_widget_details_normalize_status((string) ($detRow['status'] ?? 'belum')); ?>
                    <select class="form-select form-select-sm detail-status-select" name="detail_status[]">
                        <option value="selesai"<?php echo $detStatus === 'selesai' ? ' selected' : ''; ?>>Selesai</option>
                        <option value="dalam_pengerjaan"<?php echo $detStatus === 'dalam_pengerjaan' ? ' selected' : ''; ?>>Dalam Pengerjaan</option>
                        <option value="belum"<?php echo $detStatus === 'belum' ? ' selected' : ''; ?>>Belum Ditambahkan</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 detail-alasan-wrap">
                    <label class="form-label">Alasan</label>
                    <input type="text" class="form-control form-control-sm detail-alasan-input" name="detail_alasan[]" maxlength="500"
                           placeholder="Wajib jika belum"
                           value="<?php echo htmlspecialchars((string) ($detRow['alasan'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="col-6 col-md-1 text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-detail-opd" title="Hapus baris" aria-label="Hapus baris OPD">
                        <i class="fa-solid fa-trash" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
