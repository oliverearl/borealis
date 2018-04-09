<?php
namespace ole4\Magneto;

use Monolog\Logger;

use ole4\Magneto\i18n\Locale;
use ole4\Magneto\Database\Connector;
use ole4\Magneto\Config\Config;

class Magneto
{
    private static $instance;
    private $config;
    private $currentLanguage;
    private $locale;
    private $database;
    private $logger;

    public static function init()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Magneto();
        }
        return self::$instance;
    }

    private function __construct()
    {
        session_start();
        $this->config =             Config::getConfig();
        $this->currentLanguage =    Locale::getLanguage();
        $this->locale =             Locale::getLocale();
        $this->database =           Connector::getInstance();
        $this->logger =             new Logger('logger');
        $this->hello();
    }

    private function hello()
    {
        echo '<h1>Hello World</h1><hr>';
        var_dump($this);
    }

}
