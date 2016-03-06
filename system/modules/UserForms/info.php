<?php

return [
    'name' => 'Формы',
    'menu' => [
        'appAdmin' => [
            [
                'name' => 'Формы связи',
                'href' => '/admin/userForms/Form',
                'childs' => [
                    [
                        'name' => 'Полученные формы',
                        'href' => '/admin/userForms/recive',
                    ],
                ]
            ]
        ]
    ]
];
