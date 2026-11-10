<?php
declare(strict_types=1);
?>
<section class="page-head page-head--error">
    <div class="wrap page-head__inner">
        <p class="eyebrow">413</p>
        <h1><?= e(t('error.413.title')) ?></h1>
        <p class="page-head__lead"><?= e(t('error.413.body')) ?></p>
        <a class="btn btn--primary" href="<?= e(lang_url('/contact')) ?>"><?= e(t('nav.contact')) ?></a>
    </div>
</section>
