<?php

class MerchantsController extends Controller
{
    function testPayAction()
    {
        $data = [
            'merchant' => [
                'description' => 'Тестовый платеж',
                'success' => 'http://' . INJI_DOMAIN_NAME . '/',
                'false' => 'http://' . INJI_DOMAIN_NAME . '/'
            ],
            'pay' => [
                'data' => 'test',
                'user_id' => \Users\User::$cur->id,
                'sum' => '10',
                'callback_module' => '',
                'callback_method' => ''
            ]
        ];

        $url = $this->Merchants->getPayUrl($data);
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

}
