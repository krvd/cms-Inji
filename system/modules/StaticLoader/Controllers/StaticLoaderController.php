<?php

/**
 * Static loader controlelr
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class StaticLoaderController extends Controller
{
    function indexAction()
    {
        $path = $this->module->parsePath(implode('/', func_get_args()));
        $convet = FALSE;
        if (!file_exists($path) && file_exists(mb_convert_encoding($path, 'Windows-1251', 'UTF-8'))) {
            $path = mb_convert_encoding($path, 'Windows-1251', 'UTF-8');
            $convet = true;
        }
        if (!file_exists($path)) {
            Tools::header(404, true);
        } else {
            $this->module->giveFile($path);
        }
    }

}
