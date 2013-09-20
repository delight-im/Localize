<?php

class Helper {

    const ALPHABET_ID_ENCODE = '23456789bcdfghjklmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ'; // avoid 0, 1, a, e, i, o, u in alphabet to avoid offensive words (which need vowels)

    public static function encodeForURL($text) {
        $text = preg_replace('/[^a-z0-9_]+/i', '_', $text);
        $text = preg_replace('/(_)+$/i', '', $text);
        $text = preg_replace('/^(_)+/i', '', $text);
        return mb_strtolower($text);
    }

    public static function encodeID($value) {
        $out = '';
        while ($value > 0) {
            $rest = $value % 33;
            if ($rest >= 33) { return FALSE; }
            $out .= mb_substr(self::ALPHABET_ID_ENCODE, $rest, 1);
            $value = floor($value / 33);
        }
        $out = strrev($out);
        return $out;
    }

    public static function decodeID($value) {
        $out = 0;
        $value = strrev($value);
        $len = strlen($value);
        $n = 0;
        $base = 1;
        while($n < $len) {
            $c = mb_substr($value, $n, 1);
            $index = strpos(self::ALPHABET_ID_ENCODE, $c);
            if ($index === FALSE) { return FALSE; }
            $out += $base * $index;
            $base *= 33;
            $n++;
        }
        return $out;
    }

}