<?php
///////////////////////////////////////////
// bootstrap.php  (merged: original bootstrap + config.php)
//===================================================

if (!defined('AUTH_SECRET')) {define('AUTH_SECRET',   getenv('AUTH_SECRET')   ?: '7f3a9d2e1b8c4f6a0e5d7c3b9a2f1e8d4c6b0a7f3e9d2c1b8a5f4e7d3c');}
if (!defined('AUTH_URL')) {define('AUTH_URL',      getenv('APP_URL')        ?: 'https://auth.wiredhowse.app');}
if (!defined('ADMIN_EMAIL')) {define('ADMIN_EMAIL',   getenv('AUTH_ADMIN_EMAIL') ?: 'koubre@gmail.com');}
if (!defined('ADMIN_PASS')) {define('ADMIN_PASS',    getenv('AUTH_ADMIN_PASS')  ?: 'Wh0use2025!#Secure');}
if (!defined('DB_HOST')) {define('DB_HOST',       getenv('DB_HOST')        ?: 'mysql.railway.internal');}
if (!defined('DB_NAME')) {define('DB_NAME',       getenv('DB_NAME')        ?: 'railway');}
if (!defined('DB_USER')) {define('DB_USER',       getenv('DB_USER')        ?: 'root');}
if (!defined('DB_PASS')) {define('DB_PASS',       getenv('DB_PASS')        ?: 'LdTzzPpFqlaPRrzFIkqkbZFtBSUiljbb');}
if (!defined('JWT_TTL')) {define('JWT_TTL',       900);} // 15 minutes
if (!defined('SESSION_TTL')) {define('SESSION_TTL',   86400 * 30);} // 30 days

/*define('ALLOWED_DOMAINS', [
    'wiredhowse.com',
    'wiredhowse.app',
    'alphadropsupply.com',
    'survivalskills.pro',
]);*/


if (!defined('DEBUG_MODE')) {define('DEBUG_MODE', true);      }                      // Set to false in production



// Display errors (only for development)

if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

////////////////////////////////////
if (!defined('ABSPATH')) { define('ABSPATH', __DIR__ . '/'); }

require_once ABSPATH . 'lib/PathInspector.php';
require_once ABSPATH . 'lib/Autoloader.php';

Autoloader::setPath(ABSPATH . 'lib/');
spl_autoload_register(['Autoloader', 'loader']);

Env::load(ABSPATH . '.env');

$wh_cnfg = defined('WH_CNFG') ? WH_CNFG : [];
$wh_s    = $_SERVER;
$wh_host = Env::get('APP_HOST', $wh_s['HTTP_HOST'] ?? 'localhost');
$wh_url  = Env::get('APP_URL',  'https://' . $wh_host . '/');

// ------------------------------------------------------------------
// Core / app / db / anthropic / auth defs (from original bootstrap.php)
// ------------------------------------------------------------------
$wh_defs = [
    // paths
    'ABSPATH'             => ABSPATH,
    'WH_LIB'              => ABSPATH . 'lib/',
    'WH_UTL'              => ABSPATH . 'util/',
    'WH_HEL'              => ABSPATH . 'helper/',
    'ENV_FILE'            => ABSPATH . '.env',
    // app
    'WH_HOST'             => $wh_host,
    'WH_APP'              => Env::get('APP_NAME', str_replace(['wiredhowse', '.com', '/'], '', $wh_host)),
    'WH_URL'              => $wh_url,
    'WH_URLPATH'          => $wh_url,
    // db
    'DB_HOST'             => Env::get('DB_HOST', $wh_cnfg['DB_HOST'] ?? 'localhost'),
    'DB_NAME'             => Env::get('DB_NAME', $wh_cnfg['DB_NAME'] ?? null),
    'DB_USER'             => Env::get('DB_USER', $wh_cnfg['DB_USER'] ?? null),
    'DB_PASS'             => Env::get('DB_PASS', $wh_cnfg['DB_PASS'] ?? null),
    'ACCESSP'             => Env::get('ACCESSP', $wh_cnfg['ACCESSP'] ?? false),
    'ACCESSR'             => Env::get('ACCESSR', $wh_cnfg['ACCESSR'] ?? false),
    // anthropic
    'ANTHROPIC_MODEL'     => Env::get('ANTHROPIC_MODEL',   $wh_cnfg['ANTHROPIC_MODEL']   ?? 'claude-3-haiku-20240307'),
    'ANTHROPIC_API_KEY'   => Env::get('ANTHROPIC_API_KEY', $wh_cnfg['ANTHROPIC_API_KEY'] ?? null),
    // auth (general app auth, distinct from the tracking-system auth below)
    'AUTH_ENCRYPTION_KEY' => Env::get('AUTH_ENCRYPTION_KEY', $wh_cnfg['AUTH_ENCRYPTION_KEY'] ?? null),
    'AUTH_BASE_URL'       => Env::get('AUTH_BASE_URL', $wh_url . 'auth.php'),

    // ------------------------------------------------------------------
    // From config.php — tracking-system auth/session
    // ------------------------------------------------------------------
    'AUTH_SECRET'         => Env::get('AUTH_SECRET'),          // no plaintext fallback — must be set in .env
    'AUTH_URL'            => Env::get('APP_URL', $wh_url),
    'ADMIN_EMAIL'         => Env::get('AUTH_ADMIN_EMAIL'),
    'ADMIN_PASS'          => Env::get('AUTH_ADMIN_PASS'),      // no plaintext fallback — must be set in .env
    'JWT_TTL'             => 900,            // 15 minutes
    'SESSION_TTL'         => 86400 * 30,     // 30 days

    // ClickBank
    'CLICKBANK_VENDOR_ID'  => Env::get('CLICKBANK_VENDOR_ID'),
    'CLICKBANK_SECRET_KEY' => Env::get('CLICKBANK_SECRET_KEY'), // no plaintext fallback

    // Tracking API
    'API_ENABLED'         => true,
    'API_KEY'             => Env::get('TRACKING_API_KEY'),     // no plaintext fallback

    // Logging
    'ENABLE_LOGGING'      => true,
    'LOG_FILE'            => ABSPATH . 'logs/tracking.log',

    // Notifications
    'TRACKING_ADMIN_EMAIL'    => Env::get('TRACKING_ADMIN_EMAIL', 'admin@data.alphadropsupply.com'),
    'SEND_SALE_NOTIFICATIONS' => false,
    'SEND_ERROR_NOTIFICATIONS'=> true,

    // Performance / rate limiting
    'CACHE_ENABLED'        => false,
    'CACHE_DURATION'       => 3600,
    'RATE_LIMIT_ENABLED'   => true,
    'MAX_REQUESTS_PER_HOUR'=> 10000,

    // Mode
    'DEBUG_MODE'           => Env::get('DEBUG_MODE', false),
];

if (!defined('ALLOWED_DOMAINS')) {
    define('ALLOWED_DOMAINS', [
    'wiredhowse.com',
    'wiredhowse.app',
    'alphadropsupply.com',
    'survivalskills.pro',
]);
}

if (!defined('ALLOWED_IPS')) { define('ALLOWED_IPS', [
    // Add trusted IP addresses that can access the tracking system
    // Example: '192.168.1.1', '10.0.0.1'
]);}

if (!defined('WH_SET')) {
    $wh_set = [];
    foreach ($wh_defs as $k => $v) {
        $k = strtoupper($k);
        if (!defined($k)) { define($k, $v); }
        $wh_set[$k] = $v;
    }
    define('WH_SET',   $wh_set);
    define('WH_CNFGS', $wh_set);
}

// ------------------------------------------------------------------
// Error display — driven by DEBUG_MODE
// ------------------------------------------------------------------
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Timezone
date_default_timezone_set(Env::get('APP_TIMEZONE', 'America/Chicago'));

// Logs directory
if (ENABLE_LOGGING && !file_exists(ABSPATH . 'logs')) {
    mkdir(ABSPATH . 'logs', 0755, true);
}

// ------------------------------------------------------------------
// Helper functions (from config.php)
// ------------------------------------------------------------------
function isIPAllowed(string $ip): bool {
    if (empty(ALLOWED_IPS)) {
        return true; // no IP restrictions configured
    }
    return in_array($ip, ALLOWED_IPS, true);
}

function validateAPIKey(?string $key): bool {
    if (!API_ENABLED || empty(API_KEY)) {
        return false;
    }
    return hash_equals(API_KEY, (string) $key);
}

function logError(string $message): void {
    if (ENABLE_LOGGING) {
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents(LOG_FILE, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
    }
}