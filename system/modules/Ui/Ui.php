<?php

/**
 * Ui generator
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class Ui extends Module {

    function init() {
        App::$cur->view->customAsset('js', ['file' => '/static/moduleAsset/Ui/js/Ui.js', 'name' => 'Ui']);
        App::$cur->view->customAsset('css', '/static/moduleAsset/Ui/css/Ui.css');
    }

    function getModelManager($modelName, $dataManagerName = '') {
        if (!$dataManagerName) {
            $dataManagerName = 'manger';
        }
        $managers = $this->getModelManagers($modelName);
        return !empty($managers[$dataManagerName]) ? $managers[$dataManagerName] : [];
    }

    function getModelManagers($modelName) {
        return $modelName::$dataManagers;
    }
    function getModelForm($modelName, $formName = '') {
        if (!$formName) {
            $formName = 'manger';
        }
        $forms = $this->getModelForms($modelName);
        return !empty($forms[$formName]) ? $forms[$formName] : [];
    }

    function getModelForms($modelName) {
        return $modelName::$forms;
    }

}
