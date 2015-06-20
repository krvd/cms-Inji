<?php
echo '<div '
 . 'id = "dataManager_' . $dataManager->modelName . '_' . $dataManager->managerName . '_' . \Tools::randomString() . '" '
 . 'class = "dataManager" '
 . 'data-params = \'' . json_encode($params) . '\' '
 . 'data-modelname = \'' . ($model ? get_class($model) : $dataManager->modelName) . ($model && $model->pk() ? ':' . $model->pk() : '') . '\' '
 . 'data-managername = \'' . $dataManager->managerName . '\''
 . '>';
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
