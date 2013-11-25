<?php

require_once('Phrase_Android.php');

class Phrase_Android_String extends Phrase_Android {

    protected $value;

    public function __construct($id, $phraseKey, $enabledForTranslation = true) {
        parent::__construct($id, $phraseKey, $enabledForTranslation);
    }

    /**
     * @return string the phrase's content
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * Returns the output of this phrase for the specific platform and type of phrase
     *
	 * @param bool $escapeHTML whether to escape HTML or not
     * @param int $groupID the group ID to get the output for (or Phrase::GROUP_ALL)
     * @return string output of this phrase
     */
    public function output($escapeHTML, $groupID) {
        if ($this->getGroupID() != $groupID && $groupID != Phrase::GROUP_ALL) {
            return '';
        }
        $out = "\t".'<string name="'.$this->phraseKey.'">'.self::writeToRaw($this->value, $escapeHTML).'</string>'."\n";
        return $out;
    }

    /**
     * Gets the phrase's payload in form of JSON data
     *
     * @return string payload as JSON data
     */
    public function getPayload() {
        return self::getPayloadFromValue($this->value);
    }

    /**
     * Creates JSON payload from a phrase's value(s)
     *
     * @param mixed $value single string or array of strings (value for the phrase)
     * @return string JSON payload
     */
    public static function getPayloadFromValue($value) {
        $data = array(
            'class' => 'Phrase_Android_String',
            'value' => $value
        );
        return json_encode($data);
    }

    /**
     * Sets the phrase's payload from the given JSON data
     *
     * @param string $json JSON data to get the payload from
     * @param bool $createKeysOnly whether the complete phrase should be created (true) or keys only (false)
     * @param bool $isUsingDefaultPhrase whether this is only using the default language's value and must thus be marked as empty
     */
    public function setPayload($json, $createKeysOnly = false, $isUsingDefaultPhrase = false) {
        $data = json_decode($json, true);
        if (!$createKeysOnly) {
            $this->isEmpty = $isUsingDefaultPhrase || empty($data['value']);
            $this->value = $data['value'];
        }
        else {
            $this->isEmpty = true;
            $this->value = '';
        }
    }

    /**
     * Returns the list of values for this phrase
     *
     * @return array list of values
     */
    public function getPhraseValues() {
        return array($this->value);
    }

    /**
     * Set the value at the given sub-key for this phrase
     *
     * @param string $subKey sub-key
     * @param string $value the new value to set
     */
    public function setPhraseValue($subKey, $value) {
        $this->value = $value;
    }

    /**
     * Adds a new value to the given phrase object, either with the given sub-key or with an auto-incrementing ID
     *
     * @param string $value the value (phrase content) to add
     * @param string $subKey (optional) sub-key if no auto-incrementing ID can/should be used
     * @throws Exception (optionally) if this phrase object does not support auto-incrementing IDs and the given sub-key is not allowed
     */
    public function addValue($value, $subKey = NULL) {
        $this->value = $value;
    }

    /**
     * Returns the the number of complete values and total values for this phrase
     *
     * @return array the first entry contains the number of complete values in this phrase and the second the total number of values
     */
    public function getCompleteness() {
        $complete = empty($this->value) ? 0 : 1;
        return array($complete, 1);
    }

}

?>