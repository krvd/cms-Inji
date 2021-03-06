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
        //Основные параметры
        'name' => ['type' => 'text'],
        'price' => ['type' => 'decimal'],
        'currency_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'currency'],
        'price_text' => ['type' => 'textarea'],
        'max_cart_price' => ['type' => 'decimal'],
        'icon_file_id' => ['type' => 'image'],
        'info' => ['type' => 'html'],
        //Системные
        'weight' => ['type' => 'number'],
        'date_create' => ['type' => 'dateTime'],
        //Менеджеры
        'field' => ['type' => 'dataManager', 'relation' => 'fields']
    ];
    public static $labels = [
        'name' => 'Название',
        'price' => 'Стоимость',
        'price_text' => 'Текстовое описание стоимости (отображается вместо цены)',
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
                'field'
            ],
            'sortMode' => true
        ],
    ];
    public static $forms = [
        'manager' => [
            'map' => [
                ['name',],
                ['max_cart_price', 'icon_file_id'],
                ['price', 'currency_id'],
                ['price_text'],
                ['info'],
                ['field']
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
            'fields' => [
                'type' => 'relModel',
                'model' => 'Ecommerce\Delivery\Field',
                'relModel' => 'Ecommerce\Delivery\DeliveryFieldLink'
            ]
        ];
    }

}
