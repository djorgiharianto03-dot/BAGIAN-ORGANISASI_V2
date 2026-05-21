<?php
declare(strict_types=1);

/**
 * Dashboard KPI stat card.
 *
 * @var string $label
 * @var string|int $value
 * @var string $hint
 * @var string $tone blue|violet|emerald|amber
 * @var string $icon Font Awesome class
 */

org_tailwind_bootstrap();

$tone = preg_replace('/[^a-z]/', '', (string) ($tone ?? 'blue'));
$icon = (string) ($icon ?? 'fa-chart-line');
?>
<article class="org-dash-stat eo-stat-card eo-stat-card--<?php echo htmlspecialchars($tone, ENT_QUOTES, 'UTF-8'); ?>">
    <span class="org-dash-stat__icon org-dash-stat__icon--<?php echo htmlspecialchars($tone, ENT_QUOTES, 'UTF-8'); ?> eo-stat-card__icon" aria-hidden="true">
        <i class="fa-solid <?php echo htmlspecialchars($icon, ENT_QUOTES, 'UTF-8'); ?>"></i>
    </span>
    <div class="eo-stat-card__body min-w-0">
        <p class="org-dash-stat__label eo-stat-card__label"><?php echo htmlspecialchars((string) ($label ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
        <p class="org-dash-stat__value eo-stat-card__value">
            <?php
            if (!empty($valueHtml)) {
                echo $valueHtml;
            } else {
                echo htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
            }
            ?>
        </p>
        <?php if (($hint ?? '') !== ''): ?>
            <p class="org-dash-stat__hint eo-stat-card__hint"<?php echo !empty($hintId) ? ' id="' . htmlspecialchars((string) $hintId, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>><?php echo htmlspecialchars((string) $hint, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
    </div>
</article>
