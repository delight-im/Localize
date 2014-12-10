<?php

require_once('UI.php');

class UI_Form_Button extends UI {

    const ACTION_SUBMIT = 1;
    const ACTION_CANCEL = 2;

    protected $label;
    protected $key;
    protected $type;
    protected $action;
    protected $jsEvents;
    protected $enabled;
    protected $id;

    function __construct($label, $type = UI_Link::TYPE_SUCCESS, $action = self::ACTION_SUBMIT, $key = '', $value = '', $jsEvents = '') {
        $this->label = $label;
        $this->key = $key;
        $this->value = $value;
        $this->type = $type;
        $this->action = $action;
        $this->jsEvents = $jsEvents;
        $this->enabled = true;
        $this->id = '';
    }

    public function setID($id) {
        $this->id = $id;
    }

    public function setEnabled($state) {
        $this->enabled = $state;
    }

    public function setJSEvents($js) {
        $this->jsEvents = $js;
    }

    public function getHTML() {
        $out = '<button';
        if ($this->action == self::ACTION_CANCEL) {
            $out .= ' type="reset"';
        }
        else {
            $out .= ' type="submit"';
        }
        $out .= UI_Link::getButtonClass($this->type);
        if (!empty($this->key)) {
            $out .= ' name="'.htmlspecialchars($this->key).'"';
            if (!empty($this->value)) {
                $out .= ' value="'.htmlspecialchars($this->value).'"';
            }
        }
        if (!$this->enabled) {
            $out .= ' disabled="disabled"';
        }
        if (!empty($this->id)) {
            $out .= ' id="'.htmlspecialchars($this->id).'"';
        }
        if (!empty($this->jsEvents)) {
            $out .= ' onclick="'.$this->jsEvents.'"';
        }
        $out .= '>'.$this->label.'</button>';
        return $out;
    }

}

class UI_Form_ButtonGroup extends UI {

    /**
     * List of all buttons and links that will be rendered in this button group
     *
     * @var array|UI_Form_Button[]|UI_Link[]
     */
    private $buttons;
    private $isInline;

    public function __construct($buttons, $isInline = false) {
        $this->isInline = $isInline;
        if (isset($buttons) && is_array($buttons) && !empty($buttons)) {
            foreach ($buttons as $button) {
                if ($button instanceof UI_Form_Button || $button instanceof UI_Link) {
                    $this->buttons[] = $button;
                }
                else {
                    throw new Exception('Button must be an instance of class UI_Form_Button or class UI_Link');
                }
            }
        }
        else {
            throw new Exception('Buttons must be a non-empty array');
        }
    }

    public function getHTML() {
        $out = '<div class="form-group">';
        $out .= '<div class="'.($this->isInline ? 'col-lg-12' : 'col-lg-offset-2 col-lg-10').'">';
        $counter = 0;
        foreach ($this->buttons as $button) {
            if ($counter > 0) {
                $out .= ' ';
            }
            $out .= $button->getHTML();
            $counter++;
        }
        $out .= '</div>';
        $out .= '</div>';
        return $out;
    }
}