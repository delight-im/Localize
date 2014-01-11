<?php

require_once(__DIR__.'/../config.php');

class Authentication {

    const SESSION_HTTPS = CONFIG_SESSION_HTTPS; // bool from config.php in root directory
    const SESSION_HTTP_ONLY = true;
    const ALLOW_SIGN_UP_DEVELOPERS = CONFIG_ALLOW_SIGN_UP_DEVELOPERS; // bool from config.php in root directory

    public static function init() {
        ini_set('session.use_only_cookies', 1); // use cookies only (no session IDs that are sent via GET)
        ini_set('session.cookie_lifetime', 0); // total session lifetime is not limited (until the browser is closed)
		// make sure to set session.gc_maxlifetime = 86400 in php.ini as well
        ini_set('session.gc_maxlifetime', 86400); // session may be regarded as garbage and thus time out if user is not active for 24 hours

        $cookieParams = session_get_cookie_params();
        session_set_cookie_params($cookieParams['lifetime'], $cookieParams['path'], $cookieParams['domain'], self::SESSION_HTTPS, self::SESSION_HTTP_ONLY);

        @session_start();
    }

    public static function isAllowSignUpDevelopers() {
        return self::ALLOW_SIGN_UP_DEVELOPERS;
    }

    public static function isSignedIn() {
        return isset($_SESSION) && isset($_SESSION['user']);
    }

    /**
     * Gets the user object for the currently authenticated user
     *
     * @return User the user object
     */
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

    public static function getUserName() {
        $userObject = self::getUser();
        if (empty($userObject)) {
            return '';
        }
        else {
            return $userObject->getUsername();
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

    public static function getUserCountry() {
        $userObject = self::getUser();
        if (empty($userObject)) {
            return '';
        }
        else {
            return $userObject->getCountry();
        }
    }

    public static function getUserTimezone() {
        $userObject = self::getUser();
        if (empty($userObject)) {
            return '';
        }
        else {
            return $userObject->getTimezone();
        }
    }

    public static function getUserEmail() {
        $userObject = self::getUser();
        if (empty($userObject)) {
            return '';
        }
        else {
            return $userObject->getEmail();
        }
    }

    public static function getUserEmail_lastVerificationAttempt() {
        $userObject = self::getUser();
        if (empty($userObject)) {
            return time();
        }
        else {
            return $userObject->getEmail_lastVerificationAttempt();
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
            $restoredEditsArray[$databaseResult['repositoryID']][$databaseResult['languageID']][URL::encodeID($databaseResult['referencedPhraseID'])][$databaseResult['phraseSubKey']] = $databaseResult['suggestedValue'];
        }
        $_SESSION['edits'] = $restoredEditsArray;
    }

    public static function saveCachedRepository($id, $name) {
        $_SESSION['recentlyVisited'][$id] = $name;
    }

    public static function getCachedRepositories() {
        if (isset($_SESSION['recentlyVisited'])) {
            asort($_SESSION['recentlyVisited']);
            return $_SESSION['recentlyVisited'];
        }
        else {
            return array();
        }
    }

    public static function getCachedLanguageProgress($repositoryID) {
        if (isset($_SESSION['cachedLanguageProgress'][$repositoryID])) {
            return $_SESSION['cachedLanguageProgress'][$repositoryID];
        }
        else {
            return array();
        }
    }

    public static function setCachedLanguageProgress($repositoryID, $data) {
        $_SESSION['cachedLanguageProgress'][$repositoryID] = $data;
    }

    public static function mayChangeEmailAgain($email_lastVerificationAttempt) {
        return (time()-$email_lastVerificationAttempt) > 86400;
    }

    public static function mayVerifyEmailAgain($email_lastVerificationAttempt) {
        return $email_lastVerificationAttempt > 0 && (time()-$email_lastVerificationAttempt) > 21600;
    }

    public static function mayReceiveNotificationsAgain($lastNotification) {
        return (time()-$lastNotification) > 86400;
    }

    public static function createVerificationToken($email) {
        return sha1(mt_rand(1, 1000000000).' '.$email.' '.mt_rand(1, 1000000000));
    }

}

?>