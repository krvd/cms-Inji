<?php

/**
 * Item param
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2015 Alexey Krupskiy
 * @license https://github.com/injitools/cms-Inji/blob/master/LICENSE
 */

namespace Ecommerce\Item\Offer;

class Param extends \Model
{
    public static $objectName = 'Параметр товара';
    public static $labels = [
        'item_offer_option_id' => 'Параметр предложения',
        'item_offer_id' => 'Предложение',
        'value' => 'Значение',
    ];
    public static $cols = [
        //Основные параметры
        'item_offer_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'offer'],
        'item_offer_option_id' => ['type' => 'select', 'source' => 'relation', 'relation' => 'option', 'onChange' => 'reloadForm'],
        'value' => ['type' => 'dynamicType', 'typeSource' => 'selfMethod', 'selfMethod' => 'realType'],
        //Системные
        'date_create' => ['type' => 'dateTime']
    ];

    public static function indexes()
    {
        return [
            'ecommerce_itemOfferOptionRelation' => [
                'type' => 'INDEX',
                'cols' => [
                    'item_offer_param_item_offer_id',
                    'item_offer_param_item_offer_option_id'
                ]
            ],
            'ecommerce_paramItemOfferIndex' => [
                'type' => 'INDEX',
                'cols' => [
                    'item_offer_param_item_offer_id',
                ]
            ],
            'ecommerce_paramOfferOptionIndex' => [
                'type' => 'INDEX',
                'cols' => [
                    'item_offer_param_item_offer_option_id'
                ]
            ],
        ];
    }

    public function realType()
    {
        if ($this->option && $this->option->type)  {
            $type = $this->option->type;

            if ($type == 'select') {
                return [
                    'type' => 'select',
                    'source' => 'relation',
                    'relation' => 'option:items',
                ];
            }
            return $type;
        }
        return 'text';
    }

    public static $dataManagers = [

        'manager' => [
            'name' => 'Параметры предложения',
            'cols' => [
                'item_offer_option_id',
                'item_offer_id',
                'value',
            ],
        ],
    ];
    public static $forms = [
        'manager' => [
            'map' => [
                ['item_offer_id', 'item_offer_option_id'],
                ['value']
            ]
    ]];

    function name()
    {
        return $this->value;
    }

    public function value($default = '')
    {
        if ($this->option->type != 'select') {
            return $this->value;
        } elseif ($this->optionItem) {
            return $this->optionItem->value;
        }
        return $default;
    }

    public static function relations()
    {
        return [
            'file' => [
                'model' => 'Files\File',
                'col' => 'value'
            ],
            'option' => [
                'model' => 'Ecommerce\Item\Offer\Option',
                'col' => 'item_offer_option_id'
            ],
            'offer' => [
                'model' => 'Ecommerce\Item\Offer',
                'col' => 'item_offer_id'
            ],
            'optionItem' => [
                'model' => 'Ecommerce\Item\Offer\Option\Item',
                'col' => 'value'
            ]
        ];
    }

}
