<?php

require_once('UI.php');

class UI_Heading extends UI {

    const LEVEL_MIN = 1;
    const LEVEL_MAX = 6;

    protected $text;
    protected $isTextCentered;
    protected $subtext;

    function __construct($text, $isTextCentered = FALSE, $level = self::LEVEL_MIN, $subtext = '') {
        $this->text = $text;
        $this->isTextCentered = $isTextCentered;
        if ($level >= self::LEVEL_MIN && $level <= self::LEVEL_MAX) {
            $this->level = $level;
        }
        else {
            throw new Exception('Level must be between '.self::LEVEL_MIN.' and '.self::LEVEL_MAX);
        }
        $this->subtext = $subtext;
    }

    public function getHTML() {
        $out = '<div class="page-header">';
        $out .= '<h'.$this->level.($this->isTextCentered ? ' class="text-center"' : '').'>';
        $out .= $this->text;
        if (!empty($this->subtext)) {
            $out .= '<br><small>'.htmlspecialchars($this->subtext).'</small>';
        }
        $out .= '</h'.$this->level.'>';
        $out .= '</div>';
        return $out;
    }

}