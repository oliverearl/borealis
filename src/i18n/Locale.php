<?php
namespace ole4\Magneto\i18n;

use ole4\Magneto\Magneto;

/**
 * Class Locale
 * @package ole4\Magneto\i18n
 * @author Oliver Earl <ole4@aber.ac.uk>
 */
class Locale
{
    /**
     * Supported Languages Array. English and Welsh.
     */
    const LANGUAGES = ['en', 'cy'];

    /**
     * Default Language - probably English.
     */
    const DEFAULT_LANG = 'en';

    /**
     * @var string
     * Current language in use.
     */
    private static $language;

    /**
     * @var array
     * The loaded locale file - the contents extracted from JSON
     */
    private static $locale;

    /**
     * Get Language
     * @return string
     * Attempts to return the currently set language, if it isn't set, it will call the method
     * to begin setting it.
     */
    public static function getLanguage()
    {
        if (!isset(self::$language)) {
            self::setLanguage();
        }
        return self::$language;
    }

    /**
     * Set Language
     * @param null $language
     * This lengthy method can take an optional parameter, allowing the language to be specifically set.
     * But the method does lengthy checks to ensure that the language is supported. If it's not in the
     * array of suppored languages, or the optional parameter wasn't set, then the program will call
     * determineLanguage() to attempt to determine the user's language. Otherwise, it uses whatever
     * was in $language.
     *
     * Afterwards, it then overrides any previous decisions should the user have submitted a language change
     * request - this comes in the form of a GET parameter - like this index.php?language=cy
     * If the language is in the array of supported languages, it becomes the new active language.
     *
     * Once this is done, the session language value is set and is used throughout the program.
     */
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
    }

    /**
     * Determine Language
     * @return string
     * This method is called if the current language is unknown and needs to be determined somehow.
     *
     * If the session has already defined a language, so the setLanguage() routine has already been
     * executed before, and the session data is still in the list of supported languages (so it hasn't
     * been tampered with), it will use this. If the value is no longer supported, then it defaults to
     * English.
     *
     * The next check involves the browser's language. If the browser's language is using a supported
     * language, that will be used as the program language. If not, English is used by default.
     *
     * I wonder how many people have their web browsers in Welsh.
     */
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

    /**
     * Get Locale
     * @return array
     * Attempts to retrieve the locale array. If it's not set, it will set it. If the language isn't set, it
     * will do that too before taking care of the locale.
     *
     * Returns an associative array used to contain localisations.
     */
    public static function getLocale()
    {
        if (!isset(self::$locale)) {
            self::setLocale(self::getLanguage());
        }
        return self::$locale;
    }

    /**
     * Set Locale
     * @param null $language
     * Takes an optional language parameter, but if this is null, it just assumes English.
     *
     * It checks if supportedLanguage.json is present inside the locale folder. If it is, will extract
     * its contents and JSON decode them into an associative array. If the file is missing, or the JSON
     * is malformed or broken, errors will be triggered.
     *
     * Unfortunately busted locale files often knock the entire program down, but error handling sometimes
     * works.
     */
    public static function setLocale($language = null)
    {
        if (is_null($language)) {
            $language = self::DEFAULT_LANG;
        }

        $dataset = "locale/{$language}.json";

        if (file_exists($dataset)) {
            $json = json_decode(file_get_contents($dataset), true);

            if (is_null($json)) {
                Magneto::error('locale_json_failure', json_last_error_msg());
            }
            self::$locale = $json;
            return;
        }
        Magneto::error('locale_json_missing', json_last_error_msg());
        self::$locale = null;
    }
}
