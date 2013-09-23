<?php

require_once('Phrase_Android.php');

class Phrase_Android_StringArray extends Phrase_Android {

    protected $values;

    public function __construct($id, $phraseKey, $enabledForTranslation = true) {
        parent::__construct($id, $phraseKey, $enabledForTranslation);
        $this->values = array();
    }

    /**
     * Returns the output of this phrase for the specific platform and type of phrase
     *
     * @return string output of this phrase
     */
    public function output() {
        $out = "\t".'<string-array name="'.$this->phraseKey.'">'."\n";
        foreach ($this->values as $value) {
            $out .= "\t\t".'<item>'.self::writeToRaw($value).'</item>'."\n";
        }
        $out .= "\t".'</string-array>'."\n";
        return $out;
    }

    /**
     * Returns the percentage of completion for this phrase where 0.0 is empty and 1.0 is completed
     *
     * @return float the percentage of completion for this phrase
     */
    public function getCompleteness() {
        $complete = 0;
        $total = 0;
        foreach ($this->values as $value) {
            if (!empty($value)) {
                $complete++;
            }
            $total++;
        }
        return $complete/$total;
    }

    /**
     * Gets the phrase's payload in form of JSON data
     *
     * @return string payload as JSON data
     */
    public function getPayload() {
        return self::getPayloadFromValue($this->values);
    }

    /**
     * Creates JSON payload from a phrase's value(s)
     *
     * @param mixed $value single string or array of strings (value for the phrase)
     * @return string JSON payload
     */
    public static function getPayloadFromValue($value) {
        $data = array(
            'class' => 'Phrase_Android_StringArray',
            'values' => $value
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
            $this->values = $data['values'];
        }
        else {
            $this->values = array();
            $keys = array_keys($data['values']);
            foreach ($keys as $key) {
                $this->values[$key] = '';
            }
        }
    }

    /**
     * Returns the list of values for this phrase
     *
     * @return array list of values
     */
    public function getPhraseValues() {
        return $this->values;
    }

    /**
     * Set the value at the given sub-key for this phrase
     *
     * @param string $subKey sub-key
     * @param string $value the new value to set
     * @throws Exception if the sub-key could not be found
     */
    public function setPhraseValue($subKey, $value) {
        if (isset($this->values[$subKey])) {
            $this->values[$subKey] = $value;
        }
        else {
            throw new Exception('Unknown sub-key '.$subKey);
        }
    }

    /**
     * Adds a new value to the given phrase object, either with the given sub-key or with an auto-incrementing ID
     *
     * @param string $value the value (phrase content) to add
     * @param string $subKey (optional) sub-key if no auto-incrementing ID can/should be used
     * @throws Exception (optionally) if this phrase object does not support auto-incrementing IDs and the given sub-key is not allowed
     */
    public function addValue($value, $subKey = NULL) {
        $this->values[] = $value;
    }

}

?>