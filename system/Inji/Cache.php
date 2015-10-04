<?php

/**
 * Item name
 *
 * Info
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

/**
 * Description of Cache
 *
 * @author inji
 */
class Cache
{
    static $server = null;
    static $connectTrying = false;
    static $connected = false;

    static function connect()
    {
        if (!self::$connectTrying && class_exists('Memcache', false)) {
            self::$server = new Memcache();
            self::$connected = @self::$server->connect('localhost', 11211);
        }
        self::$connectTrying = true;
    }

    static function get($name, $params = [], $callback = null)
    {
        if (!self::$connected) {
            self::connect();
        }
        if (!self::$connected) {
            if (is_callable($callback, true)) {
                return $callback($params);
            }
            return false;
        }
        $val = @self::$server->get($name . serialize($params));
        if ($val !== false) {
            return $val;
        } else {
            if (is_callable($callback, true)) {
                $val = $callback($params);
                self::set($name, $params, $val);
                return $val;
            }
        }
        return false;
    }

    static function set($name, $params = [], $val = '', $lifeTime = 3600)
    {
        if (!self::$connected) {
            self::connect();
        }
        if (self::$connected) {
            return @self::$server->set($name . serialize($params), $val, false, $lifeTime);
        }
        return false;
    }

    static function file($file, $options = [])
    {
        $dir = App::$primary->path;
        $sizes = !empty($options['resize']) ? $options['resize'] : [];
        $crop = !empty($options['crop']) ? $options['crop'] : '';
        $pos = !empty($options['pos']) ? $options['pos'] : 'center';
        $fileinfo = pathinfo($file);
        $fileCheckSum = md5($fileinfo['dirname'].filemtime($file));
        $path = 'cache/' . App::$primary->dir . '/' . $fileCheckSum . '_' . $fileinfo['filename'];
        if ($sizes) {
            $path .= '.' . $sizes['x'] . 'x' . $sizes['y'] . $crop . $pos;
        }
        $path .= '.' . $fileinfo['extension'];
        if (!file_exists($path)) {
            Tools::createDir('cache/' . App::$primary->dir . '/');
            copy($file, $path);
            if ($sizes) {
                Tools::resizeImage($path, $sizes['x'], $sizes['y'], $crop, $pos);
            }
        }

        return $path;
    }

}
