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
    public static $cols = [
        'code' => ['type' => 'text'],
        'type_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'type'],
        'folder_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'foler'],
        'upload_code' => ['type' => 'text'],
        'path' => ['type' => 'textarea'],
        'name' => ['type' => 'text'],
        'about' => ['type' => 'html'],
        'original_name' => ['type' => 'text'],
        'md5' => ['type' => 'text'],
        'date_create' => ['type' => 'dateTime'],
    ];

    public function beforeSave()
    {
        $path = $this->getRealPath();
        if (!$this->md5 && $this->path && file_exists($path)) {
            $this->md5 = md5_file($path);
        }
    }

    public function beforeDelete()
    {
        $path = $this->getRealPath();
        if (file_exists($path)) {
            unlink($path);
        }
    }

    public function getRealPath()
    {
        $sitePath = \App::$primary->path;
        return "{$sitePath}{$this->path}";
    }

    public static function relations()
    {
        return [
            'type' => [
                'model' => 'Files\Type',
                'col' => 'type_id'
            ],
            'folder' => [
                'model' => 'Files\Folder',
                'col' => 'folder_id'
            ],
        ];
    }

}
