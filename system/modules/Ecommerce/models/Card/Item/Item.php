<?php

/**
 * Card item model
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ecommerce\Card;

class Item extends \Model {

    static $objectName = 'Карта пользователя';
    static $cols = [
        'card_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'card'],
        'card_level_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'level'],
        'user_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'user'],
        'sum' => ['type' => 'text'],
        'code' => ['type' => 'text'],
    ];
    static $labels = [
        'card_id' => 'Карта',
        'card_level_id' => 'Уровень карты',
        'user_id' => 'Пользователь',
        'sum' => 'Накопления',
        'code' => 'Уникальный код',
    ];
    static $dataManagers = [
        'manager' => [
            'name' => 'Карты пользователей',
            'cols' => [
                'card_id',
                'card_level_id',
                'user_id',
                'sum',
                'code',
            ],
        ],
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['card_id', 'card_level_id'],
                ['user_id', 'sum'],
                ['code'],
            ]
    ]];

    function beforeSave() {
        foreach ($this->card->levels as $level) {
            if ((float) $level->sum <= (float) $this->sum) {
                $this->card_level_id = $level->id;
            }
        }
    }

    static function relations() {
        return [
            'card' => [
                'model' => 'Ecommerce\Card',
                'col' => 'card_id'
            ],
            'level' => [
                'model' => 'Ecommerce\Card\Level',
                'col' => 'card_level_id'
            ],
            'user' => [
                'model' => 'Users\User',
                'col' => 'user_id'
            ]
        ];
    }

}
