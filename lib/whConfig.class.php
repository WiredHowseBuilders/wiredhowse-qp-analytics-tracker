<?php





class whConfig {

    public static $WHAPP;

    public static $WHINST;

    public static $WHPATH;

    public static $WHURL;

    public static $WHJSURL;

    public static $WHUSER;

    public static $WHDB;

    public static $WHPSS;

    public static $WHHOST;



    public static function init() {

        // Core Identity

        self::$WHAPP   = self::$WHAPP   ?? 'whsPxl';

        self::$WHINST  = self::$WHINST  ?? 'api';

        

        // Database Credentials

        self::$WHUSER  = self::$WHUSER  ?? 'root';

        self::$WHDB    = self::$WHDB    ?? 'railway';

        self::$WHPSS   = self::$WHPSS   ?? 'gqqdOTsBKjKgQLxGVgTNGYooldENNPmb';

        self::$WHHOST  = self::$WHHOST  ?? 'mysql.railway.internal';



        // Server Environment & Pathing

        $scriptPath    = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

        $host          = $_SERVER['HTTP_HOST'] ?? 'localhost';

        $protocol      = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';



        self::$WHPATH  = self::$WHPATH  ?? $scriptPath;

        self::$WHURL   = self::$WHURL   ?? $protocol . $host . $scriptPath;

        self::$WHJSURL = self::$WHJSURL ?? self::$WHURL . '/assets/js/';

    }

}

whConfig::init();