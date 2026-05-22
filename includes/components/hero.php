<?php
declare(strict_types=1);

/**
 * Hero section — portal sub-page variant.
 *
 * @var string $portalHeroEyebrow
 * @var string $portalHeroTitle
 * @var string $portalHeroLead
 * @var list<array{value: int|string, label: string}> $portalHeroStats
 */

org_tailwind_bootstrap();

$portalHeroLead = trim((string) ($portalHeroLead ?? ''));
$portalHeroStats = $portalHeroStats ?? [];
$portalHeroTitle = (string) ($portalHeroTitle ?? '');
$portalHeroEyebrow = (string) ($portalHeroEyebrow ?? '');
$portalHeroBreadcrumb = trim((string) ($portalHeroBreadcrumb ?? $portalHeroTitle ?? ''));
$portalHeroAriaLabel = $portalHeroTitle !== '' ? $portalHeroTitle : ($portalHeroBreadcrumb !== '' ? $portalHeroBreadcrumb : 'Halaman portal');
?>
<section class="org-hero org-hero--sub sg-subhero" aria-label="<?php echo htmlspecialchars($portalHeroAriaLabel, ENT_QUOTES, 'UTF-8'); ?>">
    <div class="org-hero__bg sg-subhero__bg" aria-hidden="true"></div>
    <?php $sgAmbientVariant = 'subhero'; $sgParticleCount = 24; require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'sg_ambient_layer.php'; ?>
    <div class="container-global hero-inner org-hero__container">
        <div class="org-hero__grid sg-subhero__grid">
            <div class="sg-subhero__copy">
                <?php if ($portalHeroEyebrow !== ''): ?>
                <p class="org-eyebrow sg-subhero__eyebrow sg-subhero__eyebrow--dash">
                    <span class="org-eyebrow__dot sg-hero__eyebrow-dot" aria-hidden="true"></span>
                    <?php echo htmlspecialchars($portalHeroEyebrow, ENT_QUOTES, 'UTF-8'); ?>
                </p>
                <?php endif; ?>
                <?php if ($portalHeroTitle !== ''): ?>
                <h1 class="org-hero__title org-heading-1 sg-subhero__title"><?php echo htmlspecialchars($portalHeroTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
                <?php endif; ?>
                <?php if ($portalHeroLead !== ''): ?>
                    <p class="org-hero__lead org-text-lead sg-subhero__lead"><?php echo htmlspecialchars($portalHeroLead, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
                <?php if ($portalHeroBreadcrumb !== ''): ?>
                <nav class="org-hero__breadcrumb sg-subhero__breadcrumb" aria-label="Breadcrumb">
                    <a href="<?php echo htmlspecialchars(function_exists('org_home_url') ? org_home_url() : 'index.php', ENT_QUOTES, 'UTF-8'); ?>">Beranda</a>
                    <span class="sg-subhero__breadcrumb-sep" aria-hidden="true">/</span>
                    <span class="sg-subhero__breadcrumb-current"><?php echo htmlspecialchars($portalHeroBreadcrumb, ENT_QUOTES, 'UTF-8'); ?></span>
                </nav>
                <?php endif; ?>
            </div>
            <?php if (count($portalHeroStats) > 0): ?>
                <div class="org-hero__stats sg-subhero__stats">
                    <?php foreach ($portalHeroStats as $st): ?>
                        <?php $statValue = (int) ($st['value'] ?? 0); ?>
                        <div class="org-hero__stat sg-subhero__stat">
                            <p class="org-hero__stat-value sg-subhero__stat-num mb-0" data-sg-count="<?php echo $statValue; ?>"><?php echo $statValue; ?></p>
                            <p class="org-hero__stat-label sg-subhero__stat-label mb-0"><?php echo htmlspecialchars((string) ($st['label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
