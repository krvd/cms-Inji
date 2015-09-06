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

namespace Migrations\Parser\Object;

class Relation extends \Migrations\Parser {

    function parse() {
        $options = $this->param->options ? json_decode($this->param->options, true) : [];
        $modelName = get_class($this->object->model);
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
            foreach ($this->reader->readPath() as $code => $item) {
                if ($newObject) {
                    $object->code = $code;
                    $object->name = $code;
                    $object->save();
                    $newObject = false;
                }
                $objectParser = new \Migrations\Parser\Object();
                $objectParser->object = $object;
                $objectParser->parentObject = $this->object;
                $objectParser->parentParam = $this;
                $objectParser->reader = $item;
                $objectParser->setModel();
                if ($objectParser->model) {
                    if(!$this->object->model->pk()){
                        $this->object->model->save();
                    }
                    $objectParser->model->{$relation['col']} = $this->object->model->pk();
                }
                $objectParser->parse();
                if ($objectParser->model && $objectParser->model->pk()) {
                    $ids[] = $objectParser->model->pk();
                }
            }
            if (!empty($options['clearMissing'])) {
                $where = [];
                $where[] = [$relation['col'], $this->object->model->pk()];
                if ($ids) {
                    $where[] = ['id', implode(',', $ids), 'NOT IN'];
                }
                $modelName = $relation['model'];
                $objects = $modelName::getList(['where' => $where]);
                foreach ($objects as $object){
                    $object->delete();
                }
                
            }
        } else {
            $objectParser = new \Migrations\Parser\Object();
            $objectParser->object = $object;
            $objectParser->parentObject = $this->object;
            $objectParser->parentParam = $this;
            $objectParser->reader = $this->reader;
            $objectParser->setModel();
            if ($objectParser->model) {
                $this->object->model->{$relation['col']} = $objectParser->model->pk();
            }
            $objectParser->parse();
        }
    }

}
