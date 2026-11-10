<?php
declare(strict_types=1);

/** @var array<string, mixed> $category */
/** @var array<string, string> $errors */

$isEdit = $category['id'] !== null;
$action = $isEdit ? '/admin/categories/' . (int) $category['id'] . '/edit' : '/admin/categories/new';
?>
<div class="admin-back">
    <a class="table-link" href="/admin/categories">← Back to departments</a>
</div>

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

<form class="admin-form" method="post" action="<?= e($action) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <section class="panel">
        <header class="panel__head">
            <h2>Content</h2>
            <p class="panel__hint">Paired Arabic and English fields. The department name and summary appear on the catalog pages.</p>
        </header>

        <div class="bilingual">
            <div class="field">
                <label for="name_en">Name <span class="field__lang">English</span></label>
                <input type="text" id="name_en" name="name_en" value="<?= e($category['name_en']) ?>" maxlength="160" dir="ltr" required>
                <?php if (isset($errors['name_en'])): ?><p class="field__error"><?= e($errors['name_en']) ?></p><?php endif; ?>
            </div>
            <div class="field">
                <label for="name_ar">الاسم <span class="field__lang" lang="ar" dir="rtl">العربية</span></label>
                <input type="text" id="name_ar" name="name_ar" value="<?= e($category['name_ar']) ?>" maxlength="160" dir="rtl" required>
                <?php if (isset($errors['name_ar'])): ?><p class="field__error"><?= e($errors['name_ar']) ?></p><?php endif; ?>
            </div>

            <div class="field">
                <label for="summary_en">Summary <span class="field__lang">English</span></label>
                <textarea id="summary_en" name="summary_en" rows="3" maxlength="400" dir="ltr"><?= e($category['summary_en']) ?></textarea>
            </div>
            <div class="field">
                <label for="summary_ar">الملخص <span class="field__lang" lang="ar" dir="rtl">العربية</span></label>
                <textarea id="summary_ar" name="summary_ar" rows="3" maxlength="400" dir="rtl"><?= e($category['summary_ar']) ?></textarea>
            </div>

            <div class="field">
                <label for="description_en">Description <span class="field__lang">English</span></label>
                <textarea id="description_en" name="description_en" rows="5" dir="ltr"><?= e($category['description_en']) ?></textarea>
            </div>
            <div class="field">
                <label for="description_ar">الوصف <span class="field__lang" lang="ar" dir="rtl">العربية</span></label>
                <textarea id="description_ar" name="description_ar" rows="5" dir="rtl"><?= e($category['description_ar']) ?></textarea>
            </div>
        </div>
    </section>

    <section class="panel">
        <header class="panel__head">
            <h2>Placement and image</h2>
            <p class="panel__hint">The public URL is generated automatically from the English name.</p>
        </header>

        <div class="form-grid">
            <div class="field">
                <label for="sort_order">Sort order</label>
                <input type="number" id="sort_order" name="sort_order" value="<?= (int) $category['sort_order'] ?>" step="1" dir="ltr">
                <p class="field__help">Lower numbers appear first on the home page and catalog.</p>
            </div>

            <div class="field field--check">
                <label class="check">
                    <input type="checkbox" name="is_active" value="1"<?= (int) $category['is_active'] === 1 ? ' checked' : '' ?>>
                    <span>Visible on the public site</span>
                </label>
                <p class="field__help">Hiding a department also hides all of its equipment.</p>
            </div>
        </div>

        <div class="media-field">
            <div class="media-field__preview">
                <?php if (!empty($category['image'])): ?>
                    <img src="<?= e(image_url((string) $category['image'])) ?>" alt="" width="240" height="180">
                <?php else: ?>
                    <div class="media-field__empty">No image</div>
                <?php endif; ?>
            </div>
            <div class="media-field__body">
                <label for="image">Replace image</label>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" data-image-input data-max-bytes="<?= upload_limit_bytes() ?>" data-max-dimension="1800">
                <p class="field__help">JPEG, PNG, or WebP up to <?= e(upload_limit_label()) ?>. Large photos are resized in your browser before upload. This photograph is used on the home page and as the fallback for items without their own image.</p>
                <?php if (isset($errors['image'])): ?><p class="field__error"><?= e($errors['image']) ?></p><?php endif; ?>
                <?php if (!empty($category['image'])): ?>
                    <label class="check check--small">
                        <input type="checkbox" name="remove_image" value="1">
                        <span>Remove the current image</span>
                    </label>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <div class="form-actions">
        <button class="btn btn--primary" type="submit"><?= $isEdit ? 'Save changes' : 'Create department' ?></button>
        <a class="btn btn--ghost" href="/admin/categories">Cancel</a>
    </div>
</form>
