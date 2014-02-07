<?php

require_once('Phrase.php');
require_once('OutputContainer.php');

abstract class Language {

    const LANGUAGE_ENGLISH = 1;
    const LANGUAGE_AFRIKAANS = 2;
    const LANGUAGE_AMHARIC = 3;
    const LANGUAGE_ARABIC = 4;
    const LANGUAGE_AZERBAIJANI = 5;
    const LANGUAGE_BASHKIR = 6;
    const LANGUAGE_BELARUSIAN = 7;
    const LANGUAGE_BULGARIAN = 8;
    const LANGUAGE_BENGALI = 9;
    const LANGUAGE_BRETON = 10;
    const LANGUAGE_BOSNIAN = 11;
    const LANGUAGE_CATALAN = 12;
    const LANGUAGE_CZECH = 13;
    const LANGUAGE_CHUVASH = 14;
    const LANGUAGE_WELSH = 15;
    const LANGUAGE_DANISH = 16;
    const LANGUAGE_GERMAN = 17;
    const LANGUAGE_GREEK = 18;
    const LANGUAGE_SPANISH = 19;
    const LANGUAGE_ESTONIAN = 20;
    const LANGUAGE_BASQUE = 21;
    const LANGUAGE_PERSIAN = 22;
    const LANGUAGE_FINNISH = 23;
    const LANGUAGE_FRENCH = 24;
    const LANGUAGE_WESTERN_FRISIAN = 25;
    const LANGUAGE_IRISH = 26;
    const LANGUAGE_GALICIAN = 27;
    const LANGUAGE_GUJARATI = 28;
    const LANGUAGE_HINDI = 29;
    const LANGUAGE_HAITIAN = 30;
    const LANGUAGE_CROATIAN = 31;
    const LANGUAGE_HUNGARIAN = 32;
    const LANGUAGE_ARMENIAN = 33;
    const LANGUAGE_INDONESIAN = 34;
    const LANGUAGE_ICELANDIC = 35;
    const LANGUAGE_ITALIAN = 36;
    const LANGUAGE_HEBREW = 37;
    const LANGUAGE_JAPANESE = 38;
    const LANGUAGE_JAVANESE = 39;
    const LANGUAGE_GEORGIAN = 40;
    const LANGUAGE_KANNADA = 41;
    const LANGUAGE_KAZAKH = 42;
    const LANGUAGE_KOREAN = 43;
    const LANGUAGE_KURDISH = 44;
    const LANGUAGE_KIRGHIZ = 45;
    const LANGUAGE_LUXEMBOURGISH = 46;
    const LANGUAGE_LITHUANIAN = 47;
    const LANGUAGE_LATVIAN = 48;
    const LANGUAGE_MALAGASY = 49;
    const LANGUAGE_MACEDONIAN = 50;
    const LANGUAGE_MALAYALAM = 51;
    const LANGUAGE_MARATHI = 52;
    const LANGUAGE_MALAY = 53;
    const LANGUAGE_NEPALI = 54;
    const LANGUAGE_NORWEGIAN_BOKMAL = 55;
    const LANGUAGE_DUTCH = 56;
    const LANGUAGE_NORWEGIAN_NYNORSK = 57;
    const LANGUAGE_OCCITAN = 58;
    const LANGUAGE_POLISH = 59;
    const LANGUAGE_PORTUGUESE_BRAZIL = 60;
    const LANGUAGE_PORTUGUESE_PORTUGAL = 61;
    const LANGUAGE_ROMANIAN = 62;
    const LANGUAGE_RUSSIAN = 63;
    const LANGUAGE_SLOVAK = 64;
    const LANGUAGE_SLOVENE = 65;
    const LANGUAGE_ALBANIAN = 66;
    const LANGUAGE_SERBIAN = 67;
    const LANGUAGE_SUNDANESE = 68;
    const LANGUAGE_SWEDISH = 69;
    const LANGUAGE_SWAHILI = 70;
    const LANGUAGE_TELUGU = 71;
    const LANGUAGE_TAJIK = 72;
    const LANGUAGE_THAI = 73;
    const LANGUAGE_TAGALOG = 74;
    const LANGUAGE_TURKISH = 75;
    const LANGUAGE_TATAR = 76;
    const LANGUAGE_UKRAINIAN = 77;
    const LANGUAGE_UZBEK = 78;
    const LANGUAGE_VIETNAMESE = 79;
    const LANGUAGE_WALLOON = 80;
    const LANGUAGE_YORUBA = 81;
    const LANGUAGE_CHINESE_SIMPLIFIED = 82;
    const LANGUAGE_CHINESE_TRADITIONAL = 83;
    const LANGUAGE_ARAGONESE = 84;
    const LANGUAGE_HAUSA = 85;
    const LANGUAGE_IGBO = 86;
    const LANGUAGE_KHMER = 87;
    const LANGUAGE_LAO = 88;
    const LANGUAGE_MALTESE = 89;
    const LANGUAGE_MAORI = 90;
    const LANGUAGE_PUNJABI = 91;
    const LANGUAGE_SOMALI = 92;
    const LANGUAGE_TAMIL = 93;
    const LANGUAGE_URDU = 94;
    const LANGUAGE_YIDDISH = 95;
    const LANGUAGE_ZULU = 96;

    /**
     * ID of this language
     *
     * @var int
     */
    protected $id;
    /**
     * List of phrases for this language
     *
     * @var array|Phrase[]
     */
    protected $phrases;

    /**
     * Constructs the platform-specific output for this Language object
     *
     * @param bool $escapeHTML whether to escape HTML (true) or not (false)
     * @param int $groupID the group ID to get the output for (or Phrase::GROUP_ALL)
     * @return OutputContainer the output object containing both data and completeness in percent
     */
    abstract public function output($escapeHTML, $groupID);

    /**
     * Returns the platform-specific key (string) for this language
     *
     * @return string key for this language
     */
    abstract public function getKey();

    public function __construct($id) {
        $this->id = $id;
        $this->phrases = array();
    }

    public function getID() {
        return $this->id;
    }

    /**
     * @return array|Phrase[] list of Phrase instances that are part of this language for the given project
     */
    public function getPhrases() {
        return $this->phrases;
    }

    public function getPhraseByKey($searchKey) {
        if (isset($this->phrases[$searchKey])) {
            $out = $this->phrases[$searchKey];
        }
        else {
            $out = NULL;
        }
        return $out;
    }

    public function addPhrase($phrase) {
        if ($phrase instanceof Phrase) {
            $this->phrases[$phrase->getPhraseKey()] = $phrase;
        }
        else {
            throw new Exception('The phrase must be an instance of class Phrase');
        }
    }

    public function removePhrase($phraseKey) {
        unset($this->phrases[$phraseKey]);
    }

    /**
     * Normalizes the phrase with the given phrase key by comparing it to the given reference phrase
     *
     * @param string $phraseKey
     * @param Phrase $referencePhrase
     * @param boolean $prefillContent whether to pre-fill the phrase with the default language's content (true) or not (false)
     */
    public function normalizePhrase($phraseKey, $referencePhrase, $prefillContent) {
        $this->phrases[$phraseKey]->setGroupID($referencePhrase->getGroupID());
        if ($prefillContent) {
            $phraseValues = $this->phrases[$phraseKey]->getPhraseValues();
            $referenceValues = $referencePhrase->getPhraseValues();
            foreach ($phraseValues as $subKey => $value) {
                if ($value == '') {
                    $this->phrases[$phraseKey]->setPhraseValue($subKey, $referenceValues[$subKey]);
                }
            }
        }
    }

    public function getNameFull() {
        return self::getLanguageNameFull($this->id);
    }

    public static function getLanguageNameFull($languageID) {
        return self::getLanguageName($languageID).' ('.self::getLanguageNameNative($languageID).')';
    }

    public function getName() {
        return self::getLanguageName($this->id);
    }

    public static function getLanguageName($languageID) {
        switch ($languageID) {
            case self::LANGUAGE_ENGLISH:
                return 'English';
            case self::LANGUAGE_AFRIKAANS:
                return 'Afrikaans';
            case self::LANGUAGE_AMHARIC:
                return 'Amharic';
            case self::LANGUAGE_ARABIC:
                return 'Arabic';
            case self::LANGUAGE_AZERBAIJANI:
                return 'Azerbaijani';
            case self::LANGUAGE_BASHKIR:
                return 'Bashkir';
            case self::LANGUAGE_BELARUSIAN:
                return 'Belarusian';
            case self::LANGUAGE_BULGARIAN:
                return 'Bulgarian';
            case self::LANGUAGE_BENGALI:
                return 'Bengali';
            case self::LANGUAGE_BRETON:
                return 'Breton';
            case self::LANGUAGE_BOSNIAN:
                return 'Bosnian';
            case self::LANGUAGE_CATALAN:
                return 'Catalan';
            case self::LANGUAGE_CZECH:
                return 'Czech';
            case self::LANGUAGE_CHUVASH:
                return 'Chuvash';
            case self::LANGUAGE_WELSH:
                return 'Welsh';
            case self::LANGUAGE_DANISH:
                return 'Danish';
            case self::LANGUAGE_GERMAN:
                return 'German';
            case self::LANGUAGE_GREEK:
                return 'Greek';
            case self::LANGUAGE_SPANISH:
                return 'Spanish';
            case self::LANGUAGE_ESTONIAN:
                return 'Estonian';
            case self::LANGUAGE_BASQUE:
                return 'Basque';
            case self::LANGUAGE_PERSIAN:
                return 'Persian';
            case self::LANGUAGE_FINNISH:
                return 'Finnish';
            case self::LANGUAGE_FRENCH:
                return 'French';
            case self::LANGUAGE_WESTERN_FRISIAN:
                return 'Western Frisian';
            case self::LANGUAGE_IRISH:
                return 'Irish';
            case self::LANGUAGE_GALICIAN:
                return 'Galician';
            case self::LANGUAGE_GUJARATI:
                return 'Gujarati';
            case self::LANGUAGE_HINDI:
                return 'Hindi';
            case self::LANGUAGE_HAITIAN:
                return 'Haitian';
            case self::LANGUAGE_CROATIAN:
                return 'Croatian';
            case self::LANGUAGE_HUNGARIAN:
                return 'Hungarian';
            case self::LANGUAGE_ARMENIAN:
                return 'Armenian';
            case self::LANGUAGE_INDONESIAN:
                return 'Indonesian';
            case self::LANGUAGE_ICELANDIC:
                return 'Icelandic';
            case self::LANGUAGE_ITALIAN:
                return 'Italian';
            case self::LANGUAGE_HEBREW:
                return 'Hebrew';
            case self::LANGUAGE_JAPANESE:
                return 'Japanese';
            case self::LANGUAGE_JAVANESE:
                return 'Javanese';
            case self::LANGUAGE_GEORGIAN:
                return 'Georgian';
            case self::LANGUAGE_KANNADA:
                return 'Kannada';
            case self::LANGUAGE_KAZAKH:
                return 'Kazakh';
            case self::LANGUAGE_KOREAN:
                return 'Korean';
            case self::LANGUAGE_KURDISH:
                return 'Kurdish';
            case self::LANGUAGE_KIRGHIZ:
                return 'Kirghiz';
            case self::LANGUAGE_LUXEMBOURGISH:
                return 'Luxembourgish';
            case self::LANGUAGE_LITHUANIAN:
                return 'Lithuanian';
            case self::LANGUAGE_LATVIAN:
                return 'Latvian';
            case self::LANGUAGE_MALAGASY:
                return 'Malagasy';
            case self::LANGUAGE_MACEDONIAN:
                return 'Macedonian';
            case self::LANGUAGE_MALAYALAM:
                return 'Malayalam';
            case self::LANGUAGE_MARATHI:
                return 'Marathi';
            case self::LANGUAGE_MALAY:
                return 'Malay';
            case self::LANGUAGE_NEPALI:
                return 'Nepali';
            case self::LANGUAGE_NORWEGIAN_BOKMAL:
                return 'Norwegian Bokmål';
            case self::LANGUAGE_DUTCH:
                return 'Dutch';
            case self::LANGUAGE_NORWEGIAN_NYNORSK:
                return 'Norwegian Nynorsk';
            case self::LANGUAGE_OCCITAN:
                return 'Occitan';
            case self::LANGUAGE_POLISH:
                return 'Polish';
            case self::LANGUAGE_PORTUGUESE_BRAZIL:
                return 'Portuguese (Brazil)';
            case self::LANGUAGE_PORTUGUESE_PORTUGAL:
                return 'Portuguese (Portugal)';
            case self::LANGUAGE_ROMANIAN:
                return 'Romanian';
            case self::LANGUAGE_RUSSIAN:
                return 'Russian';
            case self::LANGUAGE_SLOVAK:
                return 'Slovak';
            case self::LANGUAGE_SLOVENE:
                return 'Slovene';
            case self::LANGUAGE_ALBANIAN:
                return 'Albanian';
            case self::LANGUAGE_SERBIAN:
                return 'Serbian';
            case self::LANGUAGE_SUNDANESE:
                return 'Sundanese';
            case self::LANGUAGE_SWEDISH:
                return 'Swedish';
            case self::LANGUAGE_SWAHILI:
                return 'Swahili';
            case self::LANGUAGE_TELUGU:
                return 'Telugu';
            case self::LANGUAGE_TAJIK:
                return 'Tajik';
            case self::LANGUAGE_THAI:
                return 'Thai';
            case self::LANGUAGE_TAGALOG:
                return 'Tagalog';
            case self::LANGUAGE_TURKISH:
                return 'Turkish';
            case self::LANGUAGE_TATAR:
                return 'Tatar';
            case self::LANGUAGE_UKRAINIAN:
                return 'Ukrainian';
            case self::LANGUAGE_UZBEK:
                return 'Uzbek';
            case self::LANGUAGE_VIETNAMESE:
                return 'Vietnamese';
            case self::LANGUAGE_WALLOON:
                return 'Walloon';
            case self::LANGUAGE_YORUBA:
                return 'Yoruba';
            case self::LANGUAGE_CHINESE_SIMPLIFIED:
                return 'Chinese (Simplified)';
            case self::LANGUAGE_CHINESE_TRADITIONAL:
                return 'Chinese (Traditional)';
            case self::LANGUAGE_ARAGONESE:
                return 'Aragonese';
            case self::LANGUAGE_HAUSA:
                return 'Hausa';
            case self::LANGUAGE_IGBO:
                return 'Igbo';
            case self::LANGUAGE_KHMER:
                return 'Khmer';
            case self::LANGUAGE_LAO:
                return 'Lao';
            case self::LANGUAGE_MALTESE:
                return 'Maltese';
            case self::LANGUAGE_MAORI:
                return 'Maori';
            case self::LANGUAGE_PUNJABI:
                return 'Punjabi';
            case self::LANGUAGE_SOMALI:
                return 'Somali';
            case self::LANGUAGE_TAMIL:
                return 'Tamil';
            case self::LANGUAGE_URDU:
                return 'Urdu';
            case self::LANGUAGE_YIDDISH:
                return 'Yiddish';
            case self::LANGUAGE_ZULU:
                return 'Zulu';
            default:
                throw new Exception('Unknown language ID '.$languageID);
        }
    }

    public function getNameNative() {
        return self::getLanguageNameNative($this->id);
    }

    public static function getLanguageNameNative($languageID) {
        switch ($languageID) {
            case self::LANGUAGE_ENGLISH:
                return 'English';
            case self::LANGUAGE_AFRIKAANS:
                return 'Afrikaans';
            case self::LANGUAGE_AMHARIC:
                return 'አማርኛ';
            case self::LANGUAGE_ARABIC:
                return 'العربية';
            case self::LANGUAGE_AZERBAIJANI:
                return 'Azərbaycan';
            case self::LANGUAGE_BASHKIR:
                return 'Башҡортса';
            case self::LANGUAGE_BELARUSIAN:
                return 'беларуская мова';
            case self::LANGUAGE_BULGARIAN:
                return 'български';
            case self::LANGUAGE_BENGALI:
                return 'বাংলা';
            case self::LANGUAGE_BRETON:
                return 'Brezhoneg';
            case self::LANGUAGE_BOSNIAN:
                return 'Bosanski';
            case self::LANGUAGE_CATALAN:
                return 'Català';
            case self::LANGUAGE_CZECH:
                return 'Česky';
            case self::LANGUAGE_CHUVASH:
                return 'Чӑвашла';
            case self::LANGUAGE_WELSH:
                return 'Cymraeg';
            case self::LANGUAGE_DANISH:
                return 'Dansk';
            case self::LANGUAGE_GERMAN:
                return 'Deutsch';
            case self::LANGUAGE_GREEK:
                return 'ελληνικά';
            case self::LANGUAGE_SPANISH:
                return 'Español';
            case self::LANGUAGE_ESTONIAN:
                return 'Eesti';
            case self::LANGUAGE_BASQUE:
                return 'Euskara';
            case self::LANGUAGE_PERSIAN:
                return 'فارسی';
            case self::LANGUAGE_FINNISH:
                return 'Suomi';
            case self::LANGUAGE_FRENCH:
                return 'Français';
            case self::LANGUAGE_WESTERN_FRISIAN:
                return 'Frysk';
            case self::LANGUAGE_IRISH:
                return 'Gaeilge';
            case self::LANGUAGE_GALICIAN:
                return 'Galego';
            case self::LANGUAGE_GUJARATI:
                return 'ગુજરાતી';
            case self::LANGUAGE_HINDI:
                return 'हिन्दी';
            case self::LANGUAGE_HAITIAN:
                return 'Kreyòl Ayisyen';
            case self::LANGUAGE_CROATIAN:
                return 'Hrvatski';
            case self::LANGUAGE_HUNGARIAN:
                return 'Magyar';
            case self::LANGUAGE_ARMENIAN:
                return 'Հայերեն';
            case self::LANGUAGE_INDONESIAN:
                return 'Bahasa Indonesia';
            case self::LANGUAGE_ICELANDIC:
                return 'Íslenska';
            case self::LANGUAGE_ITALIAN:
                return 'Italiano';
            case self::LANGUAGE_HEBREW:
                return 'עברית';
            case self::LANGUAGE_JAPANESE:
                return '日本語';
            case self::LANGUAGE_JAVANESE:
                return 'Basa Jawa';
            case self::LANGUAGE_GEORGIAN:
                return 'ქართული';
            case self::LANGUAGE_KANNADA:
                return 'ಕನ್ನಡ';
            case self::LANGUAGE_KAZAKH:
                return 'Қазақ тілі';
            case self::LANGUAGE_KOREAN:
                return '한국어';
            case self::LANGUAGE_KURDISH:
                return 'Kurdî';
            case self::LANGUAGE_KIRGHIZ:
                return 'Кыргызча';
            case self::LANGUAGE_LUXEMBOURGISH:
                return 'Lëtzebuergesch';
            case self::LANGUAGE_LITHUANIAN:
                return 'Lietuvių';
            case self::LANGUAGE_LATVIAN:
                return 'Latviešu';
            case self::LANGUAGE_MALAGASY:
                return 'Malagasy';
            case self::LANGUAGE_MACEDONIAN:
                return 'Македонски';
            case self::LANGUAGE_MALAYALAM:
                return 'മലയാളം';
            case self::LANGUAGE_MARATHI:
                return 'मराठी';
            case self::LANGUAGE_MALAY:
                return 'Bahasa Melayu';
            case self::LANGUAGE_NEPALI:
                return 'नेपाली';
            case self::LANGUAGE_NORWEGIAN_BOKMAL:
                return 'Norsk bokmål';
            case self::LANGUAGE_DUTCH:
                return 'Nederlands';
            case self::LANGUAGE_NORWEGIAN_NYNORSK:
                return 'Norsk nynorsk';
            case self::LANGUAGE_OCCITAN:
                return 'Occitan';
            case self::LANGUAGE_POLISH:
                return 'Polski';
            case self::LANGUAGE_PORTUGUESE_BRAZIL:
                return 'Português';
            case self::LANGUAGE_PORTUGUESE_PORTUGAL:
                return 'Português';
            case self::LANGUAGE_ROMANIAN:
                return 'Română';
            case self::LANGUAGE_RUSSIAN:
                return 'Русский';
            case self::LANGUAGE_SLOVAK:
                return 'Slovenčina';
            case self::LANGUAGE_SLOVENE:
                return 'Slovenščina';
            case self::LANGUAGE_ALBANIAN:
                return 'Shqip';
            case self::LANGUAGE_SERBIAN:
                return 'Српски';
            case self::LANGUAGE_SUNDANESE:
                return 'Basa Sunda';
            case self::LANGUAGE_SWEDISH:
                return 'Svenska';
            case self::LANGUAGE_SWAHILI:
                return 'Kiswahili';
            case self::LANGUAGE_TELUGU:
                return 'తెలుగు';
            case self::LANGUAGE_TAJIK:
                return 'Тоҷикӣ';
            case self::LANGUAGE_THAI:
                return 'ไทย';
            case self::LANGUAGE_TAGALOG:
                return 'Tagalog';
            case self::LANGUAGE_TURKISH:
                return 'Türkçe';
            case self::LANGUAGE_TATAR:
                return 'Татарча';
            case self::LANGUAGE_UKRAINIAN:
                return 'Українська';
            case self::LANGUAGE_UZBEK:
                return 'Oʻzbekcha';
            case self::LANGUAGE_VIETNAMESE:
                return 'Tiếng Việt';
            case self::LANGUAGE_WALLOON:
                return 'Walon';
            case self::LANGUAGE_YORUBA:
                return 'Yorùbá';
            case self::LANGUAGE_CHINESE_SIMPLIFIED:
                return '中文';
            case self::LANGUAGE_CHINESE_TRADITIONAL:
                return '中文';
            case self::LANGUAGE_ARAGONESE:
                return 'Aragonés';
            case self::LANGUAGE_HAUSA:
                return 'Hausa';
            case self::LANGUAGE_IGBO:
                return 'Asụsụ Igbo';
            case self::LANGUAGE_KHMER:
                return 'ភាសាខ្មែរ';
            case self::LANGUAGE_LAO:
                return 'ພາສາລາວ';
            case self::LANGUAGE_MALTESE:
                return 'Malti';
            case self::LANGUAGE_MAORI:
                return 'Māori';
            case self::LANGUAGE_PUNJABI:
                return 'ਪੰਜਾਬੀ';
            case self::LANGUAGE_SOMALI:
                return 'Af-Soomaali';
            case self::LANGUAGE_TAMIL:
                return 'தமிழ்';
            case self::LANGUAGE_URDU:
                return 'Urdū';
            case self::LANGUAGE_YIDDISH:
                return 'ייִדיש';
            case self::LANGUAGE_ZULU:
                return 'isiZulu';
            default:
                throw new Exception('Unknown language ID '.$languageID);
        }
    }

    public function isRTL() {
        return self::isLanguageRTL($this->id);
    }

    public static function isLanguageRTL($languageID) {
        return $languageID == self::LANGUAGE_ARABIC || $languageID == self::LANGUAGE_PERSIAN || $languageID == self::LANGUAGE_HEBREW || $languageID == self::LANGUAGE_YIDDISH || $languageID == self::LANGUAGE_URDU;
    }

    public static function getList($defaultLanguage = self::LANGUAGE_ENGLISH) {
        $list = array(
            self::LANGUAGE_AFRIKAANS,
            self::LANGUAGE_ALBANIAN,
            self::LANGUAGE_AMHARIC,
            self::LANGUAGE_ARABIC,
            self::LANGUAGE_ARAGONESE,
            self::LANGUAGE_ARMENIAN,
            self::LANGUAGE_AZERBAIJANI,
            self::LANGUAGE_BASHKIR,
            self::LANGUAGE_BASQUE,
            self::LANGUAGE_BELARUSIAN,
            self::LANGUAGE_BENGALI,
            self::LANGUAGE_BOSNIAN,
            self::LANGUAGE_BRETON,
            self::LANGUAGE_BULGARIAN,
            self::LANGUAGE_CATALAN,
            self::LANGUAGE_CHINESE_SIMPLIFIED,
            self::LANGUAGE_CHINESE_TRADITIONAL,
            self::LANGUAGE_CHUVASH,
            self::LANGUAGE_CROATIAN,
            self::LANGUAGE_CZECH,
            self::LANGUAGE_DANISH,
            self::LANGUAGE_DUTCH,
            self::LANGUAGE_ENGLISH,
            self::LANGUAGE_ESTONIAN,
            self::LANGUAGE_FINNISH,
            self::LANGUAGE_FRENCH,
            self::LANGUAGE_GALICIAN,
            self::LANGUAGE_GEORGIAN,
            self::LANGUAGE_GERMAN,
            self::LANGUAGE_GREEK,
            self::LANGUAGE_GUJARATI,
            self::LANGUAGE_HAITIAN,
            self::LANGUAGE_HAUSA,
            self::LANGUAGE_HEBREW,
            self::LANGUAGE_HINDI,
            self::LANGUAGE_HUNGARIAN,
            self::LANGUAGE_ICELANDIC,
            self::LANGUAGE_IGBO,
            self::LANGUAGE_INDONESIAN,
            self::LANGUAGE_IRISH,
            self::LANGUAGE_ITALIAN,
            self::LANGUAGE_JAPANESE,
            self::LANGUAGE_JAVANESE,
            self::LANGUAGE_KANNADA,
            self::LANGUAGE_KAZAKH,
            self::LANGUAGE_KHMER,
            self::LANGUAGE_KIRGHIZ,
            self::LANGUAGE_KOREAN,
            self::LANGUAGE_KURDISH,
            self::LANGUAGE_LAO,
            self::LANGUAGE_LATVIAN,
            self::LANGUAGE_LITHUANIAN,
            self::LANGUAGE_LUXEMBOURGISH,
            self::LANGUAGE_MACEDONIAN,
            self::LANGUAGE_MALAGASY,
            self::LANGUAGE_MALAY,
            self::LANGUAGE_MALAYALAM,
            self::LANGUAGE_MALTESE,
            self::LANGUAGE_MAORI,
            self::LANGUAGE_MARATHI,
            self::LANGUAGE_NEPALI,
            self::LANGUAGE_NORWEGIAN_BOKMAL,
            self::LANGUAGE_NORWEGIAN_NYNORSK,
            self::LANGUAGE_OCCITAN,
            self::LANGUAGE_PERSIAN,
            self::LANGUAGE_POLISH,
            self::LANGUAGE_PORTUGUESE_BRAZIL,
            self::LANGUAGE_PORTUGUESE_PORTUGAL,
            self::LANGUAGE_PUNJABI,
            self::LANGUAGE_ROMANIAN,
            self::LANGUAGE_RUSSIAN,
            self::LANGUAGE_SERBIAN,
            self::LANGUAGE_SLOVAK,
            self::LANGUAGE_SLOVENE,
            self::LANGUAGE_SOMALI,
            self::LANGUAGE_SPANISH,
            self::LANGUAGE_SUNDANESE,
            self::LANGUAGE_SWAHILI,
            self::LANGUAGE_SWEDISH,
            self::LANGUAGE_TAGALOG,
            self::LANGUAGE_TAJIK,
            self::LANGUAGE_TAMIL,
            self::LANGUAGE_TATAR,
            self::LANGUAGE_TELUGU,
            self::LANGUAGE_THAI,
            self::LANGUAGE_TURKISH,
            self::LANGUAGE_UKRAINIAN,
            self::LANGUAGE_URDU,
            self::LANGUAGE_UZBEK,
            self::LANGUAGE_VIETNAMESE,
            self::LANGUAGE_WALLOON,
            self::LANGUAGE_WELSH,
            self::LANGUAGE_WESTERN_FRISIAN,
            self::LANGUAGE_YIDDISH,
            self::LANGUAGE_YORUBA,
            self::LANGUAGE_ZULU
        );

        $out = array();
        $out[] = $defaultLanguage;
        foreach ($list as $language) {
            if ($language != $defaultLanguage) {
                $out[] = $language;
            }
        }

        return $out;
    }

    /**
     * Returns the percentage of completion for this language where 0.0 is empty and 1.0 is completed
     *
     * @return float the percentage of completion for this language
     */
    public function getCompleteness() {
        $complete = 0;
        $total = 0;
        foreach ($this->phrases as $phrase) {
            /** @var Phrase $phrase */
            $res = $phrase->getCompleteness();
            $complete += $res[0];
            $total += $res[1];
        }
        if ($total > 0) {
            return $complete/$total;
        }
        else {
            return 1;
        }
    }

    public function sortKeysAlphabetically() {
        uksort($this->phrases, 'strcasecmp');
    }

    public function sortUntranslatedFirst() {
        uasort($this->phrases, 'Language::sortUntranslatedFirstCompare');
    }

    /**
     * Comparator for phrases so that empty phrases are shown first
     *
     * @param Phrase $a
     * @param Phrase $b
     * @return int 1 if $a is less complete then $b, 0 if both are equally complete, and -1 otherwise
     */
    public static function sortUntranslatedFirstCompare($a, $b) {
        $aCompletenessData = $a->getCompleteness();
        $aCompleteness = $aCompletenessData[0] / $aCompletenessData[1];
        $bCompletenessData = $b->getCompleteness();
        $bCompleteness = $bCompletenessData[0] / $bCompletenessData[1];
        if ($aCompleteness == $bCompleteness) {
            $res = 0;
            return $res;
        }
        else {
            $res = ($aCompleteness > $bCompleteness) ? 1 : -1;
            return $res;
        }
    }

}