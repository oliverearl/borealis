<?php
/**
 * Created by PhpStorm.
 * User: oliver
 * Date: 10/04/2018
 * Time: 22:53
 */

namespace ole4\Magneto\Controllers;

use PDO;

use ole4\Magneto\Magneto;
use ole4\Magneto\Models\Magnetometer;
use ole4\Magneto\Database\Connector;

class MagnetometerController
{
    private $db;

    public function __construct()
    {
        $this->db = Connector::getInstance();
    }

    public function getById($id)
    {
        if (!Magneto::sanitiseInt($id)) {
            return null;
        }

        $stmt = $this->db->prepare('SELECT * FROM magneto_meter WHERE id = :key ORDER BY id DESC');
        $stmt->bindParam(':key', $id);
        $stmt->execute();
        $result =  $stmt->fetch(PDO::FETCH_ASSOC);

        if (is_null($result['timestamp'])) {
            return null;
        }

        return $result;
    }

    public function getObjectById($id)
    {
        $components = $this->getById($id);
        if (is_null($components)) {
            return null;
        }

        $magnetometer =  new Magnetometer(
            $components['id'],
            $components['timestamp'],
            $components['value'],
            $components['temp'],
            $components['last_modified']
        );
        return $magnetometer;
    }

    public function getObjectsFromIds($ids)
    {
        $magnetometer = null;
        $array = [];
        foreach ($ids as $id) {
            $magnetometer = $this->getObjectById($id);
            if (!is_null($magnetometer)) {
                array_push($array, $magnetometer);
            }
        }
        return $array;
    }

    /**
     * @param $magnetometers
     * @return array
     * [1]: https://stackoverflow.com/questions/44250066/how-to-pass-data-from-php-to-chart-js
     * [2]: https://processwire.com/talk/topic/12307-php-arrays-to-json-without/
     */
    public function graphJsonFromObjects($magnetometers)
    {
        $formattedJson = [];
        foreach ($magnetometers as $magnetometer) {
            if (!is_null($magnetometer)) {
                $formattedJson[] = [
                    'x' => $magnetometer->getValue(),
                    'y' => $magnetometer->getTimestamp()
                ];
            }
        }
        return json_encode($formattedJson, JSON_FORCE_OBJECT);
    }

    public function getLatest()
    {
        $stmt = $this->db->prepare('SELECT id FROM magneto_meter ORDER BY id DESC LIMIT 1');
        $stmt->execute();
        $magnetometer = $this->getObjectById($stmt->fetch(PDO::FETCH_ASSOC)['id']);
        if (!is_null($magnetometer)) {
            return $magnetometer;
        }
        return null;
    }

}
