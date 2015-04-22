<?php

class templatesManagerController extends Controller {

    function indexAction() {
        $templates = $this->view->modConf['site'];
        $this->view->set_title('Шаблоны сайта');
        $this->view->page(compact('templates'));
    }

    function createAction() {
        $this->view->set_title('Создание шаблона');
        if (!empty($_POST['template_name']) && !empty($_POST['name'])) {
            $templates = Inji::app()->Config->custom(Inji::app()->app['parent']['path'] . '/templates/config.php');
            if (!isset($templates['install_templates'][$_POST['name']])) {
                $path = Inji::app()->app['parent']['path'] . '/templates/' . $_POST['name'] . '/index.html';
                $this->_FS->create_dir(substr($path, 0, strripos($path, '/')));
                $config = $this->view->modConf;
                $config['site']['install_templates'][$_POST['name']] = $_POST['template_name'];
                $this->Config->save('module', $config, 'View');
                $this->Config->save(Inji::app()->app['parent']['path'] . '/templates/' . $_POST['name'] . '/config.php', array(
                    'template_name' => $_POST['template_name'],
                    'name' => $_POST['name'],
                    'file' => 'index.html',
                    'css' => array(
                        'style.css'
                    ),
                    'js' => array(
                        'script.js'
                    ),
                    'files' => array(
                        array('file' => 'default', 'name' => 'Внутренняя страница'),
                        array('file' => 'main_page', 'name' => 'Главная страница'),
                    )
                ));

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
    {CONTENT}
    </body>
</html>
';
                file_put_contents($path, $text);
                $path = Inji::app()->app['parent']['path'] . '/templates/' . $_POST['name'] . '/css/style.css';
                $this->_FS->create_dir(substr($path, 0, strripos($path, '/')));
                file_put_contents($path, '');
                $path = Inji::app()->app['parent']['path'] . '/templates/' . $_POST['name'] . '/js/script.js';
                $this->_FS->create_dir(substr($path, 0, strripos($path, '/')));
                file_put_contents($path, '');
                $this->url->redirect('/admin/View');
            } else {
                $this->msg->add('Такая тема уже существует!', 'danger');
            }
        }
        $this->view->page();
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

    function set_defaultAction($name) {
        $templates = $this->view->modConf;
        $templates['site']['current'] = $name;
        $this->Config->save('module', $templates, 'View');
        $this->url->redirect('/admin/View');
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