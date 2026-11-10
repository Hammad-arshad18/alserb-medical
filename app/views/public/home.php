<?php
declare(strict_types=1);

/** @var list<array<string, mixed>> $categories */
/** @var list<array<string, mixed>> $featured */
$intro = setting('home_intro');
?>
<section class="hero">
    <div class="shell hero__grid">
        <div class="hero__copy">
            <p class="eyebrow eyebrow--light"><?= e(t('home.hero.eyebrow')) ?></p>
            <h1 class="hero__title"><?= e(t('home.hero.title')) ?></h1>
            <p class="hero__lead"><?= e(t('home.hero.lead')) ?></p>
            <div class="hero__actions">
                <a class="btn btn--light" href="<?= e(lang_url('/equipment')) ?>"><?= e(t('action.browse')) ?></a>
                <a class="btn btn--outline-light" href="<?= e(lang_url('/contact')) ?>"><?= e(t('action.quote')) ?></a>
            </div>
            <p class="hero__note"><span class="dot"></span><?= e(t('home.hero.note')) ?></p>
        </div>

        <figure class="hero__figure">
            <div class="hero__media">
                <img src="<?= e(asset('/assets/img/hero.jpeg')) ?>" alt="<?= e(t('home.hero.alt')) ?>" width="1600" height="1200">
            </div>
            <figcaption class="hero__caption">
                <span class="mono">01</span>
                <span><?= e(t('home.hero.caption')) ?></span>
            </figcaption>
        </figure>
    </div>
</section>

<div class="factbar">
    <div class="shell factbar__grid">
        <div class="fact"><span class="mono">01</span><span><?= e(t('home.fact1')) ?></span></div>
        <div class="fact"><span class="mono">02</span><span><?= e(t('home.fact2')) ?></span></div>
        <div class="fact"><span class="mono">03</span><span><?= e(t('home.fact3', ['city' => setting_city()])) ?></span></div>
        <div class="fact"><span class="mono">04</span><span><?= e(t('home.fact4')) ?></span></div>
    </div>
</div>

<section class="section">
    <div class="shell">
        <header class="section-head">
            <div class="section-head__top">
                <p class="eyebrow"><?= e(t('home.categories.eyebrow')) ?></p>
                <a class="link-arrow" href="<?= e(lang_url('/equipment')) ?>"><?= e(t('action.view_all')) ?>
                    <svg class="icon-arrow" viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8h9M8.5 3.5 13 8l-4.5 4.5" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
                </a>
            </div>
            <div class="section-head__row">
                <h2><?= e(t('home.categories.title')) ?></h2>
                <p class="section-head__lead"><?= e(t('home.categories.lead')) ?></p>
            </div>
        </header>

        <div class="category-grid">
            <?php foreach ($categories as $index => $category): ?>
                <a class="category-card" href="<?= e(lang_url('/equipment?category=' . urlencode((string) $category['slug']))) ?>">
                    <span class="category-card__media">
                        <img src="<?= e(image_url((string) $category['image'])) ?>" alt="<?= e(bi($category, 'name')) ?>" loading="lazy" width="1200" height="900">
                    </span>
                    <span class="category-card__body">
                        <span class="category-card__index mono"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                        <span class="category-card__name"><?= e(bi($category, 'name')) ?></span>
                        <span class="category-card__summary"><?= e(bi($category, 'summary')) ?></span>
                        <span class="category-card__link"><?= e(t('action.view_equipment')) ?>
                            <svg class="icon-arrow" viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8h9M8.5 3.5 13 8l-4.5 4.5" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
                        </span>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--tint">
    <div class="shell">
        <header class="section-head">
            <div class="section-head__top">
                <p class="eyebrow"><?= e(t('home.featured.eyebrow')) ?></p>
                <a class="link-arrow" href="<?= e(lang_url('/equipment')) ?>"><?= e(t('home.featured.link')) ?>
                    <svg class="icon-arrow" viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8h9M8.5 3.5 13 8l-4.5 4.5" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
                </a>
            </div>
            <div class="section-head__row">
                <h2><?= e(t('home.featured.title')) ?></h2>
                <p class="section-head__lead"><?= e(t('home.featured.lead')) ?></p>
            </div>
        </header>

        <div class="product-grid product-grid--four">
            <?php foreach ($featured as $product): ?>
                <?php $item = $product; require APP_ROOT . '/app/views/public/partials/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="shell split">
        <div class="split__intro">
            <p class="eyebrow"><?= e(t('home.about.eyebrow')) ?></p>
            <h2><?= e(t('home.about.title')) ?></h2>
            <a class="link-arrow" href="<?= e(lang_url('/about')) ?>"><?= e(t('home.about.link')) ?>
                <svg class="icon-arrow" viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8h9M8.5 3.5 13 8l-4.5 4.5" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
            </a>
        </div>
        <div class="split__body">
            <p class="lead"><?= nl2br(e($intro)) ?></p>
            <ul class="principle-list">
                <li>
                    <span class="mono">01</span>
                    <div>
                        <h3><?= e(t('home.principle1.title')) ?></h3>
                        <p><?= e(t('home.principle1.body')) ?></p>
                    </div>
                </li>
                <li>
                    <span class="mono">02</span>
                    <div>
                        <h3><?= e(t('home.principle2.title')) ?></h3>
                        <p><?= e(t('home.principle2.body')) ?></p>
                    </div>
                </li>
                <li>
                    <span class="mono">03</span>
                    <div>
                        <h3><?= e(t('home.principle3.title')) ?></h3>
                        <p><?= e(t('home.principle3.body', ['city' => setting_city()])) ?></p>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</section>

<section class="section section--line">
    <div class="shell">
        <header class="section-head">
            <div class="section-head__top">
                <p class="eyebrow"><?= e(t('home.supply.eyebrow')) ?></p>
                <a class="link-arrow" href="<?= e(lang_url('/supply')) ?>"><?= e(t('nav.supply')) ?>
                    <svg class="icon-arrow" viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8h9M8.5 3.5 13 8l-4.5 4.5" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
                </a>
            </div>
            <div class="section-head__row">
                <h2><?= e(t('home.supply.title')) ?></h2>
                <p class="section-head__lead"><?= e(t('home.supply.lead')) ?></p>
            </div>
        </header>

        <ol class="process">
            <li class="process__step">
                <span class="process__num mono">01</span>
                <h3><?= e(t('home.step1.title')) ?></h3>
                <p><?= e(t('home.step1.body')) ?></p>
            </li>
            <li class="process__step">
                <span class="process__num mono">02</span>
                <h3><?= e(t('home.step2.title')) ?></h3>
                <p><?= e(t('home.step2.body')) ?></p>
            </li>
            <li class="process__step">
                <span class="process__num mono">03</span>
                <h3><?= e(t('home.step3.title')) ?></h3>
                <p><?= e(t('home.step3.body')) ?></p>
            </li>
        </ol>
    </div>
</section>

<section class="section section--tint" id="faq">
    <div class="shell">
        <header class="section-head">
            <div class="section-head__top">
                <p class="eyebrow"><?= e(t('faq.eyebrow')) ?></p>
                <a class="link-arrow" href="<?= e(lang_url('/terms')) ?>"><?= e(t('nav.terms')) ?>
                    <svg class="icon-arrow" viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8h9M8.5 3.5 13 8l-4.5 4.5" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
                </a>
            </div>
            <div class="section-head__row">
                <h2><?= e(t('faq.title')) ?></h2>
                <p class="section-head__lead"><?= e(t('faq.lead')) ?></p>
            </div>
        </header>

        <div class="faq-list">
            <?php foreach (($faqs ?? []) as $index => $faq): ?>
                <details class="faq-item"<?= $index === 0 ? ' open' : '' ?>>
                    <summary><?= e($faq['question']) ?></summary>
                    <p><?= e($faq['answer']) ?></p>
                </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="cta">
    <div class="shell cta__inner">
        <div>
            <p class="eyebrow eyebrow--light"><?= e(t('home.cta.eyebrow')) ?></p>
            <h2><?= e(t('home.cta.title')) ?></h2>
            <p class="cta__lead"><?= e(t('home.cta.body')) ?></p>
        </div>
        <div class="cta__actions">
            <a class="btn btn--light" href="<?= e(lang_url('/equipment')) ?>"><?= e(t('action.browse')) ?></a>
            <a class="btn btn--outline-light" href="<?= e(lang_url('/cart')) ?>"><?= e(t('action.view_cart')) ?></a>
        </div>
    </div>
</section>
