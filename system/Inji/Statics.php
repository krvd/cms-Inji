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

/**
 * Description of Statics
 *
 * @author inji
 */
class Statics
{
    static function file($path, $resize = '', $resizeCrop = '')
    {
        $absolutePath = App::$cur->staticLoader->parsePath($path);
        $convet = FALSE;
        if (!file_exists($absolutePath) && file_exists(mb_convert_encoding($absolutePath, 'Windows-1251', 'UTF-8'))) {
            $absolutePath = mb_convert_encoding($absolutePath, 'Windows-1251', 'UTF-8');
            $convet = true;
        }
        if (!file_exists($absolutePath)) {
            return ''; //Tools::header(404, true);
        } else {
            $options = [];
            if ($resize) {
                $resize = explode('x', $resize);
                $options = ['resize' => ['x' => $resize[0], 'y' => $resize[1]]];
            }
            $options['crop'] = $resizeCrop;
            $path = Cache::file($absolutePath, $options);
            $path = $convet ? mb_convert_encoding($path, 'UTF-8', 'Windows-1251') : $path;
            return '/' . $path;
        }
    }



}
