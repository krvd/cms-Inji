<?php

namespace Ecommerce\Item\Price;

class Type extends \Model {

    static $objectName = 'Тип цены';
    static $cols = [
        'name' => ['type' => 'text'],
        'curency' => ['type' => 'text'],
    ];
    static $labels = [
        'name' => 'Название',
        'curency' => 'Валюта',
    ];
    static $dataManagers = [
        'manager' => [
            'cols' => [
                'name',
                'curency'
            ]
        ]
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name', 'curency']
            ]
        ]
    ];

}
