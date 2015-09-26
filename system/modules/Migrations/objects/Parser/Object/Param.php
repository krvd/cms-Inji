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

class Param extends \Migrations\Parser
{
    function parse()
    {
        $objectParamValue = [
            'col' => '',
            'value' => ''
        ];
        foreach ($this->reader->readPath() as $code => $objectParam) {
            $param = $this->getParam($code);
            if ($this->model && $param->type) {
                switch ($param->type) {
                    case 'paramName':
                        $col = \Migrations\Migration\Object\Param\Value::get([['original', (string) $objectParam], ['param_id', $param->id]]);
                        if (!$col) {
                            $valueObject = new \Migrations\Migration\Object\Param\Value();
                            $valueObject->param_id = $param->id;
                            $valueObject->original = (string) $objectParam;
                            $valueObject->save();
                        } else {
                            $objectParamValue['col'] = $col->replace;
                        }
                        break;
                    case 'paramValue':
                        if ($objectParamValue['col']) {
                            $modelName = get_class($this->model);
                            $col = $modelName::$cols[$objectParamValue['col']];
                            if ($col['type'] == 'select' && $col['source'] == 'relation') {
                                $relation = $modelName::getRelation($col['relation']);
                                $item = $relation['model']::get((string) $objectParam, 'name');
                                if (!$item) {
                                    $item = new $relation['model'];
                                    $item->name = (string) $objectParam;
                                    $item->save();
                                }
                                $objectParamValue['value'] = $item->id;
                            }
                        }
                        break;
                }
            }
        }
        if ($objectParamValue['col']) {
            $this->model->{$objectParamValue['col']} = $objectParamValue['value'];
        }
    }

    function getParam($code)
    {
        $param = \Migrations\Migration\Object\Param::get([
                    ['parent_id', $this->param->id],
                    ['object_id', $this->object->object->id],
                    ['code', $code]
        ]);
        if (!$param) {
            $param = new \Migrations\Migration\Object\Param();
            $param->parent_id = $this->param->id;
            $param->object_id = $this->object->object->id;
            $param->code = $code;
            $param->save();
        }
        return $param;
    }

    function editor()
    {
        return [
            '' => 'Выберите',
            'paramName' => 'Название параметра',
            'paramValue' => 'Значение параметра',
        ];
    }

}
