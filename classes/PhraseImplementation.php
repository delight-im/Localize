<?php

interface PhraseImplementation {

    /**
     * Decodes the content of a translation from raw output to internal text representation
     *
     * @param string $text the raw output to decode
     * @return string the internal text representation
     */
    public static function readFromRaw($text);

    /**
     * Encodes the content of a translation from internal text representation to raw output
     *
     * @param string $text the internal text representation to encode
	 * @param bool $escapeHTML whether to escape HTML or not
     * @return string the raw output
     */
    public static function writeToRaw($text, $escapeHTML);

    /**
     * Returns whether the given phrase key is valid (true) or not (false)
     *
     * @param string $phraseKey phrase key to check
     * @return bool whether the phrase key is valid
     */
    public static function isPhraseKeyValid($phraseKey);

    /**
     * Returns an array of placeholders that have been found in the given phrase text
     *
     * @param string $phraseText
     * @return array list of placeholders
     */
    public static function getPlaceholders($phraseText);

    /**
     * Returns all occurrences of leading or trailing whitespace that has been found in the given phrase text
     *
     * @param string $phraseText
     * @return array occurrences of leading or trailing whitespace
     */
    public static function getOuterWhitespace($phraseText);

    /**
     * Returns an array of HTML tags that have been found in the given phrase text
     *
     * @param string $phraseText
     * @return array list of HTML tags
     */
    public static function getHTMLTags($phraseText);

}