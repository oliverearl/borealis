<?php
namespace ole4\Magneto;

use Exception;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use ole4\Magneto\i18n\Locale;
use ole4\Magneto\Database\Connector;
use ole4\Magneto\Config\Config;

/**
 * Class Magneto
 * @package ole4\Magneto
 *
 * The main application class. This class contains the bulk of the program's behaviour, including special
 * helper methods: validation, CSRF protection, error handling.
 *
 * It is also where important program routines are initialised. Namely the Renderer and Retriever.
 */
class Magneto
{
    /**
     * Program singleton instance
     * @var Magneto
     */
    private static $instance;

    /**
     * Logging library instance
     * @var Logger
     */
    private static $logger;

    /**
     * Config array
     * @var array|null
     */
    private $config;

    /**
     * Current program language
     * @var string
     */
    private $currentLanguage;

    /**
     * Loaded program locale
     * @var array
     */
    private $locale;

    /**
     * Database instance
     * @var \PDO
     */
    private $database;

    /**
     * Application Rendering and Routing Engine
     * @var Renderer
     */
    private $renderer;

    /**
     * Magnetometer Retriever
     * @var Retriever
     */
    private $retriever;


    /**
     * Init - Bootstrapping Method
     * @return Magneto
     *
     * This static method is the primary entry-point for the program and launches crucial initial services
     * before the program begins its primary routines. As an application singleton, it returns itself
     * if it has already launched, otherwise it instantiates a new object, which carries out the bulk of
     * program setting up.
     */
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

    /**
     * Magneto constructor.
     * The construction of a Magneto object begins setting up other helper aspects of the program, such
     * as the program language and locale, the configuration file, database instance, rendering and routing
     * services, and the magnetometer retriever.
     *
     * From then, it opens the start() method, which is where primary subroutines are called.
     */
    private function __construct()
    {
        $this->config =             Config::getConfig();
        $this->currentLanguage =    Locale::getLanguage();
        $this->locale =             Locale::getLocale();
        $this->database =           Connector::getInstance();

        $this->renderer =           new Renderer();
        $this->retriever =          Retriever::getInstance();

        $this->start();
    }

    /**
     * Start
     * This method contains calls to other important methods throughout the program. The first call it makes
     * is to the CSRF watchdog, before starting up the Retriever. Lastly, the Renderer/Router functionality
     * is brought online.
     */
    private function start()
    {
        // CSRF Watchdog
        $this->csrfWatchdog();

        // Retrieve and other services
        $this->retriever->watchdog();

        // Router Templater Hybrid
        $this->renderer->route();
    }

    /**
     * CSRF Watchdog
     * This method is responsible for protecting the program against CSRF (cross-site request forgery)
     * attacks. It first checks the user's session to see whether there is a token there, if there isn't, it
     * generates a new 32-bit cryptographically secure token that is used program-wide.
     *
     * If there is POST data, it will check if this token exists for the user in their form data. If for
     * whatever reason there is a mismatch, or if the CSRF token is missing from their form, the program will
     * hang.
     */
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

    /**
     * Configure Timezone
     * This method configures the timezone to Europe/London. Many frameworks and libraries will refuse
     * to run without first having the default timezone set.
     */
    private static function configureTimezone()
    {
        date_default_timezone_set('Europe/London');
    }

    /**
     * Configure Errors
     * Error reporting behaviour, debug mode behaviour, and error logging are handled in this method.
     *
     * If the debug flag is set, errors will be printed to the screen as and when they occur in all of their
     * detail. If in production mode, they are suppressed. Regardless, errors are still logged.
     */
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

    /**
     * Configure Session
     * If the user does not have a session active, this method will start one.
     */
    private static function configureSession()
    {
        if(!isset($_SESSION))
        {
            session_start();
        }
    }

    /**
     * Sanitise Integer
     * @param $data
     * @return integer|null
     * This method checks to see whether an integer is a valid integer and performs some basic sanitisation.
     * If it isn't, null is returned.
     */
    public static function sanitiseInt($data)
    {
        return strip_tags(filter_var($data, FILTER_SANITIZE_NUMBER_INT));
    }

    /**
     * Error Handler
     * @param $description
     * @param $exception
     * If there is a CSRF violation, an exception is thrown which causes the program to hang.
     *
     * Otherwise, the error handler attempts to use the Locale class to provide localised error messages, but
     * if it is not successful, default error messages passed to the method will be used.
     *
     * Errors will be added to the session array, causing Bootstrap alerts to be displayed on the website.
     * Additionally, errors will be logged to the error logfile.
     *
     * Exceptions caught by the error function will intentionally cause an unrecoverable error.
     */
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

    /**
     * Error Logger
     * @param $error
     * Errors are logged to the error.log logfile using this method.
     */
    private static function logError($error) {
        self::$logger->error($error);
    }
}
