<?php

/**
 * INJI CMS 4.0.0 dev
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */
//set locale
setlocale(LC_ALL, 'ru_RU.UTF-8', 'rus_RUS.UTF-8', 'Russian_Russia.65001');
setlocale(LC_NUMERIC, 'C');

// time start
define('INJI_TIME_START', microtime(true));
// system files dir
define('INJI_SYSTEM_DIR', __DIR__ . '/system');
// sites files dir
define('INJI_PROGRAM_DIR', __DIR__ . '/program');

// check base config
if (!file_exists(INJI_SYSTEM_DIR) || !is_dir(INJI_SYSTEM_DIR)) {
    INJI_SYSTEM_ERROR('Error in base config: INJI_SYSTEM_DIR not correct', true);
}
if (!file_exists(INJI_PROGRAM_DIR) || !is_dir(INJI_PROGRAM_DIR)) {
    INJI_SYSTEM_ERROR('Error in base config: INJI_PROGRAM_DIR not correct', true);
}


require_once( INJI_SYSTEM_DIR . '/init.php' );
/**
 * System error messages
 */
function INJI_SYSTEM_ERROR($msg, $fatal = false)
{
    if ($fatal) {
        exit("<div style = 'text-align:center;font-size:20px;margin-top:25%;'>{$msg}</div>");
    } else {
        echo "<div style = 'text-align:center;font-size:20px;margin-top:25%;'>{$msg}</div>";
    }
}
