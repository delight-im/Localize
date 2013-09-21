<?php

require_once('UI.php');

class UI_Cell extends UI {

    const SIZE_MIN = 1;
    const SIZE_MAX = 12;

    protected $size;
    protected $contents;

    function __construct($contents, $size = self::SIZE_MAX) {
        if ($size >= self::SIZE_MIN && $size <= self::SIZE_MAX) {
            $this->size = $size;
            $this->contents = array();
            if (isset($contents) && is_array($contents) && count($contents) > 0) {
                foreach ($contents as $content) {
                    if ($content instanceof UI) {
                        $this->contents[] = $content;
                    }
                    else {
                        throw new Exception('Contents must contain instances of class UI');
                    }
                }
            }
            else {
                throw new Exception('Contents must be a non-empty array');
            }
        }
        else {
            throw new Exception('Size must be between '.self::SIZE_MIN.' and '.self::SIZE_MAX);
        }
    }

    public function getHTML() {
        $out = '<div class="col-lg-'.$this->size.'">';
        foreach ($this->contents as $content) {
            $out .= $content->getHTML();
        }
        $out .= '</div>';
        return $out;
    }

}