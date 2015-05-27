<?php

/**
 * StaticLoader class
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class StaticLoader extends Module {

    public $mimes = [];

    function init() {
        $this->mimes = $this->config['mimes'];
    }

    function parsePath($params) {
        foreach ($params as $key => $param) {
            $param = trim($param);
            if ($param == '..') {
                unset($params[$key]);
            } else {
                $params[$key] = $param;
            }
        }
        return implode('/', $params);
    }

    function header($code, $exit = false) {
        switch ($code) {
            case '404':
                header('HTTP/1.1 404 Not Found');
                break;
            default :
                header($code);
        }
        if ($exit) {
            exit;
        }
    }

    function giveFile($file) {
        if (!file_exists($file)) {
            $this->header(404, true);
        }


        $fileinfo = pathinfo($file);
        //if( empty( $fileinfo['extension'] ) ) {
        // header('HTTP/1.1 404 Not Found');
        //  exit();
        //}

        if (!empty($_GET['resize'])) {

            $allow_resize = false;
            if (App::$cur->db) {
                $type = App::$cur->Files->get_type_by_ext($fileinfo['extension']);
                $allow_resize = $type['file_type_allow_resize'];
            }
            if (empty($type) && in_array(strtolower($fileinfo['extension']), array('png', 'jpg', 'jpeg', 'gif')))
                $allow_resize = true;
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

                    if (App::$cur->app['system'])
                        $dir = App::$cur->app['parent']['path'];
                    else
                        $dir = App::$cur->app['path'];

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

                    $dirnoslash = str_replace('/', '', substr($fileinfo['dirname'], strpos($fileinfo['dirname'], '/static')));
                    $path = $dir . '/static/cache/' . $dirnoslash . $fileinfo['filename'] . '.' . $sizes[0] . 'x' . $sizes[1] . $crop . '.' . $fileinfo['extension'];
                    //exit($path);
                    if (!file_exists($path)) {
                        App::$cur->_FS->create_dir($dir . '/static/cache/');
                        copy($file, $path);
                        App::$cur->_IMAGE->resize($path, $sizes[0], $sizes[1], $crop);
                    }

                    $file = $path;
                }
            }
        }


        //if( strpos( $file, '/static/doc' ) !== false ) {
        header('Content-Description: File Transfer');
        //}
        if (isset($this->mimes[strtolower($fileinfo['extension'])])) {
            header("Content-Type: " . $this->mimes[strtolower($fileinfo['extension'])]);
        }
        if (isset($_GET['frustrate_dl']) || in_array($fileinfo['extension'], array('pptx'))) {
            header('Content-Disposition: attachment; filename=' . basename($file));
        }

        header('Content-Transfer-Encoding: binary');
        //}
        header("Cache-control: public");
        header("Accept-Ranges: bytes");
        header("Pragma: public");
        header("Content-Length: " . filesize($file));
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($file)) . ' GMT');
        if (in_array($fileinfo['extension'], array('doc', 'docx', 'xls', 'xlsx')))
            header("Content-Disposition: attachment; filename=" . $file);
        $request = getallheaders();
        if (isset($request['If-Modified-Since'])) {
            // Разделяем If-Modified-Since (Netscape < v6 отдаёт их неправильно)
            $modifiedSince = explode(';', $request['If-Modified-Since']);

            // Преобразуем запрос клиента If-Modified-Since в таймштамп
            $modifiedSince = strtotime($modifiedSince[0]);
        } else {
            // Устанавливаем время модификации в ноль
            $modifiedSince = 0;
        }
        if (filemtime($file) <= $modifiedSince && isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] != 'no-cache') {
            // Разгружаем канал передачи данных!
            header('HTTP/1.1 304 Not Modified');
            exit();
        }


        readfile($file);
        exit();
    }

}
