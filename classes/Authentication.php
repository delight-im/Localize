<?php

require_once(__DIR__.'/../config.php');

class Authentication {

    const SESSION_HTTPS = CONFIG_SESSION_HTTPS; // bool from config.php in root directory
    const SESSION_HTTP_ONLY = true;
    const ALLOW_SIGN_UP_DEVELOPERS = CONFIG_ALLOW_SIGN_UP_DEVELOPERS; // bool from config.php in root directory
    const SECRET_LENGTH_SHORT = 128; // 128 bit = 32 chars
    const SECRET_LENGTH_LONG = 256; // 256 bit = 64 chars
    const TRANSLATION_SESSION_MAX_DURATION = 604800; // 7 days

    public static function init() {
        ini_set('session.use_only_cookies', 1); // use cookies only (no session IDs that are sent via GET)
        ini_set('session.cookie_lifetime', 0); // total session lifetime is not limited (until the browser is closed)
		// make sure to set session.gc_maxlifetime = 86400 in php.ini as well
        ini_set('session.gc_maxlifetime', 86400); // session may be regarded as garbage and thus time out if user is not active for 24 hours

        $cookieParams = session_get_cookie_params();
        session_set_cookie_params($cookieParams['lifetime'], $cookieParams['path'], $cookieParams['domain'], self::SESSION_HTTPS, self::SESSION_HTTP_ONLY);

        @session_start();
    }

    private static function createCSRFToken($recreateIfExists = false) {
        if (!isset($_SESSION['CSRFToken']) || $recreateIfExists) {
            $_SESSION['CSRFToken'] = base64_encode(uniqid(rand(), true));
        }
    }

    public static function getCSRFToken() {
        self::createCSRFToken();
        return $_SESSION['CSRFToken'];
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

            // force new token for CSRF attack prevention
            self::createCSRFToken(true);
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

    /**
     * Creates a new verification token, saves it to the database and sends the verification mail
     *
     * @param string $email the email address to verify
     * @param User $userObject (optional) the existing session user object to modify
     */
    public static function askForEmailVerification($email, &$userObject = NULL) {
        $mailVerificationToken = self::createSecret(self::SECRET_LENGTH_SHORT);
        Database::saveVerificationToken(Authentication::getUserID(), $mailVerificationToken, time()+86400);

        // save the timestamp for this new verification attempt to the current session
        if (empty($userObject)) {
            $newUserObject = self::getUser();
            $newUserObject->setEmail_lastVerificationAttempt(time());
            self::updateUserInfo($newUserObject);
        }
        else {
            $userObject->setEmail_lastVerificationAttempt(time());
        }

        // send the email containing the verification link
        self::sendVerificationMail($email, $mailVerificationToken);
    }

    public static function sendVerificationMail($email, $mailVerificationToken) {
        $mailSubject = CONFIG_SITE_NAME.': Verify your email address';
        $mailDomain = parse_url(CONFIG_ROOT_URL, PHP_URL_HOST);

        $mail = new Email(CONFIG_SITE_EMAIL, CONFIG_SITE_NAME, $mailSubject);
        $mail->addRecipient($email);
        $mail->addLine('Hello '.self::getUserName().',');
        $mail->addLine('');
        $mail->addLine('Please open the following link in order to verify your email address on '.CONFIG_SITE_NAME.':');
        $mail->addLine(URL::toEmailVerification($mailVerificationToken));
        $mail->addLine('');
        $mail->addLine('If you did not sign up on '.CONFIG_SITE_NAME.' and enter your email address there, please just ignore this email and accept our excuses.');
        $mail->addLine('');
        $mail->addLine('Regards');
        $mail->addLine(CONFIG_SITE_NAME);
        $mail->addLine($mailDomain);
        $mail->send();
    }

    /**
     * Converts binary data to custom HEX string with improved readability
     *
     * @param string $raw binary data to encode in custom HEX
     * @return string custom HEX string
     */
    protected static function bin2hex_custom($raw) {
        $hex = bin2hex($raw);
        $hex = str_replace('0', 'w', $hex); // replace HEX-character 0 with better-readable w (which is not part of hex-alphabet)
        $hex = str_replace('1', 'p', $hex); // replace HEX-character 1 with better-readable p (which is not part of hex-alphabet)
        $hex = str_replace('f', 'y', $hex); // replace HEX-character f with better-readable y (which is not part of hex-alphabet)
        return strtolower($hex); // return lowercase for uniform case-insensitivity
    }

    /**
     * Creates a readable and secure secret of pre-defined length
     *
     * @param int $bitLength the bit length to use for this secret
     * @return string the secret string
     */
    public static function createSecret($bitLength = self::SECRET_LENGTH_LONG) {
        $raw = openssl_random_pseudo_bytes((int) ($bitLength / 8)); // create secure random bytes
        return self::bin2hex_custom($raw); // convert random data to custom HEX
    }

    /**
     * Check whether the given password (with verification) is allowed (must be in sync with JavaScript implementation)
     *
     * @param string $passwordOriginal the original password
     * @param string $passwordVerification the verification of the password
     * @return boolean whether the password is allowed or not
     */
    public static function isPasswordAllowed($passwordOriginal, $passwordVerification) {
        $containsLetter = '/[a-zA-Z]+/';
        $containsNumber = '/[0-9]+/';
        $originalIsValid = mb_strlen($passwordOriginal) >= 8 && preg_match($containsLetter, $passwordOriginal) && preg_match($containsNumber, $passwordOriginal);
        return $originalIsValid && $passwordOriginal == $passwordVerification;
    }

}

?>