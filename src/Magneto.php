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
        // CSRF Watchdog
        $this->csrfWatchdog();

        // Retrieve and other services
        $this->retriever->watchdog();

        // Router Templater Hybrid
        $this->renderer->route();
    }

    private function csrfWatchdog()
    {
        if (empty($_SESSION['token'])) {
            $_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
        }

        if (!empty($_POST)) {
            if (empty($_POST['csrf_check']) || !hash_equals($_POST['csrf_check'], $_SESSION['token'])) {
                Magneto::error('csrf_violation', 'CSRF Violation');
            }
        }
    }

    private static function configureTimezone()
    {
        date_default_timezone_set('Europe/London');
    }

    private static function configureErrors()
    {
        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);
        error_reporting(0);

        if (Config::getConfigEntry('debug')) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
        }

        try
        {
            self::$logger = new Logger('logger');
            self::$logger->pushHandler(new StreamHandler(
              'storage/logs/errors.log',
                Logger::ERROR));
        }
        catch (Exception $exception)
        {
            self::error('logger_failure', $exception);
        }
    }

    private static function configureSession()
    {
        if(!isset($_SESSION))
        {
            session_start();
        }
    }

    public static function sanitiseInt($data)
    {
        return strip_tags(filter_var($data, FILTER_SANITIZE_NUMBER_INT));
    }

    public static function error($description, $exception)
    {
        try
        {
            if ($description === 'csrf_violation') {
                new Exception('CSRF violation');
            }
            $locale = Locale::getLocale();
            $localisedException = $description;
            if (isset($locale[$description])) {
                $localisedException = $locale[$description];
            }
            $_SESSION['errors'][] = $localisedException;
            self::logError("{$localisedException} {$exception}");
        }
        catch (Exception $exception)
        {
            trigger_error("Unrecoverable error. Please contact developer. <br>Exception: {$exception}",
                E_USER_ERROR);
        }

    }

    private static function logError($error) {
        self::$logger->error($error);
    }
}
