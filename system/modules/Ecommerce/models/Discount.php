<?php

namespace Ecommerce;

class Discount extends \Model {

    static $objectName = 'Скидка';
    static $cols = [
        'name' => ['type' => 'text'],
        'type' => ['type' => 'select', 'source' => 'array', 'sourceArray' => ['sum' => 'Сумма', 'procent' => 'Процент']],
        'amount' => ['type' => 'text'],
        'condition' => ['type' => 'json'],
    ];
    static $labels = [
        'name' => 'Название',
        'type' => 'Тип',
        'amount' => 'Значение',
        'condition' => 'Условие',
    ];
    static $dataManagers = [
        'manager' => [
            'name' => 'Скидки',
            'cols' => [
                'name',
                'type',
                'amount',
            ],
        ],
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name'],
                ['type', 'amount'],
                //['condition']
            ]
    ]];

}
