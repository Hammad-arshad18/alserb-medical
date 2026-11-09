<?php
declare(strict_types=1);

/** @var array<string, int> $counts */
/** @var list<array<string, mixed>> $recentOrders */
/** @var list<array<string, mixed>> $recent */
?>
<div class="stat-grid">
    <a class="stat" href="/admin/orders?status=pending">
        <span class="stat__label">Pending orders</span>
        <span class="stat__num"><?= (int) $counts['pending_orders'] ?></span>
        <span class="stat__meta">Awaiting review</span>
    </a>
    <a class="stat" href="/admin/orders">
        <span class="stat__label">All orders</span>
        <span class="stat__num"><?= (int) $counts['orders'] ?></span>
        <span class="stat__meta">Order requests received</span>
    </a>
    <a class="stat" href="/admin/products">
        <span class="stat__label">Equipment</span>
        <span class="stat__num"><?= (int) $counts['products'] ?></span>
        <span class="stat__meta"><?= (int) $counts['active_products'] ?> active on the site</span>
    </a>
    <a class="stat" href="/admin/inquiries">
        <span class="stat__label">New requests</span>
        <span class="stat__num"><?= (int) $counts['new_inquiries'] ?></span>
        <span class="stat__meta">Quotation messages</span>
    </a>
</div>

<section class="panel">
    <header class="panel__head">
        <h2>Recent orders</h2>
        <a class="panel__link" href="/admin/orders">View all →</a>
    </header>

    <?php if ($recentOrders === []): ?>
        <p class="panel__empty">No orders yet. Order requests sent from the public cart appear here.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td>
                                <span class="cell-strong mono"><?= e($order['reference']) ?></span>
                                <span class="cell-sub"><?= e(format_date($order['created_at'], 'j M Y')) ?></span>
                            </td>
                            <td>
                                <span class="cell-strong"><?= e($order['customer_name']) ?></span>
                                <?php if (!empty($order['organization'])): ?>
                                    <span class="cell-sub"><?= e($order['organization']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="mono"><?= (int) $order['unit_count'] ?></td>
                            <td><span class="status-pill status--<?= e($order['status']) ?>"><?= e(order_statuses()[$order['status']] ?? $order['status']) ?></span></td>
                            <td class="align-end"><a class="table-link" href="/admin/orders/<?= (int) $order['id'] ?>">Open</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="panel">
    <header class="panel__head">
        <h2>Recent quotation requests</h2>
        <a class="panel__link" href="/admin/inquiries">View all →</a>
    </header>

    <?php if ($recent === []): ?>
        <p class="panel__empty">No quotation messages yet.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Received</th>
                        <th>Name</th>
                        <th>Equipment</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent as $inquiry): ?>
                        <tr>
                            <td class="nowrap"><?= e(format_date($inquiry['created_at'], 'j M Y')) ?></td>
                            <td>
                                <span class="cell-strong"><?= e($inquiry['name']) ?></span>
                                <?php if (!empty($inquiry['organization'])): ?>
                                    <span class="cell-sub"><?= e($inquiry['organization']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($inquiry['equipment_interest']) ?></td>
                            <td><span class="pill pill--<?= e($inquiry['status']) ?>"><?= e(inquiry_statuses()[$inquiry['status']] ?? $inquiry['status']) ?></span></td>
                            <td class="align-end"><a class="table-link" href="/admin/inquiries/<?= (int) $inquiry['id'] ?>">Open</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="panel panel--split">
    <div>
        <h2 class="panel__title">Catalog</h2>
        <p class="panel__text">Add equipment, edit bilingual content, replace images, and control what appears on the public site.</p>
        <div class="button-row">
            <a class="btn btn--primary btn--sm" href="/admin/products/new">Add equipment</a>
            <a class="btn btn--ghost btn--sm" href="/admin/categories">Manage departments</a>
        </div>
    </div>
    <div>
        <h2 class="panel__title">Store settings</h2>
        <p class="panel__text">Phone, email, WhatsApp, address, and the introduction text on the home and about pages.</p>
        <div class="button-row">
            <a class="btn btn--ghost btn--sm" href="/admin/settings">Edit settings</a>
            <a class="btn btn--ghost btn--sm" href="/ar" target="_blank" rel="noopener">View site</a>
        </div>
    </div>
</section>
