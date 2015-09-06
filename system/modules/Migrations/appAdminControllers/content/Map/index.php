<?php

$form = new Ui\Form();
$form->begin('Карта миграции');

function drawPath($path, $form, $models, $objects) {
    $form->input('select', 'type[' . $path->id . ']', $path->item, [
        'values' => [
    '' => 'Выберите',
    'continue' => 'Пропустить',
    'container' => 'Контейнер',
    'object' => [
        'text' => 'Объект',
        'input' => [
            'name' => 'typeOptions[' . $path->id . ']',
            'type' => 'select',
            'source' => 'array',
            'sourceArray' => $models
        ]
    ]
        ] + $objects,
        'value' => $path->type != 'object' ? $path->type : $path->object_id,
    ]);
    foreach ($path->childs as $path) {
        echo '<div class="col-xs-offset-1">';
        drawPath($path, $form, $models, $objects);
        echo '</div>';
    }
}

foreach ($map->paths(['where' => ['parent_id', 0]]) as $path) {
    drawPath($path, $form, $models, $objects);
}

function drawParam($param, $form, $models, $objects) {
    $selectArrays = [];
    $objectsCols = [];

    if (!isset($selectArrays[$param->object->migration_id])) {
        $selectArrays[$param->object->migration_id] = Migrations\Migration\Object::getList(['where' => ['migration_id', $param->object->migration_id], 'forSelect' => true]);

        $selectArrays[$param->object->migration_id] = [
            '' => 'Выберите',
            'continue' => 'Пропустить',
            'container' => 'Контейнер'
                ] + $selectArrays[$param->object->migration_id];
    }

    if (empty($objectsCols[$param->object_id])) {
        $modelName = $param->object->model;
        foreach (array_keys($modelName::$cols) as $colName) {
            $objectsCols[$param->object_id][$colName] = $modelName::$labels[$colName];
        }
    }
    $modelName = $param->object->model;
    $relations = [];
    foreach ($modelName::relations() as $relName => $relation) {
        $relations[$relName] = $relName;
    }
    $form->input('select', 'param[' . $param->id . ']', $param->code, ['values' => [
            '' => 'Выберите',
            'continue' => 'Пропустить',
            'item_key' => 'Ключ элемента',
            'value' => [
                'text' => 'Значение',
                'input' => [
                    'name' => 'paramOptions[' . $param->id . ']',
                    'type' => 'select',
                    'source' => 'array',
                    'sourceArray' => $objectsCols[$param->object_id],
                    'options' => [
                        'value' => $param->type == 'value' ? $param->value : ''
                    ]
                ]
            ],
            'relation' => [
                'text' => 'Зависимость',
                'input' => [
                    'name' => 'paramOptions[' . $param->id . ']',
                    'type' => 'select',
                    'source' => 'array',
                    'sourceArray' => $relations,
                    'options' => [
                        'value' => $param->type == 'relation' ? $param->value : ''
                    ]
                ]
            ],
            'object' => [
                'text' => 'Объект',
                'input' => [
                    'name' => 'paramOptions[' . $param->id . ']',
                    'type' => 'select',
                    'source' => 'array',
                    'sourceArray' => $selectArrays[$param->object->migration_id],
                    'options' => [
                        'value' => $param->type == 'object' ? $param->value : ''
                    ]
                ]
            ],
            'objectLink' => [
                'text' => 'Ссылка на объект',
                'input' => [
                    'name' => 'paramOptions[' . $param->id . ']',
                    'type' => 'select',
                    'source' => 'array',
                    'sourceArray' => $selectArrays[$param->object->migration_id],
                    'options' => [
                        'value' => $param->type == 'objectLink' ? $param->value : ''
                    ]
                ]
            ],
            'newObject' => [
                'text' => 'Новый объект',
                'input' => [
                    'name' => 'paramOptions[' . $param->id . ']',
                    'type' => 'select',
                    'source' => 'array',
                    'sourceArray' => $models,
                    'options' => [
                        'value' => $param->type == 'newObject' ? $param->value : ''
                    ]
                ]
            ]
        ],
        'value' => $param->type
    ]);
}

echo "<h2>Объекты</h2>";
foreach ($map->migration->objects as $object) {
    echo "<h4>{$object->name}</h4>";
    foreach ($object->params as $param) {
        echo '<div class="col-xs-offset-1">';
        drawParam($param, $form, $models, $objects);
        echo '</div>';
    }
}
$form->end();
