<?php

if(!class_exists('whConfig')){
require_once 'whConfig.class.php';
}

class whDatabase {
    private static $connection = null;

    public static function connect() {
        if (self::$connection === null) {
            try {
                // Using your specific whConfig properties
                $dsn = "mysql:host=" . whConfig::$WHHOST . ";dbname=" . whConfig::$WHDB . ";charset=utf8mb4";
                
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];

                self::$connection = new PDO($dsn, whConfig::$WHUSER, whConfig::$WHPSS, $options);
            } catch (\PDOException $e) {
                // Custom error handling
                die("Connection failed: " . $e->getMessage());
            }
        }
        return self::$connection;
    }
}