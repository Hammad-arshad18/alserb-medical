<?php
declare(strict_types=1);

/** @var list<array<string, mixed>> $lines */
/** @var array<string, string> $errors */
/** @var array<string, string> $old */

$errorFor = static fn (string $field): ?string => $errors[$field] ?? null;
$count = 0;
foreach ($lines as $line) {
    $count += (int) $line['quantity'];
}
?>
<section class="page-head">
    <div class="shell">
        <p class="eyebrow"><?= e(t('checkout.title')) ?></p>
        <h1><?= e(t('checkout.title')) ?></h1>
        <p class="page-head__lead"><?= e(t('checkout.lead')) ?></p>
    </div>
</section>

<section class="section section--checkout">
    <div class="shell checkout-layout">
        <form class="checkout-form" method="post" action="<?= e(lang_url('/checkout')) ?>">
            <?= csrf_field() ?>
            <div class="hp" aria-hidden="true">
                <label for="website">Website</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="form-panel">
                <h2 class="form-panel__title"><?= e(t('checkout.form.title')) ?></h2>

                <div class="form-grid">
                    <div class="field<?= $errorFor('name') ? ' has-error' : '' ?>">
                        <label for="name"><?= e(t('contact.form.name')) ?> <span class="req">*</span></label>
                        <input type="text" id="name" name="name" value="<?= e($old['name']) ?>" maxlength="120" autocomplete="name" required>
                        <?php if ($errorFor('name')): ?><p class="field__error"><?= e((string) $errorFor('name')) ?></p><?php endif; ?>
                    </div>

                    <div class="field<?= $errorFor('organization') ? ' has-error' : '' ?>">
                        <label for="organization"><?= e(t('contact.form.organization')) ?> <span class="hint"><?= e(t('contact.form.optional')) ?></span></label>
                        <input type="text" id="organization" name="organization" value="<?= e($old['organization']) ?>" maxlength="160" autocomplete="organization">
                        <?php if ($errorFor('organization')): ?><p class="field__error"><?= e((string) $errorFor('organization')) ?></p><?php endif; ?>
                    </div>

                    <div class="field<?= $errorFor('phone') ? ' has-error' : '' ?>">
                        <label for="phone"><?= e(t('contact.form.phone')) ?> <span class="req">*</span></label>
                        <input type="tel" id="phone" name="phone" value="<?= e($old['phone']) ?>" maxlength="40" dir="ltr" autocomplete="tel" required>
                        <?php if ($errorFor('phone')): ?><p class="field__error"><?= e((string) $errorFor('phone')) ?></p><?php endif; ?>
                    </div>

                    <div class="field<?= $errorFor('email') ? ' has-error' : '' ?>">
                        <label for="email"><?= e(t('contact.form.email')) ?> <span class="req">*</span></label>
                        <input type="email" id="email" name="email" value="<?= e($old['email']) ?>" maxlength="160" dir="ltr" autocomplete="email" required>
                        <?php if ($errorFor('email')): ?><p class="field__error"><?= e((string) $errorFor('email')) ?></p><?php endif; ?>
                    </div>

                    <div class="field<?= $errorFor('city') ? ' has-error' : '' ?>">
                        <label for="city"><?= e(t('checkout.city')) ?> <span class="req">*</span></label>
                        <input type="text" id="city" name="city" value="<?= e($old['city']) ?>" maxlength="120" autocomplete="address-level2" required>
                        <?php if ($errorFor('city')): ?><p class="field__error"><?= e((string) $errorFor('city')) ?></p><?php endif; ?>
                    </div>

                    <div class="field field--full<?= $errorFor('address') ? ' has-error' : '' ?>">
                        <label for="address"><?= e(t('checkout.address')) ?> <span class="req">*</span></label>
                        <input type="text" id="address" name="address" value="<?= e($old['address']) ?>" maxlength="255" autocomplete="street-address" required>
                        <?php if ($errorFor('address')): ?><p class="field__error"><?= e((string) $errorFor('address')) ?></p><?php endif; ?>
                    </div>

                    <div class="field field--full<?= $errorFor('notes') ? ' has-error' : '' ?>">
                        <label for="notes"><?= e(t('checkout.notes')) ?> <span class="hint"><?= e(t('contact.form.optional')) ?></span></label>
                        <textarea id="notes" name="notes" rows="4" maxlength="2000" placeholder="<?= e(t('checkout.notes_hint')) ?>"><?= e($old['notes']) ?></textarea>
                        <?php if ($errorFor('notes')): ?><p class="field__error"><?= e((string) $errorFor('notes')) ?></p><?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="checkout-submit">
                <p class="checkout-submit__note"><?= e(t('checkout.note')) ?></p>
                <button class="btn btn--primary" type="submit"><?= e(t('checkout.submit')) ?></button>
            </div>
        </form>

        <aside class="summary summary--checkout">
            <h2 class="summary__title"><?= e(t('checkout.summary.title')) ?></h2>
            <ul class="checkout-lines">
                <?php foreach ($lines as $line): ?>
                    <li class="checkout-line">
                        <img src="<?= e(image_url($line['image'] ?? null, (string) ($line['category_image'] ?? ''))) ?>" alt="" width="64" height="64" loading="lazy">
                        <div>
                            <span class="checkout-line__name"><?= e(bi($line, 'name')) ?></span>
                            <span class="checkout-line__meta"><?= e(t('checkout.quantity')) ?>: <?= (int) $line['quantity'] ?></span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
            <dl class="summary__rows">
                <div>
                    <dt><?= e(t('cart.summary.items')) ?></dt>
                    <dd><?= $count ?></dd>
                </div>
                <div>
                    <dt><?= e(t('cart.summary.pricing')) ?></dt>
                    <dd><?= e(t('cart.summary.pricing_value')) ?></dd>
                </div>
            </dl>
            <p class="summary__note"><?= e(t('checkout.note')) ?></p>
            <a class="summary__continue" href="<?= e(lang_url('/cart')) ?>"><?= e(t('checkout.edit')) ?></a>
        </aside>
    </div>
</section>
