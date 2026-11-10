<?php
declare(strict_types=1);

/**
 * Small helpers shared by the public site and the admin panel.
 */

function config(?string $key = null, mixed $default = null): mixed
{
    $config = $GLOBALS['config'] ?? [];

    if ($key === null) {
        return $config;
    }

    $value = $config;
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }

    return $value;
}

function db(): PDO
{
    if (!isset($GLOBALS['pdo']) || !$GLOBALS['pdo'] instanceof PDO) {
        throw new RuntimeException('Database connection is not available.');
    }

    return $GLOBALS['pdo'];
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function post_string(string $key): string
{
    $value = $_POST[$key] ?? '';

    return is_string($value) ? trim($value) : '';
}

function post_int(string $key, int $default = 0): int
{
    $value = $_POST[$key] ?? null;

    return is_numeric($value) ? (int) $value : $default;
}

function get_string(string $key): string
{
    $value = $_GET[$key] ?? '';

    return is_string($value) ? trim($value) : '';
}

function current_lang(): string
{
    $lang = $GLOBALS['lang'] ?? 'ar';

    return $lang === 'en' ? 'en' : 'ar';
}

function is_rtl(): bool
{
    return current_lang() === 'ar';
}

function other_lang(): string
{
    return current_lang() === 'ar' ? 'en' : 'ar';
}

function lang_url(string $path = '/'): string
{
    $path = '/' . ltrim($path, '/');

    return '/' . current_lang() . ($path === '/' ? '' : $path);
}

function switch_lang_url(): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $parts = parse_url($uri);
    $path = isset($parts['path']) ? '/' . trim($parts['path'], '/') : '/';

    if (preg_match('#^/(ar|en)(/.*)?$#', $path, $match)) {
        $path = '/' . other_lang() . ($match[2] ?? '');
    } else {
        $path = lang_url('/');
    }

    $query = isset($parts['query']) && $parts['query'] !== '' ? '?' . $parts['query'] : '';

    return $path . $query;
}

/**
 * Absolute URL for a site-relative path.
 */
function absolute_url(string $path): string
{
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }

    return app_url() . '/' . ltrim($path, '/');
}

/**
 * Canonical URL for the current page. Search queries are dropped so the
 * catalog page stays the canonical target; the category filter is kept.
 */
function canonical_url(): string
{
    $path = current_path();
    $url = absolute_url('/' . current_lang() . ($path === '/' ? '' : $path));

    if ($path === '/equipment' && ($category = get_string('category')) !== '') {
        $url .= '?category=' . rawurlencode($category);
    }

    return $url;
}

/**
 * Same page in the other language, used for hreflang links.
 */
function alternate_url(string $lang): string
{
    $lang = $lang === 'en' ? 'en' : 'ar';
    $path = current_path();
    $url = absolute_url('/' . $lang . ($path === '/' ? '' : $path));

    if ($path === '/equipment' && ($category = get_string('category')) !== '') {
        $url .= '?category=' . rawurlencode($category);
    }

    return $url;
}

function current_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $path = '/' . trim(preg_replace('#/{2,}#', '/', $path) ?? $path, '/');

    if (preg_match('#^/(ar|en)(?=/|$)#', $path, $match)) {
        $path = substr($path, 3);
        if ($path === '' || $path === false) {
            $path = '/';
        }
    }

    return $path;
}

function nav_is(string $path): bool
{
    $current = current_path();

    if ($path === '/') {
        return $current === '/';
    }

    return $current === $path || str_starts_with($current, $path . '/');
}

function asset(string $path): string
{
    $path = '/' . ltrim($path, '/');
    $file = PUBLIC_ROOT . $path;

    if (is_file($file)) {
        return $path . '?v=' . filemtime($file);
    }

    return $path;
}

/** @return array<string, string> */
function lang_strings(): array
{
    static $cache = [];

    $lang = current_lang();
    if (!isset($cache[$lang])) {
        $file = APP_ROOT . '/app/lang/' . $lang . '.php';
        $strings = is_file($file) ? require $file : [];
        $cache[$lang] = is_array($strings) ? $strings : [];
    }

    return $cache[$lang];
}

function t(string $key, array $replace = []): string
{
    $strings = lang_strings();
    $text = $strings[$key] ?? $key;

    foreach ($replace as $search => $value) {
        $text = str_replace(':' . $search, (string) $value, $text);
    }

    return $text;
}

/**
 * Reads a paired translated column, e.g. name_ar / name_en.
 */
function bi(array $row, string $field): string
{
    $lang = current_lang();
    $value = $row[$field . '_' . $lang] ?? null;

    if ($value === null || $value === '') {
        $other = $field . '_' . ($lang === 'ar' ? 'en' : 'ar');
        $value = $row[$other] ?? '';
    }

    return (string) $value;
}

/** @return array<string, array{value_ar: ?string, value_en: ?string}> */
function all_settings(): array
{
    static $cache = null;

    if ($cache !== null) {
        return $cache;
    }

    $cache = [];
    try {
        foreach (db()->query('SELECT skey, value_ar, value_en FROM settings') as $row) {
            $cache[(string) $row['skey']] = [
                'value_ar' => $row['value_ar'],
                'value_en' => $row['value_en'],
            ];
        }
    } catch (Throwable) {
        $cache = [];
    }

    return $cache;
}

function setting(string $key, string $default = ''): string
{
    $settings = all_settings();
    if (!isset($settings[$key])) {
        return $default;
    }

    $lang = current_lang();
    $value = $settings[$key]['value_' . $lang] ?? '';

    if ($value === null || $value === '') {
        $other = $lang === 'ar' ? 'en' : 'ar';
        $value = $settings[$key]['value_' . $other] ?? '';
    }

    return $value !== null && $value !== '' ? (string) $value : $default;
}

function setting_pair(string $key): array
{
    $settings = all_settings();

    return [
        'value_ar' => (string) ($settings[$key]['value_ar'] ?? ''),
        'value_en' => (string) ($settings[$key]['value_en'] ?? ''),
    ];
}

/**
 * City used in copy such as "based in ..." and the home page fact bar.
 * Reads the `city` setting; falls back to the first part of the address.
 */
function setting_city(): string
{
    $city = setting('city');
    if ($city !== '') {
        return $city;
    }

    $address = setting('address');
    if ($address === '') {
        return '';
    }

    $parts = preg_split('/\s+[—–]\s+/u', $address, 2);
    $city = trim($parts[0] ?? $address);

    return $city !== '' ? $city : $address;
}

/**
 * Timezone read from the `timezone` setting, then the config file.
 * Invalid or unknown values fall back to Africa/Tripoli.
 */
function app_timezone(): string
{
    $zone = setting('timezone');
    if ($zone === '') {
        $zone = (string) config('app.timezone', 'Africa/Tripoli');
    }

    return in_array($zone, timezone_identifiers_list(), true) ? $zone : 'Africa/Tripoli';
}

function tel_href(string $phone): string
{
    return 'tel:' . preg_replace('/[^0-9+]/', '', $phone);
}

function whatsapp_href(string $number): string
{
    return 'https://wa.me/' . preg_replace('/\D/', '', $number);
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $sent = $_POST['_csrf'] ?? '';
    $known = $_SESSION['_csrf'] ?? '';

    if (!is_string($sent) || !is_string($known) || $known === '' || !hash_equals($known, $sent)) {
        if (wants_json()) {
            json_response(['ok' => false, 'message' => t('error.419.body')], 419);
        }

        http_response_code(419);
        render('public/419', [
            'metaTitle' => t('error.419.title'),
            'metaDescription' => t('error.419.body'),
        ]);
        exit;
    }
}

function wants_json(): bool
{
    if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
        return true;
    }

    return stripos((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json') !== false;
}

function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }

    $value = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);

    return is_string($value) ? $value : null;
}

function redirect(string $path, int $status = 302): never
{
    header('Location: ' . $path, true, $status);
    exit;
}

function render(string $view, array $data = []): void
{
    AgentReady::emitCommonHeaders();
    AgentReady::emitLinkHeaders(canonical_url());

    if (AgentReady::wantsMarkdown()) {
        $markdown = AgentReady::markdownForView($view, $data);
        if ($markdown !== null) {
            AgentReady::serveMarkdown($markdown);
        }
    }

    extract($data, EXTR_SKIP);

    ob_start();
    require APP_ROOT . '/app/views/' . $view . '.php';
    $content = (string) ob_get_clean();

    require APP_ROOT . '/app/views/layouts/public.php';
}

function render_admin(string $view, array $data = []): void
{
    extract($data, EXTR_SKIP);

    ob_start();
    require APP_ROOT . '/app/views/' . $view . '.php';
    $content = (string) ob_get_clean();

    require APP_ROOT . '/app/views/layouts/admin.php';
}

function admin_user(): ?array
{
    static $loaded = false;
    static $user = null;

    if ($loaded) {
        return $user;
    }
    $loaded = true;

    $id = $_SESSION['admin_id'] ?? null;
    if (!is_numeric($id)) {
        return null;
    }

    $stmt = db()->prepare('SELECT id, username FROM staff_users WHERE id = ? LIMIT 1');
    $stmt->execute([(int) $id]);
    $row = $stmt->fetch();

    $user = $row ?: null;

    return $user;
}

function require_admin(): void
{
    if (admin_user() === null) {
        flash('admin_error', 'Please sign in to continue.');
        redirect('/admin/login');
    }
}

function not_found(): never
{
    http_response_code(404);

    if (!empty($GLOBALS['is_admin'])) {
        render_admin('admin/404', ['metaTitle' => 'Not found']);
    } else {
        render('public/404', [
            'metaTitle' => t('error.404.title'),
            'metaDescription' => t('error.404.body'),
        ]);
    }

    exit;
}

/**
 * Absolute path of the public uploads directory.
 *
 * It follows the real document root instead of a project-relative `public/`
 * path, so the app works whether `public/` is served directly or its files
 * are copied into the host's `public_html/` folder.
 */
function uploads_dir(): string
{
    if (defined('PUBLIC_ROOT')) {
        return PUBLIC_ROOT . '/uploads';
    }

    return APP_ROOT . '/public/uploads';
}

/**
 * Parses a php.ini size value ("8M", "512K", "1G") into bytes.
 */
function ini_bytes(string $value): int
{
    $value = trim($value);
    if ($value === '') {
        return 0;
    }

    $unit = strtolower(substr($value, -1));
    $number = (float) $value;

    return match ($unit) {
        'g' => (int) round($number * 1024 ** 3),
        'm' => (int) round($number * 1024 ** 2),
        'k' => (int) round($number * 1024),
        default => (int) $number,
    };
}

/**
 * Largest image the server will actually accept, in bytes. Respects the
 * application limit but never promises more than PHP or post_max_size allow.
 */
function upload_limit_bytes(): int
{
    $limits = [(int) config('max_upload_bytes', 4 * 1024 * 1024)];

    $uploadMax = ini_bytes((string) ini_get('upload_max_filesize'));
    if ($uploadMax > 0) {
        $limits[] = $uploadMax;
    }

    $postMax = ini_bytes((string) ini_get('post_max_size'));
    if ($postMax > 0) {
        // Leave room for the other form fields inside the same POST body.
        $limits[] = max(1024 * 1024, $postMax - 1024 * 1024);
    }

    return max(1024, min($limits));
}

function upload_limit_label(): string
{
    $bytes = upload_limit_bytes();

    if ($bytes >= 1024 * 1024) {
        $mb = rtrim(rtrim(number_format($bytes / (1024 * 1024), 1, '.', ''), '0'), '.');

        return $mb . ' MB';
    }

    return max(1, (int) round($bytes / 1024)) . ' KB';
}

/**
 * Stores an uploaded image in public/uploads and returns its file name.
 */
function handle_image_upload(array $file, ?string &$error = null): ?string
{
    $code = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($code === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($code === UPLOAD_ERR_INI_SIZE || $code === UPLOAD_ERR_FORM_SIZE) {
        $error = 'The image is larger than the server accepts (up to ' . upload_limit_label() . '). Resize it and try again.';
        return null;
    }

    if ($code === UPLOAD_ERR_PARTIAL) {
        $error = 'The upload was interrupted. Please try again.';
        return null;
    }

    if ($code !== UPLOAD_ERR_OK) {
        $error = 'The image could not be uploaded (error ' . $code . '). Please try again.';
        return null;
    }

    $max = upload_limit_bytes();
    if ((int) ($file['size'] ?? 0) > $max) {
        $error = 'The image is larger than the ' . upload_limit_label() . ' limit. Resize it and try again.';
        return null;
    }

    $info = @getimagesize((string) ($file['tmp_name'] ?? ''));
    $mime = is_array($info) ? ($info['mime'] ?? '') : '';
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($extensions[$mime])) {
        $error = 'Use a JPEG, PNG, or WebP image.';
        return null;
    }

    $dir = uploads_dir();
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        $error = 'The upload directory could not be created. Ask the host to make the site root writable.';
        return null;
    }

    if (!is_writable($dir)) {
        $error = 'The upload directory is not writable. Ask the host to give the site root write permission.';
        return null;
    }

    $name = bin2hex(random_bytes(10)) . '.' . $extensions[$mime];
    if (!move_uploaded_file((string) $file['tmp_name'], $dir . '/' . $name)) {
        $error = 'The image could not be saved.';
        return null;
    }

    return $name;
}

function delete_upload(?string $name): void
{
    if ($name === null || $name === '') {
        return;
    }

    $path = uploads_dir() . '/' . basename($name);
    if (is_file($path)) {
        @unlink($path);
    }
}

/**
 * Resolves a stored image name to a public URL, checking uploads then seed assets.
 */
function image_url(?string $name, string $fallback = ''): string
{
    $name = $name !== null && $name !== '' ? basename($name) : ($fallback !== '' ? basename($fallback) : '');

    if ($name === '') {
        return asset('/assets/img/placeholder.svg');
    }

    if (is_file(uploads_dir() . '/' . $name)) {
        return '/uploads/' . rawurlencode($name);
    }

    // Hosting migration: files written by older builds lived in the
    // project-relative public/uploads folder, which may sit outside the web
    // root. Move one into the real web root when possible so it starts serving.
    $legacy = APP_ROOT . '/public/uploads/' . $name;
    $target = uploads_dir() . '/' . $name;
    if ($legacy !== $target && is_file($legacy) && is_dir(uploads_dir()) && is_writable(uploads_dir())) {
        if (@rename($legacy, $target)) {
            return '/uploads/' . rawurlencode($name);
        }
    }

    if (is_file(PUBLIC_ROOT . '/assets/img/' . $name)) {
        return asset('/assets/img/' . rawurlencode($name));
    }

    return asset('/assets/img/placeholder.svg');
}

/**
 * Intrinsic dimensions and MIME type of a catalog image.
 *
 * @return array{width: int, height: int, type: string}
 */
function image_dimensions(?string $name, ?string $fallback = ''): array
{
    $name = $name !== null && $name !== '' ? basename($name) : ($fallback !== null ? basename($fallback) : '');
    $paths = [
        uploads_dir() . '/' . $name,
        PUBLIC_ROOT . '/assets/img/' . $name,
    ];

    foreach ($paths as $path) {
        if ($name === '' || !is_file($path)) {
            continue;
        }

        $info = @getimagesize($path);
        if (is_array($info) && $info[0] > 0 && $info[1] > 0) {
            return [
                'width' => (int) $info[0],
                'height' => (int) $info[1],
                'type' => (string) ($info['mime'] ?? 'image/jpeg'),
            ];
        }
    }

    return ['width' => 1200, 'height' => 630, 'type' => 'image/jpeg'];
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    $text = trim($text, '-');

    return $text !== '' ? $text : 'item';
}

function unique_slug(string $base, string $table, ?int $ignoreId = null): string
{
    $allowed = ['categories', 'products'];
    if (!in_array($table, $allowed, true)) {
        throw new InvalidArgumentException('Unexpected table for slug lookup.');
    }

    $base = slugify($base);
    $slug = $base;
    $suffix = 2;

    while (true) {
        $sql = 'SELECT id FROM ' . $table . ' WHERE slug = ?';
        $params = [$slug];

        if ($ignoreId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $ignoreId;
        }

        $stmt = db()->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);

        if ($stmt->fetch() === false) {
            return $slug;
        }

        $slug = $base . '-' . $suffix;
        $suffix++;
    }
}

function format_date(?string $date, string $format = 'j M Y, H:i'): string
{
    if ($date === null || $date === '') {
        return '—';
    }

    try {
        return (new DateTimeImmutable($date))->format($format);
    } catch (Throwable) {
        return $date;
    }
}

function inquiry_statuses(): array
{
    return [
        'new' => 'New',
        'read' => 'Read',
        'closed' => 'Closed',
    ];
}

function order_statuses(): array
{
    return [
        'pending' => 'Pending review',
        'accepted' => 'Accepted',
        'processing' => 'Processing',
        'payment_pending' => 'Payment pending',
        'payment_completed' => 'Payment completed',
        'shipping' => 'Shipping',
        'delivered' => 'Delivered',
        'completed' => 'Completed',
        'rejected' => 'Rejected',
        'cancelled' => 'Cancelled',
    ];
}

function order_status_label(string $status): string
{
    $key = 'order.status.' . $status;
    $translated = t($key);

    return $translated !== $key ? $translated : (order_statuses()[$status] ?? $status);
}

/* Cart -------------------------------------------------------------------- */

/** @return array<int, int> product id => quantity */
function cart_items(): array
{
    $cart = $_SESSION['cart'] ?? [];
    if (!is_array($cart)) {
        return [];
    }

    $clean = [];
    foreach ($cart as $id => $quantity) {
        $id = (int) $id;
        $quantity = (int) $quantity;
        if ($id > 0 && $quantity > 0) {
            $clean[$id] = min($quantity, 99);
        }
    }

    return $clean;
}

function cart_count(): int
{
    return array_sum(cart_items());
}

function cart_set(int $productId, int $quantity): void
{
    if ($productId <= 0) {
        return;
    }

    $cart = cart_items();
    $quantity = max(0, min(99, $quantity));

    if ($quantity === 0) {
        unset($cart[$productId]);
    } else {
        $cart[$productId] = $quantity;
    }

    $_SESSION['cart'] = $cart;
}

function cart_remove(int $productId): void
{
    $cart = cart_items();
    unset($cart[$productId]);
    $_SESSION['cart'] = $cart;
}

function cart_clear(): void
{
    unset($_SESSION['cart']);
}

/**
 * Returns cart lines with current product data, dropping anything that has
 * been hidden since it was added.
 *
 * @return list<array<string, mixed>>
 */
function cart_detailed(): array
{
    $cart = cart_items();
    if ($cart === []) {
        return [];
    }

    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare(
        'SELECT p.id, p.slug, p.name_ar, p.name_en, p.summary_ar, p.summary_en, p.image,'
        . ' c.name_ar AS category_name_ar, c.name_en AS category_name_en, c.image AS category_image'
        . ' FROM products p INNER JOIN categories c ON c.id = p.category_id'
        . ' WHERE p.id IN (' . $placeholders . ') AND p.is_active = 1 AND c.is_active = 1'
        . ' ORDER BY c.sort_order ASC, p.sort_order ASC, p.id ASC'
    );
    $stmt->execute($ids);

    $lines = [];
    $kept = [];
    foreach ($stmt->fetchAll() as $row) {
        $id = (int) $row['id'];
        if (($cart[$id] ?? 0) > 0) {
            $row['quantity'] = $cart[$id];
            $lines[] = $row;
            $kept[$id] = $cart[$id];
        }
    }

    $_SESSION['cart'] = $kept;

    return $lines;
}

function new_order_token(): string
{
    return bin2hex(random_bytes(16));
}

function app_url(): string
{
    $configured = trim((string) config('app.url', ''));
    if ($configured !== '') {
        return rtrim($configured, '/');
    }

    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return ($secure ? 'https' : 'http') . '://' . $host;
}

/** @param array<string, mixed> $order */
function order_public_url(array $order): string
{
    $lang = in_array($order['lang'] ?? 'en', ['ar', 'en'], true) ? (string) $order['lang'] : 'en';

    return app_url() . '/' . $lang . '/order/' . rawurlencode((string) ($order['token'] ?? ''));
}

function record_order_status(int $orderId, string $status, ?string $note = null, ?string $internalNote = null, string $source = 'system', ?int $staffId = null): void
{
    if (!array_key_exists($status, order_statuses())) {
        return;
    }

    $stmt = db()->prepare(
        'INSERT INTO order_status_history (order_id, status, note, internal_note, source, created_by)'
        . ' VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $orderId,
        $status,
        $note !== null && trim($note) !== '' ? trim($note) : null,
        $internalNote !== null && trim($internalNote) !== '' ? trim($internalNote) : null,
        in_array($source, ['system', 'staff', 'customer'], true) ? $source : 'system',
        $staffId,
    ]);
}

/** @return list<array<string, mixed>> */
function order_history(int $orderId): array
{
    $stmt = db()->prepare(
        'SELECT h.*, u.username FROM order_status_history h'
        . ' LEFT JOIN staff_users u ON u.id = h.created_by'
        . ' WHERE h.order_id = ? ORDER BY h.created_at DESC, h.id DESC'
    );
    $stmt->execute([$orderId]);

    return $stmt->fetchAll();
}

function find_order_by_reference_and_email(string $reference, string $email): ?array
{
    $stmt = db()->prepare('SELECT * FROM orders WHERE reference = ? AND LOWER(email) = LOWER(?) LIMIT 1');
    $stmt->execute([strtoupper(trim($reference)), trim($email)]);
    $order = $stmt->fetch();

    return $order ?: null;
}

/**
 * Fixed-window, per-IP rate limit backed by MySQL. Fails open if the table is
 * unavailable so a database hiccup does not take the site down.
 */
function rate_limit(string $bucket, int $max, int $windowSeconds): void
{
    $key = substr($bucket, 0, 40) . ':' . hash('sha256', (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    $now = time();
    $windowStart = $now - max(1, $windowSeconds);

    try {
        $stmt = db()->prepare(
            'INSERT INTO rate_limits (bucket_key, hits, window_start) VALUES (?, 1, ?)'
            . ' ON DUPLICATE KEY UPDATE'
            . ' hits = IF(window_start <= ?, 1, hits + 1),'
            . ' window_start = IF(window_start <= ?, ?, window_start)'
        );
        $stmt->execute([$key, $now, $windowStart, $windowStart, $now]);

        $stmt = db()->prepare('SELECT hits, window_start FROM rate_limits WHERE bucket_key = ? LIMIT 1');
        $stmt->execute([$key]);
        $row = $stmt->fetch();

        if ($row === false) {
            return;
        }

        $hits = (int) $row['hits'];
        $start = (int) $row['window_start'];

        header('X-RateLimit-Limit: ' . $max);
        header('X-RateLimit-Remaining: ' . max(0, $max - $hits));

        if ($hits > $max) {
            $retry = max(1, ($start + $windowSeconds) - $now);
            header('Retry-After: ' . $retry);
            http_response_code(429);

            if (wants_json()) {
                json_response([
                    'ok' => false,
                    'message' => t('error.429.body'),
                    'retry_after' => $retry,
                ], 429);
            }

            if (!empty($GLOBALS['is_admin']) && admin_user() !== null) {
                render_admin('admin/429', ['metaTitle' => 'Too many requests']);
            } else {
                render('public/429', [
                    'metaTitle' => t('error.429.title'),
                    'metaDescription' => t('error.429.body'),
                    'retryAfter' => $retry,
                ]);
            }
            exit;
        }

        if (random_int(1, 400) === 1) {
            db()->exec('DELETE FROM rate_limits WHERE window_start < ' . ($now - 86400));
        }
    } catch (Throwable $error) {
        error_log('Rate limit skipped: ' . $error->getMessage());
    }
}

function new_order_reference(): string
{
    $year = date('Y');

    for ($attempt = 0; $attempt < 10; $attempt++) {
        $reference = 'ALS-' . $year . '-' . strtoupper(bin2hex(random_bytes(2)));
        $stmt = db()->prepare('SELECT id FROM orders WHERE reference = ? LIMIT 1');
        $stmt->execute([$reference]);

        if ($stmt->fetch() === false) {
            return $reference;
        }
    }

    return 'ALS-' . $year . '-' . strtoupper(bin2hex(random_bytes(4)));
}

function find_order_by_token(string $token): ?array
{
    if (!preg_match('/^[a-f0-9]{32,64}$/', $token)) {
        return null;
    }

    $stmt = db()->prepare('SELECT * FROM orders WHERE token = ? LIMIT 1');
    $stmt->execute([$token]);
    $order = $stmt->fetch();

    return $order ?: null;
}

/** @return list<array<string, mixed>> */
function order_lines(int $orderId): array
{
    $stmt = db()->prepare('SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC');
    $stmt->execute([$orderId]);

    return $stmt->fetchAll();
}

function redirect_back(string $fallback): never
{
    $return = $_POST['return'] ?? '';

    if (is_string($return) && str_starts_with($return, '/') && !str_starts_with($return, '//')) {
        redirect($return);
    }

    redirect($fallback);
}

function excerpt(string $text, int $limit = 140): string
{
    $text = trim(preg_replace('/\s+/', ' ', $text) ?? $text);

    if (mb_strlen($text) <= $limit) {
        return $text;
    }

    return rtrim(mb_substr($text, 0, $limit - 1), " \t\n\r\0\x0B.,") . '…';
}
