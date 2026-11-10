<?php
declare(strict_types=1);

$metaTitle = $metaTitle ?? 'Admin';
$active = $active ?? '';
$isMinimal = !empty($minimal);
$success = flash('admin_success');
$error = flash('admin_error');
$user = admin_user();

$pendingOrders = 0;
$newInquiries = 0;
if (!$isMinimal && $user !== null) {
    try {
        $pendingOrders = (int) db()->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
        $newInquiries = (int) db()->query("SELECT COUNT(*) FROM inquiries WHERE status = 'new'")->fetchColumn();
    } catch (Throwable) {
        $pendingOrders = 0;
        $newInquiries = 0;
    }
}

$navItems = [
    ['key' => 'dashboard', 'href' => '/admin', 'label' => 'Dashboard'],
    ['key' => 'orders', 'href' => '/admin/orders', 'label' => 'Orders', 'badge' => $pendingOrders],
    ['key' => 'products', 'href' => '/admin/products', 'label' => 'Equipment'],
    ['key' => 'categories', 'href' => '/admin/categories', 'label' => 'Departments'],
    ['key' => 'inquiries', 'href' => '/admin/inquiries', 'label' => 'Quotation requests', 'badge' => $newInquiries],
    ['key' => 'settings', 'href' => '/admin/settings', 'label' => 'Settings'],
];
?>
<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($metaTitle) ?> · Alserb Medical Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="<?= e(asset('/assets/img/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="icon" href="<?= e(asset('/assets/img/favicon-32.png')) ?>" sizes="32x32" type="image/png">
    <link rel="apple-touch-icon" href="<?= e(asset('/assets/img/favicon-180.png')) ?>">
    <link rel="stylesheet" href="<?= e(asset('/assets/css/fonts.min.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('/assets/css/site.min.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('/assets/css/admin.min.css')) ?>">
    <script src="<?= e(asset('/assets/js/admin.js')) ?>" defer></script>
</head>
<body class="admin-body<?= $isMinimal ? ' admin-body--auth' : '' ?>">

<?php if ($isMinimal): ?>
    <main class="auth">
        <?php if ($success !== null): ?><div class="flash flash--success"><?= e($success) ?></div><?php endif; ?>
        <?php if ($error !== null): ?><div class="flash flash--error"><?= e($error) ?></div><?php endif; ?>
        <?= $content ?>
    </main>
<?php else: ?>
    <div class="admin-shell">
        <aside class="admin-side">
            <a class="admin-brand" href="/admin">
                <img class="admin-brand__lockup" src="<?= e(asset('/assets/img/logo-lockup.png')) ?>" alt="Alserb Medical" width="704" height="176">
                <span class="admin-brand__sub">ADMIN PANEL</span>
            </a>

            <nav class="admin-nav" aria-label="Admin">
                <?php foreach ($navItems as $item): ?>
                    <a class="admin-nav__link<?= $active === $item['key'] ? ' is-active' : '' ?>" href="<?= e($item['href']) ?>">
                        <span><?= e($item['label']) ?></span>
                        <?php if (!empty($item['badge'])): ?><span class="admin-nav__badge"><?= (int) $item['badge'] ?></span><?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="admin-side__foot">
                <a class="admin-side__link" href="/ar" target="_blank" rel="noopener">View public site ↗</a>
                <form method="post" action="/admin/logout">
                    <?= csrf_field() ?>
                    <button class="admin-side__link admin-side__link--button" type="submit">Sign out</button>
                </form>
            </div>
        </aside>

        <div class="admin-main">
            <header class="admin-top">
                <div>
                    <p class="admin-top__crumb">Alserb Medical / Admin</p>
                    <h1><?= e($metaTitle) ?></h1>
                </div>
                <div class="admin-top__meta">
                    <span class="admin-top__user"><?= e($user['username'] ?? '') ?></span>
                    <a class="admin-top__site" href="/ar" target="_blank" rel="noopener">Public site ↗</a>
                </div>
            </header>

            <?php if ($success !== null): ?><div class="flash flash--success"><?= e($success) ?></div><?php endif; ?>
            <?php if ($error !== null): ?><div class="flash flash--error"><?= e($error) ?></div><?php endif; ?>

            <div class="admin-content">
                <?= $content ?>
            </div>
        </div>
    </div>
<?php endif; ?>

</body>
</html>
