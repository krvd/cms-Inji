<?php

class Libs extends Module {

    function init() {
        if (!Inji::app()->app['system']) {
            $libs = FrontLib::get_list(['where' => [['lafl_enabled', 1]]]);
            foreach ($libs as $lib) {
                foreach ($lib->files as $file) {
                    $this->view->customAsset($file->laflf_type, '/moduleAsset/LibsAsseter/' . $file->laflf_file, true);
                }
            }
        }
    }

}

?>
