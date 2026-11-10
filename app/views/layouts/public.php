<?php
declare(strict_types=1);

$metaTitle = $metaTitle ?? null;
$metaDescription = $metaDescription ?? '';
$bodyClass = $bodyClass ?? '';
$metaKeywords = $metaKeywords ?? t('meta.keywords');
$metaRobots = $metaRobots ?? 'index, follow, max-image-preview:large';
$canonical = $canonical ?? canonical_url();
$ogImage = $ogImage ?? absolute_url(asset('/assets/img/og-default.jpg'));
$ogImageAlt = $ogImageAlt ?? t('site.name');
$ogImageWidth = $ogImageWidth ?? 1200;
$ogImageHeight = $ogImageHeight ?? 630;
$ogImageType = $ogImageType ?? 'image/jpeg';
$ogType = $ogType ?? 'website';
$twitterSite = trim((string) config('app.twitter', ''));
$jsonLd = $jsonLd ?? [];
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
    <meta name="keywords" content="<?= e($metaKeywords) ?>">
    <meta name="robots" content="<?= e($metaRobots) ?>">
    <meta name="author" content="<?= e(t('site.name')) ?>">
    <meta name="application-name" content="<?= e(t('site.name')) ?>">
    <meta name="theme-color" content="#041D33">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="<?= e(t('site.name')) ?>">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="geo.region" content="LY-BA">
    <meta name="geo.placename" content="<?= e(setting_city()) ?>">
    <link rel="canonical" href="<?= e($canonical) ?>">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="alternate" type="text/markdown" href="<?= e($canonical) ?>">
    <link rel="describedby" href="<?= e(app_url() . '/llms.txt') ?>" type="text/plain">
    <link rel="alternate" hreflang="ar" href="<?= e(alternate_url('ar')) ?>">
    <link rel="alternate" hreflang="en" href="<?= e(alternate_url('en')) ?>">
    <link rel="alternate" hreflang="x-default" href="<?= e(alternate_url('en')) ?>">
    <meta property="og:site_name" content="<?= e(t('site.name')) ?>">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($metaDescription) ?>">
    <meta property="og:type" content="<?= e($ogType) ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <meta property="og:locale" content="<?= current_lang() === 'ar' ? 'ar_LY' : 'en_GB' ?>">
    <meta property="og:locale:alternate" content="<?= current_lang() === 'ar' ? 'en_GB' : 'ar_LY' ?>">
    <meta property="og:image" content="<?= e($ogImage) ?>">
    <?php if (str_starts_with($ogImage, 'https://')): ?>
    <meta property="og:image:secure_url" content="<?= e($ogImage) ?>">
    <?php endif; ?>
    <meta property="og:image:type" content="<?= e($ogImageType) ?>">
    <meta property="og:image:width" content="<?= (int) $ogImageWidth ?>">
    <meta property="og:image:height" content="<?= (int) $ogImageHeight ?>">
    <meta property="og:image:alt" content="<?= e($ogImageAlt) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($pageTitle) ?>">
    <meta name="twitter:description" content="<?= e($metaDescription) ?>">
    <meta name="twitter:image" content="<?= e($ogImage) ?>">
    <meta name="twitter:image:alt" content="<?= e($ogImageAlt) ?>">
    <?php if ($twitterSite !== ''): ?>
    <meta name="twitter:site" content="<?= e($twitterSite) ?>">
    <meta name="twitter:creator" content="<?= e($twitterSite) ?>">
    <?php endif; ?>
    <link rel="icon" href="<?= e(asset('/assets/img/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="icon" href="<?= e(asset('/assets/img/favicon-32.png')) ?>" sizes="32x32" type="image/png">
    <link rel="apple-touch-icon" href="<?= e(asset('/assets/img/favicon-180.png')) ?>">
    <link rel="stylesheet" href="<?= e(asset('/assets/css/fonts.min.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('/assets/css/site.min.css')) ?>">
    <script src="<?= e(asset('/assets/js/site.js')) ?>" defer></script>
    <?php
    $siteUrl = app_url();
    $organizationId = $siteUrl . '/#organization';
    $sameAs = AgentReady::sameAsProfiles();
    $organization = [
        '@type' => ['MedicalBusiness', 'LocalBusiness', 'Organization'],
        '@id' => $organizationId,
        'name' => t('site.name'),
        'alternateName' => is_rtl() ? 'Alserb Medical' : 'السرب الطبي',
        'description' => t('meta.about'),
        'url' => $siteUrl . lang_url('/'),
        'logo' => absolute_url('/assets/img/logo-lockup.png'),
        'image' => $ogImage,
        'telephone' => $phone,
        'email' => $email,
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => $address,
            'addressLocality' => setting_city(),
            'addressCountry' => 'LY',
        ],
        'areaServed' => [
            '@type' => 'Country',
            'name' => is_rtl() ? 'ليبيا' : 'Libya',
        ],
        'knowsLanguage' => ['ar', 'en'],
        'contactPoint' => [
            '@type' => 'ContactPoint',
            'telephone' => $phone,
            'email' => $email,
            'contactType' => 'sales',
            'availableLanguage' => ['ar', 'en'],
            'areaServed' => 'LY',
        ],
    ];
    if ($sameAs !== []) {
        $organization['sameAs'] = $sameAs;
    }
    $graph = array_merge([
        $organization,
        [
            '@type' => 'WebSite',
            '@id' => $siteUrl . '/#website',
            'url' => $siteUrl,
            'name' => t('site.name'),
            'inLanguage' => ['ar', 'en'],
            'publisher' => ['@id' => $organizationId],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => $siteUrl . '/' . current_lang() . '/equipment?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ],
    ], $jsonLd);
    $schemaJson = json_encode(
        ['@context' => 'https://schema.org', '@graph' => $graph],
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP
    );
    ?>
    <script type="application/ld+json"><?= $schemaJson ?></script>
</head>
<body
    class="<?= e($bodyClass) ?>"
    data-phone="<?= e($phone) ?>"
    data-email="<?= e($email) ?>"
    data-address="<?= e($address) ?>"
    data-whatsapp="<?= e(setting('whatsapp')) ?>"
    data-home-url="<?= e(lang_url('/')) ?>"
    data-equipment-url="<?= e(lang_url('/equipment')) ?>"
    data-contact-url="<?= e(lang_url('/contact')) ?>"
    data-about-url="<?= e(lang_url('/about')) ?>"
    data-supply-url="<?= e(lang_url('/supply')) ?>"
    data-terms-url="<?= e(lang_url('/terms')) ?>"
    data-site-name="<?= e(t('site.name')) ?>"
>
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
                    <li><a href="<?= e(lang_url('/contact')) ?>"><?= e(t('nav.contact')) ?></a></li>
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
                    <li><a href="<?= e(lang_url('/terms')) ?>"><?= e(t('nav.terms')) ?></a></li>
                </ul>
            </div>
        </div>

        <div class="footer__base">
            <p><?= e(t('footer.legal', ['year' => date('Y')])) ?></p>
            <p><a href="<?= e(lang_url('/terms')) ?>"><?= e(t('nav.terms')) ?></a> · <?= e(t('footer.note')) ?></p>
        </div>
    </div>
</footer>
</body>
</html>
