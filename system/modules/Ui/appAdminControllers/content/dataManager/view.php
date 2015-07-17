<?php
$modelName = get_class($item);
$table = new Ui\Table();
$row = [];
foreach ($modelName::$cols as $colName => $options) {
    $modelName = get_class($item);
    $colInfo = $modelName::getColInfo($colName);
    $type = !empty($colInfo['colParams']['type']) ? $colInfo['colParams']['type'] : 'string';
    switch ($type) {
        case'select':
            switch ($colInfo['colParams']['source']) {
                case 'array':
                    $value = !empty($colInfo['colParams']['sourceArray'][$item->$colName]) ? $colInfo['colParams']['sourceArray'][$item->$colName] : 'Не задано';
                    break;
                case 'method':
                    $values = $colInfo['colParams']['module']->$colInfo['colParams']['method']();
                    $value = !empty($values[$item->$colName]) ? $values[$item->$colName] : 'Не задано';
                    break;
                case 'relation':
                    $relations = $colInfo['modelName']::relations();
                    $relValue = $relations[$colInfo['colParams']['relation']]['model']::get($item->$colName);
                    $value = $relValue ? "<a href='/admin/" . str_replace('\\', '/view/', $relations[$colInfo['colParams']['relation']]['model']) . "/" . $relValue->pk() . "'>" . $relValue->name() . "</a>" : 'Не задано';
                    break;
            }
            break;
        case 'image':
            $file = Files\File::get($item->$colName);
            if ($file) {
                $value = '<img src="' . $file->path . '?resize=60x120" />';
            } else {
                $value = '<img src="/static/system/images/no-image.png?resize=60x120" />';
            }
            break;
        case 'bool':
            $value = $item->$colName ? 'Да' : 'Нет';
            break;
        default:
            $value = $item->$colName;
            break;
    }
    $table->addRow([
        !empty($modelName::$labels[$colName]) ? $modelName::$labels[$colName] : $colName,
        $value
    ]);
}
$table->draw();
?>
<div>
    <h3>Комментарии (<?=
        \Dashboard\Comment::getCount(['where' => [
                ['item_id', $item->id],
                ['model', $modelName],
        ]]);
        ?>)</h3>
    <?php
    foreach (\Dashboard\Comment::getList([ 'where' => [
            ['item_id', $item->id],
            ['model', $modelName],
        ], 'order' => ['date', 'desc']]) as $comment) {
        ?>
        <div class="row">
            <div class="col-sm-3" style="max-width: 300px;">
                <a href='/admin/Users/view/User/<?= $comment->user->pk(); ?>'><?= $comment->user->name(); ?></a><br />
                <?= $comment->date; ?>
            </div>
            <div class="col-sm-9">
                <?= $comment->text; ?>
            </div>
        </div>
        <?php
    }
    ?>
</div>
<div>
    <?php
    $form = new \Ui\Form();
    $form->begin();
    $form->input('textarea', 'comment', 'Комментарий');
    $form->end();
    ?>
</div>
