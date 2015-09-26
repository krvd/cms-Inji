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

namespace Ecommerce\Item\Option;

class Item extends \Model
{
    static $objectName = 'Элемент коллекции опции';
    static $cols = [
        'value' => ['type' => 'text']
    ];
    static $labels = [
        'value' => 'Значение'
    ];

}
