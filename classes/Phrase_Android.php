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

        $text = str_replace('\n', "\n", $text); // replace literal with control character
        $text = str_replace('\\\'', '\'', $text);
        $text = str_replace('\\"', '"', $text);

        $text = str_replace('&#8230;', '...', $text);
        $text = str_replace('&#38;', '&', $text); // do ampersand last in this group so that it will not be double-decoded

        return $text;
    }

    /**
     * Encodes the content of a translation from internal text representation to raw output
     *
     * @param string $text the internal text representation to encode
     * @return string the raw output
     */
    public static function writeToRaw($text) {
        $text = preg_replace(self::NEWLINE_REGEX, "\n", $text); // replace all newline control characters to the same representation

        $text = str_replace("\n", '\n', $text); // replace control character with literal
        $text = str_replace('\'', '\\\'', $text);
        $text = str_replace('"', '\\"', $text);

        $text = str_replace('&', '&#38;', $text); // do ampersand first in this group so that it will not be double-encoded
        $text = str_replace('...', '&#8230;', $text);

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