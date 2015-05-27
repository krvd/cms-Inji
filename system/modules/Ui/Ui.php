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
        App::$cur->view->customAsset('js', ['file' => '/static/moduleAsset/Ui/js/Ui.js', 'name' => 'Ui']);
    }

}
