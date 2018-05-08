<?php
require_once 'vendor/autoload.php';

use ole4\Magneto\Config\Config;
use ole4\Magneto\Database\Connector;
use ole4\Magneto\Controllers\MagnetometerController;

if (isset($_GET)) {
    $api = new API($_GET);
} elseif (isset($_POST)) {
    $api = new API($_POST);
} else {
    http_response_code(400);
}

/**
 * Class API
 * @author Oliver Earl <ole4@aber.ac.uk>
 * This is the API class, responsible for allowing programmatic access to the web application and serves as
 * the secondary entry-point for the program.
 */
class API
{
    /**
     * Config instance
     * @var array|null
     */
    private $config;

    /**
     * Database instance
     * @var PDO
     */
    private $db;

    /**
     * MagnetometerController instance
     * @var MagnetometerController
     */
    private $controller;

    /**
     * Determines whether the program returns JSON or XML
     * @var string
     */
    private $dataFormat;

    /**
     * API constructor.
     * @param $input
     *
     * Initially configures the request and puts into motion the received request
     */
    public function __construct($input)
    {
        if (empty($input)) {
            http_response_code(400);
            exit();
        }

        $this->config = Config::getConfig();
        $this->db = Connector::getInstance();
        $this->controller = new MagnetometerController();

        $this->handleRequest($input);
    }

    /**
     * Handle Request
     * @param $input
     * The first thing this method does is determine whether or not the user wishes to see JSON or XML as
     * the format of choice for their results.
     *
     * Afterwards, it checks for the 'latest' entry. If so, it will retrieve the latest magnetometer entry
     * and ignore all other input before closing. It does the same thing for 'all' following that.
     *
     * If the program gets this far without the aforementioned special commands, it will count how many
     * 'values' variables there are - only stopping to iterate once it reaches the end. The issue however is
     * that it counts sequentially, so while 0-10 will work, a missing eleven will also spell the end
     * for twelve.
     *
     * After building an array and performing sanitisation to ensure data is trustworthy, data retrieval
     * is carried out and the results are printed.
     *
     * Should an error be encountered along the way, the appropriate error handling routine is called - often
     * presenting users with an Error 400.
     */
    private function handleRequest($input)
    {
        try {
            $magnetometers = [];
            $this->dataFormat = 'json';
            // Determine data format
            if (isset($input['xml']) && $input['xml'] === 'true') {
                $this->dataFormat = 'xml';
            }

            if (isset($input['latest']) && $input['latest'] === 'true') {
                $magnetometers = $this->controller->getLatest();
                $this->printResult($magnetometers);
                exit();
            }

            if (isset($input['all']) && $input['all'] === 'true') {
                $magnetometers = $this->controller->getAll();
                $this->printResult($magnetometers);
                exit();
            }

            $i = 0;
            $fetch = [];
            while (isset($input['values'][$i])) {
                if (is_numeric($input['values'][$i])) {
                    $fetch[] = strip_tags(htmlspecialchars($input['values'][$i]));
                }
                $i++;
            }
            if (empty($fetch)) {
                $this->printError('No values provided.');
                exit();
            }

            // Fetch magnetometer entries
            $magnetometers = $this->controller->getObjectsFromIds($fetch);
            if (empty($magnetometers)) {
                $this->printError('No matching magnetometer entries found.');
                exit();
            }
            $this->printResult($magnetometers);
        } catch (Exception  $exception) {
            http_response_code(400);
            exit();
        }

    }

    /**
     * Print Result
     * @param $magnetometers
     * Depending on whether XML or JSON is chosen as the format, it passes the
     * retrieved object array onto the appropriate method.
     */
    private function printResult($magnetometers)
    {
        if ($this->dataFormat === 'xml') {
            $this->xmlPrint($magnetometers);
        } else {
            $this->jsonPrint($magnetometers);
        }
    }

    /**
     * Prints an error message, and provides a 400 error code.
     * @param $error
     */
    private function printError($error)
    {
        http_response_code(400);
        if ($this->dataFormat === 'xml') {
            $this->xmlPrint(['error' => $error]);
        } else {
            $this->jsonPrint(['error' => $error]);
        }
    }

    /**
     * JSON Print
     * @param $array
     * Iterates through the array of magnetometer entries, fetching their values using children methods to
     * build a new array that can be encoded into JSON and echoed.
     */
    private function jsonPrint($array)
    {
        header("Content-Type: application/json; charset=UTF-8");
        if (isset($array['error'])) {
            echo json_encode($array['error']);
        } else {
            $json = [];
            foreach ($array as $entry) {
                $entryArray = [
                    'id' => $entry->getId(),
                    'timestamp' => $entry->getTimestamp(),
                    'value' => $entry->getValue(),
                    'temp' => $entry->getTemp(),
                    'lastModified' => $entry->getLastModified()
                ];
                array_push($json, $entryArray);
            }
            echo json_encode($json);
        }
    }

    /** XML Print
     * @param $array
     * Iterates through the array of magnetometer entries and directly echoes their values into XML.
     *
     * Not the cleanest piece of code I have ever written, but it works.
     */
    private function xmlPrint($array)
    {
        header('Content-type: text/xml');
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        if (isset($array['error'])):
            echo "<error>{$array['error']}</error>";
        else:
            echo '<magnetometers>';
            foreach ($array as $magnetometer):
                if (!empty($magnetometer)):
                    echo '<magnetometer>';
                    echo "<id>{$magnetometer->getId()}</id>";
                    echo "<timestamp>{$magnetometer->getTimestamp()}</timestamp>";
                    echo "<value>{$magnetometer->getValue()}</value>";
                    echo "<temp>{$magnetometer->getTemp()}</temp>";
                    echo "<lastmodified>{$magnetometer->getLastModified()}</lastmodified>";
                    echo '</magnetometer>';
                endif;
            endforeach;
            echo '</magnetometers>';
        endif;
    }
}
