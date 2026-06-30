<?php
/**
 * create_site.php - one-off CLI helper to register a new customer/site.
 * Run from the command line: php create_site.php "Client Name" "client@email.com"
 * Prints the site_id (goes in the snippet) and api_key (goes nowhere public).
 */

require_once 'bootstrap.php';

$argv = $_SERVER['argv'] ?? [];
$argc = count($argv);

if ($argc < 2) {
    echo "Usage: php create_site.php \"Site Name\" [owner_email]\n";
    exit(1);
}

$name  = $argv[1];
$email = $argv[2] ?? null;

$siteId = 'WH-' . bin2hex(random_bytes(6));
$apiKey = bin2hex(random_bytes(24));

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $stmt = $pdo->prepare(
        "INSERT INTO sites (site_id, api_key, name, owner_email) VALUES (:site_id, :api_key, :name, :email)"
    );
    $stmt->execute([
        ':site_id' => $siteId,
        ':api_key' => $apiKey,
        ':name'    => $name,
        ':email'   => $email,
    ]);

    echo "Site created.\n";
    echo "name:    $name\n";
    echo "site_id: $siteId   (put this in the pixel snippet)\n";
    echo "api_key: $apiKey   (private, for reporting API only)\n";

} catch (PDOException $e) {
    echo "Failed: " . $e->getMessage() . "\n";
    exit(1);
}
