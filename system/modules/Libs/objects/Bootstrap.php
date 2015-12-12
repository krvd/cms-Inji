<?php

/**
 * Bootstrap library
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Libs;

class Bootstrap extends \Object
{
    static $name = 'BootStrap';
    static $composerPacks = [
        'twbs/bootstrap' => '3.*'
    ];
    static $files = [
        'js' => [
            'twbs/bootstrap/dist/js/bootstrap.min.js',
            '/static/moduleAsset/libs/libs/bootstrap/js/modalStack.js'
        ],
        'css' => [
            'twbs/bootstrap/dist/css/bootstrap.min.css'
        ]
    ];
    static $staticDirs = [
        'twbs/bootstrap/dist'
    ];
    static $requiredLibs = [
        'jquery'
    ];

}
