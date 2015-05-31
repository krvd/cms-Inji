<?php

/**
 * Item name
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Users;
class Session extends \Model{
    static function table() {
        return 'user_sessions';
    }

    static function index() {
        return 'us_id';
    }
}
