<?php

require_once('UI.php');

class UI_Form_File extends UI {

    private $label;
    private $key;
    private $helpText;

    function __construct($label, $key, $helpText = '') {
        $this->label = $label;
        $this->key = $key;
        $this->helpText = $helpText;
    }

    public function getHTML() {
        $out = '';
        $out .= '<div class="form-group"><label for="'.htmlspecialchars($this->key).'" class="col-lg-2 control-label">'.$this->label.'</label>';
        $out .= '<div class="col-lg-10">';
        $out .= '<input type="file" class="form-control" id="'.htmlspecialchars($this->key).'" name="'.htmlspecialchars($this->key).'">';
        if ($this->helpText != '') {
            $out .= '<span class="help-block">'.$this->helpText.'</span>';
        }
        $out .= '</div>';
        $out .= '</div>';
        return $out;
    }

}