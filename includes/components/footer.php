<?php
declare(strict_types=1);

/**
 * Site footer — semantic org-footer structure.
 *
 * @var string $prosesSaranUrlEsc
 */

org_tailwind_bootstrap();
?>
    </main>
    <footer class="org-footer site-footer site-footer--modern mt-5">
        <div class="org-footer__cta site-footer__cta-band">
            <div class="container-global">
                <div class="grid gap-6 lg:grid-cols-2 lg:items-stretch">
                    <div class="order-1 lg:order-2">
                        <div class="org-footer__card site-footer-card site-footer-card--form h-full">
                            <h2 class="org-footer__card-title site-footer-card__title">Saran &amp; kritik</h2>
                            <p class="org-footer__card-lead site-footer-card__lead">Masukan Anda membantu kami meningkatkan layanan informasi. Pengiriman tanpa memuat ulang halaman.</p>
                            <form id="formSaranPublik" class="site-footer-form grid gap-3" method="post" action="<?php echo $prosesSaranUrlEsc; ?>" data-saran-endpoint="<?php echo $prosesSaranUrlEsc; ?>" novalidate>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="org-footer-form__label site-footer-form__label" for="saran_nama">Nama</label>
                                        <input type="text" class="org-footer-form__control form-control site-footer-form__control" id="saran_nama" name="nama" placeholder="Nama" required maxlength="190">
                                    </div>
                                    <div>
                                        <label class="org-footer-form__label site-footer-form__label" for="saran_email">Email</label>
                                        <input type="email" class="org-footer-form__control form-control site-footer-form__control" id="saran_email" name="email" placeholder="Email" required maxlength="190">
                                    </div>
                                </div>
                                <div>
                                    <label class="org-footer-form__label site-footer-form__label" for="saran_pesan">Pesan</label>
                                    <textarea class="org-footer-form__control form-control site-footer-form__control site-footer-form__textarea" id="saran_pesan" name="pesan" rows="4" placeholder="Tulis pesan Anda di sini…" required maxlength="20000"></textarea>
                                </div>
                                <div>
                                    <?php echo org_ui_button('Kirim', ['variant' => 'primary', 'type' => 'submit', 'class' => 'btn btn-site-footer-submit', 'id' => 'btnSaranPublik']); ?>
                                    <div class="site-footer-form__status org-text-muted mt-2 mb-0 text-org-xs" id="saranPublikStatus" role="status" aria-live="polite"></div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="order-2 lg:order-1">
                        <div class="org-footer__card site-footer-card site-footer-card--contact h-full">
                            <h2 class="org-footer__card-title site-footer-card__title">Hubungi kami</h2>
                            <p class="org-footer__card-lead site-footer-card__lead site-footer-card__lead--emphasis">Bagian Organisasi — Sekretariat Daerah Kabupaten Kepulauan Aru.</p>
                            <div class="footer-contact footer-contact--boxed">
                                <a class="footer-contact__row" href="mailto:aru.organisasi@gmail.com">
                                    <span class="footer-contact__icon" aria-hidden="true"><i class="fa-solid fa-envelope"></i></span>
                                    <span class="footer-contact__text">
                                        <span class="footer-contact__label">Email</span>
                                        aru.organisasi@gmail.com
                                    </span>
                                </a>
                                <div class="footer-contact__row">
                                    <span class="footer-contact__icon" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></span>
                                    <span class="footer-contact__text">
                                        <span class="footer-contact__label">Alamat</span>
                                        Jl. Pemda, Kabupaten Kepulauan Aru, Maluku.
                                    </span>
                                </div>
                                <div class="footer-contact__row">
                                    <span class="footer-contact__icon" aria-hidden="true"><i class="fa-solid fa-phone"></i></span>
                                    <span class="footer-contact__text">
                                        <span class="footer-contact__label">Telepon</span>
                                        Koordinasi resmi melalui email di atas.
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="org-footer__links site-footer__links-band">
            <div class="container-global py-6">
                <nav aria-label="Menu footer" class="site-footer__nav-wrap">
                    <ul class="org-footer__nav site-footer__nav">
                        <li><a href="index.php">Beranda</a></li>
                        <li><a href="profil.php">Profil</a></li>
                        <li><a href="layanan.php">Layanan</a></li>
                        <li><a href="dokumen.php">Dokumen</a></li>
                        <li><a href="berita.php">Pusat Informasi &amp; Pengumuman</a></li>
                        <li><a href="galeri.php">Galeri</a></li>
                    </ul>
                </nav>
                <div class="site-footer__copyright mt-4">
                    <div class="site-footer__copyright-inner">
                        <p class="org-footer__copyright site-footer__copyright-text mb-0">
                            Copyright &copy; DJH 2026 Bagian Organisasi Setda Kepulauan Aru. All Rights Reserved.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
