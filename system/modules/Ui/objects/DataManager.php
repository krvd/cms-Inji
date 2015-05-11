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

namespace Ui;

class DataManager extends \Object {

    public $modelName = '';
    public $name = 'Менеджер данных';

    function __construct($modelName) {
        $this->modelName = $modelName;
        if (!empty($modelName::$objectName)) {
            $this->name = 'Менеджер данных: ' . $modelName::$objectName;
        } else {
            $this->name = 'Менеджер данных: ' . $modelName;
        }
    }

    function draw($dataManagerName = 'manager', $model = null, $params = []) {
        $table = new Table();
        $table->name = $this->name;
        $formParams = [];
        $formModelName = $this->modelName;
        $modelName = $this->modelName;
        if($model){
            $formModelName = get_class($model);
            $relations = $formModelName::relations();
            $formParams['preset']=[$relations[$params['relation']]['col']=>$model->pk()];
        }
        $table->addButton([
            'text' => 'Добавить элемент',
            'onclick' => 'ui.form.popUp("' . str_replace('\\', '\\\\',$modelName) . '",'.  json_encode($formParams).')',
        ]);
        
        $dataManagerConfig = $modelName::$dataManagers['manager'];

        $cols = array_keys($dataManagerConfig['cols']);
        foreach ($cols as $key => $col) {
            if (!empty($modelName::$labels[$col])) {
                $cols[$key] = $modelName::$labels[$col];
            }
        }
        $cols[] = '';
        $table->setCols($cols);
        if ($model && !empty($params['relation'])) {
            $items = $model->$params['relation'];
        } else {
            $items = $modelName::getList();
        }
        foreach ($items as $key => $item) {
            $row = [];
            foreach ($dataManagerConfig['cols'] as $colName => $params) {
                $relations = $modelName::relations();
                if (!empty($params['relation']) && !empty($relations[$params['relation']]['type']) && $relations[$params['relation']]['type'] == 'many') {
                    switch ($relations[$params['relation']]['type']) {
                        case'many':
                            $managerParams = ['relation' => $params['relation']];
                            $count = $item->$params['relation'](['count' => 1]);
                            $row[] = "<a class = 'btn btn-xs btn-primary' onclick = 'ui.dataManager.show(\"" . str_replace('\\', '\\\\', $modelName) . ":" . $item->pk() . "\"," . json_encode($managerParams) . ")'>{$count} Элементы</a>";
                            break;
                    }
                } else {
                    $row[] = $item->$colName;
                }
            }
            $table->addRow($row);
        }
        $table->draw();
    }

}
