<?php
namespace ole4\Magneto\Tests;

use ole4\Magneto\i18n\Locale;
use PHPUnit_Framework_TestCase;

class LocaleTest extends PHPUnit_Framework_Testcase
{
    public function testGetLanguage()
    {
        if (!session_status() === PHP_SESSION_ACTIVE):
            session_start();
        endif;
        $language = Locale::getLanguage();
        $this->assertEquals('en', $language);
    }

    public function testSetLanguage()
    {
        if (!session_status() === PHP_SESSION_ACTIVE):
            session_start();
        endif;
        Locale::setLanguage('en');
        $this->assertEquals('en', $_SESSION['language']);
    }

    public function testGetLocale()
    {
        $locale = Locale::getLocale();
        $this->assertNotNull($locale);
    }

    public function testSetLocale()
    {
        Locale::setLocale('cy');
        $locale = Locale::getLocale();
        $this->assertNotNull($locale);
    }
}
