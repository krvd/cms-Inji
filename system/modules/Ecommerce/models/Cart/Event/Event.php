<?php

namespace Ecommerce\Cart;

class Event extends \Model
{
    static function relations()
    {
        return [
            'type' => [
                'model' => 'Ecommerce\Cart\Event\Type',
                'col' => 'cart_event_type_id',
            ],
            'cart' => [
                'model' => 'Ecommerce\Cart',
                'col' => 'cart_id',
            ],
        ];
    }

    function afterSave()
    {
        $this->cart->date_last_activ = $this->date_create;
        $this->cart->save();
    }

}
