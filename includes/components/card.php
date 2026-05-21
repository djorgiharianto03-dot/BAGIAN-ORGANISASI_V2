<?php
declare(strict_types=1);

/**
 * Generic content card — set $cardSlot for body HTML before including, or use helpers.
 *
 * @var string $cardTitle
 * @var string $cardBodyHtml
 * @var string $cardFooterHtml
 * @var string $cardVariant flat|interactive
 * @var string $cardClass
 * @var string $cardMediaHtml
 */

org_tailwind_bootstrap();

echo org_ui_card_open([
    'title' => $cardTitle ?? '',
    'variant' => $cardVariant ?? '',
    'class' => $cardClass ?? '',
]);
if (!empty($cardMediaHtml)) {
    echo '<div class="org-card__media">' . $cardMediaHtml . '</div>';
}
if (!empty($cardBodyHtml)) {
    echo '<div class="org-card__body">' . $cardBodyHtml . '</div>';
}
if (!empty($cardFooterHtml)) {
    echo '<div class="org-card__footer">' . $cardFooterHtml . '</div>';
}
echo org_ui_card_close();
