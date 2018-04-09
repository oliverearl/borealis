<?php
namespace ole4\Magneto\Database;

use PDO;
use PDOException;

use ole4\Magneto\Config\Config;

class Connector
{
    private static $db;

    public static function getInstance()
    {
        if (!isset(self::$db)) {
            self::setInstance();
        }
        return self::$db;
    }

    public static function reloadDatabase()
    {
        self::$db = null;
    }

    private static function setInstance()
    {
        // TODO: Change to configure database programmatically
        $config = Config::getConfig();
        try {
            $dsn = "{$config['dbType']}:host={$config['hostname']};dbname={$config['dbName']}";
            self::$db = new PDO($dsn, $config['username'], $config['password']);
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $ex) {
            trigger_error('Failed to connect to database: ' . $ex, E_USER_ERROR);
        }
    }
}
