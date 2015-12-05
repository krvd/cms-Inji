<?php
/**
 * File
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
namespace Files;

class File extends \Model
{
    function beforeDelete()
    {
        $path = $this->getRealPath();
        if (file_exists($path)) {
            unlink($path);
        }
    }

    function getRealPath()
    {
        $sitePath = \App::$primary->path;
        return "{$sitePath}/{$this->file_path}";
    }

    static function relations()
    {
        return [
            'type' => [
                'model' => 'Files\Type',
                'col' => 'type_id'
            ]
        ];
    }

}
