<?php
namespace ole4\Magneto\Database;

use ole4\Magneto\Magneto;
use ole4\Magneto\Config\Config;

use PDO;
use PDOException;

/**
 * Database Connectivity Class (Connector)
 * Class Connector
 * @package ole4\Magneto\Database
 * @author Oliver Earl <ole4@aber.ac.uk>
 */
class Connector
{
    /**
     * Database instance
     * @var PDO
     */
    private static $db;

    /**
     * Get Instance
     * @return PDO
     * Self-explanatory, returns the $db static instance if it is already configured, otherwise attempts to
     * configure it by calling setInstance().
     */
    public static function getInstance()
    {
        if (!isset(self::$db)) {
            self::setInstance();
        }
        return self::$db;
    }

    /**
     * Reload Database
     * Simply nulls the database, forcing it to be reconfigured the next time getInstance() is called.
     */
    public static function reloadDatabase()
    {
        self::$db = null;
    }

    /**
     * Set Instance
     * This important method first retrieves a copy of the config array for use in this method, before
     * constructing a PDO object. It uses the database type, hostname, database name, username, and password
     * in order to do this. If anything goes wrong, expect a PDOException.
     *
     * This is why it's super important to set up settings.php correctly during deployment, or everything
     * might explode.
     *
     * It was intended to allow the user to change what database they are using programmatically, either
     * through the web application or via the API, but allowing the user to tamper with databases and/or
     * determine which one the user connects to is extremely dangerous without proper authentication.
     * YAGNI.
     */
    private static function setInstance()
    {
        // TODO: Change to configure database programmatically
        $config = Config::getConfig();
        try {
            $dsn = "{$config['dbType']}:host={$config['hostname']};dbname={$config['dbName']}";
            self::$db = new PDO($dsn, $config['username'], $config['password']);
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $ex) {
            Magneto::error('Database Error', $ex);
        }
    }
}
