<?php
declare(strict_types=1);

/**
 * Public, bilingual pages.
 */
final class PublicSite
{
    public static function home(): void
    {
        $categories = db()->query(
            'SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, id ASC'
        )->fetchAll();

        $catalogRows = db()->query(
            'SELECT p.*, c.slug AS category_slug, c.name_ar AS category_name_ar, c.name_en AS category_name_en,'
            . ' c.image AS category_image'
            . ' FROM products p INNER JOIN categories c ON c.id = p.category_id'
            . ' WHERE p.is_active = 1 AND c.is_active = 1'
            . ' ORDER BY c.sort_order ASC, p.sort_order ASC, p.id ASC'
        )->fetchAll();

        $featured = [];
        $seenCategories = [];
        foreach ($catalogRows as $row) {
            $categoryId = (int) $row['category_id'];
            if (!isset($seenCategories[$categoryId])) {
                $featured[] = $row;
                $seenCategories[$categoryId] = true;
            }
            if (count($featured) === 4) {
                break;
            }
        }

        render('public/home', [
            'metaTitle' => null,
            'metaDescription' => t('meta.home'),
            'metaKeywords' => t('meta.keywords.home'),
            'bodyClass' => 'page-home',
            'categories' => $categories,
            'featured' => $featured,
        ]);
    }

    public static function about(): void
    {
        render('public/about', [
            'metaTitle' => t('nav.about'),
            'metaDescription' => t('meta.about'),
            'metaKeywords' => t('meta.keywords.about'),
            'bodyClass' => 'page-about',
        ]);
    }

    public static function equipment(): void
    {
        $categories = db()->query(
            'SELECT c.*, COUNT(p.id) AS product_count'
            . ' FROM categories c LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1'
            . ' WHERE c.is_active = 1 GROUP BY c.id ORDER BY c.sort_order ASC, c.id ASC'
        )->fetchAll();

        $selected = get_string('category');
        $query = mb_substr(get_string('q'), 0, 100);
        $category = null;

        if ($selected !== '') {
            foreach ($categories as $item) {
                if ($item['slug'] === $selected) {
                    $category = $item;
                    break;
                }
            }

            if ($category === null) {
                redirect(lang_url('/equipment'));
            }
        }

        $sql = 'SELECT p.*, c.slug AS category_slug, c.name_ar AS category_name_ar, c.name_en AS category_name_en,'
            . ' c.image AS category_image'
            . ' FROM products p INNER JOIN categories c ON c.id = p.category_id'
            . ' WHERE p.is_active = 1 AND c.is_active = 1';

        $params = [];
        if ($category !== null) {
            $sql .= ' AND c.id = ?';
            $params[] = (int) $category['id'];
        }

        if ($query !== '') {
            $sql .= ' AND (p.name_ar LIKE ? OR p.name_en LIKE ? OR p.summary_ar LIKE ? OR p.summary_en LIKE ?)';
            $like = '%' . $query . '%';
            array_push($params, $like, $like, $like, $like);
        }

        $sql .= ' ORDER BY c.sort_order ASC, p.sort_order ASC, p.id ASC';

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();

        $equipmentUrl = canonical_url();
        $listItems = [];
        foreach ($products as $index => $item) {
            $listItems[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => bi($item, 'name'),
                'url' => app_url() . lang_url('/equipment/' . rawurlencode((string) $item['slug'])),
            ];
        }

        $crumbs = [
            ['@type' => 'ListItem', 'position' => 1, 'name' => t('nav.home'), 'item' => app_url() . lang_url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => t('nav.equipment'), 'item' => app_url() . lang_url('/equipment')],
        ];
        $pageName = $category !== null ? bi($category, 'name') : t('nav.equipment');
        if ($category !== null) {
            $crumbs[] = [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => bi($category, 'name'),
                'item' => $equipmentUrl,
            ];
        }

        $jsonLd = [
            [
                '@type' => 'CollectionPage',
                '@id' => $equipmentUrl . '#page',
                'url' => $equipmentUrl,
                'name' => $pageName,
                'description' => $category !== null ? bi($category, 'summary') : t('meta.equipment'),
                'inLanguage' => current_lang(),
                'isPartOf' => ['@id' => app_url() . '/#website'],
                'mainEntity' => [
                    '@type' => 'ItemList',
                    'numberOfItems' => count($listItems),
                    'itemListElement' => $listItems,
                ],
            ],
            ['@type' => 'BreadcrumbList', 'itemListElement' => $crumbs],
        ];

        $keywords = t('meta.keywords.equipment');
        if ($category !== null) {
            $keywords = bi($category, 'name') . ', ' . $keywords;
        }

        render('public/equipment', [
            'metaTitle' => $category !== null ? bi($category, 'name') : t('nav.equipment'),
            'metaDescription' => t('meta.equipment'),
            'metaKeywords' => $keywords,
            'metaRobots' => $query !== '' ? 'noindex, follow' : 'index, follow, max-image-preview:large',
            'jsonLd' => $jsonLd,
            'bodyClass' => 'page-equipment',
            'categories' => $categories,
            'category' => $category,
            'products' => $products,
            'query' => $query,
        ]);
    }

    public static function product(string $slug): void
    {
        $stmt = db()->prepare(
            'SELECT p.*, c.slug AS category_slug, c.name_ar AS category_name_ar, c.name_en AS category_name_en,'
            . ' c.image AS category_image, c.summary_ar AS category_summary_ar, c.summary_en AS category_summary_en'
            . ' FROM products p INNER JOIN categories c ON c.id = p.category_id'
            . ' WHERE p.slug = ? AND p.is_active = 1 AND c.is_active = 1 LIMIT 1'
        );
        $stmt->execute([$slug]);
        $product = $stmt->fetch();

        if ($product === false) {
            not_found();
        }

        $relatedStmt = db()->prepare(
            'SELECT p.*, c.slug AS category_slug, c.name_ar AS category_name_ar, c.name_en AS category_name_en,'
            . ' c.image AS category_image'
            . ' FROM products p INNER JOIN categories c ON c.id = p.category_id'
            . ' WHERE p.category_id = ? AND p.id <> ? AND p.is_active = 1 AND c.is_active = 1'
            . ' ORDER BY p.sort_order ASC, p.id ASC LIMIT 3'
        );
        $relatedStmt->execute([(int) $product['category_id'], (int) $product['id']]);
        $related = $relatedStmt->fetchAll();

        $productUrl = app_url() . lang_url('/equipment/' . rawurlencode((string) $product['slug']));
        $productImage = preg_replace('/\?.*$/', '', absolute_url(image_url((string) $product['image'])));
        $productImageMeta = image_dimensions((string) $product['image']);
        $categoryUrl = app_url() . lang_url('/equipment?category=' . rawurlencode((string) $product['category_slug']));

        $jsonLd = [
            [
                '@type' => 'Product',
                '@id' => $productUrl . '#product',
                'name' => bi($product, 'name'),
                'description' => excerpt(bi($product, 'description'), 320),
                'image' => [$productImage],
                'url' => $productUrl,
                'category' => bi($product, 'category_name'),
                'inLanguage' => current_lang(),
                'isPartOf' => ['@id' => app_url() . '/#website'],
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => t('nav.home'), 'item' => app_url() . lang_url('/')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => t('nav.equipment'), 'item' => app_url() . lang_url('/equipment')],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => bi($product, 'category_name'), 'item' => $categoryUrl],
                    ['@type' => 'ListItem', 'position' => 4, 'name' => bi($product, 'name'), 'item' => $productUrl],
                ],
            ],
        ];

        render('public/product', [
            'metaTitle' => bi($product, 'name'),
            'metaDescription' => excerpt(bi($product, 'summary'), 155),
            'metaKeywords' => t('meta.keywords.product', [
                'name' => bi($product, 'name'),
                'category' => bi($product, 'category_name'),
                'city' => setting_city(),
            ]),
            'ogImage' => $productImage,
            'ogImageAlt' => bi($product, 'name'),
            'ogImageWidth' => $productImageMeta['width'],
            'ogImageHeight' => $productImageMeta['height'],
            'ogImageType' => $productImageMeta['type'],
            'ogType' => 'product',
            'jsonLd' => $jsonLd,
            'bodyClass' => 'page-product',
            'product' => $product,
            'related' => $related,
        ]);
    }

    public static function supply(): void
    {
        render('public/supply', [
            'metaTitle' => t('nav.supply'),
            'metaDescription' => t('meta.supply'),
            'metaKeywords' => t('meta.keywords.supply'),
            'bodyClass' => 'page-supply',
        ]);
    }

    public static function cart(): void
    {
        $lines = cart_detailed();

        render('public/cart', [
            'metaTitle' => t('cart.title'),
            'metaDescription' => t('meta.cart'),
            'metaKeywords' => t('meta.keywords.cart'),
            'metaRobots' => 'noindex, nofollow',
            'bodyClass' => 'page-cart',
            'lines' => $lines,
        ]);
    }

    public static function cartAdd(): void
    {
        rate_limit('cart', 80, 60);
        verify_csrf();

        $productId = post_int('product_id');
        $quantity = max(1, min(99, post_int('quantity', 1)));

        $stmt = db()->prepare(
            'SELECT p.id FROM products p INNER JOIN categories c ON c.id = p.category_id'
            . ' WHERE p.id = ? AND p.is_active = 1 AND c.is_active = 1 LIMIT 1'
        );
        $stmt->execute([$productId]);

        if ($stmt->fetch() === false) {
            if (wants_json()) {
                json_response(['ok' => false, 'message' => t('cart.error.unavailable')], 422);
            }

            flash('cart_error', t('cart.error.unavailable'));
            redirect_back(lang_url('/equipment'));
        }

        $current = cart_items()[$productId] ?? 0;
        cart_set($productId, $current + $quantity);

        if (wants_json()) {
            json_response([
                'ok' => true,
                'message' => t('cart.added'),
                'cart_count' => cart_count(),
            ]);
        }

        flash('cart_success', t('cart.added'));
        redirect_back(lang_url('/cart'));
    }

    public static function cartUpdate(): void
    {
        rate_limit('cart', 80, 60);
        verify_csrf();

        $quantities = $_POST['qty'] ?? [];
        if (is_array($quantities)) {
            foreach ($quantities as $productId => $quantity) {
                if (is_numeric($productId)) {
                    cart_set((int) $productId, (int) $quantity);
                }
            }
        }

        redirect(lang_url('/cart'));
    }

    public static function cartRemove(): void
    {
        rate_limit('cart', 80, 60);
        verify_csrf();
        cart_remove(post_int('product_id'));

        flash('cart_success', t('cart.removed'));
        redirect(lang_url('/cart'));
    }

    public static function checkout(): void
    {
        $lines = cart_detailed();

        if ($lines === []) {
            flash('cart_error', t('checkout.empty'));
            redirect(lang_url('/cart'));
        }

        $errors = [];
        $old = [
            'name' => '',
            'organization' => '',
            'phone' => '',
            'email' => '',
            'city' => '',
            'address' => '',
            'notes' => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            rate_limit('checkout', 8, 300);
            verify_csrf();

            if (trim((string) ($_POST['website'] ?? '')) !== '') {
                redirect(lang_url('/cart'));
            }

            $old = [
                'name' => post_string('name'),
                'organization' => post_string('organization'),
                'phone' => post_string('phone'),
                'email' => post_string('email'),
                'city' => post_string('city'),
                'address' => post_string('address'),
                'notes' => post_string('notes'),
            ];

            if ($old['name'] === '' || mb_strlen($old['name']) > 120) {
                $errors['name'] = t('form.error.name');
            }
            if (mb_strlen(preg_replace('/\D/', '', $old['phone']) ?? '') < 6 || mb_strlen($old['phone']) > 40) {
                $errors['phone'] = t('form.error.phone');
            }
            if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($old['email']) > 160) {
                $errors['email'] = t('form.error.email');
            }
            if ($old['city'] === '' || mb_strlen($old['city']) > 120) {
                $errors['city'] = t('form.error.city');
            }
            if ($old['address'] === '' || mb_strlen($old['address']) > 255) {
                $errors['address'] = t('form.error.address');
            }
            if (mb_strlen($old['organization']) > 160) {
                $errors['organization'] = t('form.error.length');
            }
            if (mb_strlen($old['notes']) > 2000) {
                $errors['notes'] = t('form.error.length');
            }

            if ($errors === []) {
                $pdo = db();
                $pdo->beginTransaction();

                try {
                    $reference = new_order_reference();
                    $token = new_order_token();

                    $stmt = $pdo->prepare(
                        'INSERT INTO orders (reference, token, customer_name, organization, phone, email, city, address, notes, status, lang)'
                        . ' VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                    );
                    $stmt->execute([
                        $reference,
                        $token,
                        $old['name'],
                        $old['organization'] !== '' ? $old['organization'] : null,
                        $old['phone'],
                        $old['email'],
                        $old['city'],
                        $old['address'],
                        $old['notes'] !== '' ? $old['notes'] : null,
                        'pending',
                        current_lang(),
                    ]);

                    $orderId = (int) $pdo->lastInsertId();
                    $itemStmt = $pdo->prepare(
                        'INSERT INTO order_items (order_id, product_id, name_ar, name_en, category_ar, category_en, quantity)'
                        . ' VALUES (?, ?, ?, ?, ?, ?, ?)'
                    );

                    foreach ($lines as $line) {
                        $itemStmt->execute([
                            $orderId,
                            (int) $line['id'],
                            (string) $line['name_ar'],
                            (string) $line['name_en'],
                            (string) $line['category_name_ar'],
                            (string) $line['category_name_en'],
                            (int) $line['quantity'],
                        ]);
                    }

                    $pdo->commit();
                } catch (Throwable $error) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    throw $error;
                }

                try {
                    record_order_status($orderId, 'pending', t('order.history.created_note'), null, 'system');
                } catch (Throwable $historyError) {
                    error_log('Order history: ' . $historyError->getMessage());
                }

                Mailer::sendOrderEvent([
                    'id' => $orderId,
                    'reference' => $reference,
                    'token' => $token,
                    'customer_name' => $old['name'],
                    'email' => $old['email'],
                    'lang' => current_lang(),
                    'status' => 'pending',
                ], $lines, 'created');

                cart_clear();
                redirect(lang_url('/order/' . $token));
            }
        }

        render('public/checkout', [
            'metaTitle' => t('checkout.title'),
            'metaDescription' => t('meta.checkout'),
            'metaKeywords' => t('meta.keywords.checkout'),
            'metaRobots' => 'noindex, nofollow',
            'bodyClass' => 'page-checkout',
            'lines' => $lines,
            'errors' => $errors,
            'old' => $old,
        ]);
    }

    public static function order(string $token): void
    {
        $order = find_order_by_token($token);

        if ($order === null) {
            not_found();
        }

        render('public/order', [
            'metaTitle' => t('order.title') . ' ' . $order['reference'],
            'metaDescription' => t('meta.order'),
            'metaKeywords' => t('meta.keywords.order'),
            'metaRobots' => 'noindex, nofollow',
            'bodyClass' => 'page-order',
            'order' => $order,
            'lines' => order_lines((int) $order['id']),
            'history' => order_history((int) $order['id']),
        ]);
    }

    public static function track(): void
    {
        $error = null;
        $old = ['reference' => get_string('reference'), 'email' => ''];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            rate_limit('track', 10, 300);
            verify_csrf();

            $old = [
                'reference' => post_string('reference'),
                'email' => post_string('email'),
            ];

            if ($old['reference'] === '' || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
                $error = t('track.error.required');
            } else {
                $order = find_order_by_reference_and_email($old['reference'], $old['email']);
                if ($order !== null) {
                    redirect(lang_url('/order/' . rawurlencode((string) $order['token'])));
                }
                $error = t('track.error.not_found');
            }
        }

        render('public/track', [
            'metaTitle' => t('track.title'),
            'metaDescription' => t('meta.track'),
            'metaKeywords' => t('meta.keywords.track'),
            'metaRobots' => 'noindex, nofollow',
            'bodyClass' => 'page-track',
            'error' => $error,
            'old' => $old,
        ]);
    }

    public static function contact(): void
    {
        $errors = [];
        $old = [
            'name' => '',
            'organization' => '',
            'phone' => '',
            'email' => '',
            'equipment_interest' => '',
            'message' => '',
            'product_id' => '',
        ];
        $product = null;
        $sent = flash('contact_sent') === '1';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            rate_limit('contact', 8, 60);
            verify_csrf();

            if (trim((string) ($_POST['website'] ?? '')) !== '') {
                flash('contact_sent', '1');
                redirect(lang_url('/contact'));
            }

            $old = [
                'name' => post_string('name'),
                'organization' => post_string('organization'),
                'phone' => post_string('phone'),
                'email' => post_string('email'),
                'equipment_interest' => post_string('equipment_interest'),
                'message' => post_string('message'),
                'product_id' => (string) post_int('product_id'),
            ];

            if ($old['name'] === '' || mb_strlen($old['name']) > 120) {
                $errors['name'] = t('form.error.name');
            }
            if (mb_strlen(preg_replace('/\D/', '', $old['phone']) ?? '') < 6 || mb_strlen($old['phone']) > 40) {
                $errors['phone'] = t('form.error.phone');
            }
            if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($old['email']) > 160) {
                $errors['email'] = t('form.error.email');
            }
            if ($old['equipment_interest'] === '' || mb_strlen($old['equipment_interest']) > 255) {
                $errors['interest'] = t('form.error.interest');
            }
            if (mb_strlen($old['message']) < 3 || mb_strlen($old['message']) > 4000) {
                $errors['message'] = t('form.error.message');
            }
            if (mb_strlen($old['organization']) > 160) {
                $errors['organization'] = t('form.error.length');
            }

            $productId = (int) $old['product_id'];
            if ($productId > 0) {
                $product = self::findProductById($productId);
                if ($product === null) {
                    $errors['product'] = t('form.error.product');
                }
            }

            if ($errors === []) {
                $stmt = db()->prepare(
                    'INSERT INTO inquiries (name, organization, phone, email, product_id, equipment_interest, message, status, lang)'
                    . ' VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([
                    $old['name'],
                    $old['organization'] !== '' ? $old['organization'] : null,
                    $old['phone'],
                    $old['email'],
                    $product !== null ? (int) $product['id'] : null,
                    $old['equipment_interest'],
                    $old['message'],
                    'new',
                    current_lang(),
                ]);

                flash('contact_sent', '1');
                redirect(lang_url('/contact'));
            }
        } else {
            $slug = get_string('product');
            if ($slug !== '') {
                $product = self::findProductBySlug($slug);
                if ($product !== null) {
                    $old['equipment_interest'] = bi($product, 'name');
                    $old['product_id'] = (string) $product['id'];
                }
            }
        }

        render('public/contact', [
            'metaTitle' => t('nav.contact'),
            'metaDescription' => t('meta.contact', ['city' => setting_city()]),
            'metaKeywords' => t('meta.keywords.contact', ['city' => setting_city()]),
            'bodyClass' => 'page-contact',
            'errors' => $errors,
            'old' => $old,
            'product' => $product,
            'sent' => $sent,
        ]);
    }

    public static function credits(): void
    {
        render('public/credits', [
            'metaTitle' => t('credits.title'),
            'metaDescription' => t('meta.credits'),
            'metaKeywords' => t('meta.keywords.credits'),
            'bodyClass' => 'page-credits',
        ]);
    }

    private static function findProductBySlug(string $slug): ?array
    {
        $stmt = db()->prepare(
            'SELECT p.id, p.slug, p.name_ar, p.name_en'
            . ' FROM products p INNER JOIN categories c ON c.id = p.category_id'
            . ' WHERE p.slug = ? AND p.is_active = 1 AND c.is_active = 1 LIMIT 1'
        );
        $stmt->execute([$slug]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    private static function findProductById(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT p.id, p.slug, p.name_ar, p.name_en'
            . ' FROM products p INNER JOIN categories c ON c.id = p.category_id'
            . ' WHERE p.id = ? AND p.is_active = 1 AND c.is_active = 1 LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function robots(): void
    {
        header('Content-Type: text/plain; charset=utf-8');

        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /ar/cart',
            'Disallow: /en/cart',
            'Disallow: /ar/checkout',
            'Disallow: /en/checkout',
            'Disallow: /ar/order/',
            'Disallow: /en/order/',
            'Disallow: /ar/track',
            'Disallow: /en/track',
            'Disallow: /*?q=',
            '',
            'Sitemap: ' . app_url() . '/sitemap.xml',
        ];

        echo implode("\n", $lines) . "\n";
    }

    public static function sitemap(): void
    {
        $base = app_url();

        $pages = [
            ['path' => '/', 'lastmod' => null],
            ['path' => '/about', 'lastmod' => null],
            ['path' => '/equipment', 'lastmod' => null],
            ['path' => '/supply', 'lastmod' => null],
            ['path' => '/contact', 'lastmod' => null],
            ['path' => '/credits', 'lastmod' => null],
        ];

        $categories = db()->query(
            'SELECT slug, updated_at FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, id ASC'
        )->fetchAll();
        foreach ($categories as $category) {
            $pages[] = [
                'path' => '/equipment?category=' . rawurlencode((string) $category['slug']),
                'lastmod' => substr((string) $category['updated_at'], 0, 10),
            ];
        }

        $products = db()->query(
            'SELECT slug, updated_at FROM products WHERE is_active = 1 ORDER BY sort_order ASC, id ASC'
        )->fetchAll();
        foreach ($products as $product) {
            $pages[] = [
                'path' => '/equipment/' . rawurlencode((string) $product['slug']),
                'lastmod' => substr((string) $product['updated_at'], 0, 10),
            ];
        }

        header('Content-Type: application/xml; charset=utf-8');

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

        foreach ($pages as $page) {
            $suffix = $page['path'] === '/' ? '' : $page['path'];
            $lastmod = $page['lastmod'] !== null && $page['lastmod'] !== ''
                ? '    <lastmod>' . e($page['lastmod']) . "</lastmod>\n"
                : '';

            foreach (['ar', 'en'] as $lang) {
                echo "  <url>\n";
                echo '    <loc>' . e($base . '/' . $lang . $suffix) . "</loc>\n";
                echo $lastmod;
                echo '    <xhtml:link rel="alternate" hreflang="ar" href="' . e($base . '/ar' . $suffix) . "\"/>\n";
                echo '    <xhtml:link rel="alternate" hreflang="en" href="' . e($base . '/en' . $suffix) . "\"/>\n";
                echo '    <xhtml:link rel="alternate" hreflang="x-default" href="' . e($base . '/en' . $suffix) . "\"/>\n";
                echo "  </url>\n";
            }
        }

        echo "</urlset>\n";
    }
}
