<?php
declare(strict_types=1);

/** @var list<array<string, mixed>> $categories */
/** @var array<string, mixed>|null $category */
/** @var list<array<string, mixed>> $products */
/** @var string $query */

$heading = $category !== null ? bi($category, 'name') : t('equipment.title');
$lead = $category !== null ? bi($category, 'summary') : t('equipment.lead');
$totalItems = array_sum(array_map(static fn (array $row): int => (int) $row['product_count'], $categories));
$clearUrl = lang_url('/equipment' . ($category !== null ? '?category=' . urlencode((string) $category['slug']) : ''));
?>
<section class="page-head">
    <div class="shell">
        <nav class="crumbs" aria-label="<?= is_rtl() ? 'مسار التنقل' : 'Breadcrumb' ?>">
            <a href="<?= e(lang_url('/')) ?>"><?= e(t('nav.home')) ?></a>
            <span aria-hidden="true">/</span>
            <a href="<?= e(lang_url('/equipment')) ?>"><?= e(t('nav.equipment')) ?></a>
            <?php if ($category !== null): ?>
                <span aria-hidden="true">/</span>
                <span aria-current="page"><?= e($heading) ?></span>
            <?php endif; ?>
        </nav>

        <div class="page-head__row">
            <div>
                <p class="eyebrow"><?= e(t('equipment.eyebrow')) ?></p>
                <h1><?= e($heading) ?></h1>
                <p class="page-head__lead"><?= e($lead) ?></p>
            </div>

            <form class="catalog-search" method="get" action="<?= e(lang_url('/equipment')) ?>" role="search">
                <?php if ($category !== null): ?>
                    <input type="hidden" name="category" value="<?= e($category['slug']) ?>">
                <?php endif; ?>
                <label class="sr-only" for="q"><?= e(t('equipment.search.label')) ?></label>
                <input type="search" id="q" name="q" value="<?= e($query) ?>" placeholder="<?= e(t('equipment.search.placeholder')) ?>">
                <button class="catalog-search__submit" type="submit" aria-label="<?= e(t('equipment.search.label')) ?>">
                    <svg viewBox="0 0 20 20" aria-hidden="true"><circle cx="9" cy="9" r="5.5" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="m13 13 4 4" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
                </button>
            </form>
        </div>
    </div>
</section>

<section class="section section--catalog">
    <div class="shell catalog-layout">
        <aside class="filters">
            <p class="filters__title"><?= e(t('equipment.filter.label')) ?></p>
            <nav class="filters__list" aria-label="<?= e(t('equipment.filter.label')) ?>">
                <a class="filters__link<?= $category === null ? ' is-active' : '' ?>" href="<?= e(lang_url('/equipment')) ?>">
                    <span><?= e(t('equipment.filter.all')) ?></span>
                    <span class="filters__count"><?= $totalItems ?></span>
                </a>
                <?php foreach ($categories as $item): ?>
                    <a class="filters__link<?= $category !== null && (int) $category['id'] === (int) $item['id'] ? ' is-active' : '' ?>" href="<?= e(lang_url('/equipment?category=' . urlencode((string) $item['slug']))) ?>">
                        <span><?= e(bi($item, 'name')) ?></span>
                        <span class="filters__count"><?= (int) $item['product_count'] ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="filters__help">
                <p><?= e(t('equipment.help.title')) ?></p>
                <a class="btn btn--outline btn--sm btn--block" href="<?= e(lang_url('/contact')) ?>"><?= e(t('action.quote')) ?></a>
            </div>
        </aside>

        <div class="catalog-main">
            <div class="catalog-toolbar">
                <p><?= e(t('equipment.results', ['count' => count($products)])) ?></p>
                <?php if ($query !== ''): ?>
                    <p class="catalog-toolbar__query"><?= e(t('equipment.results_for', ['query' => $query])) ?></p>
                    <a class="catalog-toolbar__clear" href="<?= e($clearUrl) ?>"><?= e(t('equipment.search.clear')) ?></a>
                <?php endif; ?>
            </div>

            <?php if ($products === []): ?>
                <div class="empty-state">
                    <p class="empty-state__title"><?= e(t('equipment.empty')) ?></p>
                    <p><?= e(t('equipment.empty.body')) ?></p>
                    <a class="btn btn--dark btn--sm" href="<?= e(lang_url('/equipment')) ?>"><?= e(t('equipment.filter.all')) ?></a>
                </div>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($products as $product): ?>
                        <?php $item = $product; require APP_ROOT . '/app/views/public/partials/product-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
