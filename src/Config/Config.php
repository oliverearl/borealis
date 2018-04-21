<?php
namespace ole4\Magneto\Config;

use Exception;

use WriteiniFile\WriteiniFile;

use ole4\Magneto\Magneto;

class Config
{
    const CONFIGFILE = __DIR__ . '/../../storage/settings/settings.ini';

    private static $config;
    private static $writer;

    private function __construct() {}

    public static function getConfig()
    {
        try {
            if (!isset(self::$config)) {
                self::$config = self::loadConfig();
            }
            self::$writer = new WriteiniFile(self::CONFIGFILE);
        } catch (Exception $exception) {
            Magneto::error('Error when initially setting up INI configuration file', $exception);
        }
        return self::$config;
    }

    private static function loadConfig()
    {
        if (file_exists(self::CONFIGFILE)) {
            return parse_ini_file(self::CONFIGFILE);
        }
        return null;
    }

    private static function saveConfig()
    {
        try {
            $writer = self::$writer;

            $writer->erase();
            $writer->create(self::$config);
            $writer->write();
        } catch (Exception $exception) {
            Magneto::error('Error when saving INI configuration file.', $exception);
        }
    }

    public static function updateConfigEntry($key, $value)
    {
        if (array_key_exists($key, self::$config)) {
            self::$config[$key] = $value;
            self::saveConfig();
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
