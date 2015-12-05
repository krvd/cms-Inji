<?php

/**
 * Apps Setup Controller
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
class AppsController extends Controller
{
    function configureAction()
    {
        $appOptions = Apps\App::get($_GET['item_pk']);
        $app = new App();
        $app->name = $appOptions->name;
        $app->system = true;
        $app->staticPath = "/" . $appOptions->dir . "/static";
        $app->templatesPath = "/" . $appOptions->dir . "/static/templates";
        $app->path = INJI_PROGRAM_DIR . '/' . $appOptions->dir;
        $app->type = 'app';
        $app->installed = true;
        $app->params = [];
        $app->config = Config::app($app);
        $modules = Module::getInstalled($app, $app);
        $inputs = [];
        foreach ($modules as $module) {
            $info = Module::getInfo($module);
            if (!empty($info['configure'])) {
                $config = Config::module($module, false, $app);
                foreach ($info['configure'] as $optionName => $params) {
                    if (!empty($_POST[$optionName])) {
                        $config[$optionName] = $_POST[$optionName];
                        Config::save('module', $config, $module, $app);
                    }
                    $input = [];
                    $input['name'] = $optionName;
                    $input['type'] = $params['type'];
                    $input['label'] = $params['label'];
                    $input['options']['value'] = !empty($config[$optionName]) ? $config[$optionName] : '';
                    $input['options']['values'] = ['' => 'Не выбрано'];
                    $input['options']['values'] += $params['model']::getList(['forSelect' => true, 'key' => $params['col']]);
                    $inputs[] = $input;
                }
            }
        }
        if (!empty($_POST)) {
            Tools::redirect('/setup');
        }
        $this->view->page(['data' => compact('inputs')]);
    }

}
