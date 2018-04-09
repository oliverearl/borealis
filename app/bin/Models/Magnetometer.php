<?php
/**
 * Created by PhpStorm.
 * User: oliver
 * Date: 25/02/2018
 * Time: 21:02
 */

namespace ole4\Magneto\Models;

require_once 'DataSource.php';

use PDO;

class Magnetometer extends DataSource
{

    const EPOCH_DIFF = 2082844800;

    private $timestamp;
    private $value;
    private $temp;
    private $lastModified;

    public function __construct($timeParam, $valueParam, $tempParam, $lastModifiedParam)
    {
        $this->timestamp    = $timeParam;
        $this->value        = $valueParam;
        $this->temp         = $tempParam;
        $this->lastModified = $lastModifiedParam;
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

    public function convertToUnix($lvTimestamp)
    {
        return ($lvTimestamp + $this::EPOCH_DIFF);
    }

    public function convertToLabView($unixTimestamp)
    {
        return ($unixTimestamp - $this::EPOCH_DIFF);
    }

    public static function getLatestEntry($db)
    {
        $stmt = $db->prepare('SELECT id FROM magneto_meter ORDER BY id DESC LIMIT 1');
        $stmt->execute();
        $components = $stmt->fetch(PDO::FETCH_ASSOC);
        return new Magnetometer(
            $components['timestamp'],
            $components['value'],
            $components['temp'],
            $components['last_modified']
        );
    }
}
