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

class API
{
    private $config;
    private $db;
    private $controller;
    private $dataFormat;

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

    private function printResult($magnetometers)
    {
        if ($this->dataFormat === 'xml') {
            $this->xmlPrint($magnetometers);
        } else {
            $this->jsonPrint($magnetometers);
        }
    }

    private function printError($error)
    {
        http_response_code(400);
        if ($this->dataFormat === 'xml') {
            $this->xmlPrint(['error' => $error]);
        } else {
            $this->jsonPrint(['error' => $error]);
        }
    }

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
