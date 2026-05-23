<?php
?>
            <div class="layanan-premium-empty" role="status">
                <svg class="layanan-premium-empty__svg" viewBox="0 0 200 140" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                    <defs>
                        <linearGradient id="layananEmptyGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#93c5fd"/>
                            <stop offset="100%" stop-color="#c4b5fd"/>
                        </linearGradient>
                    </defs>
                    <rect x="28" y="24" width="144" height="92" rx="14" fill="#f8fafc" stroke="#cbd5e1" stroke-width="2"/>
                    <path d="M52 88 L78 62 L98 78 L128 48 L152 88 Z" fill="url(#layananEmptyGrad)" opacity="0.55"/>
                    <circle cx="148" cy="48" r="10" fill="#fde68a" stroke="#f59e0b" stroke-width="2"/>
                    <rect x="62" y="102" width="76" height="8" rx="4" fill="#e2e8f0"/>
                </svg>
                <h3 class="layanan-premium-empty__title">Segera Hadir</h3>
                <p class="layanan-premium-empty__text">Layanan untuk kategori ini sedang dalam penyusunan. Informasi akan dipublikasikan setelah siap.</p>
                <div class="layanan-premium-empty__actions mt-3">
                    <a class="btn btn-sm btn-primary rounded-pill px-3" href="<?php echo org_href('index.php'); ?>">Kembali ke beranda</a>
                    <?php if (!empty($isAdmin)): ?>
                    <a class="btn btn-sm btn-outline-secondary rounded-pill px-3" href="<?php echo org_href('admin/dashboard.php', '', 'panel-layanan'); ?>">Kelola layanan</a>
                    <?php endif; ?>
                </div>
            </div>
