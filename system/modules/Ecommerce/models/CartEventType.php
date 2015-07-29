<?php

/**
 * Item Price model
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 */
class CartEventType extends Model
{

    static function index()
    {
        return 'ecet_id';
    }

    static function table()
    {
        return 'ecommerce_cart_event_type';
    }
}
