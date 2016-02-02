<?php

/**
 * Money admin Controller
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class MoneyController extends adminController
{
    public function manualClosePayAction($payId)
    {
        $pay = \Money\Pay::get((int) $payId);
        $result = new Server\Result();
        if ($pay && $pay->pay_status_id == 1) {
            $pay->pay_status_id = 2;
            $pay->save();
            if ($pay->callback_module && $pay->callback_method) {
                \App::$primary->{$pay->callback_module}->{$pay->callback_method}(['status' => 'success', 'payId' => $pay->id, 'pay' => $pay]);
            }
            $result->successMsg = 'Счет был проведен';
            $result->send();
        }
        $result->success = false;
        $result->content = 'Такой счет не найден';
        $result->send();
    }

}
