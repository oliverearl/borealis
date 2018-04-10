<?php
namespace ole4\Magneto;

use Twig_Loader_Filesystem;
use Twig_Environment;
use Twig_Error;
use Twig_Filter_Function;

use ole4\Magneto\Config\Config;
use ole4\Magneto\Database\Connector;
use ole4\Magneto\i18n\Locale;

class Renderer
{
    private $twigEnv;
    private $twig;
    const TEMPLATE_DIR = __DIR__ . '/../templates';

    public function __construct()
    {
        $this->twigEnv = new Twig_Loader_Filesystem($this::TEMPLATE_DIR);
        $this->twig = new Twig_Environment($this->twigEnv);
        $this->twig->addFilter('var_dump', new Twig_Filter_Function('var_dump'));
        $this->registerGlobals();
    }

    private function registerGlobals() {
        $this->twig->addGlobal('config', Config::getConfig());
        $this->twig->addGlobal('database', Connector::getInstance());
        $this->twig->addGlobal('language', Locale::getLanguage());
        $this->twig->addGlobal('locale', Locale::getLocale());
    }

    public function route()
    {
        try {
            echo $this->twig->render('index.php.twig');
        } catch (Twig_Error $ex) {
            trigger_error("Templating error $ex", E_USER_ERROR);
        }
    }

}
