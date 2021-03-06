<?php

/**
 * Active form input dynamic list
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ui\ActiveForm\Input;

class DynamicList extends \Ui\ActiveForm\Input
{
    public function draw()
    {
        $inputName = $this->colName();
        $inputLabel = $this->colLabel();
        $inputOptions = [
            'cols' => $this->getCols(),
            'values' => $this->value(),
            'modelPk' => $this->activeForm->model->pk()
        ];
        $this->form->input('dynamicList', $inputName, $inputLabel, $inputOptions);
        return true;
    }

    public function parseRequest($request)
    {
        $modelName = $this->modelName;
        $rels = [];
        $relation = $modelName::getRelation($this->colParams['relation']);
        if (!empty($request[$this->colName]) && $this->activeForm->model->pk()) {
            switch ($relation['type']) {
                case 'relModel':
                    foreach ($request[$this->colName] as $row) {
                        $rels[$row['relItem']] = true;
                    }
                    $relModels = $relation['relModel']::getList(['where' => [$modelName::index(), $this->activeForm->model->pk()], 'key' => $relation['model']::index()]);
                    foreach ($relModels as $model) {
                        if (empty($rels[$model->{$relation['model']::index()}])) {
                            $model->delete();
                        } else {
                            unset($rels[$model->{$relation['model']::index()}]);
                        }
                    }
                    foreach ($rels as $relId => $trash) {
                        $model = new $relation['relModel']([
                            $modelName::index() => $this->activeForm->model->pk(),
                            $relation['model']::index() => $relId
                        ]);
                        $model->save();
                    }
                    break;
            }
        }
    }

    public function value()
    {
        $values = [];
        if ($this->activeForm->model) {
            $items = $this->activeForm->model->{$this->colParams['relation']}(['array' => true]);
            foreach ($items as $key => $item) {
                $values[] = ['relItem' => $key];
            }
        }

        return $values;
    }

    public function getCols()
    {
        $modelName = $this->modelName;
        $relation = $modelName::getRelation($this->colParams['relation']);
        $cols = [];
        switch ($relation['type']) {
            case 'relModel':
                $cols['relItem'] = [
                    'label' => $relation['model']::objectName(),
                    'type' => 'select',
                    'options' => [
                        'values' => $relation['model']::getList(['forSelect' => true])
                    ]
                ];
                break;
        }
        return $cols;
    }

}
