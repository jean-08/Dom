<?php

class Database {

    private static ?PDO $instance = null;

    private function __construct() {}

    public static function getInstance(): PDO {

        if (self::$instance === null) {

            try {

                self::$instance = new PDO(
                    "mysql:host=localhost;dbname=DomAssist;charset=utf8mb4",
                    "xyra",
                    "!",
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );

            } catch (PDOException $e) {

                die("Erreur connexion DB : " . $e->getMessage());

            }
        }

        return self::$instance;
    }
}