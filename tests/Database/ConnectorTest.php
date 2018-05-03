<?php
namespace ole4\Magneto\Tests;

use ole4\Magneto\Database\Connector;
use PHPUnit_Framework_TestCase;

class ConnectorTest extends PHPUnit_Framework_Testcase
{
    public function testGetInstance()
    {
        $db = Connector::getInstance();
        $this->assertNotNull($db);
    }

    public function testReloadDatabase()
    {
        Connector::reloadDatabase();
        $db = Connector::getInstance();
        $this->assertNotNull($db);
    }
}
