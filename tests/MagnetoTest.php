<?php
namespace ole4\Magneto\Tests;

use PHPUnit_Framework_TestCase;

use ole4\Magneto\Magneto;

class MagnetoTest extends PHPUnit_Framework_Testcase
{
    public function testSanitiseIntWithAlphanumericCharacters()
    {
        $alphanumeric = is_numeric(Magneto::sanitiseInt('abc123'));
        $this->assertTrue($alphanumeric);
    }

    public function testSanitiseIntWithHTMLTags()
    {
        $javascript = Magneto::sanitiseInt('<script>alert("Hello World");</script>');
        $this->assertEmpty($javascript);
    }

    public function testSanitiseIntWithInt()
    {
        $five = Magneto::sanitiseInt(5);
        $this->assertEquals(5, $five);
    }
}
