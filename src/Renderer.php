<?php
namespace ole4\Magneto;

use Twig_Loader_Filesystem;
use Twig_Environment;
use Twig_Filter_Function; // Deprecated
use Twig_Error;

use ole4\Magneto\Config\Config;
use ole4\Magneto\Database\Connector;
use ole4\Magneto\i18n\Locale;

/**
 * Class Renderer
 * @package ole4\Magneto
 * @author Oliver Earl <ole4@aber.ac.uk>
 */
class Renderer
{
    private $twigEnv;
    private $twig;
    const TEMPLATE_DIR = __DIR__ . '/../templates';

    /**
     * Renderer constructor.
     *
     * var_dump Twig Filter:
     * Adding the var_dump filter is a clever workaround for debugging and dumping variable contents from within views.
     * Twig 2.x. includes this behaviour built in, but since we are working with PHP 5.4 we're limited to the first
     * major revision of Twig that lacks this. This means we can use it like "{{ foo | var_dump }}" to view the contents
     * of foo. Naturally due to the implications of dumping variable contents to the browser we only want this in debug
     * mode. (Potentially very dangerous if one was to dump the contents of the database instance, for example.)
     */
    public function __construct()
    {
        $this->twigEnv = new Twig_Loader_Filesystem($this::TEMPLATE_DIR);
        $this->twig = new Twig_Environment($this->twigEnv);
        if (Config::getConfigEntry('debug')) {
            $this->twig->addFilter('var_dump', new Twig_Filter_Function('var_dump'));
        }

        $this->registerGlobals();
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
                case 'graph':
                    $this->loadPage('graph', array('data' => $this->additionalData()));
                    break;
                case 'table':
                    $this->loadPage('table', array('data' => $this->additionalData()));
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

        if (isset($_POST['values']) && ($_POST['values'] <= $max)) {
            $testingArray = $_POST;
        } elseif (isset($_GET['values']) && ($_GET['values'] <= $max)) {
            $testingArray = $_GET;
        } else {
            return $newArray;
        }

        for ($i = 0; $i < $max; $i++) {
            if (isset($testingArray["values_{$i}"])) {
                if (Magneto::sanitiseInt($testingArray["values_{$i}"])) {
                    array_push($newArray, $testingArray["values_{$i}"]);
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
