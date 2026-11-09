<?php
declare(strict_types=1);

/** @var int $retryAfter */
$retryAfter = $retryAfter ?? 60;
?>
<section class="page-head page-head--error">
    <div class="shell page-head__inner">
        <p class="eyebrow">429</p>
        <h1><?= e(t('error.429.title')) ?></h1>
        <p class="page-head__lead"><?= e(t('error.429.body')) ?></p>
        <p class="form__privacy"><?= e(t('error.429.retry', ['seconds' => (string) $retryAfter])) ?></p>
        <a class="btn btn--primary" href="<?= e(lang_url('/')) ?>"><?= e(t('error.404.back')) ?></a>
    </div>
</section>
