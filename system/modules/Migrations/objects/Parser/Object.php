<?php

/**
 * Pareser object
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Migrations\Parser;

class Object extends \Object
{
    public $object;
    public $parentObject;
    public $parentParam;
    public $reader;
    public $model;

    function parse()
    {
        if (!$this->model) {
            $this->setModel();
        }
        foreach ($this->reader->readPath() as $code => $objectParam) {
            $param = $this->getParam($code);
            if ($this->model && $param->type && $param->type != 'item_key') {
                if ($param->type == 'object') {
                    $object = \Migrations\Migration\Object::get($param->value);
                    $parser = new \Migrations\Parser\Object;
                    $parser->reader = $objectParam;
                    $parser->object = $object;
                    $parser->parentObject = $this;
                    $parser->parse();
                } else {
                    if ($param->type == 'custom') {
                        $parserName = $param->value;
                    } else {
                        $parserName = '\Migrations\Parser\Object\\' . ucfirst($param->type);
                    }
                    $parser = new $parserName;
                    $parser->reader = $objectParam;
                    $parser->param = $param;
                    $parser->model = $this->model;
                    $parser->object = $this;
                    $parser->parse();
                }
            }
        }
        if ($this->model) {
            $this->model->save();
        }
    }

    function setModel()
    {
        $keyCol = null;
        $uniques = [];

        foreach ($this->object->params as $param) {
            $options = $param->options ? json_decode($param->options, true) : [];
            if ($param->type == 'item_key') {
                $keyCol = $param->code;
                break;
            } elseif (!empty($options['unique'])) {
                $uniques[$param->code] = $param;
            }
        }
        $objectId = null;
        if ($keyCol && $this->reader->__isset($keyCol)) {
            $objectId = \Migrations\Id::get([['parse_id', (string) $this->reader->$keyCol], ['type', $this->object->model]]);
            if ($objectId) {
                $modelName = $this->object->model;
                $this->model = $modelName::get($objectId->object_id);
            } else {
                $this->model = new $this->object->model;
                $this->model->save(['empty' => true]);
                $objectId = new \Migrations\Id();
                $objectId->object_id = $this->model->id;
                $objectId->parse_id = (string) $this->reader->$keyCol;
                $objectId->type = $this->object->model;
                $objectId->save();
            }
        } elseif ($uniques) {
            $where = [];
            foreach ($uniques as $code => $param) {
                if (!$this->reader->__isset($code)) {
                    return;
                }
                switch ($param->type) {
                    case 'objectLink':
                        $object = \Migrations\Migration\Object::get($param->value);
                        $objectId = \Migrations\Id::get([['parse_id', (string) $this->reader->$code], ['type', $object->model]]);
                        if (!$objectId) {
                            return;
                        }
                        $modelName = $object->model;
                        $model = $modelName::get($objectId->object_id);
                        $where[] = [$model->index(), $model->pk()];
                        break;
                    case 'relation':
                        $modelName = $this->object->model;
                        $relation = $modelName::getRelation($param->value);
                        $objectId = \Migrations\Id::get([['parse_id', (string) $this->reader->$code], ['type', $relation['model']]]);
                        if (!$objectId) {
                            return;
                        }
                        $modelName = $relation['model'];
                        $model = $modelName::get($objectId->object_id);
                        $where[] = [$relation['col'], $model->pk()];
                        break;
                }
            }
            if ($where) {
                if ($this->parentParam) {
                    $modelName = get_class($this->parentObject->model);
                    $relation = $modelName::getRelation($this->parentParam->param->value);
                    if (!empty($relation['type']) && $relation['type'] == 'many') {
                        $where[] = [$relation['col'], $this->parentObject->model->pk()];
                    }
                } elseif ($this->parentObject) {
                    $modelName = get_class($this->parentObject->model);
                    $where[] = [$modelName::index(), $this->parentObject->model->pk()];
                }
            }
            if ($where) {
                $modelName = $this->object->model;
                $this->model = $modelName::get($where);
                if (!$this->model) {
                    $this->model = new $this->object->model;
                    foreach ($where as $item) {
                        $this->model->{$item[0]} = $item[1];
                    }
                }
            }
        }
    }

    function getParam($code)
    {
        $param = \Migrations\Migration\Object\Param::get([
                    ['object_id', $this->object->id],
                    ['code', $code]
        ]);
        if (!$param) {
            $param = new \Migrations\Migration\Object\Param();
            $param->object_id = $this->object->id;
            $param->code = $code;
            $param->save();
        }
        return $param;
    }

}
