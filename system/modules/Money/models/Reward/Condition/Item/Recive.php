<?php

/**
 * Reward condition item recive
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Money\Reward\Condition\Item;

class Recive extends \Model
{

    static function relations()
    {
        return [
            'item' => [
                'model' => 'Money\Reward\Condition\Item',
                'col' => 'reward_condition_item_id'
            ]
        ];
    }

}
