<?php

/**
 * Item name
 *
 * Info
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ecommerce;

class UserAdds extends \Model
{
    static $labels = [
        'values' => 'Информация'
    ];
    static $cols = [
        'values' => ['type' => 'select', 'source' => 'relation', 'relation' => 'values'],
    ];

    static function relations()
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
