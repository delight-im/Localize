<?php

require_once('UI.php');

class UI_Paragraph extends UI {

    private $uiInstance;
    private $text;

    public function __construct($textOrUI) {
        if ($textOrUI instanceof UI) {
            $this->uiInstance = $textOrUI;
        }
        elseif (is_string($textOrUI)) {
            $this->text = $textOrUI;
        }
        else {
            throw new Exception('Paragraph content must be an instance of class UI or a text string');
        }
    }

    public function getHTML() {
        $out = '<p>';
        if (isset($this->uiInstance)) {
            $out .= $this->uiInstance->getHTML();
        }
        else {
            $out .= $this->text;
        }
        $out .= '</p>';
        return $out;
    }

}

?>