<?php

require_once('UI.php');

class UI_Form_Select extends UI {

    private $label;
    private $key;
    private $helpText;
    private $options;
    private $defaultOptionKey;

    public function __construct($label, $key, $helpText = '') {
        $this->label = $label;
        $this->key = $key;
        $this->helpText = $helpText;
        $this->options = array();
        $this->defaultOptionKey = NULL;
    }

    public function addOption($label, $value) {
        $this->options[] = array($label, $value);
    }

    public function setDefaultOption($key) {
        $this->defaultOptionKey = $key;
    }

    public function getHTML() {
        $out = '<div class="form-group">';
        $out .= '<label class="col-lg-2 control-label">'.$this->label.'</label>';
        $out .= '<div class="col-lg-10">';
        $out .= '<select class="form-control" name="'.$this->key.'">';
        foreach ($this->options as $option) {
            $out .= '<option value="'.htmlspecialchars($option[1]).'"';
            if ($option[1] == $this->defaultOptionKey) {
                $out .= ' selected="selected"';
            }
            $out .= '>'.htmlspecialchars($option[0]).'</option>';
        }
        $out .= '</select>';
        if ($this->helpText != '') {
            $out .= '<span class="help-block">'.$this->helpText.'</span>';
        }
        $out .= '</div>';
        $out .= '</div>';
        return $out;
    }

}

?>