<?php

/** @var string $berandaLazySectionId @var string $berandaLazySectionLabel */
$berandaLazySectionId = $berandaLazySectionId ?? '';
$berandaLazySectionLabel = $berandaLazySectionLabel ?? 'Memuat konten…';
if ($berandaLazySectionId === '') {
    return;
}
$hostId = htmlspecialchars($berandaLazySectionId, ENT_QUOTES, 'UTF-8');
$berandaLazySectionHiddenTitle = isset($berandaLazySectionHiddenTitle) && is_string($berandaLazySectionHiddenTitle)
    ? trim($berandaLazySectionHiddenTitle)
    : '';
$berandaLazyHiddenTitleId = $berandaLazySectionHiddenTitle !== ''
    ? $hostId . '-section-title'
    : '';
?>
<div class="beranda-lazy-section section-spacing" data-beranda-lazy-section="<?php echo $hostId; ?>" aria-busy="true"<?php echo $berandaLazyHiddenTitleId !== '' ? ' aria-labelledby="' . htmlspecialchars($berandaLazyHiddenTitleId, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>>
<?php if ($berandaLazySectionHiddenTitle !== ''): ?>
    <h2 id="<?php echo htmlspecialchars($berandaLazyHiddenTitleId, ENT_QUOTES, 'UTF-8'); ?>" class="visually-hidden"><?php echo htmlspecialchars($berandaLazySectionHiddenTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
<?php endif; ?>
    <div class="beranda-lazy-section__placeholder" aria-hidden="true">
        <div class="beranda-lazy-section__placeholder-bar"></div>
        <div class="beranda-lazy-section__placeholder-block"></div>
        <p class="small text-muted mb-0 mt-2"><?php echo htmlspecialchars($berandaLazySectionLabel, ENT_QUOTES, 'UTF-8'); ?></p>
    </div>
    <div class="beranda-lazy-section__inner" data-beranda-section-host="<?php echo $hostId; ?>">
