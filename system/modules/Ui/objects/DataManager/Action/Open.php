<?php

/**
 * Data manager open action
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2016 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ui\DataManager\Action;

class Open extends \Ui\DataManager\Action
{
    public static $name = 'Просмотр';
    public static $groupAction = false;
    public static $rowAction = true;

    public static function rowButton($dataManager, $item, $params, $actionParams)
    {
        if (\App::$cur->name != 'admin') {
            return '';
        }
        $query = [
            'formName' => !empty($dataManager->managerOptions['editForm']) ? $dataManager->managerOptions['editForm'] : 'manager',
            'redirectUrl' => !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : str_replace('\\', '/', $modelName)
        ];
        return "<a href='/admin/" . str_replace('\\', '/view/', get_class($item)) . "/{$item->pk()}?" . http_build_query($query) . "'><i class='glyphicon glyphicon-eye-open'></i></a>";
    }

}
