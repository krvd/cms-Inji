
<?php
if ($item->cart_status_id != 5 && !App::$cur->Exchange1c) {
    ?>
    <a onclick="inji.Server.request({
            url: '/admin/ecommerce/closeCart/<?= $item->id; ?>',
            success: function () {
              inji.Ui.dataManagers.reloadAll();
            }});
          return false;
       " href ='#' class="btn btn-xs btn-primary">Завершить</a>
    <?php
}
\App::$cur->view->widget('Ui\DataManager/rowButtons', [
    'dataManager' => $dataManager,
    'item' => $item,
    'params' => $params
]);
