<?php

class Libs extends Module {

    function loadLib($libName) {
        if (file_exists($this->path . '/static/libs/' . $libName . '/libConfig.php')) {
            $lib = include $this->path . '/static/libs/' . $libName . '/libConfig.php';
            if(!empty($lib['requiredLibs'])){
                foreach ($lib['requiredLibs'] as $rLib){
                    $this->loadLib($rLib);
                }
            }
            if (!empty($lib['files']['css'])) {
                foreach ($lib['files']['css'] as $file) {
                    App::$cur->view->customAsset('css', '/static/moduleAsset/libs/libs/' . $libName . '/' . $file, true);
                }
            }
            if (!empty($lib['files']['js'])) {
                foreach ($lib['files']['js'] as $file) {
                    App::$cur->view->customAsset('js', '/static/moduleAsset/libs/libs/' . $libName . '/' . $file, true);
                }
            }
        }
        return [];
    }

}
