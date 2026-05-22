<?php
declare(strict_types=1);

/** @var string $berandaLazySectionId @var string $berandaLazySectionLabel */
$berandaLazySectionId = $berandaLazySectionId ?? '';
$berandaLazySectionLabel = $berandaLazySectionLabel ?? 'Memuat konten…';
if ($berandaLazySectionId === '') {
    return;
}
$hostId = htmlspecialchars($berandaLazySectionId, ENT_QUOTES, 'UTF-8');
?>
<div class="beranda-lazy-section section-spacing" data-beranda-lazy-section="<?php echo $hostId; ?>" aria-busy="true">
    <div class="beranda-lazy-section__placeholder" aria-hidden="true">
        <div class="beranda-lazy-section__placeholder-bar"></div>
        <div class="beranda-lazy-section__placeholder-block"></div>
        <p class="small text-muted mb-0 mt-2"><?php echo htmlspecialchars($berandaLazySectionLabel, ENT_QUOTES, 'UTF-8'); ?></p>
    </div>
    <div class="beranda-lazy-section__inner" data-beranda-section-host="<?php echo $hostId; ?>">
