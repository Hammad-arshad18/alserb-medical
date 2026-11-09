<?php
declare(strict_types=1);
?>
<section class="page-head page-head--error">
    <div class="wrap page-head__inner">
        <p class="eyebrow">404</p>
        <h1><?= e(t('error.404.title')) ?></h1>
        <p class="page-head__lead"><?= e(t('error.404.body')) ?></p>
        <a class="btn btn--primary" href="<?= e(lang_url('/')) ?>"><?= e(t('error.404.back')) ?></a>
    </div>
</section>
