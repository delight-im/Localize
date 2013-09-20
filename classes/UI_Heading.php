<?php

require_once('UI.php');

class UI_Heading extends UI {

    const LEVEL_MIN = 1;
    const LEVEL_MAX = 6;

    private $text;
    private $isTextCentered;

    function __construct($text, $isTextCentered = FALSE, $level = self::LEVEL_MIN) {
        $this->text = $text;
        $this->isTextCentered = $isTextCentered;
        if ($level >= self::LEVEL_MIN && $level <= self::LEVEL_MAX) {
            $this->level = $level;
        }
        else {
            throw new Exception('Level must be between '.self::LEVEL_MIN.' and '.self::LEVEL_MAX);
        }
    }

    public function getHTML() {
        return '<div class="page-header"><h'.$this->level.($this->isTextCentered ? ' class="text-center"' : '').'>'.$this->text.'</h'.$this->level.'></div>';
    }

}