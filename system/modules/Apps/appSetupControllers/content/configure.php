<?php

$form = new Ui\Form();
$form->begin();
foreach ($inputs as $input) {
    $form->input($input['type'], $input['name'], $input['label'], $input['options']);
}
$form->end();
