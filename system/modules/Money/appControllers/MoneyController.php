<?php

/**
 * Money Controller
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class MoneyController extends Controller
{
    function refillAction($currencyId = 0)
    {
        $currency = null;
        if (!empty($_POST['currency_id'])) {
            $currency = Money\Currency::get((int) $_POST['currency_id']);
        }
        if ($currency && !empty($_POST['amount'])) {
            $pay = new Money\Pay([
                'data' => '',
                'user_id' => \Users\User::$cur->id,
                'currency_id' => $currency->id,
                'sum' => (float) str_replace(',', '.', $_POST['amount']),
                'type' => 'refill',
                'callback_module' => 'Money',
                'callback_method' => 'refillPayRecive'
            ]);
            $pay->save();
            Tools::redirect('/money/merchants/pay/' . $pay->id);
        } else {
            $currencies = Money\Currency::getList(['where' => ['refill', 1], 'forSelect' => true]);
            $this->view->page(['data' => compact('currencies')]);
        }
    }

    function exchangeAction()
    {
        $wallets = $this->module->getUserWallets();
        $currency = !empty($_GET['currency_id']) ? \Money\Currency::get((int) $_GET['currency_id']) : null;
        if ($currency && empty($wallets[$currency->id])) {
            $currency = null;
        }
        $targetCurrency = !empty($_GET['target_currency_id']) ? \Money\Currency::get((int) $_GET['target_currency_id']) : null;
        if ($targetCurrency && empty($wallets[$targetCurrency->id])) {
            $targetCurrency = null;
        }
        $where = [];
        if ($currency) {
            $where[] = ['currency_id', $currency->id];
        } else {
            $where[] = ['currency_id', implode(',', array_keys($wallets)), 'IN'];
        }
        if ($targetCurrency) {
            $where[] = ['target_currency_id', $targetCurrency->id];
        } else {
            $where[] = ['target_currency_id', implode(',', array_keys($wallets)), 'IN'];
        }
        if ($where) {
            $rates = Money\Currency\ExchangeRate::getList(['where' => $where]);
        } else {
            $rates = [];
        }
        if (!empty($_GET['exchange']) && $currency && $targetCurrency && !empty($rates[$_GET['exchange']['rate_id']])) {
            $error = false;
            $rate = $rates[$_GET['exchange']['rate_id']];
            if (empty($_GET['exchange']['give']['amount']) || !(float) $_GET['exchange']['give']['amount']) {
                Msg::add('Укажите сумму которую вы хотите отдать');
                $error = true;
            } else {
                $amount = (float) $_GET['exchange']['give']['amount'];
            }
            if ($amount && $amount > $wallets[$currency->id]->amount) {
                Msg::add('Вы указали сумму большую чем вам доступно');
                $error = true;
            }
            if (!$error) {
                $wallets[$currency->id]->amount -= $amount;
                $wallets[$currency->id]->save();
                $wallets[$targetCurrency->id]->amount += $amount * $rate->rate;
                $wallets[$targetCurrency->id]->save();
                Tools::redirect('/users/cabinet', 'Обмен был успешно проведен');
            }
        }

        $this->view->page(['data' => compact('rates', 'currency', 'targetCurrency', 'wallets')]);
    }

}
