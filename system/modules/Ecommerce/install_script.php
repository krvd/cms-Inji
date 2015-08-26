<?php

return function ($step = NULL, $params = array()) {

    //Категории
    App::$cur->db->createTable('ecommerce_category', array(
        'category_id' => 'pk',
        //Основные параметры
        'category_parent_id' => 'int(11) UNSIGNED NOT NULL',
        'category_name' => 'varchar(255) NOT NULL',
        'category_description' => 'text NOT NULL',
        'category_image_file_id' => 'int(11) UNSIGNED NOT NULL',
        'category_options_inherit' => 'bool NOT NULL',
        //Системные
        'category_imported' => 'text NOT NULL',
        'category_tree_path' => 'text NOT NULL',
        'category_user_id' => 'int(11) UNSIGNED NOT NULL',
        'category_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ));
    //Типы товаров
    App::$cur->db->createTable('ecommerce_item_type', array(
        'item_type_id' => 'pk',
        //Основные параметры
        'item_type_name' => 'varchar(255) NOT NULL',
        'item_type_code' => 'varchar(255) NOT NULL',
        'item_type_electronic' => 'tinyint(1) NOT NULL',
    ));
    //Товары
    App::$cur->db->createTable('ecommerce_item', [
        'item_id' => 'pk',
        //Основные параметры
        'item_category_id' => 'int(11) UNSIGNED NOT NULL',
        'item_image_file_id' => 'int(11) UNSIGNED NOT NULL',
        'item_name' => 'varchar(255) NOT NULL',
        'item_description' => 'TEXT NOT NULL',
        'item_item_type_id' => 'int(11) UNSIGNED NOT NULL',
        'item_similar' => 'TEXT NOT NULL',
        'item_best' => 'tinyint(1) NOT NULL',
        //Системные
        'item_user_id' => 'int(11) UNSIGNED NOT NULL',
        'item_weight' => 'int(11) UNSIGNED NOT NULL',
        'item_sales' => 'int(11) UNSIGNED NOT NULL',
        'item_imported' => 'TEXT NOT NULL',
        'item_tree_path' => 'TEXT NOT NULL',
        'item_search_index' => 'TEXT NOT NULL',
        'item_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ]);

    //Опции товаров
    App::$cur->db->createTable('ecommerce_item_option', array(
        'item_option_id' => 'pk',
        //Основные параметры
        'item_option_name' => 'varchar(255) NOT NULL',
        'item_option_code' => 'varchar(255) NOT NULL',
        'item_option_type' => 'varchar(255) NOT NULL',
        'item_option_postfix' => 'varchar(255) NOT NULL',
        'item_option_default_val' => 'text NOT NULL',
        'item_option_view' => 'BOOL NOT NULL',
        'item_option_searchable' => 'bool NOT NULL',
        //Системные
        'item_option_weight' => 'int(11) UNSIGNED NOT NULL',
        'item_option_user_id' => 'int(11) UNSIGNED NOT NULL',
        'item_option_advance' => 'text NOT NULL',
        'item_option_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ));

    //Параметры товаров
    App::$cur->db->createTable('ecommerce_item_param', array(
        'item_param_id' => 'pk',
        //Основные параметры
        'item_param_item_id' => 'int(11) UNSIGNED NOT NULL',
        'item_param_item_option_id' => 'int(11) UNSIGNED NOT NULL',
        'item_param_value' => 'text NOT NULL',
        //Системные
        'item_param_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            ), [
        'INDEX ' . App::$cur->db->table_prefix . '_ecommerce_itemOptionRelation (item_param_item_id, item_param_item_option_id)',
        'INDEX ' . App::$cur->db->table_prefix . '_ecommerce_paramItemIndex (item_param_item_id)',
        'INDEX ' . App::$cur->db->table_prefix . '_ecommerce_paramOptionIndex (item_param_item_option_id)'
    ]);
    //связи опций с каталогами
    App::$cur->db->createTable('ecommerce_item_option_relation', array(
        'item_option_relation_id' => 'pk',
        //Основные параметры
        'item_option_relation_category_id' => 'int(11) UNSIGNED NOT NULL',
        'item_option_relation_item_option_id' => 'int(11) UNSIGNED NOT NULL',
        //Системные
        'item_option_relation_weight' => 'int(11) UNSIGNED NOT NULL',
        'item_option_relation_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ));
    //Элементы коллекций опций
    App::$cur->db->createTable('ecommerce_item_option_item', array(
        'item_option_item_id' => 'pk',
        //Основные параметры
        'item_option_item_item_option_id' => 'int(11) UNSIGNED NOT NULL',
        'item_option_item_value' => 'VARCHAR(255) NOT NULL',
        //Системные
        'item_option_item_weight' => 'int(11) UNSIGNED NOT NULL',
        'item_option_item_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ));
    //Типы цен
    App::$cur->db->createTable('ecommerce_item_offer_price_type', array(
        'item_offer_price_type_id' => 'pk',
        //Основные параметры
        'item_offer_price_type_name' => 'varchar(255) NOT NULL',
        'item_offer_price_type_curency' => 'varchar(255) NOT NULL',
        'item_offer_price_type_roles' => 'varchar(255) NOT NULL',
        //Системные
        'item_offer_price_type_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ));
    //Единицы измерения
    App::$cur->db->createTable('ecommerce_unit', [
        'unit_id' => 'pk',
        //Основные параметры
        'unit_name' => 'VARCHAR(255) NOT NULL',
        'unit_code' => 'VARCHAR(255) NOT NULL',
        //Системные
        'unit_international' => 'VARCHAR(255) NOT NULL',
    ]);
    //Торговые предложения
    App::$cur->db->createTable('ecommerce_item_offer', array(
        'item_offer_id' => 'pk',
        //Основные параметры
        'item_offer_item_id' => 'int(11) UNSIGNED NOT NULL',
        'item_offer_name' => 'varchar(255) NOT NULL',
        'item_offer_article' => 'varchar(255) NOT NULL',
        //Системные
        'item_offer_weight' => 'int(11) UNSIGNED NOT NULL',
        'item_offer_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ));
    //Цены
    App::$cur->db->createTable('ecommerce_item_offer_price', array(
        'item_offer_price_id' => 'pk',
        //Основные параметры
        'item_offer_price_item_offer_id' => 'int(11) UNSIGNED NOT NULL',
        'item_offer_price_unit_id' => 'int(11) UNSIGNED NOT NULL',
        'item_offer_price_item_offer_price_type_id' => 'int(11) UNSIGNED NOT NULL',
        'item_offer_price_name' => 'text NOT NULL',
        'item_offer_price_price' => 'decimal(10, 2) NOT NULL',
        'item_offer_price_delivery_weight' => 'int(11) UNSIGNED NOT NULL',
        'item_offer_price_article' => 'varchar(255) NOT NULL',
        'item_offer_price_inpack' => 'int(11) UNSIGNED NOT NULL',
        'item_offer_price_image_file_id' => 'int(11) UNSIGNED NOT NULL',
        //Системные
        'item_offer_price_weight' => 'int(11) UNSIGNED NOT NULL',
        'item_offer_price_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ));
    //Склады
    App::$cur->db->createTable('ecommerce_warehouse', array(
        'warehouse_id' => 'pk',
        //Основные параметры
        'warehouse_name' => 'VARCHAR(255) NOT NULL',
        //Системные
        'warehouse_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ));
    //Количество на складах торговых предложений 
    App::$cur->db->createTable('ecommerce_item_offer_warehouse', array(
        'item_offer_warehouse_id' => 'pk',
        //Основные параметры
        'item_offer_warehouse_warehouse_id' => 'int(11) UNSIGNED NOT NULL',
        'item_offer_warehouse_item_offer_price_id' => 'int(11) UNSIGNED NOT NULL',
        'item_offer_warehouse_count' => 'int(11) NOT NULL',
        //Системные
        'item_offer_warehouse_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            ), [
        'INDEX ' . App::$cur->db->table_prefix . '_ecommerce_warehousePriceIndex (item_offer_warehouse_item_offer_price_id)'
    ]);
    //Типы статусов корзин
    App::$cur->db->createTable('ecommerce_cart_status', array(
        'cart_status_id' => 'pk',
        //Основные параметры
        'cart_status_name' => 'varchar(255) NOT NULL',
        'cart_status_type' => 'varchar(255) NOT NULL',
        'cart_status_code' => 'varchar(255) NOT NULL',
        //Системные
        'cart_status_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ));
    //Адреса доставок
    App::$cur->db->createTable('ecommerce_useradds', array(
        'useradds_id' => 'pk',
        //Основные параметры
        'useradds_user_id' => 'int(11) UNSIGNED NOT NULL',
        'useradds_name' => 'varchar(255) NOT NULL',
        //Системные
        'useradds_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ));
    //Поля адресов доставок
    App::$cur->db->createTable('ecommerce_useradds_field', array(
        'useradds_field_id' => 'pk',
        //Основные параметры
        'useradds_field_name' => 'varchar(255) NOT NULL',
        'useradds_field_type' => 'varchar(255) NOT NULL',
        'useradds_field_required' => 'bool NOT NULL',
        //Системные
        'useradds_field_weight' => 'int(11) UNSIGNED NOT NULL',
        'useradds_field_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ));
    //Значения полей адресов доставок
    App::$cur->db->createTable('ecommerce_useradds_value', array(
        'useradds_value_id' => 'pk',
        //Основные параметры
        'useradds_value_useradds_id' => 'int(11) UNSIGNED NOT NULL',
        'useradds_value_useradds_field_id' => 'int(11) UNSIGNED NOT NULL',
        'useradds_value_value' => 'text NOT NULL',
        //Системные
        'useradds_value_weight' => 'int(11) UNSIGNED NOT NULL',
        'useradds_value_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ));
    //Способы доставки
    App::$cur->db->createTable('ecommerce_delivery', array(
        'delivery_id' => 'pk',
        //Основные параметры
        'delivery_name' => 'varchar(255) NOT NULL',
        'delivery_price' => 'decimal(10,2) NOT NULL',
        'delivery_max_cart_price' => 'decimal(10,2) NOT NULL',
        'delivery_icon_file_id' => 'int(11) UNSIGNED NOT NULL',
        //Системные
        'delivery_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ));
    //Способы оплат
    App::$cur->db->createTable('ecommerce_paytype', [
        'paytype_id' => 'pk',
        'paytype_name' => 'VARCHAR(255) NOT NULL',
        'paytype_icon_file_id' => 'VARCHAR(255) NOT NULL',
    ]);
    //Корзины
    App::$cur->db->createTable('ecommerce_cart', array(
        'cart_id' => 'pk',
        //Основные параметры
        'cart_user_id' => 'int(11) UNSIGNED NOT NULL',
        'cart_useradds_id' => 'int(11) UNSIGNED NOT NULL',
        'cart_cart_status_id' => 'int(11) UNSIGNED NOT NULL',
        'cart_delivery_id' => 'int(11) UNSIGNED NOT NULL',
        'cart_paytype_id' => 'int(11) UNSIGNED NOT NULL',
        'cart_comment' => 'text NOT NULL',
        'cart_day' => 'VARCHAR(255) NOT NULL',
        'cart_time' => 'VARCHAR(255) NOT NULL',
        //Системные
        'cart_sum' => 'decimal(10,2) NOT NULL',
        'cart_payid' => 'int(11) UNSIGNED NOT NULL',
        'cart_read' => 'bool NOT NULL',
        'cart_payed' => 'bool NOT NULL',
        'cart_warehouse_block' => 'bool NOT NULL',
        'cart_exported' => 'bool NOT NULL',
        'cart_date_status' => 'timestamp DEFAULT 0',
        'cart_payed_date' => 'timestamp DEFAULT 0',
        'cart_complete_data' => 'timestamp DEFAULT 0',
        'cart_date_last_activ' => 'timestamp DEFAULT 0',
        'cart_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            ), [
        'INDEX ' . App::$cur->db->table_prefix . '_ecommerce_cartStatusBlock (cart_cart_status_id, cart_warehouse_block)',
        'INDEX ' . App::$cur->db->table_prefix . '_ecommerce_cartStats (cart_cart_status_id)',
        'INDEX ' . App::$cur->db->table_prefix . '_ecommerce_cartBlock (cart_warehouse_block)'
    ]);
    //товары в корзинe
    App::$cur->db->createTable('ecommerce_cart_item', array(
        'cart_item_id' => 'pk',
        //Основные параметры
        'cart_item_cart_id' => 'int(11) UNSIGNED NOT NULL',
        'cart_item_count' => 'int(11) UNSIGNED NOT NULL',
        'cart_item_item_offer_price_id' => 'int(11) UNSIGNED NOT NULL',
        'cart_item_item_id' => 'int(11) UNSIGNED NOT NULL',
        'cart_item_final_price' => 'decimal(10,2) NOT NULL',
        //Системные
        'cart_item_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            ), [
        'INDEX ' . App::$cur->db->table_prefix . '_ecommerce_cartItemCart (cart_item_cart_id)'
    ]);
    //Типы событий корзины
    App::$cur->db->createTable('ecommerce_cart_event_type', array(
        'cart_event_type_id' => 'pk',
        //Основные параметры
        'cart_event_type_name' => 'varchar(255) NOT NULL',
    ));
    //Стандартные типы событий
    App::$cur->db->insert('ecommerce_cart_event_type', [
        'cart_event_type_name' => 'Добавление товара'
    ]);
    App::$cur->db->insert('ecommerce_cart_event_type', [
        'cart_event_type_name' => 'Удаление товара'
    ]);
    App::$cur->db->insert('ecommerce_cart_event_type', [
        'cart_event_type_name' => 'Изменение цены'
    ]);
    App::$cur->db->insert('ecommerce_cart_event_type', [
        'cart_event_type_name' => 'Изменение количества'
    ]);
    //События корзины
    App::$cur->db->createTable('ecommerce_cart_event', array(
        'cart_event_id' => 'pk',
        //Основные параметры
        'cart_event_user_id' => 'int(11) UNSIGNED NOT NULL',
        'cart_event_cart_id' => 'int(11) UNSIGNED NOT NULL',
        'cart_event_cart_event_type_id' => 'int(11) UNSIGNED NOT NULL',
        'cart_event_info' => 'varchar(255) NOT NULL',
        //Системные
        'cart_event_date_create' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            ), [
        'INDEX ' . App::$cur->db->table_prefix . '_ecommerce_cartEventCart (cart_event_cart_id)',
        'INDEX ' . App::$cur->db->table_prefix . '_ecommerce_cartEventDate (cart_event_date_create)'
    ]);
    //Блокировки товара
    App::$cur->db->createTable('ecommerce_warehouse_block', [
        'warehouse_block_id' => 'pk',
        //Основные параметры
        'warehouse_block_cart_id' => 'int(11) UNSIGNED NOT NULL',
        'warehouse_block_item_offer_id' => 'int(11) UNSIGNED NOT NULL',
        'warehouse_block_count' => 'DECIMAL(10, 3) NOT NULL',
            ], [
        'INDEX ' . App::$cur->db->table_prefix . '_ecommerce_warehousesBlockCart (warehouse_block_cart_id)',
        'INDEX ' . App::$cur->db->table_prefix . '_ecommerce_warehousesBlockItem (warehouse_block_item_offer_id)'
            ]
    );
};
