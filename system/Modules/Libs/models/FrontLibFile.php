<?php

/**
 * Description of FrontLib
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 */
class FrontLibFile extends Model {

    static function table() {
        return 'libs_asseter_front_lib_files';
    }

    static function index() {
        return 'laflf_id';
    }


}
