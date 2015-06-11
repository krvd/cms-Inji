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
    public $managerOptions = [];
    public $managerName = 'noNameManager';
    public $name = 'Менеджер данных';

    function __construct($modelName, $dataManager = 'manager') {
        $this->modelName = $modelName;
        if (is_string($dataManager)) {
            $this->managerName = $dataManager;
            $dataManager = \App::$cur->ui->getModelManager($modelName,$dataManager);
        }
        $this->managerOptions = $dataManager;
        
        if (!empty($modelName::$objectName)) {
            $this->name = 'Менеджер данных: ' . $modelName::$objectName;
        } else {
            $this->name = 'Менеджер данных: ' . $modelName;
        }
    }

    /**
     * Get buttons for monager
     * 
     * @param string $params
     * @param object $model
     */
    function getButtons($params = [], $model = null) {
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
     * @return string
     */
    function getCols() {
        $modelName = $this->modelName;
        $cols = $this->managerOptions['cols'];
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
     * @param string $params
     * @param object $model
     * @return type
     */
    function getRows($params = [], $model = null) {
        if (!$this->chackAccess()) {
            $this->drawError('you not have access to "' . $this->modelName . '" manager with name: "' . $this->managerName . '"');
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
            foreach ($this->managerOptions['cols'] as $colName) {
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

    function draw($params = [], $model = null) {


        $modelName = $this->modelName;

        $buttons = $this->getButtons( $params, $model);
        $cols = $this->getCols();
        //$rows = $this->getRows($params, $model);

        $table = new Table();
        $table->name = $this->name;
        $table->class = 'table dataManager';
        $table->id = 'dataManager_' . $modelName . '_' . $this->managerName . '_' . \Tools::randomString();
        $table->attributes['data-params'] = json_encode($params);
        $table->attributes['data-modelname'] = ($model ? get_class($model) : $modelName) . ($model ? ':' . $model->pk() : '');
        $table->attributes['data-managername'] = $this->managerName;
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
     * @return boolean
     */
    function chackAccess() {
        $modelName = $this->modelName;
        if (empty($this->managerOptions)) {
            $this->drawError('"' . $this->modelName . '" manager with name: "' . $this->managerName . '" not found');
            return false;
        }

        if (!empty($this->managerOptions['options']['access']['groups']) && !in_array(\Users\User::$cur->group_id, $this->managerOptions['options']['access']['groups'])) {
            return false;
        }
        return true;
    }

}
