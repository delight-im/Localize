<?php

require_once('UI.php');

class UI_Row extends UI {

    private $contents;

    function __construct($contents) {
        $this->contents = array();
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
        $out = '<div class="row">';
        foreach ($this->contents as $content) {
            $out .= $content->getHTML();
        }
        $out .= '</div>';
        return $out;
    }

}