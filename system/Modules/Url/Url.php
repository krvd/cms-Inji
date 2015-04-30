<?php

class Url extends Module
{

    function module($module_name = '')
    {
        if (Inji::app()->app['parent'])
            $path = '/' . Inji::app()->app['name'] . '/';
        else
            $path = '/';

        if ($module_name === '')
            $path .= Inji::app()->controller['module'];
        else
            $path .= rtrim($module_name, '/');

        return $path;
    }

    function current($url = '')
    {
        return rtrim(Inji::app()->uri['path'], '/') . '/' . $url;
    }

    function up_to($count, $url = '')
    {
        $ar = explode('/', Inji::app()->uri['path']);
        return rtrim(implode('/', array_slice($ar, 0, -$count)), '/') . '/' . $url;
    }

    function redirect($href = '/', $text = false, $status = 'info')
    {

        if ($text !== false)
            Inji::app()->msg->add($text, $status);

        header("Location: {$href}");
        exit("Перенаправление на: <a href = '{$href}'>{$href}</a>");
    }

}
