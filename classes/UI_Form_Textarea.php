<?php

require_once('UI.php');

class UI_Form_Textarea extends UI {

    private $label;
    private $key;
    private $placeholder;
    private $helpText;
    private $isInline;
    private $defaultText;
    private $rows;
    private $isRTL;
    private $onChangeJS;

    function __construct($label, $key, $placeholder, $helpText = '', $isInline = false, $defaultText = '', $rows = 1, $isRTL = false, $onChangeJS = '') {
        $this->label = $label;
        $this->key = $key;
        $this->placeholder = $placeholder;
        $this->helpText = $helpText;
        $this->isInline = $isInline;
        $this->defaultText = $defaultText;
        $this->rows = $rows;
        $this->isRTL = $isRTL;
        $this->onChangeJS = $onChangeJS;
    }

    public function getHTML() {
        $out = '';
        if (!$this->isInline) {
            $out .= '<div class="form-group"><label for="'.htmlspecialchars($this->key).'" class="col-lg-2 control-label">'.$this->label.'</label>';
            $out .= '<div class="col-lg-10">';
        }
        $out .= '<textarea class="form-control" rows="'.$this->rows.'" id="'.htmlspecialchars($this->key).'" name="'.htmlspecialchars($this->key).'" placeholder="'.htmlspecialchars($this->placeholder).'"';
        if ($this->isRTL) {
            $out .= ' dir="rtl"';
        }
        else {
            $out .= ' dir="ltr"';
        }
        if (!empty($this->onChangeJS)) {
            $out .= ' onchange="'.htmlspecialchars($this->onChangeJS).'"';
        }
        $out .= '>';
        if (!empty($this->defaultText)) {
            $out .= $this->defaultText;
        }
        $out .= '</textarea>';
        if (!$this->isInline) {
            if ($this->helpText != '') {
                $out .= '<span class="help-block">'.$this->helpText.'</span>';
            }
            $out .= '</div>';
            $out .= '</div>';
        }
        return $out;
    }

    public static function getOptimalRowCount($text, $minRows = 1) {
        if (stripos($text, "\n") !== false && $minRows < 2) {
            $minRows = 2;
        }

        $estimatedLines = floor(mb_strlen($text) / 24);
        if ($estimatedLines < $minRows) {
            return $minRows;
        }
        else {
            return $estimatedLines;
        }
    }

}