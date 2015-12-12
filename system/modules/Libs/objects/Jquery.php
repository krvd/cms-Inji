<?php

/**
 * Jquery library
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Libs;

class Jquery extends \Object
{
    static $name = 'jQuery';
    static $composerPacks = [
        'components/jquery' => '2.1.*'
    ];
    static $files = [
        'js' => [
            'components/jquery/jquery.min.js'
        ]
    ];
    static $staticDirs = [
        'components/jquery'
    ];

}
