<?php

/**
 * Reward condition item
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Money\Reward\Condition;

class Item extends \Model
{
    function recivedCount($userId = 0)
    {
        $userId = $userId ? $userId : \Users\User::$cur->id;
        if (!$userId) {
            return 0;
        }
        $count = 0;
        foreach ($this->recives(['where' => ['user_id', $userId]]) as $recive) {
            $count += $recive->count;
        }
        return $count;
    }

    function checkComplete($userId = 0)
    {
        return $this->count <= $this->recivedCount($userId);
    }

    static function relations()
    {
        return [
            'condition' => [
                'model' => 'Money\Reward\Condition',
                'col' => 'reward_condition_id'
            ],
            'recives' => [
                'type' => 'many',
                'model' => 'Money\Reward\Condition\Item\Recive',
                'col' => 'reward_condition_item_id'
            ]
        ];
    }

}
