<?php

$form = new Ui\Form();
$form->begin('Не распознанные блоки');
foreach ($delayed as $delay) {
    $form->input('select', 'type[' . $delay->id . ']', $delay->path . $delay->item, ['values' => [
            '' => 'Выберите',
            'continue' => 'Пропустить',
            'container' => 'Контейнер',
            'object' => [
                'text' => 'Объект',
                'input' => [
                    'name' => 'typeOptions[' . $delay->id . ']',
                    'type' => 'select',
                    'source' => 'array',
                    'sourceArray' => $models
                ]
            ]
    ]]);
}
echo "<h2>Параметры</h2>";
$selectArrays = [];
$objectsCols = [];
foreach ($delayedParams as $delayedParam) {
    if (!isset($selectArrays[$delayedParam->object->migration_id])) {
        $selectArrays[$delayedParam->object->migration_id] = Migrations\Migration\Object::getList(['where' => ['migration_id', $delayedParam->object->migration_id], 'forSelect' => true]);

        $selectArrays[$delayedParam->object->migration_id] = [
            '' => 'Выберите',
            'continue' => 'Пропустить',
            'container' => 'Контейнер'
                ] + $selectArrays[$delayedParam->object->migration_id];
    }

    if (empty($objectsCols[$delayedParam->object_id])) {
        $modelName = $delayedParam->object->model;
        foreach (array_keys($modelName::$cols) as $colName) {
            $objectsCols[$delayedParam->object_id][$colName] = $modelName::$labels[$colName];
        }
    }
    $modelName = $delayedParam->object->model;
    $relations = [];
    foreach ($modelName::relations() as $relName => $relation) {
        $relations[$relName] = $relName;
    }
    $form->input('select', 'param[' . $delayedParam->id . ']', $delayedParam->object->name . '->' . $delayedParam->code, ['values' => [
            '' => 'Выберите',
            'continue' => 'Пропустить',
            'item_key' => 'Ключ элемента',
            'value' => [
                'text' => 'Значение',
                'input' => [
                    'name' => 'paramOptions[' . $delayedParam->id . ']',
                    'type' => 'select',
                    'source' => 'array',
                    'sourceArray' => $objectsCols[$delayedParam->object_id]
                ]
            ],
            'relation' => [
                'text' => 'Зависимость',
                'input' => [
                    'name' => 'paramOptions[' . $delayedParam->id . ']',
                    'type' => 'select',
                    'source' => 'array',
                    'sourceArray' => $relations
                ]
            ],
            'object' => [
                'text' => 'Объект',
                'input' => [
                    'name' => 'paramOptions[' . $delayedParam->id . ']',
                    'type' => 'select',
                    'source' => 'array',
                    'sourceArray' => $selectArrays[$delayedParam->object->migration_id]
                ]
            ],
            'objectLink' => [
                'text' => 'Ссылка на объект',
                'input' => [
                    'name' => 'paramOptions[' . $delayedParam->id . ']',
                    'type' => 'select',
                    'source' => 'array',
                    'sourceArray' => $selectArrays[$delayedParam->object->migration_id]
                ]
            ],
            'newObject' => [
                'text' => 'Новый объект',
                'input' => [
                    'name' => 'paramOptions[' . $delayedParam->id . ']',
                    'type' => 'select',
                    'source' => 'array',
                    'sourceArray' => $models
                ]
            ]
    ]]);
}
$form->end();
