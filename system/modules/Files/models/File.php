<?php

/**
 * File model
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 */
class File extends Model
{

    static function table()
    {
        return 'files';
    }

    static function index()
    {
        return 'file_id';
    }

    function beforeDelete()
    {
        $path = $this->getRealPath();
        if (file_exists($path)) {
            unlink($path);
        }
    }

    function getRealPath()
    {
        if (!empty(Inji::app()->app['parent'])) {
            $sitePath = Inji::app()->app['parent']['path'];
        } else {
            $sitePath = Inji::app()->app['path'];
        }
        return "{$sitePath}/{$this->file_path}";
    }

}
