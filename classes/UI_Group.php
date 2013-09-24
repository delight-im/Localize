<?php

require_once('UI.php');

class UI_Group extends UI {

    /**
     * List of UI instances to render in this group
     *
     * @var array|UI[]
     */
    protected $contents;

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
        $out = '';
        foreach ($this->contents as $content) {
            $out .= $content->getHTML();
        }
        return $out;
    }

}