<?php
declare(strict_types=1);

/** @var list<array<string, mixed>> $lines */
$count = 0;
foreach ($lines as $line) {
    $count += (int) $line['quantity'];
}
?>
<section class="page-head">
    <div class="shell">
        <p class="eyebrow"><?= e(t('nav.cart')) ?></p>
        <h1><?= e(t('cart.title')) ?></h1>
        <p class="page-head__lead"><?= e(t('cart.lead')) ?></p>
    </div>
</section>

<section class="section section--cart">
    <div class="shell">
        <?php if ($lines === []): ?>
            <div class="empty-state empty-state--center">
                <p class="empty-state__title"><?= e(t('cart.empty.title')) ?></p>
                <p><?= e(t('cart.empty.body')) ?></p>
                <a class="btn btn--dark" href="<?= e(lang_url('/equipment')) ?>"><?= e(t('cart.empty.cta')) ?></a>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <div class="cart-main">
                    <form class="cart-form" method="post" action="<?= e(lang_url('/cart/update')) ?>">
                        <?= csrf_field() ?>
                        <div class="cart-list">
                            <?php foreach ($lines as $line): ?>
                                <div class="cart-line">
                                    <a class="cart-line__media" href="<?= e(lang_url('/equipment/' . rawurlencode((string) $line['slug']))) ?>">
                                        <img src="<?= e(image_url($line['image'] ?? null, (string) ($line['category_image'] ?? ''))) ?>" alt="" loading="lazy" width="96" height="96">
                                    </a>
                                    <div class="cart-line__info">
                                        <span class="cart-line__cat"><?= e(bi($line, 'category_name')) ?></span>
                                        <a class="cart-line__name" href="<?= e(lang_url('/equipment/' . rawurlencode((string) $line['slug']))) ?>"><?= e(bi($line, 'name')) ?></a>
                                        <span class="cart-line__badge"><?= e(t('product.new')) ?></span>
                                    </div>
                                    <div class="cart-line__qty">
                                        <label for="qty-<?= (int) $line['id'] ?>"><?= e(t('cart.quantity')) ?></label>
                                        <input type="number" id="qty-<?= (int) $line['id'] ?>" name="qty[<?= (int) $line['id'] ?>]" value="<?= (int) $line['quantity'] ?>" min="1" max="99" inputmode="numeric">
                                    </div>
                                    <button class="cart-line__remove" type="submit" formaction="<?= e(lang_url('/cart/remove')) ?>" name="product_id" value="<?= (int) $line['id'] ?>"><?= e(t('cart.remove')) ?></button>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="cart-actions">
                            <a class="link-arrow" href="<?= e(lang_url('/equipment')) ?>"><?= e(t('action.continue_shopping')) ?></a>
                            <button class="btn btn--outline btn--sm" type="submit"><?= e(t('action.update_cart')) ?></button>
                        </div>
                    </form>
                </div>

                <aside class="summary">
                    <h2 class="summary__title"><?= e(t('cart.summary.title')) ?></h2>
                    <dl class="summary__rows">
                        <div>
                            <dt><?= e(t('cart.summary.items')) ?></dt>
                            <dd><?= $count ?></dd>
                        </div>
                        <div>
                            <dt><?= e(t('cart.summary.pricing')) ?></dt>
                            <dd><?= e(t('cart.summary.pricing_value')) ?></dd>
                        </div>
                    </dl>
                    <p class="summary__note"><?= e(t('cart.summary.note')) ?></p>
                    <a class="btn btn--primary btn--block" href="<?= e(lang_url('/checkout')) ?>"><?= e(t('action.checkout')) ?></a>
                    <a class="summary__continue" href="<?= e(lang_url('/equipment')) ?>"><?= e(t('action.continue_shopping')) ?></a>
                </aside>
            </div>
        <?php endif; ?>
    </div>
</section>
