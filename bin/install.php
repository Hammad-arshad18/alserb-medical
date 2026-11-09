#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Creates the database, applies database/schema.sql, and seeds the catalog.
 *
 *   php bin/install.php --fresh
 *   php bin/install.php --fresh --password='your password'
 *   php bin/install.php --fresh --username=admin --password='...'
 */

define('APP_ROOT', dirname(__DIR__));

$configFile = APP_ROOT . '/app/config.local.php';
if (!is_file($configFile)) {
    fwrite(STDERR, "Missing app/config.local.php. Copy app/config.example.php first.\n");
    exit(1);
}

$config = require $configFile;
$db = $config['db'] ?? [];

$options = getopt('', ['fresh', 'username::', 'password::']);
$fresh = isset($options['fresh']);
$username = isset($options['username']) && is_string($options['username']) ? $options['username'] : 'admin';
$password = isset($options['password']) && is_string($options['password']) ? $options['password'] : null;

$database = (string) ($db['name'] ?? 'alserb_medical');
if (!preg_match('/^[A-Za-z0-9_]+$/', $database)) {
    fwrite(STDERR, "The database name may contain only letters, numbers, and underscores.\n");
    exit(1);
}

$host = (string) ($db['host'] ?? '127.0.0.1');
$port = (int) ($db['port'] ?? 3306);
$charset = (string) ($db['charset'] ?? 'utf8mb4');
$user = (string) ($db['user'] ?? 'root');
$pass = (string) ($db['password'] ?? '');

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%d;charset=%s', $host, $port, $charset),
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    $pdo->exec(sprintf(
        'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
        $database
    ));
    $pdo->exec(sprintf('USE `%s`', $database));
} catch (PDOException $error) {
    fwrite(STDERR, "Database connection failed: " . $error->getMessage() . "\n");
    fwrite(STDERR, "Check app/config.local.php and make sure MySQL is running.\n");
    exit(1);
}

$existing = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

if ($existing !== [] && !$fresh) {
    fwrite(STDERR, "The database already contains tables.\n");
    fwrite(STDERR, "Use --fresh to drop and rebuild them, or point config.local.php at another database.\n");
    exit(1);
}

if ($fresh && $existing !== []) {
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    foreach (['order_status_history', 'order_items', 'orders', 'rate_limits', 'inquiries', 'products', 'categories', 'settings', 'staff_users'] as $table) {
        $pdo->exec(sprintf('DROP TABLE IF EXISTS `%s`', $table));
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
}

$schema = file_get_contents(APP_ROOT . '/database/schema.sql');
if ($schema === false) {
    fwrite(STDERR, "Could not read database/schema.sql.\n");
    exit(1);
}

$schema = preg_replace('/^\s*--.*$/m', '', $schema) ?? $schema;
$statements = preg_split('/;\s*(?:\r?\n|$)/', $schema) ?: [];

$count = 0;
foreach ($statements as $statement) {
    $statement = trim($statement);
    if ($statement === '') {
        continue;
    }

    try {
        $pdo->exec($statement);
        $count++;
    } catch (PDOException $error) {
        fwrite(STDERR, "Failed while applying the schema:\n" . $error->getMessage() . "\n");
        fwrite(STDERR, "Statement: " . substr($statement, 0, 180) . "\n");
        exit(1);
    }
}

$generated = false;
if ($password === null || $password === '') {
    $password = bin2hex(random_bytes(6));
    $generated = true;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare('INSERT INTO staff_users (username, password_hash) VALUES (?, ?)');
$stmt->execute([$username, $hash]);

$productCount = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$categoryCount = (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();

echo "Alserb Medical installed.\n\n";
echo "Database:  {$database}\n";
echo "Statements: {$count}\n";
echo "Catalog:   {$categoryCount} departments, {$productCount} items\n\n";
echo "Admin:     /admin/login\n";
echo "Username:  {$username}\n";
echo "Password:  {$password}\n";
if ($generated) {
    echo "\nThis password was generated once. Store it in a safe place.\n";
}
echo "\nStart the site with: php -S localhost:8080 router.php\n";
echo "The seeded contact details and sample requests are placeholders. Replace them in the admin panel.\n";
