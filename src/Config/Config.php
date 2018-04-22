<?php
namespace ole4\Magneto\Config;

use Exception;

use ole4\Magneto\Magneto;

class Config
{
    const CONFIG_FILE = __DIR__ . '/../../storage/settings/settings.php';

    private static $config;

    private function __construct() { }

    public static function getInstance()
    {
        return self::getConfig();
    }

    public static function getConfig()
    {
        try
        {
            if (!isset(self::$config))
            {
                self::$config = self::loadConfig();
            }
            return self::$config;
        }
        catch (Exception $exception)
        {
            Magneto::error('Error during config load.', $exception);
            return null;
        }
    }

    private static function loadConfig()
    {
        if (file_exists(self::CONFIG_FILE)) {
            return require_once self::CONFIG_FILE;
        }
        throw new Exception('Config file not found.');
    }

    private static function saveConfigToDisk()
    {
        try
        {
            file_put_contents(self::CONFIG_FILE, '<?php return ' . var_export(self::$config, true) . ';');
            return true;
        }
        catch (Exception $exception)
        {
            Magneto::error('Error saving config to disk.', $exception);
            return null;
        }
    }

    public static function getConfigEntry($key)
    {
        if (isset(self::$config[$key])) {
            return self::$config[$key];
        }
        return null;
    }

    public static function updateConfigEntry($key, $value)
    {
        if (isset(self::$config[$key])) {
            self::$config[$key] = $value;
            self::saveConfigToDisk();
        }
    }
}
