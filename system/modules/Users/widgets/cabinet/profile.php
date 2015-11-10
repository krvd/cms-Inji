<h3>Мой профиль</h3>
<?php
$form = new Ui\ActiveForm(Users\User::$cur->info, 'profile');
$form->header = false;
$form->checkRequest();
$form->draw();
