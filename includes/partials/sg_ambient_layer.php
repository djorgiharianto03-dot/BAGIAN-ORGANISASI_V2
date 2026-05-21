<?php
declare(strict_types=1);
/** Lapisan ambient: glow + partikel (dekorasi). */
$sgAmbientVariant = $sgAmbientVariant ?? 'hero';
$sgParticleCount = (int) ($sgParticleCount ?? ($sgAmbientVariant === 'hero' ? 36 : 20));
$layerClass = 'sg-ambient-layer sg-ambient-layer--' . preg_replace('/[^a-z0-9-]/', '', (string) $sgAmbientVariant);
?>
<div class="<?php echo htmlspecialchars($layerClass, ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true">
    <span class="sg-ambient-glow sg-ambient-glow--a"></span>
    <span class="sg-ambient-glow sg-ambient-glow--b"></span>
    <span class="sg-ambient-glow sg-ambient-glow--c"></span>
    <div class="sg-particles" data-sg-particles="<?php echo max(8, min(48, $sgParticleCount)); ?>"></div>
</div>
