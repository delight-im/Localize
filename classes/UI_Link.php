<?php

require_once('UI.php');

class UI_Link extends UI {

    const TYPE_NONE = 0;
    const TYPE_SUCCESS = 1;
    const TYPE_INFO = 2;
    const TYPE_WARNING = 3;
    const TYPE_DANGER = 4;
    const TYPE_IMPORTANT = 5;
    const TYPE_UNIMPORTANT = 6;

    private $label;
    private $target;
    private $buttonType;
    private $cssClasses;
    private $cssStyles;
    private $jsEvents;

    function __construct($label, $target, $buttonType = self::TYPE_NONE, $cssClasses = '', $cssStyles = '', $jsEvents = '') {
        $this->label = $label;
        $this->target = $target;
        $this->buttonType = $buttonType;
        $this->cssClasses = $cssClasses;
        $this->cssStyles = $cssStyles;
        $this->jsEvents = $jsEvents;
    }

    public function getHTML() {
        $out = '<a href="'.$this->target.'"';
        $out .= self::getButtonClass($this->buttonType, $this->cssClasses);
        if (!empty($this->cssStyles)) {
            $out .= ' style="'.$this->cssStyles.'"';
        }
        if (!empty($this->jsEvents)) {
            $out .= ' onclick="'.$this->jsEvents.'"';
        }
        $out .= '>'.$this->label.'</a>';
        return $out;
    }

    public static function getButtonClass($type, $additionalClasses = '') {
        switch ($type) {
            case self::TYPE_SUCCESS:
                $cssClasses = 'btn btn-success';
                break;
            case self::TYPE_INFO:
                $cssClasses = 'btn btn-info';
                break;
            case self::TYPE_WARNING:
                $cssClasses = 'btn btn-warning';
                break;
            case self::TYPE_DANGER:
                $cssClasses = 'btn btn-danger';
                break;
            case self::TYPE_IMPORTANT:
                $cssClasses = 'btn btn-primary';
                break;
            case self::TYPE_UNIMPORTANT:
                $cssClasses = 'btn btn-default';
                break;
            default:
                $cssClasses = '';
        }
        if (empty($additionalClasses)) {
            if (empty($cssClasses)) {
                return '';
            }
            else {
                return ' class="'.$cssClasses.'"';
            }
        }
        else {
            if (empty($cssClasses)) {
                return ' class="'.$additionalClasses.'"';
            }
            else {
                return ' class="'.$cssClasses.' '.$additionalClasses.'"';
            }
        }
    }

}