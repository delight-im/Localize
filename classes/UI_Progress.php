<?php

require_once('UI.php');

class UI_Progress extends UI {

    private $progress;

    function __construct($progress) {
        $this->progress = $progress;
    }

    public function getHTML() {
        $out = '<div class="progress">';
        $out .= '<div class="progress-bar '.self::getColorClass($this->progress).'" role="progressbar" aria-valuenow="'.$this->progress.'" aria-valuemin="0" aria-valuemax="100" style="width:'.$this->progress.'%;">';
        $out .= '<span class="sr-only">'.$this->progress.'%</span>';
        $out .= '</div>';
        $out .= '</div>';
        return $out;
    }

    public static function getColorClass($progress) {
        if ($progress < 33) {
            return 'progress-bar-warning';
        }
        elseif ($progress < 66) {
            return 'progress-bar-info';
        }
        else {
            return 'progress-bar-success';
        }
    }

}