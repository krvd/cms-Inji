<?php

/**
 * UserAdds
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ecommerce;

class UserAdds extends \Model
{
    public static $labels = [
        'value' => 'Информация'
    ];
    public static $cols = [
        'value' => ['type' => 'dataManager', 'relation' => 'values'],
    ];

    public static function relations()
    {
        return [
            'values' => [
                'type' => 'many',
                'model' => 'Ecommerce\UserAdds\Value',
                'col' => 'useradds_id',
            ]
        ];
    }

}
