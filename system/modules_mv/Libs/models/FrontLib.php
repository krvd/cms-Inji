<?php

namespace Libs;
class FrontLib extends \Model {

    static function table() {
        return 'libs_asseter_front_libs';
    }

    static function index() {
        return 'lafl_id';
    }

    static function relations() {
        return [
            'files' => [
                'type' => 'many',
                'model' => 'Libs\FrontLibFile',
                'col' => 'laflf_lafl_id',
            ],
        ];
    }

}
