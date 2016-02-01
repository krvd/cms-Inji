<?php

/**
 * Reward
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Money;

class Reward extends \Model
{
    public static $cols = [
        'name' => ['type' => 'text'],
        'active' => ['type' => 'bool'],
        'lasthaveall' => ['type' => 'bool'],
        'block' => ['type' => 'bool'],
        'block_date_expired' => ['type' => 'text'],
        'round_type' => ['type' => 'text'],
        'round_precision' => ['type' => 'number'],
        'peruser' => ['type' => 'number'],
    ];

    public function checkBlocked()
    {
        $blocked = Wallet\Block::getList(['where' => [
                        ['data', 'reward:' . $this->id],
                        [
                            ['date_expired', '0000-00-00 00:00:00'],
                            ['date_expired', date('Y-m-d H:i:s'), '>', 'OR']
                        ]
                    ]
        ]);
        $usersCompleted = [];
        foreach ($blocked as $block) {
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
                $block->wallet->diff($block->amount, $block->comment);
                $block->delete();
            }
        }
    }

    public static function relations()
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
