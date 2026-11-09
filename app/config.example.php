<?php
declare(strict_types=1);

/**
 * Copy this file to app/config.local.php and adjust the database credentials.
 * app/config.local.php is intentionally excluded from version control.
 *
 * These defaults match the MySQL instance managed by DBngin. Do not run a
 * Homebrew MySQL server beside it.
 */
return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'alserb_medical',
        'user' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],

    'app' => [
        'debug' => true,
        'url' => 'http://localhost:8080',
    ],

    'mail' => [
        // Local development: Mailpit SMTP. Set the production SMTP host,
        // port, credentials, and encryption here. Set enabled false to skip.
        'enabled' => true,
        'transport' => 'smtp',
        'host' => '127.0.0.1',
        'port' => 1025,
        'username' => '',
        'password' => '',
        'encryption' => '',
        'from_email' => 'orders@alserbmedical.ly',
        'from_name' => 'Alserb Medical',
        // BCC address for staff notifications. Leave empty to disable the copy.
        'admin_to' => 'info@example.com',
    ],

    // Absolute path and public URL for uploaded catalog images.
    'uploads_dir' => dirname(__DIR__) . '/public/uploads',
    'uploads_url' => '/uploads',

    // Maximum accepted size for one uploaded image, in bytes.
    'max_upload_bytes' => 4 * 1024 * 1024,
];
