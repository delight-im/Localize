<?php

require_once('UI.php');

class UI_Table extends UI {

    private $columnCount;
    private $headers;
    private $rows;

    function __construct($headers) {
        if (isset($headers) && is_array($headers) && !empty($headers)) {
            $this->columnCount = count($headers);
            $this->headers = $headers;
        }
        else {
            throw new Exception('Headers must be a non-empty array of strings');
        }
        $this->rows = array();
    }

    public function addRow($columns, $id = '', $cssClasses = '', $cssStyle = '') {
        if (isset($columns) && is_array($columns) && !empty($columns)) {
            if (count($columns) == $this->columnCount) {
                $this->rows[] = array($columns, $id, $cssClasses, $cssStyle);
            }
            else {
                throw new Exception('Row must have '.$this->columnCount.' columns as specified by the headers');
            }
        }
        else {
            throw new Exception('Columns must be a non-empty array of strings');
        }
    }

    public function getHTML() {
        $out = '<div class="table-responsive">';
        $out .= '<table class="table table-bordered">';
        $out .= '<thead><tr>';
        foreach ($this->headers as $header) {
            $out .= '<th>'.$header.'</th>';
        }
        $out .= '</tr></thead>';
        $out .= '<tbody>';
        foreach ($this->rows as $row) {
            $out .= '<tr';
            if (!empty($row[1])) {
                $out .= ' id="'.$row[1].'"';
            }
            if (!empty($row[2])) {
                $out .= ' class="'.$row[2].'"';
            }
            if (!empty($row[3])) {
                $out .= ' style="'.$row[3].'"';
            }
            $out .= '>';
            foreach ($row[0] as $column) {
                $out .= '<td>'.$column.'</td>';
            }
            $out .= '</tr>';
        }
        $out .= '</tbody>';
        $out .= '</table>';
        $out .= '</div>';
        return $out;
    }

}