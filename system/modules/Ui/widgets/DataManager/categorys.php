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