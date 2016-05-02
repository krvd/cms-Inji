<?php

/**
 * Item image
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2016 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ecommerce\Item;

class Image extends \Model
{
    static $cols = [
        'file_id' => ['type' => 'image'],
        'item_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'item'],
        'name' => ['type' => 'text'],
        'description' => ['type' => 'html'],
        'weight' => ['type' => 'number'],
    ];

    static function relations()
    {
        return [
            'item' => [
                'col' => 'item_id',
                'model' => 'Ecommerce\Item'
            ],
            'file' => [
                'col' => 'file_id',
                'model' => 'Files\File'
            ]
        ];
    }

    static $dataManagers = [
        'manager' => [
            'cols' => [
                'file_id', 'name'
            ]
        ]
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name'],
                ['item_id', 'file_id'],
                ['description']
            ]
        ]
    ];
    function beforeDelete()
    {
        $this->file->delete();
    }

}
