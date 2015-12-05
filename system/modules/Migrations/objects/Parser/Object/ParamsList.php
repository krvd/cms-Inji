<?php

/**
 * Parser Object ParamsList
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Migrations\Parser\Object;

class ParamsList extends \Migrations\Parser
{
    function parse()
    {
        foreach ($this->reader->readPath() as $code => $objectParam) {
            $param = $this->getParam($code);
            if ($this->model) {
                if ($param->type == 'custom') {
                    $parserName = $param->value;
                } else {
                    $parserName = '\Migrations\Parser\Object\\' . ucfirst($param->type);
                }
                $parser = new $parserName;
                $parser->reader = $objectParam;
                $parser->param = $param;
                $parser->model = $this->model;
                $parser->object = $this->object;
                $parser->parse();
            }
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
            $param->type = 'param';
            $param->save();
        }
        return $param;
    }

    function editor()
    {
        return [
            '' => 'Выберите',
            'param' => 'Параметр',
        ];
    }

}
