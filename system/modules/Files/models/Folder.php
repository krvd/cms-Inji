<?php

namespace Files;

class Folder extends \Model
{

    function beforeDelete()
    {
        foreach ($this->files as $file) {
            $file->delete();
        }
    }

    static function relations()
    {
        return [
            'files' => [
                'type' => 'many',
                'model' => 'Files\File',
                'col' => 'folder_id'
            ]
        ];
    }

}
