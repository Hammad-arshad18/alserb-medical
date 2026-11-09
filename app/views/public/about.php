<?php
declare(strict_types=1);

$intro = setting('about_intro');
?>
<section class="page-head">
    <div class="shell">
        <p class="eyebrow"><?= e(t('about.eyebrow')) ?></p>
        <h1><?= e(t('about.title')) ?></h1>
        <p class="page-head__lead"><?= e(t('about.lead', ['city' => setting_city()])) ?></p>
    </div>
</section>

<section class="section">
    <div class="shell split">
        <div class="split__intro">
            <p class="eyebrow"><?= e(t('home.about.eyebrow')) ?></p>
            <h2><?= e(t('about.what.title')) ?></h2>
        </div>
        <div class="split__body">
            <p class="lead"><?= nl2br(e($intro)) ?></p>
            <p><?= e(t('about.what.body1')) ?></p>
            <p><?= e(t('about.what.body2')) ?></p>
        </div>
    </div>
</section>

<section class="section section--line">
    <div class="shell">
        <header class="section-head">
            <div class="section-head__top">
                <p class="eyebrow"><?= e(t('home.supply.eyebrow')) ?></p>
            </div>
            <div class="section-head__row">
                <h2><?= e(t('about.how.title')) ?></h2>
                <p class="section-head__lead"><?= e(t('about.how.lead')) ?></p>
            </div>
        </header>

        <ol class="process process--four">
            <li class="process__step">
                <span class="process__num mono">01</span>
                <h3><?= e(t('about.step1.title')) ?></h3>
                <p><?= e(t('about.step1.body')) ?></p>
            </li>
            <li class="process__step">
                <span class="process__num mono">02</span>
                <h3><?= e(t('about.step2.title')) ?></h3>
                <p><?= e(t('about.step2.body')) ?></p>
            </li>
            <li class="process__step">
                <span class="process__num mono">03</span>
                <h3><?= e(t('about.step3.title')) ?></h3>
                <p><?= e(t('about.step3.body')) ?></p>
            </li>
            <li class="process__step">
                <span class="process__num mono">04</span>
                <h3><?= e(t('about.step4.title')) ?></h3>
                <p><?= e(t('about.step4.body')) ?></p>
            </li>
        </ol>
    </div>
</section>

<section class="section">
    <div class="shell">
        <div class="callout">
            <p class="eyebrow"><?= e(t('about.documents.title')) ?></p>
            <p class="callout__body"><?= e(t('about.documents.body')) ?></p>
        </div>
    </div>
</section>

<section class="cta">
    <div class="shell cta__inner">
        <div>
            <p class="eyebrow eyebrow--light"><?= e(t('home.cta.eyebrow')) ?></p>
            <h2><?= e(t('about.cta.title')) ?></h2>
            <p class="cta__lead"><?= e(t('home.cta.body')) ?></p>
        </div>
        <div class="cta__actions">
            <a class="btn btn--light" href="<?= e(lang_url('/equipment')) ?>"><?= e(t('action.browse')) ?></a>
            <a class="btn btn--outline-light" href="<?= e(lang_url('/contact')) ?>"><?= e(t('action.quote')) ?></a>
        </div>
    </div>
</section>
