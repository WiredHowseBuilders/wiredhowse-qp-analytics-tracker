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
    public static $WHCBANK;
    public static $WHROOTPATH;
    public static $WHROOTAPIPATH;
    public static $WHROOTAPPPATH;
    

    public static function init() {
        // Core Identity
        self::$WHAPP   = self::$WHAPP   ?? 'whsPxl';
        self::$WHINST  = self::$WHINST  ?? 'api';

        
        // Database Credentials
        self::$WHUSER  = self::$WHUSER  ?? 'ubuqjuz5ehjgu';
        self::$WHDB    = self::$WHDB    ?? 'dbqrnw09pbjrir';
        self::$WHPSS   = self::$WHPSS   ?? 'D3M1g0DS11!';
        self::$WHHOST  = self::$WHHOST  ?? 'localhost';
        //CLICKBANK account monk242, api key and instant notification secret key
        self::$WHCBANK = self::$WHCBANK ?? ['account'=>'monk242','api'=>'API-128OWEDWXRLQCZ1ALZ9KU2HPV7BLHZRTH9H5','ipnkey'=>'E1E2241C9B3BEA7'];
                //CLICKBANK account monk242, api key and instant notification secret key
        
        

        // Server Environment & Pathing
        $scriptPath    = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        $host          = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $protocol      = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';

        self::$WHROOTPATH = self::$WHROOTPATH ?? '/home/customer/www/data.alphadropsupply.com/public_html' . '/';
        self::$WHROOTAPIPATH = self::$WHROOTAPIPATH ??  self::$WHROOTPATH . '_api' . '/';
        self::$WHROOTAPPPATH = self::$WHROOTAPPPATH ??  self::$WHROOTPATH . 'apps' . '/';

        self::$WHPATH  = self::$WHPATH  ?? $scriptPath;
        self::$WHURL   = self::$WHURL   ?? $protocol . $host . $scriptPath;
        self::$WHJSURL = self::$WHJSURL ?? self::$WHURL . '/assets/js/';
    }
}
whConfig::init();