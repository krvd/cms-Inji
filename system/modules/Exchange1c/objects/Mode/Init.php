<?php

/**
 * Mode Init
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Exchange1c\Mode;

class Init extends \Exchange1c\Mode
{
    public function process()
    {
        echo "zip=no\n";
        echo 'file_limit=' . \Tools::toBytes(ini_get('post_max_size'));
        $this->end();
    }

}
