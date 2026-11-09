<?php
declare(strict_types=1);

/** @var array<string, mixed> $item */
$itemUrl = lang_url('/equipment/' . rawurlencode((string) $item['slug']));
$itemImage = image_url($item['image'] ?? null, (string) ($item['category_image'] ?? ''));
?>
<article class="pcard">
    <a class="pcard__media" href="<?= e($itemUrl) ?>" tabindex="-1" aria-hidden="true">
        <img src="<?= e($itemImage) ?>" alt="" loading="lazy" width="1200" height="900">
    </a>
    <div class="pcard__body">
        <div class="pcard__meta">
            <span><?= e(bi($item, 'category_name')) ?></span>
            <span class="pcard__badge"><?= e(t('product.new')) ?></span>
        </div>
        <h3 class="pcard__title"><a href="<?= e($itemUrl) ?>"><?= e(bi($item, 'name')) ?></a></h3>
        <p class="pcard__summary"><?= e(bi($item, 'summary')) ?></p>
        <div class="pcard__foot">
            <form class="pcard__form" method="post" action="<?= e(lang_url('/cart/add')) ?>" data-cart-add>
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= (int) $item['id'] ?>">
                <input type="hidden" name="quantity" value="1">
                <button class="btn btn--soft btn--xs" type="submit" aria-label="<?= e(t('action.add_to_cart') . ': ' . bi($item, 'name')) ?>">
                    <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M8 3v10M3 8h10" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
                    <?= e(t('action.add_to_cart')) ?>
                </button>
            </form>
            <a class="pcard__details" href="<?= e($itemUrl) ?>"><?= e(t('action.details')) ?>
                <svg class="icon-arrow" viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8h9M8.5 3.5 13 8l-4.5 4.5" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
            </a>
        </div>
    </div>
</article>
