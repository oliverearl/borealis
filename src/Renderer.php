<?php
namespace ole4\Magneto;

use Twig_Loader_Filesystem;
use Twig_Environment;
use Twig_Filter_Function; // Deprecated
use Twig_Error;

use ole4\Magneto\Config\Config;
use ole4\Magneto\Database\Connector;
use ole4\Magneto\i18n\Locale;
use ole4\Magneto\Controllers\MagnetometerController;

/**
 * Class Renderer
 * @package ole4\Magneto
 * @author Oliver Earl <ole4@aber.ac.uk>
 *
 * The Renderer class is a large God-class that consists of hybrid rendering and routing functionality.
 * While an unusual decision, it was to ensure that data could properly be injected into the Twig view
 * and separate concerns properly depending on the webpage and on GET/POST data.
 *
 * This class contains numerous methods pertaining to rendering and Twig, routing HTTP requests, and handling
 * additional GET/POST data.
 */
class Renderer
{
    /**
     * Twig (1/2)
     * @var Twig_Loader_Filesystem
     */
    private $twigEnv;

    /**
     * Twig (2/2)
     * @var Twig_Environment
     */
    private $twig;

    /**
     * MagnetometerController for retrieving specially crafted data from the database.
     * @var MagnetometerController
     */
    private $magnetometer;

    /**
     * Template Directory filepath
     */
    const TEMPLATE_DIR = __DIR__ . '/../templates';

    /**
     * Renderer constructor.
     *
     * var_dump Twig Filter:
     * Adding the var_dump filter is a clever workaround for debugging and dumping variable contents from within views.
     * From Twig ^1.5.x this behaviour is built in[1], but since we are working with PHP 5.4 we're limited to the first
     * Twig[2], that lacks this. With this workaround we can use "{{ foo | var_dump }}" to view the contents of foo.
     * Naturally due to the implications of dumping variable contents to the browser we need to make sure in templates
     * that it is only used in debug mode. If it were enforced here, it would cause an unknown filter exception.
     * (Potentially very dangerous if one was to dump the contents of the database/config instances, for example.)
     * Credit: https://stackoverflow.com/questions/7317438/how-to-var-dump-variables-in-twig-templates
     * [1]: https://twig.symfony.com/doc/1.x/functions/dump.html (Says 1.x, but only available from ^1.5.x)
     * [2]: https://twig.symfony.com/doc/2.x/intro.html
     */
    public function __construct()
    {
        $this->twigEnv = new Twig_Loader_Filesystem($this::TEMPLATE_DIR);
        $this->twig = new Twig_Environment($this->twigEnv);
        $this->twig->addFilter('var_dump', new Twig_Filter_Function('var_dump'));

        $this->registerGlobals();
        $this->magnetometer = new MagnetometerController();
    }

    /**
     * Routing Mega Method
     * This method has two primary responsibilites. Switch on the GET['page'] value, and then in each
     * appropriate case inject the required data and instruct Twig to render the page. If no GET['page'] is
     * found then the homepage is loaded. This is probably one of the neatest ways to get around the lack of
     * a rewrite engine and pretty URLs.
     *
     * For example, if the user navigates to GET['page'] = graph, a lot of additional data will be loaded.
     * Most of this data is harvested from the additionalData() sister method before using its findings
     * to do database enquiries.
     */
    public function route()
    {
        if (isset($_GET['page'])) {
            switch(strtolower($_GET['page'])) {
                case 'settings':
                    $settings =     $this->settingsWatchdog();
                    $this->loadPage('settings');
                    break;
                case 'about':
                    $this->loadPage('about');
                    break;
                case 'navigator':
                    $this->loadPage('navigator', [
                        'objects'   => $this->magnetometer->getAll(),
                    ]);
                    break;
                case 'graph':
                    $entries =      $this->additionalData();
                    $objects =      $this->magnetometer->getObjectsFromIds($entries);
                    $graphJson =    $this->magnetometer->getGraphJsonFromObjects($objects);
                    $apiPaths =     $this->apiPathBuilder($entries);
                    $this->loadPage('graph', [
                        'entries'   => $entries,
                        'objects'   => $objects,
                        'json'      => $graphJson,
                        'jsonApi'   => $apiPaths,
                        'xmlApi'    => $apiPaths . 'xml=true'
                    ]);
                    break;
                case 'table':
                    $entries =  $this->additionalData();
                    $objects =  $this->magnetometer->getObjectsFromIds($entries);
                    $this->loadPage('table', [
                        'entries'   => $entries,
                        'objects'   => $objects,
                    ]);
                    break;
                case 'all':
                    $objects =      $this->magnetometer->getAll();
                    $entries =      $this->magnetometer->getIdsFromObjectsArray($objects);
                    $graphJson =    $this->magnetometer->getGraphJsonFromObjects($objects);
                    $apiPaths =     $this->apiPathBuilder($entries);
                    $this->loadPage('graph', [
                       'objects'    => $objects,
                       'entries'    => $entries,
                       'json'       => $graphJson,
                       'jsonApi'   => $apiPaths,
                       'xmlApi'    => $apiPaths . 'xml=true'
                    ]);
                    break;
                case 'latest':
                    $objects =       $this->magnetometer->getLatest();
                    $entries =       $this->magnetometer->getIdsFromObjectsArray($objects);
                    $graphJson =     $this->magnetometer->getGraphJsonFromObjects($objects);
                    $apiPaths =      $this->apiPathBuilder($entries);
                    $this->loadPage('graph', [
                        'objects'   => $objects,
                        'entries'   => $entries,
                        'json'      => $graphJson,
                        'jsonApi'   => $apiPaths,
                        'xmlApi'    => $apiPaths . 'xml=true'
                    ]);
                    break;
                case 'home':
                default:
                    $this->loadPage('index');
            }
        } else {
            $this->loadPage('index');
        }
    }

    /**
     * Load Page
     * @param $page
     * @param array $params
     * This method is a wrapper method for the Twig render function. It will load
     * the specified Twig template file, and pass an array as parameters to inject
     * into the view. The Bootstrap alerts are then reset.
     *
     * If an error occurs, the error handler is called.
     */
    private function loadPage($page, $params = [])
    {
        try
        {
            echo $this->twig->render("{$page}.php.twig", $params);
            $this->resetSession();
        }
        catch (Twig_Error $exception)
        {
            Magneto::error('renderer_failure', $exception);
        }
    }

    /**
     * Additional Data
     * @return array
     *
     * This large method determines whether or not there exists additional data stored in either POST or GET.
     * When data is found in either POST or GET, and under the maximum allowed values, it is then iterated
     * through the defined number of times which builds an array of sanitised IDs. These IDs are then used
     * back in the route() method for firing off calls to other methods using the IDs as parameters for
     * Magnetometer object retrieval, etc.
     *
     * If no data is provided, then a blank array is returned.
     */
    private function additionalData()
    {
        $testingArray = [];
        $newArray = [];
        $max = Config::getConfigEntry('maxElements');
        $iterate = $max;
        if (isset($_POST['values']) && isset($_POST['number']) && ($_POST['number'] < $max)) {
            $testingArray = $_POST;
        } elseif (isset($_GET['values']) && isset($_GET['number']) && ($_GET['number'] < $max)) {
            $testingArray = $_GET;
        } else {
            // Why did we open graph/table without data? No problem.
            return $newArray;
        }

        if (isset($testingArray['number']) && is_numeric($testingArray['number'])) {
            $iterate = ($testingArray['number'] + 1);
        }

        for ($i = 0; $i < $iterate; $i++) {
            if (isset($testingArray['values'][$i])) {
                if (Magneto::sanitiseInt($testingArray['values'][$i])) {
                    array_push($newArray, $testingArray['values'][$i]);
                }
            }
        }
        return $newArray;
    }

    /**
     * Register Globals
     * Twig has the ability to register global variables (not true globals, only global in a Twig context)
     * that are available in all views and do not need to be painstakingly injected in each switch case.
     *
     * The data injected includes the config, database instance, language and locale, the CSRF token, and
     * any accumulated errors and success dialogues that are to be rendered into Bootstrap alerts.
     */
    private function registerGlobals()
    {
        $this->twig->addGlobal('config', Config::getConfig());
        $this->twig->addGlobal('database', Connector::getInstance());
        $this->twig->addGlobal('language', Locale::getLanguage());
        $this->twig->addGlobal('locale', Locale::getLocale());
        $this->twig->addGlobal('csrf', $_SESSION['token']);

        $this->twig->addGlobal('errors', $this->addSession('errors'));
        $this->twig->addGlobal('successes', $this->addSession('successes'));
    }

    /**
     * Add Session
     * @param $session
     * @return array|null
     * This method is used to add session data, either successes or errors, to the view without the need for
     * multiple methods.
     * If it is called and there is nothing for it to do, it returns null.
     */
    private function addSession($session)
    {
        if (isset($_SESSION[$session])) {
            $storage = $_SESSION[$session];
            return $storage;
        }
        else return null;
    }

    /**
     * Reset Session
     * The application uses a small timer to determine how long success and error messages should be
     * displayed for to ensure that they are displayed at least once. This method records the current
     * time if it is not present, and unsets it once the time has expired.
     */
    private function resetSession()
    {
        if (isset($_SESSION['errors']) || isset($_SESSION['successes'])) {
            if (isset($_SESSION['timer'])) {
                if ($_SESSION['timer'] !== time()) {
                    unset($_SESSION['errors'], $_SESSION['successes'], $_SESSION['timer']);
                }
            } else {
                $_SESSION['timer'] = time();
            }
        }
    }

    /**
     * API Path Builder
     * @param $ids
     * @return string
     * TODO: Fix URI-Too-Long Bug
     * The API path builder builds a long URL that is injected into the view in order to enable the
     * Export JSON/XML functionality.
     */
    private function apiPathBuilder($ids)
    {
        $pathToApi = 'api.php?';
        $i = 0;
        foreach ($ids as $id) {
            $pathToApi .= "values[{$i}]={$id}&";
            $i++;
        }
        return $pathToApi;
    }

    /**
     * Settings Watchdog
     * @return bool|null
     *
     * This method watches for $_POST data containing maxElements. If it is valid input, the config
     * will be changed and a success notification added. On failure, an error.
     * Returns true on success, null on failure.
     */
    private function settingsWatchdog()
    {
        if (isset($_POST['maxElements']) && is_numeric($_POST['maxElements'])) {
            if ($_POST['maxElements'] > 0 && $_POST['maxElements'] <= 100) {
                $_SESSION['successes'][] = 'setting_changed';
                Config::updateConfigEntry('maxElements', $_POST['maxElements']);
                return true;
            }
            $_SESSION['errors'][] = 'settingFail';
            return null;
        }
    }
}
