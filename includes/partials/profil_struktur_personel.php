<?php
declare(strict_types=1);

/** @var string $profilStrukturImgWeb */
/** @var array<string, mixed> $siteSettings */
/** @var list<array<string, mixed>> $personnelKepalaList */
/** @var list<array<string, mixed>> $personnelLainList */
/** @var bool $isAdmin */

$profilStrukturImgWeb = $profilStrukturImgWeb ?? '';
$personnelKepalaList = $personnelKepalaList ?? [];
$personnelLainList = $personnelLainList ?? [];

$profilPersonPhotoIsPlaceholder = static function (string $photoUrl): bool {
    $u = strtolower($photoUrl);

    return $u === ''
        || str_starts_with($u, 'data:image')
        || str_contains($u, 'default')
        || str_contains($u, 'placeholder')
        || str_contains($u, 'no-photo')
        || str_contains($u, 'avatar');
};

$profilPersonRenderPhoto = static function (string $name, string $photoUrl) use ($profilPersonPhotoIsPlaceholder): void {
    $alt = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
  if ($profilPersonPhotoIsPlaceholder($photoUrl)) {
        echo '<div class="profil-person-exec__photo-placeholder" aria-hidden="true">';
        echo '<svg viewBox="0 0 24 24" width="48" height="48" stroke="currentColor" fill="none" stroke-width="1.5"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>';
        echo '</div>';
    } else {
        $src = htmlspecialchars($photoUrl, ENT_QUOTES, 'UTF-8');
        echo '<img class="profil-person-exec__photo-img" src="', $src, '" alt="', $alt, '" loading="lazy" decoding="async" onerror="this.style.display=\'none\';this.nextElementSibling?.classList.remove(\'d-none\');">';
        echo '<div class="profil-person-exec__photo-placeholder d-none" aria-hidden="true">';
        echo '<svg viewBox="0 0 24 24" width="48" height="48" stroke="currentColor" fill="none" stroke-width="1.5"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>';
        echo '</div>';
    }
    echo '<span class="profil-person-exec__photo-frame" aria-hidden="true"></span>';
};

$profilPersonRenderActions = static function (array $person) use ($isAdmin): void {
    if (empty($isAdmin)) {
        return;
    }
    $personNip = (string) ($person['nip'] ?? '');
    $personId = (string) ($person['id'] ?? '');
    ?>
    <div class="profil-person-exec__actions">
        <button
            type="button"
            class="profil-person-exec__action js-edit-person"
            data-bs-toggle="modal"
            data-bs-target="#editPersonnelModal"
            data-id="<?php echo htmlspecialchars($personId, ENT_QUOTES, 'UTF-8'); ?>"
            data-name="<?php echo htmlspecialchars((string) ($person['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
            data-nip="<?php echo htmlspecialchars($personNip, ENT_QUOTES, 'UTF-8'); ?>"
            data-position="<?php echo htmlspecialchars((string) ($person['position'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
            title="Edit personel"
        >
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
            <span>Edit</span>
        </button>
        <form method="post" class="profil-person-exec__action-form" onsubmit="return confirm('Yakin ingin menghapus personel ini secara permanen?');">
            <input type="hidden" name="action" value="delete_personnel">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(org_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="return_to" value="profil.php">
            <input type="hidden" name="person_id" value="<?php echo htmlspecialchars($personId, ENT_QUOTES, 'UTF-8'); ?>">
            <button type="submit" class="profil-person-exec__action profil-person-exec__action--danger" title="Hapus personel">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                <span>Hapus</span>
            </button>
        </form>
    </div>
    <?php
};
?>
        <section class="profil-structure section-spacing" id="profil-struktur-organisasi" aria-labelledby="profil-structure-title">
            <div class="profil-structure__ambient" aria-hidden="true"></div>

            <div class="profil-structure__container">
                <header class="profil-structure__head" data-aos="fade-up" data-aos-duration="420">
                    <span class="profil-structure__accent" aria-hidden="true"></span>
                    <p class="profil-structure__eyebrow">Struktur &amp; SDM</p>
                    <h2 id="profil-structure-title" class="profil-structure__title">Bagan Organisasi &amp; Daftar Personel</h2>
                </header>

                <div class="profil-structure__block" data-aos="fade-up" data-aos-duration="420">
                    <h3 class="profil-structure__block-title">Bagan organisasi</h3>
                    <p class="profil-structure__block-desc">Kepala Bagian berada di puncak dan membawahi kelompok jabatan fungsional serta pelaksana.</p>

                    <div class="profil-org-chart" role="group" aria-label="Bagan organisasi utama">
                        <div class="profil-org-chart__tree">
                            <div class="profil-org-chart__apex">
                                <article class="profil-org-chart__chief" aria-labelledby="org-chief-title">
                                    <div class="profil-org-chart__chief-inner">
                                        <span class="profil-org-chart__chief-icon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                        </span>
                                        <div class="profil-org-chart__chief-body">
                                            <span class="profil-org-chart__chief-badge">
                                                <span class="profil-org-chart__status-dot" aria-hidden="true"></span>
                                                Unit utama
                                            </span>
                                            <h4 id="org-chief-title" class="profil-org-chart__chief-title">Kepala Bagian Organisasi</h4>
                                        </div>
                                    </div>
                                </article>
                            </div>
                            <span class="profil-org-chart__connector-v" aria-hidden="true"></span>
                            <span class="profil-org-chart__connector-h" aria-hidden="true"></span>
                            <div class="profil-org-chart__branches">
                                <article class="profil-org-chart__node profil-org-chart__node--fungsional">
                                    <span class="profil-org-chart__node-icon" aria-hidden="true"><i class="fa-solid fa-user-graduate"></i></span>
                                    <div>
                                        <span class="profil-org-chart__node-meta">Dibawahi Kepala Bagian</span>
                                        <p class="profil-org-chart__node-label">Kelompok Jabatan Fungsional</p>
                                    </div>
                                </article>
                                <article class="profil-org-chart__node profil-org-chart__node--pelaksana">
                                    <span class="profil-org-chart__node-icon" aria-hidden="true"><i class="fa-solid fa-users-gear"></i></span>
                                    <div>
                                        <span class="profil-org-chart__node-meta">Dibawahi Kepala Bagian</span>
                                        <p class="profil-org-chart__node-label">Kelompok Jabatan Pelaksana</p>
                                    </div>
                                </article>
                            </div>
                        </div>
                    </div>

                    <?php
                    $strukturTeks = trim((string) ($siteSettings['profile_struktur'] ?? ''));
                    if ($strukturTeks !== ''):
                    ?>
                        <h3 id="profil-struktur-teks" class="profil-structure__block-title mt-4">Deskripsi struktur</h3>
                        <div class="profil-structure__text-block"><?php echo nl2br(htmlspecialchars($strukturTeks, ENT_QUOTES, 'UTF-8')); ?></div>
                    <?php endif; ?>

                    <?php if ($profilStrukturImgWeb !== ''): ?>
                        <div class="profil-structure__img-wrap d-flex flex-wrap align-items-start gap-3">
                            <a href="<?php echo htmlspecialchars($profilStrukturImgWeb, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">
                                <img class="img-fluid" style="max-width: min(100%, 560px);" src="<?php echo htmlspecialchars($profilStrukturImgWeb, ENT_QUOTES, 'UTF-8'); ?>" alt="Skema struktur organisasi" width="640" height="360" loading="lazy">
                            </a>
                            <div class="d-flex flex-column gap-2">
                                <a class="btn btn-sm btn-outline-primary rounded-pill px-3" href="<?php echo htmlspecialchars($profilStrukturImgWeb, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener"><i class="fa-solid fa-magnifying-glass-plus me-1" aria-hidden="true"></i>Lihat penuh</a>
                                <a class="btn btn-sm btn-outline-secondary rounded-pill px-3" href="<?php echo htmlspecialchars($profilStrukturImgWeb, ENT_QUOTES, 'UTF-8'); ?>" download><i class="fa-solid fa-download me-1" aria-hidden="true"></i>Unduh</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="profil-structure__block profil-personnel" data-aos="fade-up" data-aos-duration="420">
                    <div class="profil-personnel__toolbar">
                        <div>
                            <h3 class="profil-structure__block-title mb-1">Direktori personel</h3>
                            <p class="profil-structure__block-desc mb-0">Data personel dinamis sesuai struktur organisasi unit.</p>
                        </div>
                        <?php if (!empty($isAdmin)): ?>
                            <button type="button" class="profil-personnel__add-btn" data-bs-toggle="modal" data-bs-target="#addPersonnelModal">
                                <i class="fa-solid fa-user-plus me-1" aria-hidden="true"></i> Tambah personel
                            </button>
                        <?php endif; ?>
                    </div>

                    <?php if (count($personnelKepalaList) > 0): ?>
                        <div class="personnel-chief-stack mb-4 mb-lg-5" role="region" aria-label="Pimpinan unit">
                            <div class="row justify-content-center g-4">
                                <?php foreach ($personnelKepalaList as $idx => $person): ?>
                                    <?php
                                    $personNip = (string) ($person['nip'] ?? '');
                                    $photoUrl = (string) ($person['photo'] ?? '');
                                    ?>
                                    <div class="col-12 col-lg-8 col-xl-7">
                                        <article class="profil-person-exec profil-person-exec--chief h-100">
                                            <div class="profil-person-exec__inner">
                                                <div class="profil-person-exec__photo">
                                                    <?php $profilPersonRenderPhoto((string) ($person['name'] ?? ''), $photoUrl); ?>
                                                </div>
                                                <div class="profil-person-exec__body">
                                                    <span class="profil-person-exec__rank">Pimpinan struktur</span>
                                                    <h3 class="profil-person-exec__name"><?php echo htmlspecialchars((string) ($person['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></h3>
                                                    <p class="profil-person-exec__nip"><?php echo $personNip !== '' ? 'NIP: ' . htmlspecialchars($personNip, ENT_QUOTES, 'UTF-8') : 'NIP: —'; ?></p>
                                                    <p class="profil-person-exec__role"><?php echo htmlspecialchars((string) ($person['position'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                                                    <?php $profilPersonRenderActions($person); ?>
                                                </div>
                                            </div>
                                        </article>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (count($personnelLainList) > 0): ?>
                        <?php if (count($personnelKepalaList) > 0): ?>
                            <h3 class="profil-structure__block-title profil-personnel__subsection">Jabatan fungsional &amp; pelaksana</h3>
                        <?php endif; ?>
                        <div class="profil-personnel__grid profil-personnel__grid--staff" role="list">
                            <?php foreach ($personnelLainList as $idx => $person): ?>
                                <?php
                                $personNip = (string) ($person['nip'] ?? '');
                                $photoUrl = (string) ($person['photo'] ?? '');
                                ?>
                                <article class="profil-person-exec profil-person-exec--staff" role="listitem">
                                    <div class="profil-person-exec__inner">
                                        <div class="profil-person-exec__photo">
                                            <?php $profilPersonRenderPhoto((string) ($person['name'] ?? ''), $photoUrl); ?>
                                        </div>
                                        <div class="profil-person-exec__body">
                                            <h3 class="profil-person-exec__name"><?php echo htmlspecialchars((string) ($person['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></h3>
                                            <p class="profil-person-exec__nip"><?php echo $personNip !== '' ? 'NIP: ' . htmlspecialchars($personNip, ENT_QUOTES, 'UTF-8') : 'NIP: —'; ?></p>
                                            <p class="profil-person-exec__role"><?php echo htmlspecialchars((string) ($person['position'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                                            <?php $profilPersonRenderActions($person); ?>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif (count($personnelKepalaList) === 0): ?>
                        <p class="profil-personnel__empty mb-0" role="status">Belum ada data personel.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
