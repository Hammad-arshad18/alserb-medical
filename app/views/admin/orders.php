<?php
declare(strict_types=1);

/** @var list<array<string, mixed>> $orders */
/** @var string $statusFilter */
/** @var array<string, int> $statusCounts */
/** @var string $query */
$total = array_sum($statusCounts);
$querySuffix = $query !== '' ? '&q=' . urlencode($query) : '';
?>
<div class="toolbar">
    <p class="toolbar__meta"><?= $total ?> orders · newest first</p>
    <form class="admin-search" method="get" action="/admin/orders" role="search">
        <?php if ($statusFilter !== ''): ?>
            <input type="hidden" name="status" value="<?= e($statusFilter) ?>">
        <?php endif; ?>
        <label class="sr-only" for="order-search">Search orders</label>
        <input type="search" id="order-search" name="q" value="<?= e($query) ?>" placeholder="Search reference, customer, phone…">
        <button type="submit">Search</button>
    </form>
</div>

<div class="filter-tabs">
    <a class="filter-tab<?= $statusFilter === '' ? ' is-active' : '' ?>" href="/admin/orders<?= $query !== '' ? '?q=' . urlencode($query) : '' ?>">All <span><?= $total ?></span></a>
    <?php foreach (order_statuses() as $key => $label): ?>
        <a class="filter-tab<?= $statusFilter === $key ? ' is-active' : '' ?>" href="/admin/orders?status=<?= e($key) ?><?= e($querySuffix) ?>"><?= e($label) ?> <span><?= (int) ($statusCounts[$key] ?? 0) ?></span></a>
    <?php endforeach; ?>
</div>

<?php if ($query !== ''): ?>
    <p class="admin-search-note">Results for “<?= e($query) ?>” · <a href="/admin/orders<?= $statusFilter !== '' ? '?status=' . urlencode($statusFilter) : '' ?>">Clear search</a></p>
<?php endif; ?>

<section class="panel">
    <?php if ($orders === []): ?>
        <p class="panel__empty">No orders in this view.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th class="align-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>
                                <span class="cell-strong mono"><?= e($order['reference']) ?></span>
                                <span class="cell-sub"><?= e(format_date($order['created_at'], 'j M Y, H:i')) ?></span>
                            </td>
                            <td>
                                <span class="cell-strong"><?= e($order['customer_name']) ?></span>
                                <?php if (!empty($order['organization'])): ?>
                                    <span class="cell-sub"><?= e($order['organization']) ?></span>
                                <?php endif; ?>
                                <span class="cell-sub" dir="ltr"><?= e($order['phone']) ?></span>
                            </td>
                            <td>
                                <span class="cell-strong"><?= (int) $order['unit_count'] ?> units</span>
                                <span class="cell-sub"><?= (int) $order['line_count'] ?> lines</span>
                            </td>
                            <td><span class="status-pill status--<?= e($order['status']) ?>"><?= e(order_statuses()[$order['status']] ?? $order['status']) ?></span></td>
                            <td class="align-end"><a class="table-link" href="/admin/orders/<?= (int) $order['id'] ?>">Open</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
