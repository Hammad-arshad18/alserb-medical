<?php
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
define('PUBLIC_ROOT', __DIR__);

$configFile = APP_ROOT . '/app/config.local.php';

if (!is_file($configFile)) {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="en"><meta charset="utf-8"><title>Setup required</title>';
    echo '<body style="font-family:system-ui;max-width:44rem;margin:4rem auto;line-height:1.6">';
    echo '<h1>Setup required</h1><p>Copy <code>app/config.example.php</code> to <code>app/config.local.php</code>, ';
    echo 'set the database credentials, then run <code>php bin/install.php</code>.</p></body></html>';
    exit;
}

$config = require $configFile;
$GLOBALS['config'] = $config;

mb_internal_encoding('UTF-8');

$secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_name('alserbsid');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => $secure,
]);
session_start();

require APP_ROOT . '/app/helpers.php';
require APP_ROOT . '/app/Router.php';
require APP_ROOT . '/app/PublicSite.php';
require APP_ROOT . '/app/Admin.php';
require APP_ROOT . '/app/Mailer.php';

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: SAMEORIGIN');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; font-src 'self'; script-src 'self'; form-action 'self'; base-uri 'self'; frame-ancestors 'self'");

set_exception_handler(static function (Throwable $error): void {
    error_log((string) $error);
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');

    if ((bool) config('app.debug')) {
        echo '<!doctype html><html lang="en"><meta charset="utf-8"><title>Application error</title><body>';
        echo '<h1>Application error</h1><pre>' . e((string) $error) . '</pre></body></html>';
    } else {
        echo '<!doctype html><html lang="en"><meta charset="utf-8"><title>Server error</title><body>';
        echo '<h1>Something went wrong</h1><p>Please try again later.</p></body></html>';
    }
});

$db = $config['db'] ?? [];
$dsn = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
    $db['host'] ?? '127.0.0.1',
    (int) ($db['port'] ?? 3306),
    $db['name'] ?? 'alserb_medical',
    $db['charset'] ?? 'utf8mb4'
);

try {
    $pdo = new PDO($dsn, (string) ($db['user'] ?? 'root'), (string) ($db['password'] ?? ''), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $error) {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="en"><meta charset="utf-8"><title>Database unavailable</title><body>';
    echo '<h1>Database unavailable</h1><p>Create the database and seed it with <code>php bin/install.php</code>, ';
    echo 'then check the credentials in <code>app/config.local.php</code>.</p>';
    if (!empty($config['app']['debug'])) {
        echo '<pre>' . e($error->getMessage()) . '</pre>';
    }
    echo '</body></html>';
    exit;
}

$GLOBALS['pdo'] = $pdo;

// Timezone comes from the `timezone` setting, falling back to config.
date_default_timezone_set(app_timezone());

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$requestPath = rawurldecode($requestPath);
$requestPath = '/' . trim(preg_replace('#/{2,}#', '/', $requestPath) ?? $requestPath, '/');

if ($requestPath === '/robots.txt') {
    PublicSite::robots();
    exit;
}

if ($requestPath === '/sitemap.xml') {
    PublicSite::sitemap();
    exit;
}

if ($requestPath === '/') {
    redirect('/ar');
}

$GLOBALS['lang'] = 'ar';
$GLOBALS['is_admin'] = false;

if (preg_match('#^/(ar|en)(/.*)?$#', $requestPath, $match)) {
    $GLOBALS['lang'] = $match[1];
    $routePath = $match[2] ?? '/';
    if ($routePath === '') {
        $routePath = '/';
    }
} elseif ($requestPath === '/admin' || str_starts_with($requestPath, '/admin/')) {
    $GLOBALS['lang'] = 'en';
    $GLOBALS['is_admin'] = true;
    $routePath = $requestPath;
} else {
    not_found();
}

// A POST body larger than post_max_size arrives empty, which would otherwise
// surface as a misleading "session expired" error. Catch it first.
$contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
$postMax = ini_bytes((string) ini_get('post_max_size'));
if (
    ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
    && $_POST === []
    && $_FILES === []
    && $postMax > 0
    && $contentLength > $postMax
) {
    http_response_code(413);
    render('public/413', [
        'metaTitle' => t('error.413.title'),
        'metaDescription' => t('error.413.body'),
        'metaRobots' => 'noindex, nofollow',
    ]);
    exit;
}

rate_limit('general', 300, 60);

$router = new Router();
require APP_ROOT . '/app/routes.php';
$router->dispatch($_SERVER['REQUEST_METHOD'], $routePath);
