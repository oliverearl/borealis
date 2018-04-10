<?php
namespace ole4\Magneto\i18n;

class Locale
{
    const LANGUAGES = ['en', 'cy'];
    const DEFAULT_LANG = 'en';

    private static $language;
    private static $locale;

    public static function getLanguage()
    {
        if (!isset(self::$language)) {
            self::setLanguage();
        }
        return self::$language;
    }

    public static function setLanguage($language = null)
    {
        if (is_null($language) || !in_array(strtolower($language), self::LANGUAGES)) {
            self::$language = self::determineLanguage();
        } else {
            self::$language = $language;
        }

        if (isset($_GET['language']) && in_array(strtolower($_GET['language']), self::LANGUAGES)) {
            self::$language = strtolower($_GET['language']);
        }

        $_SESSION['language'] = self::$language;
        // echo "<h1>{$_SESSION['language']}</h1>";
    }

    private static function determineLanguage()
    {
        // Default language is English
        $default = self::DEFAULT_LANG;

        // Is the language already pre-determined from session data?
        if (isset($_SESSION['language'])) {
            if (in_array(strtolower($_SESSION['language']), self::LANGUAGES)) {
                return strtolower($_SESSION['language']);
            } else {
                // Non-supported language, or funny business
                return $default;
            }
        } else {
            // Language undefined, can we autodetect it?
            $browserLanguage = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
            if (in_array($browserLanguage, self::LANGUAGES)) {
                return $browserLanguage;
            }
        }
        return $default;
    }

    public static function getLocale()
    {
        if (!isset(self::$locale)) {
            self::setLocale(self::getLanguage());
        }
        return self::$locale;
    }

    public static function setLocale($language = null)
    {
        if (is_null($language)) {
            $language = self::DEFAULT_LANG;
        }

        $dataset = "locale/{$language}.json";

        if (file_exists($dataset)) {
            $json = json_decode(file_get_contents($dataset), true);

            if (is_null($json)) {
                trigger_error('Locale extraction failure: ' . json_last_error_msg(), E_USER_ERROR);
            }
            self::$locale = $json;
            return;
        }
        trigger_error('Locale file not found.', E_USER_ERROR);
        self::$locale = null;
    }
}
