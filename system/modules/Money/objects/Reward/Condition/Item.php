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

namespace Money\Reward\Condition;

class Item extends \Model
{
    static function relations()
    {
        return [
            'condition' => [
                'model' => 'Money\Reward\Condition',
                'col' => 'reward_condition_id'
            ]
        ];
    }

}
