<?php
namespace ole4\Magneto;

use Icewind\SMB\Server;

use ole4\Magneto\Config\Config;
use ole4\Magneto\Controllers\MagnetometerController;
use ole4\Magneto\Models\Magnetometer;

class Retriever
{
    private static $instance;
    private $server;
    private $share;

    private $host;
    private $username;
    private $password;
    private $shareName;

    public static function getInstance()
    {
        if (isset(self::$instance)) {
            return self::$instance;
        }

        if (self::retrievalWatchdog()) {
            self::$instance = new Retriever();
            return self::$instance;
        }

        return null;
    }
    public function __construct()
    {
        $this->host =       Config::getConfigEntry('magnetometer_hostname');
        $this->username =   Config::getConfigEntry('magnetometer_username');
        $this->password =   Config::getConfigEntry('magnetometer_password');
        $this->shareName =  Config::getConfigEntry('magnetometer_share');

        // This is not a poltergeist class! The Retriever handles most of the interaction with the Server object.
        $this->server = new Server($this->host, $this->username, $this->password);
        $this->share = $this->server->getShare($this->shareName);
        $this->retrieveData();
    }

    private function listAllFiles()
    {
        $content = $this->share->dir('2018');

        foreach ($content as $info) {
            echo $info->getName();
            echo $info->getMTime();
            echo '<br>';
        }
    }

    public function retrieveData($day = null)
    {
        if (is_null($day)) {
            $day = (date('z') + 1);
        }
        $year = date('Y');
        $file = $this->share->read("{$year}/DATA{$day}");

        var_dump($file);
        die();
    }

    private static function retrievalWatchdog()
    {
        return true;
        // We should check whether or not a connection to the magnetometer is required
        // First, let's grab a magnetometer controller
        $controller = new MagnetometerController();

        // With this in mind, let's grab the latest magnetometer entry
        // The method returns an array remember, so grab the object from the array
        $pop = $controller->getLatest();
        $latestEntry = array_pop($pop);

        // We need the timestamp from this object
        $latestTimestamp = strtotime($latestEntry->getTimestamp());

        // And now grab the day of the year from it
        // https://www.epochconverter.com/daynumbers
        $latestYearDay = (date('z', $latestTimestamp) + 1);


    }
}
