<?php

require_once('UI.php');

class UI_Form_Hidden extends UI {

    private $key;
    private $value;

    function __construct($key, $value) {
        $this->key = $key;
        $this->value = $value;
    }

    public function getHTML() {
        $out = '<input type="hidden" name="'.htmlspecialchars($this->key).'" value="'.htmlspecialchars($this->value).'">';
        return $out;
    }

}