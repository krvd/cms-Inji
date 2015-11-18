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

namespace Money;

/**
 * Description of Reward
 *
 * @author inji
 */
class Reward extends \Model
{
    static function relations()
    {
        return [
            'levels' => [
                'type' => 'many',
                'model' => 'Money\Reward\Level',
                'col' => 'reward_id'
            ],
            'conditions' => [
                'type' => 'many',
                'model' => 'Money\Reward\Condition',
                'col' => 'reward_id'
            ]
        ];
    }

}
