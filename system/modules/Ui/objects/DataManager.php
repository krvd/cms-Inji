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

    /**
     * Get buttons for monager
     * 
     * @param string $dataManagerName
     * @param string $params
     * @param object $model
     */
    function getButtons($dataManagerName = 'manager', $params = [], $model = null) {
        $formModelName = $modelName = $this->modelName;
        $formParams = [
            'dataManagerParams' => $params
        ];
        if ($model) {
            $formModelName = get_class($model);
            $relations = $formModelName::relations();
            $formParams['preset'] = [$relations[$params['relation']]['col'] => $model->pk()];
        }
        $buttons = [];
        $buttons[] = [
            'text' => 'Добавить элемент',
            'onclick' => 'inji.Ui.forms.popUp("' . str_replace('\\', '\\\\', $modelName) . '",' . json_encode($formParams) . ')',
        ];
        return $buttons;
    }

    /**
     * Get cols for manager
     * 
     * @param string $dataManagerName
     * @return string
     */
    function getCols($dataManagerName = 'manager') {
        $modelName = $this->modelName;
        $cols = $modelName::$dataManagers[$dataManagerName]['cols'];
        foreach ($cols as $key => $col) {
            if (!empty($modelName::$labels[$col])) {
                $cols[$key] = $modelName::$labels[$col];
            }
        }
        $cols[] = "";
        return $cols;
    }

    /**
     * Get rows for manager
     * 
     * @param string $dataManagerName
     * @param string $params
     * @param object $model
     * @return type
     */
    function getRows($dataManagerName = 'manager', $params = [], $model = null) {
        if (!$this->chackAccess($dataManagerName)) {
            $this->drawError('you not have access to "' . $this->modelName . '" manager with name: "' . $dataManagerName . '"');
            return [];
        }
        $modelName = $this->modelName;
        if ($model && !empty($params['relation'])) {
            $items = $model->$params['relation'];
        } else {
            $items = $modelName::getList($params);
        }
        $rows = [];
        foreach ($items as $key => $item) {
            $row = [];
            foreach ($modelName::$dataManagers[$dataManagerName]['cols'] as $colName) {
                $relations = $modelName::relations();
                if (!empty($modelName::$cols[$colName]['relation']) && !empty($relations[$modelName::$cols[$colName]['relation']]['type']) && $relations[$modelName::$cols[$colName]['relation']]['type'] == 'many') {
                    switch ($relations[$modelName::$cols[$colName]['relation']]['type']) {
                        case'many':
                            $managerParams = ['relation' => $modelName::$cols[$colName]['relation']];
                            $count = $item->{$modelName::$cols[$colName]['relation']}(array_merge($params, ['count' => 1]));
                            $row[] = "<a class = 'btn btn-xs btn-primary' onclick = 'inji.Ui.dataManagers.popUp(\"" . str_replace('\\', '\\\\', $modelName) . ":" . $item->pk() . "\"," . json_encode(array_merge($params, $managerParams)) . ")'>{$count} Элементы</a>";
                            break;
                    }
                } else {
                    
                    $row[] = $item->$colName;
                }
            }
            $row[] = $this->rowButtons($item, $params);
            $rows[] = $row;
        }
        return $rows;
    }

    function rowButtons($item, $params) {
        $modelName = $this->modelName;
        $formParams = [
            'dataManagerParams' => $params
        ];
        $buttons = '';
        $buttons .= "<a onclick='inji.Ui.forms.popUp(\"" . str_replace('\\', '\\\\', $modelName) . ":{$item->pk()}\"," . json_encode($formParams) . ");return false;' class = 'btn btn-success btn-xs'><i class='glyphicon glyphicon-edit'></i></a>";
        $buttons .= " <a onclick='inji.Ui.dataManagers.get(this).delRow({$item->pk()});return false;' class = 'btn btn-danger btn-xs'><i class='glyphicon glyphicon-remove'></i></a>";
        return $buttons;
    }

    function draw($dataManagerName = 'manager', $params = [], $model = null) {
        $modelName = $this->modelName;

        $buttons = $this->getButtons($dataManagerName, $params, $model);
        $cols = $this->getCols($dataManagerName);
        $rows = $this->getRows($dataManagerName, $params, $model);

        $table = new Table();
        $table->name = $this->name;
        $table->class = 'table dataManager';
        $table->id = 'dataManager_' . $modelName . '_' . $dataManagerName . '_' . \Tools::randomString();
        $table->attributes['data-params'] = json_encode($params);
        $table->attributes['data-modelname'] = $modelName;
        $table->attributes['data-managername'] = $dataManagerName;
        $table->setCols($cols);
        foreach ($buttons as $button) {
            $table->addButton($button);
        }
        $table->draw();
    }

    /**
     * Draw error message
     * 
     * @param text $errorText
     */
    function drawError($errorText) {
        echo $errorText;
    }

    /**
     * Check access cur user to manager with name in param
     * 
     * @param text $dataManagerName
     * @return boolean
     */
    function chackAccess($dataManagerName) {
        $modelName = $this->modelName;
        if (empty($modelName::$dataManagers[$dataManagerName])) {
            $this->drawError('"' . $this->modelName . '" manager with name: "' . $dataManagerName . '" not found');
            return false;
        }
        $manager = $modelName::$dataManagers[$dataManagerName];
        
        if (!empty($manager['options']['access']['groups']) && !in_array(\Users\User::$cur->user_group_id, $manager['options']['access']['groups'])) {
            return false;
        }
        return true;
    }

}
