<?php
namespace ole4\Magneto\Tests;

use ole4\Magneto\Models\Magnetometer;
use PHPUnit_Framework_TestCase;

class MagnetometerTest extends PHPUnit_Framework_Testcase
{
    const DATE = '09/19/2014';
    const EPOCH_DIFF = 2082844800;

    public function testSaveMagnetometer()
    {
        $magnetometer = new Magnetometer(
            null,
            '01/01/2001',
            420,
            16,
            null
        );

        $magnetometer->saveMagnetometer();
    }

    public function testDateToUnix()
    {
        $timestamp = Magnetometer::dateToUnix($this::DATE);
        $answer = strtotime($this::DATE);
        $this->assertEquals($answer, $timestamp);
    }

    public function testConvertUnixToLabView()
    {
        $unixTimestamp = strtotime($this::DATE);
        $labViewTimestamp = Magnetometer::convertToLabView($unixTimestamp);
        $answer = ($unixTimestamp + $this::EPOCH_DIFF);
        $this->assertEquals($answer, $labViewTimestamp);
    }

    public function testConvertLabViewToUnix()
    {
        $labViewTimestamp = Magnetometer::convertToLabView(strtotime($this::DATE));
        $unixTimestamp = Magnetometer::convertToUnix($labViewTimestamp);
        $answer = ($labViewTimestamp - $this::EPOCH_DIFF);
        $this->assertEquals($answer, $unixTimestamp);
    }
}
