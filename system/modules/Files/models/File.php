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
        $sitePath = \App::$primary->path;
        return "{$sitePath}/{$this->file_path}";
    }

}
