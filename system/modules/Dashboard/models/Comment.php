<?php

/**
 * Comment
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Dashboard;

class Comment extends \Model
{
    public static $objectName = 'Комментарии';
    public static $labels = [
        'model' => 'Тип ресурса',
        'item_id' => 'Ресурс',
        'user_id' => 'Автор',
        'text' => 'Текст',
    ];
    public static $cols = [
        'user_id' => [
            'type' => 'relation',
            'relation' => 'user',
            'showCol' => 'mail'
        ],
        'model' => ['type' => 'text'],
        'text' => ['type' => 'html'],
        'item_id' => [
            'type' => 'void',
            'value' => [
                'type' => 'moduleMethod',
                'module' => 'Dashboard',
                'method' => 'itemHref'
            ]
        ]
    ];
    public static $dataManagers = [
        'manager' => [
            'cols' => [
                'model',
                'item_id',
                'user_id',
                'text',
                'date_create'
            ],
            'sortable' => [
                'user_id',
                'text',
                'date_create'
            ],
            'preSort' => [
                'date_create' => 'desc'
            ],
        ],
    ];

    public static function relations()
    {
        return [
            'user' => [
                'model' => 'Users\User',
                'col' => 'user_id'
            ]
        ];
    }

}
