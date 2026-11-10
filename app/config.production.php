<?php
declare(strict_types=1);

/**
 * PRODUCTION CONFIGURATION
 *
 * Upload this file to the server's `app/` folder and rename it to:
 *     app/config.local.php
 * (overwrite the existing file)
 *
 * Keep this file out of any public web root. It contains credentials.
 */
return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'u506226831_alserb_medical',
        'user' => 'u506226831_alserb_medical',
        'password' => 'Alserb_medical#18',
        'charset' => 'utf8mb4',
    ],

    'app' => [
        'debug' => false,
        // Public base URL. Used for canonical, Open Graph, sitemap and email links.
        'url' => 'https://alserbmedical.com',
        // Optional X/Twitter handle, e.g. '@alserbmedical'. Leave empty to omit.
        'twitter' => '',
    ],

    'mail' => [
        'enabled' => true,
        'transport' => 'smtp',

        // GoDaddy Email Essentials (Professional Email powered by Titan).
        // If port 465 is blocked by the host, use port 587 with 'tls'.
        'host' => 'smtp.hostinger.com',
        'port' => 465,
        'username' => 'status@alserbmedical.com',
        'password' => 'Alserb_medical#18', // TODO: the info@ mailbox password
        'encryption' => 'ssl',

        // Must be the same mailbox as the username above.
        'from_email' => 'status@alserbmedical.com',
        'from_name' => 'Alserb Medical',

        // Staff BCC copy of every new order request. Same mailbox here.
        'admin_to' => 'status@alserbmedical.com',
    ],

    // Uploads go to <document root>/uploads automatically.

    'max_upload_bytes' => 4 * 1024 * 1024,
];
