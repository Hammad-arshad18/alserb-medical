<?php
declare(strict_types=1);

/** @var array<string, mixed> $order */
/** @var list<array<string, mixed>> $lines */
$publicLang = $order['lang'] === 'ar' ? 'ar' : 'en';
?>
<div class="admin-back">
    <a class="table-link" href="/admin/orders">← Back to orders</a>
</div>

<div class="order-head">
    <div>
        <p class="order-head__ref mono"><?= e($order['reference']) ?></p>
        <h2 class="order-head__name"><?= e($order['customer_name']) ?></h2>
        <p class="order-head__meta">
            <?= !empty($order['organization']) ? e($order['organization']) . ' · ' : '' ?>
            <?= e(format_date($order['created_at'], 'j M Y, H:i')) ?>
        </p>
    </div>
    <span class="status-pill status--<?= e($order['status']) ?>"><?= e(order_statuses()[$order['status']] ?? $order['status']) ?></span>
</div>

<div class="order-grid">
    <section class="panel">
        <header class="panel__head">
            <h2>Items</h2>
            <a class="panel__link" href="/<?= e($publicLang) ?>/order/<?= e($order['token']) ?>" target="_blank" rel="noopener">Customer view ↗</a>
        </header>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Department</th>
                        <th class="align-end">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lines as $line): ?>
                        <tr>
                            <td>
                                <span class="cell-strong"><?= e($line['name_en']) ?></span>
                            </td>
                            <td><?= e($line['category_en']) ?></td>
                            <td class="align-end mono"><?= (int) $line['quantity'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($order['notes'])): ?>
            <div class="order-note">
                <h3 class="order-note__title">Customer notes</h3>
                <p dir="auto"><?= nl2br(e($order['notes'])) ?></p>
            </div>
        <?php endif; ?>

        <div class="order-note">
            <h3 class="order-note__title">Tracking history</h3>
            <ol class="admin-timeline">
                <?php foreach ($history as $entry): ?>
                    <li class="admin-timeline__item">
                        <div class="admin-timeline__head">
                            <span class="status-pill status--<?= e($entry['status']) ?>"><?= e(order_statuses()[$entry['status']] ?? $entry['status']) ?></span>
                            <span class="admin-timeline__date mono"><?= e(format_date($entry['created_at'], 'j M Y, H:i')) ?></span>
                        </div>
                        <?php if (!empty($entry['note'])): ?>
                            <p class="admin-timeline__note" dir="auto"><?= nl2br(e($entry['note'])) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($entry['internal_note'])): ?>
                            <p class="admin-timeline__internal" dir="auto"><strong>Internal:</strong> <?= nl2br(e($entry['internal_note'])) ?></p>
                        <?php endif; ?>
                        <p class="admin-timeline__meta">
                            <?= e(ucfirst((string) $entry['source'])) ?>
                            <?php if (!empty($entry['username'])): ?> · <?= e($entry['username']) ?><?php endif; ?>
                        </p>
                    </li>
                <?php endforeach; ?>
                <?php if ($history === []): ?>
                    <li class="panel__empty">No history yet.</li>
                <?php endif; ?>
            </ol>
        </div>
    </section>

    <div class="order-side">
        <section class="panel">
            <header class="panel__head"><h2>Customer</h2></header>
            <dl class="detail-list">
                <div><dt>Name</dt><dd><?= e($order['customer_name']) ?></dd></div>
                <?php if (!empty($order['organization'])): ?>
                    <div><dt>Organization</dt><dd><?= e($order['organization']) ?></dd></div>
                <?php endif; ?>
                <div><dt>Phone</dt><dd><a href="<?= e(tel_href((string) $order['phone'])) ?>" dir="ltr"><?= e($order['phone']) ?></a></dd></div>
                <div><dt>Email</dt><dd><a href="mailto:<?= e($order['email']) ?>" dir="ltr"><?= e($order['email']) ?></a></dd></div>
            </dl>
        </section>

        <section class="panel">
            <header class="panel__head"><h2>Delivery</h2></header>
            <dl class="detail-list">
                <div><dt>City</dt><dd><?= e($order['city']) ?></dd></div>
                <div><dt>Address</dt><dd><?= e($order['address']) ?></dd></div>
            </dl>
        </section>

        <section class="panel">
            <header class="panel__head"><h2>Status</h2></header>
            <form class="status-form" method="post" action="/admin/orders/<?= (int) $order['id'] ?>/status">
                <?= csrf_field() ?>
                <div class="field">
                    <label for="status">Order status</label>
                    <select id="status" name="status">
                        <?php foreach (order_statuses() as $key => $label): ?>
                            <option value="<?= e($key) ?>"<?= $order['status'] === $key ? ' selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="customer_note">Customer update</label>
                    <textarea id="customer_note" name="customer_note" rows="3" maxlength="1000" placeholder="Shown on the customer tracking page and in the status email."></textarea>
                    <p class="field__help">Visible to the customer in the order history and email.</p>
                </div>
                <div class="field">
                    <label for="admin_note">Internal note</label>
                    <textarea id="admin_note" name="admin_note" rows="4" maxlength="2000"><?= e($order['admin_note'] ?? '') ?></textarea>
                    <p class="field__help">Only visible to staff.</p>
                </div>
                <button class="btn btn--primary btn--sm" type="submit">Update order</button>
            </form>
            <p class="field__help">Last updated <?= e(format_date($order['updated_at'], 'j M Y, H:i')) ?>.</p>
        </section>
    </div>
</div>
