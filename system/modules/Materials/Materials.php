<?php

class Materials extends Module
{
    function viewsList()
    {
        $return = [
            'inherit' => 'Как у родителя',
            'default' => 'Стандартная страница'
        ];
        $conf = App::$primary->view->template->config;

        if (!empty($conf['files']['modules']['Materials'])) {

            foreach ($conf['files']['modules']['Materials'] as $file) {
                if (!empty($file['type']) && $file['type'] == 'Material') {
                    $return[$file['file']] = $file['name'];
                }
            }
        }
        return $return;
    }

    function templatesList()
    {
        $return = [
            'inherit' => 'Как у родителя',
            'current' => 'Текущая тема'
        ];

        $conf = App::$primary->view->template->config;

        if (!empty($conf['files']['aditionTemplateFiels'])) {
            foreach ($conf['files']['aditionTemplateFiels'] as $file) {
                if ($file['type'] == 'Material') {
                    $return[$file['file']] = '- ' . $file['name'];
                }
            }
        }
        return $return;
    }

    function viewsCategoryList()
    {
        $return = [
            'inherit' => 'Как у родителя',
            'category' => 'Стандартная категория',
        ];
        $conf = App::$primary->view->template->config;

        if (!empty($conf['files']['modules']['Materials'])) {

            foreach ($conf['files']['modules']['Materials'] as $file) {
                if ($file['type'] == 'Category') {
                    $return[$file['file']] = $file['name'];
                }
            }
        }
        return $return;
    }

    function templatesCategoryList()
    {
        $return = [
            'inherit' => 'Как у родителя',
            'current' => 'Текущая тема'
        ];

        $conf = App::$primary->view->template->config;

        if (!empty($conf['files']['aditionTemplateFiels'])) {
            foreach ($conf['files']['aditionTemplateFiels'] as $file) {
                if ($file['type'] == 'Category') {
                    $return[$file['file']] = '- ' . $file['name'];
                }
            }
        }
        return $return;
    }

}

?>
