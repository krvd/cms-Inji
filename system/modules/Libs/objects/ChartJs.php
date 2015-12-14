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

class ChartJs extends \Object
{
    static $name = 'ChartJs';
    static $composerPacks = [
        'nnnick/chartjs' => 'dev-master'
    ];
    static $files = [
        'js' => [
            'nnnick/chartjs/Chart.min.js',
        ],
    ];
    static $staticDirs = [
        'nnnick/chartjs'
    ];

}
