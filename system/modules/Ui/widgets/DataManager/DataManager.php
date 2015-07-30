<?php
echo Html::el('div', [
    'id' => $dataManager->managerId,
    'class' => 'dataManager',
    'data-params' => json_encode($params),
    'data-modelname' => ($model ? get_class($model) : $dataManager->modelName) . ($model && $model->pk() ? ':' . $model->pk() : ''),
    'data-managername' => $dataManager->managerName,
    'data-cols' => json_encode($dataManager->cols),
    'data-options' => json_encode($dataManager->managerOptions)
        ], '', true);
if (!empty($dataManager->managerOptions['categorys'])) {
    ?>
    <div class ="col-lg-2" style = 'overflow-x: auto;max-height:400px;'>
        <h3>Категории
            <div class="pull-right">
                <a class ='btn btn-xs btn-primary' onclick='<?= 'inji.Ui.forms.popUp("' . str_replace('\\', '\\\\', $dataManager->managerOptions['categorys']['model']) . '");'; ?>'>Создать</a>
            </div>
        </h3>
        <div class="categoryTree">
            <?php
            $dataManager->drawCategorys();
            ?>
        </div>
    </div>
    <div class ="col-lg-10">
        <?php
        $table->draw();
        ?>
        <div class="pagesContainer text-right"></div>
    </div>
    <div class="clearfix"></div>
    <?php
} else {
    $table->draw();
    echo '<div class="pagesContainer text-right"></div>';
}
echo '</div>';

if (false) {
    ?>
    <?php
    if (!empty($dataManager->managerOptions['groupActions'])) {
        ?>
        <div class="table_tools">
            <div class="dropdown_menu" id="batch_actions_selector">
                <a class="dropdown_menu_button" href="#">Групповые операции</a>
                <div class="dropdown_menu_list_wrapper" style="display:none;">
                    <div class="dropdown_menu_nipple"></div>
                    <ul class=" dropdown_menu_list">
                        <?php
                        foreach ($dataManager->managerOptions['groupActions'] as $actionName => $detail) {
                            echo '<li><a href="#" class="batch_action" data-action="' . $actionName . '">' . $detail['name'] . ' выбранное</a></li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
    <div class = 'paginated_collection'>
        <div class = 'paginated_collection_contents'>
            <div class = 'index_content'>
                <div class = 'index_as_table'>
                    <?php
                    $table->class = 'index_table index';
                    $table->indexCol = 0;
                    $table->name = false;
                    $table->afterHeader = '';
                    echo '<div '
                    . 'id = "dataManager_' . $dataManager->modelName . '_' . $dataManager->managerName . '_' . \Tools::randomString() . '" '
                    . 'class = "dataManager" '
                    . 'data-params = \'' . json_encode($params) . '\' '
                    . 'data-modelname = \'' . ($model ? get_class($model) : $dataManager->modelName) . ($model && $model->pk() ? ':' . $model->pk() : '') . '\' '
                    . 'data-managername = \'' . $dataManager->managerName . '\''
                    . 'data-cols = \'' . json_encode($dataManager->cols) . '\''
                    . 'data-options = \'' . json_encode($dataManager->managerOptions) . '\''
                    . '>';

                    if (!empty($dataManager->managerOptions['filters'])) {
                        ?>
                        <div style="padding-right: 285px;float: left;min-width: 100%;">
                            <?php
                        }

                        if (!empty($dataManager->managerOptions['categorys']) && empty($dataManager->managerOptions['categorys']['noShow'])) {
                            ?>
                            <div class ="col-lg-2" style = 'overflow-x: auto;max-height:400px;'>
                                <h3>Категории
                                    <div class="pull-right">
                                        <a class ='btn btn-xs btn-primary' onclick='<?= 'inji.Ui.forms.popUp("' . str_replace('\\', '\\\\', $dataManager->managerOptions['categorys']['model']) . '");'; ?>'>Создать</a>
                                    </div>
                                </h3>
                                <div class="categoryTree">
                                    <?php
                                    $dataManager->drawCategorys();
                                    ?>
                                </div>
                            </div>
                            <div class ="col-lg-10">
                                <?php
                                $table->draw();
                                ?>
                                <div class="pagesContainer text-right"></div>
                            </div>
                            <div class="clearfix"></div>
                            <?php
                        } else {
                            $table->draw();
                            ?>
                            <div class="pull-left">
                                Скачать: <a href ="#" onclick="inji.Ui.dataManagers.get(this).load({download: true});
                                        return false;">csv</a>
                            </div>
                            <?php
                            echo '<div class="pagesContainer text-right"></div>';
                        }
                        echo '</div>';
                        if (!empty($dataManager->managerOptions['filters'])) {
                            ?>
                            <div id="sidebar">
                                <div class="sidebar_section panel" id="filters_sidebar_section">
                                    <h3>Фильтры</h3>
                                    <div class="panel_contents">
                                        <form accept-charset="UTF-8" action="" class="filter_form dataManagerFilters" method="get" onsubmit="inji.Ui.dataManagers.get(this).reload();
                                                return false;">
                                                  <?php
                                                  $form = new Ui\Form();
                                                  foreach ($dataManager->managerOptions['filters'] as $col) {
                                                      if ($modelName) {
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
                                                                          $values = array_merge(['' => 'Не задано'], $colInfo['colParams']['sourceArray']);
                                                                          break;
                                                                      case 'method':
                                                                          $values = $colInfo['colParams']['module']->$colInfo['colParams']['method']();
                                                                          break;
                                                                      case 'relation':
                                                                          $relations = $colInfo['modelName']::relations();
                                                                          $filters = $relations[$colInfo['colParams']['relation']]['model']::managerFilters();
                                                                          $items = $relations[$colInfo['colParams']['relation']]['model']::getList(['where' => !empty($filters['getRows']['where']) ? $filters['getRows']['where'] : '', 'order' => 'name']);
                                                                          $values = ['' => 'Не задано'];
                                                                          foreach ($items as $key => $item) {
                                                                              if (!empty($inputParams['showCol'])) {
                                                                                  $values[$key] = $item->$inputParams['showCol'];
                                                                              } else {
                                                                                  $values[$key] = $item->name;
                                                                              }
                                                                          }
                                                                          $values;
                                                                          break;
                                                                  }
                                                                  $value = !empty($_GET['datamanagerFilters'][$col]['value']) ? $_GET['datamanagerFilters'][$col]['value'] : '';
                                                                  ?>
                                                            <div class="filter_form_field filter_select">
                                                                <?php
                                                                $inputOptions = ['value' => $value, 'values' => $values, 'noContainer' => true];
                                                                if (!empty($dataManager->managerOptions['userGroupFilter'][\Users\User::$cur->group_id]['getRows'][$col])) {

                                                                    $inputOptions['disabled'] = true;
                                                                    $colOptions = $dataManager->managerOptions['userGroupFilter'][\Users\User::$cur->group_id]['getRows'][$col];
                                                                    if (!empty($colOptions['userCol'])) {
                                                                        if (strpos($colOptions['userCol'], ':')) {
                                                                            $rel = substr($colOptions['userCol'], 0, strpos($colOptions['userCol'], ':'));
                                                                            $param = substr($colOptions['userCol'], strpos($colOptions['userCol'], ':') + 1);

                                                                            $inputOptions['value'] = \Users\User::$cur->$rel->$param;
                                                                        }
                                                                    } elseif (!empty($colOptions['value'])) {

                                                                        $inputOptions['value'] = $colOptions['value'];
                                                                    }
                                                                }
                                                                $form->input('select', "datamanagerFilters[{$col}][value]", $colInfo['label'], $inputOptions);
                                                                ?>
                                                            </div>
                                                            <?php
                                                            break;
                                                        case 'email':
                                                        case 'text':
                                                        case 'textarea':
                                                        case 'html':
                                                            ?>
                                                            <div class="filter_form_field filter_string select_and_search">
                                                                <label class=" label" for="q_first_name"><?= $colInfo['label']; ?></label>
                                                                <select id="" name="datamanagerFilters[<?= $col; ?>][compareType]">
                                                                    <option value="contains" selected="selected">Содержит</option>
                                                                    <option value="equals">=</option>
                                                                    <option value="starts_with">Начинается с</option>
                                                                    <option value="ends_with">Заканчивается</option>
                                                                </select>
                                                                <input id="q_first_name" name="datamanagerFilters[<?= $col; ?>][value]" type="text">
                                                            </div>
                                                            <?php
                                                            break;
                                                        case 'bool':
                                                            ?>
                                                            <div class="filter_form_field filter_boolean">
                                                                <label class="" for="q_published"><?= $colInfo['label']; ?></label>
                                                                <input id="q_published" name="datamanagerFilters[<?= $col; ?>][value]" value="1" type="checkbox" <?= !empty($_GET['datamanagerFilters'][$col]['value']) ? 'checked' : ''; ?>>
                                                            </div>
                                                            <?php
                                                            break;
                                                        case 'number':
                                                            ?>
                                                            <div class="filter_form_field filter_numeric_range">
                                                                <label class=" label" for="rating_gte_numeric"><?= $colInfo['label']; ?></label>
                                                                <input id="rating_gte_numeric" name="datamanagerFilters[<?= $col; ?>][min]" size="10" type="text">
                                                                <span class="seperator">-</span>
                                                                <input id="rating_lte_numeric" name="datamanagerFilters[<?= $col; ?>][max]" size="10" type="text">
                                                            </div>
                                                            <?php
                                                            break;
                                                        case 'dateTime':
                                                        case 'date':
                                                        case 'currentDateTime':
                                                            ?>
                                                            <div class="filter_form_field filter_date_range">
                                                                <label class=" label" for="q_created_at_gte"><?= $colInfo['label']; ?></label>
                                                                <?php $form->input('date', "datamanagerFilters[{$col}][min]", false, ['noContainer' => true, 'class' => 'datepicker']); ?>
                                                                <span class="seperator">-</span>
                                                                <?php $form->input('date', "datamanagerFilters[{$col}][max]", false, ['noContainer' => true, 'class' => 'datepicker']); ?>
                                                            </div>
                                                            <?php
                                                            break;
                                                    }
                                                }
                                            }
                                            ?>
                                            <div class="buttons">
                                                <input name="commit" value="Фильтровать" type="submit"> 
                                                <a href="#" class="clear_filters_btn"
                                                   onclick="$(this).next().click();
                                                           $(this).prev().click();
                                                           return false;"
                                                   >Очистить</a>
                                                <input style="display: none;" type="reset" value="Очистить">

                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
}