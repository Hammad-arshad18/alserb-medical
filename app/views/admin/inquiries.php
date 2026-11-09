<?php
declare(strict_types=1);

/** @var list<array<string, mixed>> $inquiries */
/** @var string $query */
?>
<div class="toolbar">
    <p class="toolbar__meta"><?= count($inquiries) ?> quotation requests · newest first</p>
    <form class="admin-search" method="get" action="/admin/inquiries" role="search">
        <label class="sr-only" for="inquiry-search">Search requests</label>
        <input type="search" id="inquiry-search" name="q" value="<?= e($query) ?>" placeholder="Search name, phone, equipment…">
        <button type="submit">Search</button>
    </form>
</div>

<?php if ($query !== ''): ?>
    <p class="admin-search-note">Results for “<?= e($query) ?>” · <a href="/admin/inquiries">Clear search</a></p>
<?php endif; ?>

<section class="panel">
    <?php if ($inquiries === []): ?>
        <p class="panel__empty">No quotation requests yet. Submissions from the public contact form appear here.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Received</th>
                        <th>Contact</th>
                        <th>Equipment</th>
                        <th>Language</th>
                        <th>Status</th>
                        <th class="align-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inquiries as $inquiry): ?>
                        <tr>
                            <td class="nowrap"><?= e(format_date($inquiry['created_at'], 'j M Y')) ?><span class="cell-sub"><?= e(format_date($inquiry['created_at'], 'H:i')) ?></span></td>
                            <td>
                                <span class="cell-strong"><?= e($inquiry['name']) ?></span>
                                <?php if (!empty($inquiry['organization'])): ?>
                                    <span class="cell-sub"><?= e($inquiry['organization']) ?></span>
                                <?php endif; ?>
                                <span class="cell-sub" dir="ltr"><?= e($inquiry['phone']) ?></span>
                            </td>
                            <td><?= e($inquiry['equipment_interest']) ?></td>
                            <td><span class="pill pill--neutral"><?= $inquiry['lang'] === 'ar' ? 'Arabic' : 'English' ?></span></td>
                            <td><span class="pill pill--<?= e($inquiry['status']) ?>"><?= e(inquiry_statuses()[$inquiry['status']] ?? $inquiry['status']) ?></span></td>
                            <td class="align-end"><a class="table-link" href="/admin/inquiries/<?= (int) $inquiry['id'] ?>">Open</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
