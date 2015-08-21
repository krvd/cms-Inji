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

namespace Exchange1c\Mode;

class File extends \Exchange1c\Mode {

    function process() {
        $dir = $this->exchange->path;
        \Tools::createDir($dir);
        $file = new \Exchange1c\Exchange\File();
        $file->name = $_GET['filename'];
        $file->exchange_id = $this->exchange->id;
        $file->save();

        if (strpos($_GET['filename'], '/') !== false) {
            $subDir = substr($_GET['filename'], 0, strrpos($_GET['filename'], "/") + 1);
            \Tools::createDir($dir . '/' . $subDir);
        }
        $status = 'success';
        $text = '';
        if (false === file_put_contents($dir . '/' . $_GET['filename'], file_get_contents("php://input"))) {
            $status = 'failure';
            $text = 'Fail on save file: ' . $_GET['filename'];
        }
        \App::$cur->exchange1c->response($status, $text, false);
        $this->end($status);
    }

}