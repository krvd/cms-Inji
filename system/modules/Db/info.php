<?php

return [
    'name' => 'Базы данных',
    'configure' => [
        'default' => [
            'type' => 'select',
            'label' => 'Используемая база данных',
            'model' => 'Db\Options',
            'col' => 'connect_alias'
        ]
    ]
];
