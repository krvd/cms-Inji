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
    function checkBlocked()
    {
        $blocked = Wallet\Block::getList(['where' => ['data', 'reward:' . $this->id]]);
        $usersCompleted = [];
        foreach ($blocked as $block) {
            if ($block->date_expired != '0000-00-00 00:00:00' && \DateTime::createFromFormat('Y-m-d H:i:s', $block->date_expired) <= new \DateTime()) {
                if ($block->expired_type == 'burn') {
                    $block->delete();
                }
                continue;
            }
            if (!isset($usersCompleted[$block->wallet->user_id])) {
                $complete = true;
                foreach ($this->conditions as $condition) {
                    if (!$condition->checkComplete($block->wallet->user_id)) {
                        $complete = false;
                        break;
                    }
                }
                if ($complete) {
                    $usersCompleted[$block->wallet->user_id] = true;
                } else {
                    $usersCompleted[$block->wallet->user_id] = false;
                }
            }
            if ($usersCompleted[$block->wallet->user_id]) {
                $block->wallet->amount += $block->amount;
                $block->wallet->save();
                $block->delete();
            }
        }
    }

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
