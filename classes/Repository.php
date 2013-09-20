<?php

class Repository {

    const VISIBILITY_PUBLIC = 1;
    const VISIBILITY_PROTECTED = 2;
    const VISIBILITY_PRIVATE = 3;
    const ROLE_NONE = 0;
    const ROLE_ADMINISTRATOR = 1;
    const ROLE_DEVELOPER = 2;
    const ROLE_MODERATOR = 3;

    protected $id;
    protected $name;
    protected $visibility;
    protected $defaultLanguage;
    protected $languages;

    public function __construct($id, $name, $visibility, $defaultLanguage) {
        $this->id = $id;
        $this->name = $name;
        $this->visibility = $visibility;
        $this->defaultLanguage = $defaultLanguage;
        $this->languages = array();
        $languages = Language::getList();
        foreach ($languages as $language) {
            $this->languages[$language] = new Language_Android($language);
        }
    }

    /**
     * @return int ID of the project
     */
    public function getID() {
        return $this->id;
    }

    /**
     * @return string name of the project
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return int visibility ID of the project
     */
    public function getVisibility() {
        return $this->visibility;
    }

    public function getVisibilityTitle() {
        return self::getRepositoryVisibilityTitle($this->visibility);
    }

    public static function getRepositoryVisibilityTitle($visibilityID) {
        switch ($visibilityID) {
            case self::VISIBILITY_PUBLIC:
                return 'public';
            case self::VISIBILITY_PROTECTED:
                return 'protected';
            case self::VISIBILITY_PRIVATE:
                return 'private';
            default:
                throw new Exception('Unknown visibility ID: '.$visibilityID);
        }
    }

    public function getVisibilityDescription() {
        return self::getRepositoryVisibilityDescription($this->visibility);
    }

    public static function getRepositoryVisibilityDescription($visibilityID) {
        switch ($visibilityID) {
            case self::VISIBILITY_PUBLIC:
                return 'Visible to everybody';
            case self::VISIBILITY_PROTECTED:
                return 'Visible to signed-in users only';
            case self::VISIBILITY_PRIVATE:
                return 'Visible to invited users only';
            default:
                throw new Exception('Unknown visibility ID: '.$visibilityID);
        }
    }

    public function getVisibilityTag() {
        return self::getRepositoryVisibilityTag($this->visibility);
    }

    public static function getRepositoryVisibilityTag($visibilityID) {
        return '<abbr title="'.self::getRepositoryVisibilityDescription($visibilityID).'">'.self::getRepositoryVisibilityTitle($visibilityID).'</abbr>';
    }

    public function getDefaultLanguage() {
        return $this->defaultLanguage;
    }

    public function getPermissions($userID, $role) {
        return new RepositoryPermissions($userID, $this->visibility, $role);
    }

    public function addPhrase($language, $id, $phraseKey, $payload, $enabled = true, $createKeysOnly = false) {
        $this->languages[$language]->addPhrase(Phrase::create($id, $phraseKey, $payload, $enabled, $createKeysOnly));
    }

    public function removePhrase($language, $phraseKey) {
        $this->languages[$language]->removePhrase($phraseKey);
    }

    public function loadLanguages($isExport = false) {
        $phrases = Database::select("SELECT id, languageID, phraseKey, enabled, payload FROM phrases WHERE repositoryID = ".intval($this->id));
        if (!empty($phrases)) {
            foreach ($phrases as $phrase) {
                $this->addPhrase($phrase['languageID'], $phrase['id'], $phrase['phraseKey'], $phrase['payload'], $phrase['enabled']);
            }
        }
        $this->normalizeLanguages($isExport);
    }

    protected function normalizeLanguages($isExport) {
        $defLangObject = $this->languages[$this->defaultLanguage];
        $defLangPhrases = $defLangObject->getPhrases();
        foreach ($this->languages as $langID => $lang) {
            if ($lang != $this->defaultLanguage) {
                $currentPhrases = $lang->getPhrases();
                foreach ($currentPhrases as $currentPhrase) {
                    $originalPhrase = $defLangObject->getPhraseByKey($currentPhrase->getPhraseKey());
                    if (!isset($originalPhrase)) {
                        $this->removePhrase($langID, $currentPhrase->getPhraseKey());
                    }
                }
                foreach ($defLangPhrases as $defLangPhrase) {
                    $currentPhrase = $lang->getPhraseByKey($defLangPhrase->getPhraseKey());
                    if (!isset($currentPhrase)) {
                        $this->addPhrase($langID, $defLangPhrase->getID(), $defLangPhrase->getPhraseKey(), $defLangPhrase->getPayload(), $defLangPhrase->isEnabledForTranslation(), !$isExport);
                    }
                }
            }
        }
    }

    public function getLanguage($languageID) {
        if (isset($this->languages[$languageID])) {
            return $this->languages[$languageID];
        }
        else {
            throw new Exception('Unknown language ID '.$languageID);
        }
    }

    public static function hasUserPermissions($userID, $repositoryID, $repositoryData, $minimumRole) {
        $repository = new Repository($repositoryID, $repositoryData['name'], $repositoryData['visibility'], $repositoryData['defaultLanguage']);
        $role = Database::getRepositoryRole(Authentication::getUserID(), $repositoryID);
        $permissions = $repository->getPermissions($userID, $role);
        if (!$permissions->isLoginMissing() && !$permissions->isInvitationMissing()) {
            if ($role == Repository::ROLE_ADMINISTRATOR || $role == Repository::ROLE_DEVELOPER) {
                return true;
            }
        }
        return false;
    }

    public static function isRoleAllowedToMovePhrases($role) {
        if ($role == Repository::ROLE_ADMINISTRATOR || $role == Repository::ROLE_DEVELOPER) {
            return true;
        }
        else {
            return false;
        }
    }

}

class RepositoryPermissions {

    private $userID;
    private $repositoryVisibility;
    private $role;

    public function __construct($userID, $repositoryVisibility, $role) {
        $this->userID = $userID;
        $this->repositoryVisibility = $repositoryVisibility;
        $this->role = $role;
    }

    public function isLoginMissing() {
        if ($this->repositoryVisibility == Repository::VISIBILITY_PUBLIC) {
            return false;
        }
        elseif ($this->repositoryVisibility == Repository::VISIBILITY_PROTECTED || $this->repositoryVisibility == Repository::VISIBILITY_PRIVATE) {
            if ($this->userID > 0) {
                return false;
            }
            else {
                return true;
            }
        }
        else {
            throw new Exception('Unknown visibility ID '.$this->repositoryVisibility);
        }
    }

    public function isInvitationMissing() {
        if ($this->repositoryVisibility == Repository::VISIBILITY_PUBLIC) {
            return false;
        }
        elseif ($this->repositoryVisibility == Repository::VISIBILITY_PROTECTED) {
            return false;
        }
        elseif ($this->repositoryVisibility == Repository::VISIBILITY_PRIVATE) {
            if ($this->role == Repository::ROLE_ADMINISTRATOR) {
                return false;
            }
            elseif ($this->role == Repository::ROLE_DEVELOPER) {
                return false;
            }
            elseif ($this->role == Repository::ROLE_MODERATOR) {
                return false;
            }
            elseif ($this->role == Repository::ROLE_NONE) {
                return true;
            }
            else {
                throw new Exception('Unknown role '.$this->role);
            }
        }
        else {
            throw new Exception('Unknown visibility ID '.$this->repositoryVisibility);
        }
    }

}