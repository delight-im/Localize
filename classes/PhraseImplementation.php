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
     * @return string the raw output
     */
    public static function writeToRaw($text);

}