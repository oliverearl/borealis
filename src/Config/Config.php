<?php
namespace ole4\Magneto\Config;

class Config
{
    private static $config = array(
        'appName' =>        'Magnetosphere Monitor',
        'appVersion' =>     'Prerelease 1, MID-DEMO',
        'appDescription' => 'Application',
        'appAuthor' =>      'Oliver Earl',
        'debug' =>          'true',
        'hostname' =>       'db.dcs.aber.ac.uk',
        'dbName' =>         'ole4',
        'username' =>       'ole4',
        'password' =>       '***REMOVED***',
        'dbType' =>         'mysql'
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

    public static function updateConfig($value)
    {
        /** TODO: Write to file */
        return null;
    }
}
