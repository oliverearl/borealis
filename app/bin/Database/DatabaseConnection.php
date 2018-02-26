<?php

namespace App\Database;

use PDO;
use PDOException;

class DatabaseConnection
{
    private static $instance = null;
    // You can't use visibility modifiers (i.e. public/private) for constants until PHP 7.1.0...
    // https://secure.php.net/manual/en/language.oop5.constants.php
    const DEFAULT_DATABASE = [
        'hostname' =>       'db.dcs.aber.ac.uk',
        'dbName' =>         'ole4',
        'username' =>       'ole4',
        'password' =>       '***REMOVED***',
        'dbType' =>         'mysql',
    ];

    private $hostname;
    private $dbName;
    private $username;
    private $password;
    private $dbType;

    private function __construct($config)
    {
        if (is_null($config) || $config['debug']) {
            $this->hostname =   $this::DEFAULT_DATABASE['hostname'];
            $this->dbName =     $this::DEFAULT_DATABASE['dbName'];
            $this->username =   $this::DEFAULT_DATABASE['username'];
            $this->password =   $this::DEFAULT_DATABASE['password'];
            $this->dbType =     $this::DEFAULT_DATABASE['dbType'];
        }

        $this->hostname =   $config['hostname'];
        $this->dbName =     $config['dbName'];
        $this->username =   $config['username'];
        $this->password =   $config['password'];
        $this->dbType =     $config['dbType'];

        try
        {
            $dataSource = "{$this->dbType}:host={$this->hostname};dbname={$this->dbName}";
            $pdo = new PDO($dataSource, $this->username, $this->password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $ex)
        {
            trigger_error('Failed to connect to database: ' . $ex, E_USER_ERROR);
        }
    }

    public static function getInstance($config)
    {
        if (is_null(self::$instance)) {
            self::$instance = new DatabaseConnection($config);
        }

        return self::$instance;
    }
}