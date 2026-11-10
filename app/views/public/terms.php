<?php
declare(strict_types=1);
?>
<section class="page-head">
    <div class="shell">
        <p class="eyebrow"><?= e(t('terms.eyebrow')) ?></p>
        <h1><?= e(t('terms.title')) ?></h1>
        <p class="page-head__lead"><?= e(t('terms.lead')) ?></p>
    </div>
</section>

<section class="section">
    <div class="shell prose-block">
        <h2><?= e(t('terms.orders.title')) ?></h2>
        <p><?= e(t('terms.orders.body')) ?></p>

        <h2><?= e(t('terms.pricing.title')) ?></h2>
        <p><?= e(t('terms.pricing.body')) ?></p>

        <h2><?= e(t('terms.privacy.title')) ?></h2>
        <p><?= e(t('terms.privacy.body')) ?></p>

        <h2><?= e(t('terms.site.title')) ?></h2>
        <p><?= e(t('terms.site.body')) ?></p>

        <p class="prose-block__cta">
            <a class="btn btn--dark" href="<?= e(lang_url('/contact')) ?>"><?= e(t('nav.contact')) ?></a>
        </p>
    </div>
</section>
