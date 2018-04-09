<?php

namespace ole4\Magneto\i18n;

class LocaleLoader
{
    private static $instance = null;

    public static function loadLocale($language)
    {
        $localeFile = __DIR__ . "/../../locale/{$language}.json";

        if (file_exists($localeFile)) {
            $locale = json_decode(file_get_contents($localeFile), true);

            if (is_null($locale)) {
                trigger_error('Locale extraction failure: ' . json_last_error_msg(), E_USER_ERROR);
                return null;
            }
            return $locale;
        }

        trigger_error('Locale file not found.', E_USER_ERROR);
        return null;
    }
}
