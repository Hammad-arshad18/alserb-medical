<?php
declare(strict_types=1);

/**
 * Router for the PHP built-in server.
 *
 * Run from the project root:
 *   php -S localhost:8080 router.php
 *
 * The document root stays at the project root, so this file streams anything
 * that exists under public/ and sends every other request to the front
 * controller. PHP files are never streamed as source.
 */

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = rawurldecode($path);
$path = '/' . ltrim(preg_replace('#/{2,}#', '/', $path) ?? $path, '/');

$publicRoot = realpath(__DIR__ . '/public');
$file = __DIR__ . '/public' . $path;
$real = realpath($file);

if (
    $publicRoot !== false
    && $real !== false
    && is_file($real)
    && str_starts_with($real, $publicRoot . DIRECTORY_SEPARATOR)
    && !str_starts_with(basename($real), '.')
    && !str_contains($path, "\0")
) {
    $extension = strtolower(pathinfo($real, PATHINFO_EXTENSION));
    $executable = ['php', 'phtml', 'phar', 'php3', 'php4', 'php5', 'php7', 'php8', 'inc'];

    // Never stream source or execute scripts from public/. The front
    // controller is the only PHP entry point.
    if (!in_array($extension, $executable, true)) {
        $mimes = [
            'css' => 'text/css; charset=utf-8',
            'js' => 'application/javascript; charset=utf-8',
            'svg' => 'image/svg+xml',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'json' => 'application/json; charset=utf-8',
            'txt' => 'text/plain; charset=utf-8',
            'pdf' => 'application/pdf',
        ];

        header('Content-Type: ' . ($mimes[$extension] ?? 'application/octet-stream'));
        header('Content-Length: ' . (string) filesize($real));
        header('Cache-Control: public, max-age=604800');
        readfile($real);
        exit;
    }
}

require __DIR__ . '/public/index.php';
