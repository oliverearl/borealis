<?php
/**
 * Created by PhpStorm.
 * User: oliver
 * Date: 25/02/2018
 * Time: 21:02
 */

namespace ole4\Magneto\Models;

use ole4\Magneto\Database\Connector;

class Magnetometer extends DataSource
{

    const EPOCH_DIFF = 2082844800;

    private $id;
    private $timestamp;
    private $value;
    private $temp;
    private $lastModified;

    public function __construct($id = null, $timeParam, $valueParam, $tempParam, $lastModifiedParam = null)
    {
        $this->id           = $id;
        $this->timestamp    = $timeParam;
        $this->value        = $valueParam;
        $this->temp         = $tempParam;
        $this->lastModified = $lastModifiedParam;
    }

    public function saveMagnetometer()
    {
        $time = $this->getTimestamp();
        $value = $this->getValue();
        $temp = $this->getTemp();
        $db = Connector::getInstance();

        $stmt = $db->prepare('INSERT INTO magneto_meter (timestamp, value, temp) VALUES (:time, :value, :temp)');
        $stmt->bindParam(':time',   $time);
        $stmt->bindParam(':value',  $value);
        $stmt->bindParam(':temp',   $temp);
        $stmt->execute();
    }

    public function getId() {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param mixed $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getTemp()
    {
        return $this->temp;
    }

    /**
     * @param mixed $temp
     */
    public function setTemp($temp)
    {
        $this->temp = $temp;
    }

    /**
     * @return mixed
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    public static function dateToUnix($date)
    {
        return strtotime($date);
    }

    public static function convertToUnix($lvTimestamp)
    {
        return ($lvTimestamp - self::EPOCH_DIFF);
    }

    public static function convertToLabView($unixTimestamp)
    {
        return ($unixTimestamp + self::EPOCH_DIFF);
    }
}
