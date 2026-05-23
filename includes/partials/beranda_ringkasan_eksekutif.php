<?php
declare(strict_types=1);

/** @var string $berandaVisiRingkas */
/** @var string $berandaMisiPlain */
?>
            <div class="row row-cols-1 row-cols-md-3 g-4 beranda-exec-grid align-items-stretch">
                <div class="col d-flex">
                    <article class="beranda-exec-card beranda-exec-card--visi w-100">
                        <span class="beranda-exec-card__quote" aria-hidden="true">&ldquo;</span>
                        <div class="beranda-exec-card__icon-wrap" aria-hidden="true"><i class="fa-regular fa-eye"></i></div>
                        <h3 class="beranda-exec-card__title">Visi</h3>
                        <div class="beranda-exec-card__content">
                            <div class="beranda-exec-card__body beranda-exec-card__body--fade">
                                <?php if ($berandaVisiRingkas !== ''): ?>
                                    <p class="beranda-exec-card__text"><?php echo htmlspecialchars($berandaVisiRingkas, ENT_QUOTES, 'UTF-8'); ?></p>
                                <?php else: ?>
                                    <p class="beranda-exec-card__text text-muted">Belum diisi.</p>
                                <?php endif; ?>
                            </div>
                            <footer class="beranda-exec-card__footer mt-auto">
                                <a class="beranda-exec-ghost-btn beranda-exec-ghost-btn--visi" href="<?php echo org_href('profil.php', '', 'profil-visi'); ?>">
                                    Selengkapnya <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                </a>
                            </footer>
                        </div>
                    </article>
                </div>
                <div class="col d-flex">
                    <article class="beranda-exec-card beranda-exec-card--misi w-100">
                        <span class="beranda-exec-card__quote" aria-hidden="true">&ldquo;</span>
                        <div class="beranda-exec-card__icon-wrap" aria-hidden="true"><i class="fa-regular fa-flag"></i></div>
                        <h3 class="beranda-exec-card__title">Misi</h3>
                        <div class="beranda-exec-card__content">
                            <div class="beranda-exec-card__body beranda-exec-card__body--fade">
                                <?php if ($berandaMisiPlain !== ''): ?>
                                    <p class="beranda-exec-card__text"><?php echo htmlspecialchars($berandaMisiPlain, ENT_QUOTES, 'UTF-8'); ?></p>
                                <?php else: ?>
                                    <p class="beranda-exec-card__text text-muted">Belum diisi.</p>
                                <?php endif; ?>
                            </div>
                            <footer class="beranda-exec-card__footer mt-auto">
                                <a class="beranda-exec-ghost-btn beranda-exec-ghost-btn--misi" href="<?php echo org_href('profil.php', '', 'profil-misi'); ?>">
                                    Lihat selengkapnya <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                </a>
                            </footer>
                        </div>
                    </article>
                </div>
                <div class="col d-flex">
                    <article class="beranda-exec-card beranda-exec-card--struktur w-100">
                        <div class="beranda-exec-card__icon-wrap" aria-hidden="true"><i class="fa-solid fa-sitemap"></i></div>
                        <h3 class="beranda-exec-card__title">Struktur</h3>
                        <div class="beranda-exec-card__content">
                            <div class="beranda-exec-org" role="img" aria-label="Diagram hierarki: Kepala Bagian, Jabatan Fungsional, Jabatan Pelaksana">
                                <div class="beranda-exec-org__node beranda-exec-org__node--lead">
                                    <span class="beranda-exec-org__avatar"><i class="fa-solid fa-user-tie" aria-hidden="true"></i></span>
                                    <span class="beranda-exec-org__label">Kepala Bagian</span>
                                </div>
                                <span class="beranda-exec-org__vline" aria-hidden="true"></span>
                                <div class="beranda-exec-org__branch">
                                    <div class="beranda-exec-org__node">
                                        <span class="beranda-exec-org__label">Jabatan Fungsional</span>
                                    </div>
                                    <div class="beranda-exec-org__node">
                                        <span class="beranda-exec-org__label">Jabatan Pelaksana</span>
                                    </div>
                                </div>
                            </div>
                            <footer class="beranda-exec-card__footer mt-auto">
                                <a class="beranda-exec-ghost-btn beranda-exec-ghost-btn--struktur" href="<?php echo org_href('profil.php', '', 'profil-struktur-organisasi'); ?>">
                                    <i class="fa-solid fa-diagram-project" aria-hidden="true"></i> Bagan organisasi
                                </a>
                            </footer>
                        </div>
                    </article>
                </div>
            </div>
