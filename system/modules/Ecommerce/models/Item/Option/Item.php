<?php

/**
 * Item option item
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ecommerce\Item\Option;

class Item extends \Model
{
    public static $objectName = 'Элемент коллекции опции';
    public static $cols = [
        'value' => ['type' => 'text']
    ];
    public static $labels = [
        'value' => 'Значение'
    ];

}
