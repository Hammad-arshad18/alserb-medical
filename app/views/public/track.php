<?php
declare(strict_types=1);

/** @var string|null $error */
/** @var array<string, string> $old */
?>
<section class="page-head">
    <div class="shell">
        <p class="eyebrow"><?= e(t('track.eyebrow')) ?></p>
        <h1><?= e(t('track.title')) ?></h1>
        <p class="page-head__lead"><?= e(t('track.lead')) ?></p>
    </div>
</section>

<section class="section">
    <div class="shell track-layout">
        <div class="form-panel">
            <h2 class="form-panel__title"><?= e(t('track.form.title')) ?></h2>

            <?php if ($error !== null): ?>
                <p class="form-alert" role="alert"><?= e($error) ?></p>
            <?php endif; ?>

            <form class="form" method="post" action="<?= e(lang_url('/track')) ?>">
                <?= csrf_field() ?>
                <div class="form-grid">
                    <div class="field">
                        <label for="reference"><?= e(t('track.reference')) ?> <span class="req">*</span></label>
                        <input type="text" id="reference" name="reference" value="<?= e($old['reference']) ?>" placeholder="ALS-2026-0001" maxlength="32" dir="ltr" autocomplete="off" required>
                    </div>
                    <div class="field">
                        <label for="track_email"><?= e(t('track.email')) ?> <span class="req">*</span></label>
                        <input type="email" id="track_email" name="email" value="<?= e($old['email']) ?>" maxlength="160" dir="ltr" autocomplete="email" required>
                    </div>
                </div>
                <div class="form__footer">
                    <p class="form__privacy"><?= e(t('track.privacy')) ?></p>
                    <button class="btn btn--primary" type="submit"><?= e(t('track.submit')) ?></button>
                </div>
            </form>
        </div>

        <aside class="track-note">
            <p class="eyebrow"><?= e(t('track.help.title')) ?></p>
            <p><?= e(t('track.help.body')) ?></p>
            <a class="link-arrow" href="<?= e(lang_url('/contact')) ?>"><?= e(t('nav.contact')) ?>
                <svg class="icon-arrow" viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8h9M8.5 3.5 13 8l-4.5 4.5" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
            </a>
        </aside>
    </div>
</section>
