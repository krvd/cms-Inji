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
    public $parentModel;
    public $parentParam;
    public $data;

    public function parse($preset = [])
    {
        $ids = [];
        if (!\Tools::isAssoc($this->data)) {
            foreach ($this->data as &$data) {
                $id = $this->parseData($data, $preset);
                if ($id) {
                    $ids[] = $id;
                }
            }
        } else {
            $id = $this->parseData($this->data, $preset);
            if ($id) {
                $ids[] = $id;
            }
        }
        return $ids;
    }

    private function parseData($data, $preset)
    {
        $model = $this->setModel($data);
        if ($model) {
            foreach ($preset as $col => $value) {
                $model->{$col} = $value;
            }

            $walked = [];
            foreach ($this->object->params as $param) {
                if ($model && $param->type && $param->type != 'item_key') {
                    if ($param->type == 'object') {
                        $object = \App::$cur->migrations->getMigrationObject($param->value);
                        $parser = new \Migrations\Parser\Object;
                        $parser->data = &$data[$param->code];
                        $parser->object = $object;
                        $parser->parentObject = $this;
                        $parser->parentModel = $model;
                        $parser->walker = $this->walker;
                        $parser->parse();
                    } else {
                        if ($param->type == 'custom') {
                            $parserName = $param->value;
                        } else {
                            $parserName = '\Migrations\Parser\Object\\' . ucfirst($param->type);
                        }
                        $parser = new $parserName;
                        $parser->data = &$data[$param->code];
                        $parser->param = $param;
                        $parser->model = $model;
                        $parser->object = $this;
                        $parser->parse();
                    }
                }
                $walked[$param->code] = true;
            }
            //check unparsed params
            foreach ($data as $key => $item) {
                //skip parsed and attribtes
                if ($key == '@attributes' || !empty($walked[$key])) {
                    continue;
                }
                $param = new \Migrations\Migration\Object\Param();
                $param->object_id = $this->object->id;
                $param->code = $key;
                $param->save();
            }
            if ($model) {
                if (!$model->pk() || !empty($model->_changedParams)) {
                    $model->save();
                }
                return $model->pk();
            }
        }
        return 0;
    }

    public function setModel($data)
    {
        $model = null;
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
        if ($keyCol && isset($data[$keyCol])) {
            $objectId = \App::$cur->migrations->findObject((string) $data[$keyCol], $this->object->model);
            if ($objectId) {
                $modelName = $this->object->model;
                $model = $modelName::get($objectId->object_id);
            } else {
                $model = new $this->object->model;
                $model->save(['empty' => true]);
                $objectId = new \Migrations\Id();
                $objectId->object_id = $model->id;
                $objectId->parse_id = (string) $data[$keyCol];
                $objectId->type = $this->object->model;
                $objectId->save();
                \App::$cur->migrations->ids['objectIds'][$this->object->model][$model->id] = $objectId;
                \App::$cur->migrations->ids['parseIds'][$this->object->model][(string) $data[$keyCol]] = $objectId;
            }
        } elseif ($uniques) {
            $where = [];
            foreach ($uniques as $code => $param) {
                if (!isset($data[$code])) {
                    return;
                }
                switch ($param->type) {
                    case 'objectLink':
                        $object = \App::$cur->migrations->getMigrationObject($param->value);
                        $objectId = \App::$cur->migrations->findObject((string) $data[$code], $object->model);
                        if (!$objectId) {
                            return;
                        }
                        $modelName = $object->model;
                        $model = $modelName::get($objectId->object_id);
                        if (!$model) {
                            $objectId->delete();
                            unset(\App::$cur->migrations->ids['objectIds'][$modelName][$objectId->object_id]);
                            unset(\App::$cur->migrations->ids['parseIds'][$modelName][$objectId->parse_id]);
                            return;
                        }
                        $where[] = [$model->index(), $model->pk()];
                        break;
                    case 'relation':
                        $modelName = $this->object->model;
                        $relation = $modelName::getRelation($param->value);
                        $objectId = \App::$cur->migrations->findObject((string) $data[$code], $relation['model']);
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
                    $modelName = $this->parentObject->object->model;
                    $relation = $modelName::getRelation($this->parentParam->param->value);
                    if (!empty($relation['type']) && $relation['type'] == 'many') {
                        $where[] = [$relation['col'], $this->parentModel->pk()];
                    }
                } elseif ($this->parentObject) {
                    $modelName = $this->parentObject->object->model;
                    $where[] = [$modelName::index(), $this->parentModel->pk()];
                }
            }
            if ($where) {
                $modelName = $this->object->model;
                $model = $modelName::get($where);
                if (!$model) {
                    $model = new $this->object->model;
                    foreach ($where as $item) {
                        $model->{$item[0]} = $item[1];
                    }
                }
            }
        }
        return $model;
    }

}
