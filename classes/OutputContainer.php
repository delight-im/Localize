<?php

class OutputContainer {

    protected $content;
    protected $phrasesCount;
    protected $phrasesTotal;

    public function __construct() {
        $this->content = '';
        $this->phrasesCount = 0;
        $this->phrasesTotal = 0;
    }

    public function newPhrase($isEmpty = false) {
        if ($isEmpty) {
            $this->phrasesTotal++;
        }
        else {
            $this->phrasesCount++;
            $this->phrasesTotal++;
        }
    }

    public function setContent($content) {
        $this->content = $content;
    }

    public function getCompleteness() {
        return intval($this->phrasesCount/$this->phrasesTotal*100);
    }

    public function getContent() {
        return $this->content;
    }

}

?>