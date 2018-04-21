<?php
namespace ole4\Magneto;

use Exception;

use Icewind\SMB\Server;

use ole4\Magneto\Config\Config;
use ole4\Magneto\Controllers\MagnetometerController;
use ole4\Magneto\Models\Magnetometer;

class Retriever
{
    const TEMP_FILE = __DIR__ . '/../storage/csv/temp.csv';

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

    public function retrieveData($day = null, $year = null)
    {
        try {
            if (is_null($day)) {
                $day = sprintf('%03d', date('z'));
            }
            if ($day === '000') {
                $day = '001'; // The magnetometer counts from 1... strange case for January 1st
            }
            if (is_null($year)) {
                $year = date('Y');
            }

            $name = "DATA{$day}.csv";
            $tempFile = self::TEMP_FILE;
            $dir = $this->share->dir($year);

            foreach ($dir as $file) {
                if ($file->getName() === $name) {
                    $this->share->get($file->getPath(), $tempFile);
                    return $this->newProcessData($tempFile);
                }
            }
            return null;
        } catch (Exception $exception) {
            // Log failure to connect to magnetometer
            self::$instance = null;
            return null;
        }
    }

    private function processData($target) {
        // Need to go through file line-by-line
        if (!file_exists($target)) {
            return null;
        }
        $entries = array_map('str_getcsv', file($target));
        foreach ($entries as $entry) {
            $formattedDate = date('Y-m-d H:i:s', Magnetometer::convertToUnix($entry[0]));
            $magnetometer = new Magnetometer(
                null,
                $formattedDate, // Date
                $entry[1], // Value
                $entry[2], // Temperature
                null
            );
            // Save it
            $magnetometer->saveMagnetometer();
        }
        return true;
    }

    private function newProcessData($target) {
        try {
            if (!file_exists($target)) {
                return null;
            }

            $date = null;
            $values = [];
            $temps = [];

            $entries = array_map('str_getcsv', file($target));
            foreach ($entries as $entry) {
                $date = $entry[0];
                array_push($values, $entry[1]);
                array_push($temps, $entry[2]);
            }

            // Correctly convert and format the timestamp from labView to UNIX and into a date
            $formattedDate = date('Y-m-d H:i:s', Magnetometer::convertToUnix($date));

            // Remove any blanks so that averages aren't poisoned, and then find the average in the arrays
            // https://stackoverflow.com/questions/33461430/how-to-find-average-from-array-in-php
            $values =       array_filter($values);
            $averageValue = array_sum($values) / count($values);

            $temps =        array_filter($temps);
            $averageTemp =  array_sum($temps) / count($temps);

            // Write to database
            $magnetometer = new Magnetometer(
                null,
                $formattedDate,
                $averageValue,
                $averageTemp,
                null
            );
            $magnetometer->saveMagnetometer();
            unlink($target);

            return true;
        } catch (Exception $exception) {
            return null;
        }
    }
}