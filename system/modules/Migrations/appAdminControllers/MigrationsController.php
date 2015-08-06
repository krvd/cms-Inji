<?php

/**
 * Item name
 *
 * Info
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class MigrationsController extends adminController {

    function indexAction() {
        $this->view->setTitle('Миграции данных');
        $this->view->page();
    }

    function manualAction() {
        if (!empty($_POST)) {
            $this->module->startMigration($_POST['migration'], $_POST['map'], $_FILES['file']['tmp_name']);
        }
        $selectArray = [
            '' => 'Выберите'
        ];
        $migrations = Migrations\Migration::getList();
        foreach ($migrations as $migration) {
            $item = [
                'text' => $migration->name,
                'input' => [
                    'name' => 'map',
                    'type' => 'select',
                    'source' => 'array',
                    'sourceArray' => ['' => 'Выберите']
                ]
            ];
            foreach ($migration->maps as $map) {
                $item['input']['sourceArray'][$map->id] = [
                    'text' => $map->name,
                    'input' => [
                        'type' => 'file',
                        'name' => 'file',
                        'noprefix' => true
                    ]
                ];
            }
            $selectArray[$migration->id] = $item;
        }
        $this->view->setTitle('Ручная миграции данных');
        $this->view->page(['data' => compact('selectArray')]);
    }

    function delayedAction() {
        if (!empty($_POST['type'])) {
            foreach ($_POST['type'] as $pathId => $objectType) {
                $mapPath = Migrations\Migration\Map\Path::get($pathId);
                if (is_numeric($objectType)) {
                    $mapPath->object_id = $objectType;
                } else {
                    if ($objectType == 'object') {
                        $object = new Migrations\Migration\Object();
                        $object->model = !empty($_POST['typeOptions'][$pathId]) ? $_POST['typeOptions'][$pathId] : '';
                        $object->migration_id = $mapPath->map->migration_id;
                        $object->code = $object->name = $mapPath->item;
                        $object->save();
                        $mapPath->type = 'object';
                        $mapPath->object_id = $object->id;
                    } else {
                        $mapPath->type = $objectType;
                    }
                }

                $mapPath->save();
            }
        }
        if (!empty($_POST['param'])) {
            foreach ($_POST['param'] as $paramId => $type) {
                $param = \Migrations\Migration\Object\Param::get($paramId);

                if ($type == 'newObject') {
                    $object = new Migrations\Migration\Object();
                    $object->model = !empty($_POST['paramOptions'][$paramId]) ? $_POST['paramOptions'][$paramId] : '';
                    $object->migration_id = $param->object->migration_id;
                    $object->code = $object->name = $param->code;
                    $object->save();
                    $param->type = 'object';
                    $param->value = $object->id;
                } else {
                    $param->type = $type;
                    $param->value = !empty($_POST['paramOptions'][$paramId]) ? $_POST['paramOptions'][$paramId] : '';
                }
                $param->save();
            }
        }
        $models = $this->modules->getSelectListModels();
        $delayed = \Migrations\Migration\Map\Path::getList(['where' => ['type', '']]);
        $delayedParams = \Migrations\Migration\Object\Param::getList(['where' => ['type', '']]);
        $this->view->setTitle('Уточнение распознавания');
        $this->view->page(['data' => compact('delayed', 'models', 'delayedParams')]);
    }

}
