<?php

/**
 * Item name
 *
 * Info
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class StaticLoaderController extends Controller {

    function indexAction() {
        $path = $this->module->parsePath(implode('/', func_get_args()));
        if (!file_exists($path)) {
            
            $this->module->header(404, true);
        } else {
            $this->module->giveFile($path);
        }
    }

}
