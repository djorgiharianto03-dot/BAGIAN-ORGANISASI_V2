<?php

/** @var list<string> $profilMisiPoints */
/** @var string $profilVisiHtml */
/** @var string $profilOrgIntro */
/** @var bool $isAdmin */

$profilVisiHtml = $profilVisiHtml ?? '';
$profilMisiPoints = $profilMisiPoints ?? [];
$profilOrgIntro = trim((string) ($profilOrgIntro ?? ''));
$profilAsidePersonelCount = isset($personnelData) && is_array($personnelData) ? count($personnelData) : 0;
$profilAsideMisiCount = count($profilMisiPoints);
?>
        <section class="profil-org profil-org--premium profil-org--enterprise profil-org--institutional section-spacing" aria-labelledby="profil-page-title">
            <div class="profil-org__ambient" aria-hidden="true">
                <span class="profil-org__orb profil-org__orb--tr"></span>
                <span class="profil-org__orb profil-org__orb--bl"></span>
                <span class="profil-org__grain"></span>
            </div>

            <div class="profil-org__container profil-org__container--wide">
                <header class="profil-org__page-head profil-org__page-head--institutional" data-aos="fade-up" data-aos-duration="800" data-aos-easing="ease-out-cubic">
                    <span class="profil-org__accent-line" aria-hidden="true"></span>
                    <p class="profil-org__eyebrow">Bagian Organisasi</p>
                    <h2 id="profil-page-title" class="profil-org__page-title">Profil Organisasi</h2>
                    <p class="profil-org__page-lead">Visi, misi, dan ringkasan sebagai landasan pelayanan dan tata kelola unit kerja.</p>
                </header>

                <div class="profil-org__layout">
                    <div class="profil-org__main">
                        <div class="profil-org__timeline" aria-label="Visi, misi, dan ringkasan">
                            <div class="profil-org__timeline-item">
                                <article
                                    id="profil-visi"
                                    class="profil-org-glass profil-org-glass--visi"
                                    data-aos="fade-up"
                                    data-aos-duration="750"
                                    data-aos-easing="ease-out-cubic"
                                    aria-labelledby="profil-visi-label"
                                >
                                    <div class="profil-org-glass__inner">
                                        <div class="profil-org-card__head">
                                            <span class="profil-org-card__icon profil-org-card__icon--visi" aria-hidden="true">
                                                <svg class="profil-org-card__lucide" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </span>
                                            <p id="profil-visi-label" class="profil-org-vision__label">Visi</p>
                                        </div>
                                        <?php if ($profilVisiHtml !== ''): ?>
                                            <div class="profil-org-vision__body profil-body-rich">
                                                <?php echo $profilVisiHtml; ?>
                                            </div>
                                        <?php else: ?>
                                            <p class="profil-org-vision__text profil-org-vision__text--empty">Belum diisi di Dashboard Admin.</p>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            </div>

                            <div class="profil-org__timeline-item">
                                <article
                                    id="profil-misi"
                                    class="profil-org-glass profil-org-glass--misi"
                                    data-aos="fade-up"
                                    data-aos-duration="750"
                                    data-aos-delay="100"
                                    data-aos-easing="ease-out-cubic"
                                    aria-labelledby="profil-misi-label"
                                >
                                    <div class="profil-org-glass__inner">
                                        <div class="profil-org-card__head">
                                            <span class="profil-org-card__icon profil-org-card__icon--misi" aria-hidden="true">
                                                <svg class="profil-org-card__lucide" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                                            </span>
                                            <p id="profil-misi-label" class="profil-org-mission__label">Misi</p>
                                        </div>
                                        <?php if (count($profilMisiPoints) > 0): ?>
                                            <ul class="profil-org-mission__list list-unstyled mb-0">
                                                <?php foreach ($profilMisiPoints as $point): ?>
                                                    <li class="profil-org-mission__item">
                                                        <span class="profil-org-mission__bullet" aria-hidden="true">
                                                            <svg class="profil-org-card__lucide" viewBox="0 0 24 24" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>
                                                        </span>
                                                        <span class="profil-org-mission__text"><?php echo htmlspecialchars($point, ENT_QUOTES, 'UTF-8'); ?></span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <p class="profil-org-mission__empty mb-0">Belum diisi di Dashboard Admin.</p>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            </div>

                            <div class="profil-org__timeline-item">
                                <article
                                    id="profil-ringkasan"
                                    class="profil-org-glass profil-org-glass--ringkasan"
                                    data-aos="fade-up"
                                    data-aos-duration="750"
                                    data-aos-delay="180"
                                    data-aos-easing="ease-out-cubic"
                                    aria-labelledby="profil-ringkasan-label"
                                >
                                    <div class="profil-org-glass__inner">
                                        <div class="profil-org-card__head">
                                            <span class="profil-org-card__icon profil-org-card__icon--ringkasan" aria-hidden="true">
                                                <svg class="profil-org-card__lucide" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/></svg>
                                            </span>
                                            <p id="profil-ringkasan-label" class="profil-org-summary__label">Ringkasan Organisasi</p>
                                        </div>
                                        <?php if ($profilOrgIntro !== ''): ?>
                                            <p class="profil-org-summary__text mb-0"><?php echo nl2br(htmlspecialchars($profilOrgIntro, ENT_QUOTES, 'UTF-8')); ?></p>
                                        <?php else: ?>
                                            <p class="profil-org-summary__empty mb-0">Belum diisi di konten situs (Dashboard Admin → Ringkasan organisasi).</p>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            </div>
                        </div>
                    </div>

                    <aside class="profil-org__aside" aria-label="Informasi ringkas organisasi">
                        <div class="profil-org-aside" data-aos="fade-up" data-aos-duration="700" data-aos-delay="80">
                            <h3 class="profil-org-aside__title">Sekilas</h3>
                            <p class="profil-org-aside__lead">Unit kerja di lingkungan Sekretariat Daerah Kabupaten Kepulauan Aru.</p>
                            <dl class="profil-org-aside__facts">
                                <div class="profil-org-aside__fact">
                                    <dt>Personel</dt>
                                    <dd><?php echo (int) $profilAsidePersonelCount; ?></dd>
                                </div>
                                <div class="profil-org-aside__fact">
                                    <dt>Poin misi</dt>
                                    <dd><?php echo (int) $profilAsideMisiCount; ?></dd>
                                </div>
                            </dl>
                            <hr class="profil-org-aside__sep" aria-hidden="true">
                            <p class="profil-org-aside__nav-label">Akses terkait</p>
                            <nav class="profil-org-aside__nav" aria-label="Akses terkait profil">
                                <a class="profil-org-aside__link" href="dokumen.php"><i class="fa-regular fa-folder-open" aria-hidden="true"></i> Perpustakaan digital</a>
                                <a class="profil-org-aside__link" href="layanan.php"><i class="fa-regular fa-handshake" aria-hidden="true"></i> Layanan publik</a>
                                <a class="profil-org-aside__link" href="berita.php"><i class="fa-regular fa-newspaper" aria-hidden="true"></i> Pusat informasi</a>
                            </nav>
                        </div>
                    </aside>
                </div>

                <?php if (!empty($isAdmin)): ?>
                    <p class="profil-org__admin-hint text-center mb-0" data-aos="fade-up" data-aos-delay="240">
                        Untuk mengubah teks ini, gunakan <a href="admin/dashboard.php#panel-konten">Dashboard Admin → Edit konten</a>.
                    </p>
                <?php endif; ?>
            </div>
        </section>
