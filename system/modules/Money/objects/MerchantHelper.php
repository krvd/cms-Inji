<?php

/**
 * MerchantHelper
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Money;

/**
 * Description of MerchantHelper
 *
 * @author inji
 */
class MerchantHelper extends \Object
{
    static $merchant;

    static function getMerchant()
    {
        if (!self::$merchant) {
            $class = get_called_class();
            $class = substr($class, strrpos($class, '\\') + 1);
            self::$merchant = Merchant::get($class, 'object_name');
        }
        return self::$merchant;
    }

    static function getConfig()
    {
        $merchant = self::getMerchant();
        $configs = [];
        foreach ($merchant->configs as $config) {
            $configs[$config->name] = $config->value;
        }
        return $configs;
    }

    static function getMerchantCurrency($currency)
    {
        $merchant = self::getMerchant();
        foreach ($merchant->currencies as $merchantCurrency) {
            if ($merchantCurrency->currency_id = $currency->id) {
                return $merchantCurrency;
            }
        }
    }

}
