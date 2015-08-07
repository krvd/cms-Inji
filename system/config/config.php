<?php

return [
    'autoloadModules' => [],
    'modules' => [
        'StaticLoader',
        'Ui',
        'Access'
    ],
    'moduleRouter' => [
        'static' => 'StaticLoader'
    ],
    'assets' => [
        'js' => [
            [
                'file' => '/static/moduleAsset/Server/js/Server.js',
                'name' => 'Server',
                'libs' => ['noty']
            ]
        ]
    ]
];
