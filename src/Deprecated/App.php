<?php

namespace ole4\Magneto;

use PDO;
use PDOException;

//use App\Database\DatabaseConnection;
use ole4\Magneto\i18n\LocaleLoader;
use ole4\Magneto\Models\Magnetometer;

//require_once __DIR__ . '/Database/DatabaseConnection.php';
require_once __DIR__ . '/i18n/LocaleLoader.php';

class App
{
    private static $db;
    private $config;
    private $language;
    private $i18n;

    public function __construct()
    {
        $this->config = require_once __DIR__ . '/Config/Config.php';
        $this::$db = App::getDbInstance($this->getConfig());
        //$this->db = DatabaseConnection::getInstance($this->getConfig());
        $this->setLanguage();
        $this->i18n = LocaleLoader::loadLocale($this->getLanguage());
    }

    public static function getDbInstance($config = null)
    {
        if (is_null(self::$db)) {
            try
            {
             $dsn = "{$config['dbType']}:host={$config['hostname']};dbname={$config['dbName']}";
             self::$db = new PDO($dsn, $config['username'], $config['password']);
             self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $ex)
            {
                trigger_error('Failed to connect to database: ' . $ex, E_USER_ERROR);
            }
        }
        return self::$db;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getConfigEntry($entry)
    {
        if (!is_null($this->config[$entry])) {
            return $this->config[$entry];
        }
        return null;
    }

    public function getLocale()
    {
        return $this->i18n;
    }

    public function getLocaleString($param)
    {
        if (isset($this->i18n[$param])) {
            return $this->i18n[$param];
        }
        return null;
    }

    public function getAppName()
    {
        return $this->config['appName'];
    }

    public function getLanguage() {
        return $this->language;
    }

    public function setLanguage() {
        // Default language is English.
        $this->language = 'en';

        // Is the language already defined in the session?
        if (isset($_SESSION['language'])) {
            // If so, set that as the program's language, but only if it's either English or Welsh.
            if (strtolower($_SESSION['language']) === 'en' || strtolower($_SESSION['language']) === 'cy') {
                $this->saveLanguage($_SESSION['language']);
            } else {
                // If it's not English or Welsh, maybe it's been tampered with? Set to English.
                $this->saveLanguage('en');
            }
        } else {
            // If the language isn't defined, can we autodetect it based on the user's browser?
            if (strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2)) === 'cy') {
                $this->saveLanguage('cy');
            } else {
                // Anything other than Welsh
                $this->saveLanguage('en');
            }
        }

        // Manually changing the language
        if (isset($_GET['language'])) {
            // But again, only if it's English or Welsh.
            if (strtolower($_GET['language']) === 'cy') {
                $this->saveLanguage('cy');
            } else {
                // Any cheeky business defaults to English.
                $this->saveLanguage('en');
            }
        }
    }

    private function saveLanguage($language) {
        $this->language = $language;
        $_SESSION['language'] = $language;
    }
}
