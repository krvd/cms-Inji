<?php

/**
 * Table
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ui;

class Table extends \Object {

    public $cols = [];
    public $rows = [];
    public $buttons = [];
    public $name = '&nbsp;';
    public $id = '';
    public $class = 'table';
    public $attributes = [];

    function setCols($cols) {
        $this->cols = $cols;
    }

    function draw() {
        \App::$cur->view->widget('Ui\Table/body', ['table' => $this]);
    }

    static function drawRow($row) {
        \App::$cur->view->widget('Ui\Table/row', ['row' => $row]);
    }

    function addRow($row) {
        $this->rows[] = $row;
    }

    function addButton($button) {
        $this->buttons[] = $button;
    }

}
