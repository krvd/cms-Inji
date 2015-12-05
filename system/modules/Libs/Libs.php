<?php
/**
 * Libs module
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
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

    function staticCalled($file, $dir) {
        $libPath = preg_replace('!^libs/!', '', $file);
        $libName = substr($libPath, 0, strpos($libPath, '/'));
        if (file_exists($this->path . '/static/libs/' . $libName . '/libConfig.php')) {
            $lib = include $this->path . '/static/libs/' . $libName . '/libConfig.php';
            if (!empty($lib['programDirs'])) {
                $fileDir = substr($libPath, strlen($libName) + 1, strpos($libPath, '/', strlen($libName) + 1) - strlen($libName) - 1);
                foreach ($lib['programDirs'] as $programDir) {
                    if ($programDir == $fileDir) {
                        include $dir . $file;
                        exit();
                    }
                }
            }
        }
        return $dir . $file;
    }

}
