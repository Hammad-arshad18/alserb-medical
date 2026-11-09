<?php
declare(strict_types=1);

/** @var array<string, mixed> $product */
/** @var list<array<string, mixed>> $related */

$categoryUrl = lang_url('/equipment?category=' . urlencode((string) $product['category_slug']));
$quoteUrl = lang_url('/contact?product=' . urlencode((string) $product['slug']));
$description = bi($product, 'description');
?>
<section class="page-head page-head--compact">
    <div class="shell">
        <nav class="crumbs" aria-label="<?= is_rtl() ? 'مسار التنقل' : 'Breadcrumb' ?>">
            <a href="<?= e(lang_url('/equipment')) ?>"><?= e(t('equipment.title')) ?></a>
            <span aria-hidden="true">/</span>
            <a href="<?= e($categoryUrl) ?>"><?= e(bi($product, 'category_name')) ?></a>
            <span aria-hidden="true">/</span>
            <span aria-current="page"><?= e(bi($product, 'name')) ?></span>
        </nav>
    </div>
</section>

<section class="section section--product">
    <div class="shell product-layout">
        <figure class="product-media">
            <img src="<?= e(image_url($product['image'] ?? null, (string) ($product['category_image'] ?? ''))) ?>" alt="<?= e(bi($product, 'name')) ?>" width="1200" height="900">
        </figure>

        <div class="product-info">
            <a class="product-info__cat" href="<?= e($categoryUrl) ?>"><?= e(bi($product, 'category_name')) ?></a>
            <h1><?= e(bi($product, 'name')) ?></h1>
            <p class="product-info__summary"><?= e(bi($product, 'summary')) ?></p>
            <p class="product-info__badge"><span class="status-dot"></span><?= e(t('product.supply_value')) ?></p>

            <?php if ($description !== ''): ?>
                <div class="product-info__description"><?= nl2br(e($description)) ?></div>
            <?php endif; ?>

            <div class="order-box">
                <p class="order-box__title"><?= e(t('product.order.title')) ?></p>
                <form class="order-form" method="post" action="<?= e(lang_url('/cart/add')) ?>" data-cart-add>
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                    <input type="hidden" name="return" value="<?= e(lang_url('/equipment/' . rawurlencode((string) $product['slug']))) ?>">
                    <div class="order-form__qty">
                        <label for="quantity"><?= e(t('product.quantity')) ?></label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" max="99" inputmode="numeric">
                    </div>
                    <button class="btn btn--primary" type="submit"><?= e(t('action.add_to_cart')) ?></button>
                </form>
                <p class="order-box__note"><?= e(t('product.order.note')) ?></p>
                <a class="link-arrow" href="<?= e($quoteUrl) ?>"><?= e(t('product.quote')) ?>
                    <svg class="icon-arrow" viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8h9M8.5 3.5 13 8l-4.5 4.5" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
                </a>
            </div>

            <dl class="meta-rows">
                <div>
                    <dt><?= e(t('product.department')) ?></dt>
                    <dd><a href="<?= e($categoryUrl) ?>"><?= e(bi($product, 'category_name')) ?></a></dd>
                </div>
                <div>
                    <dt><?= e(t('cart.summary.pricing')) ?></dt>
                    <dd><?= e(t('cart.summary.pricing_value')) ?></dd>
                </div>
                <div>
                    <dt><?= e(t('product.supply')) ?></dt>
                    <dd><?= e(t('product.supply_value')) ?></dd>
                </div>
            </dl>
        </div>
    </div>
</section>

<?php if ($related !== []): ?>
    <section class="section section--line">
        <div class="shell">
            <header class="section-head">
                <div class="section-head__top">
                    <p class="eyebrow"><?= e(t('product.department')) ?></p>
                    <a class="link-arrow" href="<?= e($categoryUrl) ?>"><?= e(t('action.view_all')) ?>
                        <svg class="icon-arrow" viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8h9M8.5 3.5 13 8l-4.5 4.5" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
                    </a>
                </div>
                <div class="section-head__row">
                    <h2><?= e(t('product.related')) ?></h2>
                </div>
            </header>

            <div class="product-grid">
                <?php foreach ($related as $item): ?>
                    <?php require APP_ROOT . '/app/views/public/partials/product-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
