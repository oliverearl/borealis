<?php
namespace ole4\Magneto\Config;

class Config
{
    private static $config = array(
        'debug' =>          true,
        'appName' =>        'Borealis',
        'appVersion' =>     '1.2.0',
        'appDescription' => 'Application',
        'appAuthor' =>      'Oliver Earl',
        'hostname' =>       'db.dcs.aber.ac.uk',
        'dbName' =>         'ole4',
        'username' =>       'ole4',
        'password' =>       '***REMOVED***',
        'dbType' =>         'mysql',
        'maxElements' =>    4,
        'magnetometer_hostname' => 'imapspc0017.imaps.aber.ac.uk',
        'magnetometer_username' => 'imaps\ole4',
        'magnetometer_password' => '',
        'magnetometer_share' => 'magdata'
    );

    private function __construct() {}

    public static function getConfig()
    {
        if (!isset(self::$config)) {
            self::$config = self::loadConfig();
        }
        return self::$config;
    }

    private static function loadConfig()
    {
        /**
         * TODO: Load from proper INI file rather than hardcoded
         */
        return null;
    }

    public static function updateConfigEntry($key, $value)
    {
        /** TODO: Write to file */
        if (array_key_exists($key, self::$config)) {
            self::$config[$key] = $value;
        }
    }

    public static function getConfigEntry($key)
    {
        if (isset(self::$config[$key])) {
            return self::$config[$key];
        }
        return null;
    }
}
