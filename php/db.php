<?php
// /config/Database.php

class Database
{
    private static ?mysqli $connection = null;

    private function __construct() {} // costruttore privato per singleton

    public static function getConnection(): mysqli
    {
        if (self::$connection === null) {
            self::$connection = new mysqli('my_mariadb', 'root', 'ciccio', 'scuola');

            if (self::$connection->connect_error) {
                die("Connessione fallita: " . self::$connection->connect_error);
            }
        }

        return self::$connection;
    }
}
