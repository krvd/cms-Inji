<?php
$modelName = $dataManager->modelName;
if ($item) {
    $modelName = get_class($item);
}
$formParams = [
    'dataManagerParams' => $params,
];
$buttons = [
    'open', 'edit', 'delete'
];
if (isset($dataManager->managerOptions['rowButtons'])) {
    $buttons = $dataManager->managerOptions['rowButtons'];
}
foreach ($buttons as $button) {
    if (is_string($button)) {
        switch ($button) {
            case 'open';
                $query = [
                    'formName' => !empty($dataManager->managerOptions['editForm']) ? $dataManager->managerOptions['editForm'] : 'manager',
                    'redirectUrl' => !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : str_replace('\\', '/', $modelName)
                ];
                echo "<a href='/admin/" . str_replace('\\', '/view/', $modelName) . "/{$item->pk()}?" . http_build_query($query) . "'><i class='glyphicon glyphicon-eye-open'></i></a>&nbsp;";
                break;
            case 'edit':
                if (!empty($dataManager->managerOptions['options']['formOnPage'])) {
                    $query = [
                        'item' => $modelName . ':' . $item->pk(),
                        'params' => $formParams,
                        'formName' => !empty($dataManager->managerOptions['editForm']) ? $dataManager->managerOptions['editForm'] : 'manager',
                        'redirectUrl' => !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : str_replace('\\', '/', $modelName)
                    ];
                    echo "<a href='/admin/ui/formPopUp/?" . http_build_query($query) . "'><i class='glyphicon glyphicon-edit'></i></a>";
                } else {
                    ?>
                    <a href ="#" onclick='inji.Ui.forms.popUp("<?= addcslashes($modelName, '\\') . ":{$item->pk()}\"," . json_encode($formParams) ?>);
                                                      return false;'><i class='glyphicon glyphicon-edit'></i></a>
                       <?php
                   }

                   break;
               case 'delete':
                   ?>
                <a href ="#" onclick='inji.Ui.dataManagers.get(this).delRow(<?= $item->pk(); ?>);
                                      return false;'><i class='glyphicon glyphicon-remove'></i></a>
                <?php
                break;
        }
    } else {
        $query = [
            'item_pk' => $item->pk(),
            'time' => time()
        ];
        if (!empty($button['query'])) {
            $query = $query + $button['query'];
        }
        echo "<a class='" . (isset($button['class']) ? $button['class'] : '') . "' href='{$button['href']}?" . http_build_query($query) . "'>{$button['text']}</a>&nbsp;";
    }
}
