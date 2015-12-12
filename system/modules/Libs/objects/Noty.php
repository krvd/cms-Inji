<?php

/**
 * Noty library
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Libs;

class Noty extends \Object
{
    static $name = 'Noty';
    static $composerPacks = [
        'needim/noty' => '2.3.*'
    ];
    static $files = [
        'js' => [
            'needim/noty/js/noty/packaged/jquery.noty.packaged.min.js'
        ]
    ];
    static $staticDirs = [
        'needim/noty/js'
    ];

}
