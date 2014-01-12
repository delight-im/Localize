<?php

require_once(__DIR__.'/../config.php');

class URL {

    const ROOT_URL = CONFIG_ROOT_URL; // string from config.php in root directory
    const URL_REWRITE = CONFIG_URL_REWRITE; // bool from config.php in root directory
    const BASE_PATH = CONFIG_BASE_PATH; // string from config.php in root directory
    const TEMP_PATH = CONFIG_TEMP_PATH; // string from config.php in root directory
    const UPLOAD_PATH = CONFIG_UPLOAD_PATH; // string from config.php in root directory
    const ALPHABET_ID_ENCODE = '23456789bcdfghjklmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ'; // avoid 0, 1, a, e, i, o, u in alphabet to avoid offensive words (which need vowels)

    public static function toPage($pageName) {
        if (self::URL_REWRITE) {
            return self::ROOT_URL.'pages/'.$pageName;
        }
        else {
            return self::ROOT_URL.'?p='.$pageName;
        }
    }

    public static function toResource($resourceName) {
        return self::ROOT_URL.$resourceName;
    }

    public static function toDashboard() {
        return self::ROOT_URL;
    }

    public static function toProject($repositoryID) {
        if (self::URL_REWRITE) {
            return self::ROOT_URL.'projects/'.self::encodeID($repositoryID);
        }
        else {
            return self::ROOT_URL.'?p=project&project='.self::encodeID($repositoryID);
        }
    }

    public static function toProjectShort($repositoryID) {
        if (self::URL_REWRITE) {
            return self::ROOT_URL.'v/'.self::encodeID($repositoryID);
        }
        else {
            return self::ROOT_URL.'?v='.self::encodeID($repositoryID);
        }
    }

    public static function toPhraseDetails($repositoryID, $languageID, $phraseID) {
        if (self::URL_REWRITE) {
            return self::ROOT_URL.'phrases/'.self::encodeID($repositoryID).'/'.self::encodeID($languageID).'/'.self::encodeID($phraseID);
        }
        else {
            return self::ROOT_URL.'?p=phrase&project='.self::encodeID($repositoryID).'&language='.self::encodeID($languageID).'&phrase='.self::encodeID($phraseID);
        }
    }

    public static function toLanguage($repositoryID, $languageID) {
        if (self::URL_REWRITE) {
            return self::ROOT_URL.'projects/'.self::encodeID($repositoryID).'/languages/'.self::encodeID($languageID);
        }
        else {
            return self::ROOT_URL.'?p=language&project='.self::encodeID($repositoryID).'&language='.self::encodeID($languageID);
        }
    }

    public static function toReview($repositoryID) {
        if (self::URL_REWRITE) {
            return self::ROOT_URL.'review/'.self::encodeID($repositoryID);
        }
        else {
            return self::ROOT_URL.'?p=review&project='.self::encodeID($repositoryID);
        }
    }

    public static function toReviewLanguage($repositoryID, $languageID) {
        if (self::URL_REWRITE) {
            return self::ROOT_URL.'review/'.self::encodeID($repositoryID).'/languages/'.self::encodeID($languageID);
        }
        else {
            return self::ROOT_URL.'?p=review&project='.self::encodeID($repositoryID).'&language='.self::encodeID($languageID);
        }
    }

    public static function toImport($repositoryID) {
        if (self::URL_REWRITE) {
            return self::ROOT_URL.'import/'.self::encodeID($repositoryID);
        }
        else {
            return self::ROOT_URL.'?p=import&project='.self::encodeID($repositoryID);
        }
    }

    public static function toExport($repositoryID) {
        if (self::URL_REWRITE) {
            return self::ROOT_URL.'export/'.self::encodeID($repositoryID);
        }
        else {
            return self::ROOT_URL.'?p=export&project='.self::encodeID($repositoryID);
        }
    }

    public static function toInvitations($repositoryID) {
        if (self::URL_REWRITE) {
            return self::ROOT_URL.'invitations/'.self::encodeID($repositoryID);
        }
        else {
            return self::ROOT_URL.'?p=invitations&project='.self::encodeID($repositoryID);
        }
    }

    public static function toAddPhrase($repositoryID, $languageID) {
        if (self::URL_REWRITE) {
            return self::ROOT_URL.'add_phrase/'.self::encodeID($repositoryID).'/languages/'.self::encodeID($languageID);
        }
        else {
            return self::ROOT_URL.'?p=add_phrase&project='.self::encodeID($repositoryID).'&language='.self::encodeID($languageID);
        }
    }

    public static function toEditProject($repositoryID, $toManageGroupsSection = false) {
        if (self::URL_REWRITE) {
            return self::ROOT_URL.'pages/create_project/'.self::encodeID($repositoryID).($toManageGroupsSection ? '#manage_groups' : '');
        }
        else {
            return self::ROOT_URL.'?p=create_project&project='.self::encodeID($repositoryID).($toManageGroupsSection ? '#manage_groups' : '');
        }
    }

    public static function toWatchProject($repositoryID) {
        if (self::URL_REWRITE) {
            return self::ROOT_URL.'watch/'.self::encodeID($repositoryID);
        }
        else {
            return self::ROOT_URL.'?p=watch&project='.self::encodeID($repositoryID);
        }
    }

    public static function toEmailVerification($verificationToken) {
        if (self::URL_REWRITE) {
            return self::ROOT_URL.'verify/'.urlencode($verificationToken);
        }
        else {
            return self::ROOT_URL.'?p=settings&verify='.urlencode($verificationToken);
        }
    }

    public static function toSettingsAction($action) {
        if (self::URL_REWRITE) {
            return self::ROOT_URL.'settings/action/'.urlencode($action);
        }
        else {
            return self::ROOT_URL.'?p=settings&action='.urlencode($action);
        }
    }

    public static function isProject($url) {
        return stripos($url, '?p=') !== false || stripos($url, 'projects/') !== false;
    }

    public static function getTempPath($isPublic) {
        if ($isPublic) {
            return self::ROOT_URL.self::TEMP_PATH;
        }
        else {
            return self::BASE_PATH.self::TEMP_PATH;
        }
    }

    public static function getUploadPath($isPublic) {
        if ($isPublic) {
            return self::ROOT_URL.self::UPLOAD_PATH;
        }
        else {
            return self::BASE_PATH.self::UPLOAD_PATH;
        }
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

    public static function encodeForURL($text) {
        $text = preg_replace('/[^a-z0-9_]+/i', '_', $text);
        $text = preg_replace('/(_)+$/i', '', $text);
        $text = preg_replace('/^(_)+/i', '', $text);
        return mb_strtolower($text);
    }

}