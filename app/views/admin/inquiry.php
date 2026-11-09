<?php
declare(strict_types=1);

/** @var array<string, mixed> $inquiry */
?>
<div class="admin-back">
    <a class="table-link" href="/admin/inquiries">← Back to requests</a>
</div>

<div class="order-head">
    <div>
        <p class="order-head__ref mono">Quotation request</p>
        <h2 class="order-head__name"><?= e($inquiry['name']) ?></h2>
        <p class="order-head__meta">
            <?= $inquiry['organization'] !== null && $inquiry['organization'] !== '' ? e($inquiry['organization']) . ' · ' : '' ?>
            <?= e(format_date($inquiry['created_at'], 'j M Y, H:i')) ?>
            · <?= $inquiry['lang'] === 'ar' ? 'Arabic' : 'English' ?>
        </p>
    </div>
    <span class="pill pill--<?= e($inquiry['status']) ?>"><?= e(inquiry_statuses()[$inquiry['status']] ?? $inquiry['status']) ?></span>
</div>

<div class="order-grid">
    <section class="panel">
        <header class="panel__head">
            <h2>Contact details</h2>
        </header>
        <dl class="detail-list">
            <div><dt>Phone</dt><dd><a href="<?= e(tel_href($inquiry['phone'])) ?>" dir="ltr"><?= e($inquiry['phone']) ?></a></dd></div>
            <div><dt>Email</dt><dd><a href="mailto:<?= e($inquiry['email']) ?>" dir="ltr"><?= e($inquiry['email']) ?></a></dd></div>
            <div><dt>Organization</dt><dd><?= $inquiry['organization'] !== null && $inquiry['organization'] !== '' ? e($inquiry['organization']) : '—' ?></dd></div>
            <div><dt>Equipment</dt><dd><?= e($inquiry['equipment_interest']) ?></dd></div>
            <?php if (!empty($inquiry['product_slug'])): ?>
                <div>
                    <dt>Catalog item</dt>
                    <dd><a href="/<?= e($inquiry['lang'] === 'ar' ? 'ar' : 'en') ?>/equipment/<?= e(rawurlencode((string) $inquiry['product_slug'])) ?>" target="_blank" rel="noopener"><?= e($inquiry['product_name_en'] ?: $inquiry['product_name_ar']) ?> ↗</a></dd>
                </div>
            <?php endif; ?>
        </dl>
    </section>

    <section class="panel">
        <header class="panel__head">
            <h2>Status</h2>
        </header>
        <form class="status-form" method="post" action="/admin/inquiries/<?= (int) $inquiry['id'] ?>/status">
            <?= csrf_field() ?>
            <div class="field">
                <label for="status">Request status</label>
                <select id="status" name="status">
                    <?php foreach (inquiry_statuses() as $key => $label): ?>
                        <option value="<?= e($key) ?>"<?= $inquiry['status'] === $key ? ' selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn btn--primary btn--sm" type="submit">Update status</button>
            <p class="field__help">Opening a new request marks it as read.</p>
        </form>
    </section>
</div>

<section class="panel">
    <header class="panel__head">
        <h2>Message</h2>
    </header>
    <div class="order-note">
        <p dir="auto"><?= nl2br(e($inquiry['message'])) ?></p>
    </div>
</section>
