<h2>Подтверждение перевода №<?= $transfer->id; ?></h2>
<p>Перевод валюты <?= $transfer->currency->name(); ?> на сумму <?= $transfer->amount; ?> пользователю <?= $transfer->toUser->name(); ?></p>
<p>Вам на почту было отправлено письмо для подтверждения перевода. Вставьте полученный код в поле ниже</p>
<?php
$form = new \Ui\Form();
$form->begin();
$form->input('text', 'code', 'Код подтверждения', ['value' => !empty($_GET['code']) ? $_GET['code'] : '']);
$form->end('Подтвердить');
?>
<a href="/money/cancelTransfer/<?= $transfer->id; ?>">Отменить перевод</a>