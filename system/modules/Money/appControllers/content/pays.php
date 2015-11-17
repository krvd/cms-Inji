<?php

$table = new \Ui\Table();
$table->name = 'Ваши счета';
$table->setCols([
    '№',
    'Описание',
    'Сумма',
    'Валюта',
    ''
]);
foreach ($pays as $pay) {
    $table->addRow([
        $pay->id,
        $pay->description,
        $pay->sum,
        $pay->currency->name(),
        '<a href = "/money/merchants/pay/' . $pay->id . '" class="btn btn-success btn-sm">Оплатить</a>'
    ]);
}
$table->draw();
