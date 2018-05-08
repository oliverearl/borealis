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

/**
 * Class MagnetometerController
 * @package ole4\Magneto\Controllers
 * @author Oliver Earl <ole4@aber.ac.uk>
 *
 * The MagnetometerController class is responsible for building SQL queries and retrieving data from the
 * database, as well as formatting the results of these queries and building arrays of Magnetometer objects.
 * It also importantly takes care of Chart.js JSON generation.
 */
class MagnetometerController
{
    /**
     * Database Entity
     * @var PDO
     * Contains the PDO object - the database instance
     */
    private $db;

    /**
     * MagnetometerController constructor.
     *
     * Upon instantiation of this class, it attempts to retrieve an instance of the $db property.
     */
    public function __construct()
    {
        $this->db = Connector::getInstance();
    }

    /**
     * Get Magnetometer Entry by ID
     * @param $id
     * @return array|null
     *
     * This method is responsible for retrieving a Magnetometer data entry by its unique ID.
     *
     * This method first checks whether the $id parameter is a valid integer. If not, it returns null. It
     * then starts constructing a statement using the parameter as the ID value. It then stores the results
     * of the query in an associative array.
     *
     * If the result happens to be null, i.e. there is nothing stored at that ID, then null is also returned.
     * Should all the data be okay, the array is returned.
     */
    public function getById($id)
    {
        if (!Magneto::sanitiseInt($id)) {
            return null;
        }

        $stmt = $this->db->prepare('SELECT * FROM magneto_meter WHERE id = :key ORDER BY id ASC');
        $stmt->bindParam(':key', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result =  $stmt->fetch(PDO::FETCH_ASSOC);

        if (is_null($result['timestamp'])) {
            return null;
        }

        return $result;
    }

    /**
     * Get Magnetometer Entry by Date
     * @param $date
     * @return array|null
     *
     * This method is responsible for retrieving a Magnetometer entry by its date (timestamp) rather than by
     * its unique ID.
     *
     * It builds a prepared statement using the provided date/string and fetches an associative array. If the
     * array is empty, then null is returned. The associative array itself is returned if found anything.
     */
    public function getByDate($date) {
        $stmt = $this->db->prepare("SELECT * FROM magneto_meter WHERE timestamp = :timestamp LIMIT 1");
        $stmt->bindParam(':timestamp', $date);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (is_null($result['timestamp'])) {
            return null;
        }
        return $result;
    }

    /**
     * Get Object By ID Method
     * @param $id
     * @return Magnetometer|null
     * This method is important as it returns a Magnetometer object by relying on its sibling methods to
     * do some of the heavy lifting. Once it has an associative array retrieved by passing on its parameter
     * and ensures that it is not empty (it returns null if it is) it constructs a new Magnetometer object
     * using the newly retrieved data. This object is then returned.
     */
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

    /**
     * Get Objects From IDs Method
     * @param $ids
     * @return array
     * This method will always return an array, but has the potential to return an empty array if it no
     * Magnetometer objects are returned by its sibling methods.
     *
     * It takes an array of IDs as a parameter, which it iterates through, calling its sibling method to
     * fetch a corresponding Magnetometer object. Of course, if this is returned as null at any point, the
     * ID is skipped as it will not be pushed onto the new array.
     *
     * Regardless of whether all the IDs are duds, some aren't valid, or all of them are, the array is
     * returned at the end.
     */
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
     * Generate Graph (Chart.js) JSON from Objects
     * @param $magnetometers
     * @return array
     *
     * This method is incredibly important and produces specifically crafted JSON in an array to be used
     * by the Chart.js charting/graphing library. It takes an array of Magnetometer objects as a parameter
     * and returns an array of JSON at the end. Nothing is done if this array is empty, but the view
     * will simply not display any data if it is.
     *
     * The method works by iterating through the array of Magnetometer objects. As long as they are not null,
     * the value and timestamp are extracted from it to form the X and Y coordinates accordingly. Once the
     * array is fully constructed, it is encoded into JSON and returned.
     *
     * [1]: https://stackoverflow.com/questions/44250066/how-to-pass-data-from-php-to-chart-js
     * [2]: https://processwire.com/talk/topic/12307-php-arrays-to-json-without/
     */
    public function getGraphJsonFromObjects($magnetometers)
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

    /**
     * Get IDs From Objects Array
     * @param $magnetometers
     * @return array
     *
     * This function extracts the ID values from an array of Magnetometer objects, and returns an array
     * containing only IDs.
     */
    public function getIdsFromObjectsArray($magnetometers)
    {
        if (is_null($magnetometers)) {
            return [];
        }
        $ids = [];
        foreach ($magnetometers as $magnetometer) {
            array_push($ids, $magnetometer->getId());
        }
        return $ids;
    }

    /**
     * Get Latest Entry
     * @return array
     * This stub method uses the getAll() method with the true flag enabled to return an array
     * containing a single Magnetometer object.
     *
     * Not good practice to shoehorn functionality in like this, but I figured it was a lesser evil
     * than duplicating code.
     */
    public function getLatest()
    {
        $magnetometer = $this->getAll(true);
        return $magnetometer;
    }

    /**
     * Get All Magnetometer Entries (or just one)
     * @param bool $limit1
     * @return array
     *
     * One of the most significant methods in the class - method either returns all of the magnetometer
     * entries in the database - a huge amount of objects stored in an array - or just the latest one, also
     * stored in an array. The flag will cause different SQL to be prepared. Take note of the difference in
     * ASC and DESC flags. This is because if we are fetching just one entry, we want that entry to be the
     * newest, most recently created entry, and *should* have the largest unique ID. When printing all the
     * data, we want that data to be in chronological order, so ascending order is needed.
     */
    public function getAll($limit1 = false)
    {
        $stmt = $this->db->prepare('SELECT * FROM magneto_meter ORDER BY id ASC');
        if ($limit1) {
            $stmt = $this->db->prepare('SELECT * FROM magneto_meter ORDER BY id DESC LIMIT 1');
        }
        $stmt->execute();
        $entries = $stmt->fetchAll();

        $magnetometer = null;
        $magnetometers = [];

        foreach ($entries as $entry) {
            $magnetometer =  new Magnetometer(
                $entry['id'],
                $entry['timestamp'],
                $entry['value'],
                $entry['temp'],
                $entry['last_modified']
            );
            array_push($magnetometers, $magnetometer);
        }
        return $magnetometers;
    }

}
