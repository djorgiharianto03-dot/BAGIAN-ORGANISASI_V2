<?php

/** @var list<array<string, mixed>> $pusatCarouselPosts */
/** @var callable(string, string): string|null $pusatHighlightSearch */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'pusat_informasi_ui.php';

$posts = $pusatCarouselPosts ?? [];
$highlight = $pusatHighlightSearch ?? null;
$postCount = count($posts);

$heroPost = null;
foreach ($posts as $piRow) {
    if (!is_array($piRow)) {
        continue;
    }
    if (!empty((int) ($piRow['is_featured'] ?? 0))) {
        $heroPost = $piRow;
        break;
    }
}
if ($heroPost === null && $postCount > 0 && is_array($posts[0])) {
    $heroPost = $posts[0];
}

$gridPosts = [];
$heroId = $heroPost !== null ? (int) ($heroPost['id'] ?? 0) : 0;
foreach ($posts as $piRow) {
    if (!is_array($piRow)) {
        continue;
    }
    if ((int) ($piRow['id'] ?? 0) !== $heroId) {
        $gridPosts[] = $piRow;
    }
}

$gridCount = count($gridPosts);
$gridMod = $gridCount === 1 ? 'np-grid--solo' : ($gridCount === 2 ? 'np-grid--duo' : 'np-grid--multi');
$heroPartial = __DIR__ . DIRECTORY_SEPARATOR . 'pusat_informasi_hero_card.php';
$cellPartial = __DIR__ . DIRECTORY_SEPARATOR . 'pusat_informasi_grid_cell.php';
?>
            <?php if ($postCount > 0): ?>
                <div class="news-portal" data-aos="fade-up" data-aos-duration="600">
                    <?php
                    if ($heroPost !== null):
                        $pi = $heroPost;
                        require $heroPartial;
                    endif;
                    ?>
                    <?php if ($gridCount > 0): ?>
                    <section class="np-latest" aria-labelledby="np-latest-title">
                        <header class="np-latest__head">
                            <h2 id="np-latest-title" class="np-latest__title">Publikasi Lainnya</h2>
                            <p class="np-latest__count"><?php echo (int) $gridCount; ?> entri</p>
                        </header>
                        <div class="np-grid <?php echo htmlspecialchars($gridMod, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php
                            foreach ($gridPosts as $cardIdx => $pi):
                                require $cellPartial;
                            endforeach;
                            ?>
                        </div>
                    </section>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="np-empty" role="status" data-aos="fade-up">
                    <div class="np-empty__icon" aria-hidden="true"><i class="fa-regular fa-newspaper"></i></div>
                    <p class="np-empty__text mb-0"><?php echo ($pusatSearchQuery ?? '') !== '' ? 'Tidak ada entri yang cocok dengan pencarian.' : 'Belum ada publikasi di Pusat Informasi.'; ?></p>
                </div>
            <?php endif; ?>
