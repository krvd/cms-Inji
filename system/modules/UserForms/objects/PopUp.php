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
namespace UserForms;
/**
 * Description of PopUp
 *
 * @author inji
 */
class PopUp
{
    static function onClick($userFormId)
    {
        $userForm = Form::get($userFormId);
        return "popUpForm({$userForm->id},'{$userForm->name}');";
    }

}
