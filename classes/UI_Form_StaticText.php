<?php

require_once('UI.php');

class UI_Form_StaticText extends UI {

    protected $label;
    protected $content;
    protected $helpText;

    function __construct($label, $content, $helpText = '') {
        $this->label = $label;
        $this->content = $content;
        $this->helpText = $helpText;
    }

    public function getHTML() {
        $out = '<div class="form-group">';
        $out .= '<label class="col-lg-2 control-label">'.$this->label.'</label>';
        $out .= '<div class="col-lg-10">';
        $out .= '<p class="form-control-static">'.$this->content.'</p>';
        if ($this->helpText != '') {
            $out .= '<span class="help-block">'.$this->helpText.'</span>';
        }
        $out .= '</div>';
        $out .= '</div>';
        return $out;
    }

}