#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Sets a new password for a staff user.
 *
 *   php bin/password.php --username=admin --password='new password'
 */

define('APP_ROOT', dirname(__DIR__));

$configFile = APP_ROOT . '/app/config.local.php';
if (!is_file($configFile)) {
    fwrite(STDERR, "Missing app/config.local.php.\n");
    exit(1);
}

$config = require $configFile;
$db = $config['db'] ?? [];
$options = getopt('', ['username::', 'password::']);

$username = isset($options['username']) && is_string($options['username']) ? $options['username'] : 'admin';
$password = isset($options['password']) && is_string($options['password']) ? $options['password'] : '';

if (mb_strlen($password) < 8) {
    fwrite(STDERR, "Choose a password of at least 8 characters.\n");
    exit(1);
}

try {
    $pdo = new PDO(
        sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            (string) ($db['host'] ?? '127.0.0.1'),
            (int) ($db['port'] ?? 3306),
            (string) ($db['name'] ?? 'alserb_medical'),
            (string) ($db['charset'] ?? 'utf8mb4')
        ),
        (string) ($db['user'] ?? 'root'),
        (string) ($db['password'] ?? ''),
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $stmt = $pdo->prepare('UPDATE staff_users SET password_hash = ? WHERE username = ?');
    $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $username]);

    if ($stmt->rowCount() === 0) {
        fwrite(STDERR, "No staff user named {$username} was found.\n");
        exit(1);
    }

    echo "Password updated for {$username}.\n";
} catch (PDOException $error) {
    fwrite(STDERR, "Database error: " . $error->getMessage() . "\n");
    exit(1);
}
