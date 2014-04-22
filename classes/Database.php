<?php

require_once(__DIR__.'/../config.php');

class Database {

    const DB_CONNECT_STRING = CONFIG_DB_CONNECT_STRING; // string from config.php in root directory
    const DB_USERNAME = CONFIG_DB_USERNAME; // string from config.php in root directory
    const DB_PASSWORD = CONFIG_DB_PASSWORD; // string from config.php in root directory
    const TABLE_REPOSITORIES_SEQUENCE = CONFIG_DB_REPOSITORIES_SEQUENCE; // string from config.php in root directory

    /**
     * PDO database object that is used internally to communicate with the DB
     *
     * @var PDO
     */
    protected static $db;

    public static function init() {
        try {
            self::$db = new PDO(self::DB_CONNECT_STRING, self::DB_USERNAME, self::DB_PASSWORD);
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch (Exception $e) {
            throw new Exception('Could not connect to database');
        }
    }

    public static function escape($text) {
        return self::$db->quote($text);
    }

    public static function select($sql_string) {
        $result = self::$db->query($sql_string);
        return $result->fetchAll();
    }

    public static function selectFirst($sql_string) {
        $result = self::$db->query($sql_string);
        return $result->fetch();
    }

    public static function selectCount($sql_string) {
        $result = self::selectFirst($sql_string);
        return $result[0];
    }

    public static function insert($sql_string) {
        return self::$db->exec($sql_string);
    }

    public static function update($sql_string) {
        self::$db->exec($sql_string);
    }

    public static function delete($sql_string) {
        self::$db->exec($sql_string);
    }

    public static function getLastInsertID($sequenceName) {
        return self::$db->lastInsertId($sequenceName);
    }

    public static function getRepositoryData($id) {
        if ($id > 0) {
            return Database::selectFirst("SELECT name, visibility, defaultLanguage FROM repositories WHERE id = ".intval($id));
        }
        else {
            return NULL;
        }
    }

    public static function getLanguageData($id) {
        if ($id > 0) {
            return new Language_Android($id);
        }
        else {
            return NULL;
        }
    }

    public static function getPhraseData($repositoryID, $id) {
        if ($id > 0) {
            return Database::selectFirst("SELECT phraseKey, payload, groupID FROM phrases WHERE id = ".intval($id)." AND repositoryID = ".intval($repositoryID));
        }
        else {
            return NULL;
        }
    }

    public static function getRepositoryRole($userID, $repositoryID) {
        $role = Database::selectFirst("SELECT role FROM roles WHERE userID = ".intval($userID)." AND repositoryID = ".intval($repositoryID));
        if (empty($role)) {
            return Repository::ROLE_NONE;
        }
        else {
            return $role['role'];
        }
    }

    public static function addPhrase($repositoryID, $languageID, $phraseKey, $payload) {
        self::insert("INSERT INTO phrases (repositoryID, languageID, phraseKey, enabled, payload) VALUES (".intval($repositoryID).", ".intval($languageID).", ".self::escape($phraseKey).", 1, ".self::escape($payload).")");
    }

    /**
     * Add the given list of phrase objects to the database
     *
     * @param int $repositoryID the repository to associate the phrases with
     * @param int $languageID the language to add the phrases to
     * @param Phrase[] $phraseObjects the list of phrase objects to add
     * @param int $groupID the group ID to associate the phrases with (or Phrase::GROUP_NONE)
     * @param bool $doOverwrite whether to overwrite existing phrases or not (ignore them)
     * @throws Exception if the phrases could not be added to the database
     */
    public static function addPhrases($repositoryID, $languageID, $phraseObjects, $groupID, $doOverwrite) {
        $values = "";
        $counter = 0;
        foreach ($phraseObjects as $phraseObject) {
            if ($phraseObject instanceof Phrase) {
                if ($counter > 0) {
                    $values .= ",";
                }
                $values .= "(".intval($repositoryID).", ".intval($languageID).", ".self::escape($phraseObject->getPhraseKey()).", ".intval($groupID).", 1, ".self::escape($phraseObject->getPayload()).")";
                $counter++;
            }
            else {
                throw new Exception('Phrase objects must be instances of class Phrase');
            }
        }
        if (!empty($values)) {
            if ($doOverwrite) {
                self::insert("INSERT INTO phrases (repositoryID, languageID, phraseKey, groupID, enabled, payload) VALUES ".$values." ON DUPLICATE KEY UPDATE groupID = VALUES(groupID), payload = VALUES(payload)");
            }
            else {
                self::insert("INSERT IGNORE INTO phrases (repositoryID, languageID, phraseKey, groupID, enabled, payload) VALUES ".$values);
            }
        }
    }

    public static function submitEdits($repositoryID, $languageID, $userID, $editObjects) {
        $values = "";
        $counter = 0;
        foreach ($editObjects as $editObject) {
            if ($editObject instanceof Edit) {
                if ($counter > 0) {
                    $values .= ",";
                }
                $values .= "(".intval($repositoryID).", ".intval($languageID).", ".intval($editObject->getReferencedPhraseID()).", ".self::escape($editObject->getPhraseSubKey()).", ".intval($userID).", ".self::escape($editObject->getSuggestedValue()).", ".time().")";
                $counter++;
            }
            else {
                throw new Exception('Edit objects must be instances of class Edit');
            }
        }
        if (!empty($values)) {
            self::insert("INSERT INTO edits (repositoryID, languageID, referencedPhraseID, phraseSubKey, userID, suggestedValue, submit_time) VALUES ".$values." ON DUPLICATE KEY UPDATE suggestedValue = VALUES(suggestedValue)");
        }
    }

    public static function getNativeLanguages($userID) {
        $out = array();
        $entries = self::select("SELECT languageID FROM native_languages WHERE userID = ".intval($userID));
        foreach ($entries as $entry) {
            $out[] = $entry['languageID'];
        }
        return $out;
    }

    public static function updateSettings($userID, $realName, $nativeLanguages, $country, $timezone) {
        self::update("UPDATE users SET real_name = ".self::escape($realName).", localeCountry = ".self::escape($country).", localeTimezone = ".self::escape($timezone)." WHERE id = ".intval($userID));
        self::delete("DELETE FROM native_languages WHERE userID = ".intval($userID));
        if (is_array($nativeLanguages) && count($nativeLanguages) > 0) {
            $values = "";
            $counter = 0;
            foreach ($nativeLanguages as $nativeLanguage) {
                if ($counter > 0) {
                    $values .= ",";
                }
                $values .= "(".intval($userID).", ".intval($nativeLanguage).")";
                $counter++;
            }
            self::insert("INSERT INTO native_languages (userID, languageID) VALUES ".$values);
        }
    }

    public static function updateEmail($userID, $email) {
        $data = self::selectFirst("SELECT email, email_lastVerificationAttempt FROM users WHERE id = ".intval($userID));
        if (isset($data['email_lastVerificationAttempt']) && isset($data['email'])) {
            if (Authentication::mayChangeEmailAgain($data['email_lastVerificationAttempt'])) {
                if ($email != $data['email']) {
                    $verificationStatus = $email == '' ? 0 : time();
                    self::update("UPDATE users SET email = ".self::escape($email).", email_lastVerificationAttempt = ".$verificationStatus." WHERE id = ".intval($userID));
                    return true;
                }
            }
        }
        return false;
    }

    public static function updateEmailVerificationAttempt($userID) {
        $data = self::selectFirst("SELECT email, email_lastVerificationAttempt FROM users WHERE id = ".intval($userID));
        if (isset($data['email_lastVerificationAttempt']) && isset($data['email'])) {
            if (Authentication::mayVerifyEmailAgain($data['email_lastVerificationAttempt'])) {
                if ($data['email'] != '') {
                    self::update("UPDATE users SET email_lastVerificationAttempt = ".time()." WHERE id = ".intval($userID));
                    return true;
                }
            }
        }
        return false;
    }

    public static function setLastLogin($userID, $loginTime) {
        self::update("UPDATE users SET last_login = ".intval($loginTime)." WHERE id = ".intval($userID));
    }

    public static function getPendingEdit($repositoryID, $languageID, $editID = 0) {
        return self::select("SELECT a.id, a.phraseSubKey, a.userID, a.suggestedValue, a.submit_time, b.username, b.real_name, c.phraseKey, c.payload FROM edits AS a JOIN users AS b ON a.userID = b.id JOIN phrases AS c ON a.referencedPhraseID = c.id WHERE a.repositoryID = ".intval($repositoryID)." AND a.languageID = ".intval($languageID).($editID > 0 ? " AND a.id = ".intval($editID) : "")." ORDER BY a.submit_time ASC LIMIT 0, 1");
    }

    public static function getPendingEditsByRepositoryLanguageAndUser($repositoryID, $languageID, $contributorID) {
        return self::select("SELECT a.id, a.phraseSubKey, a.userID, a.suggestedValue, a.submit_time, b.username, b.real_name, c.phraseKey, c.payload FROM edits AS a JOIN users AS b ON a.userID = b.id JOIN phrases AS c ON a.referencedPhraseID = c.id WHERE a.repositoryID = ".intval($repositoryID)." AND a.languageID = ".intval($languageID)." AND a.userID = ".intval($contributorID));
    }

    public static function getPendingEditsByRepository($repositoryID) {
        return self::select("SELECT a.languageID, COUNT(*) FROM edits AS a JOIN users AS b ON a.userID = b.id JOIN phrases AS c ON a.referencedPhraseID = c.id WHERE a.repositoryID = ".intval($repositoryID)." GROUP BY a.languageID");
    }

    public static function getPendingEditsByRepositoryCount($repositoryID) {
        return self::selectCount("SELECT COUNT(*) FROM edits AS a JOIN users AS b ON a.userID = b.id JOIN phrases AS c ON a.referencedPhraseID = c.id WHERE a.repositoryID = ".intval($repositoryID));
    }

    public static function getPendingEditsByRepositoryAndLanguageCount($repositoryID, $languageID) {
        return self::selectCount("SELECT COUNT(*) FROM edits AS a JOIN users AS b ON a.userID = b.id JOIN phrases AS c ON a.referencedPhraseID = c.id WHERE a.repositoryID = ".intval($repositoryID)." AND a.languageID = ".intval($languageID));
    }

    public static function getPendingEditsByUser($userID) {
        return self::select("SELECT repositoryID, languageID, referencedPhraseID, phraseSubKey, suggestedValue FROM edits WHERE userID = ".intval($userID));
    }

    public static function getPhrase($repositoryID, $languageID, $phraseKey) {
        return self::selectFirst("SELECT payload FROM phrases WHERE repositoryID = ".intval($repositoryID)." AND languageID = ".intval($languageID)." AND phraseKey = ".self::escape($phraseKey));
    }

    public static function updatePhrase($repositoryID, $languageID, $phraseKey, $payload) {
        self::insert("INSERT INTO phrases (repositoryID, languageID, phraseKey, payload) VALUES (".intval($repositoryID).", ".intval($languageID).", ".self::escape($phraseKey).", ".self::escape($payload).") ON DUPLICATE KEY UPDATE payload = ".self::escape($payload));
    }

    public static function updateContributor($repositoryID, $contributorID) {
        self::insert("INSERT INTO contributions (userID, repositoryID) VALUES (".intval($contributorID).", ".intval($repositoryID).") ON DUPLICATE KEY UPDATE editCount = editCount+1");
    }

    public static function postponeEdit($editID) {
        self::update("UPDATE edits SET submit_time = ".time()." WHERE id = ".intval($editID));
    }

    public static function deleteEdit($editID) {
        self::delete("DELETE FROM edits WHERE id = ".intval($editID));
    }

    public static function approveEditsByContributor($repositoryID, $languageID, $contributorID) {
		$edits = self::getPendingEditsByRepositoryLanguageAndUser($repositoryID, $languageID, $contributorID);
		foreach ($edits as $edit) {
			$previousPhraseData = Database::getPhrase($repositoryID, $languageID, $edit['phraseKey']);
			if (empty($previousPhraseData)) {
				$phraseObject = Phrase::create(0, $edit['phraseKey'], $edit['payload'], 0, true);
			}
			else {
				$phraseObject = Phrase::create(0, $edit['phraseKey'], $previousPhraseData['payload'], 0);
			}
			$phraseObject->setPhraseValue($edit['phraseSubKey'], $edit['suggestedValue']);

			self::updatePhrase($repositoryID, $languageID, $edit['phraseKey'], $phraseObject->getPayload());
			self::updateContributor($repositoryID, $edit['userID']);
			self::deleteEdit($edit['id']);
		}
		Authentication::setCachedLanguageProgress($repositoryID, NULL); // unset cached version of this repository's progress
    }

    public static function deleteEditsByContributor($contributorID) {
        self::delete("DELETE FROM edits WHERE userID = ".intval($contributorID));
    }

    public static function getRepositoriesByContribution($userID) {
        return self::select("SELECT a.repositoryID, b.name FROM contributions AS a JOIN repositories AS b ON a.repositoryID = b.id WHERE a.userID = ".intval($userID));
    }

    public static function requestInvitation($repositoryID, $userID) {
        self::insert("INSERT INTO invitations (repositoryID, userID, request_time) VALUES (".intval($repositoryID).", ".intval($userID).", ".time().")");
    }

    public static function getInvitationsByUser($userID, $count = 10) {
        return self::select("SELECT a.repositoryID, a.request_time, a.accepted, b.name FROM invitations AS a JOIN repositories AS b ON a.repositoryID = b.id WHERE a.userID = ".intval($userID)." ORDER BY a.request_time DESC LIMIT 0, ".intval($count));
    }

    public static function getInvitationByRepository($repositoryID) {
        return self::select("SELECT a.userID, a.request_time, b.username, b.real_name, b.localeCountry, b.join_date, b.last_login FROM invitations AS a JOIN users AS b ON a.userID = b.id WHERE a.repositoryID = ".intval($repositoryID)." AND a.accepted = 0 LIMIT 0, 1");
    }

    public static function getInvitationsByRepositoryCount($repositoryID) {
        return self::selectCount("SELECT COUNT(*) FROM invitations AS a JOIN users AS b ON a.userID = b.id WHERE a.repositoryID = ".intval($repositoryID)." AND a.accepted = 0");
    }

    /**
     * Either accepts or declines an invitation request by another user
     *
     * @param int $repositoryID the ID of the repository to which the user wants to be invited
     * @param int $userID the ID of the user who wants to be invited
     * @param boolean $accept whether to accept this request or decline it
     * @param int $assignedRole one of the constants representing the desired role of the user
     * @throws Exception if the user has already been invited before and thus does already have a role
     */
    public static function reviewInvitation($repositoryID, $userID, $accept, $assignedRole) {
        if ($accept) {
            self::insert("INSERT INTO roles (userID, repositoryID, role) VALUES (".intval($userID).", ".intval($repositoryID).", ".intval($assignedRole).")");
        }
        self::update("UPDATE invitations SET accepted = ".($accept ? 1 : -1)." WHERE repositoryID = ".intval($repositoryID)." AND userID = ".intval($userID));
    }

    public static function phraseUntranslate($repositoryID, $phraseKey, $defaultLanguageID) {
        self::delete("DELETE FROM phrases WHERE repositoryID = ".intval($repositoryID)." AND languageID != ".intval($defaultLanguageID)." AND phraseKey = ".self::escape($phraseKey));
    }

    public static function phraseDelete($repositoryID, $phraseKey) {
        self::delete("DELETE FROM phrases WHERE repositoryID = ".intval($repositoryID)." AND phraseKey = ".self::escape($phraseKey));
    }

    public static function getPhraseCountInGroup($repositoryID, $groupID, $defaultLanguageID) {
        return self::selectCount("SELECT COUNT(*) FROM phrases WHERE repositoryID = ".intval($repositoryID)." AND languageID = ".intval($defaultLanguageID)." AND groupID = ".intval($groupID));
    }

    public static function getPhraseGroups($repositoryID, $defaultLanguageID, $needCount = true) {
        if ($needCount) {
            return self::select("SELECT id, name, (SELECT COUNT(*) FROM phrases WHERE repositoryID = ".intval($repositoryID)." AND languageID = ".intval($defaultLanguageID)." AND groupID = groups.id) AS phraseCount FROM groups WHERE repositoryID = ".intval($repositoryID));
        }
        else {
            return self::select("SELECT id, name, 0 AS phraseCount FROM groups WHERE repositoryID = ".intval($repositoryID));
        }
    }

    public static function addGroup($repositoryID, $groupName) {
        return self::insert("INSERT INTO groups (repositoryID, name) VALUES (".intval($repositoryID).", ".self::escape($groupName).")");
    }

    public static function deleteGroup($repositoryID, $groupID) {
        self::update("UPDATE phrases SET groupID = 0 WHERE groupID = ".intval($groupID)." AND repositoryID = ".intval($repositoryID));
        self::delete("DELETE FROM groups WHERE id = ".intval($groupID)." AND repositoryID = ".intval($repositoryID));
    }

    public static function setPhraseGroup($repositoryID, $phraseKey, $groupID) {
        self::update("UPDATE phrases SET groupID = ".intval($groupID)." WHERE repositoryID = ".intval($repositoryID)." AND phraseKey = ".self::escape($phraseKey));
    }

    public static function saveVerificationToken($userID, $verificationToken, $validUntil) {
        self::insert("INSERT INTO verificationTokens (userID, token, validUntil) VALUES (".intval($userID).", ".self::escape($verificationToken).", ".intval($validUntil).") ON DUPLICATE KEY UPDATE userID = VALUES(userID), validUntil = VALUES(validUntil)");
    }

    public static function getVerificationUser($verificationToken) {
        return self::selectFirst("SELECT userID FROM verificationTokens WHERE token = ".self::escape($verificationToken)." AND validUntil > ".time());
    }

    public static function verifyUserEmail($userID) {
        self::delete("DELETE FROM verificationTokens WHERE userID = ".intval($userID));
        self::update("UPDATE users SET email_lastVerificationAttempt = 0 WHERE id = ".intval($userID));
    }

    public static function getWatchedEvents($repositoryID, $userID) {
        $events = self::select("SELECT eventID, lastNotification FROM watchers WHERE repositoryID = ".intval($repositoryID)." AND userID = ".intval($userID));
        $watchedEvents = array();
        foreach ($events as $event) {
            $watchedEvents[$event['eventID']] = $event['lastNotification'];
        }
        return $watchedEvents;
    }

    public static function setWatchedEvents($repositoryID, $eventID, $userID, $watchStatus) {
        if ($watchStatus == 1) {
            self::insert("INSERT IGNORE INTO watchers (repositoryID, eventID, userID) VALUES (".intval($repositoryID).", ".intval($eventID).", ".intval($userID).")");
        }
        else {
            self::delete("DELETE FROM watchers WHERE repositoryID = ".intval($repositoryID)." AND eventID = ".intval($eventID)." AND userID = ".intval($userID));
        }
    }

    public static function getWatchers($repositoryID, $eventID) {
        return self::select("SELECT a.userID, a.lastNotification, b.email, b.email_lastVerificationAttempt FROM watchers AS a JOIN users AS b ON a.userID = b.id WHERE a.repositoryID = ".intval($repositoryID)." AND a.eventID = ".intval($eventID));
    }

    public static function setWatchersLastNotification($userIDs, $timestamp) {
        if (count($userIDs) > 0) {
            $userIDList = self::escape(implode(',', $userIDs));
            self::update("UPDATE watchers SET lastNotification = ".intval($timestamp)." WHERE userID IN (".$userIDList.")");
        }
    }

    /**
     * Starts a new translation session
     *
     * @param int $repositoryID the repository ID to start the session for
     * @param int $languageID the language ID to start the session for
     * @param int $userID the user ID to start the session for
     * @param string $secret the secret that will later be used to verify integrity
     * @param int $timeStart the time when the session has been started
     */
    public static function initTranslationSession($repositoryID, $languageID, $userID, $secret, $timeStart) {
        self::insert("INSERT INTO translationSessions (repositoryID, languageID, userID, secret, timeStart) VALUES (".intval($repositoryID).", ".intval($languageID).", ".intval($userID).", ".self::escape($secret).", ".intval($timeStart).")");
    }

    /**
     * Attempts to finish and close a translation session that has been started before
     *
     * @param int $repositoryID the repository ID to finish the session for
     * @param int $languageID the language ID to finish the session for
     * @param int $userID the user ID to finish the session for
     * @param string $secret the secret that is used to verify integrity
     * @param int $timeEnd the time when the session has been closed
     * @return boolean whether the translation session could be found and finished or not
     */
    public static function finishTranslationSession($repositoryID, $languageID, $userID, $secret, $timeEnd) {
        $translationSession = self::selectFirst("SELECT id, timeStart FROM translationSessions WHERE repositoryID = ".intval($repositoryID)." AND languageID = ".intval($languageID)." AND userID = ".intval($userID)." AND secret = ".self::escape($secret));
        if (!empty($translationSession)) {
            if (isset($translationSession['id']) && isset($translationSession['timeStart'])) {
                if (($timeEnd - $translationSession['timeStart']) < Authentication::TRANSLATION_SESSION_MAX_DURATION) {
                    self::update("UPDATE translationSessions SET timeEnd = ".intval($timeEnd)." WHERE id = ".intval($translationSession['id']));
                    return true;
                }
            }
        }
        return false;
    }

    public static function getDiscussionEntries($editID) {
        return self::select("SELECT a.id, a.userID, a.timeSent, a.content, b.username, b.real_name FROM discussions AS a JOIN users AS b ON a.userID = b.id WHERE a.editID = ".intval($editID));
    }

    public static function saveDiscussionEntry($editID, $userID, $timeSent, $content) {
        self::insert("INSERT INTO discussions (editID, userID, timeSent, content) VALUES (".intval($editID).", ".intval($userID).", ".intval($timeSent).", ".self::escape($content).")");
    }

    /**
     * Logs that the given IP address has performed the declared action so that one can check later if an action must be throttled or not for this IP address
     *
     * @param string $ipAddress the IP address that has performed the action
     * @param string $actionType the action that has been performed (one of <sign_up>,<sign_in> and <create_project>)
     * @throws Exception if the given action type does not exist
     */
    public static function Throttling_increaseCounter($ipAddress, $actionType) {
        self::insert("INSERT INTO throttling (ip_address, action_type, time_performed) VALUES (".self::escape($ipAddress).", ".self::escape($actionType).", ".time().")");
    }

    /**
     * Checks whether the given IP address is allowed to perform the declared action or if this request has to be throttled
     *
     * @param string $ipAddress the IP address that wants to perform the action
     * @param string $actionType the action that is to be performed (one of <sign_up>,<sign_in> and <create_project>)
     * @return boolean whether the action is allowed (true) or not (false)
     * @throws Exception if the given action type does not exist
     */
    public static function Throttling_isAllowed($ipAddress, $actionType) {
        switch ($actionType) {
            case 'sign_up':
                // permit no more than 5 accounts to be created by one person per 72 hours
                $checkHours = 72;
                $actionCountMax = 5;
                break;
            case 'sign_in':
                // allow no more than 9 sign-in attempts by one person per 1 hour
                $checkHours = 1;
                $actionCountMax = 9;
                break;
            case 'create_project':
                // one person may not create more than 7 new repositories per 24 hours
                $checkHours = 24;
                $actionCountMax = 7;
                break;
            default:
                throw new Exception('Unknown action type: '.$actionType);
        }
        $timeout = time() - (3600 * 24 * $checkHours);

        $actionCount = self::selectCount("SELECT COUNT(*) FROM throttling WHERE ip_address = ".self::escape($ipAddress)." AND action_type = ".self::escape($actionType)." AND time_performed > ".intval($timeout));
        return $actionCount <= $actionCountMax;
    }

}