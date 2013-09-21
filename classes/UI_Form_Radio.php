<?php

require_once('UI.php');

class UI_Form_Radio extends UI {

    protected $label;
    protected $key;
    protected $options;
    protected $defaultOptionKey;

    public function __construct($label, $key) {
        $this->label = $label;
        $this->key = $key;
        $this->options = array();
        $this->defaultOptionKey = NULL;
    }

    public function addOption($label, $value, $jsEvents = '') {
        $this->options[] = array($label, $value, $jsEvents);
        if (!isset($this->defaultOptionKey)) { // if no item has been checked yet (default option may still be set later)
            $this->defaultOptionKey = $value; // always have one item checked (the default option or the first one)
        }
    }

    public function setDefaultOption($key) {
        $this->defaultOptionKey = $key;
    }

    public function getHTML() {
        $out = '<div class="form-group">';
        $out .= '<label class="col-lg-2 control-label">'.$this->label.'</label>';
        $out .= '<div class="col-lg-10">';
        foreach ($this->options as $option) {
            $out .= '<div class="radio">';
            $out .= '<label>';
            $out .= '<input type="radio" name="'.htmlspecialchars($this->key).'" value="'.htmlspecialchars($option[1]).'"';
            if ($option[1] == $this->defaultOptionKey) {
                $out .= ' checked="checked"';
            }
            if (!empty($option[2])) {
                $out .= ' onchange="'.$option[2].'"';
            }
            $out .= '> '.$option[0];
            $out .= '</label>';
            $out .= '</div>';
        }
        $out .= '</div>';
        $out .= '</div>';
        return $out;
    }

}

?>