<?php

require_once('UI.php');

class UI_Paragraph extends UI {

    protected $uiInstance;
    protected $text;
    protected $isPreformatted;

    public function __construct($textOrUI, $isPreformatted = false) {
        if ($textOrUI instanceof UI) {
            $this->uiInstance = $textOrUI;
        }
        elseif (is_string($textOrUI)) {
            $this->text = $textOrUI;
        }
        else {
            throw new Exception('Paragraph content must be an instance of class UI or a text string');
        }
        $this->isPreformatted = $isPreformatted;
    }

    public function getHTML() {
        if (isset($this->uiInstance)) {
            $out = $this->uiInstance->getHTML();
        }
        else {
            $out = $this->text;
        }

        if ($this->isPreformatted) {
            return '<pre class="pre-scrollable">'.htmlspecialchars($out).'</pre>';
        }
        else {
            return '<p>'.$out.'</p>';
        }
    }

}

?>