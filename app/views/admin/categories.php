<?php
declare(strict_types=1);

/** @var list<array<string, mixed>> $categories */
/** @var string $query */
?>
<div class="toolbar">
    <p class="toolbar__meta"><?= count($categories) ?> departments · shown in catalog order</p>
    <div class="admin-toolbar-actions">
        <form class="admin-search" method="get" action="/admin/categories" role="search">
            <label class="sr-only" for="category-search">Search departments</label>
            <input type="search" id="category-search" name="q" value="<?= e($query) ?>" placeholder="Search departments…">
            <button type="submit">Search</button>
        </form>
        <a class="btn btn--primary btn--sm" href="/admin/categories/new">Add department</a>
    </div>
</div>

<?php if ($query !== ''): ?>
    <p class="admin-search-note">Results for “<?= e($query) ?>” · <a href="/admin/categories">Clear search</a></p>
<?php endif; ?>

<section class="panel">
    <?php if ($categories === []): ?>
        <p class="panel__empty">No departments yet. Add one before creating equipment items.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Department</th>
                        <th>Items</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th class="align-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td>
                                <div class="cell-media">
                                    <img src="<?= e(image_url($category['image'] ?? null)) ?>" alt="" width="64" height="48">
                                    <div>
                                        <span class="cell-strong"><?= e($category['name_en']) ?></span>
                                        <span class="cell-sub" dir="rtl"><?= e($category['name_ar']) ?></span>
                                        <span class="cell-slug">/<?= e($category['slug']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td class="nowrap"><?= (int) $category['product_count'] ?></td>
                            <td class="nowrap"><?= (int) $category['sort_order'] ?></td>
                            <td>
                                <span class="pill <?= (int) $category['is_active'] === 1 ? 'pill--active' : 'pill--hidden' ?>">
                                    <?= (int) $category['is_active'] === 1 ? 'Active' : 'Hidden' ?>
                                </span>
                            </td>
                            <td class="align-end">
                                <div class="row-actions">
                                    <a class="table-link" href="/admin/categories/<?= (int) $category['id'] ?>/edit">Edit</a>
                                    <form method="post" action="/admin/categories/<?= (int) $category['id'] ?>/toggle" data-confirm="<?= (int) $category['is_active'] === 1 ? 'Hide this department and all of its equipment?' : 'Show this department and its equipment?' ?>">
                                        <?= csrf_field() ?>
                                        <button class="table-link table-link--muted" type="submit"><?= (int) $category['is_active'] === 1 ? 'Hide' : 'Show' ?></button>
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
