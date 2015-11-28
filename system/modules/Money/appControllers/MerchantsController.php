<?php

class MerchantsController extends Controller
{
    function testPayAction()
    {

        $this->module->goToMerchant([
            'data' => 'test',
            'user_id' => \Users\User::$cur->id,
            'sum' => '0.01',
            'callback_module' => '',
            'callback_method' => ''
                ], null, [
            'description' => 'Тестовый платеж',
            'success' => 'http://' . INJI_DOMAIN_NAME . '/',
            'false' => 'http://' . INJI_DOMAIN_NAME . '/'
        ]);
    }

    function reciverAction($system = '', $status = '')
    {
        $postData = [];
        foreach ($_POST as $key => $text) {
            if (!is_array($text) && !mb_detect_encoding($text, array('UTF-8'), TRUE)) {
                $postData[$key] = iconv('Windows-1251', 'UTF-8', $text);
            } else {
                $postData[$key] = $text;
            }
        }
        foreach ($_GET as $key => $text) {
            if (!is_array($text) && !mb_detect_encoding($text, array('UTF-8'), TRUE)) {
                $postData[$key] = iconv('Windows-1251', 'UTF-8', $text);
            } else {
                $postData[$key] = $text;
            }
        }
        $merchant = false;

        if ($system) {
            $merchant = \Money\Merchant::get($system, 'object_name');
        }
        if (!$system || !$merchant) {
            $merchant = \Money\Merchant::get($this->module->currentMerchant, 'object_name');
        }
        $request = new Money\Merchant\Request([
            'get' => json_encode($_GET),
            'post' => json_encode($_POST),
            'status' => $status,
            'system' => $system,
            'merchant_id' => $merchant ? $merchant->id : 0,
        ]);
        $request->save();
        $this->module->reciver($postData, $system, $status, $request);
    }

    function payAction($pay_id = 0)
    {
        $pay = Money\Pay::get($pay_id);
        if (!$pay) {
            $this->view->setTitle('Выбор счета для оплаты');
            $bread = [];
            $bread = ['text' => 'Просмотр счетов'];
            $pays = Money\Pay::getList(['where' => [['user_id', \Users\User::$cur->id], ['pay_status_id', 1]], 'order' => ['date_create', 'DESC']]);
            $this->view->page(['content' => 'pays', 'data' => compact('bread', 'pays')]);
        } else {
            $where = [['active', 1]];
            $where[] = [$pay->type, 1];
            $merchants = Money\Merchant::getList(['where' => $where]);
            $bread = [];
            $bread = ['text' => 'Оплата счета'];
            $this->view->setTitle('Выбор способа оплаты');
            $this->view->page(['data' => compact('bread', 'merchants', 'pay')]);
        }
    }

    function goAction($pay_id, $merchant_id, $currency_id)
    {
        $pay = Money\Pay::get($pay_id);
        if (!$pay) {
            Tools::redirect('/', 'Такой платеж не найден', 'danger');
        }
        if ($pay->pay_status_id != 1) {
            Tools::redirect('/', 'Счет уже обработан');
        }
        $merchant = \Money\Merchant::get($merchant_id);
        if (!$merchant || !$merchant->active) {
            Tools::redirect('/', 'Такой способ оплаты не найден', 'danger');
        }
        $allowCurrencies = $merchant->allowCurrencies($pay);
        $method = [];
        foreach ($allowCurrencies as $allowCurrency) {
            if ($allowCurrency['currency']->id == $currency_id) {
                $method = $allowCurrency;
                break;
            }
        }
        if (!$method) {
            Tools::redirect('/', 'Валюта для этого способа оплаты не найдена', 'danger');
        }
        $merchantOptions = [
            'description' => $pay->description ? '#' . $pay->id . ' ' . $pay->description : 'Оплата счета №' . $pay->id . ' на сайте: ' . idn_to_utf8(INJI_DOMAIN_NAME),
            'success' => 'http://' . INJI_DOMAIN_NAME . '/',
            'false' => 'http://' . INJI_DOMAIN_NAME . '/'
        ];

        $this->Money->goToMerchant($pay, $merchant, $method, $merchantOptions);
    }

}
