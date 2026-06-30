<?php
if(!class_exists('Config')){
    require_once __DIR__ . '/Config.php';
}

class Database {
    private static $connection = null;

    public static function connect() {
        if (self::$connection === null) {
            try {
                // Using your specific Config properties
                $dsn = "mysql:host=" . Config::$WHHOST . ";dbname=" . Config::$WHDB . ";charset=utf8mb4";
                
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];

                self::$connection = new PDO($dsn, Config::$WHUSER, Config::$WHPSS, $options);
            } catch (\PDOException $e) {
                // Custom error handling
                die("Connection failed: " . $e->getMessage());
            }
        }
        return self::$connection;
    }
}