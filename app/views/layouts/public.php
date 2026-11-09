<?php
declare(strict_types=1);

$metaTitle = $metaTitle ?? null;
$metaDescription = $metaDescription ?? '';
$bodyClass = $bodyClass ?? '';
$pageTitle = $metaTitle !== null && $metaTitle !== ''
    ? $metaTitle . ' · ' . t('site.name')
    : t('site.name') . ' — ' . t('site.tagline');

$phone = setting('phone');
$email = setting('email');
$address = setting('address');
$cartCount = cart_count();
$cartMessage = flash('cart_success');
$cartError = flash('cart_error');

$navItems = [
    ['path' => '/', 'label' => t('nav.home')],
    ['path' => '/equipment', 'label' => t('nav.equipment')],
    ['path' => '/about', 'label' => t('nav.about')],
    ['path' => '/supply', 'label' => t('nav.supply')],
    ['path' => '/contact', 'label' => t('nav.contact')],
];
?>
<!doctype html>
<html lang="<?= e(current_lang()) ?>" dir="<?= is_rtl() ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($metaDescription) ?>">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($metaDescription) ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="<?= current_lang() === 'ar' ? 'ar_LY' : 'en_GB' ?>">
    <link rel="icon" href="<?= e(asset('/assets/img/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="icon" href="<?= e(asset('/assets/img/favicon-32.png')) ?>" sizes="32x32" type="image/png">
    <link rel="apple-touch-icon" href="<?= e(asset('/assets/img/favicon-180.png')) ?>">
    <link rel="stylesheet" href="<?= e(asset('/assets/css/fonts.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('/assets/css/site.css')) ?>">
    <script src="<?= e(asset('/assets/js/site.js')) ?>" defer></script>
</head>
<body class="<?= e($bodyClass) ?>">
<a class="skip-link" href="#main"><?= is_rtl() ? 'تجاوز إلى المحتوى' : 'Skip to content' ?></a>

<div class="utility">
    <div class="shell utility__inner">
        <p class="utility__note"><?= e(t('top.notice')) ?></p>
        <div class="utility__links">
            <?php if ($phone !== ''): ?>
                <a href="<?= e(tel_href($phone)) ?>" dir="ltr"><?= e($phone) ?></a>
            <?php endif; ?>
            <?php if ($email !== ''): ?>
                <a href="mailto:<?= e($email) ?>" dir="ltr"><?= e($email) ?></a>
            <?php endif; ?>
            <a class="utility__lang" href="<?= e(switch_lang_url()) ?>" hreflang="<?= e(other_lang()) ?>" lang="<?= e(other_lang()) ?>" dir="<?= other_lang() === 'ar' ? 'rtl' : 'ltr' ?>" aria-label="<?= e(t('lang.switch_label')) ?>"><?= e(t('lang.switch')) ?></a>
        </div>
    </div>
</div>

<header class="site-header">
    <div class="shell header__inner">
        <a class="brand" href="<?= e(lang_url('/')) ?>" aria-label="<?= e(t('site.name')) ?>">
            <img class="brand__lockup" src="<?= e(asset('/assets/img/logo-lockup.png')) ?>" alt="" width="704" height="176">
            <span class="brand__ar">السرب الطبي</span>
        </a>

        <nav class="nav" id="site-nav" aria-label="<?= is_rtl() ? 'التنقل الرئيسي' : 'Primary navigation' ?>">
            <?php foreach ($navItems as $item): ?>
                <a class="nav__link<?= nav_is($item['path']) ? ' is-active' : '' ?>" href="<?= e(lang_url($item['path'])) ?>"<?= nav_is($item['path']) ? ' aria-current="page"' : '' ?>><?= e($item['label']) ?></a>
            <?php endforeach; ?>
            <a class="nav__cart" href="<?= e(lang_url('/cart')) ?>">
                <span><?= e(t('nav.cart')) ?></span>
                <?php if ($cartCount > 0): ?><span class="cart-count"><?= $cartCount ?></span><?php endif; ?>
            </a>
        </nav>

        <div class="header__actions">
            <a class="cart-link" href="<?= e(lang_url('/cart')) ?>" aria-label="<?= e(t('nav.cart')) ?>">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 8h12l1 12H5L6 8Z" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M9 8V6a3 3 0 0 1 6 0v2" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
                <?php if ($cartCount > 0): ?><span class="cart-count"><?= $cartCount ?></span><?php endif; ?>
            </a>
            <a class="btn btn--dark btn--sm header__quote" href="<?= e(lang_url('/contact')) ?>"><?= e(t('action.quote')) ?></a>
            <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav" data-nav-toggle>
                <span class="nav-toggle__bar"></span>
                <span class="nav-toggle__bar"></span>
                <span class="sr-only"><?= e(t('nav.menu')) ?></span>
            </button>
        </div>
    </div>
</header>

<?php if ($cartMessage !== null || $cartError !== null): ?>
    <div class="notice-stack">
        <?php if ($cartMessage !== null): ?>
            <p class="notice notice--success" role="status"><?= e($cartMessage) ?></p>
        <?php endif; ?>
        <?php if ($cartError !== null): ?>
            <p class="notice notice--error" role="alert"><?= e($cartError) ?></p>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="snackbar" id="snackbar" role="status" aria-live="polite" hidden></div>

<main id="main">
<?= $content ?>
</main>

<footer class="site-footer">
    <div class="shell">
        <div class="footer__track">
            <div class="footer__track-copy">
                <p class="footer__title"><?= e(t('footer.track.title')) ?></p>
                <p><?= e(t('footer.track.lead')) ?></p>
            </div>
            <form class="footer-track-form" method="post" action="<?= e(lang_url('/track')) ?>">
                <?= csrf_field() ?>
                <label class="sr-only" for="footer_reference"><?= e(t('track.reference')) ?></label>
                <input type="text" id="footer_reference" name="reference" placeholder="<?= e(t('track.reference')) ?>" maxlength="32" dir="ltr" autocomplete="off" required>
                <label class="sr-only" for="footer_email"><?= e(t('track.email')) ?></label>
                <input type="email" id="footer_email" name="email" placeholder="<?= e(t('track.email_short')) ?>" maxlength="160" dir="ltr" autocomplete="email" required>
                <button class="btn btn--light btn--sm" type="submit"><?= e(t('track.submit')) ?></button>
            </form>
        </div>

        <div class="footer__top">
            <div class="footer__identity">
                <a class="brand brand--footer" href="<?= e(lang_url('/')) ?>" aria-label="<?= e(t('site.name')) ?>">
                    <img class="footer__logo" src="<?= e(asset('/assets/img/logo-full-white.png')) ?>" alt="Alserb Medical" width="480" height="410">
                </a>
                <p class="footer__about"><?= e(t('footer.about')) ?></p>
            </div>

            <div class="footer__col">
                <h2 class="footer__title"><?= e(t('footer.explore')) ?></h2>
                <ul class="footer__list">
                    <li><a href="<?= e(lang_url('/equipment')) ?>"><?= e(t('nav.equipment')) ?></a></li>
                    <li><a href="<?= e(lang_url('/about')) ?>"><?= e(t('nav.about')) ?></a></li>
                    <li><a href="<?= e(lang_url('/supply')) ?>"><?= e(t('nav.supply')) ?></a></li>
                    <li><a href="<?= e(lang_url('/credits')) ?>"><?= e(t('nav.credits')) ?></a></li>
                </ul>
            </div>

            <div class="footer__col">
                <h2 class="footer__title"><?= e(t('footer.contact')) ?></h2>
                <ul class="footer__list">
                    <?php if ($phone !== ''): ?>
                        <li><a href="<?= e(tel_href($phone)) ?>" dir="ltr"><?= e($phone) ?></a></li>
                    <?php endif; ?>
                    <?php if ($email !== ''): ?>
                        <li><a href="mailto:<?= e($email) ?>" dir="ltr"><?= e($email) ?></a></li>
                    <?php endif; ?>
                    <?php if ($address !== ''): ?>
                        <li class="footer__address"><?= e($address) ?></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="footer__base">
            <p><?= e(t('footer.legal', ['year' => date('Y')])) ?></p>
            <p><?= e(t('footer.note')) ?></p>
        </div>
    </div>
</footer>
</body>
</html>
