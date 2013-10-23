<?php

require_once('Phrase.php');
require_once('PhraseImplementation.php');

abstract class Phrase_Android extends Phrase implements PhraseImplementation {

    public function __construct($id, $phraseKey, $enabledForTranslation = true) {
        parent::__construct($id, $phraseKey, $enabledForTranslation);
    }

    /**
     * Decodes the content of a translation from raw output to internal text representation
     *
     * @param string $text the raw output to decode
     * @return string the internal text representation
     */
    public static function readFromRaw($text) {
        $text = preg_replace(self::NEWLINE_REGEX, '', $text); // remove real line breaks as only the control characters for line breaks may be taken into account and we do not want double-newlines

        $text = str_replace('\n', "\n", $text); // replace <newline literal> with <newline control character>
        $text = str_replace('\\\'', '\'', $text); // replace <escaped single quote> with <single quote>
        $text = str_replace('\\"', '"', $text); // replace <escaped double quote> with <double quote>

		// convert ampersand as the last item in the following group because it is still a special entity for the other three conversions
        $text = str_replace('&#60;', '<', $text); // replace <less-than entity> with <less-than>
		$text = str_replace('&#62;', '>', $text); // replace <greater-than entity> with <greater-than>
		$text = str_replace('&#8230;', '...', $text); // replace <ellipsis entity> with <three dots> that people can edit more easily
        $text = str_replace('&#38;', '&', $text); // replace <ampersand entity> with <ampersand>

        return $text;
    }

    /**
     * Encodes the content of a translation from internal text representation to raw output
     *
     * @param string $text the internal text representation to encode
	 * @param bool $escapeHTML whether to escape HTML or not
     * @return string the raw output
     */
    public static function writeToRaw($text, $escapeHTML) {
        $text = preg_replace(self::NEWLINE_REGEX, "\n", $text); // replace all newline control characters to the same representation

        $text = str_replace("\n", '\n', $text); // replace <newline control character> with <newline literal>
        $text = str_replace('\'', '\\\'', $text); // replace <single quote> with <escaped single quote>
        $text = str_replace('"', '\\"', $text); // replace <double quote> with <escaped double quote>

        // convert ampersand as the first item in the following group because it must not be treated as a special entity for the other three conversions
		$text = str_replace('&', '&#38;', $text); // replace <ampersand> with <ampersand entity>
		if ($escapeHTML) {
			$text = str_replace('<', '&#60;', $text); // replace <less-than> with <less-than entity>
			$text = str_replace('>', '&#62;', $text); // replace <greater-than> with <greater-than entity>
		}
        $text = str_replace('...', '&#8230;', $text); // replace <three dots> that people can edit more easily with <ellipsis entity>

        return $text;
    }

    /**
     * Returns whether the given phrase key is valid (true) or not (false)
     *
     * @param string $phraseKey phrase key to check
     * @return bool whether the phrase key is valid
     */
    public static function isPhraseKeyValid($phraseKey) {
        return preg_match('/^[a-z]+[a-zA-Z0-9_-]*$/', $phraseKey);
    }

    /**
     * Returns an array of placeholders that have been found in the given phrase text
     *
     * @param string $phraseText
     * @return array list of placeholders
     */
    public static function getPlaceholders($phraseText) {
        if (preg_match_all('/%(([0-9]+\$)?)([+\-#0]*)([0-9]*)(.[0-9]+)?((hh|h|l|ll|L|z|j|t)*)(d|i|u|f|F|e|E|g|G|x|X|o|s|S|c|C|a|A|b|B|h|H|p|n|%)/', $phraseText, $matches)) {
            return $matches[0];
        }
        else {
            return array();
        }
    }

}

?>