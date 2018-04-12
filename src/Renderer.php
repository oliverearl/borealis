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
 */
class Renderer
{
    private $twigEnv;
    private $twig;
    private $magnetometer;

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

    public function route()
    {
        if (isset($_GET['page'])) {
            switch(strtolower($_GET['page'])) {
                case 'settings':
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
                    $this->loadPage('graph', [
                        'entries'   => $entries,
                        'objects'   => $objects,
                        'json'      => $graphJson
                    ]);
                    break;
                case 'all':
                    $objects =      $this->magnetometer->getAll();
                    $entries =      $this->magnetometer->getIdsFromObjectsArray($objects);
                    $graphJson =    $this->magnetometer->getGraphJsonFromObjects($objects);
                    $this->loadPage('graph', [
                       'objects'    => $objects,
                       'entries'    => $entries,
                       'json'       => $graphJson
                    ]);
                    break;
                case 'latest':
                    $objects =       $this->magnetometer->getLatest();
                    $entries =       $this->magnetometer->getIdsFromObjectsArray($objects);
                    $graphJson =     $this->magnetometer->getGraphJsonFromObjects($objects);
                    $this->loadPage('graph', [
                        'objects'   => $objects,
                        'entries'   => $entries,
                        'json'      => $graphJson
                    ]);
                    break;
                case 'table':
                    $entries = $this->additionalData();
                    $objects = $this->magnetometer->getObjectsFromIds($entries);
                    $this->loadPage('table', [
                        'entries'   => $entries,
                        'objects'   => $objects
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

    private function loadPage($page, $params = [])
    {
        try {
            echo $this->twig->render("{$page}.php.twig", $params);
        } catch (Twig_Error $exception) {
            Magneto::error('Renderer Error', $exception);
        }
    }

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

        if (isset($testingArray['number']) && is_numeric($testingArray)) {
            $iterate = $testingArray['number'];
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

    private function registerGlobals()
    {
        $this->twig->addGlobal('config', Config::getConfig());
        $this->twig->addGlobal('database', Connector::getInstance());
        $this->twig->addGlobal('language', Locale::getLanguage());
        $this->twig->addGlobal('locale', Locale::getLocale());
    }

}
