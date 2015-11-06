<?php

class Merchants extends Module
{
    public $current = '';

    function init()
    {
        if (!empty($this->config['default'])) {
            $this->current = $this->config['default'];
        }
    }

    function getPayUrl($pay, $merchant, $merchantOptions)
    {
        if ($merchant) {
            $objectName = $merchant->object_name;
        } else {
            $objectName = $this->current;
        }
        if (is_array($pay)) {
            $pay = new Merchants\Pay($pay);
            $pay->save();
        }

        $className = 'Merchants\MerchantHelper\\' . $objectName;
        return $className::getPayUrl($pay->id, $pay->sum, $merchantOptions['description'], $merchantOptions['success'], $merchantOptions['false']);
    }

    function reciver($data, $system, $status, $mr)
    {
        if ($system) {
            $merchant = \Merchants\Merchant::get($system, 'object_name');
        } else {
            $merchant = false;
        }
        if ($merchant) {
            $this->current = $system;
        }
        $className = 'Merchants\MerchantHelper\\' . $this->current;
        $result = $className::reciver($data, $status);
        $result['pay'] = null;
        if (!empty($result['payId'])) {
            $result['pay'] = Merchants\Pay::get($result['payId']);
        }
        if ($result['pay'] && $result['pay']->callback_module && $result['pay']->callback_method) {
            $status = $this->{$result['pay']->callback_module}->{$result['pay']->callback_method}($result);
        }
        if (isset($status) && $result['pay']) {
            $result['pay']->pay_status_id = $status;
            //TODO
            //$result['pay']->date_recive = date('Y-m-d H:i:s');
            $result['pay']->save();
        }
        if (!empty($result['payId'])) {
            $mr->pay_id = $result['payId'];
        }
        if (!empty($result['callback'])) {
            echo $result['callback'];
            $mr->result_callback = json_encode($result['callback']);
        }
        if (!empty($result['status'])) {
            $mr->status = $result['status'];
        }
        $mr->save();
    }

}
