<?php

class Edit {

    protected $referencedPhraseID;
    protected $phraseSubKey;
    protected $suggestedValue;

    function __construct($referencedPhraseID, $phraseSubKey, $suggestedValue) {
        $this->referencedPhraseID = $referencedPhraseID;
        $this->phraseSubKey = $phraseSubKey;
        $this->suggestedValue = $suggestedValue;
    }

    /**
     * @return string the phrase's internal sub-key
     */
    public function getPhraseSubKey() {
        return $this->phraseSubKey;
    }

    /**
     * @return int ID of the referenced phrase from the default language
     */
    public function getReferencedPhraseID() {
        return $this->referencedPhraseID;
    }

    /**
     * @return string the suggested new value for this phrase
     */
    public function getSuggestedValue() {
        return $this->suggestedValue;
    }

}