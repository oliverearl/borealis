<?php
/**
 * Created by PhpStorm.
 * User: oliver
 * Date: 25/02/2018
 * Time: 21:02
 */

namespace App\Models;

class Magnetometer extends DataSource
{

    const EPOCH_DIFF = 2082844800;

    private $timestamp;
    private $value;
    private $temp;

    private function __construct($timeParam, $valueParam, $tempParam)
    {
        $this->timestamp    = $this->convertToUnix($timeParam);
        $this->value        = $valueParam;
        $this->temp         = $tempParam;
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
    public function getUnixTimestamp()
    {
        return ($this->getTimestamp() + $this::EPOCH_DIFF);
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

    private function convertToUnix($lvTimestamp)
    {
        return ($lvTimestamp + $this::EPOCH_DIFF);
    }

    private function convertToLabView($unixTimestamp)
    {
        return ($unixTimestamp - $this::EPOCH_DIFF);
    }
}
