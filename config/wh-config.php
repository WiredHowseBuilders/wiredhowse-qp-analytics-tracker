<?php
//=====================
// /home/customer/www/shared.wiredhowse.com/public_html/config/wh-config.php
$wh_s  = $_SERVER ?? [];
if (!defined('WH_HOST')) { define('WH_HOST', Env::get('APP_HOST',$wh_s['HTTP_HOST'] ?? 'localhost')); }
if (!defined('WH_APP'))  { define('WH_APP',  Env::get('APP_NAME', str_replace('wiredhowse', '', str_replace('.com', '', str_replace('/', '', WH_HOST))))) ; }
if (!defined('WH_URL'))  { define('WH_URL',  Env::get('APP_URL','https://' . WH_HOST . '/')); }
if (!defined('WH_URLPATH')) { define('WH_URLPATH', WH_URL); }
//=====================
// DB creds for THIS domain
if (!defined('DB_HOST')) { define('DB_HOST', Env::get('DB_HOST', 'localhost')); }
if (!defined('DB_NAME')) { define('DB_NAME', Env::get('DB_NAME')); }
if (!defined('DB_USER')) { define('DB_USER', Env::get('DB_USER')); }
if (!defined('DB_PASS')) { define('DB_PASS', Env::get('DB_PASS')); }
//=====================
if (!defined('AUTH_ENCRYPTION_KEY')) { define('AUTH_ENCRYPTION_KEY', Env::get('AUTH_ENCRYPTION_KEY', 'your-key-here')); }
if (!defined('AUTH_BASE_URL'))       { define('AUTH_BASE_URL', Env::get('AUTH_BASE_URL', WH_URLPATH . 'auth.php')); }
