<?php

$attributes = [
    'type' => 'hidden',
    'name' => $name,
    'value' => !empty($options['value']) ? $options['value'] : ''
];
if (!empty($options['attributes'])) {
    $attributes = array_merge($attributes, $options['attributes']);
}
echo Html::el('input', $attributes, '', null);
