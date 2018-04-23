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
    die;
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
            die;
        }

        $this->config =     Config::getConfig();
        $this->db =         Connector::getInstance();
        $this->controller = new MagnetometerController();

        $this->handleRequest($input);
    }

    private function handleRequest($input)
    {

        $magnetometers = [];
        $this->dataFormat = 'json';
        // Determine data format
        if (isset($input['xml']) && $input['xml'] === 'true') {
            $this->dataFormat = 'xml';
        }

        if (isset($input['latest']) && $input['latest'] === 'true') {
            $magnetometers = $this->controller->getLatest();
            $this->printResult($magnetometers);
            die;
        }

        if (isset($input['all']) && $input['all'] === 'true') {
            $magnetometers = $this->controller->getAll();
            $this->printResult($magnetometers);
            die;
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
            die;
        }

        // Fetch magnetometer entries
        $magnetometers = $this->controller->getObjectsFromIds($fetch);
        if (empty($magnetometers)) {
            $this->printError('No matching magnetometer entries found.');
            die;
        }

        $this->printResult($magnetometers);
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
            foreach($array as $entry)
            {
                $entryArray = [
                    'id' =>             $entry->getId(),
                    'timestamp' =>      $entry->getTimestamp(),
                    'value'=>           $entry->getValue(),
                    'temp' =>           $entry->getTemp(),
                    'lastModified' =>   $entry->getLastModified()
                ];
                array_push($json, $entryArray);
            }
            echo json_encode($json);
        }
    }

    private function xmlPrint($array)
    {
        header("Content-type: text/xml");
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        if (isset($array['error'])) {
            ?>
            <error>
                <?= $array['error'] ?>
            </error>
            <?php
        } else {
            echo '<magnetometers>';
            foreach ($array as $magnetometer) {
                if (!empty($magnetometer)) {
                    ?>
                    <magnetometer>
                        <id>
                            <?= $magnetometer->getId(); ?>
                        </id>
                        <timestamp>
                            <?= $magnetometer->getTimestamp(); ?>
                        </timestamp>
                        <value>
                            <?= $magnetometer->getValue(); ?>
                        </value>
                        <temp>
                            <?= $magnetometer->getTemp(); ?>
                        </temp>
                        <lastmodified>
                            <?= $magnetometer->getLastModified(); ?>
                        </lastmodified>
                    </magnetometer>
                    <?php
                }
            }
            echo '</magnetometers>';
        }
    }
}
