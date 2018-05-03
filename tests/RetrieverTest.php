<?php
namespace ole4\Magneto\Tests;

use PHPUnit_Framework_TestCase;

use ole4\Magneto\Config\Config;
use ole4\Magneto\Retriever;

class RetrieverTest extends PHPUnit_Framework_Testcase
{
    public function testGetInstance()
    {
        $retriever = Retriever::getInstance();
        $this->assertNotNull($retriever);
    }

    public function testSetRetrieval()
    {
        $retriever = Retriever::getInstance();
        $retriever->setRetrieval('001');
        $lastRetrievalDate = Config::getConfigEntry('magnetometer_latest');
        $this->assertEquals('001', $lastRetrievalDate);
    }

    public function testRetrieveData()
    {
        $retriever = Retriever::getInstance();
        $csvFile = $retriever->retrieveData();
        $this->assertNotNull($csvFile);
    }
}
