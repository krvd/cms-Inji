<?php

namespace Ecommerce;

class PayType extends \Model {

    static $objectName = 'Оплата';
    static $labels = [
        'name' => 'Название',
        'icon_file_id' => 'Иконка',
    ];
    static $cols = [
        'name' => ['type' => 'text'],
        'icon_file_id' => ['type' => 'image'],
    ];
    static $dataManagers = [
        'manager' => [
            'name' => 'Способы оплаты',
            'cols' => [
                'name'
            ],
        ],
    ];
    static $forms = [
        'manager' => [
            'map' => [
                ['name', 'icon_file_id'],
            ]
    ]];

    static function relations() {
        return [
            'icon' => [
                'model' => 'Files\File',
                'col' => 'icon_file_id'
            ],
        ];
    }

}
