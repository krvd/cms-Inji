<?php

/**
 * Typeahead Bootstrap 3 library
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Libs;

class TypeaheadBootstrap3 extends \Object
{
    static $name = 'Typeahead Bootstrap 3';
    static $composerPacks = [
        'bassjobsen/bootstrap-3-typeahead' => '4.*'
    ];
    static $files = [
        'js' => [
            'bassjobsen/bootstrap-3-typeahead/bootstrap3-typeahead.min.js'
        ]
    ];
    static $staticDirs = [
        'bassjobsen/bootstrap-3-typeahead'
    ];

}
