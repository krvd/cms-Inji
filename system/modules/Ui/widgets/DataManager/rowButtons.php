<?php
$modelName = $dataManager->modelName;

$formParams = [
    'dataManagerParams' => $params
];
?>
<a onclick='inji.Ui.forms.popUp("<?= addcslashes($modelName, '\\') . ":{$item->pk()}\"," . json_encode($formParams) ?>);
        return false;' class = 'btn btn-success btn-xs'><i class='glyphicon glyphicon-edit'></i></a>
<a onclick='inji.Ui.dataManagers.get(this).delRow(<?= $item->pk(); ?>);
        return false;' class = 'btn btn-danger btn-xs'><i class='glyphicon glyphicon-remove'></i></a>
