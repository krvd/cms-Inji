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
    public $limit = 10;
    public $page = 1;
    public $table = null;
    public $joins = [];
    public $predraw = false;
    public $cols = [];

    function __construct($modelName, $dataManager = 'manager', $options = []) {
        $this->modelName = $modelName;
        if (is_string($dataManager)) {
            $this->managerName = $dataManager;
            $dataManager = \App::$cur->ui->getModelManager($modelName, $dataManager);
        }
        $this->managerOptions = $dataManager;

        if (!empty($modelName::$objectName)) {
            $this->name = 'Менеджер данных: ' . $modelName::$objectName;
        } else {
            $this->name = 'Менеджер данных: ' . $modelName;
        }
    }

    /**
     * Get buttons for manager
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
        if (!empty($this->managerOptions['options']['formOnPage'])) {
            $query = [
                'item' => $modelName,
                'params' => $formParams
            ];
            $buttons[] = [
                'text' => 'Добавить элемент',
                'href' => '/admin/ui/formPopUp/?' . http_build_query($query),
            ];
        } else {
            $buttons[] = [
                'text' => 'Добавить элемент',
                'onclick' => 'inji.Ui.forms.popUp("' . str_replace('\\', '\\\\', $modelName) . '",' . json_encode($formParams) . ')',
            ];
        }
        return $buttons;
    }

    /**
     * Get cols for manager
     * 
     * @return string
     */
    function getCols() {
        $modelName = $this->modelName;
        $cols = [];
        if (!empty($this->managerOptions['groupActions'])) {
            $cols[] = ['label' => '<input type="checkbox" />'];
        }
        $cols['id'] = ['label' => '№', 'sortable' => true];
        foreach ($this->managerOptions['cols'] as $key => $col) {
            if (is_array($col)) {
                $colName = $key;
                $colOptions = $col;
            } else {
                $colName = $col;
                $colOptions = [];
            }
            $colInfo = $modelName::getColInfo($colName);
            if (empty($colOptions['label']) && !empty($colInfo['label'])) {
                $colOptions['label'] = $colInfo['label'];
            } elseif (empty($colOptions['label'])) {
                $colOptions['label'] = $colName;
            }
            $cols[$colName] = $colOptions;
        }
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
        if (!$this->checkAccess()) {
            $this->drawError('you not have access to "' . $this->modelName . '" manager with name: "' . $this->managerName . '"');
            return [];
        }
        $cols = $this->getCols();
        $modelName = $this->modelName;
        $queryParams = [];
        if (empty($params['all'])) {
            if (!empty($params['limit'])) {
                $this->limit = (int) $params['limit'];
            }
            if (!empty($params['page'])) {
                $this->page = (int) $params['page'];
            }
            $queryParams['limit'] = $this->limit;
            $queryParams['start'] = $this->page * $this->limit - $this->limit;
        }
        if (!empty($params['categoryPath']) && $modelName::$categoryModel) {
            $queryParams['where'][] = ['tree_path', $params['categoryPath'] . '%', 'LIKE'];
        }
        if (!empty($params['appType'])) {
            $queryParams['appType'] = $params['appType'];
        }
        if ($this->joins) {
            $queryParams['joins'] = $this->joins;
        }
        if (!empty($this->managerOptions['userGroupFilter'][\Users\User::$cur->group_id]['getRows'])) {
            foreach ($this->managerOptions['userGroupFilter'][\Users\User::$cur->group_id]['getRows'] as $colName => $colOptions) {
                if (!empty($colOptions['userCol'])) {
                    if (strpos($colOptions['userCol'], ':')) {
                        $rel = substr($colOptions['userCol'], 0, strpos($colOptions['userCol'], ':'));
                        $param = substr($colOptions['userCol'], strpos($colOptions['userCol'], ':') + 1);
                        $queryParams['where'][] = [$colName, \Users\User::$cur->$rel->$param];
                    }
                } elseif (!empty($colOptions['value'])) {
                    $queryParams['where'][] = [$colName, $colOptions['value']];
                }
            }
        }
        if (!empty($this->managerOptions['filters'])) {
            foreach ($this->managerOptions['filters'] as $col) {
                $colInfo = $modelName::getColInfo($col);
                switch ($colInfo['colParams']['type']) {
                    case 'select':
                        if (empty($params['filters'][$col]['value'])) {
                            continue;
                        }
                        $queryParams['where'][] = [$col, $params['filters'][$col]['value']];
                        break;
                    case 'bool':

                        if (empty($params['filters'][$col]['value'])) {
                            continue;
                        }
                        $queryParams['where'][] = [$col, '1'];
                        break;
                    case 'dateTime':
                    case 'date':
                        if (empty($params['filters'][$col]['min']) && empty($params['filters'][$col]['max'])) {
                            continue;
                        }
                        if (!empty($params['filters'][$col]['min'])) {
                            $queryParams['where'][] = [$col, $params['filters'][$col]['min'], '>='];
                        }
                        if (!empty($params['filters'][$col]['max'])) {
                            $queryParams['where'][] = [$col, $params['filters'][$col]['max'], '<='];
                        }
                        break;
                    case 'number':
                        if (empty($params['filters'][$col]['min']) && empty($params['filters'][$col]['max'])) {
                            continue;
                        }
                        if (!empty($params['filters'][$col]['min'])) {
                            $queryParams['where'][] = [$col, $params['filters'][$col]['min'], '>='];
                        }
                        if (!empty($params['filters'][$col]['max'])) {
                            $queryParams['where'][] = [$col, $params['filters'][$col]['max'], '<='];
                        }
                        break;
                    case'text':
                        if (empty($params['filters'][$col]['value'])) {
                            continue;
                        }
                        switch ($params['filters'][$col]['compareType']) {
                            case 'contains':
                                $queryParams['where'][] = [$col, '%' . $params['filters'][$col]['value'] . '%', 'LIKE'];
                                break;
                            case 'equals':
                                $queryParams['where'][] = [$col, $params['filters'][$col]['value']];
                                break;
                            case 'starts_with':
                                $queryParams['where'][] = [$col, $params['filters'][$col]['value'] . '%', 'LIKE'];
                                break;
                            case 'ends_with':
                                $queryParams['where'][] = [$col, '%' . $params['filters'][$col]['value'], 'LIKE'];
                                break;
                        }
                        break;
                }
            }
        }
        if (!empty($params['sortered']) && !empty($this->managerOptions['sortable'])) {
            foreach ($params['sortered'] as $key => $sortType) {
                if (!empty($this->managerOptions['cols'][$key]) && in_array($this->managerOptions['cols'][$key], $this->managerOptions['sortable'])) {
                    $colName = $this->managerOptions['cols'][$key];
                    $sortType = in_array($sortType, ['desc', 'asc']) ? $sortType : 'desc';
                    $queryParams['order'][] = [$colName, $sortType];
                }
            }
        }
        if ($model && !empty($params['relation'])) {
            $items = $model->$params['relation']($queryParams);
        } else {
            $items = $modelName::getList($queryParams);
        }
        $rows = [];
        foreach ($items as $key => $item) {
            $row = [];
            if (!empty($this->managerOptions['groupActions'])) {
                $row[] = '<input type ="checkbox" name = "pk[]" value =' . $item->pk() . '>';
            }
            $row[] = $item->pk();
            foreach ($this->managerOptions['cols'] as $key => $colName) {
                $row[] = DataManager::drawCol($item, is_array($colName) ? $key : $colName, $params);
            }
            $row[] = $this->rowButtons($item, $params);
            $rows[] = $row;
        }
        return $rows;
    }

    static function drawCol($item, $colName, $params = []) {
        $modelName = get_class($item);
        $relations = $modelName::relations();
        if (strpos($colName, ':') !== false && !empty($relations[substr($colName, 0, strpos($colName, ':'))])) {
            $rel = substr($colName, 0, strpos($colName, ':'));
            $col = substr($colName, strpos($colName, ':') + 1);
            return DataManager::drawCol($item->$rel, $col);
        }
        if (!empty($modelName::$cols[$colName]['relation'])) {
            $type = !empty($relations[$modelName::$cols[$colName]['relation']]['type']) ? $relations[$modelName::$cols[$colName]['relation']]['type'] : 'to';
            switch ($type) {
                case'many':
                    $managerParams = ['relation' => $modelName::$cols[$colName]['relation']];
                    $count = $item->{$modelName::$cols[$colName]['relation']}(array_merge($params, ['count' => 1]));
                    return "<a class = 'btn btn-xs btn-primary' onclick = 'inji.Ui.dataManagers.popUp(\"" . str_replace('\\', '\\\\', $modelName) . ":" . $item->pk() . "\"," . json_encode(array_merge($params, $managerParams)) . ")'>{$count} Элементы</a>";
                    break;
                default :
                    if ($item->{$modelName::$cols[$colName]['relation']}) {
                        return $item->{$modelName::$cols[$colName]['relation']}->name();
                    } else {
                        return $item->$colName;
                    }
            }
        } else {
            if (!empty($modelName::$cols[$colName]['type'])) {
                switch ($modelName::$cols[$colName]['type']) {
                    case'bool':
                        return $item->$colName ? 'Да' : 'Нет';
                        break;
                    case'select':
                        return !empty($modelName::$cols[$colName]['sourceArray'][$item->$colName]) ? $modelName::$cols[$colName]['sourceArray'][$item->$colName] : $item->$colName;
                        break;
                    default :
                        return $item->$colName;
                }
            } else {
                return $item->$colName;
            }
        }
    }

    function rowButtons($item, $params) {
        ob_start();
        \App::$cur->view->widget('Ui\DataManager/rowButtons', [
            'dataManager' => $this,
            'item' => $item,
            'params' => $params
        ]);
        $buttons = ob_get_contents();
        ob_end_clean();
        return $buttons;
    }

    function getPages($params = [], $model = null) {
        if (!$this->checkAccess()) {
            $this->drawError('you not have access to "' . $this->modelName . '" manager with name: "' . $this->managerName . '"');
            return [];
        }
        if (!empty($params['limit'])) {
            $this->limit = (int) $params['limit'];
        }
        if (!empty($params['page'])) {
            $this->page = (int) $params['page'];
        }
        $queryParams = [
            'count' => true
        ];
        $modelName = $this->modelName;
        if (!empty($params['categoryPath']) && $modelName::$categoryModel) {
            $queryParams['where'][] = ['tree_path', $params['categoryPath'] . '%', 'LIKE'];
        }
        if (!empty($this->managerOptions['userGroupFilter'][\Users\User::$cur->group_id]['getRows'])) {
            foreach ($this->managerOptions['userGroupFilter'][\Users\User::$cur->group_id]['getRows'] as $colName => $colOptions) {
                if (!empty($colOptions['userCol'])) {
                    if (strpos($colOptions['userCol'], ':')) {
                        $rel = substr($colOptions['userCol'], 0, strpos($colOptions['userCol'], ':'));
                        $param = substr($colOptions['userCol'], strpos($colOptions['userCol'], ':') + 1);
                        $queryParams['where'][] = [$colName, \Users\User::$cur->$rel->$param];
                    }
                }
            }
        }
        $modelName = $this->modelName;
        if (!empty($this->managerOptions['filters'])) {
            foreach ($this->managerOptions['filters'] as $col) {
                $colInfo = $modelName::getColInfo($col);
                switch ($colInfo['colParams']['type']) {
                    case 'select':
                        if (empty($params['filters'][$col]['value'])) {
                            continue;
                        }
                        $queryParams['where'][] = [$col, $params['filters'][$col]['value']];
                        break;
                    case 'bool':

                        if (empty($params['filters'][$col]['value'])) {
                            continue;
                        }
                        $queryParams['where'][] = [$col, '1'];
                        break;
                    case 'dateTime':
                    case 'date':
                        if (empty($params['filters'][$col]['min']) && empty($params['filters'][$col]['max'])) {
                            continue;
                        }
                        if (!empty($params['filters'][$col]['min'])) {
                            $queryParams['where'][] = [$col, $params['filters'][$col]['min'], '>='];
                        }
                        if (!empty($params['filters'][$col]['max'])) {
                            $queryParams['where'][] = [$col, $params['filters'][$col]['max'], '<='];
                        }
                        break;
                    case 'number':
                        if (empty($params['filters'][$col]['min']) && empty($params['filters'][$col]['max'])) {
                            continue;
                        }
                        if (!empty($params['filters'][$col]['min'])) {
                            $queryParams['where'][] = [$col, $params['filters'][$col]['min'], '>='];
                        }
                        if (!empty($params['filters'][$col]['max'])) {
                            $queryParams['where'][] = [$col, $params['filters'][$col]['max'], '<='];
                        }
                        break;
                    case'text':
                        if (empty($params['filters'][$col]['value'])) {
                            continue;
                        }
                        switch ($params['filters'][$col]['compareType']) {
                            case 'contains':
                                $queryParams['where'][] = [$col, '%' . $params['filters'][$col]['value'] . '%', 'LIKE'];
                                break;
                            case 'equals':
                                $queryParams['where'][] = [$col, $params['filters'][$col]['value']];
                                break;
                            case 'starts_with':
                                $queryParams['where'][] = [$col, $params['filters'][$col]['value'] . '%', 'LIKE'];
                                break;
                            case 'ends_with':
                                $queryParams['where'][] = [$col, '%' . $params['filters'][$col]['value'], 'LIKE'];
                                break;
                        }
                        break;
                }
            }
        }
        if ($model && !empty($params['relation'])) {
            $count = $model->$params['relation']($queryParams);
        } else {
            $count = $modelName::getCount($queryParams);
        }
        $pages = new Pages([
            'limit' => $this->limit,
            'page' => $this->page,
                ], [
            'count' => $count
        ]);
        return $pages;
    }

    function preDraw($params = [], $model = null) {
        $this->predraw = true;
        $modelName = $this->modelName;

        $buttons = $this->getButtons($params, $model);
        $cols = $this->getCols();

        $this->table = new Table();
        $this->table->name = $this->name;
        $tableCols = [];
        foreach ($cols as $colName => $colOptions) {
            $tableCols[] = !empty($colOptions['label']) ? $colOptions['label'] : $colName;
        }
        $tableCols[] = '';
        $this->table->setCols($tableCols);
        $this->table->afterHeader = '<div class="pagesContainer text-right"></div>';
        foreach ($buttons as $button) {
            $this->table->addButton($button);
        }
    }

    function draw($params = [], $model = null) {
        if (!$this->predraw) {
            $this->preDraw($params, $model);
        }
        \App::$cur->view->widget('Ui\DataManager/DataManager', [
            'dataManager' => $this,
            'model' => $model,
            'table' => $this->table,
            'params' => $params
        ]);
    }

    function drawCategorys() {
        ?>
        <ul class="nav nav-list-categorys" data-col='tree_path'>
            <?php
            $categoryModel = $this->managerOptions['categorys']['model'];
            $categorys = $categoryModel::getList();
            echo "<li>
                        <label class='nav-header'>
                            <a href='#' onclick='inji.Ui.dataManagers.get(this).switchCategory(this);return false;' data-path ='/'>/</a> 
                        </label>
                    </li>";
            foreach ($categorys as $category) {
                if ($category->parent_id == 0)
                    $this->showCategory($categorys, $category);
            }
            ?>
        </ul>
        <?php
    }

    function showCategory($categorys, $category) {
        $isset = false;
        $class = get_class($category);
        foreach ($categorys as $categoryChild) {
            if ($categoryChild->parent_id == $category->pk()) {
                if (!$isset) {
                    $isset = true;
                    echo "<li>
                            <label class='nav-toggle nav-header'>
                                <span class='nav-toggle-icon glyphicon glyphicon-chevron-right'></span> 
                                <a href='#' onclick='inji.Ui.dataManagers.get(this).switchCategory(this);return false;' data-path ='" . $category->tree_path . ($category->pk() ? $category->pk() . "/" : '') . "'> " . $category->name . "</a> 
                                    <a href = '#' onclick = 'inji.Ui.forms.popUp(\"" . str_replace('\\', '\\\\', get_class($category)) . ':' . $category->pk() . "\")' class ='glyphicon glyphicon-edit'></a>&nbsp;    
                <a onclick='inji.Ui.dataManagers.get(this).delCategory({$category->pk()});return false;' class ='glyphicon glyphicon-remove'></a>
                    </label>
                            <ul class='nav nav-list nav-left-ml'>";
                }
                $this->showCategory($categorys, $categoryChild);
            }
        }
        if ($isset) {
            echo '</ul>
                    </li>';
        } else {
            echo "<li>
                <label class='nav-header'>
                    <span  class=' nav-toggle-icon fa fa-minus'></span>&nbsp;
                    <a href='#' onclick='inji.Ui.dataManagers.get(this).switchCategory(this);return false;' data-path ='" . $category->tree_path . ($category->pk() ? $category->pk() . "/" : '') . "'> " . $category->name . "</a> 
                    <a href = '#' onclick = 'inji.Ui.forms.popUp(\"" . str_replace('\\', '\\\\', get_class($category)) . ':' . $category->pk() . "\")' class ='glyphicon glyphicon-edit'></a>&nbsp;    
                    <a onclick='inji.Ui.dataManagers.get(this).delCategory({$category->pk()});return false;' class ='glyphicon glyphicon-remove'></a>
                </label>
            </li>";
        }
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
    function checkAccess() {
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
