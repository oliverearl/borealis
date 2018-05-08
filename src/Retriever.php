<?php
namespace ole4\Magneto;

use Exception;

use Icewind\SMB\Server;

use ole4\Magneto\Config\Config;
use ole4\Magneto\Controllers\MagnetometerController;
use ole4\Magneto\Models\Magnetometer;

/**
 * Class Retriever
 * @package ole4\Magneto
 * @author Oliver Earl <ole4@aber.ac.uk>
 *
 * This Retriever class is responsible for leveraging the SMB third-party library to establish connections
 * to the magnetometer, retrieve data, process CSV files, save data, etc. Detailed algorithmic information
 * is available in the PHPDoc blocks for each method.
 *
 */
class Retriever
{
    /**
     * Temporary file used to store CSV data during processing
     */
    const TEMP_FILE = __DIR__ . '/../storage/csv/temp.csv';
    /**
     * Filepath to magnetometer logfile
     */
    const LOG_FILE = __DIR__ . '/../storage/logs/magnetometer/magnetometer.log';

    /**
     * Instance
     * @var Retriever
     */
    private static $instance;

    /**
     * SMB Library (1/2)
     * @var Server
     */
    private $server;

    /**
     * SMB Library (2/2)
     * @var \Icewind\SMB\IShare
     */
    private $share;

    /**
     * Hostname
     * @var null|string
     */
    private $host;

    /**
     * Username
     * @var null|string
     */
    private $username;

    /**
     * Password
     * @var null|string
     */
    private $password;

    /**
     * Share name, usually 'magdata'
     * @var null|string
     */
    private $shareName;

    /**
     * Date of last retrieval - 3 digit number with trailing zeroes
     * @var null|string
     */
    private $lastRetrieval;

    /**
     * Get Instance
     * @return Retriever
     * Singleton Instance method. Either constructs an instance of itself or returns the already existing
     * instance.
     */
    public static function getInstance()
    {
        if (isset(self::$instance)) {
            return self::$instance;
        }
            self::$instance = new Retriever();
            return self::$instance;
    }

    /**
     * Retriever constructor.
     * Constructs new SMB Server and Share using details provided in the config.
     * Very important to fill information in properly into settings.php.
     */
    private function __construct()
    {
        $this->host =           Config::getConfigEntry('magnetometer_hostname');
        $this->username =       Config::getConfigEntry('magnetometer_username');
        $this->password =       Config::getConfigEntry('magnetometer_password');
        $this->shareName =      Config::getConfigEntry('magnetometer_share');
        $this->lastRetrieval =  Config::getConfigEntry('magnetometer_latest');

        // This is not a poltergeist class! The Retriever handles most of the interaction with the Server object.
        $this->server = new Server($this->host, $this->username, $this->password);
        $this->share = $this->server->getShare($this->shareName);
    }

    /**
     * Watchdog
     * TODO: Fix bug with indeterminate or repeated retrieval of data
     * The watchdog method determines whether or not the program should attempt to retrieve data from the
     * magnetometer. It determines the date, and if that date is different to the one it is currently storing,
     * or if an override flag has been sent by the user, it starts retrieving data.
     *
     * After data has been retrieved and set, the last retrieval date will be set to today's date.
     *
     * Should anything go wrong and nothing be returned from the Retrieve Data function, then an error is
     * displayed as a Bootstrap alert. The program can continue to function even if data retrieval goes pearshaped.
     */
    public function watchdog()
    {
        $date = sprintf('%03d', date('z') + 1);
        if ($date !== $this->lastRetrieval || (isset($_GET['override']) && $_GET['override'] === 'true')) {
            $result = $this->retrieveData();
            $this->lastRetrieval = $date;
            if (is_null($result)) {
                if (isset($_GET['override'])) {
                    Magneto::error('override_failure', 'Override unsuccessful. Records missing or already existing.');
                } else {
                    Magneto::error('magnetometer_failure', "Today's magnetometer entry cannot be found.");
                }
            }
        }
    }

    /**
     * Set Retrieval
     * @param $date
     * Updates the config entry of the latest date of magnetometer date retrieval with the
     * provided parameter.
     */
    public function setRetrieval($date)
    {
        Config::updateConfigEntry('magnetometer_latest', $date);
    }

    /**
     * Retrieve Data
     * @param null $day
     * @param null $year
     * @return bool|null
     * This method accepts optional parameters - day and a year. If they are not used, then today's day
     * and year are used instead.
     *
     * The method attempts to find a file with the provided day inside the folder of the provided year.
     * If it is found, the file is retrieved and copied into temporary storage where the Process Data method
     * is then called on it with its filepath as the parameter.
     *
     * Errors result in the error handler being called with specific details.
     */
    public function retrieveData($day = null, $year = null)
    {
        try {
            if (is_null($day)) {
                $day = sprintf('%03d', date('z') + 1);
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
                    return $this->processData($tempFile);
                }
            }
            $_SESSION['errors'][] = 'record_missing';
            return null;
        } catch (Exception $exception) {
            Magneto::error('magentometer_failure', $exception);
            self::$instance = null;
            return null;
        }
    }

    /**
     * Previous Process Data Algorithm
     * @param $target
     * @return bool|null
     * @deprecated
     * Previous algorithm used to retrieve data from the database. This algorithm does not find averages
     * of data and instead attempts to add each line of the CSV as a new Magnetometer object to the
     * database.
     */
    private function oldProcessData($target) {
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

    /**
     * Process Data Algorithm
     * @param $target
     * @return bool|null
     *
     * This method takes a filepath to the temporary CSV file as its argument. If it isn't there, the method
     * quits early and returns null, otherwise it maps the entire contents of the file into memory; into an
     * associative array. From here, the array is iterated through whilst pushing the value and temperature
     * values respectively into two new arrays, along with a copy of the date.
     *
     * The date is properly formatted into a standard date string following lengthy conversion, and the values
     * and temperature values harvested and stored into arrays are used to find the average values. These
     * average values are then used to construct a brand new Magnetometer object, along with the date. The
     * optional ID and LastModified properties are left with null. Finally, the method saves it to the
     * database, records some information about the process to a logfile, and deletes the temp CSV file.
     */
    private function processData($target) {
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

            $controller = new MagnetometerController();
            $result = $controller->getByDate($magnetometer->getTimestamp());

            if (!is_null($result)) {
                $_SESSION['errors'][] = 'record_exists';
                unlink($target);
                return null;
            }

            $magnetometer->saveMagnetometer();

            $_SESSION['successes'][] = 'record_retrieved';
            $this->log("Record {$formattedDate} retrieved.");
            $this->setRetrieval($date);

            unlink($target);
            return true;
        } catch (Exception $exception) {
            Magneto::error('magnetometer_failure', $exception);
            return null;
        }
    }

    /**
     * Adds a log to the Magnetometer logfile, the old fashioned way
     * @param $status
     */
    private function log($status)
    {
        $date = date('d-m-Y H:m:s');
        $toPrint = "{$date}: Magnetometer: {$status} \n\n";
        file_put_contents(self::LOG_FILE, $toPrint, FILE_APPEND);
    }
}
