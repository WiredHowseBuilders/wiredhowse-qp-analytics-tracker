<?php
if (!defined('ABSPATH')) { die('ABSPATH must be defined before loading bootstrap-lib.php'); }
if (!defined('WH_HOST')) { define('WH_HOST',Env::get('APP_HOST')); }
if (!defined('WH_APP'))  { define('WH_APP', Env::get('APP_NAME')); }
if (!defined('WH_URL'))  { define('WH_URL',  Env::get('APP_URL')); }
if (!defined('DB_DOIT')) { define('DB_DOIT',Env::get('DB_DOIT')); }