<h3>Мой профиль</h3>
<?php
$form = new Ui\ActiveForm(Users\User::$cur, 'profile');
$form->header = '';
$form->checkRequest([], true);
$form->draw();
