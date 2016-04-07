<?php

/**
 * Geography module
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2016 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class Geography extends Module
{
    function init()
    {
        $alias = str_replace(App::$primary->config['site']['domain'], '', INJI_DOMAIN_NAME);
        $city = null;
        if ($alias) {
            $alias = str_replace('.', '', $alias);
            $city = Geography\City::get($alias, 'alias');
        }
        if (!$city) {
            $city = Geography\City::get(1, 'default');
        }
        Geography\City::$cur = $city;
    }

}
