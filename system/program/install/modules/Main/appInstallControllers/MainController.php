<?php

/**
 * Install controller
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class MainController extends Controller {

    function indexAction() {
        $this->db->select('files');
        $config = Config::share();
        if (!empty($config['installed'])) {
            $this->url->redirect('/', 'Приложение уже установлено');
        }
        /*
          $form = new Ui\ActiveForm([
          'inputs' => [
          'connect_name' => ['type' => 'text', 'default' => 'local'],
          'host' => ['type' => 'text', 'default' => 'localhost'],
          'user' => ['type' => 'text', 'default' => 'user'],
          'pass' => ['type' => 'text'],
          'db_name' => ['type' => 'text', 'default' => 'test'],
          ],
          'map' => [
          ['connect_name', 'host'],
          ['user', 'pass', 'db_name']
          ]
          ]);
          $form->checkRequest('dbConfig', function($data) {
          $dbconfig = Config::share('Db');
          $dbconfig['databases'][$data['connect_name']] = [
          'connect_name' => 'база сайта',
          'connect_alias' => $data['connect_name'],
          'connect_driver' => 'Mysql',
          'connect_options' => [
          'host' => $data['host'],
          'user' => $data['user'],
          'pass' => $data['pass'],
          'encoding' => 'utf8',
          'db_name' => $data['db_name'],
          'table_prefix' => 'inji_',
          'port' => '3306',
          'noConnectAbort' => ''
          ]
          ];
          Config::save('share', $dbconfig, 'Db');
          }, false);
         * 
         */
        $this->view->page();
    }

    function modulesAction() {
        if(!empty($_GET['modules'])){
            foreach ($_GET['modules'] as $module){
                $this->modules->install($module);
            }
        }
        $this->view->page();
    }

}
