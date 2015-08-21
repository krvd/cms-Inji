<?php

/**
 * Item name
 *
 * Info
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

/**
 * Description of Exchange1cController
 *
 * @author inji
 */
class Exchange1cController extends Controller {

    function indexAction() {
        set_time_limit(0);
        if (empty($_SESSION['auth'])) {
            if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
                header('WWW-Authenticate: Basic realm="exchange1c"');
                header('HTTP/1.0 401 Unauthorized');
                $this->module->response('failure', 'Not isset PHP_AUTH_USER or PHP_AUTH_PW');
            }
            if ($_SERVER['PHP_AUTH_USER'] !== $this->module->config['user']['login'] || $_SERVER['PHP_AUTH_PW'] !== $this->module->config['user']['password']) {
                $this->module->response('failure', 'User or password not correct');
            }
        }
        if (empty($_GET['mode'])) {
            $this->module->response('failure', 'Mode missing');
        }
        $exchange = \Exchange1c\Exchange::get(['session', session_id()]);
        if (!$exchange) {
            $exchange = new \Exchange1c\Exchange();
            $exchange->type = $_GET['type'];
            $exchange->session = session_id();
            $exchange->save();
            $exchange->path = App::$cur->path . '/tmp/Exchange1c/' . date('Y-m-d') . '/' . $exchange->id;
            $exchange->save();
        }

        $log = new \Exchange1c\Exchange\Log();
        $log->exchange_id = $exchange->id;
        $log->type = 'mode';
        $log->info = $_GET['mode'];
        $log->status = 'process';
        $log->save();
        $modeClass = 'Exchange1c\Mode\\' . ucfirst(strtolower($_GET['mode']));
        if (!class_exists($modeClass)) {
            $log->status = 'failure';
            $log->info = 'mode class ' . $modeClass . ' not found';
            $log->date_end = date('Y-m-d H:i:s');
            $log->save();
        }
        $mode = new $modeClass;
        $mode->exchange = $exchange;
        $mode->log = $log;
        $mode->process();
    }

    function indexOld() {
        set_time_limit(0);

        function addToXml($xml, $parent, $nodeName, $text) {
            $node = $parent->appendChild($xml->createElement($nodeName));
            $node->appendChild($xml->createTextNode($text));
            return $node;
        }

        function return_bytes($val) {
            $val = trim($val);
            $last = strtolower($val[strlen($val) - 1]);
            switch ($last) {
                // Модификатор 'G' доступен, начиная с PHP 5.1.0
                case 'g':
                    $val *= 1024;
                case 'm':
                    $val *= 1024;
                case 'k':
                    $val *= 1024;
            }

            return $val;
        }

        if (empty($_SESSION['auth'])) {
            if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
                header('WWW-Authenticate: Basic realm="exchange1c"');
                header('HTTP/1.0 401 Unauthorized');
                echo 'autorization false';
                exit;
            }
            if ($_SERVER['PHP_AUTH_USER'] !== 'sync' || $_SERVER['PHP_AUTH_PW'] !== 'SGjskljskldsdfsdfjsjk') {
                echo 'autorization false';
                exit;
            }
        }
        $exchangeEvent = ExchangeLog::get(['el_session', session_id()]);
        if (!$exchangeEvent) {
            $exchangeEvent = new ExchangeLog();
            $exchangeEvent->el_type = $_GET['type'];
            $exchangeEvent->save();
            $exchangeEvent->el_path = $this->app['path'] . '/tmp/1c_exchange/' . date('Y-m-d') . '/' . $exchangeEvent->el_id;
        }
        $exchangeEvent->el_last_mode = $_GET['mode'];
        $exchangeEvent->el_session = session_id();
        $exchangeEvent->el_status = 'process';
        $exchangeEvent->save();
        switch ($_GET['mode']) {
            case 'checkauth':
                echo "success\n";
                echo session_name() . "\n";
                echo session_id();
                $_SESSION['auth'] = true;
                $exchangeEvent->el_status = 'success';
                $exchangeEvent->save();
                break;
            case 'init':
                echo "zip=no\n";
                echo 'file_limit=' . return_bytes(ini_get('post_max_size'));
                $exchangeEvent->el_status = 'success';
                $exchangeEvent->save();
                break;
            case 'query':
                header("Content-Type: text/xml");
                header("Expires: Thu, 19 Feb 1998 13:24:18 GMT");
                header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                header("Cache-Control: no-cache, must-revalidate");
                header("Cache-Control: post-check=0,pre-check=0");
                header("Cache-Control: max-age=0");
                header("Pragma: no-cache");
                $xml = new DOMDocument('1.0', 'windows-1251');

                $xml->formatOutput = true;

                $root = $xml->createElement('КоммерческаяИнформация');
                $root->setAttribute("ВерсияСхемы", "2.03");
                $root->setAttribute("ДатаФормирования", date('Y-m-d'));
                $root = $xml->appendChild($root);

                $carts = Cart::get_list(['where' => ['cc_status', '3']]);

                foreach ($carts as $cart) {
                    $doc = $xml->createElement('Документ');
                    $dateTime = new DateTime($cart->cc_date);
                    $statusDateTime = new DateTime($cart->cc_date_status != '0000-00-00 00:00:00' ? $cart->cc_date_status : $cart->cc_date);
                    $items = $cart->cartItems;
                    if (!$items) {
                        continue;
                    }
                    $summ = 0;

                    $goodss = $xml->createElement('Товары');

                    foreach ($items as $cartitem) {
                        $item = $cartitem->item;
                        $price = $cartitem->price;

                        $summ += $price->ciprice_price * $cartitem->cci_count;
                        $goods = $goodss->appendChild($xml->createElement('Товар'));

                        $id1c = $this->Exchange1c->get1cId($item->ci_id, 'item');
                        if ($id1c) {
                            addToXml($xml, $goods, 'Ид', $id1c);
                        }
                        addToXml($xml, $goods, 'Наименование', $item->ci_name);
                        $one = addToXml($xml, $goods, 'БазоваяЕдиница', 'шт');
                        $one->setAttribute("Код", "796");
                        $one->setAttribute("НаименованиеПолное", "Штука");
                        $one->setAttribute("МеждународноеСокращение", "PCE");
                        addToXml($xml, $goods, 'ЦенаЗаЕдиницу', $price->ciprice_price);
                        addToXml($xml, $goods, 'Количество', $cartitem->cci_count);
                        addToXml($xml, $goods, 'Сумма', $price->ciprice_price * $cartitem->cci_count);
                        $reqs = $goods->appendChild($xml->createElement('ЗначенияРеквизитов'));

                        $req = $reqs->appendChild($xml->createElement('ЗначениеРеквизита'));
                        addToXml($xml, $req, 'Наименование', 'ВидНоменклатуры');
                        addToXml($xml, $req, 'Значение', 'Товар');

                        $req = $reqs->appendChild($xml->createElement('ЗначениеРеквизита'));
                        addToXml($xml, $req, 'Наименование', 'ТипНоменклатуры');
                        addToXml($xml, $req, 'Значение', 'Товар');
                    }
                    if ($cart->delivery) {
                        if ($summ < $cart->delivery->cd_max_cart_price) {
                            $summ += $cart->delivery->cd_price;

                            $goods = $goodss->appendChild($xml->createElement('Товар'));
                            addToXml($xml, $goods, 'Наименование', 'Доставка');
                            $one = addToXml($xml, $goods, 'БазоваяЕдиница', 'шт');
                            $one->setAttribute("Код", "796");
                            $one->setAttribute("НаименованиеПолное", "Штука");
                            $one->setAttribute("МеждународноеСокращение", "PCE");
                            addToXml($xml, $goods, 'ЦенаЗаЕдиницу', $cart->delivery->cd_price);
                            addToXml($xml, $goods, 'Количество', 1);
                            addToXml($xml, $goods, 'Сумма', $cart->delivery->cd_price);
                            $reqs = $goods->appendChild($xml->createElement('ЗначенияРеквизитов'));

                            $req = $reqs->appendChild($xml->createElement('ЗначениеРеквизита'));
                            addToXml($xml, $req, 'Наименование', 'ВидНоменклатуры');
                            addToXml($xml, $req, 'Значение', 'Услуга');

                            $req = $reqs->appendChild($xml->createElement('ЗначениеРеквизита'));
                            addToXml($xml, $req, 'Наименование', 'ТипНоменклатуры');
                            addToXml($xml, $req, 'Значение', 'Услуга');
                        }
                    }

                    addToXml($xml, $doc, 'Ид', $cart->cc_id);
                    addToXml($xml, $doc, 'Номер', $cart->cc_id);
                    addToXml($xml, $doc, 'Дата', $statusDateTime->format('Y-m-d'));
                    addToXml($xml, $doc, 'ХозОперация', 'Заказ товара');
                    addToXml($xml, $doc, 'Роль', 'Продавец');
                    addToXml($xml, $doc, 'Валюта', 'руб');
                    addToXml($xml, $doc, 'Курс', '1');
                    addToXml($xml, $doc, 'Сумма', $summ);

                    $agents = $doc->appendChild($xml->createElement('Контрагенты'));
                    $agent = $agents->appendChild($xml->createElement('Контрагент'));
                    $user = $cart->user;
                    addToXml($xml, $agent, 'Ид', $user->user_id);
                    addToXml($xml, $agent, 'ИдРодителя', $user->user_parent_id);
                    addToXml($xml, $agent, 'Наименование', $user->user_name);
                    addToXml($xml, $agent, 'Роль', $user->role->role_name);
                    addToXml($xml, $agent, 'ПолноеНаименование', $user->user_name);
                    $reg = $agent->appendChild($xml->createElement('АдресРегистрации'));
                    addToXml($xml, $reg, 'Представление', '');

                    $presents = $agent->appendChild($xml->createElement('Представители'));
                    $present = $presents->appendChild($xml->createElement('Представитель'));
                    $presentAgent = $present->appendChild($xml->createElement('Контрагент'));

                    addToXml($xml, $presentAgent, 'Отношение', 'Контактное лицо');
                    addToXml($xml, $presentAgent, 'Ид', $user->user_id);
                    addToXml($xml, $presentAgent, 'Наименование', $user->user_name);

                    addToXml($xml, $doc, 'Время', $statusDateTime->format('H:i:s'));
                    addToXml($xml, $doc, 'Комментарий', $cart->cc_comment);
                    $goodss = $doc->appendChild($goodss);

                    $reqs = $doc->appendChild($xml->createElement('ЗначенияРеквизитов'));
                    $req = $reqs->appendChild($xml->createElement('ЗначениеРеквизита'));
                    addToXml($xml, $req, 'Наименование', 'Метод оплаты');
                    if ($cart->payType) {
                        addToXml($xml, $req, 'Значение', $cart->payType->cpt_name);
                    } else {
                        addToXml($xml, $req, 'Значение', 'Наличный расчет');
                    }

                    $req = $reqs->appendChild($xml->createElement('ЗначениеРеквизита'));
                    addToXml($xml, $req, 'Наименование', 'Заказ оплачен');
                    addToXml($xml, $req, 'Значение', ($cart->cc_payed) ? 'true' : 'false');

                    $req = $reqs->appendChild($xml->createElement('ЗначениеРеквизита'));
                    addToXml($xml, $req, 'Наименование', 'Доставка разрешена');
                    addToXml($xml, $req, 'Значение', 'false');

                    $req = $reqs->appendChild($xml->createElement('ЗначениеРеквизита'));
                    addToXml($xml, $req, 'Наименование', 'Отменен');
                    addToXml($xml, $req, 'Значение', 'false');

                    $req = $reqs->appendChild($xml->createElement('ЗначениеРеквизита'));
                    addToXml($xml, $req, 'Наименование', 'Адрес доставки');
                    addToXml($xml, $req, 'Значение', 'г. ' . $cart->cc_city . ', ' . $cart->cc_street . ' ' . $cart->cc_house . ' ' . $cart->cc_kvart);

                    $req = $reqs->appendChild($xml->createElement('ЗначениеРеквизита'));
                    addToXml($xml, $req, 'Наименование', 'Дата доставки');
                    addToXml($xml, $req, 'Значение', $cart->cc_day);

                    $req = $reqs->appendChild($xml->createElement('ЗначениеРеквизита'));
                    addToXml($xml, $req, 'Наименование', 'Время доставки');
                    addToXml($xml, $req, 'Значение', $cart->cc_time);

                    $req = $reqs->appendChild($xml->createElement('ЗначениеРеквизита'));
                    addToXml($xml, $req, 'Наименование', 'Телефон');
                    addToXml($xml, $req, 'Значение', $cart->cc_tel);

                    $req = $reqs->appendChild($xml->createElement('ЗначениеРеквизита'));
                    addToXml($xml, $req, 'Наименование', 'Статус заказа');
                    addToXml($xml, $req, 'Значение', $cart->status->ccs_name);

                    $req = $reqs->appendChild($xml->createElement('ЗначениеРеквизита'));
                    addToXml($xml, $req, 'Наименование', 'Дата изменения статуса');
                    addToXml($xml, $req, 'Значение', $statusDateTime->format('Y-m-d H:i:s'));

                    $req = $reqs->appendChild($xml->createElement('ЗначениеРеквизита'));
                    addToXml($xml, $req, 'Наименование', 'Выгодные рубли');
                    addToXml($xml, $req, 'Значение', $cart->cc_bonus_used);

                    $doc = $root->appendChild($doc);
                }

                echo $xml->saveXML();
                exit();
                break;
            case 'success':
                echo 'success';
                $exchangeEvent->el_status = 'success';
                $exchangeEvent->save();
                break;
            case 'file':
                $this->_FS->create_dir($exchangeEvent->el_path);
                $exchangeEvent->el_file_name = $_GET['filename'];
                $exchangeEvent->save();
                $dir = $exchangeEvent->el_path;

                if ($_GET['type'] == 'catalog') {
                    if (strpos($_GET['filename'], 'import_files') !== false) {
                        $dir_name = substr($_GET['filename'], 0, strrpos($_GET['filename'], "/") + 1);
                        $this->_FS->create_dir($dir . '/' . $dir_name);
                    }
                } elseif ($_GET['type'] == 'sale') {
                    
                }
                if ($_GET['filename'] == 'marketingProfit.csv') {
                    $this->Exchange1c->parseProfit(file_get_contents("php://input"));
                }
                if (false === file_put_contents($dir . '/' . $_GET['filename'], file_get_contents("php://input"))) {
                    echo "failure\n";
                    echo 'Не удалось сохранить файл ' . $_GET['filename'];
                    $exchangeEvent->el_status = 'failure';
                    $exchangeEvent->save();
                } else {
                    $exchangeEvent->el_status = 'success';
                    $exchangeEvent->save();
                    echo 'success';
                }
                break;
            case 'import':
                $exchangeEvent->el_file_name = $_GET['filename'];
                $exchangeEvent->save();
                $dir = $exchangeEvent->el_path;
                $file = new SimpleXMLElement(file_get_contents($dir . '/' . $_GET['filename']));
                $this->Exchange1c->parseCommerceML2($file, 'exchange', $exchangeEvent, $dir);
                echo 'success';
                break;
            default:
                exit('mode error');
                break;
        }
    }

}
