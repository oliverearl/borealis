<?php

namespace App;

use App\Database\DatabaseConnection;

require_once __DIR__ . '/Database/DatabaseConnection.php';

class App
{
    private $config = array();
    private $db;

    public function __construct()
    {
        $this->config = require_once __DIR__ . '/Config/Config.php';
        $this->db = DatabaseConnection::getInstance($this->getConfig());
    }

    public function hello()
    {
        echo 'Hello World';
    }

    /**
     * @return DatabaseConnection
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @param DatabaseConnection $db
     */
    public function setDb($db)
    {
        $this->db = $db;
    }
}
