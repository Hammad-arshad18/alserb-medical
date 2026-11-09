<?php
declare(strict_types=1);

/** @var list<array<string, mixed>> $products */
/** @var string $query */
$activeCount = count(array_filter($products, static fn (array $p): bool => (int) $p['is_active'] === 1));
?>
<div class="toolbar">
    <p class="toolbar__meta"><?= count($products) ?> items · <?= $activeCount ?> active</p>
    <div class="admin-toolbar-actions">
        <form class="admin-search" method="get" action="/admin/products" role="search">
            <label class="sr-only" for="product-search">Search equipment</label>
            <input type="search" id="product-search" name="q" value="<?= e($query) ?>" placeholder="Search equipment…">
            <button type="submit">Search</button>
        </form>
        <a class="btn btn--primary btn--sm" href="/admin/products/new">Add equipment</a>
    </div>
</div>

<?php if ($query !== ''): ?>
    <p class="admin-search-note">Results for “<?= e($query) ?>” · <a href="/admin/products">Clear search</a></p>
<?php endif; ?>

<section class="panel">
    <?php if ($products === []): ?>
        <p class="panel__empty">No equipment yet. Add the first item to start the catalog.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Department</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th class="align-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <div class="cell-media">
                                    <img src="<?= e(image_url($product['image'] ?? null, (string) ($product['category_image'] ?? ''))) ?>" alt="" width="64" height="48">
                                    <div>
                                        <span class="cell-strong"><?= e($product['name_en']) ?></span>
                                        <span class="cell-sub" dir="rtl"><?= e($product['name_ar']) ?></span>
                                        <span class="cell-slug">/<?= e($product['slug']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td><?= e($product['category_name_en']) ?></td>
                            <td class="nowrap"><?= (int) $product['sort_order'] ?></td>
                            <td>
                                <span class="pill <?= (int) $product['is_active'] === 1 ? 'pill--active' : 'pill--hidden' ?>">
                                    <?= (int) $product['is_active'] === 1 ? 'Active' : 'Hidden' ?>
                                </span>
                            </td>
                            <td class="align-end">
                                <div class="row-actions">
                                    <a class="table-link" href="/admin/products/<?= (int) $product['id'] ?>/edit">Edit</a>
                                    <form method="post" action="/admin/products/<?= (int) $product['id'] ?>/toggle" data-confirm="<?= (int) $product['is_active'] === 1 ? 'Hide this item from the public catalog?' : 'Show this item on the public site?' ?>">
                                        <?= csrf_field() ?>
                                        <button class="table-link table-link--muted" type="submit"><?= (int) $product['is_active'] === 1 ? 'Hide' : 'Show' ?></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
