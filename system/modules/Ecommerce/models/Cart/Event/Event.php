<?php

namespace Ecommerce\Cart;

class Event extends \Model {

    static function relations() {
        return [
            'type' => [
                'model' => 'CartEventType',
                'col' => 'ece_ecet_id',
            ],
        ];
    }

}
