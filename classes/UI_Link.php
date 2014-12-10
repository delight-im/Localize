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
    const SIZE_DEFAULT = 0;
    const SIZE_SMALL = 1;
    const SIZE_LARGE = 2;
    const SIZE_TINY = 3;

    protected $label;
    protected $target;
    protected $buttonType;
    protected $cssClasses;
    protected $cssStyles;
    protected $jsEvents;
    protected $tabIndex;
    protected $openNewTab;
    protected $size;

    function __construct($label, $target, $buttonType = self::TYPE_NONE, $cssClasses = '', $cssStyles = '', $jsEvents = '') {
        $this->label = $label;
        $this->target = $target;
        $this->buttonType = $buttonType;
        $this->cssClasses = $cssClasses;
        $this->cssStyles = $cssStyles;
        $this->jsEvents = $jsEvents;
        $this->tabIndex = NULL;
        $this->openNewTab = false;
        $this->size = self::SIZE_DEFAULT;
    }

    public function setSize($size) {
        $this->size = $size;
    }

    public function getSize() {
        return $this->size;
    }

    public function setOpenNewTab($openNewTab) {
        $this->openNewTab = $openNewTab;
    }

    public function isOpenNewTab() {
        return $this->openNewTab;
    }

    public function setTabIndex($tabIndex) {
        $this->tabIndex = $tabIndex;
    }

    public function getHTML() {
        $out = '<a href="'.$this->target.'"';
        $out .= self::getButtonClass($this->buttonType, $this->size, $this->cssClasses);
        if ($this->openNewTab) {
            $out .= ' target="_blank"';
        }
        if (!empty($this->cssStyles)) {
            $out .= ' style="'.$this->cssStyles.'"';
        }
        if (!empty($this->jsEvents)) {
            $out .= ' onclick="'.$this->jsEvents.'"';
        }
        if (isset($this->tabIndex)) {
            $out .= ' tabindex="'.htmlspecialchars($this->tabIndex).'"';
        }
        $out .= '>'.$this->label.'</a>';
        return $out;
    }

    protected static function getSizeClass($size, $prefix = '') {
        switch ($size) {
            case self::SIZE_SMALL:
                return $prefix.'btn-sm';
            case self::SIZE_LARGE:
                return $prefix.'btn-lg';
            case self::SIZE_TINY:
                return $prefix.'btn-xs';
            default:
                return '';
        }
    }

    public static function getButtonClass($type, $size = self::SIZE_DEFAULT, $additionalClasses = '') {
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

        $cssClasses .= self::getSizeClass($size, empty($cssClasses) ? '' : ' ');

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