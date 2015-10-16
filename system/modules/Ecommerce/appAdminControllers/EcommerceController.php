<?php

/**
 * Ecommerce admin controller
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class EcommerceController extends adminController
{
    function dashboardAction()
    {
        $this->view->setTitle('Онлайн магазин');
        $this->view->page();
    }

    function configureAction()
    {
        if (!empty($_POST['config'])) {
            $config = App::$cur->ecommerce->config;
            $config['view_empty_warehouse'] = empty($_POST['config']['view_empty_warehouse']) ? false : true;
            $config['view_empty_image'] = empty($_POST['config']['view_empty_image']) ? false : true;
            $config['sell_empty_warehouse'] = empty($_POST['config']['sell_empty_warehouse']) ? false : true;
            $config['sell_over_warehouse'] = empty($_POST['config']['sell_over_warehouse']) ? false : true;
            $config['notify_mail'] = $_POST['config']['notify_mail'];
            Config::save('module', $config, 'Ecommerce');
            Tools::redirect('/admin/ecommerce/configure', 'Настройки были изменены', 'success');
        }
        $managers = [
            'Ecommerce\Delivery',
            'Ecommerce\PayType',
            'Ecommerce\Warehouse',
            'Ecommerce\Unit',
            'Ecommerce\Card',
            'Ecommerce\Discount',
            'Ecommerce\Item\Type',
            'Ecommerce\Item\Option',
            'Ecommerce\Item\Offer\Price\Type',
            'Ecommerce\UserAdds\Field',
        ];
        $this->view->setTitle('Настройки магазина');
        $this->view->page(['data' => compact('managers')]);
    }

    function reBlockIndexAction()
    {
        set_time_limit(0);
        $carts = Cart::get_list();
        Inji::app()->Log->stop();
        foreach ($carts as $cart) {
            $cart->save();
        }
        $this->url->redirect($this->url->module() . '/configure', 'Данные о блокировках обновлены');
    }

    function reSearchIndexAction()
    {
        set_time_limit(0);
        $items = Item::get_list();
        Inji::app()->Log->stop();
        foreach ($items as $key => $item) {
            $item->save();
            unset($items[$key]);
            unset($item);
        }
        $this->view->page();
        $this->url->redirect($this->url->module() . '/configure', 'Поисковый индекс обновлен');
    }

    function parseWebAction($site = '', $catalogNum = '')
    {
        set_time_limit(0);
        if ($site) {
            $catalogs = $this->Ecommerce->$site->getCatalogs($catalogNum);
        }
        $this->view->page(compact('site', 'catalogs'));
    }

    function processParseWebAction($site = '', $catalogNum = '')
    {
        set_time_limit(0);
        if ($site) {
            echo $this->Ecommerce->$site->processParseWeb($catalogNum);
        }
    }

}
