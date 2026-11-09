<?php
declare(strict_types=1);
?>
<section class="page-head">
    <div class="shell">
        <p class="eyebrow"><?= e(t('supply.eyebrow')) ?></p>
        <h1><?= e(t('supply.title')) ?></h1>
        <p class="page-head__lead"><?= e(t('supply.lead')) ?></p>
    </div>
</section>

<section class="section">
    <div class="shell supply-steps">
        <ol class="supply-list">
            <li class="supply-step">
                <span class="supply-step__num mono">01</span>
                <div>
                    <h2><?= e(t('supply.step1.title')) ?></h2>
                    <p><?= e(t('supply.step1.body')) ?></p>
                </div>
            </li>
            <li class="supply-step">
                <span class="supply-step__num mono">02</span>
                <div>
                    <h2><?= e(t('supply.step2.title')) ?></h2>
                    <p><?= e(t('supply.step2.body')) ?></p>
                </div>
            </li>
            <li class="supply-step">
                <span class="supply-step__num mono">03</span>
                <div>
                    <h2><?= e(t('supply.step3.title')) ?></h2>
                    <p><?= e(t('supply.step3.body')) ?></p>
                </div>
            </li>
            <li class="supply-step">
                <span class="supply-step__num mono">04</span>
                <div>
                    <h2><?= e(t('supply.step4.title')) ?></h2>
                    <p><?= e(t('supply.step4.body')) ?></p>
                </div>
            </li>
        </ol>

        <aside class="supply-note">
            <p class="eyebrow"><?= e(t('supply.note.title')) ?></p>
            <p><?= e(t('supply.note.body')) ?></p>
            <a class="btn btn--outline btn--sm" href="<?= e(lang_url('/cart')) ?>"><?= e(t('action.view_cart')) ?></a>
        </aside>
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
            <a class="btn btn--outline-light" href="<?= e(lang_url('/contact')) ?>"><?= e(t('action.quote')) ?></a>
        </div>
    </div>
</section>
