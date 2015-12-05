<?php

/**
 * Reward condition
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Money\Reward;

class Condition extends \Model
{
    function checkComplete($userId = 0)
    {
        $userId = $userId ? $userId : \Users\User::$cur->id;
        if (!$userId) {
            return false;
        }
        foreach ($this->items as $item) {
            if (!$item->checkComplete($userId)) {
                return false;
            }
        }
        return true;
    }

    static function relations()
    {
        return [
            'reward' => [
                'model' => 'Money\Reward',
                'col' => 'reward_id'
            ],
            'items' => [
                'type' => 'many',
                'model' => 'Money\Reward\Condition\Item',
                'col' => 'reward_condition_id'
            ]
        ];
    }

}
