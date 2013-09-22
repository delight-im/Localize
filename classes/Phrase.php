<?php

abstract class Phrase {

    protected $id;
    protected $phraseKey;
    protected $enabledForTranslation;

    /**
     * Returns the output of this phrase for the specific platform and type of phrase
     *
     * @return string output of this phrase
     */
    abstract public function output();

    /**
     * Returns the percentage of completion for this phrase where 0.0 is empty and 1.0 is completed
     *
     * @return float the percentage of completion for this phrase
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
     */
    abstract public function setPayload($json, $createKeysOnly = false);

    public function __construct($id, $phraseKey, $enabledForTranslation = true) {
        $this->id = $id;
        $this->phraseKey = $phraseKey;
        $this->enabledForTranslation = $enabledForTranslation;
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

    public static function create($id, $phraseKey, $json, $enabled = true, $createKeysOnly = false) {
        $data = json_decode($json, true);
        if (!empty($data)) {
            if (isset($data['class'])) {
                $className = $data['class'];
                $phraseObject = new $className($id, $phraseKey, $enabled);
                $phraseObject->setPayload($json, $createKeysOnly);
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

}

?>