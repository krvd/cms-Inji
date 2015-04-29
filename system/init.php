<?php

/**
 * Start system core
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
//load core
include INJI_SYSTEM_DIR . '/Inji/Inji.php';
$inji = new Inji();
$inji->setApp($inji);
spl_autoload_register([$inji, 'loadClass']);
define('INJI_DOMAIN_NAME', $_SERVER['SERVER_NAME']);
$inji->curApp = Inji::app()->router->resolveApp(INJI_DOMAIN_NAME, $_SERVER['REQUEST_URI']);
$inji->curModule = Inji::app()->router->resolveModule($inji->curApp);
if(!$inji->curModule){
    INJI_SYSTEM_ERROR('Module not found', true);
}
$inji->curController = $inji->curModule->findController();
$inji->curController->run();