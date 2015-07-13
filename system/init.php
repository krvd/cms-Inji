<?php

/**
 * Start system core
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
session_start();

define('INJI_DOMAIN_NAME', $_SERVER['SERVER_NAME']);

spl_autoload_register(function($class_name) {
    if (file_exists(INJI_SYSTEM_DIR . '/Inji/' . $class_name . '.php')) {
        include_once INJI_SYSTEM_DIR . '/Inji/' . $class_name . '.php';
    }
});

//load core
Inji::$inst = new Inji();
Inji::$config = Config::system();

$apps = Config::custom(INJI_PROGRAM_DIR . '/apps.php');
$finalApp = NULL;
foreach ($apps as $app) {
    if ($app['default'] && !$finalApp) {
        $finalApp = $app;
    }
    if (preg_match("!{$app['route']}!i", INJI_DOMAIN_NAME)) {
        $finalApp = $app;
        break;
    }
}
if (!$finalApp) {
    $finalApp = [
        'name' => INJI_DOMAIN_NAME,
        'dir' => INJI_DOMAIN_NAME,
        'installed' => false,
        'default' => true,
        'route' => INJI_DOMAIN_NAME,
    ];
}
App::$cur = new App($finalApp);

$params = Tools::uriParse($_SERVER['REQUEST_URI']);

App::$cur->type = 'app';
App::$cur->path = INJI_PROGRAM_DIR . '/' . App::$cur->dir;
App::$cur->params = $params;
App::$cur->config = Config::app(App::$cur);
App::$primary = App::$cur;

if (!empty($params[0]) && file_exists(INJI_SYSTEM_DIR . '/program/' . $params[0] . '/')) {

    App::$primary->params = [];

    App::$cur = new App();
    App::$cur->name = $params[0];
    App::$cur->system = true;
    App::$cur->staticPath = "/" . App::$cur->name . "/static";
    App::$cur->templatesPath = "/" . App::$cur->name . "/static/templates";
    App::$cur->path = INJI_SYSTEM_DIR . '/program/' . App::$cur->name;
    App::$cur->type = 'app' . ucfirst(strtolower(App::$cur->name));
    App::$cur->installed = true;
    App::$cur->params = array_slice($params, 1);
    App::$cur->config = Config::app(App::$cur);
}
$shareConfig = Config::share();
if (App::$cur->name != 'install' && empty($shareConfig['installed'])) {
    Tools::redirect('/install');
}

spl_autoload_register('Router::findClass');
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    include_once __DIR__ . '/../vendor/autoload.php';
}
/*
  if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
  Tools::createDir(__DIR__ . '/../vendor/composer');
  if (!file_exists(__DIR__ . '/../vendor/composer/bin/composer')) {
  if (!file_exists(__DIR__ . '/../vendor/composer.phar')) {
  file_put_contents(__DIR__ . '/../vendor/composerInstall.php', file_get_contents('https://getcomposer.org/installer'));
  $argv = ['install', '--install-dir', __DIR__ . '/../vendor/'];
  header("Location: " . filter_input(INPUT_SERVER, 'REQUEST_URI'));
  include_once __DIR__ . '/../vendor/composerInstall.php';
  }
  $composer = new Phar(__DIR__ . '/../vendor/composer.phar');
  $composer->extractTo(__DIR__ . '/../vendor/composer/');
  }
  //$argv = ['install'];
  include_once __DIR__ . '/../vendor/composer/bin/composer';
  //require __DIR__ . '/../vendor/composer/src/bootstrap.php';
  //$io = new Composer\IO\NullIO();
  //$composer = Composer\Factory::create($io);
  //$installComand = new Composer\Command\InstallCommand();
  //$installComand->execute();

  //var_dump($composer);
  }
  require __DIR__ . '/../vendor/autoload.php';
 */
Module::$cur = Module::resolveModule(App::$cur);

if (Module::$cur === null) {
    INJI_SYSTEM_ERROR('Module not found', true);
}

Controller::$cur = Module::$cur->findController();
if (Controller::$cur === null) {
    INJI_SYSTEM_ERROR('Controller not found', true);
}
if (!empty(App::$cur->config['autoloadModules'])) {
    foreach (App::$cur->config['autoloadModules'] as $module) {
        App::$cur->$module;
    }
}
Controller::$cur->run();
