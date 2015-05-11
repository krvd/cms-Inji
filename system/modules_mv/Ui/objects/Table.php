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

    function setCols($cols) {
        $this->cols = $cols;
    }

    function draw() {
        \Inji::app()->view->widget('Ui\Table/body', ['table' => $this]);
    }

    function addRow($row) {
        $this->rows[] = $row;
    }

    function addButton($button) {
        $this->buttons[] = $button;
    }

}
