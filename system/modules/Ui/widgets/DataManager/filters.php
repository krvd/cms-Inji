<h3>Фильтры</h3>
<form accept-charset="UTF-8" action="" class="dataManagerFilters" method="get" onsubmit="inji.Ui.dataManagers.get(this).reload();
      return false;">
        <?php
        $form = new Ui\Form();
        foreach ($dataManager->managerOptions['filters'] as $col) {
            if ($dataManager->modelName) {
                $modelName = $dataManager->modelName;
                $colInfo = $modelName::getColInfo($col);
            } else {
                $colInfo = $dataManager->managerOptions['cols'][$col];
            }
            $values = [];
            $inputOptions = [];
            if (!empty($dataManager->managerOptions['userGroupFilter'][\Users\User::$cur->group_id]['getRows'][$col])) {
                $colOptions = $dataManager->managerOptions['userGroupFilter'][\Users\User::$cur->group_id]['getRows'][$col];
                if (!empty($colOptions['userCol'])) {
                    if (strpos($colOptions['userCol'], ':')) {
                        $rel = substr($colOptions['userCol'], 0, strpos($colOptions['userCol'], ':'));
                        $param = substr($colOptions['userCol'], strpos($colOptions['userCol'], ':') + 1);

                        $inputOptions['value'] = \Users\User::$cur->$rel->$param;
                    } else {
                        $this->model->$col = \Users\User::$cur->{$preset['userCol']};
                    }
                } elseif (!empty($colOptions['value'])) {
                    $inputOptions['value'] = $colOptions['value'];
                }
                $form->input('hidden', "datamanagerFilters[{$col}][value]", '', $inputOptions);
                continue;
            }


            if (!empty($colInfo['colParams']['type'])) {
                switch ($colInfo['colParams']['type']) {
                    case'select':
                        switch ($colInfo['colParams']['source']) {
                            case 'array':
                                $values = ['' => 'Не важно'] + $colInfo['colParams']['sourceArray'];
                                break;
                            case 'method':
                                if (!empty($colInfo['colParams']['params'])) {
                                    $values = call_user_func_array([App::$cur->$colInfo['colParams']['module'], $colInfo['colParams']['method']], $colInfo['colParams']['params']);
                                } else {
                                    $values = ['' => 'Не важно'] + App::$cur->$colInfo['colParams']['module']->$colInfo['colParams']['method']();
                                }
                                break;
                            case 'model':
                                $values = ['' => 'Не важно'] + $colInfo['colParams']['model']::getList(['forSelect' => true]);
                                break;
                            case 'relation':
                                $relations = $colInfo['modelName']::relations();
                                $filters = $relations[$colInfo['colParams']['relation']]['model']::managerFilters();
                                $cols = $relations[$colInfo['colParams']['relation']]['model']::cols();
                                $options = [
                                    'where' => !empty($filters['getRows']['where']) ? $filters['getRows']['where'] : ''
                                ];
                                if (isset($cols[$relations[$colInfo['colParams']['relation']]['model']::colPrefix() . 'name'])) {
                                    $options['order'] = 'name';
                                }
                                $items = $relations[$colInfo['colParams']['relation']]['model']::getList($options);
                                $values = ['' => 'Не задано'];
                                foreach ($items as $key => $item) {
                                    if (!empty($inputParams['showCol'])) {
                                        $values[$key] = $item->$inputParams['showCol'];
                                    } else {
                                        $values[$key] = $item->name();
                                    }
                                }
                                $values;
                                break;
                        }
                        $value = !empty($_GET['datamanagerFilters'][$col]['value']) ? $_GET['datamanagerFilters'][$col]['value'] : '';
                        $inputOptions = ['value' => $value, 'values' => $values];
                        if (!empty($dataManager->managerOptions['userGroupFilter'][\Users\User::$cur->group_id]['getRows'][$col])) {

                            $inputOptions['disabled'] = true;
                            $colOptions = $dataManager->managerOptions['userGroupFilter'][\Users\User::$cur->group_id]['getRows'][$col];
                            if (!empty($colOptions['userCol'])) {
                                if (strpos($colOptions['userCol'], ':')) {
                                    $rel = substr($colOptions['userCol'], 0, strpos($colOptions['userCol'], ':'));
                                    $param = substr($colOptions['userCol'], strpos($colOptions['userCol'], ':') + 1);

                                    $inputOptions['value'] = \Users\User::$cur->$rel->$param;
                                } else {
                                    $this->model->$col = \Users\User::$cur->{$preset['userCol']};
                                }
                            } elseif (!empty($colOptions['value'])) {

                                $inputOptions['value'] = $colOptions['value'];
                            }
                        }
                        $inputOptions['class'] = 'input-sm';
                        $form->input('select', "datamanagerFilters[{$col}][value]", $colInfo['label'], $inputOptions);
                        break;
                    case 'email':
                    case 'text':
                    case 'textarea':
                    case 'html':
                        ?>
                  <div class="form-group">
                    <label><?= $colInfo['label']; ?></label>
                    <div class="row">
                      <div class="col-xs-6">
                        <select name="datamanagerFilters[<?= $col; ?>][compareType]" class="form-control input-sm">
                          <option value="contains">Содержит</option>
                          <option value="equals">=</option>
                          <option value="starts_with">Начинается с</option>
                          <option value="ends_with">Заканчивается</option>
                        </select>
                      </div>
                      <div class="col-xs-6">
                        <input  class="form-control input-sm" name="datamanagerFilters[<?= $col; ?>][value]" type="text">
                      </div>
                    </div>
                  </div>
                  <?php
                  break;
              case 'bool':
                  ?>
                  <div class="filter_form_field filter_select">
                    <?php
                    if (!empty($_GET['datamanagerFilters'][$col]['value'])) {
                        $value = 1;
                    } elseif (isset($_GET['datamanagerFilters'][$col]['value'])) {
                        $value = 0;
                    } else {
                        $value = '';
                    }
                    $inputOptions = ['value' => $value, 'values' => [
                            '' => 'Не важно',
                            '1' => $colInfo['label'],
                            '0' => 'Нет'
                        ]
                    ];
                    if (!empty($dataManager->managerOptions['userGroupFilter'][\Users\User::$cur->group_id]['getRows'][$col])) {

                        $inputOptions['disabled'] = true;
                        $colOptions = $dataManager->managerOptions['userGroupFilter'][\Users\User::$cur->group_id]['getRows'][$col];
                        if (!empty($colOptions['userCol'])) {
                            if (strpos($colOptions['userCol'], ':')) {
                                $rel = substr($colOptions['userCol'], 0, strpos($colOptions['userCol'], ':'));
                                $param = substr($colOptions['userCol'], strpos($colOptions['userCol'], ':') + 1);

                                $inputOptions['value'] = \Users\User::$cur->$rel->$param;
                            } else {
                                $this->model->$col = \Users\User::$cur->{$preset['userCol']};
                            }
                        } elseif (!empty($colOptions['value'])) {

                            $inputOptions['value'] = $colOptions['value'];
                        }
                    }
                    $inputOptions['class'] = 'input-sm';
                    $form->input('select', "datamanagerFilters[{$col}][value]", $colInfo['label'], $inputOptions);
                    ?>
                  </div>

                  <?php
                  break;
              case 'number':
                  ?>
                  <div class="form-group">
                    <label><?= $colInfo['label']; ?></label>
                    <div class="row">
                      <div class="col-xs-6">
                        <?php $form->input('number', "datamanagerFilters[{$col}][min]", 'с'); ?>
                      </div>
                      <div class="col-xs-6">
                        <?php $form->input('number', "datamanagerFilters[{$col}][max]", 'по'); ?>
                      </div>
                    </div>
                  </div>
                  <?php
                  break;
              case 'dateTime':
              case 'currentDateTime':
                  ?>
                  <div class="form-group">
                    <label><?= $colInfo['label']; ?></label>
                    <div class="row">
                      <div class="col-xs-6">
                        <?php $form->input('dateTime', "datamanagerFilters[{$col}][min]", 'с'); ?>
                      </div>
                      <div class="col-xs-6">
                        <?php $form->input('dateTime', "datamanagerFilters[{$col}][max]", 'по'); ?>
                      </div>
                    </div>
                  </div>
                  <?php
                  break;
              case 'date':
                  ?>
                  <div class="form-group">
                    <label><?= $colInfo['label']; ?></label>
                    <div class="row">
                      <div class="col-xs-6">
                        <?php $form->input('date', "datamanagerFilters[{$col}][min]", 'с'); ?>
                      </div>
                      <div class="col-xs-6">
                        <?php $form->input('date', "datamanagerFilters[{$col}][max]", 'по'); ?>
                      </div>
                    </div>
                  </div>
                  <?php
                  break;
          }
      }
  }
  ?>
  <div class="form-actions">
    <button class="btn btn-primary btn-sm">Фильтровать</button>
    <button type="reset" class="btn btn-default btn-sm">Очистить</button>
  </div>
</form>
