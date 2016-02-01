<?php
/**
 * Data manager
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ui;

class DataManager extends \Object
{
    public $modelName = '';
    public $managerOptions = [];
    public $managerName = 'customManager';
    public $name = 'Менеджер данных';
    public $limit = 30;
    public $page = 1;
    public $table = null;
    public $joins = [];
    public $predraw = false;
    public $cols = [];
    public $managerId = '';

    /**
     * Construct new data manager
     * 
     * @param string $modelName
     * @param string|array $dataManager
     * @throws Exception
     */
    public function __construct($modelName, $dataManager = 'manager')
    {
        if (!class_exists($modelName)) {
            throw Exception("model {$modelName} not exists");
        }

        $this->modelName = $modelName;

        if (is_string($dataManager)) {
            $this->managerName = $dataManager;
            $dataManager = !empty($modelName::$dataManagers[$dataManager]) ? $modelName::$dataManagers[$dataManager] : [];
        }
        $this->managerOptions = $dataManager;

        if (!$this->managerOptions || !is_array($this->managerOptions)) {
            throw Exception('empty DataManager');
        }

        if (!empty($this->managerOptions['name'])) {
            $this->name = $this->managerOptions['name'];
        } elseif ($modelName && isset($modelName::$objectName)) {
            $this->name = $modelName::$objectName;
        } else {
            $this->name = $modelName;
        }

        $this->managerId = str_replace('\\', '_', 'dataManager_' . $this->modelName . '_' . $this->managerName . '_' . \Tools::randomString());
    }

    /**
     * Get buttons for manager
     * 
     * @param string $params
     * @param object $model
     */
    public function getButtons($params = [], $model = null)
    {
        $formModelName = $modelName = $this->modelName;
        $formParams = [
            'dataManagerParams' => $params
        ];
        if ($model) {
            $formModelName = get_class($model);
            $relations = $formModelName::relations();
            $type = !empty($relations[$params['relation']]['type']) ? $relations[$params['relation']]['type'] : 'to';
            switch ($type) {
                case 'relModel':
                    $formParams['preset'] = [
                        $formModelName::index() => $model->pk()
                    ];
                    break;
                default:
                    $formParams['preset'] = [
                        $relations[$params['relation']]['col'] => $model->pk()
                    ];
            }
        }

        $buttons = [];
        if (!empty($this->managerOptions['sortMode'])) {
            $buttons[] = [
                'class' => 'modeBtn',
                'data-mode' => 'sort',
                'text' => 'Сортировать',
            ];
        }
        if (!empty($this->managerOptions['filters'])) {
            $buttons[] = [
                'text' => 'Фильтры',
                'onclick' => '  var modal = $("#' . $this->managerId . '_filters");
                modal.modal("show");',
            ];
        }
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
    public function getCols()
    {
        $modelName = $this->modelName;
        if (!class_exists($modelName)) {
            return [];
        }
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
            $colInfo = [];
            if ($modelName) {
                $colInfo = $modelName::getColInfo($colName);
            }
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
    public function getRows($params = [], $model = null)
    {
        $modelName = $this->modelName;
        if (!class_exists($modelName)) {
            return [];
        }
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
                } elseif (isset($colOptions['value'])) {
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
                        foreach ($params['filters'][$col]['value'] as $key => $value) {
                            if ($value === '') {
                                unset($params['filters'][$col]['value'][$key]);
                            }
                        }
                        if (!$params['filters'][$col]['value']) {
                            continue;
                        }
                        $queryParams['where'][] = [$col, implode(',', $params['filters'][$col]['value']), 'IN'];
                        break;
                    case 'bool':

                        if (!isset($params['filters'][$col]['value']) || $params['filters'][$col]['value'] === '') {
                            continue;
                        }
                        $queryParams['where'][] = [$col, $params['filters'][$col]['value']];
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
                            if ($colInfo['colParams']['type'] == 'dateTime' && !strpos($params['filters'][$col]['max'], ' ')) {

                                $date = $params['filters'][$col]['max'] . ' 23:59:59';
                            } else {
                                $date = $params['filters'][$col]['max'];
                            }
                            $queryParams['where'][] = [$col, $date, '<='];
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
                    case 'email':
                    case 'text':
                    case 'textarea':
                    case 'html':
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
        if (!empty($params['mode']) && $params['mode'] == 'sort') {
            $queryParams['order'] = ['weight', 'asc'];
        } elseif (!empty($params['sortered']) && !empty($this->managerOptions['sortable'])) {
            foreach ($params['sortered'] as $key => $sortType) {
                $keys = array_keys($this->managerOptions['cols']);
                $colName = '';
                if (isset($keys[$key])) {
                    if (is_array($this->managerOptions['cols'][$keys[$key]])) {
                        $colName = $keys[$key];
                    } else {
                        $colName = $this->managerOptions['cols'][$keys[$key]];
                    }
                }
                if ($colName && in_array($colName, $this->managerOptions['sortable'])) {
                    $sortType = in_array($sortType, ['desc', 'asc']) ? $sortType : 'desc';
                    $queryParams['order'][] = [$colName, $sortType];
                }
            }
        }
        if ($model && !empty($params['relation'])) {
            $relation = $model::getRelation($params['relation']);
            $items = $model->$params['relation']($queryParams);
        } else {
            $relation = false;
            $items = $modelName::getList($queryParams);
        }
        $rows = [];
        foreach ($items as $item) {
            if ($relation && !empty($relation['relModel'])) {
                $item = $relation['relModel']::get([[$relation['relModel']::index(), $item->id], [$model->index(), $model->id]]);
            }
            $row = [];
            if (!empty($this->managerOptions['groupActions'])) {
                $row[] = '<input type ="checkbox" name = "pk[]" value =' . $item->pk() . '>';
            }
            $row[] = $item->pk();
            foreach ($this->managerOptions['cols'] as $key => $colName) {
                if (!empty($params['download'])) {
                    $row[] = \Model::getColValue($item, is_array($colName) ? $key : $colName, true, false);
                } else {
                    $row[] = DataManager::drawCol($item, is_array($colName) ? $key : $colName, $params, $this);
                }
            }
            $row[] = $this->rowButtons($item, $params);
            $rows[] = $row;
        }
        return $rows;
    }

    public static function drawCol($item, $colName, $params = [], $dataManager = null, $originalCol = '', $originalItem = null)
    {
        $modelName = get_class($item);
        if (!class_exists($modelName)) {
            return false;
        }

        if (!$originalCol) {
            $originalCol = $colName;
        }
        if (!$originalItem) {
            $originalItem = $item;
        }

        $relations = $modelName::relations();
        if (strpos($colName, ':') !== false && !empty($relations[substr($colName, 0, strpos($colName, ':'))])) {
            $rel = substr($colName, 0, strpos($colName, ':'));
            $col = substr($colName, strpos($colName, ':') + 1);
            if ($item->$rel) {
                return DataManager::drawCol($item->$rel, $col, $params, $dataManager, $originalCol, $originalItem);
            } else {
                return 'Не указано';
            }
        }
        if (!empty($modelName::$cols[$colName]['relation'])) {
            $type = !empty($relations[$modelName::$cols[$colName]['relation']]['type']) ? $relations[$modelName::$cols[$colName]['relation']]['type'] : 'to';
            switch ($type) {
                case 'relModel':
                    $managerParams = ['relation' => $modelName::$cols[$colName]['relation']];
                    $count = $item->{$modelName::$cols[$colName]['relation']}(array_merge($params, ['count' => 1]));
                    return "<a class = 'btn btn-xs btn-primary' onclick = 'inji.Ui.dataManagers.popUp(\"" . str_replace('\\', '\\\\', $modelName) . ":" . $item->pk() . "\"," . json_encode(array_merge($params, $managerParams)) . ")'>{$count} " . \Tools::getNumEnding($count, ['Элемент', 'Элемента', 'Элементов']) . "</a>";
                    break;
                case 'many':
                    $managerParams = ['relation' => $modelName::$cols[$colName]['relation']];
                    $count = $item->{$modelName::$cols[$colName]['relation']}(array_merge($params, ['count' => 1]));
                    return "<a class = 'btn btn-xs btn-primary' onclick = 'inji.Ui.dataManagers.popUp(\"" . str_replace('\\', '\\\\', $modelName) . ":" . $item->pk() . "\"," . json_encode(array_merge($params, $managerParams)) . ")'>{$count} " . \Tools::getNumEnding($count, ['Элемент', 'Элемента', 'Элементов']) . "</a>";
                    break;
                default :
                    if ($item->{$modelName::$cols[$colName]['relation']}) {
                        if (\App::$cur->name == 'admin') {
                            $href = "<a href ='/admin/" . str_replace('\\', '/view/', $relations[$modelName::$cols[$colName]['relation']]['model']) . "/" . $item->{$modelName::$cols[$colName]['relation']}->pk() . "'>";
                            if (!empty($modelName::$cols[$colName]['showCol'])) {
                                $href .= $item->{$modelName::$cols[$colName]['relation']}->{$modelName::$cols[$colName]['showCol']};
                            } else {

                                $href .= $item->{$modelName::$cols[$colName]['relation']}->name();
                            }
                            $href .= '</a>';
                            return $href;
                        } else {
                            return $item->{$modelName::$cols[$colName]['relation']}->name();
                        }
                    } else {
                        return $item->$colName;
                    }
            }
        } else {
            if (!empty($modelName::$cols[$colName]['view']['type'])) {
                switch ($modelName::$cols[$colName]['view']['type']) {
                    case 'widget':
                        ob_start();
                        \App::$cur->view->widget($modelName::$cols[$colName]['view']['widget'], ['item' => $item, 'colName' => $colName, 'colParams' => $modelName::$cols[$colName]]);
                        $content = ob_get_contents();
                        ob_end_clean();
                        return $content;
                        break;
                    case 'moduleMethod':
                        return \App::$cur->{$modelName::$cols[$colName]['view']['module']}->{$modelName::$cols[$colName]['view']['method']}($item, $colName, $modelName::$cols[$colName]);
                        break;
                    case'many':
                        $managerParams = ['relation' => $modelName::$cols[$colName]['relation']];
                        $count = $item->{$modelName::$cols[$colName]['relation']}(array_merge($params, ['count' => 1]));
                        return "<a class = 'btn btn-xs btn-primary' onclick = 'inji.Ui.dataManagers.popUp(\"" . str_replace('\\', '\\\\', $modelName) . ":" . $item->pk() . "\"," . json_encode(array_merge($params, $managerParams)) . ")'>{$count} " . \Tools::getNumEnding($count, ['Элемент', 'Элемента', 'Элементов']) . "</a>";
                        break;
                    default:
                        return $item->$colName;
                }
            } elseif (!empty($modelName::$cols[$colName]['type'])) {
                if (\App::$cur->name == 'admin' && $originalCol == 'name' || ( $dataManager && !empty($dataManager->managerOptions['colToView']) && $dataManager->managerOptions['colToView'] == $originalCol)) {
                    $formName = $dataManager && !empty($dataManager->managerOptions['editForm']) ? $dataManager->managerOptions['editForm'] : 'manager';
                    $redirectUrl = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/admin/' . str_replace('\\', '/', get_class($originalItem));
                    return "<a href ='/admin/" . str_replace('\\', '/view/', get_class($originalItem)) . "/{$originalItem->id}?formName={$formName}&redirectUrl={$redirectUrl}'>{$item->$colName}</a>";
                } elseif (\App::$cur->name == 'admin' && $colName == 'name') {
                    $redirectUrl = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/admin/' . str_replace('\\', '/', get_class($originalItem));
                    return "<a href ='/admin/" . str_replace('\\', '/view/', get_class($item)) . "/{$item->id}?redirectUrl={$redirectUrl}'>{$item->$colName}</a>";
                } else {
                    return \Model::resloveTypeValue($item, $colName);
                }
            } else {
                return $item->$colName;
            }
        }
    }

    public function rowButtons($item, $params)
    {
        $modelName = $this->modelName;
        if (!class_exists($modelName)) {
            return false;
        }
        ob_start();
        $widgetName = !empty($this->managerOptions['rowButtonsWidget']) ? $this->managerOptions['rowButtonsWidget'] : 'Ui\DataManager/rowButtons';
        \App::$cur->view->widget($widgetName, [
            'dataManager' => $this,
            'item' => $item,
            'params' => $params
        ]);
        $buttons = ob_get_contents();
        ob_end_clean();
        return $buttons;
    }

    public function getPages($params = [], $model = null)
    {
        $modelName = $this->modelName;
        if (!class_exists($modelName)) {
            return [];
        }
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
                } elseif (isset($colOptions['value'])) {
                    $queryParams['where'][] = [$colName, $colOptions['value']];
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
                        foreach ($params['filters'][$col]['value'] as $key => $value) {
                            if ($value === '') {
                                unset($params['filters'][$col]['value'][$key]);
                            }
                        }
                        if (!$params['filters'][$col]['value']) {
                            continue;
                        }
                        $queryParams['where'][] = [$col, implode(',', $params['filters'][$col]['value']), 'IN'];
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
                            if ($colInfo['colParams']['type'] == 'dateTime' && !strpos($params['filters'][$col]['max'], ' ')) {

                                $date = $params['filters'][$col]['max'] . ' 23:59:59';
                            } else {
                                $date = $params['filters'][$col]['max'];
                            }
                            $queryParams['where'][] = [$col, $date, '<='];
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
                    case 'email':
                    case 'text':
                    case 'textarea':
                    case 'html':
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
            'count' => $count,
            'dataManager' => $this
        ]);
        return $pages;
    }

    public function preDraw($params = [], $model = null)
    {
        $modelName = $this->modelName;
        if (!class_exists($modelName)) {
            return false;
        }
        $this->predraw = true;
        $modelName = $this->modelName;

        $cols = $this->getCols();

        $this->table = new Table();
        $tableCols = [];
        foreach ($cols as $colName => $colOptions) {
            $tableCols[] = !empty($colOptions['label']) ? $colOptions['label'] : $colName;
        }
        $tableCols[] = '';
        $this->table->setCols($tableCols);
    }

    public function draw($params = [], $model = null)
    {
        $modelName = $this->modelName;
        if (!class_exists($modelName)) {
            return false;
        }
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

    public function drawCategorys()
    {
        $modelName = $this->modelName;
        if (!class_exists($modelName)) {
            return false;
        }
        ?>
        <ul class="nav nav-list-categorys" data-col='tree_path'>
          <?php
          $categoryModel = $this->managerOptions['categorys']['model'];
          $order = [];
          if (!empty($this->managerOptions['sortMode'])) {
              $order[] = ['weight', 'asc'];
          }
          $categorys = $categoryModel::getList(['order' => $order]);
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

    public function showCategory($categorys, $category)
    {
        $isset = false;
        $class = get_class($category);
        foreach ($categorys as $categoryChild) {
            if ($categoryChild->parent_id == $category->pk()) {
                if (!$isset) {
                    $isset = true;
                    echo "<li data-id='{$category->pk()}' data-model = '" . get_class($category) . "'>
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
            echo "<li data-id='{$category->pk()}' data-model = '" . get_class($category) . "'>
                <label class='nav-header'>
                    <span  class=' nav-toggle-icon glyphicon glyphicon-minus'></span>
                    <div class ='pull-right actions'>
                        <a href = '#' onclick = 'inji.Ui.forms.popUp(\"" . str_replace('\\', '\\\\', get_class($category)) . ':' . $category->pk() . "\")' class ='glyphicon glyphicon-edit'></a>
                        <a onclick='inji.Ui.dataManagers.get(this).delCategory({$category->pk()});return false;' class ='glyphicon glyphicon-remove'></a>
                    </div>
                    <a href='#' onclick='inji.Ui.dataManagers.get(this).switchCategory(this);return false;' data-path ='" . $category->tree_path . ($category->pk() ? $category->pk() . "/" : '') . "'> " . $category->name . "</a> 
                </label>
            </li>";
        }
    }

    /**
     * Draw error message
     * 
     * @param text $errorText
     */
    public function drawError($errorText)
    {
        echo $errorText;
    }

    /**
     * Check access cur user to manager with name in param
     * 
     * @return boolean
     */
    public function checkAccess()
    {
        if ($this->managerName == 'manager' && !\Users\User::$cur->isAdmin()) {
            return false;
        }
        if (!empty($this->managerOptions['options']['access']['apps']) && !in_array(\App::$cur->name, $this->managerOptions['options']['access']['apps'])) {
            return false;
        }
        if (!empty($this->managerOptions['options']['access']['groups']) && !in_array(\Users\User::$cur->group_id, $this->managerOptions['options']['access']['groups'])) {
            return false;
        }
        return true;
    }

}
