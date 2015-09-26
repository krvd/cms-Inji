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

namespace Dashboard;

class Comment extends \Model
{
    static $objectName = 'Комментарии';
    static $labels = [
        'model' => 'Тип ресурса',
        'item_id' => 'Ресурс',
        'user_id' => 'Автор',
        'text' => 'Текст',
        'date' => 'Дата',
    ];
    static $cols = [
        'user_id' => [
            'type' => 'relation',
            'relation' => 'user',
            'showCol' => 'mail'
        ],
        'item_id' => [
            'type' => 'void',
            'value' => [
                'type' => 'moduleMethod',
                'module' => 'Dashboard',
                'method' => 'itemHref'
            ]
        ]
    ];
    static $dataManagers = [
        'manager' => [
            'options' => [
                'access' => [
                    'groups' => [
                        3, 4
                    ]
                ],
                'formOnPage' => true
            ],
            'cols' => [
                'model',
                'item_id',
                'user_id',
                'text',
                'date'
            ],
            'sortable' => [
                'user_id',
                'text',
                'date'
            ],
            'preSort' => [
                'date' => 'desc'
            ],
            'rowButtons' => [
                'open'
            ]
        ],
    ];

    static function relations()
    {
        return [
            'user' => [
                'model' => 'Users\User',
                'col' => 'user_id'
            ]
        ];
    }

}
