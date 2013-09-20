<?php

require_once('UI.php');

class UI_Form_Text extends UI {

    private $label;
    private $key;
    private $placeholder;
    private $isPassword;
    private $helpText;
    private $cssClasses;
    private $cssStyles;
    private $hasUniqueID;
    private $isInline;
    private $isRTL;
    private $defaultValue;

    function __construct($label, $key, $placeholder, $isPassword = false, $helpText = '', $cssClasses = '', $cssStyles = '', $hasUniqueID = true, $isInline = false, $isRTL = false) {
        $this->label = $label;
        $this->key = $key;
        $this->placeholder = $placeholder;
        $this->isPassword = $isPassword;
        $this->helpText = $helpText;
        $this->cssClasses = $cssClasses;
        $this->cssStyles = $cssStyles;
        $this->hasUniqueID = $hasUniqueID;
        $this->isInline = $isInline;
        $this->isRTL = $isRTL;
        $this->defaultValue = NULL;
    }

    public function setDefaultValue($value) {
        $this->defaultValue = trim($value);
    }

    public function getHTML() {
        $out = '';
        if (!$this->isInline) {
            $out .= '<div class="form-group';
            if (!empty($this->cssClasses)) {
                $out .= ' '.$this->cssClasses;
            }
            $out .= '"';
            if (!empty($this->cssStyles)) {
                $out .= ' style="'.$this->cssStyles.'"';
            }
            $out .= '>';
            $out .= '<label';
            if ($this->hasUniqueID) {
                $out .= ' for="'.htmlspecialchars($this->key).'"';
            }
            $out .= ' class="col-lg-2 control-label">'.$this->label.'</label>';
            $out .= '<div class="col-lg-10">';
        }
        $out .= '<input type="'.($this->isPassword ? 'password' : 'text').'" class="form-control"';
        if ($this->isRTL) {
            $out .= ' dir="rtl"';
        }
        else {
            $out .= ' dir="ltr"';
        }
        if ($this->hasUniqueID) {
            $out .= ' id="'.htmlspecialchars($this->key).'"';
        }
        if (!empty($this->defaultValue)) {
            $out .= ' value="'.htmlspecialchars($this->defaultValue).'"';
        }
        $out .= ' name="'.htmlspecialchars($this->key).'" placeholder="'.htmlspecialchars($this->placeholder).'">';
        if (!$this->isInline) {
            if ($this->helpText != '') {
                $out .= '<span class="help-block">'.$this->helpText.'</span>';
            }
            $out .= '</div>';
            $out .= '</div>';
        }
        return $out;
    }

}