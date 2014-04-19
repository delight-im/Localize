<?php

abstract class Phrase {

    const NEWLINE_REGEX = '/(\r\n|\r|\n)/';
    const GROUP_ALL = -1;
    const GROUP_NONE = 0;

    protected $id;
    protected $phraseKey;
    protected $groupID;
    protected $enabledForTranslation;
    protected $isEmpty;

    /**
     * Returns the output of this phrase for the specific platform and type of phrase in Android XML format
     *
     * @return string output of this phrase
     */
    abstract public function outputAndroidXML();

    /**
     * Returns the output of this phrase for the specific platform and type of phrase in Android XML format with escaped HTML
     *
     * @return string output of this phrase
     */
    abstract public function outputAndroidXMLEscapedHTML();

    /**
     * Returns the output of this phrase for the specific platform and type of phrase in JSON format
     *
     * @return string output of this phrase
     */
    abstract public function outputJSON();

    /**
     * Returns the output of this phrase for the specific platform and type of phrase in plaintext format
     *
     * @return string output of this phrase
     */
    abstract public function outputPlaintext();

    /**
     * Returns the the number of complete values and total values for this phrase
     *
     * @return array the first entry contains the number of complete values in this phrase and the second the total number of values
     */
    abstract public function getCompleteness();

    /**
     * Gets the phrase's payload in form of JSON data
     *
     * @return string payload as JSON data
     */
    abstract public function getPayload();

    /**
     * Sets the phrase's payload from the given JSON data
     *
     * @param string $json JSON data to get the payload from
     * @param bool $createKeysOnly whether the complete phrase should be created (true) or keys only (false)
     * @param bool $isUsingDefaultPhrase whether this is only using the default language's value and must thus be marked as empty
     */
    abstract public function setPayload($json, $createKeysOnly = false, $isUsingDefaultPhrase = false);

    /**
     * Sets this phrase's group ID to the given integer
     *
     * @param int $groupID
     */
    public function setGroupID($groupID) {
        $this->groupID = $groupID;
    }

    /**
     * @return int the phrase's group ID
     */
    public function getGroupID() {
        return $this->groupID;
    }

    public function __construct($id, $phraseKey, $enabledForTranslation = true) {
        $this->id = $id;
        $this->phraseKey = $phraseKey;
        $this->enabledForTranslation = $enabledForTranslation;
        $this->isEmpty = false;
    }

    public function isEmpty() {
        return $this->isEmpty;
    }

    /**
     * @return bool whether the phrase is enabled for translation or not
     */
    public function isEnabledForTranslation() {
        return $this->enabledForTranslation;
    }

    /**
     * @return int the phrase's ID
     */
    public function getID() {
        return $this->id;
    }

    /**
     * Returns this phrase's key that identifies it within a repository's language
     *
     * @return string the phrase's key
     */
    public function getPhraseKey() {
        return $this->phraseKey;
    }

    /**
     * Returns the list of values for this phrase
     *
     * @return array list of values
     */
    abstract public function getPhraseValues();

    /**
     * Set the value at the given sub-key for this phrase
     *
     * @param string $subKey sub-key
     * @param string $value the new value to set
     */
    abstract public function setPhraseValue($subKey, $value);

    /**
     * Creates a new phrase object using the subclass extracted from the JSON payload
     *
     * @param int $id
     * @param string $phraseKey
     * @param string $json
     * @param int $groupID
     * @param bool $enabled
     * @param bool $createKeysOnly
     * @param bool $isUsingDefaultPhrase
     * @return Phrase the new Phrase object
     * @throws Exception if no valid Phrase object could be constructed from the input
     */
    public static function create($id, $phraseKey, $json, $groupID = 0, $enabled = true, $createKeysOnly = false, $isUsingDefaultPhrase = false) {
        $data = json_decode($json, true);
        if (!empty($data)) {
            if (isset($data['class'])) {
                $className = $data['class'];
                /** @var Phrase $phraseObject */
                $phraseObject = new $className($id, $phraseKey, $enabled);
                $phraseObject->setPayload($json, $createKeysOnly, $isUsingDefaultPhrase);
                $phraseObject->setGroupID($groupID);
                return $phraseObject;
            }
            else {
                throw new Exception('Phrase\'s class not set in JSON payload');
            }
        }
        else {
            throw new Exception('Could not decode phrase\'s payload from JSON');
        }
    }

    /**
     * Returns whether the two given lists of placeholders do match (except their order)
     *
     * @param array $placeholders1
     * @param array $placeholders2
     * @return bool
     */
    public static function arePlaceholdersMatching($placeholders1, $placeholders2) {
        sort($placeholders1);
        sort($placeholders2);
        $count1 = count($placeholders1);
        $count2 = count($placeholders2);
        if ($count1 == $count2) {
            for ($i = 0; $i < $count1; $i++) {
                if (!isset($placeholders2[$i]) || $placeholders1[$i] !== $placeholders2[$i]) {
                    return false;
                }
            }
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Mark the given placeholders in the given text and return the modified text
     *
     * @param string $text
     * @param array $placeholders
     * @return string modified text with marked placeholders
     */
    public static function markPlaceholders($text, $placeholders) {
        foreach ($placeholders as $placeholder) {
            $text = str_replace($placeholder, '<strong class="text-primary">'.$placeholder.'</strong>', $text);
        }
        return $text;
    }

    /**
     * Adds a new value to the given phrase object, either with the given sub-key or with an auto-incrementing ID
     *
     * @param string $value the value (phrase content) to add
     * @param string $subKey (optional) sub-key if no auto-incrementing ID can/should be used
     * @throws Exception (optionally) if this phrase object does not support auto-incrementing IDs and the given sub-key is not allowed
     */
    abstract public function addValue($value, $subKey = NULL);

    public static function validateGroupName($name) {
        if (is_string($name)) {
            $name = trim($name);
            return preg_replace('/[\(\)\[\]\{\}]/s', '', $name);
        }
        else {
            return '';
        }
    }

}

?>