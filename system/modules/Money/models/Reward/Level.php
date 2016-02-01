<?php

/**
 * Reward level
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Money\Reward;

class Level extends \Model
{
    public static function relations()
    {
        return [
            'reward' => [
                'model' => 'Money\Reward',
                'col' => 'reward_id'
            ],
            'currency' => [
                'model' => 'Money\Currency',
                'col' => 'currency_id'
            ],
            'params' => [
                'type' => 'many',
                'model' => 'Money\Reward\Level\Param',
                'col' => 'reward_level_id',
                'resultKey' => 'param'
            ]
        ];
    }

}
