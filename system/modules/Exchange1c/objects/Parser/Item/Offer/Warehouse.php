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

namespace Exchange1c\Parser\Item\Offer;

class Warehouse extends \Migrations\Parser {

    function parse() {
        $id = $this->reader->data['ИдСклада'];
        $count = $this->reader->data['КоличествоНаСкладе'];
        $objectId = \Migrations\Id::get([['parse_id', (string) $this->reader->data['ИдСклада']], ['type', 'Ecommerce\Warehouse']]);
        if ($objectId) {
            $modelName = get_class($this->object->model);
            $warehouse = \Ecommerce\Item\Offer\Warehouse::get([[$modelName::index(), $this->object->model->pk()], [\Ecommerce\Warehouse::index(), $objectId->object_id]]);
            if (!$warehouse) {
                $warehouse = new \Ecommerce\Item\Offer\Warehouse([
                    $modelName::index() => $this->object->model->pk(),
                    \Ecommerce\Warehouse::index() => $objectId->object_id,
                    'count' => $count
                ]);
                
            }
            else {
                $warehouse->count = $count;
            }
            $warehouse->save();
        }
    }

}
