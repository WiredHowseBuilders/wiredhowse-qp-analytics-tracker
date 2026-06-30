<?php
if (!defined('WH_SITE_ID')) {
define('WH_SITE_ID', '001'); 
}
/**
 * track.php - Multi-tenant pixel ingest endpoint
 * Called by pixel.js. Writes one row to `events` per call.
 *
 * GET or POST:
 *   site_id      required - public site id from the snippet
 *   event        optional - defaults to 'pageview'
 *   session_id   required - generated client-side, persists per visit
 *   click_id     optional - affiliate/campaign id pulled from URL
 *   value        optional - numeric (sale amount, lead value, etc.)
 *   url          optional - page url (falls back to referrer header)
 *   meta         optional - JSON string of arbitrary extra fields
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


function input($key, $default = null) {
    return $_POST[$key] ?? $_GET[$key] ?? $default;
}

$siteId    = trim((string)input('site_id', WH_SITE_ID));
$sessionId = trim((string)input('session_id', ''));
$eventType = trim((string)input('event', 'pageview'));
$clickId   = input('click_id');
$value     = input('value');
$pageUrl   = input('url', $_SERVER['HTTP_REFERER'] ?? null);
$metaRaw   = input('meta');

if ($siteId === '' || $sessionId === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'site_id and session_id are required']);
    exit();
}

// validate meta is real JSON if provided
$meta = null;
if ($metaRaw !== null && $metaRaw !== '') {
    $decoded = json_decode($metaRaw, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $meta = json_encode($decoded);
    }
}

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

// confirm site exists and is active
$siteCheck = $pdo->prepare("SELECT status FROM sites WHERE site_id = :site_id LIMIT 1");
$siteCheck->execute([':site_id' => $siteId]);
$site = $siteCheck->fetch(PDO::FETCH_ASSOC);

if (!$site) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Unknown site_id']);
    exit();
}
if ($site['status'] !== 'active') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Site is paused']);
    exit();
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO events (site_id, event_type, session_id, click_id, value, page_url, referrer, ip_address, user_agent, meta)
         VALUES (:site_id, :event_type, :session_id, :click_id, :value, :page_url, :referrer, :ip_address, :user_agent, :meta)"
    );

    $stmt->execute([
        ':site_id'    => $siteId,
        ':event_type' => $eventType,
        ':session_id' => $sessionId,
        ':click_id'   => $clickId,
        ':value'      => $value !== null && $value !== '' ? (float)$value : null,
        ':page_url'   => $pageUrl,
        ':referrer'   => $_SERVER['HTTP_REFERER'] ?? null,
        ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ':meta'       => $meta,
    ]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to log event']);
    logError("track.php insert failed: " . $e->getMessage());
}
