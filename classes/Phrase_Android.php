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
        $text = str_replace('\n', "\n", $text);
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
        $text = str_replace("\n", '\n', $text);
        $text = str_replace('\'', '\\\'', $text);
        $text = str_replace('"', '\\"', $text);

        $text = str_replace('&', '&#38;', $text); // do ampersand first in this group so that it will not be double-encoded
        $text = str_replace('...', '&#8230;', $text);

        return $text;
    }

}

?>