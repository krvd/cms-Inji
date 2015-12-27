
<?php
if ($item->pay_status_id == 1) {
    ?>
    <a onclick="inji.Server.request({
                url: '/admin/money/manualClosePay/<?= $item->id; ?>',
                success: function () {
                  inji.Ui.dataManagers.reloadAll();
                }});
              return false;
       " href ='#' class="btn btn-xs btn-primary">Оплачено</a>
    <?php
}
\App::$cur->view->widget('Ui\DataManager/rowButtons', [
    'dataManager' => $dataManager,
    'item' => $item,
    'params' => $params
]);
