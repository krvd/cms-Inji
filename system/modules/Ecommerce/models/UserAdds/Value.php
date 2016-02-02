<?php

/**
 * UserAdds value
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ecommerce\UserAdds;

class Value extends \Model
{
    public static $labels = [
        'useradds_field_id' => 'Поле',
        'value' => 'Значение'
    ];
    public static $cols = [
        'useradds_field_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'field'],
        'value' => ['type' => 'text']
    ];
    public static $forms = [
        'manager' => [
            'map' => [
                ['useradds_field_id', 'value']
            ]
        ]
    ];
    public static $dataManagers = [
        'manager' => [
            'cols' => [
                'useradds_field_id',
                'value'
            ]
        ]
    ];

    public static function relations()
    {
        return [
            'field' => [
                'model' => 'Ecommerce\UserAdds\Field',
                'col' => 'useradds_field_id'
            ]
        ];
    }

}
