<?php

class ViewController extends Controller {

    function indexAction() {
        $templates = Inji::app()->view->config;
        Inji::app()->view->setTitle('Шаблоны сайта');
        Inji::app()->view->page(['data' => compact('templates')]);
    }

    function setDefaultAction($name) {
        $templates = $this->view->config;
        $templates['app']['current'] = $name;
        $this->Config->save('module', $templates, 'View');
        $this->url->redirect('/admin/View');
    }

    function createTemplateAction() {
        $this->view->setTitle('Создание шаблона');
        Inji::app()->view->customAsset('css', '/static/moduleAsset/View/css/blockDrop.css');
        Inji::app()->view->customAsset('js', '/static/moduleAsset/View/js/blockDrop.js');
        if (!empty($_POST)) {
            $text = '<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        {HEAD}
    </head>
    <body>
    {PAGE:map}
    </body>
</html>';
            $templates = Inji::app()->view->config;
            $templates['app']['installed'][$_POST['name']] = $_POST['name'];
            $this->Config->save('module', $templates, 'View');
            $path = Inji::app()->curApp['parent']['path'] . '/templates/' . $_POST['name'] . '/index.html';
            $pathMap = Inji::app()->curApp['parent']['path'] . '/templates/' . $_POST['name'] . '/map.html';
            $this->files->create_dir(Inji::app()->curApp['parent']['path'] . '/templates/' . $_POST['name']);
            file_put_contents($path, $text);
            file_put_contents($pathMap, trim($_POST['map']));
            $template = [
                'template_name' => $_POST['name'],
                'name' => $_POST['name'],
                'file' => 'index.html',
            ];
            $this->Config->save(Inji::app()->curApp['parent']['path'] . '/templates/' . $_POST['name'] . '/config.php', $template);
            $this->url->redirect('/admin/View');
        }
        $this->view->page();
    }

    function editTemplateAction($templateName) {
        $this->view->setTitle('Редактирование шаблона');
        Inji::app()->view->customAsset('css', '/static/moduleAsset/View/css/blockDrop.css');
        Inji::app()->view->customAsset('js', '/static/moduleAsset/View/js/blockDrop.js');
        $template = $this->Config->custom(Inji::app()->curApp['parent']['path'] . '/templates/' . $templateName . '/config.php');
        $pathMap = Inji::app()->curApp['parent']['path'] . '/templates/' . $templateName . '/map.html';
        if (!empty($_POST)) {
            $templates = Inji::app()->view->config;
            $templates['app']['installed'][$templateName] = $_POST['name'];
            $this->Config->save('module', $templates, 'View');

            file_put_contents($pathMap, trim($_POST['map']));

            $template['template_name'] = $templateName;
            $template['name'] = $templateName;
            $this->Config->save(Inji::app()->curApp['parent']['path'] . '/templates/' . $_POST['name'] . '/config.php', $template);
            $this->url->redirect('/admin/View');
        }
        $template['map'] = file_get_contents($pathMap);
        $this->view->page(['data' => compact('template')]);
    }

    function editAction($template) {
        $templates = $this->view->modConf;
        if (!empty($_POST)) {
            foreach ($_POST['css'] as $key => $item)
                if (empty($item))
                    unset($_POST['css'][$key]);
            $templates['site']['install_templates'][$template]['css'] = $_POST['css'];
            foreach ($_POST['js'] as $key => $item)
                if (empty($item))
                    unset($_POST['js'][$key]);
            $templates['site']['install_templates'][$template]['js'] = $_POST['js'];
            $templates['site']['install_templates'][$template]['favicon'] = $_POST['favicon'];
            $templates['site']['install_templates'][$template]['template_name'] = $_POST['template_name'];
            $templates['site']['current'] = $template;
            $this->Config->save('module', $templates, 'View');
            $this->url->redirect('/admin/View');
        }
        $this->view->page(compact('template'));
    }

    function edit_fileAction($template, $type, $file_key = null) {
        $templates = $this->Config->module('View', 'site');
        if (!empty($_POST['text'])) {
            if ($type != 'html') {
                file_put_contents(Inji::app()->app['parent']['path'] . "/templates/{$template}/{$type}/{$templates['install_templates'][$template][$type][$file_key]}", $_POST['text']);
            } else {
                file_put_contents(Inji::app()->app['parent']['path'] . "/templates/{$template}/index.html", $_POST['text']);
            }
            $this->url->redirect($this->url->up_to(4) . 'edit/' . $template, 'Файл успешно отредактирован', 'success');
        }
        if ($type != 'html') {
            $text = file_get_contents(Inji::app()->app['parent']['path'] . "/templates/{$template}/{$type}/{$templates['install_templates'][$template][$type][$file_key]}");
        } else {
            $text = file_get_contents(Inji::app()->app['parent']['path'] . "/templates/{$template}/index.html");
        }
        $type = $type;
        $this->view->page(compact('text', 'type'));
    }

}

?>