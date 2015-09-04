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
class Exchange1cController extends adminController {

    function reExchangeAction() {

        $reExchange = Exchange1c\Exchange::get((int) $_GET['item_pk']);

        $exchange = new \Exchange1c\Exchange();
        $exchange->type = $reExchange->type;
        $exchange->save();
        $exchange->path = App::$primary->path . '/tmp/Exchange1c/' . date('Y-m-d') . '/' . $exchange->id;
        Tools::createDir($exchange->path);
        $exchange->save();

        Tools::copyFiles($reExchange->path, $exchange->path);

        foreach ($reExchange->logs as $reLog) {
            if (!in_array($reLog->info, ['import'])) {
                continue;
            }
            $_GET = json_decode($reLog->query, true);

            $log = new \Exchange1c\Exchange\Log();
            $log->exchange_id = $exchange->id;
            $log->type = 'mode';
            $log->info = $reLog->info;
            $log->status = 'process';
            $log->query = $reLog->query;
            $log->save();


            $modeClass = 'Exchange1c\Mode\\' . ucfirst(strtolower($log->info));
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
        Tools::redirect('/admin/exchange1c/Exchange');
    }

}
