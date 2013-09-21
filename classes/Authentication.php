<?php

class Authentication {

    const SESSION_HTTPS = false;
    const SESSION_HTTP_ONLY = true;

    public static function init() {
        ini_set('session.use_only_cookies', 1); // use cookies only (no session IDs that are sent via GET)
        ini_set('session.cookie_lifetime', 0); // total session lifetime is not limited (until the browser is closed)
        ini_set('session.gc_maxlifetime', 43200); // session may time out if user is not active for 12 hours

        $cookieParams = session_get_cookie_params();
        session_set_cookie_params($cookieParams['lifetime'], $cookieParams['path'], $cookieParams['domain'], self::SESSION_HTTPS, self::SESSION_HTTP_ONLY);

        @session_start();
    }

    public static function isSignedIn() {
        return isset($_SESSION) && isset($_SESSION['user']);
    }

    public static function getUser() {
        if (isset($_SESSION) && isset($_SESSION['user'])) {
            return unserialize($_SESSION['user']);
        }
        else {
            return NULL;
        }
    }

    public static function getUserID() {
        $userObject = self::getUser();
        if (empty($userObject)) {
            return 0;
        }
        else {
            return intval($userObject->getID());
        }
    }

    public static function getUserRealName() {
        $userObject = self::getUser();
        if (empty($userObject)) {
            return '';
        }
        else {
            return $userObject->getRealName();
        }
    }

    public static function isUserDeveloper() {
        $userObject = self::getUser();
        if (empty($userObject)) {
            return true;
        }
        else {
            return $userObject->isDeveloper();
        }
    }

    public static function signIn($user) {
        if ($user instanceof User) {
            session_regenerate_id(true);
            self::updateUserInfo($user);
        }
        else {
            throw new Exception('User must be an instance of class User');
        }
    }

    public static function updateUserInfo($userObject) {
        if (!empty($userObject)) {
            $_SESSION['user'] = serialize($userObject);
        }
        else {
            $_SESSION['user'] = NULL;
        }
    }

    public static function signOut() {
        session_regenerate_id(true); // prevent session fixation attacks

        $cookieParams = session_get_cookie_params();
        setcookie(session_name(), '', time()-86400, $cookieParams['path'], $cookieParams['domain'], self::SESSION_HTTPS, self::SESSION_HTTP_ONLY); // delete session cookie

        $_SESSION = array(); // unset session array
        session_destroy(); // delete session data
    }

    public static function saveCachedEdits($repositoryID, $languageID, $editsArray) {
        $_SESSION['edits'][$repositoryID][$languageID] = $editsArray;
    }

    public static function getCachedEdit($repositoryID, $languageID, $phraseID, $phraseSubKey, $defaultValue = '') {
        if (isset($_SESSION['edits'][$repositoryID][$languageID][$phraseID][$phraseSubKey])) {
            return trim($_SESSION['edits'][$repositoryID][$languageID][$phraseID][$phraseSubKey]);
        }
        else {
            return $defaultValue;
        }
    }

    public static function restoreCachedEdits($databaseResults) {
        $restoredEditsArray = array();
        foreach ($databaseResults as $databaseResult) {
            $restoredEditsArray[$databaseResult['repositoryID']][$databaseResult['languageID']][Helper::encodeID($databaseResult['referencedPhraseID'])][$databaseResult['phraseSubKey']] = $databaseResult['suggestedValue'];
        }
        $_SESSION['edits'] = $restoredEditsArray;
    }

}

?>