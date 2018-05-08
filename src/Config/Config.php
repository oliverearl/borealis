<?php
namespace ole4\Magneto\Config;

use Exception;

use ole4\Magneto\Magneto;

/**
 * Class Config
 * @package ole4\Magneto\Config
 * @author Oliver Earl <ole4@aber.ac.uk>
 *
 * This class is responsible for reading and writing to the 'settings.php' file, which stores a number of vital program
 * settings and constant values.
 */
class Config
{
    /**
     * Config Filepath
     * This constant is a direct link to the crucial settings.php file that contains program configuration.
     */
    const CONFIG_FILE = __DIR__ . '/../../storage/settings/settings.php';

    /**
     * Config
     * @var array
     * Contains the retrieved contents of settings.php, an associative array of configuration keys and values.
     */
    private static $config;

    /**
     * Config constructor. Disabled as this is a wrapper singleton class.
     */
    private function __construct() { }

    /**
     * Get Instance
     * @return array|null
     * This is a mirror of the getConfig() function.
     */
    public static function getInstance()
    {
        return self::getConfig();
    }

    /**
     * Get Config
     * @return array|null
     * This function attempts to load the configuration data from the settings.php file if it has not been loaded
     * already - if that is the case then it just returns the already existing values.
     *
     * If a problem is encountered, such as not being able to find the file or the file is malformed, the error
     * handling routine is called, but it is likely that missing config will cause the program to crash.
     *
     * The program returns a config array on success, null on failure.
     */
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

    /**
     * Load Config
     * @return array
     * @throws Exception
     * This method attempts to load the contents of the configuration file and returns it in the form of an associative
     * array. If it fails, or the file doesn't exist, an exception is thrown which is caught by the calling method's
     * try+catch block.
     */
    private static function loadConfig()
    {
        if (file_exists(self::CONFIG_FILE)) {
            return require_once self::CONFIG_FILE;
        }
        throw new Exception('Config file not found.');
    }

    /**
     * Save Config to Disk
     * @return bool|null
     * This method writes the currently stored configuration in memory to disk - the settings.php file using a
     * rudimentary method. Initially, it was planned that the program would use INI files to store configuration
     * values, but this was replaced after problems were encountered and a working solution could not be implemented
     * on time. While this method of accessing and saving data to specifically crafted PHP files is well-known, it is
     * still somewhat crude.
     * Inspiration: https://www.abeautifulsite.net/a-better-way-to-write-config-files-in-php
     *
     * The method returns true if it is successful, null if it is not, and starts the error handling routine.
     */
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

    /**
     * Get Config Entry
     * @param $key
     * @return string|null
     * @example $key of value ['debug'] will return $config['debug'] if it exists
     *
     * This method takes an alphanumeric string as a parameter $key. It uses this to look up whether such an entry
     * exists in the configuration associative array. If it exists, the value pertaining to that key
     * in the config array is returned. Otherwise, null is returned.
     */
    public static function getConfigEntry($key)
    {
        if (isset(self::$config[$key])) {
            return self::$config[$key];
        }
        return null;
    }

    /**
     * Update Config Entry
     * @param $key
     * @param $value
     *
     * This method is used for updating configuration entries before calling the saving routine to ensure they are also
     * saved to the settings.php file. It takes an alphanumeric string $key as a parameter. If the key matches a value
     * in the config array, it updates its value with the new $value parameter. Then it calls the saving method.
     */
    public static function updateConfigEntry($key, $value)
    {
        if (isset(self::$config[$key])) {
            self::$config[$key] = $value;
            self::saveConfigToDisk();
        }
    }
}
