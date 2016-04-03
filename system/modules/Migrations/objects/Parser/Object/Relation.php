<?php

/**
 * Parser Object Relation
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Migrations\Parser\Object;

class Relation extends \Migrations\Parser
{
    public function parse()
    {
        $options = $this->param->options ? json_decode($this->param->options, true) : [];
        $modelName = $this->object->object->model;
        $relation = $modelName::getRelation($this->param->value);
        $object = \Migrations\Migration\Object::get([
                    ['model', $relation['model']],
                    ['migration_id', $this->object->object->migration_id]
        ]);
        $newObject = (bool) $object;
        if (!$object) {
            $object = new \Migrations\Migration\Object([
                'model' => $relation['model'],
                'code' => $this->param->code,
                'name' => $this->param->code,
                'migration_id' => $this->object->object->migration_id
            ]);
            $object->save();
        }
        if (!empty($relation['type']) && $relation['type'] == 'many') {
            $ids = [];
            if ($this->data) {
                foreach ($this->data as $code => &$item) {
                    if (!\Tools::isAssoc($this->data)) {
                        foreach ($this->data as &$item) {
                            if ($newObject) {
                                $object->code = $code;
                                $object->name = $code;
                                $object->save();
                                $newObject = false;
                            }
                            $objectParser = new \Migrations\Parser\Object();
                            $objectParser->object = $object;
                            $objectParser->parentObject = $this->object;
                            $objectParser->parentModel = $this->model;
                            $objectParser->walker = $this->object->walker;
                            $objectParser->parentParam = $this;
                            $objectParser->data = &$item;
                            

                            if (!$this->model->pk()) {
                                $this->model->save();
                            }
                            $ids = array_merge($ids, $objectParser->parse([$relation['col'] => $this->model->pk()]));
                        }
                    } else {
                        if ($newObject) {
                            $object->code = $code;
                            $object->name = $code;
                            $object->save();
                            $newObject = false;
                        }
                        $objectParser = new \Migrations\Parser\Object();
                        $objectParser->object = $object;
                        $objectParser->parentObject = $this->object;
                        $objectParser->parentModel = $this->model;
                        $objectParser->walker = $this->object->walker;
                        $objectParser->parentParam = $this;
                        $objectParser->data = &$item;
                        if (!$this->model->pk()) {
                            $this->model->save();
                        }
                        $ids = array_merge($ids, $objectParser->parse([$relation['col'] => $this->model->pk()]));
                    }
                }
            }
            if (!empty($options['clearMissing'])) {
                $where = [];
                $where[] = [$relation['col'], $this->model->pk()];
                if ($ids) {
                    $where[] = ['id', implode(',', $ids), 'NOT IN'];
                }
                $modelName = $relation['model'];
                $objects = $modelName::getList(['where' => $where]);

                foreach ($objects as $object) {
                    $object->delete();
                }
            }
        } else {
            $objectParser = new \Migrations\Parser\Object();
            $objectParser->object = $object;
            $objectParser->parentObject = $this->object;
            $objectParser->parentModel = $this->model;
            $objectParser->parentParam = $this;
            $objectParser->data = &$this->data;
            $id = $objectParser->parse();
            if ($id) {
                $this->model->{$relation['col']} = $id[0];
            }
        }
    }

}
