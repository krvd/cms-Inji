<?php

/**
 * Toolkit
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class Tools extends Model {

    static function randomString($length = 20) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    static function uriParse($uri) {
        $answerPos = strpos($uri, '?');
        $params = array_slice(explode('/', substr($uri, 0, $answerPos ? $answerPos : strlen($uri) )), 1);

        foreach ($params as $key => $param) {
            if ($param != '') {
                $params[$key] = urldecode($param);
            } else {
                unset($params[$key]);
            }
        }
        return $params;
    }

    static function createDir($path) {
        if (file_exists($path))
            return true;

        $path = explode('/', $path);
        $cur = '';
        foreach ($path as $item) {
            $cur .= $item . '/';
            if (!file_exists($cur)) {
                mkdir($cur);
            }
        }
        return true;
    }

}
