# Translation memory

Create a PHP file with the following code and run it in order to create a translation memory from your installation.

You can specify the minimum frequency that is required in the following line:

`$memory = new TranslationMemory(3);`

The output file will have the same name as the PHP file that you're calling.

## Code

```
<?php

header('Content-type: text/plain; charset=utf-8');
mb_internal_encoding('UTF-8');

// TODO use PDO instead
mysql_connect('localhost', 'root', '');
mysql_select_db('localize');

class TranslationMemory {

	protected $occurrences;
	protected $minFrequency;

	public function __construct($minFrequency = 1) {
		$this->occurrences = array();
		$this->minFrequency = $minFrequency;
	}

	public function addPhrase($languageId, $projectId, $phraseId, $originalText, $translationText) {
		if (empty($translationText) || empty($originalText)) {
			return;
		}

		if (!isset($this->occurrences[$languageId])) {
			$this->occurrences[$languageId] = array();
		}

		if (!isset($this->occurrences[$languageId][$phraseId])) {
			$this->occurrences[$languageId][$phraseId] = array(
				'original' => $originalText,
				'text' => $translationText,
				'count' => array()
			);
		}

		if (!in_array($projectId, $this->occurrences[$languageId][$phraseId]['count'])) {
			$this->occurrences[$languageId][$phraseId]['count'][] = $projectId;
		}
	}

	public function getPhrases() {
		$this->calculateFrequencies();
		$this->filterByFrequency();
		$this->normalizeStructure();
		$this->sortByFrequency();

		return $this->occurrences;
	}

	protected function normalizeStructure() {
		$new = array();

		foreach ($this->occurrences as $languageId => $languageData) {
			if (empty($languageData)) {
				unset($this->occurrences[$languageId]);
			}
			else {
				$languageCode = LanguageManager::getLanguageCode($languageId);
				$new[$languageCode] = $languageData;
			}
		}

		$this->occurrences = $new;
	}

	protected function calculateFrequencies() {
		foreach ($this->occurrences as &$language) {
			$language = array_map(function ($obj) {
				$obj['count'] = count($obj['count']);
				return $obj;
			}, $language);
		}
	}

	protected function filterByFrequency() {
		foreach ($this->occurrences as &$language) {
			$language = array_filter($language, function ($obj) {
				return $obj['count'] >= $this->minFrequency;
			});
		}
	}

	protected function sortByFrequency() {
		foreach ($this->occurrences as &$language) {
			usort($language, function ($a, $b) {
				if ($a['count'] == $b['count']) {
					return strcmp($a['original'], $b['original']);
				}
				else {
					return $a['count'] <= $b['count'];
				}
			});
		}
	}

}

class ServiceManager {

	const LANGUAGE_DEFAULT_ID = 1;

	protected $translations;

	public function __construct() {
		$this->translations = array();
	}

	public function addTranslation($languageId, $projectId, $phraseKey, $payload) {
		if (!isset($this->translations[$projectId])) {
			$this->translations[$projectId] = array();
		}

		if (!isset($this->translations[$projectId][$phraseKey])) {
			$this->translations[$projectId][$phraseKey] = array();
		}

		$this->translations[$projectId][$phraseKey][$languageId] = $payload;
	}

	public static function parsePayload($jsonPayload) {
		return json_decode($jsonPayload, true);
	}

	public function getTranslations() {
		$this->normalizeTranslations();

		return $this->translations;
	}

	public static function extractPhrases($payload) {
		if (!isset($payload['class'])) {
			return [];
		}

		if ($payload['class'] === 'Phrase_Android_String') {
			$phrases = array();

			$id = self::createUniqueId($payload['value']);
			$phrases[$id] = self::escapeControlCharacters($payload['value']);

			return $phrases;
		}
		elseif ($payload['class'] === 'Phrase_Android_StringArray') {
			$phrases = array();

			foreach ($payload['values'] as $value) {
				$id = self::createUniqueId($value);
				$phrases[$id] = self::escapeControlCharacters($value);
			}

			return $phrases;
		}
		elseif ($payload['class'] === 'Phrase_Android_Plurals') {
			$phrases = array();

			foreach ($payload['values'] as $quantity => $value) {
				$id = self::createUniqueId($value, $quantity);
				$phrases[$id] = self::escapeControlCharacters($value);
			}

			return $phrases;
		}
		else {
			throw new Exception('Unexpected payload');
		}
	}

	protected function normalizeTranslations() {
		foreach ($this->translations as $projectId => $phrases) {
			foreach ($phrases as $phraseKey => $languages) {
				// if there is no text in the default language for this phrase
				if (empty($languages[self::LANGUAGE_DEFAULT_ID])) {
					// delete the complete phrase as we have no mapping here
					unset($this->translations[$projectId][$phraseKey]);
				}
				// if there is some text for this phrase in the default language
				else {
					foreach ($languages as $languageId => $payload) {
						// if this is the default language
						if ($languageId === self::LANGUAGE_DEFAULT_ID) {
							continue;
						}

						$this->translations[$projectId][$phraseKey][$languageId] = array(
							'original' => self::extractPhrases($this->translations[$projectId][$phraseKey][self::LANGUAGE_DEFAULT_ID]),
							'translation' => self::extractPhrases($this->translations[$projectId][$phraseKey][$languageId])
						);
					}

					// delete the text in the default language
					unset($this->translations[$projectId][$phraseKey][self::LANGUAGE_DEFAULT_ID]);
				}
			}
		}
	}

	protected static function createUniqueId($phraseText, $quantity = NULL) {
		$phraseText = trim($phraseText);
		$phraseText = mb_strtolower($phraseText);

		$id = md5($phraseText);

		if (isset($quantity)) {
			$id = md5($quantity . $id);
		}

		return $id;
	}

	protected static function escapeControlCharacters($text) {
		$text = str_replace("\r", '\r', $text);
		$text = str_replace("\n", '\n', $text);
		$text = str_replace("\t", '\t', $text);

		return $text;
	}

}

class LanguageManager {

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

}

$manager = new ServiceManager();
$memory = new TranslationMemory(3);

$res = mysql_query("SELECT repositoryID, languageID, phraseKey, payload FROM phrases");
while ($row = mysql_fetch_assoc($res)) {
	$payload = ServiceManager::parsePayload($row['payload']);
	$manager->addTranslation($row['languageID'], $row['repositoryID'], $row['phraseKey'], $payload);
}

$translations = $manager->getTranslations();
foreach ($translations as $projectId => $phrases) {
	foreach ($phrases as $phraseKey => $languages) {
		foreach ($languages as $languageId => $data) {
			foreach ($data['original'] as $idOriginal => $phraseOriginal) {
				if (list($idTranslation, $phraseTranslation) = each($data['translation'])) {
					$phraseId = $idOriginal . $idTranslation;
					$memory->addPhrase($languageId, $projectId, $phraseId, $phraseOriginal, $phraseTranslation);
				}
			}
		}
	}
}

$output = $memory->getPhrases();
$outputJson = json_encode($output, JSON_PRETTY_PRINT);

$outputFilename = str_replace('.php', '.txt', basename(__FILE__));
file_put_contents($outputFilename, $outputJson);
```
