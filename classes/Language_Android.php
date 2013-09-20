<?php

require_once('Language.php');

class Language_Android extends Language {

    public function __construct($id) {
        parent::__construct($id);
    }

    public function output() {
        $out = '<?xml version="1.0" encoding="utf-8"?>'."\n";
        $out .= '<resources>'."\n";
        foreach ($this->phrases as $phrase) {
            $out .= $phrase->output();
        }
        $out .= '</resources>';
        return $out;
    }

    /**
     * Returns the platform-specific key (string) for this language
     *
     * @return string key for this language
     * @throws Exception if the given language ID could not be found
     */
    public function getKey() {
        switch ($this->id) {
            case self::LANGUAGE_ENGLISH:
                return 'values';
            case self::LANGUAGE_AFRIKAANS:
                return 'values-af';
            case self::LANGUAGE_AMHARIC:
                return 'values-am';
            case self::LANGUAGE_ARABIC:
                return 'values-ar';
            case self::LANGUAGE_AZERBAIJANI:
                return 'values-az';
            case self::LANGUAGE_BASHKIR:
                return 'values-ba';
            case self::LANGUAGE_BELARUSIAN:
                return 'values-be';
            case self::LANGUAGE_BULGARIAN:
                return 'values-bg';
            case self::LANGUAGE_BENGALI:
                return 'values-bn';
            case self::LANGUAGE_BRETON:
                return 'values-br';
            case self::LANGUAGE_BOSNIAN:
                return 'values-bs';
            case self::LANGUAGE_CATALAN:
                return 'values-ca';
            case self::LANGUAGE_CZECH:
                return 'values-cs';
            case self::LANGUAGE_CHUVASH:
                return 'values-cv';
            case self::LANGUAGE_WELSH:
                return 'values-cy';
            case self::LANGUAGE_DANISH:
                return 'values-da';
            case self::LANGUAGE_GERMAN:
                return 'values-de';
            case self::LANGUAGE_GREEK:
                return 'values-el';
            case self::LANGUAGE_SPANISH:
                return 'values-es';
            case self::LANGUAGE_ESTONIAN:
                return 'values-et';
            case self::LANGUAGE_BASQUE:
                return 'values-eu';
            case self::LANGUAGE_PERSIAN:
                return 'values-fa';
            case self::LANGUAGE_FINNISH:
                return 'values-fi';
            case self::LANGUAGE_FRENCH:
                return 'values-fr';
            case self::LANGUAGE_WESTERN_FRISIAN:
                return 'values-fy';
            case self::LANGUAGE_IRISH:
                return 'values-ga';
            case self::LANGUAGE_GALICIAN:
                return 'values-gl';
            case self::LANGUAGE_GUJARATI:
                return 'values-gu';
            case self::LANGUAGE_HINDI:
                return 'values-hi';
            case self::LANGUAGE_HAITIAN:
                return 'values-ht';
            case self::LANGUAGE_CROATIAN:
                return 'values-hr';
            case self::LANGUAGE_HUNGARIAN:
                return 'values-hu';
            case self::LANGUAGE_ARMENIAN:
                return 'values-hy';
            case self::LANGUAGE_INDONESIAN:
                return 'values-id';
            case self::LANGUAGE_ICELANDIC:
                return 'values-is';
            case self::LANGUAGE_ITALIAN:
                return 'values-it';
            case self::LANGUAGE_HEBREW:
                return 'values-iw';
            case self::LANGUAGE_JAPANESE:
                return 'values-ja';
            case self::LANGUAGE_JAVANESE:
                return 'values-jv';
            case self::LANGUAGE_GEORGIAN:
                return 'values-ka';
            case self::LANGUAGE_KANNADA:
                return 'values-kn';
            case self::LANGUAGE_KAZAKH:
                return 'values-kk';
            case self::LANGUAGE_KOREAN:
                return 'values-ko';
            case self::LANGUAGE_KURDISH:
                return 'values-ku';
            case self::LANGUAGE_KIRGHIZ:
                return 'values-ky';
            case self::LANGUAGE_LUXEMBOURGISH:
                return 'values-lb';
            case self::LANGUAGE_LITHUANIAN:
                return 'values-lt';
            case self::LANGUAGE_LATVIAN:
                return 'values-lv';
            case self::LANGUAGE_MALAGASY:
                return 'values-mg';
            case self::LANGUAGE_MACEDONIAN:
                return 'values-mk';
            case self::LANGUAGE_MALAYALAM:
                return 'values-ml';
            case self::LANGUAGE_MARATHI:
                return 'values-mr';
            case self::LANGUAGE_MALAY:
                return 'values-ms';
            case self::LANGUAGE_NEPALI:
                return 'values-ne';
            case self::LANGUAGE_NORWEGIAN_BOKMAL:
                return 'values-nb';
            case self::LANGUAGE_DUTCH:
                return 'values-nl';
            case self::LANGUAGE_NORWEGIAN_NYNORSK:
                return 'values-nn';
            case self::LANGUAGE_OCCITAN:
                return 'values-oc';
            case self::LANGUAGE_POLISH:
                return 'values-pl';
            case self::LANGUAGE_PORTUGUESE_BRAZIL:
                return 'values-pt-rBR';
            case self::LANGUAGE_PORTUGUESE_PORTUGAL:
                return 'values-pt-rPT';
            case self::LANGUAGE_ROMANIAN:
                return 'values-ro';
            case self::LANGUAGE_RUSSIAN:
                return 'values-ru';
            case self::LANGUAGE_SLOVAK:
                return 'values-sk';
            case self::LANGUAGE_SLOVENE:
                return 'values-sl';
            case self::LANGUAGE_ALBANIAN:
                return 'values-sq';
            case self::LANGUAGE_SERBIAN:
                return 'values-sr';
            case self::LANGUAGE_SUNDANESE:
                return 'values-su';
            case self::LANGUAGE_SWEDISH:
                return 'values-sv';
            case self::LANGUAGE_SWAHILI:
                return 'values-sw';
            case self::LANGUAGE_TELUGU:
                return 'values-te';
            case self::LANGUAGE_TAJIK:
                return 'values-tg';
            case self::LANGUAGE_THAI:
                return 'values-th';
            case self::LANGUAGE_TAGALOG:
                return 'values-tl';
            case self::LANGUAGE_TURKISH:
                return 'values-tr';
            case self::LANGUAGE_TATAR:
                return 'values-tt';
            case self::LANGUAGE_UKRAINIAN:
                return 'values-uk';
            case self::LANGUAGE_UZBEK:
                return 'values-uz';
            case self::LANGUAGE_VIETNAMESE:
                return 'values-vi';
            case self::LANGUAGE_WALLOON:
                return 'values-wa';
            case self::LANGUAGE_YORUBA:
                return 'values-yo';
            case self::LANGUAGE_CHINESE_SIMPLIFIED:
                return 'values-zh-rCN';
            case self::LANGUAGE_CHINESE_TRADITIONAL:
                return 'values-zh-rTW';
            default:
                throw new Exception('Unknown language ID '.$this->id);
        }
    }

}