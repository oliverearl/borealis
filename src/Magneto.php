<?php
namespace ole4\Magneto;

use Exception;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use ole4\Magneto\i18n\Locale;
use ole4\Magneto\Database\Connector;
use ole4\Magneto\Config\Config;

class Magneto
{
    private static $instance;
    private static $logger;

    private $config;
    private $currentLanguage;
    private $locale;
    private $database;
    private $renderer;
    private $retriever;


    public static function init()
    {

        self::configureTimezone();
        self::configureErrors();
        self::configureSession();

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
        $this->retriever =          new Retriever();

        $this->start();
    }

    private function start()
    {
        // Router Templater Hybrid
        $this->renderer->route();

        // Retrieve and other services
        $this->retriever->watchdog();
    }

    private static function configureTimezone()
    {
        date_default_timezone_set('Europe/London');
    }

    private static function configureErrors()
    {
        if (Config::getConfigEntry('debug')) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
        }

        try {
            self::$logger = new Logger('logger');
            self::$logger->pushHandler(new StreamHandler(__DIR__ . '../storage/logs/errors.log', Logger::ERROR));
        } catch (Exception $exception) {
            self::error('Error Logging Failure', $exception);
        }
    }

    private static function configureSession()
    {
        if(!isset($_SESSION))
        {
            session_start();
            if (isset($_SESSION['error'])) {
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['success'])) {
                unset($_SESSION['success']);
            }
        }
    }

    public static function sanitiseInt($data)
    {
        return strip_tags(filter_var($data, FILTER_SANITIZE_NUMBER_INT));
    }

    public static function error($description, $exception)
    {
        if (Config::getConfigEntry( 'debug')) {
            trigger_error("{$description} {$exception}", E_USER_ERROR);
        } else {
            echo 'Fatal Error - Information not displayed due to Production mode. Please contact developer.';
        }
        self::logError("{$description} {$exception}");
        die();
    }

    private static function logError($error) {
        self::$logger->error($error);
    }

}
