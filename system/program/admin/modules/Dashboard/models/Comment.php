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

class Comment extends \Model {

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
                'user_id',
                'text',
                'date',
            ],
            'sortable' => [
                'user_id',
                'text',
                'date',
            ]
        ]
    ];

    static function relations() {
        return [
            'user' => [
                'model' => 'Users\User',
                'col' => 'user_id'
            ]
        ];
    }

}
