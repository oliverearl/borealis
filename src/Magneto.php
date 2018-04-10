<?php
namespace ole4\Magneto;

use Monolog\Logger;

use ole4\Magneto\i18n\Locale;
use ole4\Magneto\Database\Connector;
use ole4\Magneto\Config\Config;
use ole4\Magneto\Renderer;

class Magneto
{
    private static $instance;

    private $config;
    private $currentLanguage;
    private $locale;
    private $database;
    private $renderer;
    private $logger;

    public static function init()
    {
        session_start();
        if (!isset(self::$instance)) {
            self::$instance = new Magneto();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->config =             Config::getConfig();
        $this->currentLanguage =    Locale::getLanguage();
        $this->locale =             Locale::getLocale();
        $this->database =           Connector::getInstance();
        $this->renderer =           new Renderer();
        $this->logger =             new Logger('logger');

        $this->start();
    }

    private function start()
    {
        $this->renderer->route();
    }

    public static function sanitiseInt($data)
    {
        return strip_tags(filter_var($data, FILTER_SANITIZE_NUMBER_INT));
    }

    public static function error($description, $exception)
    {
        if (Config::getConfigEntry('debug')) {
            trigger_error("{$description} {$exception}", E_USER_ERROR);
        } else {
            echo 'An error has occurred. Please contact the webmaster.';
        }
        die();
    }

}
