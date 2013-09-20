<?php

require_once('UI.php');

class UI_Container extends UI {

    private $contents;
    private $isLandingPage;

    function __construct($contents, $isLandingPage = FALSE) {
        $this->contents = array();
        $this->isLandingPage = $isLandingPage;
        if (isset($contents) && is_array($contents) && count($contents) > 0) {
            foreach ($contents as $content) {
                if ($content instanceof UI) {
                    $this->contents[] = $content;
                }
                else {
                    throw new Exception('Contents must be an array of instances of class UI');
                }
            }
        }
        else {
            throw new Exception('Contents must be a non-empty array');
        }
    }

    public function getHTML() {
        $out = '';
        if ($this->isLandingPage) {
            $out .= '<div class="jumbotron">';
        }
        $out .= '<div class="container">';
        foreach ($this->contents as $content) {
            $out .= $content->getHTML();
        }
        $out .= '</div>';
        if ($this->isLandingPage) {
            $out .= '</div>';
        }
        return $out;
    }

}