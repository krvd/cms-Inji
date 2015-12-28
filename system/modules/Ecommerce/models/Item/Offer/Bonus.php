<?php

/**
 * Item offer bonus
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ecommerce\Item\Offer;

class Bonus extends \Model
{
    static $cols = [
        'item_offer_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'offer'],
        'type' => ['type' => 'text'],
        'value' => ['type' => 'text'],
        'count' => ['type' => 'decinal'],
        'limited' => ['type' => 'bool'],
        'left' => ['type' => 'number']
    ];

    static function relations()
    {
        return [
            'offer' => [
                'model' => 'Ecommerce\Item\Offer',
                'col' => 'item_offer_id'
            ]
        ];
    }


}


