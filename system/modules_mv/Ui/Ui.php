<?php

/**
 * Ui generator
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class Ui extends Module {

    function init() {
        Inji::app()->view->customAsset('js', '/static/moduleAsset/Ui/js/Ui.js');
    }

}
