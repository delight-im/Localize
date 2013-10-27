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
        self::$db->exec($sql_string);
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
            return Database::selectFirst("SELECT phraseKey, payload FROM phrases WHERE id = ".intval($id)." AND repositoryID = ".intval($repositoryID));
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

    public static function addPhrases($repositoryID, $languageID, $phraseObjects, $doOverwrite) {
        $values = "";
        $counter = 0;
        foreach ($phraseObjects as $phraseObject) {
            if ($phraseObject instanceof Phrase) {
                if ($counter > 0) {
                    $values .= ",";
                }
                $values .= "(".intval($repositoryID).", ".intval($languageID).", ".self::escape($phraseObject->getPhraseKey()).", 1, ".self::escape($phraseObject->getPayload()).")";
                $counter++;
            }
            else {
                throw new Exception('Phrase objects must be instances of class Phrase');
            }
        }
        if (!empty($values)) {
            if ($doOverwrite) {
                self::insert("INSERT INTO phrases (repositoryID, languageID, phraseKey, enabled, payload) VALUES ".$values." ON DUPLICATE KEY UPDATE payload = VALUES(payload)");
            }
            else {
                self::insert("INSERT IGNORE INTO phrases (repositoryID, languageID, phraseKey, enabled, payload) VALUES ".$values);
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

    public static function setLastLogin($userID, $loginTime) {
        self::update("UPDATE users SET last_login = ".intval($loginTime)." WHERE id = ".intval($userID));
    }

    public static function getPendingEdit($repositoryID, $languageID) {
        return self::select("SELECT a.id, a.phraseSubKey, a.userID, a.suggestedValue, a.submit_time, b.username, b.real_name, c.phraseKey, c.payload FROM edits AS a JOIN users AS b ON a.userID = b.id JOIN phrases AS c ON a.referencedPhraseID = c.id WHERE a.repositoryID = ".intval($repositoryID)." AND a.languageID = ".intval($languageID)." ORDER BY a.submit_time ASC LIMIT 0, 1");
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

}