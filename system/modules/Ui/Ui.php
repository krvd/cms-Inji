<?php

/**
 * Ui generator
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class Ui extends Module
{
    function init()
    {
        App::$cur->view->customAsset('js', ['file' => '/static/moduleAsset/Ui/js/Ui.js', 'name' => 'Ui']);
        if ((!$this->app->users || \Users\User::$cur->isAdmin()) && \App::$cur->type == 'app') {
            App::$cur->view->customAsset('js', ['file' => '/static/moduleAsset/Ui/js/fastEdit.js', 'name' => 'Ui', 'libs' => ['ckeditor']]);
        }
        App::$cur->view->customAsset('css', '/static/moduleAsset/Ui/css/Ui.css');
    }

    function getModelManager($modelName, $dataManagerName = '')
    {
        if (!$dataManagerName) {
            $dataManagerName = 'manger';
        }
        $managers = $this->getModelManagers($modelName);
        return !empty($managers[$dataManagerName]) ? $managers[$dataManagerName] : [];
    }

    function getModelManagers($modelName)
    {
        if (class_exists($modelName)) {
            return $modelName::$dataManagers;
        }
        return [];
    }

    function getModelForm($modelName, $formName = '')
    {
        if (!$formName) {
            $formName = 'manger';
        }
        $forms = $this->getModelForms($modelName);
        return !empty($forms[$formName]) ? $forms[$formName] : [];
    }

    function getModelForms($modelName)
    {
        return $modelName::$forms;
    }

}
