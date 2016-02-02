<?php

/**
 * Parser Object ObjectLink
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Migrations\Parser\Object;

class ObjectLink extends \Migrations\Parser
{
    public function parse()
    {
        $object = \Migrations\Migration\Object::get($this->param->value);
        $objectId = \Migrations\Id::get([['parse_id', (string) $this->reader], ['type', $object->model]]);
        $modelName = $object->model;
        $model = $modelName::get($objectId->object_id);
        $this->object->model->{$model->index()} = $model->pk();
    }

}
