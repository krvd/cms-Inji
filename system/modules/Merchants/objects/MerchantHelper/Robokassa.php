<?php

/**
 * Robokassa
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Merchants\MerchantHelper;

class Robokassa extends \Merchants\MerchantHelper
{
    static function reciver($data, $status)
    {
        $config = static::getConfig();

        $hashGenerated = md5("{$data['OutSum']}:{$data['InvId']}:{$config['pass2']}");

        $result['payId'] = $data["InvId"];
        $result['status'] = 'error';
        if (strtolower($data['SignatureValue']) == $hashGenerated)
            $result['status'] = 'success';

        return $result;
    }

    static function getPayUrl($payId, $amount, $description = '', $success = '/', $false = '/')
    {
        $config = static::getConfig();

        $amount = (float) $amount;
        $hash = md5("{$config['login']}:{$amount}:{$payId}:{$config['pass1']}");

        $data = [
            'MrchLogin' => $config['login'],
            'OutSum' => $amount,
            'InvId' => $payId,
            'SignatureValue' => $hash
        ];
        if (empty($config['test'])) {
            return 'https://auth.robokassa.ru/Merchant/Index.aspx?' . http_build_query($data);
        } else {
            return 'http://test.robokassa.ru/Index.aspx?' . http_build_query($data);
        }
    }

}
