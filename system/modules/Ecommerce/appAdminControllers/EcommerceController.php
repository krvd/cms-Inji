<?php

class EcommerceController extends adminController {

    function dashboardAction() {
        $this->view->setTitle('Онлайн магазин');
        $this->view->page();
    }

    function configureAction() {
        if (!empty($_POST['config'])) {
            $config = App::$cur->ecommerce->config;
            $config['view_empty_warehouse'] = empty($_POST['config']['view_empty_warehouse']) ? false : true;
            Config::save('Module', $config, 'Ecommerce');
            Tools::redirect('/admin/ecommerce/configure', 'Настройки были изменены', 'success');
        }
        $this->view->setTitle('Настройки магазина');
        $this->view->page();
    }

    function itemsCounterAction() {
        echo $this->ecommerce->dayItemCountReport();
        exit();
        $catalogs = Catalog::get_list();
        $this->view->page();
    }

    function recalcMarketingAction() {
        $this->db->where('cub_proof', 0);
        $this->db->delete('catalog_user_bonuses');
        $carts = Cart::get_list(['where' => ['cc_status', 5]]);
        foreach ($carts as $cart) {
            $this->ecommerce->goMarketing($cart);
        }
    }

    function toggleBestAction($ci_id) {
        $item = Item::get((int) $ci_id);
        $return = [
            'best' => 1,
            'ci_id' => (int) $ci_id
        ];
        if ($item->ci_best) {
            $item->ci_best = 0;
            $return['best'] = 0;
        } else {
            $item->ci_best = 1;
        }
        $item->save();
        echo json_encode($return);
    }

    function cleanImagesAction() {
        set_time_limit(0);
        $files = scandir($this->app['parent']['path'] . '/static/mediafiles/images');
        $this->checkFiles($files, '/static/mediafiles/images/');
        $this->url->redirect($this->url->module(), 'Неиспользуемые файлы были удалены', 'success');
    }

    function checkFiles($files, $dir) {
        $i = 0;

        foreach ($files as $fileName) {
            if (in_array($fileName, ['.', '..']))
                continue;

            if (is_dir($this->app['parent']['path'] . $dir . $fileName)) {

                $i += $this->checkFiles(scandir($this->app['parent']['path'] . $dir . $fileName), $dir . $fileName . '/');
                continue;
            }

            $file = File::get($dir . $fileName, 'file_path');

            if (!$file) {
                unlink($this->app['parent']['path'] . $dir . $fileName);
                $i++;
                continue;
            }

            $param = ItemParam::get([['cip_value', $file->file_id], ['cip_cio_id', 13]]);
            $user = User::get($file->file_id, 'user_photo');

            if (!$param && !$user) {
                $file->delete();
                continue;
                $i++;
            }
        }
        return $i;
    }

    function warehousesAction() {
        $this->view->set_title('Склады');
        $dataTable = new DataTable('Warehouse', $_GET);
        $this->view->page(compact('dataTable'));
    }

    function deliverysAction() {
        $this->view->set_title('Виды доставки');
        $dataTable = new DataTable('Delivery', $_GET);
        $this->view->page(compact('dataTable'));
    }

    function payTypesAction() {
        $this->view->set_title('Виды оплат');
        $dataTable = new DataTable('CartPayType', $_GET);
        $this->view->page(compact('dataTable'));
    }

    function recalcTreeAction() {
        set_time_limit(0);
        Inji::app()->Log->stop();
        $this->ecommerce->recalcCatalogTree();
        $this->url->redirect($this->url->module() . '/configure', 'Данные о зависимостях обновлены');
    }

    function reBlockIndexAction() {
        set_time_limit(0);
        $carts = Cart::get_list();
        Inji::app()->Log->stop();
        foreach ($carts as $cart) {
            $cart->save();
        }
        $this->url->redirect($this->url->module() . '/configure', 'Данные о блокировках обновлены');
    }

    function reSearchIndexAction() {
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

    function ordersAction() {
        $this->view->set_title('Заказы');
        $dataTable = new DataTable('Cart', $_GET, ['actions' => [], 'listOptions' => ['where' => [['cc_status', '2,3,6', 'in']]]]);
        $this->view->page('orders', compact('dataTable'));
    }

    function parseWebAction($site = '', $catalogNum = '') {
        set_time_limit(0);
        if ($site) {
            $catalogs = $this->Ecommerce->$site->getCatalogs($catalogNum);
        }
        $this->view->page(compact('site', 'catalogs'));
    }

    function processParseWebAction($site = '', $catalogNum = '') {
        set_time_limit(0);
        if ($site) {
            echo $this->Ecommerce->$site->processParseWeb($catalogNum);
        }
    }

}
