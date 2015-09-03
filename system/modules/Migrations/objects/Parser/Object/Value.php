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

class Value extends \Migrations\Parser {

    function parse() {
        $options = $this->param->options ? json_decode($this->param->options, true) : [];
        $modelName = get_class($this->model);
        $cols = $modelName::$cols;
        if (get_class($this->model) == 'Ecommerce\Item\Param') {
            //var_dump($cols);
            //exit();
        }
        $value = (string) $this->reader;
        if (!empty($cols[$this->param->value])) {
            $col = $cols[$this->param->value];
            if ($col['type'] == 'dynamicType') {
                switch ($col['typeSource']) {
                    case'selfMethod':
                        $type = $this->model->{$col['selfMethod']}();
                        if (is_array($type)) {
                            if (strpos($type['relation'], ':')) {
                                $relationPath = explode(':', $type['relation']);
                                $relationName = array_pop($relationPath);
                                $item = $this->model;
                                foreach ($relationPath as $path) {
                                    $item = $item->$path;
                                }
                                $itemModel = get_class($item);
                                $relation = $itemModel::getRelation($relationName);
                                $sourceModel = $relation['model'];
                            } else {
                                $relation = $modelName::getRelation($type['relation']);
                                $sourceModel = $relation['model'];
                            }
                            $objectId = \Migrations\Id::get([['parse_id', (string) $this->reader], ['type', $sourceModel]]);
                            if ($objectId) {
                                $value = $objectId->object_id;
                            }
                        }
                        break;
                }
            } else {
                $type = $col['type'];
            }
        } else {
            $type = 'text';
        }

        if (!empty($options['valueReplace'])) {
            $values = $this->param->values(['key' => 'original']);
            if (empty($values[$value])) {
                $valueObject = new \Migrations\Migration\Object\Param\Value();
                $valueObject->param_id = $this->param->id;
                $valueObject->original = $value;
                $valueObject->save();
                $value = '';
            } else {
                $valueObject = $values[$value];
            }
            $value = $valueObject->replace;
        }
        switch ($type) {
            case 'image':
                $dir = pathinfo($this->reader->source, PATHINFO_DIRNAME);
                $this->model->{$this->param->value} = \App::$primary->files->uploadFromUrl($dir . '/' . $value, ['accept_group' => 'image', 'upload_code' => 'MigrationUpload']);
                break;
            default:
                $this->model->{$this->param->value} = $value;
        }
    }

}
