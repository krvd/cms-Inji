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

    function getPayUrl($data)
    {
        if (is_array($data['pay']['data'])) {
            $data['pay']['data'] = json_encode($data['pay']['data']);
        }
        $pay = new Merchants\Pay($data['pay']);
        $pay->save();
        return $this->{'getPayUrl' . $this->current}($pay->id, $pay->sum, $data['merchant']['description'], $data['merchant']['success'], $data['merchant']['false']);
    }

    function getPayUrlPayeer($payId, $amount, $description = '', $success = '/', $false = '/')
    {
        $config = $this->config['merchants']['Payeer'];
        $data['m_shop'] = $config['shopId'];
        $data['m_orderid'] = $payId;
        $data['m_amount'] = number_format($amount, 2, '.', '');
        $data['m_curr'] = 'RUB';
        $data['m_desc'] = base64_encode($description);
        $data['m_key'] = $config['secret'];
        $data['m_process'] = 'send';

        $arHash = array(
            $data['m_shop'],
            $data['m_orderid'],
            $data['m_amount'],
            $data['m_curr'],
            $data['m_desc'],
            $data['m_key']
        );
        $data['m_sign'] = strtoupper(hash('sha256', implode(':', $arHash)));
        return 'http://payeer.com/merchant/?' . http_build_query($data);
    }

    function getPayUrlRobokassa($payId, $amount, $description = '', $success = '/', $false = '/')
    {
        $config = $this->config['merchants']['Robokassa'];

        $amount = (float) $amount;
        $hash = md5("{$config['login']}:{$amount}:{$payId}:{$config['pass1']}");

        $data = [
            'MrchLogin' => $config['login'],
            'OutSum' => $amount,
            'InvId' => $payId,
            'SignatureValue' => $hash
        ];

        return 'http://test.robokassa.ru/Index.aspx?' . http_build_query($data);
    }

    function getPayUrlW1($payId, $amount, $description = '', $success = '/', $false = '/')
    {
        $config = $this->config['merchants']['W1'];

        if (!$description)
            $description = "Оплата заказа на сайте " . INJI_DOMAIN_NAME;

        //Секретный ключ интернет-магазина
        $key = $config['secret'];

        $fields = array();

        // Добавление полей формы в ассоциативный массив
        $fields["WMI_MERCHANT_ID"] = $config['shopId'];
        $fields["WMI_PAYMENT_AMOUNT"] = number_format($amount, 0, '.', '');
        $fields["WMI_CURRENCY_ID"] = "643";
        $fields["WMI_PAYMENT_NO"] = $payId;
        $fields["WMI_DESCRIPTION"] = "BASE64:" . base64_encode($description);
        $fields["WMI_EXPIRED_DATE"] = "2019-12-31T23:59:59";
        $fields["WMI_SUCCESS_URL"] = $success;
        $fields["WMI_FAIL_URL"] = $false;
        //Сортировка значений внутри полей
        foreach ($fields as $name => $val) {
            if (is_array($val)) {
                usort($val, "strcasecmp");
                $fields[$name] = $val;
            }
        }

        // Формирование сообщения, путем объединения значений формы,
        // отсортированных по именам ключей в порядке возрастания.
        uksort($fields, "strcasecmp");
        $fieldValues = "";

        foreach ($fields as $value) {
            if (is_array($value))
                foreach ($value as $v) {
                    //Конвертация из текущей кодировки (UTF-8)
                    //необходима только если кодировка магазина отлична от Windows-1251
                    $v = iconv("utf-8", "windows-1251", $v);
                    $fieldValues .= $v;
                } else {
                //Конвертация из текущей кодировки (UTF-8)
                //необходима только если кодировка магазина отлична от Windows-1251
                $value = iconv("utf-8", "windows-1251", $value);
                $fieldValues .= $value;
            }
        }

        // Формирование значения параметра WMI_SIGNATURE, путем
        // вычисления отпечатка, сформированного выше сообщения,
        // по алгоритму MD5 и представление его в Base64

        $signature = base64_encode(pack("H*", md5($fieldValues . $key)));

        //Добавление параметра WMI_SIGNATURE в словарь параметров формы

        $fields["WMI_SIGNATURE"] = $signature;
        /*
          print "<form action=\"https://wl.walletone.com/checkout/checkout/Index\" method=\"POST\">";

          foreach ($fields as $key => $val) {
          if (is_array($val)) {
          foreach ($val as $value) {
          print "<input type=\"hidden\" name=\"$key\" value=\"$value\"/>";
          }
          } else {
          print "<input type=\"hidden\" name=\"$key\" value=\"$val\"/>";
          }
          }

          print "<input type=\"submit\"/></form>";
         */
        return 'https://www.walletone.com/checkout/default.aspx?' . http_build_query($fields);
    }

    function reciver($data, $system, $status, $mr)
    {
        if (!empty($system) && !empty($this->Merchants->config['merchants'][$system])) {
            $this->Merchants->current = $system;
        }
        $result = $this->{'reciver' . $this->current}($data, $status);
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

    function reciverPayeer($data, $status)
    {
        $config = $this->config['merchants']['Payeer'];
        $result['status'] = 'error';
        if (isset($_POST['m_operation_id']) && isset($_POST['m_sign'])) {
            $m_key = $config['secret'];
            $arHash = array($_POST['m_operation_id'],
                $_POST['m_operation_ps'],
                $_POST['m_operation_date'],
                $_POST['m_operation_pay_date'],
                $_POST['m_shop'],
                $_POST['m_orderid'],
                $_POST['m_amount'],
                $_POST['m_curr'],
                $_POST['m_desc'],
                $_POST['m_status'],
                $m_key);
            $sign_hash = strtoupper(hash('sha256', implode(':', $arHash)));
            if ($_POST['m_sign'] == $sign_hash && $_POST['m_status'] == 'success') {
                $result['callback'] = $_POST['m_orderid'] . '|success';
                $result['payId'] = $data["m_orderid"];
                $result['status'] = 'success';
            } else {
                $result['callback'] = $_POST['m_orderid'] . '|error';
            }
        }

        return $result;
    }

    function reciverRobokassa($data, $status)
    {
        $config = $this->config['merchants']['Robokassa'];

        $hashGenerated = md5("{$data['OutSum']}:{$data['InvId']}:{$config['pass2']}");

        $result['payId'] = $data["InvId"];
        $result['status'] = 'error';
        if (strtolower($data['SignatureValue']) == $hashGenerated)
            $result['status'] = 'success';

        return $result;
    }

    function reciverW1($data, $status)
    {
        $skey = $this->config['merchants']['W1']['secret'];
        // Функция, которая возвращает результат в Единую кассу

        function print_answer($result, $description)
        {
            $print = "WMI_RESULT=" . strtoupper($result) . "&";
            $print .= "WMI_DESCRIPTION=" . urlencode($description);
            return $print;
        }

        // Проверка наличия необходимых параметров в POST-запросе

        if (!isset($data["WMI_SIGNATURE"]))
            $result['callback'] = print_answer("Retry", "Отсутствует параметр WMI_SIGNATURE");

        if (!isset($data["WMI_PAYMENT_NO"]))
            $result['callback'] = print_answer("Retry", "Отсутствует параметр WMI_PAYMENT_NO");

        if (!isset($data["WMI_ORDER_STATE"]))
            $result['callback'] = print_answer("Retry", "Отсутствует параметр WMI_ORDER_STATE");

        // Извлечение всех параметров POST-запроса, кроме WMI_SIGNATURE
        $params = [];
        foreach ($data as $name => $value) {
            if ($name !== "WMI_SIGNATURE")
                $params[$name] = $value;
        }

        // Сортировка массива по именам ключей в порядке возрастания
        // и формирование сообщения, путем объединения значений формы

        uksort($params, "strcasecmp");
        $values = "";

        foreach ($params as $name => $value) {
            //Конвертация из текущей кодировки (UTF-8)
            //необходима только если кодировка магазина отлична от Windows-1251
            $value = iconv("utf-8", "windows-1251", $value);
            $values .= $value;
        }

        // Формирование подписи для сравнения ее с параметром WMI_SIGNATURE

        $signature = base64_encode(pack("H*", md5($values . $skey)));

        //Сравнение полученной подписи с подписью W1

        if (!empty($data["WMI_SIGNATURE"]) && $signature == $data["WMI_SIGNATURE"]) {
            if (strtoupper($data["WMI_ORDER_STATE"]) == "ACCEPTED") {
                // вызываем функцию обработки в случае успеха

                $result['callback'] = print_answer("Ok", "Заказ #" . $data["WMI_PAYMENT_NO"] . " оплачен!");
                $result['payId'] = $data["WMI_PAYMENT_NO"];
                $result['status'] = 'success';
                return $result;
            } else {
                // Случилось что-то странное, пришло неизвестное состояние заказа

                $result['callback'] = print_answer("Retry", "Неверное состояние " . $data["WMI_ORDER_STATE"]);
            }
        } else {
            // Подпись не совпадает, возможно вы поменяли настройки интернет-магазина
            $result['callback'] = print_answer("Retry", "Неверная подпись " . (!empty($data["WMI_SIGNATURE"]) ? $data["WMI_SIGNATURE"] : 'empty'));
        }
        return $result;
    }

}
