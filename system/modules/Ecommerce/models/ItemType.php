<?php

/**
 * Item Param model
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 */
class ItemType extends Model
{

    static function index()
    {
        return 'cit_id';
    }

    static function table()
    {
        return 'catalog_item_types';
    }

}
