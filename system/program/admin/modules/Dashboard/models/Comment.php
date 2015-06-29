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

namespace Dashboard;

class Comment extends \Model {

    static  function relations() {
        return [
            'user' => [
                'model' => 'Users\User',
                'col' => 'user_id'
            ]
        ];
    }

}