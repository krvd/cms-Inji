<?php

namespace Money;

class Pay extends \Model
{
    static function relations()
    {
        return [
            'currency' => [
                'model' => 'Money\Currency',
                'col' => 'currency_id'
            ],
            'status' => [
                'model' => 'Money\Pay\Status',
                'col' => 'pay_status_id'
            ]
        ];
    }

}
