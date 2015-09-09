<?php

require_once('UI.php');

class UI_List extends UI {

    protected $items;
    protected $ordered;

    public function __construct() {
        $this->items = array();
        $this->ordered = false;
    }

    public function addItem($textOrUI) {
        $this->items[] = $textOrUI;
    }

    public function setOrdered($ordered) {
        $this->ordered = $ordered;
    }

    public function getHTML() {
        if ($this->ordered) {
            $out = '<ol>';
        }
        else {
            $out = '<ul>';
        }
        foreach ($this->items as $item) {
            $out .= '<li>';
            if ($item instanceof UI) {
                $out .= $item->getHTML();
            }
            elseif (is_string($item)) {
                $out .= $item;
            }
            else {
                throw new Exception('List items must be instances of class UI or text strings');
            }
            $out .= '</li>';
        }
        if ($this->ordered) {
            $out .= '</ol>';
        }
        else {
            $out .= '</ul>';
        }
        return $out;
    }

}
