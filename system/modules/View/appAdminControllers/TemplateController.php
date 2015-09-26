<?php

/**
 * Template controller
 *
 * This controller help for create and edit template and template pages
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class TemplateController extends \Controller
{
    function indexAction($templateName)
    {
        $template = \View\Template::get($templateName, \App::$primary);
        $this->view->setTitle($templateName);
        $this->view->page(['content' => 'template/edit', 'data' => compact('template')]);
    }

}
