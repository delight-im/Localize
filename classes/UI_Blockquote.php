<?php

require_once('UI.php');
require_once('UI.php');

class UI_Blockquote extends UI_Paragraph {

    protected $authorName;

    public function __construct($textOrUI, $authorName = NULL) {
        parent::__construct($textOrUI, false);
        $this->authorName = $authorName;
    }

    public function getHTML() {
        $out = '<blockquote style="background-color:rgba(255, 255, 255, 0.6); border:2px solid rgba(0, 0, 0, 0.1);">';
        $out .= parent::getHTML();
        if (!empty($this->authorName)) {
            $out .= '<footer style="font-size:70%; text-indent:42px;">— '.$this->authorName.'</footer>';
        }
        $out .= '</blockquote>';
        return $out;
    }

}

?>