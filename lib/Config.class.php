<?php 
class Config {
    // Environment
    public static $ENV; 

    // Settings
    public static $WHAPP;
    public static $WHINST;
    public static $WHPATH;
    public static $WHURL;
    public static $WHHOST;
    public static $WHUSER;
    public static $WHDB;
    public static $WHPSS;

    /**
     * Maps an associative array to Class Properties.
     * Use keys like 'WHURL', 'WHDB', 'WHAPP', etc.
     */
    public static function whOveride($settings = []) {
        foreach ($settings as $key => $value) {
            if (property_exists('Config', $key)) {
                self::$$key = $value;
            }
        }
    }

    public static function init() {
        // 1. Environment Detection
        if (!self::$ENV) {
            self::$ENV = (($_SERVER['REMOTE_ADDR'] ?? '') == '127.0.0.1' || ($_SERVER['HTTP_HOST'] ?? '') == 'localhost') 
                         ? 'DEV' : 'PROD';
        }

        // 2. Default App Settings (if not already overridden)
        self::$WHAPP  = self::$WHAPP  ?? 'whsPxl';
        self::$WHINST = self::$WHINST ?? 'api';

        // 3. Database Credentials
        if (self::$ENV === 'DEV') {
            self::$WHUSER = self::$WHUSER ?? 'root';
            self::$WHDB   = self::$WHDB   ?? 'local_db';
        } else {
            self::$WHUSER = self::$WHUSER ?? 'ubuqjuz5ehjgu';
            self::$WHDB   = self::$WHDB   ?? 'dbqrnw09pbjrir';
            self::$WHPSS  = self::$WHPSS  ?? 'D3M1g0DS11!';
        }

        // 4. URL & Path Logic
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        
        // Only auto-detect if whOveride didn't provide values
        self::$WHHOST = self::$WHHOST ?? ($_SERVER['HTTP_HOST'] ?? 'localhost');
        
        if (!self::$WHURL) {
            $scriptPath  = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            self::$WHURL = $protocol . self::$WHHOST . $scriptPath;
        }
        
        self::$WHPATH = self::$WHPATH ?? rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    }
}