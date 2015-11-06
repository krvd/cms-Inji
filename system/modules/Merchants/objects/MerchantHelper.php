<?php

/**
 * MerchantHelper
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Merchants;

/**
 * Description of MerchantHelper
 *
 * @author inji
 */
class MerchantHelper extends \Object
{
    static function getConfig()
    {
        $class = get_called_class();
        $class = substr($class, strrpos($class, '\\') + 1);
        $merchant = Merchant::get($class, 'object_name');
        $configs = [];
        foreach ($merchant->configs as $config) {
            $configs[$config->name] = $config->value;
        }
        return $configs;
    }

}
