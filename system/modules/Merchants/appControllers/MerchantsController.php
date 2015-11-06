<?php

class MerchantsController extends Controller
{
    function testPayAction()
    {

        $url = $this->Merchants->getPayUrl([
            'data' => 'test',
            'user_id' => \Users\User::$cur->id,
            'sum' => '10',
            'callback_module' => '',
            'callback_method' => ''
                ], null, [
            'description' => 'Тестовый платеж',
            'success' => 'http://' . INJI_DOMAIN_NAME . '/',
            'false' => 'http://' . INJI_DOMAIN_NAME . '/'
        ]);
        echo "<a href = '{$url}'>{$url}</a>";
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
        $request = new Merchants\Request([
            'get' => json_encode($_GET),
            'post' => json_encode($postData),
            'status' => $status,
            'system' => $system
        ]);
        $request->save();
        $this->Merchants->reciver($postData, $system, $status, $request);
    }

    function payAction($pay_id)
    {
        $merchants = Merchants\Merchant::getList(['where' => ['active', 1]]);
        $bread = [];
        $bread = ['text' => 'Оплата счета'];
        $this->view->page(['data' => compact('bread', 'merchants', 'pay_id')]);
    }

    function goAction($pay_id, $merchant_id)
    {
        $pay = Merchants\Pay::get($pay_id);
        if (!$pay) {
            Tools::redirect('/', 'Такой платеж не найден', 'danger');
        }
        $merchant = \Merchants\Merchant::get($merchant_id);
        if (!$merchant || !$merchant->active) {
            Tools::redirect('/', 'Такой способ оплаты не найден', 'danger');
        }
        $merchantOptions = [
            'description' => 'Оплата счета на сайте: ' . INJI_DOMAIN_NAME,
            'success' => 'http://' . INJI_DOMAIN_NAME . '/',
            'false' => 'http://' . INJI_DOMAIN_NAME . '/'
        ];

        $url = $this->Merchants->getPayUrl($pay, $merchant, $merchantOptions);
        Tools::redirect($url);
        //echo "<a href = '{$url}'>{$url}</a>";
    }

}
