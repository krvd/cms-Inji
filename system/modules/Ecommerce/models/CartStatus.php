<?php

/**
 * Item Price model
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 */
class CartStatus extends Model
{

    static function index()
    {
        return 'ccs_id';
    }

    static function table()
    {
        return 'catalog_cart_statuses';
    }

}
