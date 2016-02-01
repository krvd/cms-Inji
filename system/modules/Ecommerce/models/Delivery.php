<?php

/**
 * Delivery
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ecommerce;

class Delivery extends \Model
{
    public static $objectName = 'Доставка';
    public static $cols = [
        'name' => ['type' => 'text'],
        'price' => ['type' => 'decimal'],
        'max_cart_price' => ['type' => 'decimal'],
        'icon_file_id' => ['type' => 'image'],
        'currency_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'currency'],
        'weight' => ['type' => 'number'],
        'info' => ['type' => 'textarea']
    ];
    public static $labels = [
        'name' => 'Название',
        'price' => 'Стоимость',
        'max_cart_price' => 'Басплатно при',
        'icon_file_id' => 'Иконка',
        'currency_id' => 'Валюта',
        'info' => 'Дополнительная информация'
    ];
    public static $dataManagers = [
        'manager' => [
            'name' => 'Варианты доставки',
            'cols' => [
                'name',
                'price',
                'currency_id',
                'max_cart_price',
            ],
            'sortMode' => true
        ],
    ];
    public static $forms = [
        'manager' => [
            'map' => [
                ['name',],
                ['price', 'currency_id'],
                ['max_cart_price', 'icon_file_id'],
                ['info']
            ]
    ]];

    public static function relations()
    {
        return [
            'icon' => [
                'model' => 'Files\File',
                'col' => 'icon_file_id'
            ],
            'currency' => [
                'model' => 'Money\Currency',
                'col' => 'currency_id'
            ],
        ];
    }

}
