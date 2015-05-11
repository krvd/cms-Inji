<?php

class Libs extends Module {

    function init() {
        if (!Inji::app()->app['system']) {
            $libs = Libs\FrontLib::get_list(['where' => [['lafl_enabled', 1]]]);
            foreach ($libs as $lib) {
                foreach ($lib->files as $file) {
                    Inji::app()->view->customAsset($file->laflf_type, '/static/moduleAsset/Libs/' . $file->laflf_file, true);
                }
            }
        }
    }

}