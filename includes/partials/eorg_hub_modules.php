<?php

/** @var list<array<string, mixed>> $eorgHubServices */
$services = $eorgHubServices ?? [];
?>
            <section class="eo-modules" aria-labelledby="eo-modules-title">
                <header class="eo-modules__head">
                    <h2 id="eo-modules-title" class="eo-modules__title">Modul layanan</h2>
                    <p class="eo-modules__lead">Akses cepat ke fungsi administrasi dan operasional.</p>
                </header>
                <div class="eo-modules__grid">
                    <?php foreach ($services as $svc): ?>
                        <?php
                        $theme = preg_replace('/[^a-z0-9_-]/', '', (string) ($svc['theme'] ?? 'default'));
                        $badge = $svc['badge'] ?? null;
                        ?>
                        <a
                            class="eo-module-card eo-module-card--<?php echo htmlspecialchars($theme, ENT_QUOTES, 'UTF-8'); ?>"
                            href="<?php echo htmlspecialchars((string) ($svc['href'] ?? '#'), ENT_QUOTES, 'UTF-8'); ?>"
                        >
                            <?php if (is_array($badge) && isset($badge['count'])): ?>
                                <?php
                                $toneRaw = (string) ($badge['tone'] ?? 'info');
                                $badgeTone = in_array($toneRaw, ['danger', 'warning', 'info'], true) ? $toneRaw : 'info';
                                ?>
                                <span class="eo-module-card__badge eo-module-card__badge--<?php echo htmlspecialchars($badgeTone, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo (int) $badge['count']; ?>
                                </span>
                            <?php endif; ?>
                            <span class="eo-module-card__icon" aria-hidden="true">
                                <i class="<?php echo htmlspecialchars((string) ($svc['icon'] ?? 'fa-solid fa-circle'), ENT_QUOTES, 'UTF-8'); ?>"></i>
                            </span>
                            <span class="eo-module-card__body">
                                <span class="eo-module-card__title"><?php echo htmlspecialchars((string) ($svc['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="eo-module-card__desc"><?php echo htmlspecialchars((string) ($svc['desc'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                            </span>
                            <span class="eo-module-card__arrow" aria-hidden="true"><i class="fa-solid fa-arrow-right"></i></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
