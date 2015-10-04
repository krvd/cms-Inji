<?php

/**
 * StaticLoader class
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class StaticLoader extends Module
{
    public $mimes = [];

    function init()
    {
        $this->mimes = $this->config['mimes'];
    }

    function parsePath($path)
    {
        $path = Tools::parsePath($path);

        if (strpos($path, '/') === 0) {
            $path = substr($path, 1);
        }
        $app = substr($path, 0, strpos($path, '/'));
        if (file_exists(INJI_SYSTEM_DIR . '/program/' . $app)) {
            $path = substr($path, strpos($path, '/') + 1);
            if (\App::$cur->name != $app) {
                $scriptApp = new App();
                $scriptApp->name = $app;
                $scriptApp->system = true;
                $scriptApp->staticPath = "/" . $scriptApp->name . "/static";
                $scriptApp->templatesPath = "/" . $scriptApp->name . "/static/templates";
                $scriptApp->path = INJI_SYSTEM_DIR . '/program/' . $scriptApp->name;
                $scriptApp->type = 'app' . ucfirst(strtolower($scriptApp->name));
                $scriptApp->installed = true;
                $scriptApp->params = [];
                $scriptApp->config = Config::app($scriptApp);
            } else {
                $scriptApp = \App::$cur;
            }
        } else {
            $scriptApp = \App::$cur->system ? \App::$primary : \App::$cur;
        }
        if (strpos($path, 'static/') !== false && strpos($path, 'static/') <= 1) {
            $path = substr($path, strpos($path, 'static') + 7);
        }
        $type = substr($path, 0, strpos($path, '/'));

        switch ($type) {
            case 'templates':
                $path = substr($path, strpos($path, '/') + 1);
                return $scriptApp->view->templatesPath . '/' . $path;
            case 'system':
                $path = substr($path, strpos($path, '/') + 1);
                return INJI_SYSTEM_DIR . '/static/' . $path;
            case 'moduleAsset':
                $path = substr($path, strpos($path, '/') + 1);
                if (!strpos($path, '/')) {
                    return false;
                }
                $module = substr($path, 0, strpos($path, '/'));

                if (!$scriptApp->$module) {
                    return false;
                }
                $path = substr($path, strpos($path, '/') + 1);
                if (is_callable([$module, 'staticCalled'])) {
                    return $scriptApp->$module->staticCalled($path, $scriptApp->$module->path . '/static/');
                }
                return $scriptApp->$module->path . '/static/' . $path;
            default:
                return $scriptApp->path . '/static/' . $path;
        }
    }

    function giveFile($file)
    {
        $convet = FALSE;
        if (!file_exists($file) && file_exists(mb_convert_encoding($file, 'Windows-1251', 'UTF-8'))) {
            $file = mb_convert_encoding($file, 'Windows-1251', 'UTF-8');
            $convet = true;
        }
        if (!file_exists($file)) {
            header('HTTP/1.1 404 Not Found');
            exit();
        }

        $fileinfo = pathinfo($file);
        if (empty($fileinfo['extension'])) {
            header('HTTP/1.1 404 Not Found');
            exit();
        }
        $options = [];
        if (!empty($_GET['resize'])) {

            $allow_resize = false;
            if (App::$cur->db->connect) {
                $type = Files\Type::get($fileinfo['extension'], 'ext');
                $allow_resize = $type->allow_resize;
            }
            if (empty($type) && in_array(strtolower($fileinfo['extension']), array('png', 'jpg', 'jpeg', 'gif'))) {
                $allow_resize = true;
            }
            if ($allow_resize) {

                $sizes = explode('x', $_GET['resize']);
                $sizes[0] = intval($sizes[0]);
                if (isset($sizes[1]))
                    $sizes[1] = intval($sizes[1]);
                else
                    $sizes[1] = 0;

                if (!$sizes[0] || !$sizes[1]) {
                    header('HTTP/1.1 404 Not Found');
                    exit();
                } elseif ($sizes[0] > 2000 || $sizes[1] > 2000) {
                    header('HTTP/1.1 404 Not Found');
                    exit();
                } else {
                    $dir = App::$primary->path;

                    if (!empty($_GET['resize_crop'])) {
                        if (in_array($_GET['resize_crop'], array('q', 'c')))
                            $crop = $_GET['resize_crop'];
                        else
                            $crop = 'c';
                    }
                    elseif (!empty($_GET['resize_quadro']))
                        $crop = 'q';
                    else
                        $crop = '';
                    $pos = 'center';
                    if (!empty($_GET['resize_pos']) && in_array($_GET['resize_pos'], array('top', 'center'))) {
                        $pos = $_GET['resize_pos'];
                    }
                    $options = [];
                    if ($sizes) {
                        $options = ['resize' => ['x' => $sizes[0], 'y' => $sizes[1]]];
                    }
                    $options['crop'] = $crop;
                    $options['pos'] = $pos;
                }
            }
        }
        $path = Cache::file($file, $options);
        $path = $convet ? mb_convert_encoding($path, 'UTF-8', 'Windows-1251') : $path;
        Tools::redirect('/' . $path);
    }

}
