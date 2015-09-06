<?php
echo Html::el('div', [
    'id' => $dataManager->managerId,
    'class' => 'dataManager',
    'data-params' => $params,
    'data-modelname' => ($model ? get_class($model) : $dataManager->modelName) . ($model && $model->pk() ? ':' . $model->pk() : ''),
    'data-managername' => $dataManager->managerName,
    'data-cols' => $dataManager->cols,
    'data-options' => $dataManager->managerOptions
        ], '', true);
?>

    <?php
    $mainCol = [
        'class' => 'mainTableWrap',
        'style' => ''
    ];
    if (!empty($dataManager->managerOptions['categorys'])) {
        $mainCol['style'].='margin-left:260px;';
        ?>
        <div class ="pull-left" style = 'width:250px;'>
            <?php $this->widget('Ui\DataManager/categorys', compact('dataManager')); ?>
        </div>
        <?php
    }
    if (!empty($dataManager->managerOptions['filters'])) {
        $mainCol['style'].='margin-right:260px;';
        ?>
        <div class ="pull-right" style = 'width:250px;'>
            <?php $this->widget('Ui\DataManager/filters', compact('dataManager')); ?>
        </div>
        <?php
    }
    echo Html::el('div', $mainCol, '', true);
    $table->draw();
    ?>
    <div class="pagesContainer text-right"></div>
    <?php
    echo '</div>';
    ?>
    <div class="clearfix"></div>
<?php
echo '</div>';
