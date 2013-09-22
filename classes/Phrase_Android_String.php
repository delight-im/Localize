<?php

require_once('Phrase_Android.php');

class Phrase_Android_String extends Phrase_Android {

    protected $value;

    public function __construct($id, $phraseKey, $enabledForTranslation = true) {
        parent::__construct($id, $phraseKey, $enabledForTranslation);
    }

    public function setValue($value) {
        $this->value = $value;
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
     * @return string output of this phrase
     */
    public function output() {
        $out = "\t".'<string name="'.$this->phraseKey.'">'.self::writeToRaw($this->value).'</string>'."\n";
        return $out;
    }

    /**
     * Returns the percentage of completion for this phrase where 0.0 is empty and 1.0 is completed
     *
     * @return float the percentage of completion for this phrase
     */
    public function getCompleteness() {
        return !empty($this->value);
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
     */
    public function setPayload($json, $createKeysOnly = false) {
        $data = json_decode($json, true);
        if (!$createKeysOnly) {
            $this->value = $data['value'];
        }
        else {
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

}

?>