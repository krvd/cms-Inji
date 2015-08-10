<?php

class Libs extends Module {

    function loadLib($libName, $options = []) {

        if (file_exists($this->path . '/static/libs/' . $libName . '/libConfig.php')) {
            $lib = include $this->path . '/static/libs/' . $libName . '/libConfig.php';
            if (!empty($lib['requiredLibs'])) {
                foreach ($lib['requiredLibs'] as $rLib) {
                    $this->loadLib($rLib);
                }
            }
            if (!empty($lib['files']['css']) && (!isset($options['loadCss']) || $options['loadCss'] )) {
                foreach ($lib['files']['css'] as $file) {
                    App::$cur->view->customAsset('css', '/static/moduleAsset/libs/libs/' . $libName . '/' . $file, $libName);
                }
            }
            if (!empty($lib['files']['js'])) {
                foreach ($lib['files']['js'] as $file) {
                    if (strpos($file, '//') !== false) {

                        App::$cur->view->customAsset('js', $file, $libName);
                    } else {

                        App::$cur->view->customAsset('js', '/static/moduleAsset/libs/libs/' . $libName . '/' . $file, $libName);
                    }
                }
            }
        }
    }

}
