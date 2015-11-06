<?php

namespace Merchants;

class Pay extends \Model
{
    static function relations()
    {
        return [
            'status' => [
                'model' => 'Merchants\Pay\Status',
                'col' => 'pay_status_id'
            ]
        ];
    }

}
