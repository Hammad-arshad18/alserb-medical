<?php
declare(strict_types=1);

/** @var array<string, array{value_ar: string, value_en: string}> $values */
/** @var array<string, string> $errors */
?>
<?php if ($errors !== []): ?>
    <div class="flash flash--error" role="alert">
        <strong>Please check the form.</strong>
        <ul class="flash__list">
            <?php foreach ($errors as $message): ?>
                <li><?= e($message) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form class="admin-form" method="post" action="/admin/settings">
    <?= csrf_field() ?>

    <section class="panel">
        <header class="panel__head">
            <h2>Contact details</h2>
            <p class="panel__hint">These values appear in the header, footer, and contact page. The seeded values are placeholders.</p>
        </header>

        <div class="form-grid">
            <div class="field<?= isset($errors['phone']) ? ' has-error' : '' ?>">
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" value="<?= e($values['phone']['value_en']) ?>" maxlength="40" dir="ltr" required>
                <?php if (isset($errors['phone'])): ?><p class="field__error"><?= e($errors['phone']) ?></p><?php endif; ?>
            </div>

            <div class="field<?= isset($errors['email']) ? ' has-error' : '' ?>">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= e($values['email']['value_en']) ?>" maxlength="160" dir="ltr" required>
                <?php if (isset($errors['email'])): ?><p class="field__error"><?= e($errors['email']) ?></p><?php endif; ?>
            </div>

            <div class="field">
                <label for="whatsapp">WhatsApp</label>
                <input type="text" id="whatsapp" name="whatsapp" value="<?= e($values['whatsapp']['value_en']) ?>" maxlength="40" dir="ltr">
                <p class="field__help">Include the country code, for example +218 91 000 0000.</p>
            </div>

            <div class="field<?= isset($errors['timezone']) ? ' has-error' : '' ?>">
                <label for="timezone">Timezone</label>
                <input type="text" id="timezone" name="timezone" value="<?= e($values['timezone']['value_en']) ?>" maxlength="64" dir="ltr" list="timezone-list" required>
                <datalist id="timezone-list">
                    <option value="Africa/Tripoli"></option>
                    <option value="Africa/Cairo"></option>
                    <option value="Africa/Tunis"></option>
                    <option value="Europe/London"></option>
                    <option value="UTC"></option>
                </datalist>
                <p class="field__help">A PHP timezone identifier. Times in the admin panel and history use it.</p>
                <?php if (isset($errors['timezone'])): ?><p class="field__error"><?= e($errors['timezone']) ?></p><?php endif; ?>
            </div>
        </div>
    </section>

    <section class="panel">
        <header class="panel__head">
            <h2>Address</h2>
            <p class="panel__hint">Shown on the contact page and in the footer.</p>
        </header>

        <div class="bilingual">
            <div class="field<?= isset($errors['address']) ? ' has-error' : '' ?>">
                <label for="address_en">Address <span class="field__lang">English</span></label>
                <input type="text" id="address_en" name="address_en" value="<?= e($values['address']['value_en']) ?>" maxlength="255" dir="ltr">
                <?php if (isset($errors['address'])): ?><p class="field__error"><?= e($errors['address']) ?></p><?php endif; ?>
            </div>
            <div class="field<?= isset($errors['address']) ? ' has-error' : '' ?>">
                <label for="address_ar">العنوان <span class="field__lang" lang="ar" dir="rtl">العربية</span></label>
                <input type="text" id="address_ar" name="address_ar" value="<?= e($values['address']['value_ar']) ?>" maxlength="255" dir="rtl">
                <?php if (isset($errors['address'])): ?><p class="field__error"><?= e($errors['address']) ?></p><?php endif; ?>
            </div>

            <div class="field<?= isset($errors['city']) ? ' has-error' : '' ?>">
                <label for="city_en">City <span class="field__lang">English</span></label>
                <input type="text" id="city_en" name="city_en" value="<?= e($values['city']['value_en']) ?>" maxlength="80" dir="ltr">
                <?php if (isset($errors['city'])): ?><p class="field__error"><?= e($errors['city']) ?></p><?php endif; ?>
            </div>
            <div class="field<?= isset($errors['city']) ? ' has-error' : '' ?>">
                <label for="city_ar">المدينة <span class="field__lang" lang="ar" dir="rtl">العربية</span></label>
                <input type="text" id="city_ar" name="city_ar" value="<?= e($values['city']['value_ar']) ?>" maxlength="80" dir="rtl">
                <?php if (isset($errors['city'])): ?><p class="field__error"><?= e($errors['city']) ?></p><?php endif; ?>
            </div>
        </div>
    </section>

    <section class="panel">
        <header class="panel__head">
            <h2>Home page introduction</h2>
            <p class="panel__hint">The short company text on the home page.</p>
        </header>

        <div class="bilingual">
            <div class="field<?= isset($errors['home_intro']) ? ' has-error' : '' ?>">
                <label for="home_intro_en">Introduction <span class="field__lang">English</span></label>
                <textarea id="home_intro_en" name="home_intro_en" rows="5" dir="ltr"><?= e($values['home_intro']['value_en']) ?></textarea>
                <?php if (isset($errors['home_intro'])): ?><p class="field__error"><?= e($errors['home_intro']) ?></p><?php endif; ?>
            </div>
            <div class="field<?= isset($errors['home_intro']) ? ' has-error' : '' ?>">
                <label for="home_intro_ar">المقدمة <span class="field__lang" lang="ar" dir="rtl">العربية</span></label>
                <textarea id="home_intro_ar" name="home_intro_ar" rows="5" dir="rtl"><?= e($values['home_intro']['value_ar']) ?></textarea>
                <?php if (isset($errors['home_intro'])): ?><p class="field__error"><?= e($errors['home_intro']) ?></p><?php endif; ?>
            </div>
        </div>
    </section>

    <section class="panel">
        <header class="panel__head">
            <h2>About page introduction</h2>
            <p class="panel__hint">The company paragraph at the top of the About page.</p>
        </header>

        <div class="bilingual">
            <div class="field<?= isset($errors['about_intro']) ? ' has-error' : '' ?>">
                <label for="about_intro_en">Introduction <span class="field__lang">English</span></label>
                <textarea id="about_intro_en" name="about_intro_en" rows="5" dir="ltr"><?= e($values['about_intro']['value_en']) ?></textarea>
                <?php if (isset($errors['about_intro'])): ?><p class="field__error"><?= e($errors['about_intro']) ?></p><?php endif; ?>
            </div>
            <div class="field<?= isset($errors['about_intro']) ? ' has-error' : '' ?>">
                <label for="about_intro_ar">المقدمة <span class="field__lang" lang="ar" dir="rtl">العربية</span></label>
                <textarea id="about_intro_ar" name="about_intro_ar" rows="5" dir="rtl"><?= e($values['about_intro']['value_ar']) ?></textarea>
                <?php if (isset($errors['about_intro'])): ?><p class="field__error"><?= e($errors['about_intro']) ?></p><?php endif; ?>
            </div>
        </div>
    </section>

    <div class="form-actions">
        <button class="btn btn--primary" type="submit">Save settings</button>
        <a class="btn btn--ghost" href="/admin">Cancel</a>
    </div>
</form>
