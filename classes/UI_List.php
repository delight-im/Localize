<?php

require_once('UI.php');

class UI_List extends UI {

    protected $items;

    public function __construct() {
        $this->items = array();
    }

    public function addItem($textOrUI) {
        $this->items[] = $textOrUI;
    }

    public function getHTML() {
        $out = '<ul>';
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
        $out .= '</ul>';
        return $out;
    }

}

?>