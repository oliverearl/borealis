<?php
namespace ole4\Magneto\Tests;

use ole4\Magneto\Config\Config;
use PHPUnit_Framework_TestCase;

class ConfigTest extends PHPUnit_Framework_Testcase
{
    public function testGetConfig()
    {
        $db = Config::getConfig();
        $this->assertNotNull($db);
    }

    public function testGetInstance()
    {
        $db = Config::getInstance();
        $this->assertNotNull($db);
    }

    public function testGetDebugEntry()
    {
        $debugFlag = Config::getConfigEntry('debug');
        $this->assertNotNull($debugFlag);
    }

    public function testUpdateConfigEntry()
    {
        Config::updateConfigEntry('debug', true);
        $debugFlag = Config::getConfigEntry('debug');

        $this->assertTrue($debugFlag);
    }
}
