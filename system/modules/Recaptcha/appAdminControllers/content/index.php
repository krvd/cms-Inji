<?php

$form = new Ui\Form();
$form->begin();
$form->input('text', 'sitekey', 'sitekey', ['value' => $config['sitekey']]);
$form->input('text', 'secret', 'secret', ['value' => $config['secret']]);
$form->end();
