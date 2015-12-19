<?php

/**
 * Money app Controller
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class MoneyController extends Controller
{
    function transferAction()
    {
        $transfer = new Money\Transfer();
        $form = new Ui\ActiveForm($transfer, 'transfer');
        $transferId = $form->checkRequest();
        if ($transferId) {
            $transfer = Money\Transfer::get($transferId);
            $transfer->user_id = \Users\User::$cur->id;
            $transfer->code = Tools::randomString();
            $transfer->save();
            $wallets = $this->money->getUserWallets();
            $block = new Money\Wallet\Block();
            $block->wallet_id = $wallets[$transfer->currency_id]->id;
            $block->amount = $transfer->amount;
            $block->comment = 'Заблокированно на перевод средств для ' . $transfer->toUser->name();
            $block->data = 'Money\Transfer:' . $transfer->id;
            $wallets[$transfer->currency_id]->diff(-$transfer->amount, 'Перевод средств для ' . $transfer->toUser->name());
            $block->save();
            $from = 'noreply@' . INJI_DOMAIN_NAME;
            $to = \Users\User::$cur->mail;
            $subject = 'Подтверждение перевода';
            $text = 'Чтобы подтвержить перевод №' . $transfer->id . ' введите код <b>' . $transfer->code . '</b> на <a href = "http://' . INJI_DOMAIN_NAME . '/money/confirmTransfer/' . $transfer->id . '?code=' . $transfer->code . '">странице</a> перевода';
            Tools::sendMail($from, $to, $subject, $text);
            Tools::redirect('/money/confirmTransfer/' . $transfer->id);
        }
        $this->view->setTitle('Перевод средств');
        $this->view->page(['data' => compact('form')]);
    }

    function confirmTransferAction($transferId = 0)
    {
        $transfer = Money\Transfer::get((int) $transferId);
        if (!$transfer || $transfer->user_id != \Users\User::$cur->id || $transfer->complete || $transfer->canceled) {
            Tools::redirect('/', 'Такой перевод не найден');
        }
        if (!empty($_POST['code'])) {
            if ($transfer->code != $_POST['code']) {
                Msg::add('Код не совпадает', 'danger');
            } else {
                $transfer->complete = true;
                $block = Money\Wallet\Block::get('Money\Transfer:' . $transfer->id, 'data');
                $block->delete();
                $wallets = $this->money->getUserWallets($transfer->to_user_id);
                $wallets[$transfer->currency_id]->diff($transfer->amount, 'Перевод средств от ' . $transfer->user->name());
                Tools::redirect('/users/cabinet', 'Перевод был успешно завершен', 'success');
            }
        }
        $this->view->setTitle('Подтверждение перевода средств');
        $this->view->page(['data' => compact('transfer')]);
    }

    function cancelTransferAction($transferId = 0)
    {
        $transfer = Money\Transfer::get((int) $transferId);
        if (!$transfer || $transfer->user_id != \Users\User::$cur->id || $transfer->complete || $transfer->canceled) {
            Tools::redirect('/', 'Такой перевод не найден');
        }
        $transfer->canceled = true;
        $block = Money\Wallet\Block::get('Money\Transfer:' . $transfer->id, 'data');
        $block->delete();
        $wallets = $this->money->getUserWallets();
        $wallets[$transfer->currency_id]->diff($transfer->amount, 'Отмена перевода средств');
        $transfer->save();
        Tools::redirect('/users/cabinet', 'Перевод был успешно отменен', 'success');
    }

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
            $this->view->setTitle('Пополнение счета');
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
            if (!empty($amount) && $amount > $wallets[$currency->id]->amount) {
                Msg::add('Вы указали сумму большую чем вам доступно');
                $error = true;
            }
            if (!$error) {
                $wallets[$currency->id]->diff(-$amount, 'Обмен валюты на ' . $targetCurrency->name());
                $wallets[$targetCurrency->id]->diff($amount * $rate->rate, 'Обмне валюты с ' . $currency->name());
                Tools::redirect('/users/cabinet', 'Обмен был успешно проведен');
            }
        }
        $this->view->setTitle('Обмен валюты');
        $this->view->page(['data' => compact('rates', 'currency', 'targetCurrency', 'wallets')]);
    }

    function walletPayAction($payId, $walletId)
    {
        $pay = Money\Pay::get((int) $payId);
        if (!$pay || $pay->user_id != \Users\User::$cur->id) {
            Tools::redirect('/money/merchants/pay/', 'Такой счет не найден');
        }
        $wallet = Money\Wallet::get((int) $walletId);
        if (!$wallet || $wallet->user_id != \Users\User::$cur->id) {
            Tools::redirect('/money/merchants/pay/' . $pay->id, 'Такой кошелек не найден');
        }
        if ($pay->sum > $wallet->amount) {
            Tools::redirect('/money/merchants/pay/' . $pay->id, 'На вашем счете недостаточно средств');
        }
        $wallet->diff(-$pay->sum, 'Оплата счета №' . $payId);
        $statuses = \Money\Pay\Status::getList(['key' => 'code']);
        if (!empty($statuses['success'])) {
            $pay->pay_status_id = $statuses['success']->id;
        }
        $pay->date_recive = date('Y-m-d H:i:s');
        $pay->save();
        if ($pay->callback_module && $pay->callback_method) {
            App::$cur->{$pay->callback_module}->{$pay->callback_method}(['status' => 'success', 'payId' => $pay->id, 'pay' => $pay]);
        }
        Tools::redirect('/users/cabinet', 'Вы успешно оплатили счет', 'success');
    }

}
