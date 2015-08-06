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

namespace Migrations\Migration\Map;

class Path extends \Model {

    static function relations() {
        return [
            'map' => [
                'model' => 'Migrations\Migration\Map',
                'col' => 'map_id'
            ]
        ];
    }

}
