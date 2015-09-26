<?php

namespace Ecommerce;

class Warehouse extends \Model
{
    static $objectName = 'Склад';
    static $labels = [
        'name' => 'Название',
    ];
    static $cols = [
        'name' => ['type' => 'text'],
    ];
    static $dataManagers = [
        'manager' => [
            'name' => 'Склады',
            'cols' => [
                'name',
            ],
        ],
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name'],
            ]
    ]];

}
