<?php

/**
 * Model for card
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ecommerce;

class Card extends \Model
{
    static $objectName = 'Карта';
    static $cols = [
        'name' => ['type' => 'text'],
        'price' => ['type' => 'text'],
        'level' => ['type' => 'dataManager', 'relation' => 'levels'],
        'image_file_id' => ['type' => 'image'],
    ];
    static $labels = [
        'name' => 'Название',
        'price' => 'Стоимость',
        'level' => 'Уровни',
        'image_file_id' => 'Изображение',
    ];
    static $dataManagers = [
        'manager' => [
            'name' => 'Бонусные карты',
            'cols' => [
                'name',
                'price',
                'level'
            ],
        ],
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name', 'price'],
                ['image_file_id'],
                ['level'],
            ]
    ]];

    static function relations()
    {
        return [
            'levels' => [
                'type' => 'many',
                'model' => 'Ecommerce\Card\Level',
                'col' => 'card_id'
            ],
            'image' => [
                'model' => 'Files\File',
                'col' => 'image_file_id'
            ]
        ];
    }

}
