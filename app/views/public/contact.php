<?php
declare(strict_types=1);

/** @var array<string, string> $errors */
/** @var array<string, string> $old */
/** @var array<string, mixed>|null $product */
/** @var bool $sent */

$phone = setting('phone');
$email = setting('email');
$whatsapp = setting('whatsapp');
$address = setting('address');

$errorFor = static fn (string $field): ?string => $errors[$field] ?? null;
?>
<section class="page-head">
    <div class="shell">
        <p class="eyebrow"><?= e(t('contact.eyebrow')) ?></p>
        <h1><?= e(t('contact.title')) ?></h1>
        <p class="page-head__lead"><?= e(t('contact.lead')) ?></p>
    </div>
</section>

<section class="section section--contact">
    <div class="shell contact-layout">
        <div class="contact-main">
            <?php if ($sent): ?>
                <div class="success-panel">
                    <span class="success-panel__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="m5 12.5 4.5 4.5L19 7.5" fill="none" stroke="currentColor" stroke-width="1.8"/></svg>
                    </span>
                    <h2><?= e(t('contact.form.success.title')) ?></h2>
                    <p><?= e(t('contact.form.success.body')) ?></p>
                    <a class="btn btn--ghost" href="<?= e(lang_url('/contact')) ?>"><?= e(t('contact.form.success.again')) ?></a>
                </div>
            <?php else: ?>
                <div class="form-panel">
                    <h2 class="form-panel__title"><?= e(t('contact.form.title')) ?></h2>

                    <?php if (isset($errors['product'])): ?>
                        <p class="form-alert" role="alert"><?= e($errors['product']) ?></p>
                    <?php endif; ?>

                    <?php if ($product !== null): ?>
                        <p class="form-context"><?= e(t('contact.form.regarding')) ?>: <strong><?= e(bi($product, 'name')) ?></strong></p>
                    <?php endif; ?>

                    <form class="form" method="post" action="<?= e(lang_url('/contact')) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="product_id" value="<?= e($product !== null ? (string) $product['id'] : '0') ?>">
                        <div class="hp" aria-hidden="true">
                            <label for="website">Website</label>
                            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="form-grid">
                            <div class="field<?= $errorFor('name') ? ' has-error' : '' ?>">
                                <label for="name"><?= e(t('contact.form.name')) ?> <span class="req">*</span></label>
                                <input type="text" id="name" name="name" value="<?= e($old['name']) ?>" maxlength="120" autocomplete="name" required<?= $errorFor('name') ? ' aria-invalid="true"' : '' ?>>
                                <?php if ($errorFor('name')): ?><p class="field__error"><?= e((string) $errorFor('name')) ?></p><?php endif; ?>
                            </div>

                            <div class="field<?= $errorFor('organization') ? ' has-error' : '' ?>">
                                <label for="organization"><?= e(t('contact.form.organization')) ?> <span class="hint"><?= e(t('contact.form.optional')) ?></span></label>
                                <input type="text" id="organization" name="organization" value="<?= e($old['organization']) ?>" maxlength="160" autocomplete="organization">
                                <?php if ($errorFor('organization')): ?><p class="field__error"><?= e((string) $errorFor('organization')) ?></p><?php endif; ?>
                            </div>

                            <div class="field<?= $errorFor('phone') ? ' has-error' : '' ?>">
                                <label for="phone"><?= e(t('contact.form.phone')) ?> <span class="req">*</span></label>
                                <input type="tel" id="phone" name="phone" value="<?= e($old['phone']) ?>" maxlength="40" dir="ltr" autocomplete="tel" required<?= $errorFor('phone') ? ' aria-invalid="true"' : '' ?>>
                                <?php if ($errorFor('phone')): ?><p class="field__error"><?= e((string) $errorFor('phone')) ?></p><?php endif; ?>
                            </div>

                            <div class="field<?= $errorFor('email') ? ' has-error' : '' ?>">
                                <label for="email"><?= e(t('contact.form.email')) ?> <span class="req">*</span></label>
                                <input type="email" id="email" name="email" value="<?= e($old['email']) ?>" maxlength="160" dir="ltr" autocomplete="email" required<?= $errorFor('email') ? ' aria-invalid="true"' : '' ?>>
                                <?php if ($errorFor('email')): ?><p class="field__error"><?= e((string) $errorFor('email')) ?></p><?php endif; ?>
                            </div>

                            <div class="field field--full<?= $errorFor('interest') ? ' has-error' : '' ?>">
                                <label for="equipment_interest"><?= e(t('contact.form.interest')) ?> <span class="req">*</span></label>
                                <input type="text" id="equipment_interest" name="equipment_interest" value="<?= e($old['equipment_interest']) ?>" maxlength="255" placeholder="<?= e(t('contact.form.interest_hint')) ?>" required<?= $errorFor('interest') ? ' aria-invalid="true"' : '' ?>>
                                <?php if ($errorFor('interest')): ?><p class="field__error"><?= e((string) $errorFor('interest')) ?></p><?php endif; ?>
                            </div>

                            <div class="field field--full<?= $errorFor('message') ? ' has-error' : '' ?>">
                                <label for="message"><?= e(t('contact.form.message')) ?> <span class="req">*</span></label>
                                <textarea id="message" name="message" rows="6" maxlength="4000" placeholder="<?= e(t('contact.form.message_hint')) ?>" required<?= $errorFor('message') ? ' aria-invalid="true"' : '' ?>><?= e($old['message']) ?></textarea>
                                <?php if ($errorFor('message')): ?><p class="field__error"><?= e((string) $errorFor('message')) ?></p><?php endif; ?>
                            </div>
                        </div>

                        <div class="form__footer">
                            <p class="form__privacy"><?= e(t('contact.form.privacy')) ?></p>
                            <button class="btn btn--primary" type="submit"><?= e(t('action.send')) ?></button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <aside class="contact-panel">
            <p class="eyebrow eyebrow--light"><?= e(t('contact.aside.title')) ?></p>
            <h2><?= e(t('contact.aside.heading')) ?></h2>

            <dl class="contact-details">
                <?php if ($phone !== ''): ?>
                    <div>
                        <dt><?= e(t('contact.aside.phone')) ?></dt>
                        <dd><a href="<?= e(tel_href($phone)) ?>" dir="ltr"><?= e($phone) ?></a></dd>
                    </div>
                <?php endif; ?>
                <?php if ($email !== ''): ?>
                    <div>
                        <dt><?= e(t('contact.aside.email')) ?></dt>
                        <dd><a href="mailto:<?= e($email) ?>" dir="ltr"><?= e($email) ?></a></dd>
                    </div>
                <?php endif; ?>
                <?php if ($whatsapp !== ''): ?>
                    <div>
                        <dt><?= e(t('contact.aside.whatsapp')) ?></dt>
                        <dd><a href="<?= e(whatsapp_href($whatsapp)) ?>" dir="ltr" rel="noopener" target="_blank"><?= e($whatsapp) ?></a></dd>
                    </div>
                <?php endif; ?>
                <?php if ($address !== ''): ?>
                    <div>
                        <dt><?= e(t('contact.aside.address')) ?></dt>
                        <dd><?= e($address) ?></dd>
                    </div>
                <?php endif; ?>
            </dl>

            <?php if ($whatsapp !== ''): ?>
                <a class="btn btn--light btn--block" href="<?= e(whatsapp_href($whatsapp)) ?>" rel="noopener" target="_blank"><?= e(t('contact.aside.whatsapp_cta')) ?></a>
            <?php endif; ?>

            <p class="contact-panel__note"><?= e(t('contact.aside.note')) ?></p>
        </aside>
    </div>
</section>
