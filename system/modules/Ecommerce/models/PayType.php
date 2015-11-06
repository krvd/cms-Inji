<?php

namespace Ecommerce;

class PayType extends \Model
{
    static $objectName = 'Оплата';
    static $labels = [
        'name' => 'Название',
        'merchants' => 'Платежные системы',
        'icon_file_id' => 'Иконка',
    ];
    static $cols = [
        'name' => ['type' => 'text'],
        'merchants' => ['type' => 'bool'],
        'icon_file_id' => ['type' => 'image'],
    ];
    static $dataManagers = [
        'manager' => [
            'name' => 'Способы оплаты',
            'cols' => [
                'name', 'merchants'
            ],
        ],
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name', 'icon_file_id'],
                ['merchants']
            ]
    ]];

    static function relations()
    {
        return [
            'icon' => [
                'model' => 'Files\File',
                'col' => 'icon_file_id'
            ],
        ];
    }

}
