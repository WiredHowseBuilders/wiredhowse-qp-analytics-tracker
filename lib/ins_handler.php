<?php
/**
 * Path: /_api/cb_pixel/ins_handler.php
 * Decrypts and processes ClickBank Instant Notifications (INS v8.0)
 */

require_once __DIR__ . '/lib/whConfig.class.php';
require_once __DIR__ . '/lib/whDatabase.class.php';

// 1. Configuration
whConfig::init();
$secretKey = "YOUR_CLICKBANK_SECRET_KEY"; // Set this in ClickBank Advanced Tools
$pdo = whDatabase::connect();

// 2. Get the raw encrypted post from ClickBank
$message = json_decode(file_get_contents('php://input'));
if (!$message || !isset($message->notification) || !isset($message->iv)) {
    die("Invalid Request");
}

$encrypted = $message->notification;
$iv = $message->iv;

// 3. Decrypt the message (ClickBank AES-256-CBC)
$decrypted = trim(
    openssl_decrypt(
        base64_decode($encrypted),
        'AES-256-CBC',
        substr(sha1($secretKey), 0, 32),
        OPENSSL_RAW_DATA,
        base64_decode($iv)
    ), 
    "\0..\32"
);

$order = json_decode($decrypted);
if (!$order) { die("Decryption Failed"); }

// 4. Update or Insert the Data
// We use 'ON DUPLICATE KEY UPDATE' to ensure that if the pixel already 
// caught the sale, the INS just updates it with the Pay Method and Type.
try {
    $sql = "INSERT INTO `clickbank_logs` 
            (`receipt`, `transaction_type`, `vendor`, `affiliate`, `role`, 
             `total_amount`, `currency`, `payment_method`, `item_no`, `product_title`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            `transaction_type` = VALUES(`transaction_type`),
            `payment_method` = VALUES(`payment_method`),
            `total_amount` = VALUES(`total_amount`)";

    $stmt = $pdo->prepare($sql);
    
    // INS v8.0 stores line items in an array; we'll take the first one for the summary
    $item = $order->lineItems[0] ?? null;

    $stmt->execute([
        $order->receipt,
        $order->transactionType,
        $order->vendor,
        $order->affiliate,
        $order->role,
        $order->totalOrderAmount,
        $order->currency,
        $order->paymentMethod,
        $item ? $item->itemNo : '',
        $item ? $item->productTitle : ''
    ]);

    // ClickBank requires a 200-range response within 3 seconds
    http_response_code(204); 
} catch (Exception $e) {
    error_log("INS Error: " . $e->getMessage());
    http_response_code(500);
}