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
class Cache {

    static $server = null;
    static $connectTrying = false;
    static $connected = false;

    static function connect() {
        if (!self::$connectTrying && class_exists('Memcache', false)) {
            self::$server = new Memcache();
            self::$connected = @self::$server->connect('192.168.0.88', 11211);
        }
        self::$connectTrying = true;
    }

    static function get($name, $params = [], $callback = null) {
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

    static function set($name, $params = [], $val = '', $lifeTime = 3600) {
        if (!self::$connected) {
            self::connect();
        }
        if (self::$connected) {
            return @self::$server->set($name . serialize($params), $val, false, $lifeTime);
        }
        return false;
    }

}
