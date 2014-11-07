<?php

require_once('Language.php');
require_once('OutputContainer.php');

class Language_Android extends Language {

    public function __construct($id) {
        parent::__construct($id);
    }

    /**
     * Returns the platform-specific keys (strings) for the given language
     *
     * @param int $languageID the language ID to get the key for
     * @return array keys for this language (at least one)
     * @throws Exception if the given language ID could not be found
     */
    public static function getLanguageKeys($languageID) {
        $out = array();

        if ($languageID == Language::LANGUAGE_ENGLISH) {
            $out[] = 'values';
        }
        else {
            $out[] = 'values-'.self::getLanguageCode($languageID);
        }

        // add alternative codes where necessary for better compatibility
        if ($languageID == Language::LANGUAGE_HEBREW) {
            $out[] = 'values-he';
        }
        elseif ($languageID == Language::LANGUAGE_INDONESIAN) {
            $out[] = 'values-id';
        }
        elseif ($languageID == Language::LANGUAGE_YIDDISH) {
            $out[] = 'values-yi';
        }

        return $out;
    }

    /**
     * Returns the platform-specific code (string) for the given language
     *
     * @param int $languageID the language ID to get the key for
     * @return string code for this language
     * @throws Exception if the given language ID could not be found
     */
    public static function getLanguageCode($languageID) {
        switch ($languageID) {
            case self::LANGUAGE_ENGLISH:
                return 'en';
            case self::LANGUAGE_AFRIKAANS:
                return 'af';
            case self::LANGUAGE_AMHARIC:
                return 'am';
            case self::LANGUAGE_ARABIC:
                return 'ar';
            case self::LANGUAGE_AZERBAIJANI:
                return 'az';
            case self::LANGUAGE_BASHKIR:
                return 'ba';
            case self::LANGUAGE_BELARUSIAN:
                return 'be';
            case self::LANGUAGE_BULGARIAN:
                return 'bg';
            case self::LANGUAGE_BENGALI:
                return 'bn';
            case self::LANGUAGE_BRETON:
                return 'br';
            case self::LANGUAGE_BOSNIAN:
                return 'bs';
            case self::LANGUAGE_CATALAN:
                return 'ca';
            case self::LANGUAGE_CZECH:
                return 'cs';
            case self::LANGUAGE_CHUVASH:
                return 'cv';
            case self::LANGUAGE_WELSH:
                return 'cy';
            case self::LANGUAGE_DANISH:
                return 'da';
            case self::LANGUAGE_GERMAN:
                return 'de';
            case self::LANGUAGE_GREEK:
                return 'el';
            case self::LANGUAGE_SPANISH:
                return 'es';
            case self::LANGUAGE_ESTONIAN:
                return 'et';
            case self::LANGUAGE_BASQUE:
                return 'eu';
            case self::LANGUAGE_PERSIAN:
                return 'fa';
            case self::LANGUAGE_FINNISH:
                return 'fi';
            case self::LANGUAGE_FRENCH:
                return 'fr';
            case self::LANGUAGE_WESTERN_FRISIAN:
                return 'fy';
            case self::LANGUAGE_IRISH:
                return 'ga';
            case self::LANGUAGE_GALICIAN:
                return 'gl';
            case self::LANGUAGE_GUJARATI:
                return 'gu';
            case self::LANGUAGE_HINDI:
                return 'hi';
            case self::LANGUAGE_HAITIAN:
                return 'ht';
            case self::LANGUAGE_CROATIAN:
                return 'hr';
            case self::LANGUAGE_HUNGARIAN:
                return 'hu';
            case self::LANGUAGE_ARMENIAN:
                return 'hy';
            case self::LANGUAGE_INDONESIAN:
                return 'in';
            case self::LANGUAGE_ICELANDIC:
                return 'is';
            case self::LANGUAGE_ITALIAN:
                return 'it';
            case self::LANGUAGE_HEBREW:
                return 'iw';
            case self::LANGUAGE_JAPANESE:
                return 'ja';
            case self::LANGUAGE_JAVANESE:
                return 'jv';
            case self::LANGUAGE_GEORGIAN:
                return 'ka';
            case self::LANGUAGE_KANNADA:
                return 'kn';
            case self::LANGUAGE_KAZAKH:
                return 'kk';
            case self::LANGUAGE_KOREAN:
                return 'ko';
            case self::LANGUAGE_KURDISH:
                return 'ku';
            case self::LANGUAGE_KIRGHIZ:
                return 'ky';
            case self::LANGUAGE_LUXEMBOURGISH:
                return 'lb';
            case self::LANGUAGE_LITHUANIAN:
                return 'lt';
            case self::LANGUAGE_LATVIAN:
                return 'lv';
            case self::LANGUAGE_MALAGASY:
                return 'mg';
            case self::LANGUAGE_MACEDONIAN:
                return 'mk';
            case self::LANGUAGE_MALAYALAM:
                return 'ml';
            case self::LANGUAGE_MARATHI:
                return 'mr';
            case self::LANGUAGE_MALAY:
                return 'ms';
            case self::LANGUAGE_NEPALI:
                return 'ne';
            case self::LANGUAGE_NORWEGIAN_BOKMAL:
                return 'nb';
            case self::LANGUAGE_DUTCH:
                return 'nl';
            case self::LANGUAGE_NORWEGIAN_NYNORSK:
                return 'nn';
            case self::LANGUAGE_OCCITAN:
                return 'oc';
            case self::LANGUAGE_POLISH:
                return 'pl';
            case self::LANGUAGE_PORTUGUESE_BRAZIL:
                return 'pt-rBR';
            case self::LANGUAGE_PORTUGUESE_PORTUGAL:
                return 'pt-rPT';
            case self::LANGUAGE_ROMANIAN:
                return 'ro';
            case self::LANGUAGE_RUSSIAN:
                return 'ru';
            case self::LANGUAGE_SLOVAK:
                return 'sk';
            case self::LANGUAGE_SLOVENE:
                return 'sl';
            case self::LANGUAGE_ALBANIAN:
                return 'sq';
            case self::LANGUAGE_SERBIAN:
                return 'sr';
            case self::LANGUAGE_SUNDANESE:
                return 'su';
            case self::LANGUAGE_SWEDISH:
                return 'sv';
            case self::LANGUAGE_SWAHILI:
                return 'sw';
            case self::LANGUAGE_TELUGU:
                return 'te';
            case self::LANGUAGE_TAJIK:
                return 'tg';
            case self::LANGUAGE_THAI:
                return 'th';
            case self::LANGUAGE_TAGALOG:
                return 'tl';
            case self::LANGUAGE_TURKISH:
                return 'tr';
            case self::LANGUAGE_TATAR:
                return 'tt';
            case self::LANGUAGE_UKRAINIAN:
                return 'uk';
            case self::LANGUAGE_UZBEK:
                return 'uz';
            case self::LANGUAGE_VIETNAMESE:
                return 'vi';
            case self::LANGUAGE_WALLOON:
                return 'wa';
            case self::LANGUAGE_YORUBA:
                return 'yo';
            case self::LANGUAGE_CHINESE_SIMPLIFIED:
                return 'zh-rCN';
            case self::LANGUAGE_CHINESE_TRADITIONAL:
                return 'zh-rTW';
            case self::LANGUAGE_ARAGONESE:
                return 'an';
            case self::LANGUAGE_HAUSA:
                return 'ha';
            case self::LANGUAGE_IGBO:
                return 'ig';
            case self::LANGUAGE_KHMER:
                return 'km';
            case self::LANGUAGE_LAO:
                return 'lo';
            case self::LANGUAGE_MALTESE:
                return 'mt';
            case self::LANGUAGE_MAORI:
                return 'mi';
            case self::LANGUAGE_PUNJABI:
                return 'pa';
            case self::LANGUAGE_SOMALI:
                return 'so';
            case self::LANGUAGE_TAMIL:
                return 'ta';
            case self::LANGUAGE_URDU:
                return 'ur';
            case self::LANGUAGE_YIDDISH:
                return 'ji';
            case self::LANGUAGE_ZULU:
                return 'zu';
            default:
                throw new Exception('Unknown language ID '.$languageID);
        }
    }

    public static function getLanguageNameFull($languageID) {
        return parent::getLanguageNameFull($languageID).' — '.self::getLanguageCode($languageID);
    }

    /**
     * Returns the platform-specific keys (strings) for this language
     *
     * @return array keys for this language (at least one)
     * @throws Exception if the given language ID could not be found
     */
    public function getKeys() {
        return self::getLanguageKeys($this->id);
    }

    /**
     * Constructs the platform-specific output for this Language object in Android XML format
     *
     * @param int $groupID the group ID to get the output for (or Phrase::GROUP_ALL)
     * @param int $ignoreIfSameAsDefaultLanguage exclude a phrase if it's the same as the default language
     * @param int $defaultLanguageObject the default language object	 
     * @return OutputContainer the output object containing both data and completeness in percent
     */
    public function outputAndroidXML($groupID, $ignoreIfSameAsDefault, $defaultLanguageObject) {
        $container = new OutputContainer();
        $phraseEntries = array();

        foreach ($this->phrases as $phrase) {
            // if we want all groups or if the phrase is in the selected group
            if ($groupID == Phrase::GROUP_ALL || $phrase->getGroupID() == $groupID) {
				if (!$ignoreIfSameAsDefault  || $phrase->outputAndroidXML($groupID) != $defaultLanguageObject->getPhraseByKey($phrase->getPhraseKey())->outputAndroidXML($groupID)) {
					$container->newPhrase($phrase->isEmpty());
					$phraseEntries[] = $phrase->outputAndroidXML($groupID);
				}
            }
        }

        $container->setContent('<?xml version="1.0" encoding="utf-8"?>'."\n".'<resources>'."\n" . implode("\n", $phraseEntries) . "\n".'</resources>');
        return $container;
    }

    /**
     * Constructs the platform-specific output for this Language object in Android XML format with escaped HTML
     *
     * @param int $groupID the group ID to get the output for (or Phrase::GROUP_ALL)
     * @param int $ignoreIfSameAsDefaultLanguage exclude a phrase if it's the same as the default language
     * @param int $defaultLanguageObject the default language object	 
     * @return OutputContainer the output object containing both data and completeness in percent
     */
    public function outputAndroidXMLEscapedHTML($groupID, $ignoreIfSameAsDefault, $defaultLanguageObject) {
        $container = new OutputContainer();
        $phraseEntries = array();

        foreach ($this->phrases as $phrase) {
            // if we want all groups or if the phrase is in the selected group
            if ($groupID == Phrase::GROUP_ALL || $phrase->getGroupID() == $groupID) {
				if (!$ignoreIfSameAsDefault  || $phrase->outputAndroidXMLEscapedHTML($groupID) != $defaultLanguageObject->getPhraseByKey($phrase->getPhraseKey())->outputAndroidXMLEscapedHTML($groupID)) {
					$container->newPhrase($phrase->isEmpty());
					$phraseEntries[] = $phrase->outputAndroidXMLEscapedHTML($groupID);
				}
            }
        }

        $container->setContent('<?xml version="1.0" encoding="utf-8"?>'."\n".'<resources>'."\n" . implode("\n", $phraseEntries) . "\n".'</resources>');
        return $container;
    }

    /**
     * Constructs the platform-specific output for this Language object in JSON format
     *
     * @param int $groupID the group ID to get the output for (or Phrase::GROUP_ALL)
     * @param int $ignoreIfSameAsDefaultLanguage exclude a phrase if it's the same as the default language
     * @param int $defaultLanguageObject the default language object	 
     * @return OutputContainer the output object containing both data and completeness in percent
     */
    public function outputJSON($groupID, $ignoreIfSameAsDefault, $defaultLanguageObject) {
        $container = new OutputContainer();
        $phraseEntries = array();

        foreach ($this->phrases as $phrase) {
            // if we want all groups or if the phrase is in the selected group
            if ($groupID == Phrase::GROUP_ALL || $phrase->getGroupID() == $groupID) {
				if (!$ignoreIfSameAsDefault  || $phrase->outputJSON($groupID) != $defaultLanguageObject->getPhraseByKey($phrase->getPhraseKey())->outputJSON($groupID)) {			
					$container->newPhrase($phrase->isEmpty());
					$phraseEntries[] = $phrase->outputJSON($groupID);
				}
            }
        }

        $container->setContent('{'."\n" . implode(",\n", $phraseEntries) . "\n".'}');
        return $container;
    }

    /**
     * Constructs the platform-specific output for this Language object in plaintext format
     *
     * @param int $groupID the group ID to get the output for (or Phrase::GROUP_ALL)
     * @param int $ignoreIfSameAsDefaultLanguage exclude a phrase if it's the same as the default language
     * @param int $defaultLanguageObject the default language object	 
     * @return OutputContainer the output object containing both data and completeness in percent
     */
    public function outputPlaintext($groupID, $ignoreIfSameAsDefault, $defaultLanguageObject) {
        $container = new OutputContainer();
        $phraseEntries = array();

        foreach ($this->phrases as $phrase) {
            // if we want all groups or if the phrase is in the selected group
            if ($groupID == Phrase::GROUP_ALL || $phrase->getGroupID() == $groupID) {
				if (!$ignoreIfSameAsDefault  || $phrase->outputPlaintext($groupID) != $defaultLanguageObject->getPhraseByKey($phrase->getPhraseKey())->outputPlaintext($groupID)) {
					$container->newPhrase($phrase->isEmpty());
					$phraseEntries[] = $phrase->outputPlaintext($groupID);
				}
            }
        }

        $container->setContent(implode("\n", $phraseEntries));
        return $container;
    }

}

?>