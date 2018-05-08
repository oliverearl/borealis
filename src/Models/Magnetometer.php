<?php
/**
 * Created by PhpStorm.
 * User: oliver
 * Date: 25/02/2018
 * Time: 21:02
 */

namespace ole4\Magneto\Models;

use ole4\Magneto\Database\Connector;

/**
 * Class Magnetometer
 * @package ole4\Magneto\Models
 * @author Oliver Earl <ole4@aber.ac.uk>
 *
 * Magnetometer Model class. Contains functionality pertinent to Magnetometer objects, conversion routines
 * for timestamps and for saving an object to the database.
 */
class Magnetometer extends DataSource
{

    /**
     * The numerical constant that is used to convert between UNIX and LabVIEW epochs.
     */
    const EPOCH_DIFF = 2082844800;

    /**
     * Unique ID
     * @var integer|null
     */
    private $id;

    /**
     * Date
     * @var string
     */
    private $timestamp;

    /**
     * Arbitrary Value
     * @var float|integer
     */
    private $value;

    /**
     * Instrument Temperature in Celsius
     * @var float|integer
     */
    private $temp;

    /**
     * Last Modified Date
     * @var string|null
     */
    private $lastModified;

    /**
     * Magnetometer constructor.
     * @param null $id
     * @param $timeParam
     * @param $valueParam
     * @param $tempParam
     * @param null $lastModifiedParam
     *
     * This constructor will happily take null values as defaults for the ID and the Last Modified properties
     * as if they are unknown, such as a newly constructed object retrieved from the Magnetometer, they will
     * be filled in once the object is written to the database and later retrieved and reconstructed.
     */
    public function __construct($id = null, $timeParam, $valueParam, $tempParam, $lastModifiedParam = null)
    {
        $this->id           = $id;
        $this->timestamp    = $timeParam;
        $this->value        = $valueParam;
        $this->temp         = $tempParam;
        $this->lastModified = $lastModifiedParam;
    }

    /**
     * This method is responsible for saving a Magnetometer object to the database. It first uses its
     * children methods and stores their values within instance variables, and grabs a database PDO instance.
     *
     * It then constructs SQL to insert the newly torn apart object into the database. As this is for saving
     * a new magnetometer entry, it assumes there is no ID or LastModified date.
     */
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

    /**
     * Gets current ID.
     * @return integer|null
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Gets current date.
     * @return string
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Sets current date.
     * @param string $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * Gets current value
     * @return integer|float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets current value
     * @param integer|float $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Gets current temperature
     * @return integer|float
     */
    public function getTemp()
    {
        return $this->temp;
    }

    /**
     * Sets current temperature
     * @param integer|float $temp
     */
    public function setTemp($temp)
    {
        $this->temp = $temp;
    }

    /**
     * Gets Last Modified Date
     * @return string|null
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * Parses and converts a date or date string into a UNIX timestamp.
     * @param $date
     * @return false|int
     */
    public static function dateToUnix($date)
    {
        return strtotime($date);
    }

    /**
     * Converts a LabVIEW timestamp into a UNIX timestamp.
     * @param $lvTimestamp
     * @return int
     */
    public static function convertToUnix($lvTimestamp)
    {
        return ($lvTimestamp - self::EPOCH_DIFF);
    }

    /**
     * Converts a UNIX timestamp into a LabVIEW timestamp.
     * @param $unixTimestamp
     * @return int
     */
    public static function convertToLabView($unixTimestamp)
    {
        return ($unixTimestamp + self::EPOCH_DIFF);
    }
}
