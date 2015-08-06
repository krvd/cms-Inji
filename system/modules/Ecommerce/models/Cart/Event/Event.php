<?php

/**
 * Item Price model
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 */
class CartEvent extends Model
{

    static function index()
    {
        return 'ece_id';
    }

    static function table()
    {
        return 'ecommerce_cart_events';
    }
    static function relations()
    {
        return [
            'type' => [
                'model' => 'CartEventType',
                'col' => 'ece_ecet_id',
            ],
        ];
    }
}
