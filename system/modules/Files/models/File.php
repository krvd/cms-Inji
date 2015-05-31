<?php

namespace Files;

class File extends \Model {

    function beforeDelete() {
        $path = $this->getRealPath();
        if (file_exists($path)) {
            unlink($path);
        }
    }

    function getRealPath() {
        if (!empty(App::$cur->app['parent'])) {
            $sitePath = App::$cur->app['parent']['path'];
        } else {
            $sitePath = App::$cur->app['path'];
        }
        return "{$sitePath}/{$this->file_path}";
    }

}
