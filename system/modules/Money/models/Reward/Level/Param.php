<?php

/**
 * Reward level
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Money\Reward\Level;

class Param extends \Model
{
    public static $cols = [
        'reward_level_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'level'],
        'param' => ['type' => 'text'],
        'value' => ['type' => 'text'],
    ];

    public static function relations()
    {
        return [
            'level' => [
                'model' => 'Money\Reward\Level',
                'col' => 'reward_level_id'
            ],
        ];
    }

}
