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
class StaticLoaderController extends Controller
{
    function indexAction()
    {
        $path = $this->module->parsePath(implode('/', func_get_args()));
        if (!file_exists(preg_match('![а-Я]!', $path) ? mb_convert_encoding($path, 'Windows-1251', 'UTF-8') : $path)) {
            Tools::header(404, true);
        } else {
            $this->module->giveFile($path);
        }
    }

}
