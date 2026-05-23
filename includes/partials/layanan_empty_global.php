<?php
?>
        <div class="layanan-dir-global-empty" role="status">
            <svg class="layanan-dir-global-empty__svg" viewBox="0 0 220 160" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                <defs>
                    <linearGradient id="ldEmptyGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#bfdbfe"/>
                        <stop offset="100%" stop-color="#a5b4fc"/>
                    </linearGradient>
                </defs>
                <rect x="20" y="28" width="180" height="104" rx="16" fill="#f8fafc" stroke="#e2e8f0" stroke-width="2"/>
                <rect x="44" y="52" width="132" height="10" rx="5" fill="#e2e8f0"/>
                <rect x="44" y="72" width="96" height="10" rx="5" fill="#e2e8f0"/>
                <rect x="44" y="92" width="108" height="10" rx="5" fill="#e2e8f0"/>
                <circle cx="110" cy="118" r="18" fill="url(#ldEmptyGrad)" opacity="0.85"/>
                <path d="M104 118l4 4 10-10" stroke="#fff" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <h2 id="layanan-empty-global-title" class="layanan-dir-global-empty__title">Belum ada layanan yang dipublikasikan</h2>
            <p class="layanan-dir-global-empty__text">Daftar layanan akan tampil di sini setelah diatur melalui dashboard admin. Silakan kembali lagi nanti atau hubungi administrator situs.</p>
            <div class="layanan-dir-global-empty__actions">
                <a class="layanan-dir__btn layanan-dir__btn--primary" href="<?php echo org_href('index.php'); ?>"><i class="fa-solid fa-house" aria-hidden="true"></i> Kembali ke beranda</a>
                <a class="layanan-dir__btn" href="<?php echo org_href('dokumen.php'); ?>"><i class="fa-regular fa-folder-open" aria-hidden="true"></i> Perpustakaan digital</a>
                <?php if (!empty($isAdmin)): ?>
                <a class="layanan-dir__btn" href="<?php echo org_href('admin/dashboard.php', '', 'panel-layanan'); ?>"><i class="fa-solid fa-gear" aria-hidden="true"></i> Kelola layanan</a>
                <?php endif; ?>
            </div>
        </div>
