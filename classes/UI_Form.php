<?php

require_once('UI.php');

class UI_Form extends UI {

    protected $targetURL;
    protected $isGET;
    protected $contents;
    protected $hasFileUpload;

    function __construct($targetURL, $isGET, $hasFileUpload = false) {
        $this->targetURL = $targetURL;
        $this->isGET = $isGET;
        $this->contents = array();
        $this->hasFileUpload = $hasFileUpload;
    }

    public function addContent($content) {
        if ($content instanceof UI) {
            $this->contents[] = $content;
        }
        else {
            throw new Exception('Content must be an instance of class UI');
        }
    }

    public function getHTML() {
        $out = '<form class="form-horizontal" action="'.$this->targetURL.'" method="'.($this->isGET ? 'get' : 'post').'"';
        if ($this->hasFileUpload) {
            $out .= ' enctype="multipart/form-data"';
        }
        $out .= ' role="form">';
        foreach ($this->contents as $content) {
            $out .= $content->getHTML();
        }
        $out .= '</form>';
        return $out;
    }

}